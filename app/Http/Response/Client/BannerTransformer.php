<?php

namespace App\Http\Response\Client;

class BannerTransformer
{

    public static function general($message, $response = NULL)
    {
        return response()->json([
            'code' => 200,
            'message' => $message,
            'result' => $response
        ], 200);
    }

    public static function list($response)
    {
        if ($response instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $data = [
                'data' => $response->getCollection()->tranform(function ($v) {
                    return self::reformer($v);
                })->groupBy('group_name'),
                'current_page' => $response->currentPage(),
                'next_page_url' => $response->nextPageUrl(),
                'prev_page_url' => $response->previousPageUrl(),
                'total' => $response->total()
            ];
        } else {
            $response->transform(function ($v) {
                return self::reformer($v);
            });
            $data = [
                'data' => $response->groupBy('group_name')
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
    private static function reformer($response)
    {
        $result = [
            'id' => $response->id,
            'name' => $response->name,
            'slug' => $response->slug,
            'description' => $response->description,
            'web_banner' => isset($response->webBanner->url) ? asset($response->webBanner->url) : '',
            'mobile_banner' => isset($response->mobileBanner->url) ? asset($response->mobileBanner->url) : '',
            'relation_type' => $response->relation_to,
            'redirect_url' => '',
            'relation_entity'  => []
        ];
        if ($response->relation_to === 'redirect') {
            $result['redirect_url'] = $response->redirect_url;
        } else {
            foreach ($response->relation as $rs) {
                $result['relation_entity'][] = [
                    'id' => $rs->id,
                    'name' => $rs->name,
                    'code' => isset($rs->code) ? $rs->code : '',
                    'slug' => isset($rs->slug) ? $rs->slug : ''
                ];
            }
        }
        return $result;
    }
}
