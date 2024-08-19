<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\OTP;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendOtp;
use Tests\TestCase;
use function Pest\Laravel\{postJson, getJson};

beforeEach(function () {
    Mail::fake();
});

it('can send OTP to email', function () {
    $email = 'test' . uniqid() . '@example.com';
    $user = User::factory()->create([
        'email' => $email,
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    $response = postJson('/api/v1/auth/send-otp', ['email' => $email]);

    $response->assertStatus(200)
             ->assertJson(['message' => 'OTP dikirim ke email.']);

    Mail::assertSent(SendOtp::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email)
            && $mail->full_name === $user->first_name . ' ' . $user->last_name;
    });
});

it('can verify OTP successfully', function () {
    $email = 'test' . uniqid() . '@example.com';
    $user = User::factory()->create([
        'email' => $email,
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    $otpService = app(\App\Services\OtpService::class);
    $otpData = $otpService->generateOtp($user->id);

    $token = $otpData['token'];

    $otp = OTP::where('user_id', $user->id)->first();

    $response = postJson('/api/v1/auth/verify-otp/' . $token, [
        'email' => $email,
        'otp' => $otp->OTP_code,
    ]);

    $response->assertStatus(200)
             ->assertJson(['message' => 'Email berhasil diverifikasi.']);

    $this->assertNotNull($user->fresh()->email_verified_at);
});

it('can send OTP for forgot password', function () {
    $email = 'test' . uniqid() . '@example.com';
    $user = User::factory()->create([
        'email' => $email,
        'first_name' => 'Jane',
        'last_name' => 'Smith',
    ]);

    $response = postJson('/api/v1/auth/forgot-password', ['email' => $email]);

    $response->assertStatus(200)
             ->assertJson(['message' => 'OTP dikirim untuk reset password.']);

    Mail::assertSent(SendOtp::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email)
            && $mail->full_name === $user->first_name . ' ' . $user->last_name;
    });
});

it('can reset password using OTP', function () {
    $email = 'test' . uniqid() . '@example.com';
    $user = User::factory()->create([
        'email' => $email,
        'password' => Hash::make('oldpassword'),
        'first_name' => 'Alice',
        'last_name' => 'Johnson',
    ]);

    $otpService = app(\App\Services\OtpService::class);
    $otpData = $otpService->generateOtp($user->id);

    $token = $otpData['token'];

    $otp = OTP::where('user_id', $user->id)->first();

    $response = postJson('/api/v1/auth/reset-password/' . $token, [
        'email' => $email,
        'otp' => $otp->OTP_code,
        'password' => 'newpassword',
        'password_confirmation' => 'newpassword',
    ]);

    $response->assertStatus(200)
             ->assertJson(['message' => 'Password berhasil direset.']);

    $this->assertTrue(Hash::check('newpassword', $user->fresh()->password));
});
