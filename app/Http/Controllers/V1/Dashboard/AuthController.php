<?php

namespace App\Http\Controllers\V1\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Auth\LoginRequest;
use DB;
use Auth;
use Carbon\Carbon;
use Socialite;
use App\Models\V1\User;
use App\Models\OAuthClient;

use App\Http\Response\Dashboard\AuthTransformer;
use Lcobucci\JWT\Parser;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        try {
            $user = User::where([
                'email' => $request->email,
                'activation_token' => NULL,
                'activation_time_limit' => NULL
            ])->firstOrFail();
            if ($user->can_access_super_admin || $user->can_access_admin) {
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
        } catch (\Exception $e) {
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
            return AuthTransformer::general('Logout success', NULL);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function profile(Request $request)
    {
        try {
            $user = auth()->user();
            return AuthTransformer::general('Get data success', $user);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
