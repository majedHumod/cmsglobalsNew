<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Communication\CommunicationCatalog;
use App\Services\Communication\CommunicationInboxService;
use Illuminate\Http\Request;

class CommunicationController extends Controller
{
    public function catalog(CommunicationCatalog $catalog)
    {
        return response()->json([
            'data' => $catalog->catalog(),
        ]);
    }

    public function inboxSummary(Request $request, CommunicationInboxService $inbox)
    {
        return response()->json([
            'data' => $inbox->summaryFor($request->user()),
        ]);
    }
}
