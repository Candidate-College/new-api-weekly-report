<?php

use App\Models\OTP;
use App\Models\User;
use App\Models\KPIRating;
use App\Models\DailyReport;
use App\Models\MonthlyFeedback;
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

test('user has many staff', function () {
    $supervisor = User::where('Sflag', true)->first();
    $staff = User::factory()->count(3)->create([
        'supervisor_id' => $supervisor->id,
        'StFlag' => true,
    ]);

    expect($supervisor->staff()->count())->toBe(7);
    $staff->each(function ($staffUser) use ($supervisor) {
        expect($staffUser->supervisor_id)->toBe($supervisor->id);
    });
});

test('user has many monthly feedbacks', function () {
    $user = User::first();
    $feedbacks = MonthlyFeedback::factory()->count(3)->create([
        'user_id' => $user->id,
    ]);

    expect($user->monthlyFeedbacks()->count())->toBe(7);
    $feedbacks->each(function ($feedback) use ($user) {
        expect($feedback->user_id)->toBe($user->id);
    });
});

test('user has many daily reports', function () {
    $user = User::first();
    $reports = DailyReport::factory()->count(1)->create([
        'user_id' => $user->id,
    ]);

    expect($user->dailyReports()->count())->toBe(21);
    $reports->each(function ($report) use ($user) {
        expect($report->user_id)->toBe($user->id);
    });
});

test('user has many kpis', function () {
    $user = User::first();
    $kpis = KPIRating::factory()->count(3)->create([
        'user_id' => $user->id,
    ]);

    expect($user->kpis()->count())->toBe(7);
    $kpis->each(function ($kpi) use ($user) {
        expect($kpi->user_id)->toBe($user->id);
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
