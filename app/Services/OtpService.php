<?php

namespace App\Services;

use App\Mail\SendOtp;
use App\Models\OTP;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OtpService
{
    public function handleCreateOtp(int $userId)
    {
        DB::beginTransaction();

        try {
            $token = Str::random(32);
            $codeOtp = str_pad((string) rand(0, 9999), 4, '0', STR_PAD_LEFT);

            $otp = new OTP([
                'user_id' => $userId,
                'OTP_code' => $codeOtp,
                'token' => $token,
                'expiration_time' => now()->addMinutes(5),
                'created_at' => now(),
            ]);

            $otp->save();

            DB::commit();

            $this->sendMail($userId, $codeOtp);

            return $token; 
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    private function sendMail(int $userId, string $otp): void
    {
        $user = User::findOrFail($userId);
        Mail::send(new SendOtp($user->email, $otp));
    }
}
