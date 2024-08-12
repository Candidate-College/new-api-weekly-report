<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\OtpRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\SendOtpRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Services\AuthService;
use Illuminate\Support\Facades\Hash;
use Validator;
use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|between:2,100',
            'last_name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6',
            'batch_no' => 'required|integer',
            'division' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create(array_merge($validator->validated(), ['password' => bcrypt($request->password)]));

        return response()->json(
            [
                'message' => 'User Berhasi Dibuat',
                'user' => $user,
            ],
            201,
        );
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $credentials = $request->only('email', 'password');

        if (!($token = auth()->guard('api')->attempt($credentials))) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Email atau Password Anda salah',
                ],
                401,
            );
        }

        return $this->respondWithToken($token);
    }

    public function logout()
    {
        auth('api')->logout();
        return response()->json(['message' => 'User Berhasi Logout']);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    public function userProfile()
    {
        return response()->json(auth('api')->user());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ]);
    }

    public function sendOtp(SendOtpRequest $request)
    {
        $user = User::where('email', $request->input('email'))->firstOrFail();
        $userId = $user->id;

        $existingOtp = $this->authService->getLatestOtp($userId);

        if ($existingOtp && $existingOtp->expiration_time > now()) {
            $remainingTime = $existingOtp->expiration_time->diffForHumans();
            return response()->json(
                [
                    'message' => 'Harap tunggu sebelum meminta ulang OTP',
                    'error' => "Try again after $remainingTime.",
                ],
                400,
            );
        }

        try {
            $token = $this->authService->sendOtp($userId);
            return response()->json(['message' => 'OTP dikirim ke email.', 'token' => $token], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Gagal mengirim OTP', 'error' => $th->getMessage()], 400);
        }
    }

    public function verifyOtp(VerifyOtpRequest $request, $token)
    {
        $user = User::where('email', $request->input('email'))->firstOrFail();
        $otpRecord = $this->authService->verifyOtp($user->id, $token, $request->input('otp'));

        if (!$otpRecord) {
            return response()->json(['message' => 'OTP tidak valid atau kedaluwarsa.'], 400);
        }

        $user->update(['email_verified_at' => now()]);

        return response()->json(['message' => 'Email berhasil diverifikasi.'], 200);
    }

    public function forgotPassword(SendOtpRequest $request)
    {
        $user = User::where('email', $request->input('email'))->firstOrFail();
        try {
            $token = $this->authService->sendOtp($user->id);
            return response()->json(['message' => 'OTP dikirim untuk reset password.', 'token' => $token], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Gagal mengirim OTP.', 'error' => $th->getMessage()], 400);
        }
    }

    public function resetPassword(ResetPasswordRequest $request, $token)
    {
        $user = User::where('email', $request->input('email'))->firstOrFail();
        $otpRecord = $this->authService->verifyOtp($user->id, $token, $request->input('otp'));

        if (!$otpRecord) {
            return response()->json(['message' => 'OTP tidak valid atau kedaluwarsa.'], 400);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return response()->json(['message' => 'Password berhasil direset.'], 200);
    }
}