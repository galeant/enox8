<?php

namespace App\Http\Controllers\V1\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Response\Dashboard\BannerTransformer;
use App\Http\Requests\Dashboard\Banner\CreateRequest;
use App\Models\V1\Banner;
use App\Models\V1\Category;
use DB;

use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    private $folder_upload = 'public/banner/';


    public function getData(Request $request, $id = NULL)
    {
        try {
            $data = Banner::with('webBanner', 'mobileBanner');
            if ($id !== NULL) {
                $data = $data->where('id', $id)->firstOrFail();
                return BannerTransformer::detail($data);
            } else {
                $data = $data->paginate(10);
                return BannerTransformer::list($data);
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function create(CreateRequest $request)
    {
        $user = auth()->user();
        DB::beginTransaction();
        $delete_path = [];

        try {
            $data = Banner::create([
                'name' => $request->name,
                'slug' => str_slug($request->name . '-' . $user->store_id),
                'description' => $request->description,
                'relation_to' => $request->type,
                'redirect_url' => $request->input('url', NULL),
                'status' => $request->filled('status') ? $request->status : 'draft'
            ]);

            if ($request->filled('banner_web')) {
                $web_banner = imageUpload($this->folder_upload, $request->banner_web);
                $data->webBanner()->create([
                    'relation_to' => 'web_banner',
                    'url' => $web_banner
                ]);
                $delete_path[] = str_replace('storage', 'public', $web_banner);
            }

            if ($request->filled('banner_mobile')) {
                $mobile_banner = imageUpload($this->folder_upload, $request->banner_mobile);
                $data->mobileBanner()->create([
                    'relation_to' => 'mobile_banner',
                    'url' => $mobile_banner
                ]);
                $delete_path[] = str_replace('storage', 'public', $mobile_banner);
            }

            if ($request->type !== 'redirect') {
                if ($request->type === 'category') {
                    $id = $this->getCategoryIdRelated($request->id);
                    $data->relation()->sync($id);
                } else {
                    $data->relation()->sync($request->id);
                }
            }
            DB::commit();
            return BannerTransformer::detail($data);
        } catch (\Exception $e) {
            DB::rollback();
            foreach ($delete_path as $dp) {
                Storage::delete($dp);
            }
            throw new \Exception($e->getMessage());
        }
    }

    public function update(CreateRequest $request, $id)
    {
        DB::beginTransaction();
        $delete_path = [];
        $old_path = [];
        try {
            $data = Banner::with('webBanner', 'mobileBanner')->where('id', $id)->firstOrFail();
            $data->update([
                'name' => $request->name,
                'description' => $request->description,
                'relation_to' => $request->type,
                'redirect_url' => $request->input('url', NULL),
                'status' => $request->filled('status') ? $request->status : 'draft'
            ]);

            $old_path = [
                $data->webBanner === NULL ?: $data->webBanner->url,
                $data->mobileBanner === NULL ?: $data->mobileBanner->url,
            ];

            $img_req = [
                str_replace(url('/') . '/', '', $request->banner_web),
                str_replace(url('/') . '/', '', $request->banner_mobile)
            ];

            if ($old_path[0] !== $img_req[0]) {
                $web_banner = imageUpload($this->folder_upload, $request->banner_web);
                $data->webBanner()->update([
                    'relation_to' => 'web_banner',
                    'url' => $web_banner
                ]);
                $delete_path[] = str_replace('storage', 'public', $web_banner);
            }

            if ($old_path[1] !== $img_req[1]) {
                $mobile_banner = imageUpload($this->folder_upload, $request->banner_mobile);
                $data->mobileBanner()->update([
                    'relation_to' => 'mobile_banner',
                    'url' => $mobile_banner
                ]);
                $delete_path[] = str_replace('storage', 'public', $mobile_banner);
            }

            if ($request->type !== 'redirect') {
                if ($request->type === 'category') {
                    $id = $this->getCategoryIdRelated($request->id);
                    $data->relation()->sync($id);
                } else {
                    $data->relation()->sync($request->id);
                }
            }

            DB::commit();
            foreach ($old_path as $op) {
                Storage::delete(str_replace('storage', 'public', $op));
            }
            return BannerTransformer::detail($data->fresh());
        } catch (\Exception $e) {
            DB::rollback();
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
            $data = Image::where('id', $id)->firstOrFail();
            $data->relation()->detach();
            $data->delete();
            DB::commit();

            $data->webBanner === NULL ?: Storage::delete(str_replace('storage', 'public', $data->webBanner->url));
            $data->mobileBanner === NULL ?: Storage::delete(str_replace('storage', 'public', $data->mobileBanner->url));

            return BannerTransformer::delete($data);
        } catch (\Exception $e) {
            DB::rollback();
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
}
