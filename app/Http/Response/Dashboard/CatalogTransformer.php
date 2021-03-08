<?php

namespace App\Http\Response\Dashboard;

use Carbon\Carbon;

class CatalogTransformer
{

    public static function list($response, $update = false)
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
            if ($update == true) {
                $response->transform(function ($v) {
                    return self::reformer($v);
                });
            } else {
                $response->transform(function ($value) {
                    return [
                        'id' => $value->id,
                        'name' => $value->name
                    ];
                });
            }

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
        $return = self::reformer($response);
        $return['total_review'] = $response->review->count();
        foreach ($response->images as $imgC) {
            if ($imgC->url !== NULL) {
                $return['images'][] = [
                    'url' => asset($imgC->url)
                ];
            }
        }
        return response()->json([
            'code' => 200,
            'message' => 'Get detail success',
            'result' => $return
        ], 200);
    }

    public static function type($response)
    {
        return response()->json([
            'code' => 200,
            'message' => 'Get detail success',
            'result' => $response
        ], 200);
    }

    public static function general($message, $response = NULL)
    {
        return response()->json([
            'code' => 200,
            'message' => $message,
            'result' => $response
        ], 200);
    }

    public static function delete($response)
    {
        return response()->json([
            'code' => 200,
            'message' => 'Delete success',
            'result' => $response->fresh()
        ], 200);
    }

    public static function reviewList($response)
    {
        if ($response instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $response->getCollection()->transform(function ($v) {
                return [
                    'id' => $v->id,
                    'review' => $v->review,
                    'rating' => (float) $v->rating,
                    'transaction_id' => $v->transaction_id,
                    'user_id' => $v->user_id,
                    'user_name' =>  $v->user->detail->fullname,
                    'product_name' => $v->product->name,
                    'product_type_name' => $v->product_type->name,
                    'created_at' => Carbon::parse($v->created_at)->format('Y-m-d H:i:s'),
                    'image' => $v->image->transform(function ($im) {
                        return asset($im->url);
                    })
                ];
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
                return [
                    'id' => $v->id,
                    'review' => $v->review,
                    'rating' => (float) $v->rating,
                    'user_id' => $v->user_id,
                    'user_name' =>  $v->user->detail->fullname,
                    'transaction_id' => $v->transaction_id,
                    'created_at' => Carbon::parse($v->created_at)->format('Y-m-d H:i:s'),
                    'image' => $v->image->transform(function ($im) {
                        return asset($im->url);
                    })
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

    private static function reformer($value)
    {
        $return = [
            'id' => $value->id,
            'name' => $value->name,
            'code' => $value->code,
            'description' => $value->description,
            'main_image' => $value->main_image !== NULL ? asset($value->main_image) : NULL,
            'meta_title' => $value->meta_title,
            'meta_description' => $value->meta_description,
            'category' => [],
            'type' => [],
            'status' => $value->status,
            'weight' => $value->weight,
            'condition' => $value->condition,
            'minimum_order' => $value->minimum_order,
            'insurance' => $value->insurance,
            'rating' => $value->rating
        ];
        foreach ($value->type as $type) {
            $percentage = NULL;
            $nominal = NULL;
            if ($type->price !== $type->recalculate_discount && $type->recalculate_discount !== NULL) {
                $discount_price = $type->recalculate_discount;
                $percentage = round(100 - ($discount_price / ($type->price / 100)));
                $nominal = $type->price - $discount_price;
            }
            $return['type'][] = [
                'name' => $type->name,
                'id' => $type->id,
                'price' => $type->price,
                'stock' => $type->stock,
                'percent_discount' => $percentage,
                'nominal_discount' => $nominal,
                'discount_price' => $type->recalculate_discount,
                'effective_start_date' => $type->promo_effective_start_date,
                'effective_end_date' => $type->promo_effective_end_date,
                'image' => $type->image !== NULL ? asset($type->image) : NULL,
                'status' => $type->status,
                'is_default' => $type->is_default
            ];
        }
        $category = $value->category->first(function ($v, $key) {
            if ($v->pivot->selected === true) {
                return $v;
            }
        });
        $return['category'] = [
            'id' => isset($category) ? $category->id : NULL,
            'name' => isset($category) ? $category->name : NULL
        ];

        $return['tag'] = $value->tag->transform(function ($v) {
            return [
                'id' => $v->id,
                'slug' => $v->slug,
                'name' => $v->name
            ];
        });

        return $return;
    }
}
