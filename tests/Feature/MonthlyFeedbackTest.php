<?php

use App\Models\User;
use App\Models\KPIRating;

use App\Models\MonthlyFeedback;
use Illuminate\Support\Facades\DB;
    beforeEach(function () {
        DB::beginTransaction();
    });

    afterEach(function () {
        DB::rollBack();
    });

test('monthly feedback factory creates valid feedback', function () {
    $supervisors = User::where('Sflag', true)->get();
    $staff = User::factory()->create([
        'supervisor_id' => $supervisors->random()->id,
        'vice_supervisor_id' => $supervisors->random()->id,
        'CFlag' => false,
        'Sflag' => false,
        'StFlag' => true,
    ]);
    $feedback = MonthlyFeedback::factory()->create(['user_id' => $staff->id]);
    expect($feedback)->toBeInstanceOf(MonthlyFeedback::class)
        ->and($feedback->content_text)->not->toBeNull();
});

test('monthly feedback belongs to a user', function () {
    $supervisors = User::where('Sflag', true)->get();
    $staff = User::factory()->create([
        'supervisor_id' => $supervisors->random()->id,
        'vice_supervisor_id' => $supervisors->random()->id,
        'CFlag' => false,
        'Sflag' => false,
        'StFlag' => true,
    ]);
    $feedback = MonthlyFeedback::factory()->create(['user_id' => $staff->id]);

    expect($feedback->user)->toBeInstanceOf(User::class)
        ->and($feedback->user->id)->toBe($staff->id);
});

test('monthly feedback attributes are set correctly', function () {
    $feedback = MonthlyFeedback::factory()->create([
        'user_id' => 22,
        'year' => '2024',
        'month' => 8,
        'content_text' => 'Great performance this month.',
    ]);
    expect($feedback->year)->toBeString()
        ->and($feedback->month)->toBeInt()
        ->and($feedback->content_text)->toBeString();
});

test('monthly feedback has correct primary key', function () {
    $feedback = new MonthlyFeedback();

    expect($feedback->getKeyName())->toBe(['user_id', 'year', 'month'])
        ->and($feedback->incrementing)->toBeFalse();
});

describe('GET /api/v1/feedback/monthly', function () {

    test('returns 404 if no feedback found', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson('/api/v1/feedback/monthly');

        $response->assertStatus(404)
                ->assertJson([
                    'message' => 'Data not found.',
                ]);
    });
    
    test('returns 401 if unauthenticated', function () {
        $response = $this->getJson('/api/v1/feedback/monthly');

        $response->assertStatus(401);
    });

    test('successfully retrieves monthly feedback a user', function () {
        $staffToken = authenticateAs('ward.ruecker@example.com', 'rahasia');
        $response = $this->withToken($staffToken)->getJson('/api/v1/feedback/monthly');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'year',
                            'month',
                            'content'
                        ]]]);
    });
});

describe('GET /api/v1/feedback/staff-performance/{month}', function () {

    test('returns 401 if user unauthenticated', function () {
        $response = $this->getJson("/api/v1/feedback/staff-performance/1");

        expect($response->status())->toEqual(401);
        expect($response->json())->toMatchArray([
            'message' => 'Unauthorized']);
    });

    test('successfully retrieves performance feedback for a given month', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $kpi = KPIRating::factory()->create([
            'user_id' => $user->id,
            'year' => 2024,
            'month' => 7,
        ]);

        $response = $this->getJson("/api/v1/feedback/staff-performance/7");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'user_id',
                        'year',
                        'month',
                        'kpi_data' => [
                            '*' => [
                                'aspect',
                                'total_aspect',
                                'value_conversion',
                                'type' => [
                                    '*' => [
                                        'kpi',
                                        'end_of_month_realization',
                                        'score',
                                        'final_score'
                                    ]
                                ]
                            ]
                        ],
                        'total_aspects',
                        'value_conversion'
                    ]
                ]);
        });
    test('returns 404 if no performance feedback found for a given month', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson("/api/v1/feedback/staff-performance/7");

        $response->assertStatus(404)
                ->assertJson(['message' => 'Data not found.',
                ]);
    });
});

describe('POST /api/v1/feedback/supervisor-staff/{id}/{year}/{month}', function () {
   test('returns 401 if the user is not a supervisor', function () {
        $user = User::factory()->create();
        
        $this->actingAs($user);

        $response = $this->postJson("/api/v1/feedback/supervisor-staff/14/2024/8", [
            'content_text' => 'Excellent work on the project!'
        ]);

        $response->assertStatus(403)
                ->assertJson([
                    'message' => 'Forbidden'
                ]);
    });

    test('supervisor successfully creates monthly feedback for a staff', function () {
        $supervisorToken = authenticateAs('ward.ruecker@example.com', 'rahasia');

        $response = $this->postJson("/api/v1/feedback/supervisor-staff/14/2024/8", [
            'content_text' => 'Excellent work on the project!'
        ], ['Authorization' => "Bearer $supervisorToken"]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'year',
                        'month',
                        'content'
                    ]
                ]);

        $this->assertDatabaseHas('monthly_feedbacks', [
            'user_id' => 14,
            'year' => '2024',
            'month' => 8,
            'content_text' => 'Excellent work on the project!'
        ]);
    });

    test('returns 409 if feedback for the month already exists', function () {
        $supervisorToken = authenticateAs('ward.ruecker@example.com', 'rahasia');

        $response = $this->postJson("/api/v1/feedback/supervisor-staff/13/2024/5", [
            'content_text' => 'Keren Banget'
        ], ['Authorization' => "Bearer $supervisorToken"]);

        $response->assertStatus(409)
                ->assertJson([
                    'message' => 'Feedback for this month already exists',
                ]);
    });
});
