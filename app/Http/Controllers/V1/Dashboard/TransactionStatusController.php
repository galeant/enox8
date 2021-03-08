<?php

namespace App\Http\Controllers\V1\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Requests\Dashboard\Transaction\Status\CreateRequest;
use App\Http\Response\Dashboard\TransactionStatusTransformer;

use App\Models\V1\TransactionStatus;
use Carbon\Carbon;
use DB;

class TransactionStatusController extends Controller
{
    public function getData(Request $request, $id = null)
    {
        try {
            $data = new TransactionStatus;
            if ($id !== NULL) {
                $data = $data->where('id', $id)->firstOrFail();
                return TransactionStatusTransformer::detail($data);
            } else {
                $data = $data->all();
                return TransactionStatusTransformer::list($data);
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function create(CreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = TransactionStatus::create([
                'name' => $request->name
            ]);
            DB::commit();
            return TransactionStatusTransformer::detail($data);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    public function update(CreateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = TransactionStatus::where('id', $id)->firstOrFail();
            $data->update([
                'name' => $request->name
            ]);

            DB::commit();
            return TransactionStatusTransformer::detail($data->fresh());
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $data = TransactionStatus::where('id', $id)->firstOrFail();
            $data->delete();
            DB::commit();
            return TransactionStatusTransformer::delete($data);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }
}
