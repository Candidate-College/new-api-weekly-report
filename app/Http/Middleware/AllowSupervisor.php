<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AllowSupervisor
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('api')->user();

        // Check if the user is a head or co-head
        if (empty($user->ChFlag) && empty($user->HFlag)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
