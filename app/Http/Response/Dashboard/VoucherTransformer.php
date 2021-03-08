<?php

namespace App\Http\Response\Dashboard;

use Carbon\Carbon;

class VoucherTransformer
{

    public static function list($response)
    {
        if ($response instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $response->getCollection()->transform(function ($value) {
                return self::reformer($value);
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
            $response->transform(function ($value) {
                return self::reformer($value);
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
        return response()->json([
            'code' => 200,
            'message' => 'Get detail success',
            'result' => self::reformer($response)
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
        $now = Carbon::now();
        $eStart = Carbon::parse($value->effective_start_date);
        $eEnd = Carbon::parse($value->effective_end_date);
        $notif = 'no_action';
        if ($now->between($eStart, $eEnd)) {
            $notif = 'active';
            if ($now->diffInDays($eEnd) <= 7) {
                $notif = 'warning';
            }
        } else if ($now > $eEnd) {
            $notif = 'danger';
        }
        $return = [
            'id' => $value->id,
            'name' => $value->name,
            'code' => $value->code,
            'description' => $value->description,
            'image' => NULL,
            'limit_per_user' => $value->limit_per_user,
            'limit_per_user_per_day' => $value->limit_per_user_per_day,
            'effective_start_date' => [
                'value' => $value->effective_start_date,
                'notif' => $notif
            ],
            'effective_end_date' => [
                'value' => $value->effective_end_date,
                'notif' => $notif
            ],
            'minimum_payment' => $value->minimum_payment,
            'value' => $value->value,
            'unit' => $value->unit,
            'status' => $value->status,
            'max_discount' => $value->max_discount,
            'product' => [],
            'category' => []
        ];
        if ($value->image !== NULL) {
            $return['image'] = asset($value->image->url);
        }
        foreach ($value->product as $pr) {
            $return['product'][] = [
                'id' => $pr->id,
                'name' => $pr->name,
                'slug' => $pr->slug
            ];
        }

        foreach ($value->category as $ct) {
            $return['category'][] = [
                'id' => $ct->id,
                'name' => $ct->name,
                'slug' => $ct->slug
            ];
        }
        return $return;
    }
}
