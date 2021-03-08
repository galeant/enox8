<?php

namespace App\Http\Response\Client;

class AuthTransformer
{

    public static function general($message, $response = NULL)
    {
        return response()->json([
            'code' => 200,
            'message' => $message,
            'result' => $response
        ], 200);
    }

    public static function register($response)
    {
        $return = [
            'email' => $response->email,
            'firstname' => $response->detail->firstname,
            'lastname' => $response->detail->lastname,
            'phone' => $response->detail->phone,
            'subscribe' => $response->detail->subscribe,
            'avatar' => $response->avatar
        ];
        return response()->json([
            'code' => 200,
            'message' => 'Register success',
            'result' => $return
        ], 200);
    }

    public static function profile($response, $token = false)
    {
        $return = [
            // 'id' => $response->id,
            'email' => $response->email,
            'firstname' => $response->detail->firstname,
            'lastname' => $response->detail->lastname,
            'phone' => $response->detail->phone,
            'subscribe' => $response->detail->subscribe,
            'avatar' => isset($response->detail->avatar) ? asset($response->detail->avatar) : '',
            'gender' => $response->detail->gender,
            'birthdate' => $response->detail->birthdate
        ];
        if ($token) {
            $return['id'] = $response->id;
        }
        return response()->json([
            'code' => 200,
            'message' => 'Get profile susccess',
            'result' => $return
        ], 200);
    }
}
