<?php
use App\Models\User;
use Illuminate\Support\Facades\DB;
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

function authenticateAs($email, $password)
{
    $response = test()->postJson('/api/v1/auth/login', [
        'email' => $email,
        'password' => $password,
    ]);

    $response->assertStatus(200);

    return $response->json('access_token');
}

describe('POST /api/v1/kpi/supervisor-staff/{id}/{month}/score', function () {

    it('returns 403 unauthorized if user is not supervisor', function () {
        $staffToken = authenticateAs('turner.emmet@example.org', 'rahasia');

        $response = $this->withToken($staffToken)->postJson('/api/v1/kpi/supervisor-staff/15/8/score');

        expect($response->status())->toBe(403);
        expect($response->json())->toMatchArray(['message' => 'Forbidden']);
    });

    it('returns 403 if the staff is not under supervision', function () {
        $supervisorToken = authenticateAs('ward.ruecker@example.com', 'rahasia');

        $response = $this->withToken($supervisorToken)->postJson("/api/v1/kpi/supervisor-staff/15/8/score");

        expect($response->status())->toBe(403);
        expect($response->json())->toMatchArray(['message' => 'Unauthorized. This staff member is not under your supervision.']);
    });

    it('returns 400 if validates KPI input correctly', function () {
        $supervisorToken = authenticateAs('ward.ruecker@example.com', 'rahasia');

        $invalidData = [
            'activeness_Q1_realization' => 6,
        ];

        $response = $this->withToken($supervisorToken)->postJson("/api/v1/kpi/supervisor-staff/14/8/score", $invalidData);

        expect($response->status())->toBe(400);
        expect($response->json('errors'))->toHaveKey('activeness_Q1_realization');
    });

    it('returns 201 if can create KPI for staff', function () {
        $supervisorToken = authenticateAs('ward.ruecker@example.com', 'rahasia');

        $response = $this->withToken($supervisorToken)->postJson("/api/v1/kpi/supervisor-staff/14/8/score", createKPIData());

        expect($response->status())->toBe(201);
        expect($response->json('data'))->toHaveKeys([
            'user_id', 'year', 'month', 'kpi_data', 'total_aspects', 'value_conversion'
        ]);
    });

    it('returns 409 if conflict if KPI already exists', function () {
        $supervisorToken = authenticateAs('ward.ruecker@example.com', 'rahasia');

        $response = $this->withToken($supervisorToken)->postJson("/api/v1/kpi/supervisor-staff/14/4/score", createKPIData());

        expect($response->status())->toBe(409);
        expect($response->json())->toMatchArray(['message' => 'KPI for this user, year, and month already exists.']);
    });

});

describe('GET /api/v1/kpi/supervisor-staff/{id}/{month}/score', function () {
    
    it('returns 403 unauthorized if user is not supervisor', function () {
        $staffToken = authenticateAs('turner.emmet@example.org', 'rahasia');

        $response = $this->withToken($staffToken)->getJson('/api/v1/kpi/supervisor-staff/14/8/score');

        expect($response->status())->toBe(403);
        expect($response->json())->toMatchArray(['message' => 'Forbidden']);
    });

    it('returns 403 if the staff is not under supervision', function () {
        $supervisorToken = authenticateAs('ward.ruecker@example.com', 'rahasia');

        $response = $this->withToken($supervisorToken)->getJson("/api/v1/kpi/supervisor-staff/15/8/score");

        expect($response->status())->toBe(403);
        expect($response->json())->toMatchArray(['message' => 'Unauthorized. This staff member is not under your supervision.']);
    });

    it('returns 200 if can access KPI endpoint if authenticated and authorized', function () {
        $supervisorToken = authenticateAs('ward.ruecker@example.com', 'rahasia');
        $staffId = 14;
        $month = 8;
        $year = 2024;
        $response1 = $this->withToken($supervisorToken)->postJson("/api/v1/kpi/supervisor-staff/{$staffId}/{$month}/score", createKPIData());
        $response = $this->withToken($supervisorToken)->getJson("/api/v1/kpi/supervisor-staff/{$staffId}/{$month}/score");

        $expectedResponse = [
            'data' => [
                'user_id' => $staffId,
                'year' => (string) $year,
                'month' => $month,
                'kpi_data' => [
                    [
                        'aspect' => 'activeness',
                        'total_aspect' => 20,
                        'value_conversion' => 'A',
                        'type' => [
                            [
                                'kpi' => 'Q1',
                                'end_of_month_realization' => 5,
                                'score' => 100,
                                'final_score' => 10
                            ],
                            [
                                'kpi' => 'Q2',
                                'end_of_month_realization' => 1,
                                'score' => 100,
                                'final_score' => 5
                            ],
                            [
                                'kpi' => 'Q3',
                                'end_of_month_realization' => 2,
                                'score' => 100,
                                'final_score' => 5
                            ]
                        ]
                    ],
                    [
                        'aspect' => 'ability',
                        'total_aspect' => 25,
                        'value_conversion' => 'A',
                        'type' => [
                            [
                                'kpi' => 'Q1',
                                'end_of_month_realization' => 100,
                                'score' => 100,
                                'final_score' => 25
                            ]
                        ]
                    ],
                    [
                        'aspect' => 'communication',
                        'total_aspect' => 20,
                        'value_conversion' => 'A',
                        'type' => [
                            [
                                'kpi' => 'Q1',
                                'end_of_month_realization' => 4,
                                'score' => 100,
                                'final_score' => 10
                            ],
                            [
                                'kpi' => 'Q2',
                                'end_of_month_realization' => 4,
                                'score' => 100,
                                'final_score' => 10
                            ]
                        ]
                    ],
                    [
                        'aspect' => 'discipline',
                        'total_aspect' => 35,
                        'value_conversion' => 'A',
                        'type' => [
                            [
                                'kpi' => 'Q1',
                                'end_of_month_realization' => 100,
                                'score' => 100,
                                'final_score' => 15
                            ],
                            [
                                'kpi' => 'Q2',
                                'end_of_month_realization' => 100,
                                'score' => 100,
                                'final_score' => 15
                            ],
                            [
                                'kpi' => 'Q3',
                                'end_of_month_realization' => 100,
                                'score' => 100,
                                'final_score' => 5
                            ]
                        ]
                    ]
                ],
                'total_aspects' => 100,
                'value_conversion' => 'Excellent'
            ]
        ];

        expect($response->status())->toEqual(200);
        expect($response->json())->toMatchArray($expectedResponse);
    });

    it('returns 403 if cannot access KPI endpoint if not authorized', function () {

        $supervisorToken = authenticateAs('ward.ruecker@example.com', 'rahasia');
        $response = $this->withToken($supervisorToken)->getJson("/api/v1/kpi/supervisor-staff/15/3/score");

        expect($response->status())->toEqual(403);
        expect($response->json())->toMatchArray([
            'message' => 'Unauthorized. This staff member is not under your supervision.'
        ]);
    });

    it('returns 404 if KPI data is not found', function () {
        $supervisorToken = authenticateAs('ward.ruecker@example.com', 'rahasia');

        $response = $this->withToken($supervisorToken)->getJson("/api/v1/kpi/supervisor-staff/14/9/score");

        expect($response->status())->toEqual(404);
        expect($response->json())->toMatchArray([
            'message' => 'Data not found.'
        ]);
    });
});
