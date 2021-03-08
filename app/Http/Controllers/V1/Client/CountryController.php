<?php

namespace App\Http\Controllers\V1\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\V1\Country;
use App\Http\Response\Client\CountryTransformer;

class CountryController extends Controller
{
    public function getList(Request $request)
    {
        try {
            $data = new Country;
            if ($request->filled('id')) {
                $data = $data->where('id', $request->id);
            }
            if ($request->filled('name')) {
                $data = $data->where('name', 'ilike', '%' . $request->name . '%');
            }

            $data = $data->get();
            return CountryTransformer::list($data);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
