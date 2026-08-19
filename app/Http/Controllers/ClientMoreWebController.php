<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\MembershipAccessService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientMoreWebController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'trainee']);
    }

    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $memberPages = Page::query()
            ->published()
            ->inMenu()
            ->accessibleBy($user)
            ->orderBy('menu_order')
            ->orderBy('title')
            ->get(['id', 'title', 'slug', 'excerpt', 'access_level', 'menu_order'])
            ->filter(fn (Page $page) => $page->canAccess($user))
            ->values();

        $profileUrl = \Illuminate\Support\Facades\Route::has('profile.show')
            ? route('profile.show')
            : url('/user/profile');

        return view('client.more.index', [
            'memberPages' => $memberPages,
            'profileUrl' => $profileUrl,
            'siteName' => \App\Models\SiteSetting::get('site_name', config('app.name')),
            'isTrainee' => MembershipAccessService::hasTraineeRole($user),
        ]);
    }
}
