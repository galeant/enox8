<?php

namespace App\Http\Response\Dashboard;

class PermissionTransformer{

    public static function list($response){
        $response = self::reformer($response);
        $data = [
            'data' => $response
        ];
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

    private static function reformer($response){
        $return = [];
        foreach($response as $rsp){
            $description = $rsp->description;
            if($description->tier_0 === 'dashboard' && $description->tier_2){
                if(isset($description->tier_3) && 
                    (
                        $description->tier_3 === 'getData' ||
                        $description->tier_3 === 'create' ||
                        $description->tier_3 === 'update' ||
                        $description->tier_3 === 'delete'
                    )
                
                ){
                    $return[$description->tier_1][$description->tier_2][] = [
                        'id' => $rsp->id,
                        'access' => $description->tier_3
                    ];
                }else if(isset($description->tier_2) && !isset($description->tier_3) && 
                    (
                        $description->tier_2 === 'getData' ||
                        $description->tier_2 === 'create' ||
                        $description->tier_2 === 'update' ||
                        $description->tier_2 === 'delete'
                    )
                ){
                    $return[$description->tier_1][] = [
                        'id' => $rsp->id,
                        'access' => $description->tier_2
                    ];
                }
                // else{
                //     $return[$description->tier_0][$description->tier_1][] = [
                //         'id' => $rsp->id,
                //         'access' => $description->tier_1
                //     ];
                // }
            }
        }
        return $return;
    }
}