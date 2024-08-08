<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MonthlyFeedback;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\PerformanceFeedbackResource;

class FeedbackController extends Controller
{
    public function getUserMonthlyFeedback()
    {
        $user = Auth::user();
        $feedbacks = MonthlyFeedback::where('user_id', $user->id)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
    
        return PerformanceFeedbackResource::collection($feedbacks);
    }

    public function createStaffMonthlyFeedback(Request $request, $id, $year, $month)
    {
        $request->validate([
            'content_text' => 'required|string',
        ]);

        $monthlyFeedback = MonthlyFeedback::firstOrCreate(
            ['user_id' => $id, 'year' => $year, 'month' => $month],
            ['content_text' => $request->input('content_text')]
        );

        if (!$monthlyFeedback->wasRecentlyCreated) {
            return response()->json(['message' => 'Feedback for this month already exists'], 409);
        }

        return new PerformanceFeedbackResource($monthlyFeedback);
    }

    public function getStaffMonthlyFeedback($id, $year, $month)
    {
        $monthlyFeedback = MonthlyFeedback::where('user_id', $id)
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        if (!$monthlyFeedback) {
            return response()->json(['message' => 'No feedback found for the specified month'], 404);
        }

        return new PerformanceFeedbackResource($monthlyFeedback);
    }

}
