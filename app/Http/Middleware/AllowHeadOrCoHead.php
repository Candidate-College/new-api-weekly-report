<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AllowHeadOrCoHead
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

        if ($user->HFlag == 0 && $user->ChFlag == 0) {
            return response()->json([
                'message' => 'Forbidden Access',
            ], 403);
        }

        return $next($request);
    }
}
