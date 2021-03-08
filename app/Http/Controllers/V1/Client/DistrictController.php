<?php

namespace App\Http\Controllers\V1\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\V1\District;
use App\Http\Response\Client\DistrictTransformer;

class DistrictController extends Controller
{
    public function getList(Request $request)
    {
        try {
            $data = new District;
            if ($request->filled('id')) {
                $data = $data->where('id', $request->id);
            }
            if ($request->filled('country_id')) {
                $data = $data->whereHas('regency.province', function ($q) use ($request) {
                    $q->where('country_id', $request->country_id);
                });
            }
            if ($request->filled('province_id')) {
                $data = $data->whereHas('regency', function ($q) use ($request) {
                    $q->where('province_id', $request->province_id);
                });
            }
            if ($request->filled('regency_id')) {
                $data = $data->where('regency_id', $request->regency_id);
            }

            if ($request->filled('name')) {
                $data = $data->where('name', 'ilike', '%' . $request->name . '%');
            }

            if ($request->filled('country_name')) {
                $data = $data->whereHas('regency.province.country', function ($q) use ($request) {
                    $q->where('name', 'ilike', '%' . $request->country_name . '%');
                });
            }
            if ($request->filled('province_name')) {
                $data = $data->whereHas('regency.province', function ($q) use ($request) {
                    $q->where('name', 'ilike', '%' . $request->province_name . '%');
                });
            }
            if ($request->filled('regency_name')) {
                $data = $data->whereHas('regency', function ($q) use ($request) {
                    $q->where('name', 'ilike', '%' . $request->regency_name . '%');
                });
            }

            $data = $data->get();
            return DistrictTransformer::list($data);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
