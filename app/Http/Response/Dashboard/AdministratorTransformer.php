<?php

namespace App\Http\Response\Dashboard;

class AdministratorTransformer{

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

    public static function detail($response){
        $avatar = NULL;
        if($response->detail->avatar !== NULL){
            $avatar = asset($response->detail->avatar);
        }
        $return = [
            'id' => $response->id,
            'email' => $response->email,
            'firstname' => $response->detail->firstname,
            'lastname' => $response->detail->lastname,
            'phone' => $response->detail->phone,
            'avatar' => $avatar,
            'role_id' => $response->role_id,
            'role_name' => $response->role->name,
            'status' => $response->status
        ];
        return response()->json([
            'code' => 200,
            'message' => 'Get data success',
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
        $response->transform(function ($value) {
            $avatar = NULL;
            if($value->detail->avatar !== NULL){
                $avatar = asset($value->detail->avatar);
            }
            $return = [
                'id' => $value->id,
                'firstname' => $value->detail->firstname,
                'lastname' => $value->detail->lastname,
                'email' => $value->email,
                'phone' => $value->detail->phone,
                'role_id' => $value->role_id,
                'role_name' => $value->role->name,
                'avatar' => $avatar,
                'status' => $value->status
            ];
            return $return;
        });
    }
}