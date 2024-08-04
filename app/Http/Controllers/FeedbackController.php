<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MonthlyFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/feedback/monthly",
     *     summary="Get monthly feedback",
     *     tags={"Feedback"},
     *     @OA\Response(
     *         response=200,
     *         description="Monthly feedback data",
     *     )
     * )
     */

    /**
     * @OA\Post(
     *     path="/api/v1/feedback/monthly",
     *     summary="Create monthly feedback",
     *     tags={"Feedback"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/MonthlyFeedback")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Monthly feedback created successfully",
     *     )
     * )
     */
    public function getMonthlyFeedback()
    {
        // Logic to fetch monthly feedback
    }

    public function createMonthlyFeedback(Request $request)
    {
        // Logic for supervisor to create monthly feedback for staff
    }
}
