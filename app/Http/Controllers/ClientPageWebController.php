<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientPageWebController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'trainee']);
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $pages = Page::query()
            ->published()
            ->accessibleBy($user)
            ->orderByDesc('show_in_menu')
            ->orderBy('menu_order')
            ->orderBy('title')
            ->get(['id', 'title', 'slug', 'excerpt', 'featured_image', 'access_level', 'show_in_menu', 'menu_order', 'published_at'])
            ->filter(fn (Page $page) => $page->canAccess($user))
            ->values();

        return view('client.pages.index', compact('pages'));
    }

    public function show(Request $request, string $slug): View
    {
        $page = Page::query()
            ->select([
                'id', 'title', 'slug', 'content', 'excerpt', 'meta_title',
                'meta_description', 'featured_image', 'access_level',
                'required_membership_types', 'audience_gender', 'is_published', 'published_at',
                'user_id', 'created_at', 'updated_at',
            ])
            ->where('slug', $slug)
            ->where('is_published', true)
            ->with(['user:id,name'])
            ->firstOrFail();

        if (! $page->canAccess($request->user())) {
            abort(403, 'ليس لديك صلاحية للوصول لهذه الصفحة.');
        }

        return view('client.pages.show', compact('page'));
    }
}
