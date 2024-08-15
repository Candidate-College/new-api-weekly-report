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
            'batch_no' => 'required|integer'
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

    /**
     * @OA\Post(
     *     path="/api/v1/auth/send-otp",
     *     summary="Mengirim OTP ke email pengguna",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP dikirim ke email.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="OTP dikirim ke email."),
     *             @OA\Property(property="token", type="string", example="generated-token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Gagal mengirim OTP.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Gagal mengirim OTP."),
     *             @OA\Property(property="error", type="string", example="Error message")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/v1/auth/verify-otp/{token}",
     *     summary="Verifikasi OTP untuk pengguna",
     *     tags={"Authentication"},
     *     @OA\Parameter(
     *         name="token",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Token untuk verifikasi OTP"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="otp", type="string", example="1234")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email berhasil diverifikasi.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Email berhasil diverifikasi.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="OTP tidak valid atau kedaluwarsa.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="OTP tidak valid atau kedaluwarsa.")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/v1/auth/forgot-password",
     *     summary="Mengirim OTP untuk reset password",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP dikirim untuk reset password.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="OTP dikirim untuk reset password."),
     *             @OA\Property(property="token", type="string", example="generated-token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Gagal mengirim OTP.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Gagal mengirim OTP."),
     *             @OA\Property(property="error", type="string", example="Error message")
     *         )
     *     )
     * )
     */
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

     /**
     * @OA\Post(
     *     path="/api/v1/auth/reset-password/{token}",
     *     summary="Reset password menggunakan OTP",
     *     tags={"Authentication"},
     *     @OA\Parameter(
     *         name="token",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Token untuk reset password"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="otp", type="string", example="1234"),
     *             @OA\Property(property="password", type="string", example="newpassword"),
     *             @OA\Property(property="password_confirmation", type="string", example="newpassword")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password berhasil direset.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Password berhasil direset.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="OTP tidak valid atau kedaluwarsa.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="OTP tidak valid atau kedaluwarsa.")
     *         )
     *     )
     * )
     */
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