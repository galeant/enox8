<?php

namespace App\Http\Controllers\V1\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\V1\Voucher;
use App\Models\V1\Cart;
use Carbon\Carbon;
use App\Http\Response\Client\VoucherTransformer;
use App\Http\Requests\Client\Voucher\GetRequest;
// use Exception;

class VoucherController extends Controller
{
    public function getData(GetRequest $request)
    {
        try {
            $user = auth()->user();
            $cart = Cart::whereIn('id', array_column($request->cart, 'id'))->get();
            $product_id = $cart->pluck('product_id')->toArray();
            $category_id = $cart->pluck('product.category')->flatten()->pluck('id')->toArray();
            $discount = $cart->pluck('type')->transform(function ($v) {
                if ($v->recalculate_discount  !== NULL) {
                    return $v->recalculate_discount;
                } else {
                    return $v->price;
                }
            })->sum();
            $now = Carbon::now();
            $data = Voucher::where('effective_start_date', '<=', $now)
                ->where('effective_end_date', '>=', $now)
                ->where('status', 'publish')
                ->where('minimum_payment', '>=', $discount)
                // ->select( 'voucher.*',
                //     \DB::raw('(SELECT COUNT(voucher_usage.id) FROM voucher_usage WHERE voucher.code = voucher_usage.voucher_code AND voucher_usage.user_id = '.$user->id.') as usage'),
                //     \DB::raw('(SELECT COUNT(voucher_usage.id) FROM voucher_usage WHERE voucher.code = voucher_usage.voucher_code AND date(voucher_usage.created_at) = current_date AND voucher_usage.user_id = '.$user->id.') as usage_today')
                // )
                ->where(function ($q) use ($product_id, $category_id) {
                    $q->whereHas('product', function ($q) use ($product_id) {
                        $q->whereIn('id', $product_id);
                    })->orWhereHas('category', function ($q) use ($category_id) {
                        $q->whereIn('id', $category_id);
                    });
                })
                ->withCount([
                    'usage as usage_all' => function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    },
                    'usage as usage_today' => function ($q) use ($user, $now) {
                        $q->where('user_id', $user->id)
                            ->whereDate('created_at', $now);
                    },
                ])
                ->get();
            // dd($data->toArray());
            return VoucherTransformer::list($data);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
