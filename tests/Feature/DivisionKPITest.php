<?php

// tests/Feature/KPIControllerTest.php

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

        $response = $this->withToken($supervisorToken)
                        ->postJson("/api/v1/kpi/supervisor-division/2024/5", $kpiData);

        $response1 = $this->withToken($supervisorToken)->postJson('/api/v1/kpi/supervisor-division/2024/5', $kpiData);

        expect($response1->status())->toBe(Response::HTTP_FORBIDDEN);
        expect($response1->json())->toEqual([
            'message' => 'KPIs for the given division, year, and month already exist and cannot be modified',
        ]);
    });
});

describe('GET /api/v1/kpi/supervisor-division/{year}/{month}', function () {
    
});