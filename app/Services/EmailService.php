<?php

namespace App\Services;

use App\Mail\SendOtp;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    public function sendOtpMail(string $email, string $otp, string $first_name, string $last_name): void
    {
        Mail::send(new SendOtp($email, $otp, $first_name, $last_name));
    }
}
