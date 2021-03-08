<?php

namespace App\Http\Response\Client;

class CountryTransformer
{

    public static function general($message, $response = NULL)
    {
        return response()->json([
            'code' => 200,
            'message' => $message,
            'result' => $response
        ], 200);
    }

    public static function list($response)
    {
        if ($response instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $data = [
                'data' => $response->getCollection()->tranform(function ($v) {
                    return self::reformer($v);
                }),
                'current_page' => $response->currentPage(),
                'next_page_url' => $response->nextPageUrl(),
                'prev_page_url' => $response->previousPageUrl(),
                'total' => $response->total()
            ];
        } else {
            $response->transform(function ($v) {
                return self::reformer($v);
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

    // public static function detail($response){
    //     $result = [
    //         'id' => $response->id,
    //         'banner' => $response->baner,
    //         'name' => $response->name,
    //         'description' => $response->description,
    //         'value' => $response->value,
    //         'unit' => $response->unit,
    //         'product' => []
    //     ];
    //     foreach($response->product as $product){
    //         $discount_price = NULL;
    //         if($product->defaultType->price !== $product->display_price){
    //             $discount_price = $product->display_price;
    //         }
    //         $ar = [
    //             'id' => $product->id,
    //             'slug' => $product->slug,
    //             'name' => $product->name,
    //             'main_image' => $product->main_image,
    //             'description' => $product->description,
    //             'meta_title' => $product->meta_title,
    //             'met_description' => $product->meta_description,
    //             'rating' => $product->rating,
    //             'price' => $product->defaultType->price,
    //             'discount_price' => $discount_price,
    //             'category' => []
    //         ];

    //         foreach($product->category as $category){
    //             $ar['category'] = [
    //                 'name' => $category->name,
    //                 'slug' => $category->slug
    //             ];
    //         }
    //         $result['product'][] = $ar;
    //     }
    //     return response()->json([
    //         'code' => 200,
    //         'message' => 'Get detail success',
    //         'result' => $result
    //     ],200);
    // }

    private static function reformer($response)
    {
        $result = [
            'id' => $response->id,
            'code' => $response->code,
            'name' => $response->name
        ];
        return $result;
    }
}
