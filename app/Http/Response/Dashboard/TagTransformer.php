<?php

namespace App\Http\Response\Dashboard;

class TagTransformer
{

    public static function list($response)
    {
        if ($response instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $response->getCollection()->transform(function ($value) {
                return self::reformer($value);
            });
            $data = [
                'data' => $response->items(),
                'current_page' => $response->currentPage(),
                'next_page_url' => $response->nextPageUrl(),
                'prev_page_url' => $response->previousPageUrl(),
                'total' => $response->total(),
                'total_page' => $response->lastPage()
            ];
        } else {
            $response->transform(function ($value) {
                return self::reformer($value);
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
            'message' => 'Get detail success',
            'result' => self::reformer($value)
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
        return [
            'id' => $value->id,
            'slug' => $value->slug,
            'name' => $value->name
        ];
    }
}
