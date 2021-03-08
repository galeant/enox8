<?php

namespace App\Http\Response\Dashboard;

class BlogAttributeTransformer{

    public static function list($response){
        if ($response instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $data = [
                'data' => $response->items(),
                'current_page' => $response->currentPage(),
                'next_page_url' => $response->nextPageUrl(),
                'prev_page_url' => $response->previousPageUrl(),
                'total' => $response->total(),
                'total_page' => $response->lastPage()
            ];
        }else{
            $response->transform(function($value){
                return [
                    'id' => $value->id,
                    'name' => $value->name
                ];
            });
            $data = [
                'data' => $response
            ];
        }
        return response()->json([
            'code' => 200,
            'message' => 'Get list success',
            'result' => $data
        ],200);
    }

    public static function detail($response){
        return response()->json([
            'code' => 200,
            'message' => 'Get detail success',
            'result' => $response
        ],200);
    }

    public static function delete($response){
        return response()->json([
            'code' => 200,
            'message' => 'Delete success',
            'result' => $response
        ],200);
    }
}