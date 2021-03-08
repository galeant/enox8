<?php

namespace App\Http\Controllers\V1\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Carbon\Carbon;

use App\Http\Requests\Dashboard\Store\CreateRequest;
use App\Http\Requests\Dashboard\Store\UpdateRequest;
use App\Http\Response\Dashboard\StoreTransformer;
use App\Notifications\Client\RegisterNotification;

use App\Models\V1\User;
use App\Models\V1\UserDetail;
use App\Models\V1\Store;
use App\Models\V1\Role;
use Auth;
use DB;

class StoreController extends Controller
{
    private $folder_logo = 'public/logo/';

    public function getData(Request $request, $id = null)
    {
        try {
            $user = Auth::user();
            if ($user->can_access_admin && $user->store_id !== NULL) {
                $id = $user->store_id;
            }
            $per_page = $request->input('per_page', 10);
            $data = new Store;

            if ($request->filled('filter')) {
                foreach ($request->filter as $ft) {
                    switch ($ft['operator']) {
                        case 'eq':
                            $opr = '=';
                            break;
                        case 'ne':
                            $opr = '!=';
                            break;
                        case 'lt':
                            $opr = '<';
                            break;
                        case 'le':
                            $opr = '<=';
                            break;
                        case 'gt':
                            $opr = '>';
                            break;
                        case 'ge':
                            $opr = '>=';
                            break;
                    }
                    $data  = $data->where($ft['field'], $opr, $ft['value']);
                }
            }

            $order = 'name';
            if (isset($request->sort['field'])) {
                $order = $request->sort['field'];
            }

            $sort = 'desc';
            if (isset($request->sort['value'])) {
                $sort = $request->sort['value'];
            }
            // if($request->filled('name')){
            //     $data = $data->where('name','ilike','%'.$request->name.'%');
            // }

            // if($request->filled('id')){
            //     $ids = explode(',',$request->id);
            //     $data = $data->whereIn('id',$ids);
            // }
            if ($id != null) {
                $data = $data->where('id', $id)->first();
                return StoreTransformer::detail($data);
            } else if ($request->filled('all')) {
                $data = $data->get();
                return StoreTransformer::list($data);
            } else {
                $data = $data->orderBy($order, $sort)->paginate($per_page);
                $data->appends($request->all());
                return StoreTransformer::list($data);
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function create(CreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $store = Store::create([
                'name' => $request->store_name,
                'address' => $request->store_address,
                'phone' => $request->store_phone,
                'email' => $request->store_email,
                'regency_id' => $request->store_regency,
                'province_id' => $request->store_province,
                'country_id' => $request->store_country,
                'district_id' => $request->store_district,
                'village_id' => $request->store_village,
                'postal_code' => $request->store_postal_code
                // 'currency_id' => $currency_id
            ]);

            $role = Role::where('name', 'Super admin')->orWhere('name', 'Admin')->firstOrFail();
            $user = User::create([
                'email' => $request->email,
                'password' => bcrypt(base64_decode($request->password)),
                'activation_token' => md5(time()),
                'activation_time_limit' => Carbon::now()->addHours(3),
                'can_access_admin' => true,
                'store_id' => $store->id,
                'role_id' => $role->id
            ]);

            $user_detail = UserDetail::create([
                'user_id' => $user->id,
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'phone' => $request->phone,
                'avatar' => $request->avatar
            ]);

            $user->notify(new RegisterNotification());
            $response = [
                'account' => $user,
                'detail' => $user_detail,
                'store' => $store
            ];
            DB::commit();
            return StoreTransformer::detail($response);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    public function update(UpdateRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $store_id = $request->id;
            if ($user->can_access_admin && $user->store_id !== NULL) {
                $store_id = $user->store_id;
            }
            $store = Store::where('id', $store_id)->firstOrFail();
            // $user = Auth::user();
            // if($user->store_id !== $store->id){
            //     throw new \Illuminate\Validation\UnauthorizedException;
            // }
            $fill = [
                'name' => $request->name,
                'address' => $request->address,
                'phone' => $request->phone,
                'email' => $request->email,
                'country_id' => $request->country_id,
                'regency_id' => $request->regency_id,
                'district_id' => $request->district_id,
                'province_id' => $request->province_id,
                'village_id' => $request->village_id,
                'postal_code' => $request->postal_code
            ];

            if ($request->filled('logo')) {
                $fill['logo'] = imageUpload($this->folder_logo, $request->logo);
            }
            if ($request->filled('auto_complete_policy')) {
                $fill['auto_complete_policy'] = $request->auto_complete_policy;
            }

            $store->update($fill);
            DB::commit();

            return StoreTransformer::detail($store);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }
    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $data = Store::where('id', $id)->firstOrFail();
            $data->delete();
            DB::commit();
            return StoreTransformer::delete($data);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }
}
