<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AuthCheck
{
    public function handle($request, Closure $next)
    {
        if (!Auth::guard('api')->check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user = Auth::guard('api')->user();

        if (is_null($user->email_verified_at)) {
            return response()->json(['message' => 'Email not verified'], 403);
        }

        return $next($request);
    }
}
