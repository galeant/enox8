<?php

namespace App\Http\Controllers\V1\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use Illuminate\Filesystem\Filesystem;
use App\Exceptions\ProductUpdateException;

use App\Models\V1\Product;
use App\Models\V1\Type;
use App\Models\V1\Category;
use App\Models\V1\Tag;
use App\Models\V1\Store;
use App\Models\V1\Image;
use App\Models\V1\User;
use App\Models\V1\Review;

use Carbon\Carbon;

use App\Http\Requests\Dashboard\Catalog\CreateRequest;
use App\Http\Requests\Dashboard\Catalog\UpdateRequest;
use App\Http\Requests\Dashboard\Catalog\ChangeStatusTypeRequest;
use App\Http\Requests\Dashboard\Catalog\ChangeStatusProductRequest;
use App\Http\Response\Dashboard\CatalogTransformer;


class CatalogController extends Controller
{
    public function getData(Request $request, $id = null)
    {
        try {
            $user = Auth::user();
            $data = Product::with('type', 'category', 'images');
            $per_page = $request->input('per_page', 10);

            if ($user->can_access_admin) {
                $data = $data->where('store_id', $user->store_id);
            }

            // if($request->filled('filter')){
            //     foreach($request->filter as $ft){
            //         switch($ft['operator']){
            //             case 'eq':
            //                 $opr = '=';
            //                 break;
            //             case 'ne':
            //                 $opr = '!=';
            //                 break;
            //             case 'lt':
            //                 $opr = '<';
            //                 break;
            //             case 'le':
            //                 $opr = '<=';
            //                 break;
            //             case 'gt':
            //                 $opr = '>';
            //                 break;
            //             case 'ge':
            //                 $opr = '>=';
            //                 break;
            //         }
            //         $data  = $data->where($ft['field'],$opr,$ft['value']);
            //     }
            // }

            // if($request->filled('name')){
            //     $data = $data->where('name','ilike','%'.$request->name.'%');
            //                     // ->where(function($q1)use($request){
            //                     //     // ->orWhere(function($q)use($request){
            //                     //     //     $q->whereHas('category',function($q1)use($request){
            //                     //     //         $q1->where('slug','ilike','%'.$request->name.'%');
            //                     //     //     });
            //                     //     // })
            //                     //     // ->orWhere(function($q)use($request){
            //                     //     //     $q->whereHas('type',function($q1)use($request){
            //                     //     //         $q1->where('slug','ilike','%'.$request->name.'%');
            //                     //     //     });
            //                     //     // });
            //                     // });


            // }
            // if($request->filled('id')){
            //     $ids = explode(',',$request->id);
            //     $data = $data->whereIn('id',$ids);
            // }

            // if($request->filled('description')){
            //     $data = $data->where('description','ilike','%'.$request->description.'%');
            // }

            // if($request->filled('slug')){
            //     $data = $data->where('slug','ilike','%'.$request->slug.'%');
            // }

            $sort = 'created_at';
            // if($request->filled('sort')){
            //     switch($request->sort){
            //         case 'price':
            //             $sort = 'ordering_price';
            //         break;
            //     }
            // }

            $order = 'desc';
            // if($request->filled('sort')){

            // }

            if (isset($id)) {
                $data = $data->where('id', $id)->firstOrFail();
                return CatalogTransformer::detail($data);
            } else if ($request->filled('all')) {
                $data = $data->get();
                return CatalogTransformer::list($data);
            } else {
                // if($order == 'review'){
                //     $data  = $data->orderByRaw('total_review '.$order);
                // }else{
                //     $data  = $data->orderBy($sort,$order);
                // }
                $data = $data->orderBy($sort, $order)->paginate($per_page);
                $data->appends($request->all());
                return CatalogTransformer::list($data);
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function create(CreateRequest $request)
    {
        DB::beginTransaction();
        $delete_path = [];
        try {
            $user = Auth::user();
            if ($user->can_access_admin && $user->store_id !== NULL) {
                $request->merge([
                    'store_id' => $user->store_id
                ]);
            }
            $slug = str_slug($request->name) . '-' . str_slug($request->code) . '-' . str_slug($user->store_id);
            $folder_upload = 'public/product/' . $slug . '/';
            // if (Storage::exists($folder_upload)) {
            //     Storage::deleteDirectory($folder_upload);
            // }
            $product = Product::create([
                'slug' => $slug,
                'name' => $request->name,
                'description' => $request->description,
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'store_id' => $request->store_id,
                'code' => $request->input('code', $request->store_id),
                'is_featured' => $request->filled('featured') ? $request->featured : false,
                'status' => $request->filled('status') ? $request->status : 'draft',
                'weight' => $request->filled('weight') ? $request->weight : NULL,
                'condition' => $request->filled('condition') ? $request->condition : NULL,
                'minimum_order' => $request->filled('minimum_order') ? $request->minimum_order : NULL,
                'insurance' => $request->filled('insurance') ? $request->insurance : NULL
            ]);
            // TYPE
            $default_price = NULL;
            if (count($request->type) > 0) {
                foreach ($request->type as $index => $type) {
                    if (strpos($type['image'], 'http://') !== false && strpos($type['image'], 'https://') !== false) {
                        $img = imageUpload($folder_upload, @file_get_contents($type['image']));
                    } else {
                        $img = imageUpload($folder_upload, $type['image']);
                    }
                    $delete_path[] = str_replace('storage', 'public', $img);

                    $product_type = Type::create([
                        'product_id' => $product->id,
                        'image' => $img,
                        'name' => isset($type['name']) ? $type['name'] : 'Type-' . ($index + 1),
                        'price' => $type['price'],
                        'stock' => $type['stock'],
                        'is_default' => isset($type['default']) ? ($type['default'] === true) ?: false : false,
                        'discount_value' => isset($type['discount_value']) ? $type['discount_value'] : NULL,
                        'discount_unit' => isset($type['discount_unit']) ? $type['discount_unit'] : NULL,
                        'discount_effective_start_date' => isset($type['discount_effective_start_date']) ? $type['discount_effective_start_date'] : NULL,
                        'discount_effective_end_date' => isset($type['discount_effective_end_date']) ? $type['discount_effective_end_date'] : NULL,
                        'status' => isset($type['status']) ? $type['status'] : 'draft',
                    ]);

                    $display_price = $product_type->price;
                    if ($product_type->is_default === true) {
                        $default_price = $product_type->price;
                        $now = Carbon::now();
                        $discount_start_date = Carbon::parse($product_type->discount_effective_start_date);
                        $discount_end_date = Carbon::parse($product_type->discount_effective_end_date);
                        if ($now->between($discount_start_date, $discount_end_date)) {
                            switch ($product_type->discount_unit) {
                                case 'decimal':
                                    $display_price = $product_type->price - $product_type->discount_value;
                                    break;
                                case 'percentage':
                                    $display_price =  $product_type->price - (($product_type->price * $product_type->discount_value) / 100);
                                    break;
                            }
                        }
                    }
                }
            }
            // CATEGORY
            $category_id = $this->getCategoryIdRelated($request->category);
            $product->category()->sync($category_id);
            // DISCOUNT
            $discount_on_category = $this->getDiscountOnCategory($product->category->pluck('id'));
            $display_price = $display_price - $this->calculateDiscount($default_price, $discount_on_category);
            $product->update([
                'display_price' => $display_price
            ]);
            // MAIN IMAGE
            if (strpos($request->main_image_url, 'http://') !== false || strpos($request->main_image_url, 'https://') !== false) {
                $image = imageUpload($folder_upload, @file_get_contents($request->main_image_url));
            } else {
                $image = imageUpload($folder_upload, $request->main_image_url);
            }
            $delete_path[] = str_replace('storage', 'public', $image);

            $product->update([
                'main_image' => $image
            ]);
            // IMAGE GALLERY
            if (count($request->images) > 0) {
                foreach ($request->images as $im) {
                    if (strpos($im, 'http://') !== false && strpos($im, 'https://') !== false) {
                        $image_gallery = imageUpload($folder_upload, @file_get_contents($im));
                    } else {
                        $image_gallery = imageUpload($folder_upload, $im);
                    }
                    $delete_path[] = str_replace('storage', 'public', $image_gallery);
                    Image::create([
                        'url' => $image_gallery,
                        'relation_to' => 'product',
                        'relation_id' => $product->id
                    ]);
                }
            }
            // TAG
            $tg_sync = [];
            foreach ($request->tag as $tg) {
                $tvld = Tag::where('slug', str_slug($tg))->first();
                if ($tvld === NULL) {
                    $tsl = str_slug($tg);
                    $tnm = ucwords(str_replace('-', ' ', $tsl));
                    $tvld = Tag::create([
                        'name' => $tnm,
                        'slug' => $tsl
                    ]);
                }
                $tg_sync[] = $tvld->id;
            }
            $product->tag()->sync($tg_sync);
            DB::commit();

            return CatalogTransformer::detail($product->fresh());
        } catch (\Exception $e) {
            DB::rollBack();
            foreach ($delete_path as $dp) {
                Storage::delete($dp);
            }
            throw new \Exception($e->getMessage());
        }
    }

    public function update(UpdateRequest $request, $id)
    {
        DB::beginTransaction();
        $deleted_image = [];
        $upload_image = [];
        try {
            $user = Auth::user();
            if ($user->can_access_admin && $user->store_id !== NULL) {
                $request->merge([
                    'store_id' => $user->store_id
                ]);
            }
            $product = Product::where('id', $id)->firstOrFail();
            $request->merge([
                'slug' => str_slug($request->name) . '-' . str_slug($request->code) . '-' . str_slug($user->store_id)
            ]);
            $product->where('id', $id)->update([
                'slug' => str_slug($request->slug),
                'name' => $request->name,
                'description' => $request->description,
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'store_id' => $request->input('store_id', $user->store->id),
                'code' => $request->code,
                'is_featured' => $request->filled('featured') ? $request->featured : false,
                'status' => $request->filled('status') ? $request->status : 'draft',
                'weight' => $request->filled('weight') ? $request->weight : NULL,
                'condition' => $request->filled('condition') ? $request->condition : NULL,
                'minimum_order' => $request->filled('minimum_order') ? $request->minimum_order : NULL,
                'insurance' => $request->filled('insurance') ? $request->insurance : NULL
            ]);


            $old_path_url = url('/storage/product/' . $product->slug) . '/';
            $old_path_folder = str_replace(url('/') . '/', '', $old_path_url);
            $new_path_folder = 'storage/product/' . $product->fresh()->slug . '/';

            $old_upload_path = 'public/product/' . $product->slug . '/';
            $new_upload_path = 'public/product/' . $product->fresh()->slug . '/';

            // PRODUCT TYPE
            $default_price = NULL;
            $product_type = Type::where('product_id', $id)->get();
            $maxIndex = $product_type->count();
            if (count($request->type) > $maxIndex) {
                $maxIndex = count($request->type);
            }
            for ($i = 0; $i < $maxIndex; $i++) {
                $fill = [
                    'name' => isset($request->type[$i]['name']) ? $request->type[$i]['name'] : 'Type-' . ($i + 1),
                    'price' => isset($request->type[$i]['price']) ? $request->type[$i]['price'] : NULL,
                    'stock' => isset($request->type[$i]['stock']) ? $request->type[$i]['stock'] : NULL,
                    'discount_value' => isset($request->type[$i]['discount_value']) ? $request->type[$i]['discount_value'] : NULL,
                    'discount_unit' => isset($request->type[$i]['discount_unit']) ? $request->type[$i]['discount_unit'] : NULL,
                    'discount_effective_start_date' => isset($request->type[$i]['discount_effective_start_date']) ? $request->type[$i]['discount_effective_start_date'] : NULL,
                    'discount_effective_end_date' => isset($request->type[$i]['discount_effective_end_date']) ? $request->type[$i]['discount_effective_end_date'] : NULL,
                    'is_default' => isset($request->type[$i]['default']) ? $request->type[$i]['default'] : false
                ];
                if (isset($request->type[$i]) && isset($product_type[$i])) {
                    $fill['image'] = $request->type[$i]['image'];
                    if (strpos($request->type[$i]['image'], 'http://') !== false || strpos($request->type[$i]['image'], 'https://') !== false) {
                        $filename = str_replace($old_path_url, '', $request->type[$i]['image']);
                        $existing_img = $old_path_folder . $filename;
                        $new_img = $new_path_folder . $filename;
                        if ($existing_img !== $new_img || strpos($request->type[$i]['image'], url('/')) === false) {
                            $fill['image'] = imageUpload($new_upload_path, @file_get_contents($request->type[$i]['image']));
                            $deleted_image[] = $product_type[$i]->image_physical_path;
                        }
                    } else {
                        $fill['image'] = imageUpload($new_upload_path, $request->type[$i]['image']);
                        $deleted_image[] = $product_type[$i]->image_physical_path;
                    }

                    $upload_image[] = str_replace('storage', 'public', $fill['image']);
                    Type::where('id', $product_type[$i]->id)->update($fill);
                } else if (isset($request->type[$i]) && !isset($product_type[$i])) {
                    if (strpos($request->type[$i]['image'], 'http://') !== false && strpos($request->type[$i]['image'], 'https://') !== false) {
                        $fill['image'] = imageUpload($new_upload_path, @file_get_contents($request->type[$i]['image']));
                    } else {
                        $fill['image'] = imageUpload($new_upload_path, $request->type[$i]['image']);
                    }
                    $fill['product_id'] = $product->id;
                    $upload_image[] = str_replace('storage', 'public', $fill['image']);
                    Type::create($fill);
                } else if (!isset($request->type[$i]) && isset($product_type[$i])) {
                    Type::where('id', $product_type[$i]->id)->delete();
                    $deleted_image[] = $product_type[$i]->image_physical_path;
                }


                if (isset($fill['is_default']) && $fill['is_default'] === true) {
                    $display_price = $fill['price'];
                    $default_price = $fill['price'];
                    if (isset($fill['discount_value'])) {
                        $now = Carbon::now();
                        $discount_start_date = Carbon::parse($fill['discount_effective_start_date']);
                        $discount_end_date = Carbon::parse($fill['discount_effective_end_date']);
                        if ($now->between($discount_start_date, $discount_end_date)) {
                            switch ($fill['discount_unit']) {
                                case 'decimal':
                                    $display_price = $fill['price'] - $fill['discount_value'];
                                    break;
                                case 'percentage':
                                    $display_price =  $fill['price'] - (($fill['price'] * $fill['discount_value']) / 100);
                                    break;
                            }
                        }
                    }
                }
            }

            // CATEGORY
            $category_id = $this->getCategoryIdRelated($request->category);
            $product->category()->sync($category_id);

            // MAIN IMAGE
            $m_image = $request->main_image_url;
            if (strpos($request->main_image_url, 'http://') !== false || strpos($request->main_image_url, 'https://') !== false) {
                $fn_img = str_replace($old_path_url, '', $request->main_image_url);
                $em_img = $old_path_folder . $fn_img;
                $nm_img = $new_path_folder . $fn_img;
                if ($em_img != $nm_img || strpos($request->main_image_url, url('/')) === false) {
                    $m_image = imageUpload($new_upload_path, @file_get_contents($request->main_image_url));
                    $deleted_image[] = str_replace('storage', 'public', $product->main_image_physical_path);
                }
            } else {
                $m_image = imageUpload($new_upload_path, $request->main_image_url);
                $deleted_image[] = str_replace('storage', 'public', $product->main_image_physical_path);
            }
            $upload_image[] = str_replace('storage', 'public', $m_image);

            // DISCOUNT
            $discount_on_category = $this->getDiscountOnCategory(array_keys($category_id));
            $discount_on_product = $product->discount->filter(function ($v) {
                $nw = Carbon::now();
                $dstd = Carbon::parse($v->effective_start_date);
                $detd = Carbon::parse($v->effective_end_date);
                if ($nw->between($dstd, $detd) && $v->status === 'Publish') {
                    return $v;
                }
            })->transform(function ($val, $key) {
                return [
                    'id' => $val->id,
                    'unit' => $val->unit,
                    'value' => $val->value
                ];
            });

            $discount_list = $discount_on_category;
            foreach ($discount_on_product as $dp) {
                if (!in_array($dp['id'], array_column($discount_list, 'id'))) {
                    $discount_list[] = $dp;
                }
            }
            $display_price = $display_price - $this->calculateDiscount($default_price, $discount_list);
            $product->update([
                'main_image' => $m_image,
                'display_price' => $display_price
            ]);

            // IMAGE
            $product_image = Image::where([
                'relation_to' => 'product',
                'relation_id' => $id
            ])->get();

            $maxIm = $product_image->count();
            if (count($request->images) > $maxIm) {
                $maxIm = count($request->images);
            }

            for ($i = 0; $i < $maxIm; $i++) {
                if (isset($product_image[$i]) && isset($request->images[$i])) {
                    $img_g = $request->images[$i];
                    if (strpos($request->images[$i], 'http://') !== false || strpos($request->images[$i], 'https://') !== false) {
                        $fmg_img = str_replace($old_path_url, '', $request->images[$i]);
                        $emg_img = $old_path_folder . $fmg_img;
                        $nmg_img = $new_path_folder . $fmg_img;
                        if ($emg_img !== $nmg_img || strpos($request->images[$i], url('/')) === false) {
                            $img_g = imageUpload($new_upload_path, @file_get_contents($request->images[$i]));
                            $deleted_image[] = str_replace('storage', 'public', $product_image[$i]->url);
                        }
                    } else {
                        $img_g = imageUpload($new_upload_path, $request->images[$i]);
                        $deleted_image[] = str_replace('storage', 'public', $product_image[$i]->url);
                    }

                    $upload_image[] = str_replace('storage', 'public', $img_g);
                    Image::where('id', $product_image[$i]->id)->update([
                        'url' => $img_g,
                        'relation_to' => 'product'
                    ]);
                } else if (!isset($product_image[$i]) && isset($request->images[$i])) {
                    $img_g = $request->images[$i];
                    if (strpos($request->images[$i], 'http://') === false && strpos($request->images[$i], 'https://') === false) {
                        $img_g = imageUpload($new_upload_path, @file_get_contents($request->images[$i]));
                    } else {
                        $img_g = imageUpload($new_upload_path, $request->images[$i]);
                    }
                    $upload_image[] = str_replace('storage', 'public', $m_image);
                    Image::create([
                        'url' => $img_g,
                        'relation_to' => 'product',
                        'relation_id' => $product->id
                    ]);
                } else if (isset($product_image[$i]) && !isset($request->images[$i])) {
                    Image::where('id', $product_image[$i]->id)->delete();
                    $deleted_image[] = str_replace('storage', 'public', $product_image[$i]->url);
                }
            }

            // TAG
            $tg_sync = [];
            if ($request->filled('tag')) {
                foreach ($request->tag as $tg) {
                    $tvld = Tag::where('slug', str_slug($tg))->first();
                    if ($tvld === NULL) {
                        $tsl = str_slug($tg);
                        $tnm = ucwords(str_replace('-', ' ', $tsl));
                        $tvld = Tag::create([
                            'name' => $tnm,
                            'slug' => $tsl
                        ]);
                    }
                    $tg_sync[] = $tvld->id;
                }
            }


            $product->tag()->sync($tg_sync);
            DB::commit();
            if ($old_path_folder !== $new_path_folder) {
                Storage::deleteDirectory('public/product/' . $product->slug);
            }
            foreach ($deleted_image as $dim) {
                Storage::delete($dim);
            }
            return CatalogTransformer::detail($product->fresh());
        } catch (\Exception $e) {
            DB::rollBack();
            foreach ($upload_image as $dt) {
                Storage::delete($dt);
            }
            throw new \Exception($e->getMessage());
            // throw new ProductUpdateException($e->getMessage(), $upload_image);
        }
    }

    public function delete($id)
    {
        try {
            $user = Auth::user();

            $product = Product::where('id', $id);
            if ($user->can_access_admin) {
                $product = $product->where('store_id', $user->store_id);
            }
            $product = $product->firstOrFail();
            $product->delete();

            DB::commit();
            return CatalogTransformer::delete($product);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    public function changeStatusProduct(ChangeStatusProductRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = Product::whereIn('id', $request->id)->get();
            foreach ($data as $d) {
                $d->update([
                    'status' => $request->status
                ]);
            }
            DB::commit();
            return CatalogTransformer::list($data->fresh(), true);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    public function changeStatusProductType(ChangeStatusTypeRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = Type::whereIn('id', $request->type_id)->whereHas('product', function ($q) use ($request) {
                $q->where('id', $request->product_id);
            })->get();
            foreach ($data as $d) {
                $update = [
                    'status' => $request->status
                ];
                if ($request->status === 'draft' && $d->is_default === true) {
                    $update['is_default'] = false;
                }
                $d->update($update);
            }

            $type_in_product = Type::whereHas('product', function ($q) use ($request) {
                $q->where('id', $request->product_id);
            })->get();

            $publish = $type_in_product->filter(function ($v) {
                if ($v->status === 'Publish') {
                    return $v;
                }
            });

            if (count($publish) == 0) {
                $filter_default = $type_in_product->sortBy('price')->first();
                $filter_default->update([
                    'is_default' => true
                ]);
                $filter_default->product()->update([
                    'display_price' => 0,
                    'status' => 'draft'
                ]);
            } else {
                $filter_default = $publish->first(function ($val) {
                    if ($val->is_default === true) {
                        return $val;
                    }
                });
                if ($filter_default === NULL) {
                    $filter_default = $publish->sortBy('price')->first();
                    $filter_default->update([
                        'is_default' => true
                    ]);
                }

                $discount_valid = [];
                foreach ($filter_default->product->all_discount as $fdpad) {
                    $st_fdpad = Carbon::parse($fdpad['effective_start_date']);
                    $ed_fdpad = Carbon::parse($fdpad['effective_end_date']);
                    if (Carbon::now() >= $st_fdpad && Carbon::now() <= $ed_fdpad && $fdpad['status'] === 'Publish') {
                        $discount_valid[] = $fdpad;
                    }
                }

                $price = $filter_default->price;
                if ($filter_default->discount_price !== NULL) {
                    $price = $filter_default->discount_price;
                }
                $filter_default->product()->update([
                    'display_price' => $price - $this->calculateDiscount($filter_default->price, $discount_valid),
                    'status' => 'publish'
                ]);
            }
            DB::commit();

            return CatalogTransformer::detail($filter_default->product->fresh());
            // return CatalogTransformer::type($data->fresh());
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    public function getReview($id, Request $request)
    {
        try {
            $user = Auth::user();
            $data = Review::with('user.detail', 'product', 'product_type', 'image')->whereHas('product', function ($q) use ($id) {
                $q->where('id', $id);
            });
            $per_page = $request->input('per_page', 10);
            if ($user->can_access_admin) {
                $data = $data->whereHas('product', function ($q) use ($user) {
                    $q->where('store_id', $user->store_id);
                });
            }
            $sort = 'created_at';
            $order = 'desc';
            $data = $data->orderBy($sort, $order)->paginate($per_page);
            $data->appends($request->all());
            return CatalogTransformer::reviewList($data);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }


    private function getCategoryIdRelated($category)
    {
        $result = [];
        $parentes = false;
        $counter = 0;
        $c = Category::where('id', $category)->first();
        // GET CHILDREN
        if ($c->children->count() > 0) {
            foreach ($c->children as $cc) {
                $result[] = $cc->id;
            }
        }
        // GET PARENT
        while ($parentes == false && $counter <= 2) {
            if ($c->id == $category) {
                $result[$c->id] = ['selected' => true];
            } else {
                $result[] = $c->id;
            }

            if ($c->parent !== NULL) {
                $parentes = false;
                $c = $c->parent;
            } else {
                $parentes = true;
            }
            $counter++;
        }
        return $result;
    }

    private function getDiscountOnCategory($category_ids)
    {

        $now = Carbon::now();
        $category = Category::whereHas('discount', function ($q) use ($now) {
            $q->whereDate('effective_start_date', '<=', $now)
                ->whereDate('effective_end_date', '>=', $now)
                ->where('status', 'publish');
        })
            ->whereIn('id', $category_ids)
            ->get();
        $discount = [];
        foreach ($category as $ct) {
            foreach ($ct->discount as $dsc) {
                if (!in_array($dsc->id, array_column($discount, 'id'))) {
                    $discount[] = [
                        'id' => $dsc->id,
                        'value' => $dsc->value,
                        'unit' => $dsc->unit
                    ];
                }
            }
        }
        return $discount;
    }

    private function calculateDiscount($default_price, $discount)
    {
        $calculate_discount = [];
        foreach ($discount as $dsc) {
            switch ($dsc['unit']) {
                case 'decimal':
                    $calculate_discount[] = $dsc['value'];
                    break;
                case 'percentage':
                    $calculate_discount[] = ($default_price * $dsc['value']) / 100;
                    break;
            }
        }
        return array_sum($calculate_discount);
    }
}
