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
    it('returns unauthorized if user is not supervisor', function () {
            $staffToken = authenticateAs('turner.emmet@example.org', 'rahasia');

            $response = $this->withToken($staffToken)->postJson('/api/v1/kpi/supervisor-division/2024/5');

            expect($response->status())->toBe(403);
            expect($response->json())->toMatchArray(['message' => 'Forbidden']);
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

        expect($response->status())->toBe(200);

        expect($response->json())->toMatchArray([
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

    it('forbids a staff member from submitting KPIs for a division', function () {
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

        expect($response->status())->toBe(403);
        expect($response->json())->toEqual([
            'message' => 'Forbidden'
        ]);
    });

    it('returns validation error for incorrect KPI format', function () {
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

        expect($response->status())->toBe(Response::HTTP_UNPROCESSABLE_ENTITY);
        expect($response->json())->toEqual([
        'message' => 'Incorrect Filling Format',
        'errors' => [
            'kpis.0.weight' => ['The kpis.0.weight field must be a number.']
        ],
        ]);
    });

    it('returns error when total weight is not 100', function () {
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

        expect($response->status())->toBe(Response::HTTP_LOCKED);
        expect($response->json())->toEqual([
            'message' => 'Total weight must be 100',
        ]);
    });

    it('returns error when KPI already exists', function () {
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

        expect($response1->status())->toBe(Response::HTTP_FORBIDDEN);
        expect($response1->json())->toEqual([
            'message' => 'KPIs for the given division, year, and month already exist and cannot be modified',
        ]);
    });
});

describe('GET /api/v1/kpi/supervisor-division/{year}/{month}', function () {
     it('returns unauthorized if user is not supervisor', function () {
        $staffToken = authenticateAs('turner.emmet@example.org', 'rahasia');

        $response = $this->withToken($staffToken)->getJson('/api/v1/kpi/supervisor-division/2024/5');

        expect($response->status())->toBe(403);
        expect($response->json())->toMatchArray(['message' => 'Forbidden']);
    });

    it('returns 404 if KPI data is not found', function(){
        $supervisorToken = authenticateAs('ward.ruecker@example.com', 'rahasia');
        $response = $this->withToken($supervisorToken)->getJson('/api/v1/kpi/supervisor-division/2024/5');

        expect($response->status())->toBe(404);
        expect($response->json())->toMatchArray(['message' => "Data not found."]);
    });

    it('returns KPI data if authenticated and authorized', function () {
        $supervisorToken = authenticateAs('ward.ruecker@example.com', 'rahasia');

        $response = $this->withToken($supervisorToken)->getJson('/api/v1/kpi/supervisor-division/2024/2');

        expect($response->status())->toBe(200);

        expect($response->json())->toMatchArray([
            'data' => [
                [
                    'division_id' => 1,
                    'year' => '2024',
                    'month' => 2,
                    'task_name' => 'Membuat API CC Careers',
                    'weight' => 10,
                    'target' => 95,
                    'end_of_month_realization' => null,
                ],
                [
                    'division_id' => 1,
                    'year' => '2024',
                    'month' => 2,
                    'task_name' => 'Refactor Code',
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
    it('returns unauthorized if user is not clevel', function () {
         $supervisorToken = authenticateAs('ward.ruecker@example.com', 'rahasia');

        $response = $this->withToken($supervisorToken)->postJson('/api/v1/kpi/clevel/1/2024/1/score', [
            'realizations' => [90, 85],
        ]);

        expect($response->status())->toBe(403);
        expect($response->json())->toMatchArray(['message' => 'Forbidden']);
    });

    it('allows an authorized user to update KPI scores', function () {
        $supervisorToken = authenticateAs('josue60@example.com', 'rahasia');

        $response = $this->withToken($supervisorToken)->postJson('/api/v1/kpi/clevel/1/2024/1/score', [
            'realizations' => [90, 85],
        ]);

        $response->assertStatus(200)
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

        $response->assertStatus(403)
                ->assertJson(['message' => 'End-of-month realization already exists and cannot be modified']);
    });

    it('returns 422 if realization data format is required', function () {
        $supervisorToken = authenticateAs('josue60@example.com', 'rahasia');

        $response = $this->withToken($supervisorToken)->postJson('/api/v1/kpi/clevel/1/2024/1/score', [
            'realizations' => [],
        ]);

        $response->assertStatus(422)
                ->assertJson([
                    'message' => 'Incorrect Filling Format',
                    'errors' => [
                        'realizations' => ['The realizations field is required.']
                    ],
                ]);
    });

    it('returns 422 if number of realizations does not match KPIs', function () {
       $supervisorToken = authenticateAs('josue60@example.com', 'rahasia');

        $response = $this->withToken($supervisorToken)->postJson('/api/v1/kpi/clevel/1/2024/1/score', [
            'realizations' => [90],
        ]);

        $response->assertStatus(422)
                ->assertJson(['message' => 'Number of realizations must match the number of KPIs']);
    });

    it('returns 422 if realization exceeds target', function () {
 $supervisorToken = authenticateAs('josue60@example.com', 'rahasia');

        $response = $this->withToken($supervisorToken)->postJson('/api/v1/kpi/clevel/1/2024/1/score', [
            'realizations' => [90,100],
        ]);

        $response->assertStatus(422)
                ->assertJson(['message' => 'End-of-month realization cannot exceed the target']);
    });
});
