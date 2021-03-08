<?php

namespace App\Http\Controllers\V1\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\V1\Transaction;
use App\Models\V1\Complaint;
use App\Models\V1\TransactionStatus;

use App\Http\Requests\Dashboard\Transaction\ChangeStatusRequest;

use App\Http\Response\Dashboard\TransactionTransformer;
use App\Http\Response\Dashboard\ComplaintTransformer;
use Exception;
use DB;

class TransactionController extends Controller
{
    private $evidence_folder = 'public/transaction/evidence/';
    public function getData(Request $request, $id = NULl)
    {
        try {
            $user = auth()->user();
            $data = Transaction::with('detail', 'status', 'log', 'user.detail')->where('store_id', $user->store_id);
            if ($request->filled('customer_id')) {
                $data = $data->where('user_id', $request->customer_id);
            }

            if ($request->filled('status')) {
                $data = $data->where('status_id', $request->status);
            }

            if ($id !== NULL) {
                $data = $data->where('id', $id)->firstOrFail();
                return TransactionTransformer::detail($data);
            } else {
                $per_page = $request->input('per_page', 10);
                $page = $request->input('page', 1);
                $order_by = $request->input('order_by', 'created_at');
                $sort = $request->input('sort', 'asc');
                $data = $data->orderBy($order_by, $sort)->paginate($per_page);
                return TransactionTransformer::list($data);
            }
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function changeStatus(ChangeStatusRequest $request, $status, $id)
    {
        $transaction_status = TransactionStatus::all()->pluck('name')->transform(function ($v) {
            return str_slug($v, '_');
        })->toArray();
        if (!in_array($status, $transaction_status)) {
            throw new \Exception('Status not exist');
        }
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $data = Transaction::where('store_id', $user->store_id)->where('id', $id);
            switch ($status) {
                case 'decline':
                    $data = $data->first();
                    if ($data->status_id === 1 || $data->status_id === 4) {
                        $status_id = 10;
                        if ($data->status_id === 4 && $data->bank_account_type === 'Transfer Bank (Verifikasi Manual)' && $request->filled('payment_return_evidence')) {
                            $evidence_name = $data->transaction_code . '-' . time();
                            $data->update([
                                'payment_return_evidence' => imageUpload($this->evidence_folder, $request->payment_return_evidence, NULL, $evidence_name)
                            ]);
                        } else if ($data->status_id === 4 && $data->bank_account_type === 'Virtual Account') {
                            dd('no action');
                        }
                        $status_id = [11];
                        // // FCM PAYLOAD
                        $notif = [
                            'title' => 'Order decline',
                            'body' => 'Order declined by admin, please contact admin for detail'
                        ];
                        // $payload = [
                        //     'transaction_code' => $data->transaction_code,
                        //     'transaction_id' => $data->id
                        // ];
                        // //
                    } else {
                        throw new \Exception('Data not found');
                    }
                    break;
                    // case 'payment_accept':
                    //     $data = $data->where('status_id', 5)->whereNotNull('payment_evidence')->firstOrFail();
                    //     // $data->update('transaction_code',''); // Input transaction code aka inv number
                    //     $status_id = [4];
                    //     // FCM PAYLOAD
                    //     $notif = [
                    //         'title' => 'Order accept',
                    //         'body' => 'Order has been accepted by admin'
                    //     ];
                    //     // $payload = [
                    //     //     'transaction_code' => $data->transaction_code,
                    //     //     'transaction_id' => $data->id
                    //     // ];
                    //     // //
                    //     break;
                case 'on_packing':
                    $data = $data->where(function ($q) {
                        $q->where('status_id', 5);/*->whereNotNull('payment_evidence');*/
                    })->orWhere('status_id', 4)->firstOrFail();
                    $status_id = [4, 6];
                    if ($data->bank->type === 'Virtual Account') {
                        $status_id = [6];
                    }
                    // // FCM PAYLOAD
                    $notif = [
                        'title' => 'Order has been packing',
                        'body' => 'Order has been packing by seller'
                    ];
                    // $payload = [
                    //     'transaction_code' => $data->transaction_code,
                    //     'transaction_id' => $data->id
                    // ];
                    // //
                    break;
                case 'on_delivery':
                    $data = $data->where('status_id', 6)->firstOrFail();
                    $status_id = [7];
                    // // FCM PAYLOAD
                    $notif = [
                        'title' => 'Order has been send',
                        'body' => 'Order has been send by seller'
                    ];

                    break;
            }

            foreach ($status_id as $sid) {
                $data->update([
                    'status_id' => $sid
                ]);
                $data->log()->create([
                    'status_id' => $sid
                ]);
            }

            DB::commit();
            $payload = [
                'transaction_code' => $data->transaction_code,
                'transaction_id' => (int) $data->id,
                'status_id' => (int) $data->status_id,
                'status_name' => $data->status->name
            ];

            sendNotification($data->user->fcm_token, $notif, $payload);
            return TransactionTransformer::detail($data->fresh());
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
    }
}
