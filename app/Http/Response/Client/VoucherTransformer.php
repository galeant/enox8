<?php

namespace App\Http\Response\Client;

class VoucherTransformer
{

    public static function list($response)
    {
        if ($response instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $rs = $response->items()->filter(function ($v, $key) {
                if ($v->limit_per_user > $v->usage_all && $v->usage_today < $v->limit_per_user_per_day) {
                    if ($v->usage_today < $v->limit_per_user_per_day) {
                        return $v;
                    }
                }
            })->transform(function ($v) {
                return self::reformer($v);
            });
            $data = [
                'data' => $rs,
                'current_page' => $response->currentPage(),
                'next_page_url' => $response->nextPageUrl(),
                'prev_page_url' => $response->previousPageUrl(),
                'total' => $response->total()
            ];
        } else {
            $rs = $response->filter(function ($v, $key) {
                if ($v->limit_per_user > $v->usage_all && $v->usage_today < $v->limit_per_user_per_day) {
                    if ($v->usage_today < $v->limit_per_user_per_day) {
                        return $v;
                    }
                }
            })->transform(function ($v) {
                return self::reformer($v);
            });

            $data = [
                'data' => $rs
            ];
        }

        return response()->json([
            'code' => 200,
            'message' => 'Get list success',
            'result' => $data
        ], 200);
    }

    public static function detail($response)
    {
        $image = '';
        if ($response->image !== NULL) {
            $image = asset($response->image);
        }
        $return = [
            'id' => $response->id,
            'name' => $response->name,
            'code' => $response->code,
            'description' => $response->description,
            'image' => $image,
            'limit_per_user' => $response->limit_per_user,
            'limit_per_user_per_day' => $response->limit_per_user_per_day,
            'effective_start_date' => $response->effective_start_date,
            'effective_end_date' => $response->effective_end_date,
            'minimum_payment' => $response->minimum_payment,
            'value' => $response->value,
            'unit' => $response->unit,
            'status' => $response->status,
            'max_discount' => $response->max_discount
        ];
        return response()->json([
            'code' => 200,
            'message' => 'Get detail success',
            'result' => $return
        ], 200);
    }

    public static function delete($response)
    {
        return response()->json([
            'code' => 200,
            'message' => 'Delete success',
            'result' => $response
        ], 200);
    }

    private static function reformer($value)
    {
        $return = [
            'id' => $value->id,
            'name' => $value->name,
            'code' => $value->code,
            'description' => $value->description,
            'image' => '',
            'limit_per_user' => $value->limit_per_user,
            'limit_per_user_per_day' => $value->limit_per_user_per_day,
            'effective_start_date' => $value->effective_start_date,
            'effective_end_date' => $value->effective_end_date,
            'minimum_payment' => $value->minimum_payment,
            'value' => $value->value,
            'unit' => $value->unit,
            'status' => $value->status,
            'max_discount' => $value->max_discount
        ];
        if ($value->image !== NULL) {
            $return['image'] = asset($value->image);
        }
        return $return;
    }
}
