<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Billing\Plan;

class SubscribePageController extends Controller
{
    public function index()
    {
        $plans = Plan::where('active', true)
            // Avoid using reserved keyword "interval" without backticks in MariaDB
            // Use CASE for deterministic sort: monthly first, then yearly, then others
            ->orderByRaw("CASE WHEN `interval`='monthly' THEN 1 WHEN `interval`='yearly' THEN 2 ELSE 3 END")
            ->orderBy('price')
            ->get(['code','name','price','interval','currency','features']);

        return view('subscribe.index', [
            'plans' => $plans,
        ]);
    }
}

