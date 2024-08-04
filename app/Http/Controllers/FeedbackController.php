<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MonthlyFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    public function getMonthlyFeedback()
    {
        // Logic to fetch monthly feedback
    }

    public function createMonthlyFeedback(Request $request)
    {
        // Logic for supervisor to create monthly feedback for staff
    }
}
