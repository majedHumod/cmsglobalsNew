<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Billing\Plan;

class PlanController extends Controller
{
    public function index()
    {
        // Return active plans (basic endpoint to be consumed by publicsite CTAs)
        $plans = Plan::where('active', true)->orderBy('price')->get([
            'code', 'name', 'price', 'interval', 'currency', 'features'
        ]);

        // For now return JSON; can be replaced with a Blade view later
        return response()->json([
            'plans' => $plans,
        ]);
    }
}

