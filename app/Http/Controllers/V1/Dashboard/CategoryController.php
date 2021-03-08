<?php

namespace App\Http\Controllers\V1\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use Illuminate\Filesystem\Filesystem;
use App\Http\Controllers\Controller;
use App\Http\Response\Dashboard\CategoryTransformer;
use App\Http\Requests\Dashboard\Category\CreateRequest;
use App\Models\V1\Category;
use App\Models\V1\Product;
use DB;

class CategoryController extends Controller
{

    private $folder_icon = 'public/category/icon/';
    private $folder_thumbnail = 'public/category/thumbnail/';

    public function getData(Request $request, $id = null)
    {
        try {
            $per_page = $request->input('per_page', 10);
            $data = Category::with('icon', 'thumbnail');

            $order = $request->input('order', 'name');
            $sort = $request->input('sort', 'desc');

            if ($request->filled('parent')) {
                if ($request->parent === false || $request->parent === 'false') {
                    $data = $data->where('parent_id', '!=', 0);
                }
            }
            // if($request->filled('name')){
            //     $data = $data->where('name','ilike','%'.$request->name.'%');
            // }

            // if($request->filled('id')){
            //     $ids = explode(',',$request->id);
            //     $data = $data->whereIn('id',$ids);
            // }
            if ($id != null) {
                $data = $data->where('id', $id)->firstOrFail();
                return CategoryTransformer::detail($data);
            } else if ($request->filled('all')) {
                $data = $data->get();
                return CategoryTransformer::list($data);
            } else {
                $data = $data->orderBy($order, $sort)->paginate($per_page);
                $data->appends($request->all());
                return CategoryTransformer::list($data);
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function create(CreateRequest $request, $id = NULL)
    {
        DB::beginTransaction();
        $delete_path = [];
        try {
            $fill = [
                'slug' => str_slug($request->name),
                'name' => $request->name,
                'description' => $request->description,
                'parent_id' => $request->parent_id
            ];

            if ($request->filled('status')) {
                $fill['status'] = $request->status;
            }

            $fill['order'] = Category::max('order') + 1;
            if ($request->filled('order')) {
                $fill['order'] = $request->order;
            }

            $exist = Category::where('slug', str_slug($request->name))->withTrashed()->first();
            if ($id === null && $exist === NULL) {
                $data = Category::create($fill);
                if ($request->filled('icon')) {
                    $icon = imageUpload($this->folder_icon, $request->icon, [
                        'width' => 180,
                        'height' => 180
                    ]);
                    $data->icon()->create([
                        'url' => $icon,
                        'relation_to' => 'category_icon'
                    ]);
                    $data->icon->fresh();
                    $delete_path[] = str_replace('storage', 'public', $icon);
                }

                if ($request->filled('thumbnail')) {
                    $thumbnail = imageUpload($this->folder_thumbnail, $request->thumbnail, [
                        'width' => 540,
                        'height' => NULL
                    ]);
                    $data->thumbnail()->create([
                        'url' => $thumbnail,
                        'relation_to' => 'category_thumbnail'
                    ]);
                    $data->thumbnail->fresh();
                    $delete_path[] = str_replace('storage', 'public', $thumbnail);
                }
            } else if ($id !== NULL || $exist !== NULL) {
                if ($exist !== NULL) {
                    $data = $exist;
                    $data->restore();
                } else {
                    $data = Category::where('id', $id)->with('children', 'icon', 'thumbnail')->firstOrFail();
                }
                $old_path = [
                    $data->icon === NULL ?: $data->icon->url,
                    $data->thumbnail === NULL ?: $data->thumbnail->url
                ];

                $data->update($fill);

                $affected_id = $this->getCategoryIdAffected($data);
                $affected_category = Category::whereIn('id', $affected_id)->update([
                    'status' => $request->status
                ]);

                $affected_product = Product::whereHas('category', function ($q) use ($affected_id) {
                    $q->whereIn('id', $affected_id);
                })->update([
                    'status' => $request->status
                ]);

                if ($request->filled('icon') && $old_path[0] !== str_replace(url('/') . '/', '', $request->icon)) {
                    $icon = imageUpload($this->folder_icon, $request->icon, [
                        'width' => 180,
                        'height' => 180
                    ]);
                    $data->icon()->update([
                        'url' => $icon,
                        'relation_to' => 'category_icon'
                    ]);
                    $delete_path[0] = str_replace('storage', 'public', $icon);
                }

                if ($request->filled('thumbnail') && $old_path[1] !== str_replace(url('/') . '/', '', $request->thumbnail)) {
                    $thumbnail = imageUpload($this->folder_thumbnail, $request->thumbnail, [
                        'width' => 540,
                        'height' => NULL
                    ]);
                    $data->thumbnail()->update([
                        'url' => $thumbnail,
                        'relation_to' => 'category_thumbnail'
                    ]);
                    $delete_path[1] = str_replace('storage', 'public', $thumbnail);
                }
            }
            DB::commit();
            if (isset($old_path)) {
                foreach ($delete_path as $idx => $dp) {
                    Storage::delete(str_replace('storage', 'public', $old_path[$idx]));
                }
            }
            return CategoryTransformer::detail($data->fresh());
        } catch (\Exception $e) {
            DB::rollBack();
            foreach ($delete_path as $dp) {
                Storage::delete($dp);
            }
            throw new \Exception($e->getMessage());
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $data = Category::where('id', $id)->firstOrFail();
            $data->delete();
            DB::commit();
            return CategoryTransformer::delete($data);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    private function getCategoryIdAffected($category)
    {
        $category = $category->toArray();
        $return = [];
        array_walk_recursive($category, function ($value, $key) use (&$return) {
            if ($key == 'id') {
                $return[] = $value;
            }
        });
        return $return;
    }
}
