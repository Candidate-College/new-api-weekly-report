<?php

use App\Models\User;
use Database\Seeders\UserSeeder;
beforeEach(function () {
    DB::beginTransaction();
});

afterEach(function () {
    DB::rollBack();
});

test('user seeder creates correct number of users with proper flags', function () {
    
    expect(User::where('CFlag', true)->count())->toBe(6)
        ->and(User::where('Sflag', true)->count())->toBe(6)
        ->and(User::where('StFlag', true)->count())->toBe(10);
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

describe('GET /api/v1/supervisor/staff', function () {
    it('returns 403 if user is not supervisor', function () {
        $staffToken = $this->authenticateAs($this->staffEmail, $this->testPassword);
        $response = $this->withToken($staffToken)->getJson('/api/v1/supervisor/staff');
        $response->assertForbidden()
            ->assertJson(['message' => 'Forbidden']);
    });

    it('returns 200 with staff data of supervisor if authenticated and authorized', function () {
        $supervisorToken = $this->authenticateAs($this->supervisorEmail, $this->testPassword);
        $response = $this->withToken($supervisorToken)->getJson('/api/v1/supervisor/staff');
        $response->assertOk()
         ->assertJsonStructure([
                     'data' => [
                         '*' => ['id', 'first_name', 'last_name', 'email', 'instagram', 'linkedin', 'batch_no', 'division_id', 'supervisor_id', 'vice_supervisor_id', 'CFlag', 'Sflag', 'StFlag', 'profile_picture', 'created_at', 'updated_at']
                     ]
                 ]);
    });

    it('returns 404 if staff data is not found', function () {
        $supervisor = User::factory()->create([
            'Sflag' => true,
            'password' => bcrypt($this->testPassword),
        ]);

        $response = $this->actingAs($supervisor)
                         ->getJson('/api/v1/supervisor/staff');

        $response->assertNotFound()
                 ->assertJson(['message' => 'Data not found']);
    });
});

describe('GET api/v1/clevel/supervisor-staff/{divisionId}/list', function () {

    it('returns 403 if user is not clevel', function () {
        $staffToken = $this->authenticateAs($this->staffEmail, $this->testPassword);
        $response = $this->withToken($staffToken)->getJson('/api/v1/c-level/supervisor-staff/1/list');
        $response->assertForbidden()
            ->assertJson(['message' => 'Forbidden']);
    });

    it('returns 200 with staff data if authenticated and authorized', function () {
        $clevelToken = $this->authenticateAs($this->clevelEmail, $this->testPassword);
        $response = $this->withToken($clevelToken)->getJson('/api/v1/c-level/supervisor-staff/1/list');
         $response->assertOk()
             ->assertJsonStructure([
                 'division_id',
                 'division_name',
                 'team_members' => [
                     '*' => [
                         'name',
                         'role',
                         'profile_picture',
                     ]
                 ]
             ]);
    });
});

describe('GET api/v1/division/staff-count', function () {
    it('returns 403 if user is not clevel', function () {
        $staffToken = $this->authenticateAs($this->staffEmail, $this->testPassword);
        $response = $this->withToken($staffToken)->getJson('/api/v1/division/staff-count');
        $response->assertForbidden()
            ->assertJson(['message' => 'Forbidden']);
    });

   it('returns 200 with staff and division total if authenticated and authorized', function () {
    $clevelToken = $this->authenticateAs($this->clevelEmail, $this->testPassword);
    $response = $this->withToken($clevelToken)->getJson('/api/v1/division/staff-count');
    $response->assertOk()
             ->assertJsonStructure([
                 'division_count',
                 'total_staff_count'
             ]);
    });

    it('returns 404 if staff data is not found', function () {
        $clevel = User::factory()->create([
            'CFlag' => true,
            'password' => bcrypt($this->testPassword),
        ]);
        $response = $this->actingAs($clevel)
                         ->getJson('/api/v1/division/staff-count');
        $response->assertNotFound()
                 ->assertJson(['message' => 'Data not found']);
    });
});
