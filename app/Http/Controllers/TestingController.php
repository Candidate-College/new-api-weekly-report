<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use OpenApi\Annotations as OA;

class TestingController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/test",
     *     summary="Get a greeting",
     *     description="Returns a simple greeting message.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="string",
     *             example="hello world"
     *         )
     *     )
     * )
     */
    public function index()
    {
        return response("hello world", 200);
    }
}
