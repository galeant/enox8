<?php

namespace App\Http\Response\Client;
use Carbon\Carbon;

class CommentTransformer{
    public static function getList($response){
        if ($response instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            self::reformer($response->getCollection());
            $data = [
                'data' => $response->items(),
                'current_page' => $response->currentPage(),
                'next_page_url' => $response->nextPageUrl(),
                'prev_page_url' => $response->previousPageUrl(),
                'total' => $response->total()
            ];
        }else{
            self::reformer($response);
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

    public static function getDetail($response){
        return response()->json([   
            'code' => 200,
            'message' => 'Get detail success',
            'result' => $response
        ],200);
    }

    private static function reformer($response){
        $response->transform(function ($value) {
            if($value->deleted_at !== NULL){
                $return = [
                    'id' => $value->id,
                    'username' => $value->username,
                    'comment' => 'Komentar sudah di hapus',
                    'reply' => []
                ];
            }else{
                $return = [
                    'id' => $value->id,
                    'username' => $value->username,
                    'comment' => $value->content,
                    'reply' => []
                ];
            }
            foreach($value->children as $ch){
                $return['reply'][] = [
                    'username' => $ch->username,
                    'comment' => $ch->content
                ];
            }
            return $return;
        });
    }
}