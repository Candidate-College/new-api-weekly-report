<?php

use App\Models\User;
use Database\Seeders\UserSeeder;


test('user seeder creates correct number of users with proper flags', function () {
    $seeder = new UserSeeder();
    $seeder->run();

    expect(User::where('CFlag', true)->count())->toBe(1)
        ->and(User::where('Sflag', true)->count())->toBe(5)
        ->and(User::where('StFlag', true)->count())->toBe(20);
});

test('staff users have proper supervisor relationships', function () {
    $seeder = new UserSeeder();
    $seeder->run();

    $staffUsers = User::where('StFlag', true)->get();
    $staffUsers->each(function ($staffUser) {
        expect($staffUser->supervisor)->not->toBeNull()
            ->and($staffUser->viceSupervisor)->not->toBeNull()
            ->and($staffUser->supervisor->Sflag)->toBeTrue()
            ->and($staffUser->viceSupervisor->CFlag)->toBeTrue();
    });
});

test('supervisors have proper supervisor relationships', function () {
    $seeder = new UserSeeder();
    $seeder->run();

    $supervisors = User::where('Sflag', true)->get();
    $supervisors->each(function ($supervisor) {
        expect($supervisor->supervisor)->not->toBeNull()
            ->and($supervisor->supervisor->CFlag)->toBeTrue();
    });
});
