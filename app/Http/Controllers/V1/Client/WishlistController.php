<?php

namespace App\Http\Controllers\V1\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\V1\User;
use App\Models\V1\Type;
use App\Http\Response\Client\WishlistTransformer;
use App\Http\Requests\Client\Wishlist\CreateRequest;
use DB;
use Carbon\Carbon;

class WishlistController extends Controller
{
    public function getList(Request $request)
    {
        try {
            $user = auth()->user();
            $product_id = $user->wishlist->pluck('pivot.product_id');
            $type_id = $user->wishlist->pluck('pivot.type_id');
            // dd($type_id);
            $data = Type::whereIn('id', $type_id)
                ->with(['product' => function ($q) use ($product_id) {
                    $q->whereIn('id', $product_id);
                }])
                ->select(
                    'product_type.*',
                    \DB::raw('(SELECT created_at FROM user_wishlist WHERE product_type.id = user_wishlist.type_id ) as wishlist_date')
                )
                ->where('status', 'publish')
                ->whereHas('product.selectedCategory', function ($q) {
                    $q->where('status', 'publish');
                });

            $data = $data->orderBy('wishlist_date', 'desc')->paginate(10);
            return WishlistTransformer::list($data);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function create(CreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $exist = $user->whereHas('wishlist', function ($q) use ($request) {
                $q->where('id', $request->type_id);
            })->count();
            if (!$exist) {
                $user->wishlist()->attach($request->type_id, ['product_id' => $request->product_id, 'created_at' => Carbon::now()]);
                $message = 'Wishlist added';
            } else {
                $user->wishlist()->detach($request->type_id);
                $message = 'Wishlist deleted';
            }
            DB::commit();
            return WishlistTransformer::general($message);
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
    }
}
