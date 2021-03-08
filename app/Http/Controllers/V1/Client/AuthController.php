<?php

namespace App\Http\Controllers\V1\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use App\Models\OAuthClient;
use App\Models\V1\User;

use App\Http\Requests\Client\Auth\RegisterRequest;
use App\Http\Requests\Client\Auth\VerificationRequest;
use App\Http\Requests\Client\Auth\LoginRequest;
use App\Http\Requests\Client\Auth\ResetPasswordRequest;
use App\Http\Requests\Client\Auth\ProfileUpdateRequest;
use App\Http\Requests\Client\Auth\ChangePasswordRequest;
use App\Http\Response\Client\AuthTransformer;
use App\Notifications\Client\RegisterNotification;
use App\Notifications\Client\ResetPasswordNotification;

use Laravel\Socialite\Facades\Socialite;

use DB;
use Carbon\Carbon;
use Lcobucci\JWT\Parser;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make(base64_decode($request->password)),
                'can_access_customer' => true,
                'activation_token' => md5(time()),
                'activation_time_limit' => Carbon::now()->addMinutes(15)
            ]);
            $user->detail()->create([
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'phone' => $request->phone,
                'gender' => $request->gender,
                'birthdate' => $request->birthdate
            ]);

            $user->notify(new RegisterNotification());
            DB::commit();
            return AuthTransformer::register($user);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    public function verification(VerificationRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = User::where('activation_token', $request->token)->firstOrFail();
            $user->update([
                'activation_token' => NULL,
                'activation_time_limit' => NULL
            ]);
            DB::commit();
            return AuthTransformer::general('Activation success');
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    public function login(LoginRequest $request)
    {

        try {
            $user = User::where([
                'email' => $request->email,
                'activation_token' => NULL,
                'activation_time_limit' => NULL
                // 'store_id' => NULL
            ])
                // ->where(function($q){
                //     $q->where('can_access_admin',true)
                //         ->orWhere('can_access_super_admin',true);
                // })
                ->first();
            if ($user === NULL) {
                throw new \Illuminate\Validation\UnauthorizedException;
            }

            if ($user->can_access_customer) {
                $client = OAuthClient::where('id', $request->client_id)->firstOrFail();
                $request->merge([
                    'client_secret' => $client->secret,
                    'username' => $request->email,
                    'password' => base64_decode($request->password),
                    'scope' => $request->scope
                ]);
                $request = Request::create('/oauth/token', 'POST', $request->toArray());
                $token = app()->handle($request);

                if ($token->status() === 200) {
                    $response = json_decode($token->getContent());
                    $id_token = (new Parser())->parse($response->access_token)->getClaims()['jti']->getValue();
                    $token = DB::table('oauth_access_tokens')->where('id', $id_token)->update([
                        'fcm_token' => $request->fcm_token
                    ]);
                    return AuthTransformer::general('Login success', $response);
                } else {
                    throw new \Illuminate\Validation\UnauthorizedException;
                }
            }
            throw new \Illuminate\Validation\UnauthorizedException;
        } catch (Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function profile()
    {
        $user = auth()->user();
        return AuthTransformer::profile($user);
    }

    public function profileUpdate(ProfileUpdateRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            if ($request->filled('email')) {
                $user->update([
                    'email' => $request->email
                ]);
            }
            $detail_update = [];
            if ($request->filled('firstname')) {
                $detail_update['firstname'] = $request->firstname;
            }
            if ($request->filled('lastname')) {
                $detail_update['lastname'] = $request->lastname;
            }
            if ($request->filled('phone')) {
                $detail_update['phone'] = $request->phone;
            }
            if ($request->filled('gender')) {
                $detail_update['gender'] = $request->gender;
            }
            if ($request->filled('birthdate')) {
                $detail_update['birthdate'] = $request->birthdate;
            }

            if ($request->filled('avatar')) {
                $folder_upload = 'public/profile/';
                if (strpos($request->avatar, 'http://') !== false || strpos($request->avatar, 'https://') !== false) {
                    $fmg_img = str_replace(url('/') . '/', '', $request->avatar);
                    if ($user->detail->avatar !== $fmg_img) {
                        $detail_update['avatar'] = imageUpload($folder_upload, @file_get_contents($request->avatar));
                    }
                } else {
                    $detail_update['avatar'] = imageUpload($folder_upload, $request->avatar);
                }
            }
            $user->detail()->update($detail_update);
            DB::commit();
            return AuthTransformer::profile($user->fresh());
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $user->update([
                'password' => Hash::make(base64_decode($request->new_password))
            ]);
            $token = $request->user()->token();
            $client = OAuthClient::where('id', $token->client_id)->firstOrFail();
            $req = [
                'username' => $user->email,
                'client_id' => $token->client_id,
                'client_secret' => $client->secret,
                'username' => $user->email,
                'password' => base64_decode($request->new_password),
                'scope' => $request->scope,
                'grant_type' => 'password'
            ];
            $request = Request::create('/oauth/token', 'POST', $req);
            $token = app()->handle($request);
            if ($token->status() === 200) {
                DB::commit();
                return AuthTransformer::general('Change password success', json_decode($token->getContent()));
            } else {
                throw new \Illuminate\Validation\UnauthorizedException;
            }
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
    }

    public function logout(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->user()->token()->revoke();
            $request->user()->token()->delete();
            DB::commit();
            return AuthTransformer::general('Logout success');
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = User::where('email', $request->email)->firstOrFail();
            $password = Str::random(25);
            $user->update([
                'password' => Hash::make($password)
            ]);
            $user->notify(new ResetPasswordNotification($password));
            DB::commit();
            return AuthTransformer::general('Reset password success');
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
    }

    public function redirectToProvider(Request $request, $provider)
    {
        try {
            return response()->json([
                'code' => 200,
                'message' => 'Success',
                'data' => [
                    'auth_url' => Socialite::driver($provider)->stateless()->redirect()->getTargetUrl(),
                ]
            ], 200);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function social(Request $request, $provider)
    {
        DB::beginTransaction();
        try {
            $user =  Socialite::driver($provider)->stateless()->user();
            // $user = $request;
            $exist = User::where('email', $user->email)->first();
            if ($exist === NULL) {
                $exist = User::create([
                    'email' => $user->email,
                    'email_verified_at' => Carbon::now(),
                    'password' => Hash::make(ENV('SOCIAL_LOGIN_PASSWORD_DEFAULT')),
                    'can_access_customer' => true,
                    'can_access_admin' => false,
                    'can_access_super_admin' => false
                ]);
                $firstname = isset($user->name) ? $user->name : NULL;
                if (!isset($firstname)) {
                    $firstname = isset($user->given_name) ? $user->given_name : NULL;
                }
                $lastname = isset($user->name) ? $user->name : NULL;
                if (!isset($lastname)) {
                    $lastname = isset($user->family_name) ? $user->family_name : NULL;
                }

                $exist->detail()->create([
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'subscribe' => false,
                    'avatar' => isset($user->avatar) ? $user->avatar : NULL,
                    'avatar_original' => isset($user->avatar) ? $user->avatar : NULL,
                ]);
            }
            $exist->socialLogin()->create([
                'provider_id' => $user->id,
                'provider_name' => $provider
            ]);
            $client = OAuthClient::where('id', ENV('OAUTH_DEFAULT_CLIENT_ID'))->firstOrFail();
            $payload = [
                'client_id' => $client->id,
                'client_secret' => $client->secret,
                'username' => $user->email,
                'grant_type' => 'password',
                'password' => ENV('SOCIAL_LOGIN_PASSWORD_DEFAULT'),
                'scope' => ''
            ];
            $request = Request::create('/oauth/token', 'POST', $payload);
            $token = app()->handle($request);
            if ($token->status() === 200) {
                $response = json_decode($token->getContent());
                $id_token = (new Parser())->parse($response->access_token)->getClaims()['jti']->getValue();
                $token = DB::table('oauth_access_tokens')->where('id', $id_token)->update([
                    'fcm_token' => $request->fcm_token
                ]);
                DB::commit();
                return AuthTransformer::general('Login success', $response);
            } else {
                throw new \Illuminate\Validation\UnauthorizedException;
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }
}
