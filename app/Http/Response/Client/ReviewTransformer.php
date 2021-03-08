<?php

namespace App\Http\Response\Client;

use Carbon\Carbon;

class ReviewTransformer
{
    public static function list($response, $auth = false)
    {
        if ($response instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $response->getCollection()->transform(function ($v) use ($auth) {
                return self::reformer($v, $auth);
            });
            $data = [
                'data' => $response->items(),
                'current_page' => $response->currentPage(),
                'next_page_url' => $response->nextPageUrl(),
                'prev_page_url' => $response->previousPageUrl(),
                'total' => $response->total()
            ];
        } else {
            $response->transform(function ($v) use ($auth) {
                return self::reformer($v, $auth);
            });
            $data = [
                'data' => $response
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
        return response()->json([
            'code' => 200,
            'message' => 'Review success added',
            'result' => self::reformer($response)
        ], 200);
    }

    private static function reformer($response, $auth = NULL)
    {
        $image = [];
        if ($response->image->count() > 0) {
            $image = $response->image->transform(function ($q) {
                return [
                    'url' => asset($q->url)
                ];
            });
        }
        $return = [
            'id' => $response->id,
            'review' => $response->review,
            'rating' => $response->rating,
            'product_id' => $response->product_id,
            'product_nme' => $response->product->name,
            'type_id' => $response->type_id,
            'type_name' => $response->product_type->name,
            'image' => $image
        ];
        if ($auth) {
            $return['transaction_id'] = $response->transaction_id;
            $return['transaction'] = [
                'info' => $response->transaction,
                'detail' => $response->transaction->detail
            ];
        }
        return $return;
    }
}
