<?php

namespace App\Http\Response\Dashboard;

use Carbon\Carbon;

class NotificationTransformer
{

    public static function stockZero($response)
    {
        if ($response instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $res = $response->getCollection()->transform(function ($v) {
                $return = $v->product;
                if ($return->main_image !== NULL) {
                    $return->main_image = asset($return->main_image);
                }
                $return->type = [
                    'id' => $v->id,
                    'name' => $v->name,
                    'discount_value' => $v->discount_value,
                    'discount_unit' => $v->discount_unit,
                    'discount_effective_start_date' => $v->discount_effective_start_date,
                    'discount_effective_end_date' => $v->discount_effective_end_date,
                    'status' => $v->status
                ];
                return $return;
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
            // $data = [
            //     'data' => $response->transform(function ($v) {
            //         $return = $v->product;
            //         if ($return->main_image !== NULL) {
            //             $return->main_image = asset($return->main_image);
            //         }
            //         $return->type = [
            //             'id' => $v->id,
            //             'name' => $v->name,
            //             'discount_value' => $v->discount_value,
            //             'discount_unit' => $v->discount_unit,
            //             'discount_effective_start_date' => $v->discount_effective_start_date,
            //             'discount_effective_end_date' => $v->discount_effective_end_date,
            //             'status' => $v->status
            //         ];
            //         return $return;
            //     })
            // ];
            $data = [
                'total_data' => $response->count(),
                'ids' => $response->pluck('id')
            ];
        }
        return response()->json([
            'code' => 200,
            'message' => 'Get data success',
            'result' => $data
        ], 200);
    }

    public static function newComment($response)
    {
        // dd($response);
        $data = [
            'data' => $response->transform(function ($v) {
                return [
                    'comment_id' => $v->id,
                    'comment' => $v->content,
                    'create_at' => $v->updated_at, // waktu terakhir di ubah, bukan terakhir di buat
                    'user' => [
                        'id' => $v->user->id,
                        'email' => $v->user->email,
                        'firstname' => $v->user->firstname,
                        'lastname' => $v->user->lastname,
                        'avatar' => ($v->user->avatar !== NULL) ? asset($v->user->avatar) : NULL
                    ],
                    'product' => [
                        'id' => $v->product->id,
                        'name' => $v->product->name,
                        'code' => $v->product->code,
                        'image' => $v->product->main_image
                    ]
                ];
            }),
            'total' => 0
        ];
        $data['total'] = $response->count();
        return response()->json([
            'code' => 200,
            'message' => 'Get new comment success',
            'result' => $data
        ], 200);
    }

    public static function newReview($response)
    {
        // dd($response);
        $data = [
            'data' => $response->transform(function ($v) {
                // dd($v->transaction);
                return [
                    'transaction' => [
                        'id' => $v->transaction_id,
                        'code' => $v->transaction->transaction_code
                    ],
                    'review_id' => $v->id,
                    'review' => $v->review,
                    'create_at' => $v->created_at, // waktu terakhir di ubah, bukan terakhir di buat
                    'user' => [
                        'id' => $v->user->id,
                        'email' => $v->user->email,
                        'firstname' => $v->user->firstname,
                        'lastname' => $v->user->lastname,
                        'avatar' => ($v->user->avatar !== NULL) ? asset($v->user->avatar) : NULL
                    ],
                    'product' => [
                        'id' => $v->product->id,
                        'name' => $v->product->name,
                        'code' => $v->product->code,
                        'image' => isset($v->product->main_image) ? asset($v->product->main_image) : NULL
                    ],
                    'product_type' => [
                        'id' => $v->product_type->id,
                        'name' => $v->product_type->name,
                        'image' => isset($v->product_type->image) ? asset($v->product_type->image) : NULL
                    ],
                    'image' => $v->image->transform(function ($vi) {
                        return [
                            'url' => asset($vi->url)
                        ];
                    })
                ];
            }),
            'total' => 0
        ];

        $data['total'] = $response->count();
        return response()->json([
            'code' => 200,
            'message' => 'Get new review success',
            'result' => $data
        ], 200);
    }

    public static function newTransaction($response)
    {
        // dd($response);
        $data = [
            'data' => $response->transform(function ($v) {
                // dd($v->transaction);
                return [
                    'id' => $v->id,
                    'code' => $v->transaction_code
                ];
            }),
            'total' => 0
        ];
        $data['total'] = $response->count();
        return response()->json([
            'code' => 200,
            'message' => 'Get new transaction success',
            'result' => $data
        ], 200);
    }
    public static function delete($response)
    {
    }

    private static function reformer($response)
    {
    }
}
