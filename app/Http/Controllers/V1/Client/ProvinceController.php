<?php

namespace App\Http\Controllers\V1\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\V1\Province;
use App\Http\Response\Client\ProvinceTransformer;

class ProvinceController extends Controller
{
    public function getList(Request $request)
    {
        try {
            $data = new Province;
            if ($request->filled('id')) {
                $data = $data->where('id', $request->id);
            }
            if ($request->filled('name')) {
                $data = $data->where('name', 'ilike', '%' . $request->name . '%');
            }

            if ($request->filled('country_id')) {
                $data = $data->where('country_id', $request->country_id);
            }

            if ($request->filled('country_name')) {
                $data = $data->whereHas('country', function ($q) use ($request) {
                    $q->where('name', 'ilike', '%' . $request->country_name . '%');
                });
            }

            $data = $data->get();
            return ProvinceTransformer::list($data);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
