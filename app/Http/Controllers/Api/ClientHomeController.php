<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ClientHomeService;
use Illuminate\Http\Request;

class ClientHomeController extends Controller
{
    public function __invoke(Request $request, ClientHomeService $clientHomeService)
    {
        $user = $request->user();
        abort_unless($user->hasAnyRole(['user', 'client']), 403);

        return $clientHomeService->resourceFor($user, $request);
    }
}
