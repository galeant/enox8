<?php

namespace App\Http\Response\Dashboard;

class AuthTransformer{

    public static function general($message,$response = NULL){
        return response()->json([
            'code' => 200,
            'message' => $message,
            'result' => $response
        ],200);
    }
}