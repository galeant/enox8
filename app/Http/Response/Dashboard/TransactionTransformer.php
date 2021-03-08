<?php

namespace App\Http\Response\Dashboard;

use Carbon\Carbon;

class TransactionTransformer
{

    public static function list($response)
    {
        if ($response instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $res = $response->getCollection()->transform(function ($v) {
                return self::reformer($v);
            });
            $data = [
                'data' => $res,
                'current_page' => $response->currentPage(),
                'next_page_url' => $response->nextPageUrl(),
                'prev_page_url' => $response->previousPageUrl(),
                'total' => $response->total(),
                'total_page' => $response->lastPage()
            ];
        } else {
            $res = $response->transform(function ($v) {
                return self::reformer($v);
            });
            $data = [
                'data' => $res
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
        $res = [
            'transaction' => [
                'id' => $response->id,
                'code' => $response->transaction_code,
                'total_qty' => $response->detail->sum('qty'),
                'total_price' => $response->total_price,
                'total_payment' => $response->total_payment,
                'bank_account_name' => $response->bank_account_name,
                'bank_account_number' => $response->bank_account_number,
                'bank_account_type' => $response->bank_account_type,
                'insurance_fee' => $response->insurance_fee,
                'status' => [
                    'id' => $response->status_id,
                    'name' => $response->status->name,
                    'declineable' => FALSE
                ],
                'created_at' => Carbon::parse($response->created_at)->format('d M Y H:i'),
                'payment_evidence' => $response->payment_evidence !== NULL ? asset($response->payment_evidence) : '',
                'payment_return_evidence' => $response->payment_return_evidence !== NULL ? asset($response->payment_return_evidence) : '',
            ],
            'log' => $response->log->transform(function ($v) {
                return [
                    'name' => $v->status->name,
                    'created_at' => Carbon::parse($v->created_at)->format('d M Y H:i')
                ];
            })->sortBy('created_at')->values()->toArray(),
            'user' => [
                'id' => $response->user->id,
                'fullname' => $response->user->detail->firstname . ' ' . $response->user->detail->lastname,
                'email' => $response->user->email
            ],
            'detail' => $response->detail->transform(function ($v) {
                return [
                    'product' => [
                        'id'  => $v->product_id,
                        'slug' => $v->product_slug,
                        'name' => $v->product_name,
                        'image' => isset($v->product->main_image) ? asset($v->product->main_image) : NULL
                    ],
                    'type' => [
                        'id'  => $v->type_id,
                        'name' => $v->type_name,
                        'image' => isset($v->productType->image) ? asset($v->productType->image) : NULL
                    ],
                    'qty' => $v->qty,
                    'total_price' => $v->total_price,
                    'total_discount' => $v->total_discount,
                    'total_payment' => $v->total_payment
                ];
            }),
            'recipient' => [
                'name' => $response->recipient_name,
                'address' => $response->recipient_address,
                'country' => $response->recipient_country,
                'province' => $response->recipient_province,
                'regency' => $response->recipient_regency,
                'district' => $response->recipient_district,
                'phone' => $response->recipient_phone,
                'postal_code' => $response->recipient_postal_code
                // 'latitude' => $response->recipient_latitude,
                // 'longitude' => $response->recipient_longitude
            ],
            'sender' => [
                'name' => $response->sender,
                'address' => $response->sender_address,
                'country' => $response->sender_country,
                'province' => $response->sender_province,
                'regency' => $response->sender_regency,
                'district' => $response->sender_district,
                'phone' => $response->sender_phone,
                'postal_code' => $response->sender_postal_code,
                'email' => $response->sender_email,

            ],
            'courier' => [
                'id' => $response->courier_id,
                'type' => $response->courier_type,
                'price' => $response->courier_price,
                'resi_number' => $response->res_number
            ],
            'bank' => [
                'id' => $response->bank_id,
                'name' => $response->bank_name
            ],
            'complain' => [
                'is_compalint' => $response->is_complain,
                'content' => $response->complain
            ],
            'voucher' => NULL

        ];
        if (($response->status_id === 4 && $response->bank_account_type === 'Virtual Account') || ($response->status_id === 1 && $response->bank_account_type === 'Manual')
        ) {
            $res['transaction']['status']['declineable'] = TRUE;
        }
        return response()->json([
            'code' => 200,
            'message' => 'Get detail success',
            'result' => $res
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

    private static function reformer($value)
    {
        $return = [
            'transaction_id' => $value->id,
            'transaction_code' => $value->transaction_code,
            'total_price' => $value->total_price,
            'total_payment' => $value->total_payment,
            'total_qty' => $value->detail->sum('qty'),
            'user_id' => $value->user->id,
            'user_fullname' => $value->user->detail->firstname . ' ' . $value->user->detail->lastname,
            'user_email' => $value->user->email,
            'status' => $value->status->name,
            'payment_evidence' => $value->payment_evidence !== NULL ? asset($value->payment_evidence) : '',
            'created_at' => Carbon::parse($value->created_at)->format('d M Y H:i'),
            'detail' => []
        ];
        foreach ($value->detail as $dt) {
            $return['detail'][] = [
                'product' => [
                    'id'  => $dt->product_id,
                    'slug' => $dt->product_slug,
                    'name' => $dt->product_name,
                    'image' => isset($dt->product->main_image) ? asset($dt->product->main_image) : NULL
                ],
                'type' => [
                    'id'  => $dt->type_id,
                    'name' => $dt->type_name,
                    'image' => isset($dt->productType->image) ? asset($dt->productType->image) : NULL
                ],
                'qty' => $dt->qty,
                'total_price' => $dt->total_price,
                'total_discount' => $dt->total_discount,
                'total_payment' => $dt->total_payment
            ];
        }
        return $return;
    }
}
