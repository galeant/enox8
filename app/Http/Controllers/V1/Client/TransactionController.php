<?php

namespace App\Http\Controllers\V1\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Transaction\EvidenceRequest;
use App\Http\Requests\Client\Transaction\ChangeStatusRequest;
use App\Http\Response\Client\TransactionTransformer;
use Illuminate\Support\Facades\File;

use Carbon\Carbon;
use App\Models\V1\Transaction;
use App\Models\V1\TransactionStatus;
use DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewOrderNotification;

class TransactionController extends Controller
{
    // DI ANJURKAN JANGAN DI TARO DI LOKAL PROJECT, LEBIH BAIK MENGGUNAKAN 3rd PARTY SEMACAM AWS, DROPBOX, ETC
    private $evidence_folder = 'public/transaction/evidence/';

    public function getStatusList()
    {
        try {
            $data = TransactionStatus::all();
            return TransactionTransformer::statusList($data);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getList(Request $request)
    {
        try {
            $user = auth()->user();
            // $start_date = $request->input('start_date'/*, Carbon::now()->addMonths(-1)->format('Y-m-d')*/);
            // $end_date = $request->input('end_date'/*, Carbon::now()->format('Y-m-d')*/);
            $per_page = $request->input('per_page', 10);
            $sort_by = $request->input('sort_by', 'created_at');
            $order = $request->input('order', 'desc');

            $data = Transaction::where('user_id', $user->id)
                ->where('status_id', '!=', 11);

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $data = $data->whereDate('created_at', '>=', Carbon::parse($request->start_date))
                    ->whereDate('created_at', '<=', Carbon::parse($request->end_date));
            } else if ($request->filled('start_date') && !$request->filled('end_date')) {
                $data = $data->whereDate('created_at', '>=', Carbon::parse($request->start_date))
                    ->whereDate('created_at', '<=', Carbon::parse($request->start_date)->addMonths(1));
            } else if (!$request->filled('start_date') && $request->filled('end_date')) {
                $data = $data->whereDate('created_at', '>=', Carbon::parse($request->end_date)->addMonths(-1))
                    ->whereDate('created_at', '<=', Carbon::parse($request->end_date));
            }

            if ($request->filled('status')) {
                $transaction_status = TransactionStatus::where('id', $request->status)
                    ->orWhere('name', $request->status)
                    ->firstOrFail();

                // if ($transaction_status->name === 'Complete' || $request->status === 'Complete') {
                //     $data = $data->doesntHave('review');
                // }

                $data = $data->where(function ($q) use ($request) {
                    $q->where('status_id', $request->status)
                        ->orWhere(function ($q1) use ($request) {
                            $q1->whereHas('status', function ($q2) use ($request) {
                                $q2->where('name', $request->status);
                            });
                        });
                });
            }

            $data = $data->orderBy($sort_by, $order)
                ->paginate($per_page);
            return TransactionTransformer::getList($data);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getDetail(Request $request, $transaction_code)
    {
        try {
            $user = auth()->user();
            $data = Transaction::where('user_id', $user->id)
                ->with('review')
                ->where('transaction_code', $transaction_code)
                ->firstOrFail();

            return TransactionTransformer::getDetail($data);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getHistory($transaction_code)
    {
        DB::beginTransaction();
        try {
            $data = Transaction::with('log.status')->where('transaction_code', $transaction_code)->firstOrFail();
            // dd($data->log);
            return TransactionTransformer::getHistory($data->log->sortByDesc('created_at')->values());
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    public function uploadEvidence(EvidenceRequest $request, $transaction_code)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $data = Transaction::where('transaction_code', $transaction_code)
                ->where('user_id', $user->id)
                ->firstOrFail();
            $evidence_name = $data->transaction_code . '-' . time();
            $evidence = imageUpload($this->evidence_folder, $request->evidence, NULL, $evidence_name);
            if ($data->payment_evidence !== NULL) {
                if (File::exists($data->payment_evidence)) {
                    File::delete($data->payment_evidence);
                }
            }
            $data->update([
                'payment_evidence' => $evidence,
                'status_id' => 5
                // 'status_id' => 4
            ]);
            if ($data->status_id === 1) {
                $data->log()->create([
                    'status_id' => 5
                ]);
            }

            DB::commit();
            // // get fcm token admin store
            // $fcm_store_token  = $data->store->user->pluck('fcm_token');
            // if ($fcm_store_token->count() > 0) {
            // sendNotification($fcm_store_token->flatten(), [
            //     'title' => 'New Order',
            //     'message' => 'New Order'
            // ], [
            //     'transaction_code' => $data->transaction_code,
            //     'transaction_id' => $data->id
            // ]);
            // }
            Notification::send($data->store->user, new NewOrderNotification($data, true));

            return TransactionTransformer::getDetail($data->fresh());
        } catch (Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
    }

    public function cancelTransaction(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $data = Transaction::where('status_id', 1)
                ->where('user_id', $user->id)
                ->where(function ($q) use ($request) {
                    $q->where('id', $request->transaction_id)
                        ->orWhere('transaction_code', $request->transaction_code);
                })
                ->firstOrFail();
            $data->update([
                'status_id' => 2
            ]);
            $data->log()->create([
                'status_id' => 2
            ]);
            DB::commit();
            return TransactionTransformer::getDetail($data->fresh());
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
    }

    public function invoice(Request $request, $transaction_code)
    {
        try {
            $user = auth()->user();
            $data = Transaction::where('user_id', $user->id)
                ->with('user.detail', 'detail', 'courier', 'bank')
                ->where('transaction_code', $transaction_code)
                ->firstOrFail();
            // dd($data->toArray());
            return view('emails.invoice', [
                'data' => $data
            ]);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function complete($transaction_code)
    {
        DB::beginTransaction();
        try {

            $data = Transaction::where('transaction_code', $transaction_code)
                ->where('status_id', 7)
                ->firstOrFail();

            $data->update([
                'status_id' => 8
            ]);
            $data->log()->firstOrCreate([
                'status_id' => 8
            ]);

            DB::commit();
            sendNotification($data->user->fcm_token, [
                'title' => 'Order has complete',
                'body' => 'Order has complete'
            ], [
                'transaction_code' => $data->transaction_code,
                'transaction_id' => $data->id
            ]);
            return TransactionTransformer::getDetail($data->fresh());
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }
    // public function testing(Request $request, $action)
    // {
    //     $transaction = Transaction::where('transaction_code', $request->transaction_code)->firstOrFail();
    //     switch ($action) {
    //         case 'to_accept';
    //             if ($transaction->status_id === 1) {
    //                 $transaction->update([
    //                     'status_id' => 4
    //                 ]);
    //                 $transaction->log()->firstOrCreate([
    //                     'status_id' => 4
    //                 ]);

    //                 $notif = [
    //                     'title' => 'Order has acccept',
    //                     'body' => 'Order has acccept'
    //                 ];

    //                 Notification::send($transaction->store->user, new NewOrderNotification($transaction));
    //             } else {
    //                 throw new \Exception('Tidak merubah status transaksi');
    //             }

    //             break;
    //         case 'to_cancel';
    //             if ($transaction->status_id === 1) {
    //                 $transaction->update([
    //                     'status_id' => 2
    //                 ]);
    //                 $transaction->log()->firstOrCreate([
    //                     'status_id' => 2
    //                 ]);

    //                 $notif = [
    //                     'title' => 'Order has cancel',
    //                     'body' => 'Order has cancel'
    //                 ];
    //             } else {
    //                 throw new \Exception('Tidak merubah status transaksi');
    //             }

    //             break;
    //         case 'to_expired';
    //             if ($transaction->status_id === 1) {
    //                 $transaction->update([
    //                     'status_id' => 3
    //                 ]);
    //                 $transaction->log()->firstOrCreate([
    //                     'status_id' => 3
    //                 ]);

    //                 $notif = [
    //                     'title' => 'Order has expired',
    //                     'body' => 'Order has expired'
    //                 ];
    //             } else {
    //                 throw new \Exception('Tidak merubah status transaksi');
    //             }

    //             break;
    //         case 'to_complete':
    //             if ($transaction->status_id === 7) {
    //                 $transaction->update([
    //                     'status_id' => 8
    //                 ]);
    //                 $transaction->log()->firstOrCreate([
    //                     'status_id' => 8
    //                 ]);

    //                 $notif = [
    //                     'title' => 'Order has complete',
    //                     'body' => 'Order has complete'
    //                 ];
    //             } else {
    //                 throw new \Exception('Tidak merubah status transaksi');
    //             }
    //             break;
    //         case 'to_delivery':
    //             if ($transaction->status_id === 4) {
    //                 $transaction->update([
    //                     'status_id' => 6
    //                 ]);
    //                 $transaction->log()->firstOrCreate([
    //                     'status_id' => 6
    //                 ]);

    //                 $notif = [
    //                     'title' => 'Order has on delivery',
    //                     'body' => 'Order has on delivery'
    //                 ];
    //             } else {
    //                 throw new \Exception('Tidak merubah status transaksi');
    //             }
    //             break;

    //         case 'to_delivery_success':
    //             if ($transaction->status_id === 6) {
    //                 $transaction->update([
    //                     'status_id' => 7
    //                 ]);
    //                 $transaction->log()->firstOrCreate([
    //                     'status_id' => 7
    //                 ]);

    //                 $notif = [
    //                     'title' => 'Order has delivery success',
    //                     'body' => 'Order has delivery success'
    //                 ];
    //             } else {
    //                 throw new \Exception('Tidak merubah status transaksi');
    //             }
    //             break;
    //         case 'to_complete':
    //             if ($transaction->status_id === 8) {
    //                 $transaction->update([
    //                     'status_id' => 8
    //                 ]);
    //                 $transaction->log()->firstOrCreate([
    //                     'status_id' => 8
    //                 ]);

    //                 $notif = [
    //                     'title' => 'Order has complete',
    //                     'body' => 'Order has complete'
    //                 ];
    //             } else {
    //                 throw new \Exception('Tidak merubah status transaksi');
    //             }
    //             break;
    //         case 'to_complaint':
    //             if ($transaction->status_id === 8) {
    //                 $transaction->update([
    //                     'status_id' => 9
    //                 ]);
    //                 $transaction->log()->firstOrCreate([
    //                     'status_id' => 9
    //                 ]);
    //                 $notif = [
    //                     'title' => 'Order has complained',
    //                     'body' => 'Order has complained'
    //                 ];
    //             } else {
    //                 throw new \Exception('Tidak merubah status transaksi');
    //             }
    //             break;
    //         default:
    //             throw new \Exception('Status not found');
    //     }
    //     $payload = [
    //         'transaction_code' => $transaction->transaction_code,
    //         'transaction_id' => $transaction->id
    //     ];
    //     sendNotification($transaction->user->fcm_token, $notif, $payload);
    //     return $transaction->fresh();
    // }
}
