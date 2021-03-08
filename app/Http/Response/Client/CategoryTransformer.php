<?php

namespace App\Http\Response\Client;

class CategoryTransformer
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

    public static function getDetail($response)
    {
        // dd($response);
        // $parent_related = self::parentRelated($response);
        // $children_related = self::childrenRelated($response);
        // $response->related_category = array_merge($parent_related,$children_related);
        // unset($response->children);
        return response()->json([
            'code' => 200,
            'message' => 'Get detail success',
            'result' => self::reformer($response)
        ], 200);
    }

    private static function reformer($response)
    {
        $return = [
            'id' => $response->id,
            'name' => $response->name,
            'slug' => $response->slug,
            'icon' => $response->icon === NULL ? '' : asset($response->icon->url),
            'thumbnail' => $response->thumbnail === NULL ? '' : asset($response->thumbnail->url),
            'children' => []
        ];
        foreach ($response->children as $ch) {
            $return['children'][] = [
                'id' => $ch->id,
                'name' => $ch->name,
                'slug' => $ch->slug,
                'icon' => $ch->icon === NULL ? '' : asset($ch->icon->url),
                'thumbnail' => $ch->thumbnail === NULL ? '' : asset($ch->thumbnail->url),
                'children' => []
            ];
        }
        return $return;
    }


    private static function parentRelated($category)
    {
        $id = $category->id;
        $parentLess = false;
        $result = [];
        while ($parentLess == false) {
            if ($id !== $category->id) {
                $result[] = [
                    'id' => $category->id,
                    'slug' => $category->slug,
                    'name' => $category->name,
                    'parent_id' => $category->parent_id,
                    'description' => $category->description
                ];
            }

            if ($category->parent !== NULL) {
                $parentLess = false;
            } else {
                $parentLess = true;
            }
            $category = $category->parent;
        }
        return array_reverse($result);
    }

    private static function childrenRelated($category)
    {
        $return = [];
        $c = $category->children->toArray();
        $i = 0;
        array_walk_recursive($c, function ($value, $key) use (&$return, &$i) {
            $return[$i][$key] = $value;
            if ($key == 'description') {
                $i++;
            }
        });
        return $return;
    }
}
