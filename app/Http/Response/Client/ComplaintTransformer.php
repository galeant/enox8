<?php

namespace App\Http\Response\Client;

use Carbon\Carbon;

class ComplaintTransformer
{


    public static function general($message, $response = NULL)
    {
        return response()->json([
            'code' => 200,
            'message' => $message,
            'result' => $response
        ], 200);
    }

    public static function statusList($response)
    {
        return response()->json([
            'code' => 200,
            'message' => 'Get complaint status list success',
            'result' => $response->transform(function ($v) {
                return [
                    'id' => $v->id,
                    'name' => $v->name
                ];
            })
        ]);
    }

    public static function list($response)
    {
        if ($response instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $res = $response->getCollection()->transform(function ($v) {
                return self::reformer($v);
            });
            $data = [
                'data' => $res,
                'current_page' => $response->currentPage(),
                'next_page_url' => $response->nextPageUrl(),
                'prev_page_url' => $response->previousPageUrl(),
                'total' => $response->total()
            ];
        } else {
            $data = [
                'data' => $response->transform(function ($val) {
                    return self::reformer($val);
                })
            ];
        }
        return response()->json([
            'code' => 200,
            'message' => 'Get list success',
            'result' => $data
        ], 200);
    }


    public static function detail($res, $message = 'Get detail success')
    {
        $data = self::reformer($res);
        return response()->json([
            'code' => 200,
            'message' => $message,
            'result' => $data
        ], 200);
    }

    private static function reformer($val)
    {
        $return = [
            'id' => $val->id,
            'complaint' => $val->complaint,
            // 'reply' => $val->reply_complaint,
            // 'type' => $val->type,
            'created_at' => Carbon::parse($val->created_at)->format('d M Y H:i:s'),
            'status' => $val->status,
            'transaction_id' => $val->transaction_id,
            'transaction_code' => $val->transaction->transaction_code,
            'transaction_detail_id' => $val->transaction_detail_id,
            'recipient' => [
                'name' => $val->transaction->recipient_name,
                'address' => $val->transaction->recipient_address,
                'country' => $val->transaction->recipient_country,
                'province' => $val->transaction->recipient_province,
                'regency' => $val->transaction->recipient_regency,
                'district' => $val->transaction->recipient_district,
                'phone' => $val->transaction->recipient_phone,
                'postal_code' => $val->transaction->recipient_postal_code,
                'latitude' => $val->transaction->recipient_latitude,
                'longitude' => $val->transaction->recipient_longitude
            ],
            'product' => [
                'id' => $val->transactionDetail->product_id,
                'name' => $val->transactionDetail->product_name,
                'slug' => $val->transactionDetail->product_slug,
                'qty' => $val->qty
            ],
            'type' => [
                'id' => $val->transactionDetail->type_id,
                'name' => $val->transactionDetail->type_name,
                'image' => $val->transactionDetail->productType->image !== NULL ? asset($val->transactionDetail->productType->image) : ''
            ],
            'evidence' => [],
            'user_return_evidence' => $val->user_return_evidence !== NULL ? asset($val->user_return_evidence) : '',
            'store_evidence' => $val->store_evidence !== NULL ? asset($val->store_evidence) : ''
        ];
        if (count($val->complaint_evidence) > 0) {
            foreach ($val->complaint_evidence as $evi) {
                $return['evidence'][] = asset($evi);
            }
        }
        return $return;
    }
}
