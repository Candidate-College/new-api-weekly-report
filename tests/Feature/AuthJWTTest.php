<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use function Pest\Laravel\{postJson, getJson};

beforeEach(function () {
    DB::beginTransaction();
    User::factory()->create([
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane.doe@example.com',
        'password' => Hash::make('password123'),
        'batch_no' => 1
    ]);
});

afterEach(function () {
    DB::rollBack();
});

it('can register a user', function () {
    $response = postJson('/api/v1/auth/register', [
        'first_name' => 'John',
        'last_name' => 'Smith',
        'email' => 'john.smith@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'batch_no' => 1
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'user' => [
                'id',
                'first_name',
                'last_name',
                'email'
            ]
        ]);
});

it('can login a user', function () {
    $response = postJson('/api/v1/auth/login', [
        'email' => 'jane.doe@example.com',
        'password' => 'password123'
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_in'
        ]);

    $this->token = $response['access_token'];
});

it('can refresh token', function () {
    $loginResponse = postJson('/api/v1/auth/login', [
        'email' => 'jane.doe@example.com',
        'password' => 'password123'
    ]);

    $token = $loginResponse['access_token'];

    $response = postJson('/api/v1/auth/refresh', [], [
        'Authorization' => 'Bearer ' . $token
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_in'
        ]);
});

it('can logout a user', function () {
    $loginResponse = postJson('/api/v1/auth/login', [
        'email' => 'jane.doe@example.com',
        'password' => 'password123'
    ]);

    $token = $loginResponse['access_token'];

    $response = postJson('/api/v1/auth/logout', [], [
        'Authorization' => 'Bearer ' . $token
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'User Berhasil Logout'
        ]);
});
