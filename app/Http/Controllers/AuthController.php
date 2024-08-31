<?php

namespace App\Http\Controllers;


use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\SendOtpRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Services\AuthService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;
use Throwable;

class AuthController extends Controller


{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @throws ValidationException
     */
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

    /**
     * @OA\Post(
     *     path="/api/v1/auth/login",
     *     summary="Login pengguna dengan email dan password",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login berhasil, token dikembalikan.",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=3600)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Email atau password salah.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Email atau Password Anda salah")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validasi input gagal.",
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="array", @OA\Items(type="string", example="The email field is required.")),
     *             @OA\Property(property="password", type="array", @OA\Items(type="string", example="The password must be at least 6 characters."))
     *         )
     *     )
     * )
     */

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
     
         if (!($token = $this->generateTokenWithRole($credentials))) {
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

    /**
     * @OA\Post(
     *     path="/api/v1/auth/logout",
     *     summary="Logout pengguna",
     *     description="Mengakhiri sesi autentikasi pengguna dan menghapus token yang ada.",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Pengguna berhasil logout.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User Berhasi Logout")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token tidak valid atau sudah kadaluarsa.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Token tidak valid atau sudah kadaluarsa.")
     *         )
     *     )
     * )
     */

    public function logout()
    {
        auth('api')->logout();
        return response()->json(['message' => 'User Berhasi Logout']);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/refresh",
     *     summary="Memperbarui token autentikasi",
     *     description="Endpoint ini memperbarui token JWT yang kedaluwarsa, menghasilkan token baru untuk autentikasi.",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token berhasil diperbarui.",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string", example="new-access-token"),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=3600)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token tidak valid atau telah kedaluwarsa.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Token tidak valid atau telah kedaluwarsa.")
     *         )
     *     )
     * )
     */

    public function refresh()
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');
        return $this->respondWithToken($guard->refresh());
    }

    protected function generateTokenWithRole($credentials)
    {
        if (!$token = auth()->guard('api')->attempt($credentials)) {
            return false;
        }

        $user = auth()->guard('api')->user();

        $role = 'guest'; 
        if ($user->CFlag) {
            $role = 'clevel';
        } elseif ($user->Sflag) {
            $role = 'supervisor';
        } elseif ($user->StFlag) {
            $role = 'staff';
        }

        $customClaims = ['role' => $role];

        return auth()->guard('api')->claims($customClaims)->attempt($credentials);
    }
    protected function respondWithToken($token)
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $guard->factory()->getTTL() * 60, // Get token expiration time in seconds
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
        } catch (Throwable $th) {
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
        } catch (Throwable $th) {
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
