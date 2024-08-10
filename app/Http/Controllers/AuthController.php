<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\OTP;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|between:2,100',
            'last_name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6',
            'batch_no' => 'required|integer'
        ]);
    
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
    
        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));
    
        return response()->json([
            'message' => 'User Berhasi Dibuat',
            'user' => $user
        ], 201);
    }
    
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }

        $credentials = $request->only('email', 'password');

        if(!$token = auth()->guard('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau Password Anda salah'
            ], 401);
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

    public function forgotPassword(Request $request)
    {
        // Forgot password logic
    }

    public function resetPassword(Request $request)
    {
        // Reset password logic
    }
}
