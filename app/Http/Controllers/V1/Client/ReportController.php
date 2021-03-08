<?php

namespace App\Http\Controllers\V1\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\V1\Report;
use DB;
use App\Http\Response\Client\ReportTransformer;
use App\Http\Requests\Client\Report\CreateRequest;

class ReportController extends Controller
{
    public function report(CreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $data = Report::create([
                'user_id' => $user->id,
                'relation_id' => $request->relation_id,
                'relation_type' => $request->input('relation_type', 'comment'),
                'reason' => $request->reason
            ]);
            DB::commit();
            return ReportTransformer::general('Report has been submit');
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
    }
}
