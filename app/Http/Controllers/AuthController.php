<?php

namespace App\Http\Controllers;


use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\SendOtpRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Services\AuthService;
use Exception;
use Illuminate\Support\Facades\Hash;
use Storage;
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

    /**
     * @OA\Post(
     *     path="/api/v1/auth/register",
     *     summary="Register a new user and send an OTP.",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe", nullable=true, description="Optional last name of the user."),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="division_id", type="integer", example=1, description="ID of the division the user belongs to."),
     *             @OA\Property(property="linkedin", type="string", example="https://www.linkedin.com/in/johndoe", nullable=true, description="Optional LinkedIn profile URL."),
     *             @OA\Property(property="instagram", type="string", example="https://www.instagram.com/johndoe", nullable=true, description="Optional Instagram profile URL."),
     *             @OA\Property(property="number", type="string", example="81234567890", nullable=true, description="Optional phone number without country code."),
     *             @OA\Property(property="profile_picture", type="string", format="binary", nullable=true, description="Optional profile picture file (image).")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully and OTP sent.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User berhasil dibuat dan OTP telah dikirim."),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="first_name", type="string", example="John"),
     *                 @OA\Property(property="last_name", type="string", example="Doe", nullable=true),
     *                 @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                 @OA\Property(property="division_id", type="integer", example=1),
     *                 @OA\Property(property="linkedin", type="string", example="https://www.linkedin.com/in/johndoe", nullable=true),
     *                 @OA\Property(property="instagram", type="string", example="https://www.instagram.com/johndoe", nullable=true),
     *                 @OA\Property(property="number", type="string", example="+6281234567890", nullable=true),
     *                 @OA\Property(property="profile_picture", type="string", nullable=true, example="/storage/profile_pictures/filename.jpg"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-11-27T00:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-11-27T00:00:00Z")
     *             ),
     *             @OA\Property(property="otp_token", type="string", example="ABC123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Input validation failed.",
     *         @OA\JsonContent(
     *             @OA\Property(property="first_name", type="array", @OA\Items(type="string", example="The first name field is required.")),
     *             @OA\Property(property="email", type="array", @OA\Items(type="string", example="The email field must be a valid email address.")),
     *             @OA\Property(property="password", type="array", @OA\Items(type="string", example="The password must be at least 6 characters.")),
     *             @OA\Property(property="division_id", type="array", @OA\Items(type="string", example="The division ID is required.")),
     *             @OA\Property(property="profile_picture", type="array", @OA\Items(type="string", example="The profile picture must be an image file.")),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to send OTP.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User berhasil dibuat tetapi gagal mengirim OTP."),
     *             @OA\Property(property="error", type="string", example="SMTP server not available.")
     *         )
     *     )
     * )
     */


    public function register(Request $request)
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|between:2,100',
            'last_name' => 'nullable|string|between:2,100', // last_name is optional
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6',
            'division_id' => 'required|exists:divisions,id', // Ensure division_id exists in divisions table
            'linkedin' => 'nullable|string|url|max:255', // linkedin is optional
            'instagram' => 'nullable|string|url|max:255', // instagram is optional
            'number' => 'nullable|string|max:15', // number is optional
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048' // profile_picture is optional and should be an image
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        // Format the phone number by adding +62 if present
        $formattedNumber = null;
        if ($request->has('number') && $request->number) {
            $formattedNumber = '+62' . ltrim($request->number, '+62'); // Add +62 if not present
        }

        // Handle profile picture upload if present
        $profilePicturePath = null;
        if ($request->hasFile('profile_picture')) {
            // Store the profile picture in the 'profile_pictures' folder in the 'public' disk
            $profilePicturePath = $request->file('profile_picture')->store('profile_pictures', 'public');
        }

        // Create the new user
        $user = User::create(array_merge(
            $validator->validated(),
            [
                'password' => bcrypt($request->password),
                'number' => $formattedNumber, // Store the formatted number
                'profile_picture' => $profilePicturePath ? Storage::url($profilePicturePath) : null // Store the profile picture URL if uploaded
            ]
        ));

        try {
            $token = $this->sendOtpInternal($user->email); // Sending OTP to email
            return response()->json(
                [
                    'message' => 'User berhasil dibuat dan OTP telah dikirim.',
                    'user' => $user,
                    'otp_token' => $token
                ],
                201
            );
        } catch (Throwable $th) {
            return response()->json(
                ['message' => 'User berhasil dibuat tetapi gagal mengirim OTP', 'error' => $th->getMessage()],
                500
            );
        }
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
     *             @OA\Property(property="message", type="string", example="User Berhasil Logout")
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
        return response()->json(['message' => 'User Berhasil Logout']);
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

        // Determine the role based on the user's flags
        $role = 'guest'; 
        if ($user->HFlag) {
            $role = 'head';
        } elseif ($user->ChFlag) {
            $role = 'cohead';
        } elseif ($user->CFlag) {
            $role = 'clevel';
        } elseif ($user->Sflag) {
            $role = 'supervisor';
        } elseif ($user->StFlag) {
            $role = 'staff';
        }

        // Add custom claims with user ID and role
        $customClaims = [
            'role' => $role,
            'id' => $user->id,
        ];

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

    private function sendOtpInternal($email)
    {
        $user = User::where('email', $email)->firstOrFail();
        $userId = $user->id;

        $existingOtp = $this->authService->getLatestOtp($userId);

        if ($existingOtp && $existingOtp->expiration_time > now()) {
            throw new Exception("Harap tunggu sebelum meminta ulang OTP. Try again after {$existingOtp->expiration_time->diffForHumans()}.");
        }

        return $this->authService->sendOtp($userId);
    }

    
}
