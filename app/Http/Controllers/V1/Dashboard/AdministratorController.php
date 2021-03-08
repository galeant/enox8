<?php

namespace App\Http\Controllers\V1\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use Illuminate\Filesystem\Filesystem;
use App\Http\Controllers\Controller;
use App\Models\V1\User;
use App\Notifications\Dashboard\RegisterNotification;
use App\Http\Requests\Dashboard\Administrator\CreateRequest;
use App\Http\Requests\Dashboard\Administrator\ResetPasswordRequest;
use App\Http\Response\Dashboard\AdministratorTransformer;

use DB;

class AdministratorController extends Controller
{

    private $folder_avatar = 'public/avatar/';
    public function getData(Request $request, $id = NULL)
    {
        try {
            $user = auth()->user();
            $data = User::withTrashed()->where('role_id', '!=', NULL);
            // ->where('store_id','!=',NULL);

            if ($user->can_access_admin === true && $user->can_access_super_admin === false) {
                $data = $data->where('store_id', $user->store->id);
            }

            if ($id !== NULL) {
                $data = $data->where('id', $id)->firstOrFail();
                return AdministratorTransformer::detail($data);
            } else {
                $data = $data->paginate(10);
                return AdministratorTransformer::list($data);
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function create(CreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $password = Str::random(25);
            $data = User::create([
                'email' => $request->email,
                'password' => Hash::make($password),
                'role_id' => $request->role,
                'can_access_admin' => $user->can_access_admin,
                'can_access_super_admin' => $user->can_access_super_admin,
                'store_id' => $user->store_id,
            ]);

            $avatar = NULL;
            if ($request->filled('avatar')) {
                $avatar = imageUpload($this->folder_avatar, $request->avatar, [
                    'width' => 50,
                    'height' => NULL
                ]);
            }
            $data->detail()->create([
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'phone' => $request->phone,
                'avatar' => $avatar
            ]);
            $data->notify(new RegisterNotification($password, NULL));
            DB::commit();
            return AdministratorTransformer::detail($data);
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
    }

    public function update(CreateRequest $request, $id)
    {

        $uploaded_img = NULL;
        DB::beginTransaction();
        try {
            $data = User::where('id', $id)->firstOrFail();

            $update_main = [
                'email' => $request->email,
                'role_id' => $request->role
            ];
            if ($request->filled('pasword')) {
                $update_main['password'] = Hash::make(base64_decode($request->password));
            }
            $data->update($update_main);

            $update = [
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'phone' => $request->phone
            ];

            $oImg = $data->detail->avatar;
            $img_req = str_replace(url('/') . '/', '', $request->avatar);
            if (strpos($request->avatar, url('/')) !== false) {
                if ($oImg !== $img_req) {
                    $nImg = imageUpload($this->folder_avatar, @file_get_contents($request->avatar));
                    $uploaded_img = str_replace('storage', 'public', $nImg);
                    $update['avatar'] = $nImg;
                }
            } else {
                $nImg = imageUpload($this->folder_avatar, $request->avatar);
                $uploaded_img = str_replace('storage', 'public', $nImg);
                $update['avatar'] = $nImg;
            }
            $data->detail()->update($update);
            DB::commit();
            if (isset($nImg) && $img_req !== $oImg) {
                Storage::delete(str_replace('storage', 'public', $oImg));
            }
            return AdministratorTransformer::detail($data->fresh());
        } catch (\Exception $e) {
            DB::rollback();
            Storage::delete($uploaded_img);
            throw new \Exception($e->getMessage());
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $data = User::where('id', $id)->where('store_id', $user->store_id)->firstOrFail();
            $data->delete();

            DB::commit();
            return AdministratorTransformer::detail($data);
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $id = explode(",", $request->admin_id);
            $data = User::whereIn('id', $id)->where('store_id', $user->store_id)->get();
            foreach ($data as $d) {
                $password = Str::random(25);
                $d->update([
                    'password' => Hash::make($password)
                ]);
                $d->notify(new RegisterNotification($password, 'reset'));
            }
            DB::commit();
            return AdministratorTransformer::list($data->fresh());
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
    }
}
