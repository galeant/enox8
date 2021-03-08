<?php

namespace App\Providers;

// use App\Services\SocialUserResolver;
// use Coderello\SocialGrant\Resolvers\SocialUserResolverInterface;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\V1\Product;
use App\Models\V1\Type;
use App\Models\V1\Category;
use App\Models\V1\User;
use App\Models\V1\Cart;
use App\Models\V1\Comment;
use App\Models\V1\Voucher;
use App\Models\V1\VoucherUsage;
use App\Models\V1\Transaction;
use App\Models\V1\TransactionDetail;
use Carbon\Carbon;
use DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('is_base64_or_url', function ($attribute, $value, $parameters, $validator) {
            $return = true;
            if ($value !== NULL) {
                $return = false;
                $base64 = false;
                if (strpos($value, 'base64') !== false || strpos($value, ENV('APP_URL')) !== FALSE) {
                    if (
                        strpos($value, 'data:image/jpg') !== false
                        || strpos($value, 'data:image/png') !== false
                        || strpos($value, 'data:image/jpeg') !== false
                        || strpos($value, ENV('APP_URL')) !== FALSE
                    ) {
                        $base64 = true;
                    }
                }

                $url = false;
                // if (strpos($value, 'http://') !== false || strpos($value, 'https://') !== false) {
                //     $url = @file_get_contents($value);
                // }
                if ($base64 === true || $url !== false) {
                    $return = true;
                }
            }
            return $return;
        });

        Validator::extend('exist_or_double', function ($attribute, $value, $parameters, $validator) {
            $default = [];
            foreach ($value as $v) {
                if (isset($v['default']) && $v['default'] == true) {
                    $default[] = $v['default'];
                }
            }
            return count($default) == 1;
        });


        Validator::extend('token_valid', function ($attribute, $value, $parameters, $validator) {
            $return = true;
            $user = User::where('activation_token', $value)->firstOrFail();
            if (Carbon::now() > $user->activation_time_limit) {
                $return = false;
            }
            return $return;
        });

        Validator::extend('type_in_product', function ($attribute, $value, $parameters, $validator) {
            $return = false;
            $product_type = Type::where('id', $value)->whereHas('product', function ($q) use ($parameters) {
                $q->where('id', $parameters[0]);
            })->first();
            if ($product_type !== NULL) {
                $return = true;
            }
            return $return;
        });

        Validator::extend('stock_available', function ($attribute, $value, $parameters, $validator) {
            $return = false;
            if ($parameters[0] == 'NULL' && isset($parameters[1])) {
                $cart = Cart::where('id', $parameters[1])
                    ->firstOrFail();

                $stock = Type::where('id', $cart->type_id)
                    ->where('stock', '>=', $value)
                    ->first();
            } else {
                $user = auth()->user();
                $cart = $user->cart->first(function ($v) use ($parameters) {
                    if ($v->type_id == $parameters[0]) {
                        return $v;
                    }
                });
                if ($cart !== NULL) {
                    $value = $value + $cart->qty;
                }

                $stock = Type::where('id', $parameters[0])
                    ->where('stock', '>=', $value)
                    ->first();
            }
            if ($stock !== NULL) {
                $return = true;
            }

            return $return;
        });

        Validator::extend('cart_user', function ($attribute, $value, $parameters, $validator) {

            $return = false;
            $cart = Cart::where('id', $value)
                ->where('user_id', $parameters[0])
                ->first();
            if ($cart !== NULL) {
                $return = true;
            }
            return $return;
        });

        Validator::extend('exist_except_zero', function ($attribute, $value, $parameters, $validator) {
            $return = true;
            $data = $value;
            if (isset($parameters[0]) && isset($parameters[1]) && $value !== 0) {
                $data = DB::table($parameters[0])
                    ->where($parameters[1], $value)
                    ->first();
            }
            if ($data === NULL && $value !== 0) {
                $return = false;
            }
            return $return;
        });

        Validator::extend('belongs_user', function ($attribute, $value, $parameters, $validator) {
            $return = true;
            $user = User::where('id', $parameters[0])->whereHas($parameters[1], function ($q) use ($value) {
                $q->where('id', $value);
            })->first();
            if ($user === NULL) {
                $return = false;
            }
            return $return;
        });

        Validator::extend('same_store', function ($attribute, $value, $parameters, $validator) {
            $return = false;
            $value = explode(",", $value);
            $validate = [];
            foreach ($value as $v) {
                $validate[] = User::where([
                    'id' => $v,
                    'store_id' => $parameters[0]
                ])->count();
            }
            if (!in_array(0, $validate)) {
                $return = true;
            }

            return $return;
        });

        Validator::extend('product_slug_available', function ($attribute, $value, $parameters, $validator) {
            $return = true;
            $slug = str_slug($value);
            $slug = str_slug($value) . '-' . $parameters[0] . '-' . $parameters[1];

            $product = Product::where('slug', $slug);
            if (isset($parameters[2])) {
                $product = $product->where('id', '!=', $parameters[2]);
            }
            $product = $product->first();
            if ($product !== NULL) {
                $return = false;
            }
            return $return;
        });

        Validator::extend('has_child', function ($attribute, $value, $parameters, $validator) {
            $return = true;
            $comment = Comment::where('id', $value)->has('children')->first();
            if ($comment !== NULL) {
                $return = false;
            }
            return $return;
        });

        // Validator::extend('one_or_much', function ($attribute, $value, $parameters, $validator) {
        //     $return = false;
        //     $validate = [];
        //     $value = explode(",",$value);
        //     foreach($value as $v){
        //         $validate[] = DB::table($parameters[0])->where('id',$v)->count();
        //     }
        //     if(!in_array(0,$validate)){
        //         $return = true;
        //     }
        //     return $return;
        // });

        Validator::extend('publish_for_default', function ($attribute, $value, $parameters, $validator) {
            $return = true;
            if ($parameters[0] == true && $value !== 'publish') {
                $return = false;
            }
            return $return;
        });

        Validator::extend('publish_exist', function ($attribute, $value, $parameters, $validator) {
            $return = false;
            foreach ($value as $v) {
                if (isset($v['status']) && $v['status'] == 'publish') {
                    $return = true;
                    break;
                }
            }
            return $return;
        });

        // Validator::extend('mass_type_in_product', function ($attribute, $value, $parameters, $validator) {
        //     $return = false;
        //     $value = explode(",",$value);
        //     $validate = [];
        //     foreach($value as $v){
        //         $validate[] = Type::where('id',$v)->whereHas('product',function($q)use($parameters){
        //             $q->where('id',$parameters[0]);
        //         })->count();
        //     }

        //     if(!in_array(0,$validate)){
        //         $return = true;
        //     }
        //     return $return;
        // });

        Validator::extend('user_exist_with_explode', function ($attribute, $value, $parameters, $validator) {
            $return = false;
            $validate = [];
            $value = explode(",", $value);

            if (isset($parameters[0])) {
                switch ($parameters[0]) {
                    case 'customer':
                        $condition = [
                            'can_access_customer' => true
                        ];
                        break;
                    case 'admin':
                        $condition = [
                            'can_access_admin' => true
                        ];
                        break;
                }
                foreach ($value as $v) {
                    $validate[] = User::where($condition)->where('id', $v)->count();
                }
                if (!in_array(0, $validate)) {
                    $return = true;
                }
            }
            return $return;
        });

        Validator::extend('parent_recursive_counter', function ($attribute, $value, $parameters, $validator) {
            $return = true;
            if ($value != 0) {
                $ct = Category::where('id', $value)->first();
                $counter = 0;
                while ($ct->parent !== NULL) {
                    if ($counter > 2) {
                        $return  = false;
                        break;
                    }
                    $counter++;
                    $ct = $ct->parent;
                }
            }

            return $return;
        });

        Validator::extend('not_self', function ($attribute, $value, $parameters, $validator) {
            $return = true;
            if ($value == $parameters[0]) {
                $return = false;
            }

            return $return;
        });

        Validator::extend('valid_voucher', function ($attribute, $value, $parameters, $validator) {
            $return = true;
            $user = auth()->user();

            $voucher = Voucher::where('code', $value)->where('status', 'publish')->firstOrFail();
            $usage = VoucherUsage::where([
                'user_id' => $user->id,
                'voucher_code' => $value
            ])->get();

            $usage_today = $usage->filter(function ($v) {
                if (Carbon::now()->format('Y-m-d') == Carbon::parse($v->created_at)->format('Y-m-d')) {
                    return $v;
                }
            })->count();
            $v_for_prod_cat = Cart::whereIn('id', $parameters)
                ->where(function ($q) use ($value) {
                    $q->whereHas('product.voucher', function ($q1) use ($value) {
                        $q1->where('code', $value);
                    })->orWhereHas('product.category', function ($q1) use ($value) {
                        $q1->whereHas('voucher', function ($q2) use ($value) {
                            $q2->where('code', $value);
                        });
                    });
                })
                ->count();
            if (
                $usage->count() > $voucher->limit_per_user &&
                $usage_today > $voucher->limit_per_user_per_day &&
                $v_for_prod_cat > 0 &&
                Carbon::now() > $voucher->effective_start_date &&
                Carbon::now() < $voucher->effective_end_date
            ) {
                $return = false;
            }
            return $return;
        });

        Validator::extend('voucher_min_payment', function ($attribute, $value, $parameters, $validator) {
            $return = true;
            $cart = Cart::whereIn('id', $parameters)->get();
            $total_payment = $cart->sum('discount_price');
            $voucher = Voucher::where('code', $value)->firstOrFail();
            if (isset($voucher->minimum_payment) && $total_payment < $voucher->minimum_payment) {
                $return = false;
            }
            return $return;
        });

        Validator::extend('product_store_active', function ($attribute, $value, $parameters, $validator) {
            $return = true;
            $product = Product::where('slug', $value)->orWhere('id', $value)->whereHas('store', function ($q) {
                $q->where('deleted_at', NULL);
            })->count();
            if ($product == 0) {
                $return = false;
            }
            return $return;
        });

        Validator::extend('transaction_belongs', function ($attribute, $value, $parameters, $validator) {
            // $parameters[0] adalah status_id transaction yang ingin di cari
            $return = true;
            $user = auth()->user();
            $transaction = Transaction::where('transaction_code', $value)->where('user_id', $user->id);
            if (isset($parameters[0])) {
                $transaction = $transaction->whereIn('status_id', $parameters);
            }

            $transaction = $transaction->firstOrFail();
            return $return;
        });

        Validator::extend('is_child', function ($attribute, $value, $parameters, $validator) {
            $return = true;
            $validate = Category::where('id', $value)->where('parent_id', 0)->count();
            if ($validate !== 0) {
                $return = false;
            }
            return $return;
        });

        Validator::extend('is_publish', function ($attribute, $value, $parameters, $validator) {
            $return = true;
            $validate = DB::table($parameters[0])
                ->where('id', $value)
                ->where(function ($q) {
                    $q->where('status', 'publish')
                        ->orWhere('status', true)
                        ->orWhere('status', 1);
                })
                ->first();
            $nullable = isset($parameters[1]) ? true : false;
            if ($validate === NULL && $nullable === true) {
                $return = false;
            }
            return $return;
        });

        Validator::extend('is_valid_password', function ($attribute, $value, $parameters, $validator) {
            $return = false;
            if (Hash::check(base64_decode($value), $parameters[0])) {
                $return = true;
            }
            return $return;
        });

        Validator::extend('detail_transaction', function ($attribute, $value, $parameters, $validator) {
            $return = false;
            $valid = Transaction::where('transaction_code', $parameters[0])->whereHas('detail', function ($q) use ($value) {
                $q->where('id', $value);
            })->first();
            if ($valid !== NULL) {
                $return = true;
            }
            return $return;
        });

        Validator::extend('attemp_try', function ($attribute, $value, $parameters, $validator) {
            $return = false;
            $count = DB::table($parameters[1])->where('review.user_id', $parameters[2])
                ->where('review.transaction_detail_id', $parameters[3])
                ->join('transaction', 'review.transaction_id', '=', 'transaction.id')
                ->where('transaction.transaction_code', $value)
                ->count();
            if ($count < $parameters[0]) {
                $return = true;
            }
            return $return;
        });

        Validator::extend('detail_to_transaction', function ($attribute, $value, $parameters, $validator) {
            $return = true;
            TransactionDetail::where('id', $value)
                ->whereHas('transaction', function ($q) use ($parameters) {
                    $q->where('transaction_code', $parameters[0]);
                })
                ->firstOrFail();
            return $return;
        });

        Validator::extend('qty_limit', function ($attribute, $value, $parameters, $validator) {
            $return = true;
            $valid = TransactionDetail::where('id', $parameters[1])
                ->whereHas('transaction', function ($q) use ($parameters) {
                    $q->where('transaction_code', $parameters[0]);
                })
                ->firstOrFail();
            if ($valid->qty < $value) {
                $return = false;
            }
            return $return;
        });

        Validator::extend('unique_slug_name', function ($attribute, $value, $parameters, $validator) {
            $return = true;
            $slug = str_slug($value);
            if (isset($parameters[1])) {
                str_slug($value . '-' . $parameters[1]);
            }
            $validate = DB::table($parameters[0])->where('slug', $slug)->first();
            if ($validate !== NULL) {
                $return = false;
            }
            return $return;
        });

        Validator::extend('is_manual_transfer', function ($attribute, $value, $parameters, $validator) {
            $trs = Transaction::where('transaction_code', $value)->firstOrFail();
            return $trs->bank->type === 'Transfer Bank (Verifikasi Manual)' ? true : false;
        });

        Validator::extend('complained', function ($attribute, $value, $parameters, $validator) {
            $return = true;
            $data = DB::table('complaint')->where('transaction_detail_id', $value)->count();
            if ($data > 0) {
                $return = false;
            }
            return $return;
        });
    }
}
