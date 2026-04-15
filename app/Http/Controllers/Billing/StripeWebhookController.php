<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    // Placeholder: wire proper verification & handling later
    public function handle(Request $request)
    {
        // For now just log and 200 OK
        Log::info('Stripe webhook received', ['payload' => $request->all()]);
        return response()->json(['status' => 'ok']);
    }
}

