<?php

namespace App\Http\Controllers;

use App\Services\ClientHomeService;
use Illuminate\Http\Request;

class ClientHomeWebController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'trainee']);
    }

    public function __invoke(Request $request, ClientHomeService $clientHomeService)
    {
        $user = $request->user();
        $initialHomeData = $clientHomeService->resourceFor($user, $request)->resolve($request);

        return view('client.home', [
            'client' => $user,
            'initialHomeData' => $initialHomeData,
        ]);
    }
}
