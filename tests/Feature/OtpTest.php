<?php

use App\Models\OTP;
use App\Models\User;


test('OTP factory creates valid OTP', function () {

    $supervisors = User::where('Sflag', true)->get();
    $staff = User::factory()->create([
        'supervisor_id' => $supervisors->random()->id,
        'vice_supervisor_id' => $supervisors->random()->id,
        'CFlag' => false,
        'Sflag' => false,
        'StFlag' => true,
    ]);
    $otp = OTP::factory()->create(['user_id' => $staff->id]);
    expect($otp)->toBeInstanceOf(OTP::class)
        ->and($otp->OTP_code)->not->toBeNull();
});

test('OTP belongs to a user', function () {
    $supervisors = User::where('Sflag', true)->get();
    $staff = User::factory()->create([
        'supervisor_id' => $supervisors->random()->id,
        'vice_supervisor_id' => $supervisors->random()->id,
        'CFlag' => false,
        'Sflag' => false,
        'StFlag' => true,
    ]);

    $otp = OTP::factory()->create(['user_id' => $staff->id]);

    expect($otp->user)->toBeInstanceOf(User::class)
        ->and($otp->user->id)->toBe($staff->id);
});

test('OTP attributes are set correctly', function () {
    $supervisors = User::where('Sflag', true)->get();
    $staff = User::factory()->create([
        'supervisor_id' => $supervisors->random()->id,
        'vice_supervisor_id' => $supervisors->random()->id,
        'CFlag' => false,
        'Sflag' => false,
        'StFlag' => true,
    ]);
    $otp = OTP::factory()->create(['user_id' => $staff->id]);

    expect($otp->OTP_code)->toBeString()
        ->and(strlen($otp->OTP_code))->toBe(4)
        ->and($otp->created_at)->toBeInstanceOf(\DateTime::class)
        ->and($otp->expiration_time)->toBeInstanceOf(\DateTime::class);
});

test('OTP has correct primary key', function () {
    $supervisors = User::where('Sflag', true)->get();
    $staff = User::factory()->create([
        'supervisor_id' => $supervisors->random()->id,
        'vice_supervisor_id' => $supervisors->random()->id,
        'CFlag' => false,
        'Sflag' => false,
        'StFlag' => true,
    ]);
    $otp = OTP::factory()->create(['user_id' => $staff->id]);

    expect($otp->getKeyName())->toBe(['user_id', 'created_at'])
        ->and($otp->incrementing)->toBeFalse();
});

test('OTP does not use timestamps', function () {
    $otp = new OTP();
    expect($otp->timestamps)->toBeFalse();
});
