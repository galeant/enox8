<?php

namespace App\Http\Response\Dashboard;

use Carbon\Carbon;

class ComplaintTransformer
{


    public static function general($message, $response = NULL)
    {
        $res = [
            'status_id' => $response->status_id,
            'status_name' => $response->status->name
        ];
        return response()->json([
            'code' => 200,
            'message' => $message,
            'result' => $res
        ], 200);
    }

    public static function list($response)
    {
        if ($response instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $res = $response->getCollection()->transform(function ($val) {
                return self::reformer($val);
            });
            $data = [
                'data' => $res,
                'current_page' => $response->currentPage(),
                'next_page_url' => $response->nextPageUrl(),
                'prev_page_url' => $response->previousPageUrl(),
                'total' => $response->total(),
                'total_page' => $response->lastPage()
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

    public static function detail($response, $message = 'Get detail success')
    {
        $res = self::reformer($response);
        $res['user']['id'] = $response->transaction->user->id;
        $res['user']['email'] = $response->transaction->user->email;
        $res['user']['fullname'] = $response->transaction->user->detail->fullname;
        $res['user']['phone'] =  $response->transaction->user->detail->phone;
        $res['user']['avatar'] = ($response->transaction->user->detail->avatar != NULL) ? asset($response->transaction->user->detail->avatar) : NULL;
        return response()->json([
            'code' => 200,
            'message' => $message,
            'result' => $res
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
            'product' => [
                'id' => $val->transactionDetail->product_id,
                'name' => $val->transactionDetail->product_name,
                'slug' => $val->transactionDetail->product_slug,
                'qty' => $val->qty
            ],
            'type' => [
                'id' => $val->transactionDetail->type_id,
                'name' => $val->transactionDetail->type_name,
                'image' => isset($val->transactionDetail->productType->image) ? asset($val->transactionDetail->productType->image) : NULL
            ],
            'evidence' => [],
            'user_return_evidence' => $val->user_return_evidence !== NULL ? asset($val->user_return_evidence) : NULL,
            'store_evidence' => $val->store_evidence !== NULL ? asset($val->store_evidence) : NULL
        ];

        if (count($val->complaint_evidence) > 0) {
            foreach ($val->complaint_evidence as $evi) {
                $return['evidence'][] = asset($evi);
            }
        }
        return $return;
    }
}
