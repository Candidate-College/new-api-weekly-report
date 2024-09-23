<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected $clevelEmail = 'josue60@example.com';
    protected $invalidclevelEmail = 'karolann97@example.com';
    protected $supervisorEmail = 'ward.ruecker@example.com';
    protected $staffEmail = 'turner.emmet@example.org';
    protected $testPassword = 'rahasia';
    
    protected function authenticateAs($email, $password)
        {
        $response = test()->postJson('/api/v1/auth/login', [
            'email' => $email,
            'password' => $password,
        ]);

        $response->assertOk();

        return $response->json('access_token');
    }
}
