<?php

namespace App\Http\Controllers\V1\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\V1\User;
use App\Models\V1\UserDetail;
use App\Models\V1\Report;
use App\Http\Response\Dashboard\CustomerTransformer;
use App\Http\Requests\Dashboard\Customer\ResetPasswordRequest;
use App\Http\Requests\Dashboard\Customer\ChangeStatusRequest;
use App\Notifications\Client\ResetPasswordNotification;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use DB;
use Carbon\Carbon;

class CustomerController extends Controller
{
    public function getData(Request $request, $id = NULL)
    {
        try {
            $per_page = $request->input('per_page', 10);
            $page = $request->input('page', 1);
            $order_by = $request->input('order_by', 'firstname');
            $sort = 'asc';

            $data = User::withTrashed()->where([
                'can_access_customer' => true,
                'can_access_admin' => false,
                'can_access_super_admin' => false,
            ])
                ->select(
                    'users.*',
                    DB::raw('(SELECT firstname FROM user_detail WHERE users.id = user_detail.user_id ) as firstname')
                );

            if ($id !== NULL) {
                $data = $data->where('id', $id)->with('transaction', 'address')->firstOrFail();
                return CustomerTransformer::detail($data);
            } else {
                $data = $data->orderBy($order_by, $sort)->paginate(10);
                $data->appends($request->all());
                return CustomerTransformer::list($data);
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = User::whereIn('id', $request->customer_id)->get();
            foreach ($data as $d) {
                $password = Str::random(25);
                $d->update([
                    'password' => Hash::make($password)
                ]);
                $d->notify(new ResetPasswordNotification($password));
            }
            DB::commit();
            return CustomerTransformer::list($data->fresh());
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
    }

    public function verification($id)
    {
        DB::beginTransaction();
        try {
            $user = User::where('id', $id)->firstOrFail();
            $user->update([
                'activation_token' => NULL,
                'activation_time_limit' => NULL
            ]);
            DB::commit();
            return CustomerTransformer::detail($user);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    public function changeStatus(ChangeStatusRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            if (($key = array_search($user->id, $request->id)) !== false) {
                unset($request->id[$key]);
            }
            $data = User::withTrashed()->whereIn('id', $request->id)->get();
            foreach ($data as $dt) {
                switch ($request->status) {
                    case 'banned':
                        $dt->update([
                            'deleted_at' => Carbon::now()
                        ]);
                        Report::create([
                            'user_id' => $user->id,
                            'relation_id' => $dt->id,
                            'relation_type' => 'user',
                            'reason' => $request->reason
                        ]);
                        break;
                    case 'active':
                        $dt->update([
                            'deleted_at' => NULL
                        ]);
                        break;
                }
            }
            DB::commit();
            return CustomerTransformer::list($data->fresh());
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
    }
}
