<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create a user', function () {
    $user = User::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'instagram' => 'https://instagram.com/johndoe',
        'linkedin' => 'https://linkedin.com/in/johndoe',
        'batch_no' => 2021,
        'hashed_password' => bcrypt('password'),
        'division' => 'Engineering',
        'CFlag' => false,
        'SFlag' => false,
        'StFlag' => true,
    ]);
    expect($user)->toBeInstanceOf(User::class);
    expect($user->email)->toBe('john.doe@example.com');
});

it('can create a C-level user without a supervisor', function () {
    $clevel = User::factory()->clevel()->create();

    expect($clevel)->toBeInstanceOf(User::class);
    expect($clevel->CFlag)->toBeTrue();
    expect($clevel->SFlag)->toBeFalse();
    expect($clevel->StFlag)->toBeFalse();
    expect($clevel->supervisor_id)->toBeNull();
});

it('can create a supervisor user with a C-level supervisor', function () {
    $clevel = User::factory()->clevel()->create();

    $supervisor = User::factory()->supervisor($clevel)->create();

    expect($supervisor)->toBeInstanceOf(User::class);
    expect($supervisor->CFlag)->toBeFalse();
    expect($supervisor->SFlag)->toBeTrue();
    expect($supervisor->StFlag)->toBeFalse();
    expect($supervisor->supervisor_id)->toBe($clevel->id);
    expect($supervisor->supervisor)->toBeInstanceOf(User::class);
    expect($supervisor->supervisor->id)->toBe($clevel->id);
});

it('can create a staff user with a supervisor', function () {
    $clevel = User::factory()->clevel()->create();
    $supervisor = User::factory()->supervisor($clevel)->create();

    $staff = User::factory()->staff($supervisor)->create();

    expect($staff)->toBeInstanceOf(User::class);
    expect($staff->CFlag)->toBeFalse();
    expect($staff->SFlag)->toBeFalse();
    expect($staff->StFlag)->toBeTrue();
    expect($staff->supervisor_id)->toBe($supervisor->id);
    expect($staff->supervisor)->toBeInstanceOf(User::class);
    expect($staff->supervisor->id)->toBe($supervisor->id);
});
