<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Policies\PagePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PageManagementPolicyTest extends TestCase
{
    public function test_page_model_resolves_page_policy(): void
    {
        $policy = Gate::getPolicyFor(Page::class);

        $this->assertInstanceOf(PagePolicy::class, $policy);
    }

    public function test_pages_index_route_is_not_gated_only_by_page_manager_role_middleware(): void
    {
        $route = Route::getRoutes()->getByName('pages.index');
        $this->assertNotNull($route);

        $middleware = $route->gatherMiddleware();
        $flattened = implode(' ', $middleware);

        $this->assertStringNotContainsString('role:admin|page_manager', $flattened);
    }
}
