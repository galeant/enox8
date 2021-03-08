<?php

namespace App\Http\Response\Dashboard;

use Carbon\Carbon;

class PromoTransformer
{

    public static function list($response)
    {
        if ($response instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $response->getCollection()->transform(function ($v) {
                return self::reformer($v);
            });

            $data = [
                'data' => $response->items(),
                'current_page' => $response->currentPage(),
                'next_page_url' => $response->nextPageUrl(),
                'prev_page_url' => $response->previousPageUrl(),
                'total' => $response->total(),
                'total_page' => $response->lastPage()
            ];
        } else {
            $response->transform(function ($v) {
                return self::reformer($v);
            });
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
        $response = self::reformer($response);
        return response()->json([
            'code' => 200,
            'message' => 'Get detail success',
            'result' => $response
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
        $image = NULL;
        if (isset($value->image->url)) {
            $image = asset($value->image->url);
        }
        $now = Carbon::now();
        $eStart = Carbon::parse($value->start_date);
        $eEnd = Carbon::parse($value->end_date);
        $notif = 'secondary';
        if ($now->between($eStart, $eEnd)) {
            $notif = 'success';
            if ($now->diffInDays($eEnd) <= 7) {
                $notif = 'warning';
            }
        } else if ($now > $eEnd) {
            $notif = 'danger';
        }

        $return = [
            'id' => $value->id,
            'image' => $image,
            'name' => $value->name,
            'description' => $value->description,
            'status' => $value->status,
            'start_date' => [
                'value' => $value->start_date,
                'notif' => $notif
            ],
            'effective_end_date' => [
                'value' => $value->end_date,
                'notif' => $notif
            ],
            'discount' => $value->discount->transform(function ($v) {
                return [
                    'id' => $v->id,
                    'name' => $v->name,
                    // 'value' => $v->value,
                    // 'unit' => $v->unit,
                    // 'effective_start_date' => Carbon::parse($v->effective_start_date)->format('Y-m-d'),
                    // 'effective_end_date' => Carbon::parse($v->effective_end_date)->format('Y-m-d'),
                ];
            }),
            'voucher' => $value->voucher->transform(function ($v) {
                return [
                    'id' => $v->id,
                    'name' => $v->name,
                    'code' => $v->code
                ];
            })
        ];

        return $return;
    }
}
