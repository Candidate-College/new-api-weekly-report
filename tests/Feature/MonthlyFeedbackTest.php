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

    it('returns 404 if no feedback found', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson('/api/v1/feedback/monthly');

        $response->assertNotFound()
                ->assertJson([
                    'message' => 'Data not found.',
                ]);
    });
    
    it('returns 401 if unauthenticated', function () {
        $response = $this->getJson('/api/v1/feedback/monthly');

        $response->assertUnauthorized();
    });

    it('returns 200 and successfully retrieves monthly feedback a user', function () {
        $staffToken = $this->authenticateAs($this->supervisorEmail, $this->testPassword);
        $response = $this->withToken($staffToken)->getJson('/api/v1/feedback/monthly');

        $response->assertOk()
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

    it('returns 401 if user unauthenticated', function () {
        $response = $this->getJson("/api/v1/feedback/staff-performance/1");

        $response->assertUnauthorized()
                ->assertJson(['message' => 'Unauthorized']);
    });

    it('returns 200 and successfully retrieves performance feedback for a given month', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $kpi = KPIRating::factory()->create([
            'user_id' => $user->id,
            'year' => 2024,
            'month' => 7,
        ]);

        $response = $this->getJson("/api/v1/feedback/staff-performance/7");

        $response->assertOk()
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
    it('returns 404 if no performance feedback found for a given month', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson("/api/v1/feedback/staff-performance/7");

        $response->assertNotFound()
                ->assertJson([
            'message' => 'Data not found.'
        ]);
    });
});

describe('POST /api/v1/feedback/supervisor-staff/{id}/{year}/{month}', function () {
   it('returns 403 if the user is not a supervisor', function () {
        $user = User::factory()->create();
        
        $this->actingAs($user);

        $response = $this->postJson("/api/v1/feedback/supervisor-staff/14/2024/8", [
            'content_text' => 'Excellent work on the project!'
        ]);

        $response->assertForbidden()
                 ->assertJson(['message' => 'Forbidden']);
    });


    it('return 201 and supervisor successfully creates monthly feedback for a staff', function () {
        $supervisorToken = $this->authenticateAs($this->supervisorEmail, $this->testPassword);

        $response = $this->postJson("/api/v1/feedback/supervisor-staff/14/2024/8", [
            'content_text' => 'Excellent work on the project!'
        ], ['Authorization' => "Bearer $supervisorToken"]);

        $response->assertCreated()
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

    it('returns 409 if feedback for the month already exists', function () {
        $supervisorToken = $this->authenticateAs($this->supervisorEmail, $this->testPassword);

        $response = $this->postJson("/api/v1/feedback/supervisor-staff/13/2024/5", [
            'content_text' => 'Keren Banget'
        ], ['Authorization' => "Bearer $supervisorToken"]);

        $response->assertConflict()
        ->assertJson([
            'message' => 'Feedback for this month already exists'
        ]);
    });
});

describe('GET /api/v1/feedback/supervisor-staff/{id}/{year}/{month}', function () {
    it('returns 401 if the user not authorized', function () {
       $response = $this->getJson("/api/v1/feedback/supervisor-staff/8/2024/5");

        $response->assertUnauthorized()
        ->assertJson(['message' => 'Unauthorized']);
        }
    );

    it('returns 403 if the user is not a supervisor', function () {
         $supervisorToken = $this->authenticateAs($this->clevelEmail, $this->testPassword);
         $response = $this->withToken($supervisorToken)->getJson("/api/v1/feedback/supervisor-staff/8/2024/5");

        $response->assertForbidden()
                 ->assertJson(['message' => 'Forbidden']);
        });

    it('returns successfully retrieves supervisor feedback for a given staff', function () {
        $supervisorToken = $this->authenticateAs($this->supervisorEmail, $this->testPassword);
        $response = $this->withToken($supervisorToken)->getJson("/api/v1/feedback/supervisor-staff/8/2024/5");

        $response->assertOk()
                ->assertJsonStructure([
                    'data' => [
                        'year',
                        'month',
                        'content'
                    ]
                ]);
        });

    it('returns 404 if no supervisor feedback found for a given month', function () {
        $supervisorToken = $this->authenticateAs($this->supervisorEmail, $this->testPassword);

        $response = $this->withToken($supervisorToken)->getJson("/api/v1/feedback/supervisor-staff/8/2024/9");

        $response->assertNotFound()
                ->assertJson(['message' => 'No feedback found for the specified month',
                ]);
    });

});

describe('GET /api/v1/feedback/clevel-supervisor/{id}/{divisionId}/{year}/{month}', function () {
    it('returns 401 if the user not authorized', function () {
       $response = $this->getJson("/api/v1/feedback/clevel-supervisor/8/1/2024/5");

        $response->assertUnauthorized()
        ->assertJson(['message' => 'Unauthorized']);
        }
    );

    it('returns 403 if the user is not a clevel', function () {
         $supervisorToken = $this->authenticateAs($this->supervisorEmail, $this->testPassword);
         $response = $this->withToken($supervisorToken)->getJson("/api/v1/feedback/clevel-supervisor/8/1/2024/5");

        $response->assertForbidden()->assertJson(['message' => 'Forbidden']);
        }
    );
    
    it('returns 200 successfully retrieves clevel feedback for a given supervisor if the staff is part of the division', function () {
        $clevelToken = $this->authenticateAs($this->clevelEmail, $this->testPassword);
       
        $response = $this->withToken($clevelToken)->getJson("/api/v1/feedback/clevel-supervisor/8/1/2024/5");

        $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'year',
                'month',
                'content'
            ]
        ]);
    });

    it('returns 404 if no feedback found for the given period', function () {
        $supervisorToken = $this->authenticateAs($this->clevelEmail, $this->testPassword);

        $response = $this->withToken($supervisorToken)->getJson("/api/v1/feedback/clevel-supervisor/8/1/2024/9");

        $response->assertNotFound()
        ->assertJson(['message' => 'No feedback found for this period']);
    });

    it('returns 403 if the staff is not part of the division', function () {
        $supervisorToken = $this->authenticateAs($this->clevelEmail, $this->testPassword);

        $response = $this->withToken($supervisorToken)->getJson("/api/v1/feedback/clevel-supervisor/11/3/2024/1");

        $response->assertForbidden()
        ->assertJson(['message' => 'This staff member is not under your supervision.']);
    });

      it('returns 403 if division clevel not valid', function () {
        $supervisorToken = $this->authenticateAs($this->clevelEmail, $this->testPassword);

        $response = $this->withToken($supervisorToken)->getJson("/api/v1/feedback/clevel-supervisor/7/3/2024/1");

        $response->assertForbidden()
        ->assertJson(['message' => 'This Division is not under your supervision.']);
    });

});

describe('POST /api/v1/feedback/clevel-supervisor/{id}/{divisionId}/{year}/{month}', function () {
     it('returns 401 if the user not authorized', function () {
        $response = $this->postJson("/api/v1/feedback/clevel-supervisor/8/1/2024/6", [
                            'content_text' => 'Lorem Ipsum.'
                        ]);

        $response->assertUnauthorized()
        ->assertJson(['message' => 'Unauthorized']);
        }
    );

    it('returns 403 if the user is not a clevel', function () {
         $supervisorToken = $this->authenticateAs($this->supervisorEmail, $this->testPassword);
        $response = $this->withToken($supervisorToken)->postJson("/api/v1/feedback/clevel-supervisor/8/1/2024/6", [
                            'content_text' => 'Lorem Ipsum.']);

        $response->assertForbidden()
        ->assertJson(['message' => 'Forbidden']);
        }
    );
    
    it('returns 201 successfully creates feedback for a given supervisor if the staff is part of the division', function () {
        $supervisorToken = $this->authenticateAs($this->clevelEmail, $this->testPassword);

        $response = $this->withToken($supervisorToken)
                        ->postJson("/api/v1/feedback/clevel-supervisor/8/1/2024/6", [
                            'content_text' => 'Kinerja Anda bulan ini sangat baik.',
                        ]);

        $response->assertCreated()
        ->assertJson([
            'data' => [
                'year' => '2024',
                'month' => 6,
                'content' => 'Kinerja Anda bulan ini sangat baik.'
            ]
        ]);
    });

    it('returns 409 if feedback for this month already exists', function () {
        $supervisorToken = $this->authenticateAs($this->clevelEmail, $this->testPassword);

        MonthlyFeedback::factory()->create([
            'user_id' => 8,
            'year' => '2024',
            'month' => 8,
            'content_text' => 'Existing feedback.',
        ]);

        $response = $this->withToken($supervisorToken)
                        ->postJson("/api/v1/feedback/clevel-supervisor/8/1/2024/8", [
                            'content_text' => 'New feedback.',
                        ]);

        $response->assertConflict()
        ->assertJson(['message' => 'Feedback for this month already exists']);
    });

    it('returns 403 if the staff is not part of the division', function () {
        $supervisorToken = $this->authenticateAs($this->clevelEmail, $this->testPassword);
    
        $response = $this->withToken($supervisorToken)
                        ->postJson("/api/v1/feedback/clevel-supervisor/11/1/2024/8", [
                            'content_text' => 'Feedback content.',
                        ]);

        $response->assertForbidden()
        ->assertJson(['message' => 'Staff bukan bagian dari divisi.']);
    });

    it('returns 422 if the provided data is invalid', function () {
        $supervisorToken = $this->authenticateAs($this->clevelEmail, $this->testPassword);

        $response = $this->withToken($supervisorToken)
                        ->postJson("/api/v1/feedback/clevel-supervisor/8/1/2024/8", []);

        $response->assertUnprocessable()
        ->assertJson(['message' => 'The content text field is required.']);
    });
});
