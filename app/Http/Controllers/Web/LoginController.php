<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
class LoginController extends Controller
{
    // Show the login form
    public function showLoginForm()
    {
        return view('admin.login'); // This refers to the login view
    }

    // Handle the login request
    public function login(Request $request)
    {
        // Validate input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        // Attempt login with the 'web' guard
        if (Auth::guard('web')->attempt($request->only('email', 'password'))) {
            // Regenerate the session to prevent session fixation
            $request->session()->regenerate();

            // Redirect to the admin dashboard
            return redirect()->route('admin.dashboard');
        }

        // If authentication fails, redirect back with errors
        return back()->withErrors([
            'email' => 'Invalid credentials. Please try again.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        // Log out the user
        Auth::guard('web')->logout();
    
        // Invalidate the session
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    
        // Redirect to the login page
        return redirect()->route('login');
    }    

}
