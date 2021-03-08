<?php

namespace App\Http\Controllers\V1\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Complaint\CreateRequest;
use App\Http\Requests\Client\Complaint\UpdateRequest;
use App\Models\V1\Transaction;
use App\Models\V1\Complaint;
use App\Models\V1\ComplaintStatus;
use App\Http\Response\Client\ComplaintTransformer;
use DB;


class ComplaintController extends Controller
{
    private $folder_path = 'public/complaint/evidence/';
    private $product_return_path = 'public/complaint/product_return/';

    public function create(CreateRequest $request)
    {
        $delete_path = [];
        DB::beginTransaction();
        try {
            $data = Transaction::where('transaction_code', $request->transaction_code)
                ->with(['detail' => function ($q) use ($request) {
                    $q->where('id', $request->transaction_detail_id);
                }])
                ->firstOrFail();
            // $data->update([
            //     'status_id' => 9
            // ]);
            // $data->log()->create([
            //     'status_id' => 9
            // ]);

            $evidence = [];
            foreach ($request->evidence as $evi) {
                $im = imageUpload($this->folder_path, $evi);
                $evidence[] = $im;
                $delete_path[] = str_replace('storage', 'public', $im);
            }

            $cash_return_value = ($data->detail[0]->product_price - $data->detail[0]->product_discount) * $request->qty;
            $complaint = Complaint::create([
                'transaction_id' => $data->id,
                'complaint' => $request->complaint,
                'status_id' => 1,
                'cash_return_value' => $cash_return_value,
                'complaint_evidence' => json_encode($evidence),
                'qty' => $request->qty,
                'transaction_detail_id' => $request->transaction_detail_id
            ]);

            DB::commit();
            return ComplaintTransformer::general('Complaint has been send please wait for response');
        } catch (Exception $e) {
            DB::rollback();
            foreach ($delete_path as $dp) {
                Storage::delete($dp);
            }
            throw new \Exception($e->getMessage());
        }
    }

    public function getList(Request $request, $id = NULL)
    {
        try {
            $user = auth()->user();
            $per_page = $request->input('per_page', 10);
            $order_by = $request->input('order_by', 'created_at');
            $sort = $request->input('sort', 'DESC');
            $data = Complaint::with('transaction', 'transactionDetail', 'status')->whereHas('transaction', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });

            if ($request->filled('status')) {
                $complain_status = ComplaintStatus::where('id', $request->status)
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

            if ($id !== NULL) {
                $data = $data->where('id', $id)->firstOrFail();
                return ComplaintTransformer::detail($data);
            }
            $data = $data->orderBy($order_by, $sort)->paginate($per_page);
            return ComplaintTransformer::list($data);
        } catch (Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function update(UpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $data = Complaint::where(['id' => $id, 'status_id' => 6])->whereHas('transaction', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->firstOrFail();
            $status_id = 4;
            if ($data->compensate_type === 'product_return') {
                $status_id = 7;
            }
            $data->update([
                'user_return_evidence' => imageUpload($this->product_return_path, $request->evidence),
                'status_id' => $status_id
            ]);
            DB::commit();
            return ComplaintTransformer::detail($data->fresh(), 'Upload bukti kirim berhasil');
        } catch (Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
    }

    public function getStatusList(Request $request)
    {
        try {
            $data = ComplaintStatus::all();
            return ComplaintTransformer::statusList($data);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
