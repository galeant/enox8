<?php

namespace App\Http\Controllers\V1\Client;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Checkout\CreateRequest;
use DB;
use App\Models\V1\Transaction;
use App\Models\V1\Bank;
use App\Models\V1\Courier;
use App\Models\V1\Cart;
use App\Models\V1\Voucher;
use App\Models\V1\VoucherUsage;
use App\Models\V1\Address;
use App\Models\V1\Product;
use App\Models\V1\Store;
use App\Jobs\PushNotif;

use App\Notifications\NewOrderNotification;

use Carbon\Carbon;

class CheckoutController extends Controller
{
    public function proced(CreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $address = Address::where('id', $request->address_id)->firstOrFail();
            $cart = Cart::whereIn('id', $request->cart_id)->get();
            $bank = Bank::where('id', $request->bank_id)->firstOrFail();
            $courier = Courier::where('code', $request->courier_code)->firstOrFail();
            $this->cartReformer($cart);
            // dd($cart);
            $voucher = NULL;
            $v_pid = collect([]);
            $v_cid = collect([]);
            if ($request->filled('voucher_code')) {
                $voucher = Voucher::with('product', 'category')->where('code', $request->voucher_code)->firstOrFail();
                $v_pid = $voucher->product;
                if ($v_pid->count() > 0) {
                    $v_pid = $v_pid->pluck('id');
                }

                $v_cid = $voucher->category;
                if ($v_cid->count() > 0) {
                    $v_cid = $v_cid->pluck('id');
                }
            }

            $invoice = $cart->groupBy('store_id');
            $transaction_list = [];
            foreach ($invoice as $inv) {
                $insurance_fee = 0;
                $invoice_total_price = $inv->sum('total_price');
                $invoice_total_price_discount = $inv->sum('discount_price');
                $product_discount_value = $invoice_total_price - $invoice_total_price_discount;
                $weight = $inv->pluck('product.weight')->sum();
                // API RAJA ONGKIR
                $req_param = [
                    'origin' => $inv[0]->product->store->regency_id,
                    'destination' => $address->regency_id,
                    'weight' => $weight,
                    'courier' => $request->courier_code
                ];

                $res = rajaongkir('POST', 'cost', NULL, $req_param);
                $res = $res->rajaongkir->results;

                $courier_price = 0;
                foreach ($res[0]->costs as $rs) {
                    if ($rs->service === $request->courier_service_name) {
                        $courier_price = $rs->cost[0]->value;

                        $etd = str_replace("HARI", '', $rs->cost[0]->etd);
                        $etd = str_replace(" ", '', $etd);
                        $etd = explode('-', $etd);
                        $delivery_duration = 0;
                        for ($i = 0; $i < count($etd); $i++) {
                            if ($etd[$i] > $delivery_duration) {
                                $delivery_duration = $etd[$i];
                            }
                        }
                        break;
                    }
                }

                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'total_price' => $invoice_total_price,
                    'total_price_discount' => $invoice_total_price_discount,
                    'transaction_code' => 'PIKOTRANS-' . time(),
                    'total_product_discount' => $product_discount_value,
                    // 'total_payment' => $invoice_total_payment,
                    'buyer_bank_account_name' => $request->bank_account_name,
                    'buyer_bank_account_number' => $request->bank_account_number,

                    'bank_id' => $bank->id,
                    'bank_name' => $bank->name,
                    'bank_account_type' => $bank->type,
                    'store_bank_account_number' => $bank->account_number,
                    // 'unique_code' => time(), //ga tw ini gmn
                    // 'payment_evidence' => NULL,
                    'courier_id' => $courier->id,
                    'courier_code' => $courier->code,
                    'courier_name' => $courier->name,
                    'courier_service_name' => $courier->service_name,
                    'courier_price' => $courier_price,
                    'delivery_duration' => $delivery_duration,
                    'insurance_fee' => $insurance_fee,
                    // 'is_complain' => 'ass',
                    // 'complain' => 'ass',
                    // 'voucher_usage_id' => ($voucher_usage !== NULL) ? $voucher_usage->id : NULL,
                    'status_id' => 1,
                    'store_id' => $inv[0]->product->store->id,
                    'store_name' => $inv[0]->product->store->name,

                    // 'resi_number' => 'PIKORES' . time(),
                    'recipient_name' => $address->recipient_name,
                    'recipient_address' => $address->address,
                    'recipient_country' => $address->country->name,
                    'recipient_province' => $address->province->name,
                    'recipient_regency' => $address->regency->name,
                    'recipient_district' => $address->district->name,
                    'recipient_village' => $address->village->name,
                    'recipient_phone' => $address->phone,
                    'recipient_postal_code' => $address->postal_code,
                    'recipient_latitude' => $address->latitude,
                    'recipient_longitude' => $address->longitude,

                    'sender_name' => $inv[0]->product->store->name,
                    'sender_address' => $inv[0]->product->store->address,
                    'sender_country' => $inv[0]->product->store->country->name,
                    'sender_province' => $inv[0]->product->store->province->name,
                    'sender_regency' => $inv[0]->product->store->regency->name,
                    'sender_district' => $inv[0]->product->store->district->name,
                    'sender_village' => $inv[0]->product->store->village->name,
                    'sender_phone' => $inv[0]->product->store->phone,
                    'sender_email' => $inv[0]->product->store->email,
                    'sender_postal_code' => $inv[0]->product->store->postal_code,
                ]);

                $total_voucher_discount = 0;

                foreach ($inv as $inv_detail) {
                    $pd = Product::where('id', $inv_detail->product_id)->with(['type' => function ($q) use ($inv_detail) {
                        $q->where('id', $inv_detail->type_id);
                    }])->firstOrFail();
                    $voucher_discount = 0;
                    if ($voucher !== NULL) {
                        $pd_ii = $v_pid->first(function ($val) use ($pd) {
                            return $val == $pd->id;
                        });
                        $pd_ci = $pd->category->pluck('id')->intersect($v_cid);
                        if ($pd_ci->count() != 0 || $pd_ii !== NULL) {
                            switch ($voucher->unit) {
                                case 'percentage':
                                    $voucher_discount = ($invoice_total_price_discount * $voucher->value) / 100;
                                    break;
                                case 'decimal':
                                    $voucher_discount = $invoice_total_price_discount - $voucher->value;
                                    break;
                            }

                            if ($voucher->max_discount !== NULL && (float) $voucher->max_discount !== 0.0 && $voucher_discount > (float) $voucher->max_discount) {
                                $voucher_discount = $voucher->max_discount;
                            }
                        }
                    }
                    $total_voucher_discount  = $total_voucher_discount + $voucher_discount;
                    $transaction->detail()->create([
                        'product_id' => $pd->id,
                        'product_slug' => $pd->slug,
                        'product_name' => $pd->name,
                        'type_id' => $pd->type[0]->id,
                        'type_name' => $pd->type[0]->name,
                        'qty' => $inv_detail->qty,
                        'total_price' => $inv_detail->total_price,
                        'total_discount' => $inv_detail->total_price - ($inv_detail->discount_price + $voucher_discount),
                        'total_payment' => $inv_detail->discount_price - $voucher_discount,
                        'product_price' => $inv_detail->product_price,
                        'product_discount' => $inv_detail->product_discount
                    ]);
                    $pd->type[0]->update([
                        'stock' => $pd->type[0]->stock - $inv_detail->qty
                    ]);
                }

                $transaction->update([
                    'total_payment' => $invoice_total_price_discount + $courier_price - $total_voucher_discount,
                    'total_voucher_discount' => $total_voucher_discount,
                    // 'voucher_usage_id' => $voucher_usage->id
                ]);
                $transaction->log()->create([
                    'transaction_id' => $transaction->id,
                    'status_id' => $transaction->status_id
                ]);
                $transaction_list[] = $transaction->fresh();
            }
            if ($voucher !== NULL) {
                $voucher_usage = VoucherUsage::create([
                    'user_id' => $user->id,
                    'voucher_code' => $voucher->code,
                    'voucher_value' => $voucher->value,
                    'voucher_unit' => $voucher->unit,
                    'voucher_max_discount' => $voucher->max_discount,
                    'voucher_discount_value' => $total_voucher_discount, //TOTAL NOMINAL DISKONNYA
                    'total_price' => $invoice_total_price_discount, // TOTAL HARGA PRODUCT YANG SUDAH DI DISKON, BELOM TERMASUK ONGKIR
                    'total_payment' => $invoice_total_price_discount - $total_voucher_discount,  // NILAI INI YANG AKAN DI KIRIM KE PAYMENT GATEWAY JIKA MENGGUNAKAN VOUCHER
                ]);
                Transaction::whereIn('id', array_column($transaction_list, 'id'))->update([
                    'voucher_usage_id' => $voucher_usage->id
                ]);
            }
            foreach ($cart as $ct) {
                Cart::where('id', $ct->id)->delete();
            }
            // dd('hold dilu');
            DB::commit();
            // $dt_transaction = Carbon::parse($transaction_list[0]['updated_at']);
            // $dt_limit = Carbon::parse($transaction_list[0]['updated_at'])->addDays(3);
            // $limit_in_second = $dt_transaction->diffInSeconds($dt_limit);
            // dd($limit_in_second);
            if (isset($transaction_list[0]['bank_account_type']) && $transaction_list[0]['bank_account_type'] === 'Virtual Account') {
                foreach ($transaction_list as $tl) {
                    PushNotif::dispatch($tl)->delay(now()->addMinutes(1));;
                }
            }

            return response()->json([
                'code' => 200,
                'message' => 'checkout berhasil',
                'data' => [
                    'transaction_code' => array_column($transaction_list, 'transaction_code'),
                    'total_payment' => array_sum(array_column($transaction_list, 'total_payment')),
                    'payment_time_limit' => Carbon::parse($transaction_list[0]['created_at'])->addDays(3)->format('Y-m-d H:i:s'),
                    'payment_bank' => $transaction_list[0]['bank_name'],
                    // 'bank_image' => $bank_choice->image !== NULL ? asset($bank_choice->image) : NULL,
                    'payment_account_number' => $transaction_list[0]['store_bank_account_number'],
                    'payment_type' => $transaction_list[0]['bank_account_type']
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
    }

    private function cartReformer($cart)
    {
        $cart->transform(function ($value) {
            $return = (object) [];

            $return->id = $value->id;
            $return->user_id = $value->user_id;
            $return->product_id = $value->product_id;
            $return->type_id = $value->type_id;
            $return->qty = $value->qty;
            $return->note = $value->note;
            $return->created_at = $value->created_at;
            $return->updated_at = $value->updated_at;
            $return->store_id = $value->store_id;
            $return->store_name = $value->store_name;
            $return->store_slug = $value->store_slug;

            $price = $value->type->price;
            if ($value->type->recalculate_discount !== NULL) {
                $price = $value->type->recalculate_discount;
            }
            $return->total_price = $value->qty * $value->type->price;
            $return->discount_price = $value->qty * $price;
            $return->product_discount = ($value->type->price - $price);
            $return->product_price = $value->type->price;
            $return->product = $value->product;
            $return->store = $value->store;

            return $return;
        });
    }

    private function calculatePaymentPrice($voucher, $total_payment)
    {
        // dd($total_payment);
        switch ($voucher->voucher_unit) {
            case 'percentage':
                $discount = ($total_payment * $voucher->voucher_value) / 100;
                break;
            case 'decimal':
                $discount = $voucher->value;
                break;
        }
        if (
            $voucher->voucher_max_discount !== NULL &&
            $voucher->voucher_max_discount !== 0 &&
            $discount > $voucher->voucher_max_discount
        ) {
            $discount = $voucher->voucher_max_discount;
        }
        $discount = $total_payment - $discount;
        return $discount;
    }
}
