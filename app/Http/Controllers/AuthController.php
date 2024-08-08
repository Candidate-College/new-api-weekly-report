<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Services\OtpService;
use Validator;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\OTP;

class AuthController extends Controller
{
    protected $authenticationService;

    public function __construct(OtpService $authenticationService)
    {
        $this->authenticationService = $authenticationService;
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

    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $email = $request->input('email');
        $user = User::where('email', $email)->firstOrFail();
        $userId = $user->id;

        $existingOtp = OTP::where('user_id', $userId)->orderBy('created_at', 'desc')->first();

        if ($existingOtp) {
            $expirationTime = Carbon::parse($existingOtp->expiration_time);
            if ($expirationTime > now()) {
                $remainingTime = $expirationTime->diffForHumans();
                return response()->json(
                    [
                        'message' => 'Harap tunggu sebelum meminta ulang OTP',
                        'error' => "Try again after $remainingTime.",
                    ],
                    400,
                );
            }
        }

        try {
            $token = $this->authenticationService->handleCreateOtp($userId);
            return response()->json(['message' => 'OTP dikirim ke email.', 'token' => $token], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Gagal mengirim OTP', 'error' => $th->getMessage()], 400);
        }
    }

    public function verifyOtp(Request $request, $token)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|digits:4',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $email = $request->input('email');
        $otp = $request->input('otp');

        $user = User::where('email', $email)->firstOrFail();
        $userId = $user->id;

        $otpRecord = OTP::where('user_id', $userId)->where('token', $token)->where('OTP_code', $otp)->where('expiration_time', '>', now())->first();

        if (!$otpRecord) {
            return response()->json(['message' => 'OTP tidak valid atau kedaluwarsa.'], 400);
        }

        $user->update(['email_verified_at' => now()]);

        return response()->json(['message' => 'Email berhasil diverifikasi.'], 200);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $email = $request->input('email');
        $user = User::where('email', $email)->firstOrFail();
        $userId = $user->id;

        try {
            $token = $this->authenticationService->handleCreateOtp($userId);

            return response()->json(['message' => 'OTP dikirim untuk reset password.', 'token' => $token], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Gagal mengirim OTP.', 'error' => $th->getMessage()], 400);
        }
    }

    public function resetPassword(Request $request, $token)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|digits:4',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $email = $request->input('email');
        $otp = $request->input('otp');

        $user = User::where('email', $email)->firstOrFail();
        $userId = $user->id;

        $otpRecord = OTP::where('user_id', $userId)->where('token', $token)->where('OTP_code', $otp)->where('expiration_time', '>', now())->first();

        if (!$otpRecord) {
            return response()->json(['message' => 'OTP tidak valid atau kedaluwarsa.'], 400);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return response()->json(['message' => 'Password berhasil direset.'], 200);
    }
}
