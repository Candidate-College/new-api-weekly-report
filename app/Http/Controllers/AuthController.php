<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\OTP;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * @OA\Info(title="Weekly Report API", version="1.0.0")
     */

    /**
     * @OA\Post(
     *     path="/api/v1/auth/register",
     *     summary="Register a new user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *     )
     * )
     */
    public function register(Request $request)
    {
        // Validation, User creation, and OTP sending logic
    }

    public function login(Request $request)
    {
        // Login logic
    }

    public function forgotPassword(Request $request)
    {
        // Forgot password logic
    }

    public function resetPassword(Request $request)
    {
        // Reset password logic
    }
}
