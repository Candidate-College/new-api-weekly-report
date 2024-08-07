<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MonthlyFeedback;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\PerformanceFeedbackResource;

class FeedbackController extends Controller
{
    public function getMonthlyFeedback()
    {
        $user = Auth::user();
        $feedbacks = MonthlyFeedback::where('user_id', $user->id)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
    
        return PerformanceFeedbackResource::collection($feedbacks);
    }

    public function createMonthlyFeedback(Request $request)
    {
        // Logic for supervisor to create monthly feedback for staff
    }
}
