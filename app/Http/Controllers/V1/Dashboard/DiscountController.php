<?php

namespace App\Http\Controllers\V1\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use App\Http\Requests\Dashboard\Discount\CreateRequest;
use App\Http\Requests\Dashboard\Discount\UpdateRequest;
use App\Http\Requests\Dashboard\Discount\ChangeStatusRequest;
use App\Http\Response\Dashboard\DiscountTransformer;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use Illuminate\Filesystem\Filesystem;

use App\Models\V1\Category;
use App\Models\V1\Discount;
use App\Models\V1\Product;
use App\Jobs\Discount\ChangeDisplayPrice;

class DiscountController extends Controller
{
    private $folder_path = 'public/discount/';

    public function getData(Request $request, $id = null)
    {
        try {
            $model = Discount::with('product', 'category');
            $page = $request->input('page', 1);
            $per_page = $request->input('per_page', 10);
            $order_by = $request->input('order_by', 'created_at');
            $order = $request->input('order', 'desc');

            if ($id !== null) {
                $data = $model->where('id', $id)->firstOrFail();
                return DiscountTransformer::detail($data);
            } else if ($request->filled('all')) {
                $data = $model->get();
                return DiscountTransformer::list($data);
            } else {
                $data = $model->orderBy($order_by, $order)->paginate($per_page);
                $data->appends($request->all());
                return DiscountTransformer::list($data);
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
            $img = imageUpload($this->folder_path, $request->banner);
            $delete_path[] = str_replace('storage', 'public', $img);

            $discount = Discount::create([
                'name' => $request->name,
                'slug' => str_slug($request->name),
                'description' => $request->description,
                'effective_start_date' => $request->start_date,
                'effective_end_date' => $request->end_date,
                'value' => $request->value,
                'unit' => $request->unit,
                'banner' => imageUpload($this->folder_path, $request->banner),
                'status' => $request->status,
                'is_display' => isset($request->display_on_client) ? $request->display_on_client : true
            ]);

            $product_id = [];
            foreach ($request->product as $rp) {
                $product_id[$rp] = [
                    'type' => 'product'
                ];
            }

            $discount->product()->sync($product_id);

            $category_id = [];
            $category_id_related = $this->getCategoryIdRelated($request->category);
            foreach ($category_id_related as $cr) {
                $category_id[$cr] = [
                    'type' => 'category'
                ];
            }

            $discount->category()->sync($category_id);
            if ($request->status === 'publish') {

                $affected_product_id = $this->getAffectedProductId($discount);
                ChangeDisplayPrice::dispatch($affected_product_id);
            }

            DB::commit();
            return DiscountTransformer::detail($discount);
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
        $uploaded_img = NULL;
        DB::beginTransaction();
        try {
            $discount = Discount::where('id', $id)->firstOrFail();
            $fill = [
                'name' => $request->name,
                'slug' => str_slug($request->name),
                'description' => $request->description,
                'effective_start_date' => $request->start_date,
                'effective_end_date' => $request->end_date,
                'value' => $request->value,
                'unit' => $request->unit,
                'status' => $request->status
            ];
            $old_banner = $discount->banner_physical_path;
            $banner_req = str_replace(url('/') . '/', '', $request->banner);
            if (strpos($request->banner, url('/')) !== false) {
                if ($banner_req !== $discount->banner) {
                    $fill['banner'] = imageUpload($this->folder_path, @file_get_contents($request->banner));
                    $uploaded_img =  str_replace('storage', 'public', $fill['banner']);
                }
            } else {
                $fill['banner'] = imageUpload($this->folder_path, $request->banner);
                $uploaded_img = str_replace('storage', 'public', $fill['banner']);
            }

            $discount->update($fill);

            // GET AFFECTED PRODUCT EXISTING
            $exist_product_id = [];
            if ($discount->product->count() > 0) {
                $exist_product_id = $discount->product->pluck('id')->toArray();
            }

            $exist_category_id = [];
            if ($discount->category->count() > 0) {
                $exist_category_id = $discount->category->pluck('id')->toArray();
            }

            $exist_category_to_product_id = Product::whereHas('category', function ($q) use ($exist_category_id) {
                $q->whereIn('id', $exist_category_id);
            })->get();
            if ($exist_category_to_product_id->count() > 0) {
                $exist_category_to_product_id = $exist_category_to_product_id->pluck('id')->toArray();
            } else {
                $exist_category_to_product_id = $exist_category_to_product_id->toArray();
            }

            //
            // GET AFFTECTED PRODUCT PER REQUEST
            $affected_product_id = $request->product;
            $affected_category_id = $this->getCategoryIdRelated($request->category);

            $affected_category_to_product_id = Product::whereHas('category', function ($q) use ($affected_category_id) {
                $q->whereIn('id', $affected_category_id);
            })->get();
            if ($affected_category_to_product_id->count() > 0) {
                $affected_category_to_product_id = $affected_category_to_product_id->pluck('id')->toArray();
            } else {
                $affected_category_to_product_id = $affected_category_to_product_id->toArray();
            }

            //
            // FOR DISPATCHING
            $existing = array_unique(array_merge($exist_product_id, $exist_category_to_product_id));
            $affected = array_unique(array_merge($affected_product_id, $affected_category_to_product_id));
            $new_id = array_diff($affected, $existing);
            // LIST PRODUCT HAS AFFECTED CHANGE FOR DELETE
            $deleted_id = array_diff($existing, $affected);
            // LIST PRODUCT HAS AFFECTED CHANGE FOR APPLY
            $new_id = array_merge($new_id, array_intersect($affected, $existing));

            // INTO DB
            $product_id = [];
            foreach ($affected_product_id as $ap) {
                $product_id[$ap] = [
                    'type' => 'product'
                ];
            }
            $discount->product()->sync($product_id);
            // INTO DB
            $category_id = [];
            if ($request->filled('category')) {
                foreach ($affected_category_id as $ac) {
                    $category_id[$ac] = [
                        'type' => 'category'
                    ];
                }
                $discount->category()->sync($category_id);
            }
            // DISPATCH FOR CALCULATE CHANGE PUBLISH PRICE FOR AFFECTED PRODUCT (APPLY AND DELETE)
            ChangeDisplayPrice::dispatch($new_id);
            ChangeDisplayPrice::dispatch($deleted_id);
            DB::commit();
            Storage::delete($old_banner);
            return DiscountTransformer::detail($discount->fresh());
        } catch (\Exception $e) {
            DB::rollBack();
            Storage::delete($uploaded_img);
            throw new \Exception($e->getMessage());
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $discount = Discount::where('id', $id)->firstOrFail();
            $affected_product_id = $this->getAffectedProductId($discount);
            ChangeDisplayPrice::dispatch($affected_product_id);
            // $discount->product()->detach();
            // $discount->category()->detach();
            $discount->update([
                'status' => 'draft',
                'is_display' => false
            ]);

            $discount->delete();
            DB::commit();
            return DiscountTransformer::delete($discount);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    public function changeStatus(ChangeStatusRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = Discount::whereIn('id', $request->id)->get();
            foreach ($data as $discount) {
                $discount->update([
                    'status' => $request->status
                ]);
                $affected_product_id = $this->getAffectedProductId($discount);
                ChangeDisplayPrice::dispatch($affected_product_id);
            }
            DB::commit();
            return DiscountTransformer::list($data->fresh());
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    private function getCategoryIdRelated($category)
    {
        $return = [];
        foreach ($category as $ct) {
            $c = Category::where('id', $ct)->with('children')->first()->toArray();
            array_walk_recursive($c, function ($value, $key) use (&$return) {
                if ($key == 'id' && !in_array($value, $return)) {
                    $return[] = $value;
                }
            });
        }
        return $return;
    }

    private function getAffectedProductId($discount)
    {
        $affected_product_id = [];
        if ($discount->product->count() > 0) {
            $affected_product_id = $discount->product->pluck('id')->toArray();
        }
        $affected_category_id = [];
        if ($discount->category->count() > 0) {
            $affected_category_id = $discount->category->pluck('id')->toArray();
        }

        $affected_category_to_product_id = Product::whereHas('category', function ($q) use ($affected_category_id) {
            $q->whereIn('id', $affected_category_id);
        })->get();
        if ($affected_category_to_product_id->count() > 0) {
            $affected_category_to_product_id = $affected_category_to_product_id->pluck('id')->toArray();
        } else {
            $affected_category_to_product_id = $affected_category_to_product_id->toArray();
        }
        return array_unique(array_merge($affected_product_id, $affected_category_to_product_id));
    }
}
