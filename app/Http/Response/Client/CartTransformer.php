<?php

namespace App\Http\Response\Client;

class CartTransformer
{

    public static function list($response)
    {
        if ($response instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            // self::reformer($response->getCollection());
            // $data = [
            //     'data' => $response->items(),
            //     'current_page' => $response->currentPage(),
            //     'next_page_url' => $response->nextPageUrl(),
            //     'prev_page_url' => $response->previousPageUrl(),
            //     'total' => $response->total()
            // ];
        } else {
            // // USING THIS FOR MULTI STORE
            // $grouping = $response->groupBy('store_name');
            // $data = [];
            // foreach ($grouping as $gr) {
            //     $gp = [
            //         'store_id' => $gr[0]->store_id,
            //         'store_name' => $gr[0]->store_name,
            //         'store_slug' => $gr[0]->store_slug
            //     ];
            //     self::reformer($gr);
            //     // $gr->transform(function ($value) {
            //     //     $return = [
            //     //         'cart_id' => $value->id,
            //     //         'product_name' => $value->product->name,
            //     //         'product_slug' => $value->product->slug,
            //     //         'product_image' => $value->product->main_image,
            //     //         'type_id' => $value->type_id,
            //     //         'type_name' => $value->type->name,
            //     //         'type_image' => isset($value->type->image) ? asset($value->type->image) : NULL,
            //     //         'qty' => $value->qty,
            //     //         'total_price' => $value->total_price,
            //     //         'discount_price' => $value->discount_price,
            //     //         'note' => $value->note
            //     //     ];
            //     //     return $return;
            //     // });
            //     $gp['item'] = $gr;
            //     $data[] = $gp;
            // }

            // USING THI FOR SINGLE STORE
            self::reformer($response);
            $data = $response;
            // $data = [
            //     'payment_price' =>  $response->pluck('discount_price')->sum(),
            //     'item' => $response
            // ];
            $data = [
                'data' => $data
            ];
        }

        return response()->json([
            'code' => 200,
            'message' => 'Get list success',
            'result' => $data
        ], 200);
    }

    public static function shipment($cart)
    {
        self::reformer($cart);
        $return = [
            'summary' => [
                'total_payment' => $cart->pluck('discount_price')->sum(),
                'total_qty' => $cart->pluck('qty')->sum()
            ],
            'item' => $cart
        ];

        return response()->json([
            'code' => 200,
            'message' => 'Get list success',
            'result' => $return
        ], 200);
    }


    private static function reformer($response)
    {
        $response->transform(function ($value) {
            $price = $value->type->price;
            if ($value->type->recalculate_discount !== NULL) {
                $price = $value->type->recalculate_discount;
            }
            $return = [
                'cart_id' => $value->id,
                'product_id' => $value->product->id,
                'product_name' => $value->product->name,
                'product_slug' => $value->product->slug,
                'product_image' => isset($value->product->main_image) ? asset($value->product->main_image) : '',
                'type_id' => $value->type_id,
                'type_name' => $value->type->name,
                'type_image' => isset($value->type->image) ? asset($value->type->image) : '',
                'qty' => $value->qty,
                'total_price' => $value->qty * $value->type->price,
                'discount_price' => $value->qty * $price,
                'note' => (string) $value->note
            ];
            return $return;
        });
    }
}
