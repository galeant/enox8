<?php

namespace App\Http\Response\Client;

use Carbon\Carbon;

class TransactionTransformer
{
    public static function getList($response)
    {
        if ($response instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $response->transform(function ($v) {
                return self::reformer($v);
            });
            $data = [
                'data' => $response->items(),
                'current_page' => $response->currentPage(),
                'next_page_url' => $response->nextPageUrl(),
                'prev_page_url' => $response->previousPageUrl(),
                'total' => $response->total()
            ];
        } else {
            $response->transform(function ($v) {
                return self::reformer($v);
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
        // self::reformer($response);
        $return = self::reformer($response);
        // dd('qdwqwd');
        $return['courier'] = [
            'company' => $response->courier_name,
            'service_name' => $response->courier_service_name,
            'price' => $response->courier_price,
            'delivery_duration' => $response->delivery_duration === 0 ? 'Same day' : $response->delivery_duration . ' Day',
            'resi_number' => (string) $response->resi_number,
            'recipient_name' => $response->recipient_name,
            'recipient_address' => $response->recipient_address,
            'recipient_country' => $response->recipient_country,
            'recipient_province' => $response->recipient_province,
            'recipient_regency' => $response->recipient_regency,
            'recipient_district' => $response->recipient_district,
            'recipient_phone' => $response->recipient_phone,
            'recipient_postal_code' => $response->recipient_postal_code,
            'recipient_latitude' => $response->recipient_latitude,
            'recipient_longitude' => $response->recipient_longitude
        ];
        $return['payment'] = [
            'total_price' => $response->total_price,
            'discount_value' => (string) $response->product_discount_price,
            'total_payment' => $response->total_payment,
            'courier_price' => $response->courier_price,
            // 'insurance_fee' => '',
            'payment_bank' => $response->bank_name,
            'payment_type' => $response->bank->type,
            'payment_account_number' => $response->bank->account_number,
            'payment_time_limit' => $response->status_id !== 1 ? '' : Carbon::parse($response->created_at)->addDays(3)->format('Y-m-d H:i:s'),
            'payment_evidence' => isset($response->payment_evidence) ? asset($response->payment_evidence) : ''
        ];
        if (isset($response->voucherUsage)) {
            // dd($response->voucherUsage->total_payment_format);
            $return['total_price'] = $response->voucherUsage->total_price;
            $return['pembayaran']['discount_value'] = $response->product_discount_price + $response->voucherUsage->voucher_discount_value;
            $return['total_payment'] = $response->voucherUsage->total_payment;
        }

        return response()->json([
            'code' => 200,
            'message' => 'Get detail success',
            'result' => $return
        ], 200);
    }

    public static function statusList($response)
    {
        return response()->json([
            'code' => 200,
            'message' => 'Get transaction status list success',
            'result' => $response
        ]);
    }

    public static function getHistory($response)
    {
        $response->transform(function ($v) {
            return [
                'status_id' => $v->status_id,
                'name' => $v->status->name,
                'created_at' => $v->created_at->format('Y-m-d H:i:s')
            ];
        });
        return response()->json([
            'code' => 200,
            'message' => 'Get list success',
            'result' => $response
        ], 200);
    }

    private static function reformer($response)
    {
        $return = [
            'id' => $response->id,
            'transaction_code' => $response->transaction_code,
            'created_at' => $response->created_at->format('Y-m-d'),
            'status' => $response->status->name,
            'total_price' => $response->total_price,
            'total_payment' => $response->total_payment,
            'discount_value' => (string) $response->product_discount_value,
            'store_name' => $response->store_name,
            'invoice_url' => '',
            'delivery_duration' => $response->delivery_duration === 0 ? 'Same day' : $response->delivery_duration . ' Day',
            'detail' => [],
            // 'already_reviewed' => false,
            // 'already_complained' => false,
            'auto_complete' => '',
            'payment_bank' => $response->bank_name,
            'payment_type' => $response->bank->type,
        ];
        // dd($return);
        if (isset($response->voucherUsage)) {
            // dd($response->voucherUsage->total_payment_format);
            $return['total_payment'] = $response->voucherUsage->total_payment;
            $return['discount_value'] = $response->product_discount_value + $response->voucherUsage->voucher_discount_value;
        }
        // if ($response->status_id === 1 && $response->payment_evidence !== NULL) {
        //     $return['status'] = 'On Check Payment';
        // }
        // dd($return);
        foreach ($response->detail as $detail) {
            $reviewed = false;
            $complained = false;
            if ($detail->review) {
                $reviewed = true;
            }

            if ($detail->complaint) {
                $complained = true;
            }
            $return['detail'][] = [
                'id' => $detail->id,
                'product_id' => $detail->product_id,
                'type_id' => $detail->type_id,
                'name' => $detail->product_name,
                'qty' => $detail->qty,
                'price' => $detail->total_price / $detail->qty,
                'total_price' => $detail->total_price,
                'already_reviewed' => $reviewed,
                'already_complained' => $complained
                // 'total_payment' => $detail->total_payment,
            ];
        }
        if ($response->status->name === 'Payment Accept') {
            $return['invoice_url'] = route('client.transaction.invoice', ['transaction_code' => $response->transaction_code]);
        }

        if ($response->status_id === 6) {
            // Config store policy
            $policy = $response->delivery_duration + $response->store->auto_complete_policy;
            $return['auto_complete'] = Carbon::parse($response->updated_at)->addDays($policy)->format('Y-m-d');
        }
        return $return;
    }
}
