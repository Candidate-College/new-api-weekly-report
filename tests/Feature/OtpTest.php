<?php

use App\Models\OTP;
use App\Models\User;

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

test('OTP factory creates valid OTP', function () {
    $otp = OTP::factory()->create();
    expect($otp)->toBeInstanceOf(OTP::class)
        ->and($otp->OTP_code)->not->toBeNull();
});

test('OTP belongs to a user', function () {
    $user = User::factory()->create();
    $otp = OTP::factory()->create(['user_id' => $user->id]);

    expect($otp->user)->toBeInstanceOf(User::class)
        ->and($otp->user->id)->toBe($user->id);
});

test('OTP attributes are set correctly', function () {
    $otp = OTP::factory()->create();

    expect($otp->OTP_code)->toBeString()
        ->and(strlen($otp->OTP_code))->toBe(4)
        ->and($otp->created_at)->toBeInstanceOf(\DateTime::class)
        ->and($otp->expiration_time)->toBeInstanceOf(\DateTime::class);
});

test('OTP has correct primary key', function () {
    $otp = OTP::factory()->create();

    expect($otp->getKeyName())->toBe(['user_id', 'created_at'])
        ->and($otp->incrementing)->toBeFalse();
});

test('OTP does not use timestamps', function () {
    $otp = new OTP();
    expect($otp->timestamps)->toBeFalse();
});
