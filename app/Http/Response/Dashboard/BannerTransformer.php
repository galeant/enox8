<?php

namespace App\Http\Response\Dashboard;

class BannerTransformer
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
            'result' => self::reformer($response)
        ], 200);
    }

    private static function reformer($response)
    {
        // dd($response->webBanner);
        $return = [
            'id' => $response->id,
            'name' => $response->name,
            'description' => $response->description,
            'banner' => [
                'web' => $response->webBanner === NULL ? NULL : asset($response->webBanner->url),
                'mobile'  => $response->mobileBanner === NULL ? NULL : asset($response->mobileBanner->url),
            ],
            'status' => $response->status,
            'type' => $response->relation_to,
            'redirect_url' => $response->redirect_url,
            'relation' => []
        ];

        if ($response->relation_to !== 'redirect') {
            foreach ($response->relation as $rs) {
                $return['relation'][] = [
                    'id' => $rs->id,
                    'name' => $rs->name
                ];
            }
        }
        return $return;
    }
}
