<?php

namespace App\Http\Response\Dashboard;

class CustomerTransformer
{

    public static function list($response)
    {
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

    public static function detail($response)
    {
        $avatar = NULL;
        if ($response->detail !== NULL) {
            if ($response->detail->avatar !== NULL) {
                $avatar = asset($response->detail->avatar);
            }
        }

        $notif = 'secondary';
        switch ($response->status) {
            case 'Active':
                $notif = 'success';
                break;
            case 'Activation required':
                $notif = 'warning';
                break;
            case 'Banned':
                $notif = 'danger';
                break;
        }
        // dd($response);
        $return = [
            'id' => $response->id,
            'email' => $response->email,
            'firstname' => $response->detail !== NULL ? $response->detail->firstname : NULL,
            'lastname' => $response->detail !== NULL ? $response->detail->lastname : NULL,
            'phone' => $response->detail !== NULL ? $response->detail->phone : NULL,
            'avatar' => $avatar,
            'gender' => $response->detail !== NULL ? $response->detail->gender : NULL,
            'birthdate' => $response->detail !== NULL ? $response->detail->birthdate : NULL,
            'status' => [
                'value' => $response->status,
                'notif' => $notif
            ],
            'address' =>  $response->address->transform(function ($value) {
                return [
                    'id' => $value->id,
                    'address' => $value->address,
                    'alias' => $value->alias,
                    'recepient_name' => $value->recepiean_name,
                    'phone' => $value->phone,
                    'postal_code' => $value->postal_code,
                    'latitude' => $value->latitude,
                    'longitude' => $value->longitude,
                    'country' => [
                        'id' => $value->country->id,
                        'name' => $value->country->name
                    ],
                    'province' => [
                        'id' => $value->province->id,
                        'name' => $value->province->name
                    ],
                    'regency' => [
                        'id' => $value->regency->id,
                        'name' => $value->regency->name
                    ],
                    'district' => [
                        'id' => $value->district->id,
                        'name' => $value->district->name
                    ],
                    'village' => [
                        'id' => $value->village->id,
                        'name' => $value->village->name
                    ]
                ];
            }),
            'transaction' => $response->transaction->transform(function ($value) {
                $return = [
                    'id' => $value->id,
                    'transaction_code' => $value->transaction_code,
                    'total_price' => $value->total_price,
                    'product_discount_value' => $value->product_discount_value,
                    'total_payment' => $value->total_payment,
                    'bank_account_name' => $value->bank_account_name,
                    'bank_account_number' => $value->bank_account_number,
                    'bank_name' => $value->bank_name,
                    'bank_account_type' => $value->bank_account_type,
                    'unique_code' => $value->unique_code,
                    'payment_evidence' => $value->payment_evidence,
                    'courier' => $value->courier_type,
                    'courier_price' => $value->courier_price,
                    'insurance_fee' => $value->insurance_fee,
                    'file_upload_payment_transfer' => $value->file_upload_payment_transfer,
                    'store_name' => $value->store_name,

                    'resi_number' => $value->resi_number,
                    'recipient_name' => $value->recipient_name,
                    'recipient_address' => $value->recipient_address,
                    'recipient_country' => $value->recipient_country,
                    'recipient_province' => $value->recipient_province,
                    'recipient_regency' => $value->recipient_regency,
                    'recipient_district' => $value->recipient_district,
                    'recipient_village' => $value->recipient_village,
                    'recipient_phone' => $value->recipient_phone,
                    'recipient_postal_code' => $value->recipient_postal_code,
                    'recipient_latitude' => $value->recipient_latitude,
                    'recipient_longitude' => $value->recipient_longitude,
                    'item' => [],
                    'grand_total' => 0,
                    'created_at' => $value->created_at,
                    'status' => $value->status->name
                ];
                foreach ($value->detail as $dt) {
                    $return['item'][] = [
                        'product_id' => 'id',
                        'product_name' => $dt->product_name,
                        'type_id' => $dt->product_name,
                        'type_name' => $dt->type_name,
                        'qty' => $dt->qty,
                        'total_price' => $dt->total_price,
                        'total_discount' => $dt->total_discount,
                        'total_payment' => $dt->total_payment,
                        'created_at' => $dt->created_at
                    ];
                    $return['grand_total'] = $return['grand_total'] + $dt->total_payment;
                }

                return $return;
            })
        ];
        return response()->json([
            'code' => 200,
            'message' => 'Get data success',
            'result' => $return
        ], 200);
    }

    public static function delete($response)
    {
        return response()->json([
            'code' => 200,
            'message' => 'Delete success',
            'result' => $response
        ], 200);
    }

    private static function reformer($response)
    {
        $response->transform(function ($value) {
            $avatar = NULL;
            if ($value->detail !== NULL) {
                if ($value->detail->avatar !== NULL) {
                    $avatar = asset($value->detail->avatar);
                }
            }
            $notif = 'secondary';
            switch ($value->status) {
                case 'Active':
                    $notif = 'success';
                    break;
                case 'Activation required':
                    $notif = 'warning';
                    break;
                case 'Banned':
                    $notif = 'danger';
                    break;
            }
            $return = [
                'id' => $value->id,
                'firstname' => $value->detail !== NULL ? $value->detail->firstname : NULL,
                'lastname' => $value->detail !== NULL ? $value->detail->lastname : NULL,
                'email' => $value->email,
                'phone' => $value->detail !== NULL ? $value->detail->phone : NULL,
                'avatar' => $avatar,
                'status' => [
                    'value' => $value->status,
                    'notif' => $notif
                ]
            ];
            return $return;
        });
    }
}
