<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Services\RazorpayService;

class PricingController extends Controller
{
    public function index(RazorpayService $razorpay)
    {
        $hasActivePlan = false;
        if (auth()->check()) {
            $hasActivePlan = \App\Models\Subscription::where('user_id', auth()->id())
                ->where('status', 'active')
                ->where('end_date', '>=', now())
                ->exists();
        }
        
        $plans = Plan::where('status', 'active')->orderBy('sort_order')->get();
        return view('pricing.index', compact('plans', 'razorpay', 'hasActivePlan'));
    }
}
