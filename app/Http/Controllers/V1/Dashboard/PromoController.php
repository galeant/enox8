<?php

namespace App\Http\Controllers\V1\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Promo\CreateRequest;
use App\Models\V1\Promo;
use App\Http\Response\Dashboard\PromoTransformer;
use DB;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Storage;

class PromoController extends Controller
{
    private $folder_path = 'public/promo/';

    public function getData(Request $request, $id = NULL)
    {
        try {
            $per_page = $request->input('per_page', 10);
            $page = $request->input('page', 1);
            $order_by = $request->input('order_by', 'name');
            $order = $request->input('order', 'asc');
            $data = Promo::with('discount', 'voucher');
            if ($id !== NULL) {
                $data = $data->where('id', $id)->firstOrFail();
                return PromoTransformer::detail($data);
            }
            $data = $data->orderBy($order_by, $order)->paginate($per_page);
            return PromoTransformer::list($data);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }


    public function create(CreateRequest $request)
    {
        $uploaded_img = NULL;
        DB::beginTransaction();
        try {
            $user = auth()->user();
            if (strpos($request->image, 'http://') !== false || strpos($request->image, 'https://') !== false) {
                $img = imageUpload($this->folder_path, @file_get_contents($request->image));
            } else {
                $img = imageUpload($this->folder_path, $request->image);
            }
            $uploaded_img = str_replace('storage', 'public', $img);
            $promo = Promo::create([
                'slug' => str_slug($request->name . '-' . $user->store_id),
                'name' => $request->name,
                'description' => $request->description,
                'start_date' => Carbon::parse($request->start_date)->format('Y-m-d'),
                'end_date' => Carbon::parse($request->end_date)->format('Y-m-d'),
                'status' => $request->input('status', 'draft')
            ]);

            $promo->image()->create([
                'url' => $img,
                'relation_to' => 'promo'
            ]);

            $discount_id = [];
            foreach ($request->discount as $dc) {
                $discount_id[$dc] = [
                    'relation_type' => 'discount'
                ];
            }
            $promo->discount()->sync($discount_id);

            $voucher_id = [];
            foreach ($request->voucher as $vc) {
                $voucher_id[$vc] = [
                    'relation_type' => 'voucher'
                ];
            }

            $promo->voucher()->sync($voucher_id);
            DB::commit();
            return PromoTransformer::detail($promo);
        } catch (\Exception $e) {
            !isset($uploaded_img) ?: Storage::delete($uploaded_img);
            DB::rollback();
            throw new Exception($e->getMessage());
        }
    }

    public function update(CreateRequest $request, $id)
    {
        $uploaded_img = NULL;
        DB::beginTransaction();
        try {
            $promo = Promo::where('id', $id)->firstOrFail();
            $promo->update([
                'name' => $request->name,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => $request->input('status', 'draft')
            ]);

            $oImg = $promo->image->url;
            $img_req = str_replace(url('/') . '/', '', $request->image);
            if (strpos($request->image, url('/')) !== false) {
                if ($oImg !== $img_req) {
                    $nImg = imageUpload($this->folder_path, @file_get_contents($request->image));
                    $uploaded_img = str_replace('storage', 'public', $nImg);
                    $promo->image()->update([
                        'url' => $nImg
                    ]);
                }
            } else {
                $nImg = imageUpload($this->folder_path, $request->image);
                $promo->image()->update([
                    'url' => $nImg
                ]);
                $uploaded_img = str_replace('storage', 'public', $nImg);
            }

            $discount_id = [];
            foreach ($request->discount as $dc) {
                $discount_id[$dc] = [
                    'relation_type' => 'discount'
                ];
            }
            $promo->discount()->sync($discount_id);

            $voucher_id = [];
            foreach ($request->voucher as $vc) {
                $voucher_id[$vc] = [
                    'relation_type' => 'voucher'
                ];
            }
            $promo->voucher()->sync($voucher_id);
            DB::commit();
            if (isset($nImg) && $img_req !== $oImg) {
                Storage::delete(str_replace('storage', 'public', $oImg));
            }
            return PromoTransformer::detail($promo->fresh());
        } catch (\Exception $e) {
            DB::rollback();
            Storage::delete($uploaded_img);
            throw new Exception($e->getMessage());
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $data = Promo::where('id', $id)->firstOrFail();
            $data->discount()->detach();
            $data->voucher()->detach();
            Storage::delete(str_replace('storage', 'public', $data->image->url));
            $data->image->delete();
            $data->delete();
            DB::commit();
            return PromoTransformer::delete($data);
        } catch (\Exception $e) {
            DB::rollback();
            throw new Exception($e->getMessage());
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
