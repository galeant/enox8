<?php

namespace App\Http\Response\Client;

use Carbon\Carbon;

class BlogTransformer
{
    public static function getList($response)
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
                'total' => $response->total()
            ];
        } else {
            $data = [
                'data' => $response->transform(function ($value) {
                    return self::reformer($value);
                })
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
        $result = self::reformer($response);
        return response()->json([
            'code' => 200,
            'message' => 'Get detail success',
            'result' => $result
        ], 200);
    }

    private static function reformer($value)
    {
        $result = [
            'id' => $value->id,
            'title' => $value->title,
            'slug' => $value->slug,
            'short_content' => $value->short_content,
            'content' => $value->content,
            'banner' => isset($value->banner) ? asset($value->banner) : '',
            'created_by' => $value->creator->detail->fullname,
            'created_at' => Carbon::parse($value->created_at)->format('d-m-Y'),
            'tag' => [],
            'category' => []
        ];

        foreach ($value->tag as $tg) {
            $result['tag'][] = [
                'name' => $tg->name,
                'slug' => $tg->name
            ];
        }

        foreach ($value->category as $ct) {
            $result['category'][] = [
                'name' => $ct->name,
                'slug' => $ct->name
            ];
        }
        return $result;
    }
}
