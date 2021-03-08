<?php

namespace App\Http\Controllers\V1\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\V1\Regency;
use App\Http\Response\Client\RegencyTransformer;

class RegencyController extends Controller
{
    public function getList(Request $request)
    {
        try {
            $data = new Regency;
            if ($request->filled('id')) {
                $data = $data->where('id', $request->id);
            }

            if ($request->filled('country_id')) {
                $data = $data->whereHas('province', function ($q) use ($request) {
                    $q->where('country_id', $request->country_id);
                });
            }

            if ($request->filled('province_id')) {
                $data = $data->where('province_id', $request->province_id);
            }

            if ($request->filled('name')) {
                $data = $data->where('name', 'ilike', '%' . $request->name . '%');
            }

            if ($request->filled('country_name')) {
                $data = $data->whereHas('province.country', function ($q) use ($request) {
                    $q->where('name', 'ilike', '%' . $request->country_name . '%');
                });
            }

            if ($request->filled('province_name')) {
                $data = $data->whereHas('province', function ($q) use ($request) {
                    $q->where('name', 'ilike', '%' . $request->province_name . '%');
                });
            }

            $data = $data->get();
            return RegencyTransformer::list($data);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
