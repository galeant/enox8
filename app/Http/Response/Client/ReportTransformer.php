<?php

namespace App\Http\Response\Client;

class ReportTransformer{

    public static function general($message,$response = NULL){
        return response()->json([
            'code' => 200,
            'message' => $message,
            'result' => $response
        ],200);
    }


    
    public static function list($response,$related = false){
        if ($response instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $data = [
                'data' => $response->items(),
                'current_page' => $response->currentPage(),
                'next_page_url' => $response->nextPageUrl(),
                'prev_page_url' => $response->previousPageUrl(),
                'total' => $response->total()
            ];
        }else{
            $data = [
                'data' => $response
            ];
        }
        if($related === true){
            return $data['data'];
        }
        return response()->json([
            'code' => 200,
            'message' => 'Get list success',
            'result' => $data
        ],200);
    }

    public static function getDetail($response){
        
        return response()->json([
            'code' => 200,
            'message' => 'Get detail success',
            'result' => $response
        ],200);
    }

    

    private static function reformer($response){
        $response->transform(function ($value) {
            
        });
    }
}