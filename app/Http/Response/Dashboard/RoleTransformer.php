<?php

namespace App\Http\Response\Dashboard;

class RoleTransformer{

    public static function list($response){
        if ($response instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            // self::reformer($response);
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
        $return= [
            'id' => $response->id,
            'name' => $response->name,
            'description' => $response->description,
            'permisssion' => []
        ];
        foreach($response->permission as $p){
            if($p->description->tier_0 === 'dashboard'){
                $return['permission'][] = [
                    'id' => $p->id,
                    'access' => $p->access,
                    'description' => $p->description
                ];
            }
        }
        return response()->json([
            'code' => 200,
            'message' => 'Get detail success',
            'result' => $return
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
        $response->getCollection()->transform(function ($value) {
            $return= [
                'id' => $value->id,
                'name' => $value->name,
                'description' => $value->description,
                'permisssion' => []
            ];
            foreach($value->permission as $p){
                if($p->description->tier_0 === 'dashboard'){
                    $return['permission'][] = [
                        'id' => $p->id,
                        'access' => $p->access,
                        'description' => $p->description
                    ];
                }
            }

            return $return;
        });
    }
}