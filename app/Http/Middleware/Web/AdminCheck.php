<?php

namespace App\Http\Middleware\Web;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if the user is authenticated using the 'web' guard
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user(); // Get the authenticated user

            // Check all flag column is true
            if ($user->CFlag === true && $user->Sflag === true && $user->StFlag === true) {
                return $next($request); // Allow the request to proceed
            }
        }

        // If the user is not authenticated or CFlag is not true, redirect them to login
        return redirect()->route('login')->with('error', 'You must have admin privileges to access this page.');
    }
}
