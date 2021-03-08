<?php

namespace App\Http\Response\Client;

class CourierTransformer
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
                })->groupBy('group_name'),
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
                'data' => $response->groupBy('group_name')
            ];
        }
        return response()->json([
            'code' => 200,
            'message' => 'Get list success',
            'result' => $data
        ], 200);
    }


    private static function reformer($response)
    {
        $result = [
            'id' => $response->id,
            'name' => $response->name,
            'type' => $response->type,
            'price' => $response->price,
            'group_name' => $response->group_name
        ];
        return $result;
    }
}
