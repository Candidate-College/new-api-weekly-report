<?php

use App\Models\OTP;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create an OTP', function () {
    $user = User::factory()->create();
    $otp = OTP::factory()->create([
        'user_id' => $user->id,
        'OTP_code' => '123456',
        'expiration_time' => now()->addHour(),
    ]);

    expect($otp)->toBeInstanceOf(OTP::class);
    expect($otp->user_id)->toBe($user->id);
    expect($otp->OTP_code)->toBe('123456');
});
