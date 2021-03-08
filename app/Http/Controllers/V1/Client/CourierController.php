<?php

namespace App\Http\Controllers\V1\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\V1\Courier;
use App\Http\Response\Client\CourierTransformer;
use App\Http\Requests\Client\Courier\GetRequest;
use Exception;
use GuzzleHttp\Client;
use DB;
use App\Models\V1\Cart;
use App\Models\V1\Store;
use App\Models\V1\Address;

class CourierController extends Controller
{
    public function getList(GetRequest $request)
    {
        DB::beginTransaction();
        try {
            $destination = Address::where('id', $request->address_id)->firstOrFail();
            $code = ['jne', 'pos', 'tiki'];
            $cart = Cart::whereIn('id', $request->cart_id)->get();
            $store = Store::whereHas('product', function ($q) use ($cart) {
                $q->whereIn('id', $cart->pluck('product_id'));
            })->first();
            $weight = $cart->pluck('product.weight')->sum();
            $return = [];
            // dd($store);
            foreach ($code as $c) {
                $req_param = [
                    'origin' => $store->regency_id,
                    'destination' => $destination->regency_id,
                    'weight' => $weight,
                    'courier' => $c
                ];
                $res = rajaongkir('POST', 'cost', NULL, $req_param);
                $res = $res->rajaongkir->results;
                // dd($res);

                foreach ($res as $rs) {
                    $return[$rs->code] = [
                        'code' => $rs->code,
                        'name' => $rs->name
                    ];
                    foreach ($rs->costs as $ct) {
                        $cre = Courier::where(['code' => $rs->code, 'service_name' => $ct->service])->first();
                        if ($cre == NULL) {
                            $model = new Courier;
                            $model->code = $rs->code;
                            $model->service_name = $ct->service;
                            $model->name = $rs->name;
                            $model->save();
                        }
                        $p = [
                            'name' => $ct->service,
                            'description' => $ct->description,
                            'value' => 0,
                            'etd' => '',
                            'note' => ''
                        ];

                        if (isset($ct->cost[0])) {
                            $cost = $ct->cost[0];
                            $dy = 0;
                            if (strpos($cost->etd, 'JAM') === false) {
                                $etd = str_replace("HARI", '', $cost->etd);
                                $etd = str_replace(" ", '', $etd);
                                $etd = explode('-', $etd);
                                for ($i = 0; $i < count($etd); $i++) {
                                    if ($etd[$i] > $dy) {
                                        $dy = $etd[$i] . ' day';
                                    }
                                }
                            }
                            $p['value'] = $cost->value;
                            $p['etd'] = $dy === 0 ? 'Same day' : $dy;
                            $p['note'] = $cost->note;
                        }
                        $return[$rs->code]['service'][] = $p;
                    }
                }
            }
            // dd('wdwdw');

            // $data = Courier::get();
            DB::commit();
            return CourierTransformer::general('Get courier success', $return);
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
    }
}
