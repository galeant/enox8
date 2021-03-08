<?php

namespace App\Http\Response\Dashboard;

class BankTransformer{

    public static function list($response){
        if ($response instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            self::reformer($response->getCollection());
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
                    'name' => $value->name.'-'.$value->account_number
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
        if(strpos($response->image, 'http://') === false && strpos($response->image, 'https://') === false){
            $response->image = asset($response->image);
        }
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

    private static function reformer($response){
        $response->transform(function($value){
            if(strpos($value->image, 'http://') === false && strpos($value->image, 'https://') === false){
                $value->image = asset($value->image);
            }
            return $value;
        });
    }
}