<?php

namespace App\Http\Response\Dashboard;

class BlogTransformer
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

    private static function reformer($response)
    {
        $return = [
            'id' => $response->id,
            'title' =>  $response->title,
            'short_content' => $response->short_content,
            'content' => $response->content,
            'status' => $response->status,
            'banner' => ($response->banner !== NULL) ? asset($response->banner) : NULL,
            'tag' => $response->tag->transform(function ($v) {
                return [
                    'id' => $v->id,
                    'name' => $v->name
                ];
            }),
            'category' => $response->category->transform(function ($v) {
                return [
                    'id' => $v->id,
                    'name' => $v->name
                ];
            }),
            'created_at' => $response->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $response->updated_at->format('Y-m-d H:i:s'),
            'creator' => [
                'id' => $response->creator->id,
                'fullname' => $response->creator->detail->fullname
            ]
        ];
        return $return;
    }
}
