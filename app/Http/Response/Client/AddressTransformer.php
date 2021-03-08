<?php

namespace App\Http\Response\Client;

use Carbon\Carbon;

class AddressTransformer
{
    public static function getList($response)
    {
        if ($response instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            self::reformer($response->getCollection());
            $data = [
                'data' => $response->items(),
                'current_page' => $response->currentPage(),
                'next_page_url' => $response->nextPageUrl(),
                'prev_page_url' => $response->previousPageUrl(),
                'total' => $response->total()
            ];
        } else {
            self::reformer($response);
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

    public static function getDetail($response)
    {
        return response()->json([
            'code' => 200,
            'message' => 'Get detail success',
            'result' => $response
        ], 200);
    }

    private static function reformer($response)
    {
        $response->transform(function ($value) {
            $return  = [
                'id' => $value->id,
                'address' => $value->address,
                'country_id' => $value->country_id,
                'country_name' => isset($value->country) ? $value->country->name : '',
                'province_id' => $value->province_id,
                'province_name' => isset($value->province) ? $value->province->name : '',
                'regency_id' => $value->regency_id,
                'regency_name' => isset($value->regency) ? $value->regency->name : '',
                'district_id' => $value->district_id,
                'district_name' => isset($value->district) ? $value->district->name : '',
                'village_id' => $value->village_id,
                'village_name' => isset($value->village) ? $value->village->name : '',
                'alias' => $value->alias,
                'recipient_name' => $value->recipient_name,
                'phone' => $value->phone,
                'postal_code' => $value->postal_code,
                'latitude' => $value->latitude,
                'longitude' => $value->longitude,
                'main_address' => $value->main_address
            ];
            return $return;
        });
    }
}
