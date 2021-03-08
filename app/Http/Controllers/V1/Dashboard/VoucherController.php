<?php

namespace App\Http\Controllers\V1\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use App\Http\Controllers\Controller;
use App\Http\Response\Dashboard\VoucherTransformer;
use App\Http\Requests\Dashboard\Voucher\CreateRequest;
use App\Http\Requests\Dashboard\Voucher\UpdateRequest;
use App\Http\Requests\Dashboard\Voucher\ChangeStatusRequest;

use DB;
use App\Models\V1\Voucher;
use App\Models\V1\Category;

class VoucherController extends Controller
{
    private $folder_path = 'public/voucher/';

    public function getData(Request $request, $id = NULL)
    {
        try {
            $data = new Voucher;
            if ($id !== NULL) {
                $data = $data->where('id', $id)->firstOrFail();
                return VoucherTransformer::detail($data);
            } else if ($request->filled('all')) {
                $data = $data->get();
                return VoucherTransformer::list($data);
            } else {
                $data = $data->orderBy('created_at', 'desc')->paginate(10);
                $data->appends($request->all());
                return VoucherTransformer::list($data);
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function create(CreateRequest $request)
    {
        $uploaded_img = NULL;
        DB::beginTransaction();
        try {
            $minimum_payment = NULL;
            if ($request->filled('minimum_payment') && $request->minimum_payment !== 0) {
                $minimum_payment = $request->minimum_payment;
            }

            $limit_per_user = NULL;
            if ($request->filled('limit_per_user') && $request->limit_per_user !== 0) {
                $limit_per_user = $request->limit_per_user;
            }

            $limit_per_user_per_day = NULL;
            if ($request->filled('limit_per_user_per_day') && $request->limit_per_user_per_day !== 0) {
                $limit_per_user_per_day = $request->limit_per_user_per_day;
            }

            $max_discount = NULL;
            if ($request->filled('max_discount') && $request->max_discount !== 0) {
                $max_discount = $request->max_discount;
            }

            $data = Voucher::create([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->input('description', NULL),
                'effective_start_date' => $request->effective_start_date,
                'effective_end_date' => $request->effective_end_date,
                'value' => $request->value,
                'unit' => $request->unit,
                'minimum_payment' => $minimum_payment,
                'limit_per_user' => $limit_per_user,
                'limit_per_user_per_day' => $limit_per_user_per_day,
                'status' => isset($request->status) ?: 'draft',
                'max_discount' => $max_discount
            ]);

            if ($request->filled('image')) {
                $img = imageUpload($this->folder_path, $request->image);
                $data->image()->create([
                    'url' => $img,
                    'relation_to' => 'voucher'
                ]);
                $uploaded_img = str_replace('storage', 'public', $img);
            }
            $product_id = [];
            foreach ($request->product as $rp) {
                $product_id[$rp] = [
                    'type' => 'product'
                ];
            }
            $data->product()->sync($product_id);

            $category_id = [];
            $category_id_related = $this->getCategoryIdRelated($request->category);
            foreach ($category_id_related as $cr) {
                $category_id[$cr] = [
                    'type' => 'category'
                ];
            }
            $data->category()->sync($category_id);
            DB::commit();
            return VoucherTransformer::detail($data);
        } catch (\Exception $e) {
            DB::rollback();
            !isset($uploaded_img) ?: Storage::delete($uploaded_img);
            throw new \Exception($e->getMessage());
        }
    }

    public function update(UpdateRequest $request, $id)
    {
        $uploaded_img = NULL;
        DB::beginTransaction();
        try {
            $data = Voucher::where('id', $id)->firstOrFail();
            $update = [
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description,
                'effective_start_date' => $request->effective_start_date,
                'effective_end_date' => $request->effective_end_date,
                'value' => $request->value,
                'unit' => $request->unit
            ];

            if ($request->filled('image')) {
                $oImg = $data->image->url;
                $img_req = str_replace(url('/') . '/', '', $request->image);
                if (strpos($request->image, url('/')) !== false) {
                    if ($oImg !== $img_req) {
                        $nImg = imageUpload($this->folder_path, @file_get_contents($request->image));
                        $uploaded_img = str_replace('storage', 'public', $nImg);
                        $data->image()->update([
                            'url' => $nImg
                        ]);
                    }
                } else {
                    $nImg = imageUpload($this->folder_path, $request->image);
                    $data->image()->update([
                        'url' => $nImg
                    ]);
                    $uploaded_img = str_replace('storage', 'public', $nImg);
                }
            }

            if ($request->filled('minimum_payment')) {
                $update['minimum_payment'] = $request->minimum_payment;
            }

            if ($request->filled('limit_per_user')) {
                $update['limit_per_user'] = $request->limit_per_user;
            }

            if ($request->filled('limit_per_user_per_day')) {
                $update['limit_per_user_per_day'] = $request->limit_per_user_per_day;
            }

            if ($request->filled('status')) {
                $update['status'] = $request->status;
            }

            if ($request->filled('max_discount')) {
                $update['max_discount'] = $request->max_discount;
            }

            $data->update($update);
            $product_id = [];
            foreach ($request->product as $rp) {
                $product_id[$rp] = [
                    'type' => 'product'
                ];
            }
            $data->product()->sync($product_id);

            $category_id = [];
            $category_id_related = $this->getCategoryIdRelated($request->category);
            foreach ($category_id_related as $cr) {
                $category_id[$cr] = [
                    'type' => 'category'
                ];
            }
            $data->category()->sync($category_id);
            DB::commit();
            if (isset($nImg) && isset($img_req) && isset($oImg) && $img_req !== $oImg) {
                Storage::delete(str_replace('storage', 'public', $oImg));
            }
            return VoucherTransformer::detail($data->fresh());
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $data = Voucher::where('id', $id)->with('category', 'product')->firstOrFail();
            $data->product()->detach();
            $data->category()->detach();
            $data->delete();
            DB::commit();
            return VoucherTransformer::detail($data);
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
    }

    public function changeStatus(ChangeStatusRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = Voucher::whereIn('id', $request->id)->get();
            foreach ($data as $dt) {
                $dt->update([
                    'status' => $request->status
                ]);
            }
            DB::commit();
            return VoucherTransformer::list($data->fresh());
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
