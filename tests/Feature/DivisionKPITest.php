<?php
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::beginTransaction();
});

afterEach(function () {
    DB::rollBack();
});

describe('POST /api/v1/kpi/supervisor-division/{year}/{month}', function () {
    it('returns 403 unauthorized if user is not supervisor', function () {
        $staffToken = authenticateAs('turner.emmet@example.org', 'rahasia');

        $response = $this->withToken($staffToken)->postJson('/api/v1/kpi/supervisor-division/2024/5');

        $response->assertForbidden()
         ->assertJson(['message' => 'Forbidden']);
    });

    it('allows a supervisor to submit KPIs for a division', function () {
        $supervisorToken = authenticateAs('ward.ruecker@example.com', 'rahasia');

        $kpiData = [
            'kpis' => [
                [
                    'task_name' => 'Design Website CC Careers',
                    'weight' => 100, 
                    'target' => 100,
                ],
            ],
        ];

        $response = $this->withToken($supervisorToken)
                        ->postJson("/api/v1/kpi/supervisor-division/2024/5", $kpiData);

        $response->assertOk()
         ->assertJson([
            'data' => [
                [
                    'division_id' => 1,
                    'year' => '2024',
                    'month' => 5,
                    'task_name' => 'Design Website CC Careers',
                    'weight' => 100,
                    'target' => 100,
                    'end_of_month_realization' => null,
                ],
            ],
            'total_weight' => 100,
        ]);
    });

    it('returns 403 and forbids a staff member from submitting KPIs for a division', function () {
        $token = authenticateAs('turner.emmet@example.org', 'rahasia');

        $response = $this->withToken($token)->postJson('/api/v1/kpi/supervisor-division/2024/5', [
            'kpis' => [
                [
                    'task_name' => 'Design Website CC Careers',
                    'weight' => 10,
                    'target' => 100,
                ],
            ],
        ]);

        $response->assertForbidden()
         ->assertJson([
            'message' => 'Forbidden',
        ]);
    });

    it('returns 422 with validation error for incorrect KPI format', function () {
        $token = authenticateAs('ward.ruecker@example.com', 'rahasia');

        $response = $this->withToken($token)->postJson('/api/v1/kpi/supervisor-division/2024/5', [
            'kpis' => [
                [
                    'task_name' => 'Design Website CC Careers',
                    'weight' => 'ten',
                    'target' => 100,
                ],
            ],
        ]);

        $response->assertUnprocessable()
         ->assertJsonValidationErrors(['kpis.0.weight']);
    });

    it('returns 423 error when total weight is not 100', function () {
        $token = authenticateAs('ward.ruecker@example.com', 'rahasia');

        $response = $this->withToken($token)->postJson('/api/v1/kpi/supervisor-division/2024/5', [
            'kpis' => [
                [
                    'task_name' => 'Design Website CC Careers',
                    'weight' => 10,
                    'target' => 100,
                ],
                [
                    'task_name' => 'Another Task',
                    'weight' => 5,
                    'target' => 50,
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_LOCKED)
         ->assertJson(['message' => 'Total weight must be 100']);
    });

    it('returns 403 with error when KPI already exists', function () {
        $supervisorToken = authenticateAs('ward.ruecker@example.com', 'rahasia');

        $kpiData = [
            'kpis' => [
                [
                    'task_name' => 'Design Website CC Careers',
                    'weight' => 100,
                    'target' => 100,
                ],
            ],
        ];

        $this->withToken($supervisorToken)->postJson("/api/v1/kpi/supervisor-division/2024/5", $kpiData);

        $response1 = $this->withToken($supervisorToken)->postJson('/api/v1/kpi/supervisor-division/2024/5', $kpiData);

        $response1->assertForbidden()
         ->assertJson([
            'message' => 'KPIs for the given division, year, and month already exist and cannot be modified',
        ]);
    });
});

describe('GET /api/v1/kpi/supervisor-division/{year}/{month}', function () {
    it('returns 403 if user is not supervisor', function () {
        $staffToken = authenticateAs('turner.emmet@example.org', 'rahasia');

        $response = $this->withToken($staffToken)->getJson('/api/v1/kpi/supervisor-division/2024/5');

        $response->assertForbidden()
         ->assertJson(['message' => 'Forbidden']);
    });

    it('returns 404 if KPI data is not found', function () {
        $supervisorToken = authenticateAs('ward.ruecker@example.com', 'rahasia');
        $response = $this->withToken($supervisorToken)->getJson('/api/v1/kpi/supervisor-division/2024/5');

        $response->assertNotFound()
         ->assertJson(['message' => 'Data not found.']);
    });

    it('returns 200 with KPI data if authenticated and authorized', function () {
        $supervisorToken = authenticateAs('ward.ruecker@example.com', 'rahasia');

        $response = $this->withToken($supervisorToken)->getJson('/api/v1/kpi/supervisor-division/2024/1');

        $response->assertOk()
         ->assertJson([
            'data' => [
                [
                    'division_id' => 1,
                    'year' => '2024',
                    'month' => 1,
                    'task_name' => 'Design Website CC Careers',
                    'weight' => 10,
                    'target' => 95,
                    'end_of_month_realization' => null,
                ],
                [
                    'division_id' => 1,
                    'year' => '2024',
                    'month' => 1,
                    'task_name' => 'Another KPI',
                    'weight' => 90,
                    'target' => 95,
                    'end_of_month_realization' => null,
                ],
            ],
            'total_weight' => 100,
        ]);
    });
});

describe('POST api/v1/kpi/clevel/{divisionId}/{year}/{month}/score', function () {
    it('returns 403 unauthorized if user is not clevel', function () {
        $supervisorToken = authenticateAs('ward.ruecker@example.com', 'rahasia');

        $response = $this->withToken($supervisorToken)->postJson('/api/v1/kpi/clevel/1/2024/1/score', [
            'realizations' => [90, 85],
        ]);

        $response->assertForbidden()
         ->assertJson(['message' => 'Forbidden']);
    });

    it('returns 403 if clevel division not valid', function () {
        $supervisorToken = authenticateAs('karolann97@example.com', 'rahasia');

        $response = $this->withToken($supervisorToken)->postJson('/api/v1/kpi/clevel/1/2024/1/score', [
            'realizations' => [90, 85],
        ]);

        $response->assertForbidden()
         ->assertJson(['message' => 'You are not authorized to update this division']);
    });

    it('returns 200 if allows an authorized user to update KPI scores', function () {
        $supervisorToken = authenticateAs('josue60@example.com', 'rahasia');

        $response = $this->withToken($supervisorToken)->postJson('/api/v1/kpi/clevel/1/2024/1/score', [
            'realizations' => [90, 85],
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['division_id', 'year', 'month', 'task_name', 'weight', 'target', 'end_of_month_realization'],
                ],
            ]);
    });

    it('returns 404 if no Division KPI are found', function () {
        $supervisorToken = authenticateAs('josue60@example.com', 'rahasia');

        $response = $this->withToken($supervisorToken)->postJson('/api/v1/kpi/clevel/1/2024/5/score', [
            'realizations' => [90, 85],
        ]);

        $response->assertStatus(404)
            ->assertJson(['message' => 'No KPIs found for the given division, year, and month']);
    });

    it('returns 403 if end-of-month realization already exists', function () {
        $supervisorToken = authenticateAs('josue60@example.com', 'rahasia');

        $this->withToken($supervisorToken)->postJson('/api/v1/kpi/clevel/1/2024/2/score', [
            'realizations' => [90, 80],
        ]);

        $response = $this->withToken($supervisorToken)->postJson('/api/v1/kpi/clevel/1/2024/2/score', [
            'realizations' => [90, 85],
        ]);

        $response->assertForbidden()
         ->assertJson(['message' => 'End-of-month realization already exists and cannot be modified']);
    });

    it('returns 422 if realization data format is required', function () {
        $supervisorToken = authenticateAs('josue60@example.com', 'rahasia');

        $response = $this->withToken($supervisorToken)->postJson('/api/v1/kpi/clevel/1/2024/1/score', [
            'realizations' => [],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['realizations']);
    });

    it('returns 422 if number of realizations does not match KPIs', function () {
        $supervisorToken = authenticateAs('josue60@example.com', 'rahasia');

        $response = $this->withToken($supervisorToken)->postJson('/api/v1/kpi/clevel/1/2024/1/score', [
            'realizations' => [90],
        ]);

        $response->assertUnprocessable()
            ->assertJson(['message' => 'Number of realizations must match the number of KPIs']);
    });

    it('returns 422 if realization exceeds target', function () {
        $supervisorToken = authenticateAs('josue60@example.com', 'rahasia');

        $response = $this->withToken($supervisorToken)->postJson('/api/v1/kpi/clevel/1/2024/1/score', [
            'realizations' => [90, 100],
        ]);

        $response->assertUnprocessable()
            ->assertJson(['message' => 'End-of-month realization cannot exceed the target']);
    });
});

describe('GET api/v1/kpi/clevel/{divisionId}/{year}/{month}/score', function () {
    it('returns 403 if user is not clevel', function () {
        $supervisorToken = authenticateAs('ward.ruecker@example.com', 'rahasia');

        $response = $this->withToken($supervisorToken)->getJson('/api/v1/kpi/clevel/1/2024/1/score');

        $response->assertForbidden()
         ->assertJson(['message' => 'Forbidden']);
    });

    it('returns 403 if clevel division not valid', function () {
        $supervisorToken = authenticateAs('karolann97@example.com', 'rahasia');

        $response = $this->withToken($supervisorToken)->getJson('/api/v1/kpi/clevel/1/2024/1/score');

        $response->assertForbidden()
         ->assertJson(['message' => 'You are not authorized to see this division']);
    });

    it('returns 404 if KPI data is not found', function () {
        $supervisorToken = authenticateAs('josue60@example.com', 'rahasia');

        $response = $this->withToken($supervisorToken)->getJson('/api/v1/kpi/clevel/1/2024/4/score');

        $response->assertNotFound()
         ->assertJson(['message' => 'Data not found.']);
    });
});
