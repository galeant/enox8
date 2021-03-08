<?php

namespace App\Http\Response\Dashboard;

use Carbon\Carbon;

class DiscountTransformer
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
        $banner = NULL;
        if ($response->banner !== NULL) {
            $banner = asset($response->banner);
        }
        $now = Carbon::now();
        $eStart = Carbon::parse($response->effective_start_date);
        $eEnd = Carbon::parse($response->effective_end_date);
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
            'id' => $response->id,
            'banner' => $banner,
            'name' => $response->name,
            'description' => $response->description,
            'value' => $response->value,
            'unit' => $response->unit,
            'status' => 'Deleted',
            'effective_start_date' => [
                'value' => $response->effective_start_date,
                'notif' => $notif
            ],
            'effective_end_date' => [
                'value' => $response->effective_end_date,
                'notif' => $notif
            ],
            'slug' => $response->slug,
            'product' => [],
            'category' => []
        ];
        foreach ($response->product as $p) {
            $return['product'][] = $p->id;
        }

        foreach ($response->category as $c) {
            $return['category'][] = $c->id;
        }
        return response()->json([
            'code' => 200,
            'message' => 'Delete success',
            'result' => $return
        ], 200);
    }

    private static function reformer($value)
    {
        $banner = NULL;
        if ($value->banner !== NULL) {
            $banner = asset($value->banner);
        }
        $now = Carbon::now();
        $eStart = Carbon::parse($value->effective_start_date);
        $eEnd = Carbon::parse($value->effective_end_date);
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
            'banner' => $banner,
            'name' => $value->name,
            'description' => $value->description,
            'value' => $value->value,
            'unit' => $value->unit,
            'status' => $value->status,
            'effective_start_date' => [
                'value' => $value->effective_start_date,
                'notif' => $notif
            ],
            'effective_end_date' => [
                'value' => $value->effective_end_date,
                'notif' => $notif
            ],
            'slug' => $value->slug,
            'product' => [],
            'category' => []
        ];
        foreach ($value->product as $p) {
            $return['product'][] = [
                'id' => $p->id,
                'name' => $p->name
            ];
        }

        foreach ($value->category as $c) {
            $return['category'][] = [
                'id' => $c->id,
                'name' => $c->name
            ];
        }
        return $return;
    }
}
