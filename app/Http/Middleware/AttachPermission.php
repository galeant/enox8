<?php

namespace App\Http\Middleware;

use Closure;

use App\Models\V1\User;

class AttachPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = User::where('email', $request->email)->firstOrFail();
        if (isset($user->role->permission)) {
            $request->request->add([
                'scope' => '*' //implode(" ", $user->role->permission->pluck('access')->toArray())
            ]);
            return $next($request);
        } else {
            throw new \Illuminate\Validation\UnauthorizedException;
        }
    }
}
