<?php

use App\Models\User;
use Database\Seeders\UserSeeder;


test('user seeder creates correct number of users with proper flags', function () {
    
    expect(User::where('CFlag', true)->count())->toBe(10)
        ->and(User::where('Sflag', true)->count())->toBe(20)
        ->and(User::where('StFlag', true)->count())->toBe(80);
});

test('staff users have proper supervisor relationships', function () {
    $staffUsers = User::where('StFlag', true)->get();
    $staffUsers->each(function ($staffUser) {
        expect($staffUser->supervisor)->not->toBeNull()
            ->and($staffUser->viceSupervisor)->not->toBeNull()
            ->and($staffUser->supervisor->Sflag)->toBeTrue()
            ->and($staffUser->viceSupervisor->Sflag)->toBeTrue();
    });
});

test('supervisors have proper supervisor relationships', function () {

    $supervisors = User::where('Sflag', true)->get();
    $supervisors->each(function ($supervisor) {
        expect($supervisor->supervisor)->not->toBeNull()
            ->and($supervisor->supervisor->CFlag)->toBeTrue();
    });
});
