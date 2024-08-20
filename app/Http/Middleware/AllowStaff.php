<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AllowStaff
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $user = Auth::guard('api')->user();

        if (!empty($user->StFlag)) {
                return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
