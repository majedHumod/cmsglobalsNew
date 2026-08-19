<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="size-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            @hasanyrole('admin|user|client')
            <x-responsive-nav-link href="{{ route('notes.index') }}" :active="request()->routeIs('notes.*')">
                {{ __('Notes') }}
            </x-responsive-nav-link>
            @endhasanyrole

            @hasanyrole('admin|user|client')
            <x-responsive-nav-link href="{{ route('meal-plans.index') }}" :active="request()->routeIs('meal-plans.*')">
                {{ __('الجداول الغذائية') }}
            </x-responsive-nav-link>
            @endhasanyrole

            @role('admin')
            <x-responsive-nav-link href="{{ route('articles.index') }}" :active="request()->routeIs('articles.*')">
                {{ __('Articles') }}
            </x-responsive-nav-link>
            @endrole

            @can('view pages')
            <x-responsive-nav-link href="{{ route('pages.index') }}" :active="request()->routeIs('pages.index', 'pages.create', 'pages.edit')">
                {{ __('إدارة الصفحات') }}
            </x-responsive-nav-link>
            @endcan

            @role('admin')
            <x-responsive-nav-link href="{{ route('membership-types.index') }}" :active="request()->routeIs('membership-types.*')">
                {{ __('إدارة العضويات') }}
            </x-responsive-nav-link>
            @endrole

            @php
                try {
                    $allMenuPages = \App\Models\Page::where('show_in_menu', true)
                        ->where('is_published', true)
                        ->orderBy('menu_order')
                        ->get();

                    $user = auth()->user();

                    $menuPages = $allMenuPages->filter(fn ($page) => $page->canAccess($user));
                } catch (\Exception $e) {
                    $menuPages = collect();
                }
            @endphp

            @foreach($menuPages as $menuPage)
                <x-responsive-nav-link href="{{ route('pages.show', $menuPage->slug) }}" :active="request()->is('page/' . $menuPage->slug)">
                    {{ $menuPage->access_level_icon }} {{ $menuPage->title }}
                </x-responsive-nav-link>
            @endforeach

            @auth
            <x-responsive-nav-link href="{{ route('pages.public') }}" :active="request()->routeIs('pages.public')">
                {{ __('جميع الصفحات') }}
            </x-responsive-nav-link>
            @endauth
        </div>

        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="flex items-center px-4">
                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                    <div class="shrink-0 me-3">
                        <img class="size-10 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                    </div>
                @endif

                <div>
                    <div class="font-medium text-base text-gray-800">{{ Auth::user() ? Auth::user()->name : 'Guest' }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user() ? Auth::user()->email : '' }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link href="{{ route('profile.show') }}" :active="request()->routeIs('profile.show')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                    <x-responsive-nav-link href="{{ route('api-tokens.index') }}" :active="request()->routeIs('api-tokens.index')">
                        {{ __('API Tokens') }}
                    </x-responsive-nav-link>
                @endif

                <form method="POST" action="{{ route('logout') }}" x-data>
                    @csrf
                    <x-responsive-nav-link href="{{ route('logout') }}" @click.prevent="$root.submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>

                @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                    <div class="border-t border-gray-200"></div>

                    <div class="block px-4 py-2 text-xs text-gray-400">
                        {{ __('Manage Team') }}
                    </div>

                    <x-responsive-nav-link href="{{ route('teams.show', Auth::user()->currentTeam->id) }}" :active="request()->routeIs('teams.show')">
                        {{ __('Team Settings') }}
                    </x-responsive-nav-link>

                    @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                        <x-responsive-nav-link href="{{ route('teams.create') }}" :active="request()->routeIs('teams.create')">
                            {{ __('Create New Team') }}
                        </x-responsive-nav-link>
                    @endcan

                    @if (Auth::user()->allTeams()->count() > 1)
                        <div class="border-t border-gray-200"></div>

                        <div class="block px-4 py-2 text-xs text-gray-400">
                            {{ __('Switch Teams') }}
                        </div>

                        @foreach (Auth::user()->allTeams() as $team)
                            <x-switchable-team :team="$team" component="responsive-nav-link" />
                        @endforeach
                    @endif
                @endif
            </div>
        </div>
    </div>
</nav>
