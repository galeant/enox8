<?php

namespace App\Http\Controllers\V1\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Requests\Client\Cart\CreateRequest;
use App\Http\Requests\Client\Cart\UpdateRequest;
use App\Http\Requests\Client\Cart\DeleteRequest;
use App\Http\Requests\Client\Cart\ShipmentRequest;

use App\Http\Response\Client\CartTransformer;

use App\Models\V1\Product;
use App\Models\V1\Cart;

use DB;
use Carbon\Carbon;

class CartController extends Controller
{
    public function getData(Request $request)
    {
        $user = auth()->user();
        return CartTransformer::list($user->cart->sortByDesc('created_at')->values());
    }

    public function create(CreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $now = Carbon::now();
            $product = Product::where('id', $request->product_id)
                ->with(['type' => function ($q) use ($request) {
                    $q->where('id', $request->type_id);
                }])
                ->firstOrFail();

            $existing = Cart::where([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'type_id' => $request->type_id
            ])->first();

            if ($existing !== NULL) {
                $qty = $existing->qty + $request->qty;
                $existing->update([
                    'qty' => $qty,
                    'note' => $request->note
                ]);
            } else {
                $cart = Cart::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'type_id' => $request->type_id,
                    'qty' => $request->qty,
                    'note' => $request->note,
                    // ini di pake untuk multi store
                    'store_id' => $product->store->id,
                    'store_name' => $product->store->name,
                    'store_slug' => $product->store->slug
                    //
                ]);
            }
            DB::commit();
            return CartTransformer::list($user->fresh()->cart->sortByDesc('created_at')->values());
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
    }

    public function update(UpdateRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $now = Carbon::now();
            $cart = Cart::where([
                'id' => $request->cart_id,
                'user_id' => $user->id
            ])->firstOrFail();
            $cart->update([
                'qty' => $request->qty,
                'note' => $request->filled('note') ? $request->note : NULL
            ]);


            DB::commit();
            return CartTransformer::list($user->fresh()->cart->sortBy('id')->values());
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
    }

    public function delete(DeleteRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $cart = Cart::whereIn('id', $request->cart_id)->get();
            foreach ($cart as $ct) {
                $ct->delete();
            }
            DB::commit();
            return CartTransformer::list($user->fresh()->cart->sortByDesc('created_at')->values());
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
    }

    public function shipment(ShipmentRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            foreach ($request->cart as $ct) {
                $cart = Cart::where('id', $ct['id'])->firstOrFail();
                $cart->update([
                    'qty' => $ct['qty'],
                    'note' => isset($ct['note']) ? $ct['note'] : NULL
                ]);
            }
            DB::commit();
            return CartTransformer::list($user->fresh()->cart->sortByDesc('created_at')->values());
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
    }
}
