<?php

namespace App\Http\Controllers\V1\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\V1\Village;
use App\Http\Response\Client\VillageTransformer;

class VillageController extends Controller
{
    public function getList(Request $request)
    {
        try {
            $data = new Village;
            if ($request->filled('id')) {
                $data = $data->where('id', $request->id);
            }
            if ($request->filled('country_id')) {
                $data = $data->whereHas('district.regency.province', function ($q) use ($request) {
                    $q->where('country_id', $request->country_id);
                });
            }
            if ($request->filled('province_id')) {
                $data = $data->whereHas('district.regency', function ($q) use ($request) {
                    $q->where('province_id', $request->province_id);
                });
            }
            if ($request->filled('regency_id')) {
                $data = $data->whereHas('district', function ($q) use ($request) {
                    $q->where('regency_id', $request->regency_id);
                });
            }
            if ($request->filled('district_id')) {
                $data = $data->whereHas('district', function ($q) use ($request) {
                    $q->where('id', $request->district_id);
                });
            }


            if ($request->filled('country_name')) {
                $data = $data->whereHas('district.regency.province.country', function ($q) use ($request) {
                    $q->where('name', 'ilike', '%' . $request->country_name . '%');
                });
            }
            if ($request->filled('province_name')) {
                $data = $data->whereHas('district.regency.province', function ($q) use ($request) {
                    $q->where('name', 'ilike', '%' . $request->province_name . '%');
                });
            }
            if ($request->filled('regency_name')) {
                $data = $data->whereHas('district.regency', function ($q) use ($request) {
                    $q->where('name', 'ilike', '%' . $request->regency_name . '%');
                });
            }
            if ($request->filled('district_name')) {
                $data = $data->whereHas('district', function ($q) use ($request) {
                    $q->where('name', 'ilike', '%' . $request->district_name . '%');
                });
            }

            $data = $data->get();
            return VillageTransformer::list($data);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
