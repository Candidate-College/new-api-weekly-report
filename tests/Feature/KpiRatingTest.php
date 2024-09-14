<?php
beforeEach(function () {
    DB::beginTransaction();
});

afterEach(function () {
    DB::rollBack();
});

function createKPIData()
{
    return [
        'activeness_Q1_realization' => 5,
        'activeness_Q2_realization' => 1,
        'activeness_Q3_realization' => 2,
        'ability_Q1_realization' => 100,
        'communication_Q1_realization' => 4,
        'communication_Q2_realization' => 4,
        'discipline_Q1_realization' => 100,
        'discipline_Q2_realization' => 100,
        'discipline_Q3_realization' => 100,
    ];
}

describe('POST /api/v1/kpi/supervisor-staff/{id}/{month}/score', function () {
    it('returns 403 unauthorized if user is not supervisor', function () {
        $staffToken = $this->authenticateAs($this->staffEmail, $this->testPassword);

        $response = $this->withToken($staffToken)->postJson('/api/v1/kpi/supervisor-staff/15/8/score');

        $response->assertForbidden()
        ->assertJson(['message' => 'Forbidden']);
    });

    it('returns 403 if the staff is not under supervision', function () {
        $supervisorToken = $this->authenticateAs($this->supervisorEmail, $this->testPassword);

        $response = $this->withToken($supervisorToken)->postJson("/api/v1/kpi/supervisor-staff/15/8/score");

        $response->assertForbidden()
        ->assertJson(['message' => 'Unauthorized. This staff member is not under your supervision.']);
    });

    it('returns 400 if validates KPI input correctly', function () {
        $supervisorToken = $this->authenticateAs($this->supervisorEmail, $this->testPassword);

        $invalidData = [
            'activeness_Q1_realization' => 6,
        ];

        $response = $this->withToken($supervisorToken)->postJson("/api/v1/kpi/supervisor-staff/14/8/score", $invalidData);

        $response->assertBadRequest()
        ->assertJsonValidationErrors(['activeness_Q1_realization']);
    });

    it('returns 201 if can create KPI for staff', function () {
        $supervisorToken = $this->authenticateAs($this->supervisorEmail, $this->testPassword);

        $response = $this->withToken($supervisorToken)->postJson("/api/v1/kpi/supervisor-staff/14/8/score", createKPIData());

        $response->assertCreated()
        ->assertJsonStructure([
            'data' => [
                'user_id', 'year', 'month', 'kpi_data', 'total_aspects', 'value_conversion'
            ]
        ]);
    });

    it('returns 409 if conflict if KPI already exists', function () {
        $supervisorToken = $this->authenticateAs($this->supervisorEmail, $this->testPassword);

        $response = $this->withToken($supervisorToken)->postJson("/api/v1/kpi/supervisor-staff/14/4/score", createKPIData());

        $response->assertConflict()
        ->assertJson(['message' => 'KPI for this user, year, and month already exists.']);
    });
});

describe('GET /api/v1/kpi/supervisor-staff/{id}/{month}/score', function () {
    it('returns 403 unauthorized if user is not supervisor', function () {
        $staffToken = $this->authenticateAs($this->staffEmail, $this->testPassword);

        $response = $this->withToken($staffToken)->getJson('/api/v1/kpi/supervisor-staff/14/8/score');

        $response->assertForbidden()
        ->assertJson(['message' => 'Forbidden']);
    });

    it('returns 403 if the staff is not under supervision', function () {
        $supervisorToken = $this->authenticateAs($this->supervisorEmail, $this->testPassword);

        $response = $this->withToken($supervisorToken)->getJson("/api/v1/kpi/supervisor-staff/15/8/score");

        $response->assertForbidden()
        ->assertJson(['message' => 'Unauthorized. This staff member is not under your supervision.']);
    });

    it('returns 200 if can access KPI endpoint if authenticated and authorized', function () {
        $supervisorToken = $this->authenticateAs($this->supervisorEmail, $this->testPassword);
        $staffId = 14;
        $month = 8;
        $year = 2024;
        $this->withToken($supervisorToken)->postJson("/api/v1/kpi/supervisor-staff/{$staffId}/{$month}/score", createKPIData());

        $response = $this->withToken($supervisorToken)->getJson("/api/v1/kpi/supervisor-staff/{$staffId}/{$month}/score");

        $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'user_id',
                'year',
                'month',
                'kpi_data' => [
                    '*' => [
                        'aspect', 'total_aspect', 'value_conversion', 'type' => [
                            '*' => ['kpi', 'end_of_month_realization', 'score', 'final_score']
                        ]
                    ]
                ],
                'total_aspects',
                'value_conversion'
            ]
        ]);
    });

    it('returns 403 if cannot access KPI endpoint if not authorized', function () {
        $supervisorToken = $this->authenticateAs($this->supervisorEmail, $this->testPassword);
        $response = $this->withToken($supervisorToken)->getJson("/api/v1/kpi/supervisor-staff/15/3/score");

        $response->assertForbidden()
        ->assertJson([
            'message' => 'Unauthorized. This staff member is not under your supervision.'
        ]);
    });

    it('returns 404 if KPI for the month does not exist', function () {
        $supervisorToken = $this->authenticateAs($this->supervisorEmail, $this->testPassword);
        $response = $this->withToken($supervisorToken)->getJson("/api/v1/kpi/supervisor-staff/14/12/score");

        $response->assertNotFound()
        ->assertJson(['message' => 'Data not found.']);
    });
});
