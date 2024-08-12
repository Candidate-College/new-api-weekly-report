<?php

namespace App\Services;

use App\Mail\SendOtp;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    public function sendOtpMail(string $email, string $otp)
    {
        Mail::send(new SendOtp($email, $otp));
    }
}
