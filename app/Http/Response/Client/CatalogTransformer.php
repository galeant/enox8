<?php

namespace App\Http\Response\Client;

class CatalogTransformer
{

    public static function list($response, $related = false, $auth = NULL)
    {
        if ($response instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            self::reformer($response->getCollection(), $auth);
            $data = [
                'data' => $response->items(),
                'current_page' => $response->currentPage(),
                'next_page_url' => $response->nextPageUrl(),
                'prev_page_url' => $response->previousPageUrl(),
                'total' => $response->total()
            ];
        } else {
            self::reformer($response, $auth);
            $data = [
                'data' => $response
            ];
        }
        if ($related === true) {
            return $data['data'];
        }
        return response()->json([
            'code' => 200,
            'message' => 'Get list success',
            'result' => $data
        ], 200);
    }

    public static function getDetail($response, $related_product, $auth = NULL)
    {
        $return = [
            'id' => $response->id,
            'name' => $response->name,
            'slug' => $response->slug,
            'main_image' => $response->main_image !== NULL ? asset($response->main_image) : NULL,
            'description' => $response->description,
            'meta_title' => $response->meta_title,
            'meta_descrption' => $response->meta_description,
            'rating' => (string) $response->rating,
            'type' => [],
            'category' => [],
            'image' => $response->images->transform(function ($v) {
                return asset($v->url);
            }),
            'related' => self::list($related_product, true),
        ];

        foreach ($response->type as $type) {
            $return['type'][] = [
                'id' => $type->id,
                'price' => $type->price,
                'name' => $type->name,
                'stock' => $type->stock,
                'discount_price' => $type->recalculate_discount
            ];
        }
        $category = $response->category->first(function ($value, $key) {
            if ($value->pivot->selected === true) {
                return $value;
            }
        });
        $return['category'] = [
            'id' => $category->id,
            'name' => $category->name
        ];

        if ($auth) {
            $auth = auth()->user();
            $wishlist = $auth->wishlist->pluck('id')->toArray();
            foreach ($return['type'] as $rid => $rtp) {
                $return['type'][$rid]['is_wishlist'] = false;
                if (in_array($rtp['id'], $wishlist)) {
                    $return['type'][$rid]['is_wishlist'] = true;
                }
            }
        }
        return response()->json([
            'code' => 200,
            'message' => 'Get detail success',
            'result' => $return
        ], 200);
    }



    private static function reformer($response, $auth)
    {
        $response->transform(function ($value) use ($auth) {
            $discount_price = NULL;
            $percentage = 0;
            if ($value->defaultType->price !== $value->display_price) {
                $discount_price = $value->display_price;
                $percentage = '-' . round(100 - ($discount_price / ($value->defaultType->price / 100)));
            }

            $result = [
                'id' => $value->id,
                'slug' => $value->slug,
                'name' => $value->name,
                'main_image' => $value->main_image !== NULL ? asset($value->main_image) : NULL,
                'description' => $value->description,
                'meta_title' => $value->meta_title,
                'meta_description' => $value->meta_description,
                'rating' => (string) $value->rating,
                'price' => [
                    'idr' => number_format($value->defaultType->price, 2, ',', '.'),
                    'usd' => number_format($value->defaultType->price, 2, '.', ',')
                ],
                'discount_price' => [
                    'idr' => !isset($discount_price) ? '' : number_format($discount_price, 2, ',', '.'),
                    'usd' => !isset($discount_price) ? '' : number_format($discount_price, 2, ',', '.')
                ],
                'discount_precentage' => (int) $percentage,
                'category' => [],
                'is_wishlist' => false
            ];


            $category = $value->category->first(function ($v, $key) {
                if ($v->pivot->selected === true) {
                    return $v;
                }
            });
            $result['category'] = [
                'id' => isset($category->id) ? $category->id : '',
                'name' => isset($category->name) ? $category->name : ''
            ];
            if ($auth) {
                $auth = auth()->user();
                $exist = $auth->wishlist->first(function ($val, $key) use ($value) {
                    if ($val->id === $value->defaultType->id && $val->pivot->product_id === $value->id) {
                        return $val;
                    }
                });
                if ($exist) {
                    $result['is_wishlist'] = true;
                }
            }
            return $result;
        });
    }
}
