<?php

namespace App\Http\Response\Client;

class WishlistTransformer
{

    public static function general($message)
    {
        return response()->json([
            'code' => 200,
            'message' => $message,
            'result' => NULL
        ], 200);
    }

    public static function list($response)
    {
        if ($response instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            self::reformer($response->getCollection());
            $data = [
                'data' => $response->items(),
                'current_page' => $response->currentPage(),
                'next_page_url' => $response->nextPageUrl(),
                'prev_page_url' => $response->previousPageUrl(),
                'total' => $response->total()
            ];
        } else {
            self::reformer($response);
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

    private static function reformer($response)
    {
        $response->transform(function ($value) {
            $result = [
                'product' => [
                    'id' => $value->product->id,
                    'slug' => $value->product->slug,
                    'name' => $value->product->name,
                    'main_image' => $value->product->main_image,
                    'rating' => $value->rating,
                ],
                'type' => [
                    'id' => $value->id,
                    'name' => $value->name,
                    'image' => isset($value->image) ? asset($value->image) : '',
                    'price' => [
                        'idr' => number_format($value->price, 2, ',', '.'),
                        'usd' => number_format($value->price, 2, '.', ',')
                    ],
                    'discount_price' => [
                        'idr' => isset($value->discount_price) ? number_format($value->discount_price, 2, ',', '.') : '',
                        'usd' => isset($value->discount_price) ? number_format($value->discount_price, 2, '.', ',') : '',
                    ],
                    'discount_precentage' => '-0.00'
                ]
            ];
            if ($value->recalculate_discount !== NULL) {
                $result['type']['discount_price']['idr'] = number_format($value->recalculate_discount, 2, ',', '.');
                $result['type']['discount_price']['usd'] = number_format($value->recalculate_discount, 2, '.', ',');
                $result['type']['discount_precentage'] = '-' . round(100 - ($value->recalculate_discount / ($value->price / 100)));
            }
            return $result;
        });
    }
}
