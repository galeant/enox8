<?php

namespace App\Http\Response\Dashboard;

class CategoryTransformer
{

    public static function list($response)
    {
        if ($response instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $response->getCollection()->transform(function ($value) {

                $return = $value->toArray();
                $return['parent_full_path'] = self::parentPath($value);
                if ($value->icon !== NULL) {
                    $return['icon'] = asset($value->icon->url);
                }
                if ($value->thumbnail !== NULL) {
                    $return['thumbnail'] = asset($value->thumbnail->url);
                }
                return $return;
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
                return [
                    'id' => $value->id,
                    'name' => $value->name,
                    'parent_full_path' => self::parentPath($value)
                ];
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
        $return = $response->toArray();
        if ($response->icon !== NULL) {
            $return['icon'] =  asset($response->icon->url);
        }
        if ($response->thumbnail !== NULL) {
            $return['thumbnail'] =  asset($response->thumbnail->url);
        }
        $return['parent_full_path'] = self::parentPath($response);
        return response()->json([
            'code' => 200,
            'message' => 'Get detail success',
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

    private static function parentPath($category)
    {
        $parentes = false;
        $result = [];
        $counter = 0;
        while ($parentes == false && $counter <= 2) {
            $result[] =  $category->name;
            if ($category->parent !== NULL) {
                $parentes = false;
                $category = $category->parent;
            } else {
                $parentes = true;
            }
            $counter++;
        }
        // dd($result);
        return implode(' > ', array_reverse($result));
    }
}
