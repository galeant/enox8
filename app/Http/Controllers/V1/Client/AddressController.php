<?php

namespace App\Http\Controllers\V1\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\V1\Address;
use App\Http\Requests\Client\Address\CreateRequest;
use App\Http\Requests\Client\Address\DeleteRequest;
use App\Http\Response\Client\AddressTransformer;
use DB;

class AddressController extends Controller
{
    public function getData()
    {
        try {
            $user = auth()->user();
            $address = $user->address;
            return AddressTransformer::getList($address);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function create(CreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $is_main_address = false;
            if ($request->main_address === true) {
                $main_address = $user->address->filter(function ($v) {
                    if ($v->main_address === true) {
                        return $v;
                    }
                })->first();
                if ($main_address !== NULL) {
                    Address::where('id', $main_address->id)->update([
                        'main_address' => false
                    ]);
                }

                $is_main_address = true;
            }
            $user->address()->create([
                'address' => $request->address,
                'country_id' => $request->country_id,
                'province_id' => $request->province_id,
                'regency_id' => $request->regency_id,
                'district_id' => $request->district_id,
                'village_id' => $request->village_id,
                'alias' => $request->alias,
                'recipient_name' => $request->recipient_name,
                'phone' => $request->phone,
                'postal_code' => $request->postal_code,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'main_address' => $is_main_address
            ]);

            $address = Address::where('user_id', $user->id)->get();
            DB::commit();
            return AddressTransformer::getList($address);
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
    }

    public function update(CreateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $address = Address::where('id', $id)->firstOrFail();
            $address->update([
                'address' => $request->address,
                'country_id' => $request->country_id,
                'province_id' => $request->province_id,
                'regency_id' => $request->regency_id,
                'district_id' => $request->district_id,
                'village_id' => $request->village_id,
                'alias' => $request->alias,
                'recipient_name' => $request->recipient_name,
                'phone' => $request->phone,
                'postal_code' => $request->postal_code,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'main_address' => $request->main_address
            ]);

            DB::commit();
            return AddressTransformer::getList($user->address->fresh());
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
    }

    public function delete(DeleteRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            Address::whereIn('id', $request->address_id)->delete();
            DB::commit();
            return AddressTransformer::getList($user->address->fresh());
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
    }
}
