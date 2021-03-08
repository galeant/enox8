<?php

namespace App\Http\Controllers\V1\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\V1\Complaint;
use App\Models\V1\ComplaintLog;
use App\Models\V1\Transaction;
use App\Models\V1\Type;
use App\Http\Response\Dashboard\ComplaintTransformer;
use App\Http\Requests\Dashboard\Complaint\UpdateRequest;
use DB;

class ComplaintController extends Controller
{
    private $cash_return_folder = 'public/complaint/cash/';
    private $product_return_folder = 'public/complaint/product/';

    public function getList(Request $request, $id = NULl)
    {
        try {
            $user = auth()->user();
            $per_page = $request->input('per_page', 10);
            $page = $request->input('page', 1);
            $order_by = $request->input('order_by', 'created_at');
            $sort = $request->input('sort', 'DESC');

            $data = Complaint::with('transaction', 'transactionDetail', 'status')->whereHas('transaction', function ($q) use ($user) {
                $q->where('store_id', $user->store_id);
            });

            if ($request->filled('status')) {
                $data = $data->where('status', $request->status);
            }
            if ($id !== NULL) {

                $data = $data->with('transaction.user.detail')->where('id', $id)->firstOrFail();
                return ComplaintTransformer::detail($data);
            } else {
                $data = $data->orderBy($order_by, $sort)->paginate($per_page);
                return ComplaintTransformer::list($data);
            }
        } catch (Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function update(UpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $log_status = [3];
            $fill = [
                'status_id' => 3,
                'compensate_type' => $request->compensate_type,
            ];
            $data = Complaint::where('id', $id)->first();
            // if ($data->status_id == 6) {
            //     return ComplaintTransformer::detail($data, 'Tidak dapat melanjutkan proses, masih menunggu user mengirim balik barang');
            // }


            if ($request->status === 'decline') {
                $fill['status_id'] = 2;
                $log_status = [2, 9];
                // $data->update(['status_id' => 2]);
                // $data->log()->create([
                //     'status_id' => 2
                // ]);
                // $data->log()->create([
                //     'status_id' => 9
                // ]);
            } else {
                if ($request->need_return === true) {
                    $fill['status_id'] = 6;
                    $log_status[] = 6;
                    // $data->update(['status_id' => 6]);
                    // $data->log()->create([
                    //     'status_id' => 6
                    // ]);
                } else {
                    switch ($request->compensate_type) {
                        case 'cash_return':
                            $fill['status_id'] = 4;
                            $log_status[] = 4;
                            // $data->log()->create([
                            //     'status_id' => 4
                            // ]);
                            break;
                        case 'product_return':
                            $fill['status_id'] = 7;
                            $log_status[] = 7;
                            // $data->log()->create([
                            //     'status_id' => 7
                            // ]);
                            break;
                    }
                }


                if (($data->status_id == 4 || $data->status_id == 7) && $request->filled('evidence')) {
                    $folder_upload = $this->cash_return_folder;
                    if ($data->status_id == 7) {
                        $folder_upload = $this->product_return_folder;
                    }
                    $fill['store_evidence'] = imageUpload($folder_upload, $request->evidence);

                    $tr = Transaction::where('id', $data->transaction_id)->with(['detail' => function ($q) use ($data) {
                        $data->where('id', $data->transaction_detail_id);
                    }])->firstOrFail();

                    $transaction_return = Transaction::create([
                        'user_id' => $tr->user_id,
                        'transaction_code' => 'PIKOTRANS-' . time(),
                        'total_price' => $data->cash_return_value,
                        'total_price_discount' => $tr->total_price_discount,
                        'total_voucher_discount' => $tr->total_voucher_discount,
                        'total_product_discount' => 0,
                        'total_payment' => 0,

                        'buyer_bank_account_name' => $tr->buyer_bank_account_name,
                        'buyer_bank_account_number' => $tr->buyer_bank_account_number,

                        'bank_id' => $tr->bank_id,
                        'bank_name' => $tr->bank_name,
                        'bank_account_type' => $tr->bank_account_type,
                        'store_bank_account_number' => $tr->store_bank_account_number,

                        'unique_code' => $tr->unique_code,
                        'payment_evidence' => $tr->payment_evidence,

                        'courier_id' => $tr->courier_id,
                        'courier_code' => $tr->courier_code,
                        'courier_name' => $tr->courier_name,
                        'courier_service_name' => $tr->courier_service_name,
                        'courier_price' => $tr->courier_price,
                        'delivery_duration' => $tr->delivery_duration,

                        'insurance_fee' => $tr->insurance_fee,
                        // 'is_complain' => true
                        'payment_evidence' => $tr->payment_evidence,

                        'status_id' => 11,
                        'store_id' => $tr->store_id,
                        'store_name' => $tr->store_name,

                        'resi_number' => $request->resi_number,
                        'recipient_name' => $tr->recipient_name,
                        'recipient_address' => $tr->recipient_address,
                        'recipient_country' => $tr->recipient_country,
                        'recipient_province' => $tr->recipient_province,
                        'recipient_regency' => $tr->recipient_regency,
                        'recipient_district' => $tr->recipient_district,
                        'recipient_village' => $tr->recipient_village,
                        'recipient_phone' => $tr->recipient_phone,
                        'recipient_postal_code' => $tr->recipient_postal_code,
                        'recipient_latitude' => $tr->recipient_latitude,
                        'recipient_longitude' => $tr->recipient_longitude,

                        'sender_name' => $tr->sender_name,
                        'sender_address' => $tr->sender_address,
                        'sender_country' => $tr->sender_country,
                        'sender_province' => $tr->sender_province,
                        'sender_regency' => $tr->sender_regency,
                        'sender_district' => $tr->sender_district,
                        'sender_village' => $tr->sender_village,
                        'sender_phone' => $tr->sender_phone,
                        'sender_email' => $tr->sender_email,
                        'sender_postal_code' => $tr->sender_postal_code
                    ]);
                    foreach ($tr->detail as $td) {
                        $transaction_return->detail()->create([
                            'product_id' => $td->product_id,
                            'product_slug' => $td->product_slug,
                            'product_name' => $td->product_name,
                            'product_price' => $td->product_price,
                            'product_discount' => $td->product_discount,
                            // 'type_slug' => $pd->type[0]->slug,
                            'type_id' => $td->type_id,
                            'type_name' => $td->type_name,
                            'qty' => $data->qty,
                            'total_price' => $data->cash_return_value,
                            'total_discount' => 0,
                            'total_payment' => 0
                        ]);
                        // if ($request->return_to_inventory === false) {
                        //     $type = Type::where('id', $td->type_id)->firstOrFail();
                        //     $substract_qty = $type->qty - $td->qty;
                        //     $type->update([
                        //         'stock' => $substract_qty
                        //     ]);
                        // }
                    }

                    $tr->update([
                        'status_id' => 8
                    ]);

                    $tr->log()->create(['status_id' => 8]);

                    $fill['return_transaction_id'] = $transaction_return->id;
                    $fill['status_id'] = 9;
                    $log_status = [9];
                    // $data->log()->create(['status_id' => 9]);
                }
            }

            $data->update($fill);
            foreach ($log_status as $lg) {
                $data->log()->create([
                    'status_id' => $lg
                ]);
            }
            DB::commit();
            return ComplaintTransformer::detail($data->fresh());
        } catch (Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMesaage());
        }
    }
}
