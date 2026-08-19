<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Notes Card - Visible to both admin and user roles -->
                    @hasanyrole('admin|user|client')
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900">Notes Management</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Create and manage your personal notes.
                            </p>
                            <div class="mt-4">
                                <a href="{{ route('notes.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                    View Notes
                                </a>
                            </div>
                        </div>
                    </div>
                    @endhasanyrole

                    <!-- Meal Plans Card - Visible to both admin and user roles -->
                    @hasanyrole('admin|user|client')
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900">الجداول الغذائية</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                إنشاء وإدارة الوجبات والجداول الغذائية الخاصة بك.
                            </p>
                            <div class="mt-4 space-x-2">
                                <a href="{{ route('meal-plans.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                    إدارة الوجبات
                                </a>
                                <a href="{{ route('meal-plans.public') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    تصفح الوجبات
                                </a>
                            </div>
                        </div>
                    </div>
                    @endhasanyrole

                    <!-- Workouts Card - Visible to admin, coach, and client roles -->
                    @hasanyrole('admin|coach|user|client')
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900">التمارين الرياضية</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                إدارة وتنظيم التمارين والبرامج التدريبية.
                            </p>
                            <div class="mt-4 space-x-2">
                                <a href="{{ route('workouts.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700">
                                    إدارة التمارين
                                </a>
                                <a href="{{ route('workout-schedules.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    الجدول الأسبوعي
                                </a>
                            </div>
                        </div>
                    </div>
                    @endhasanyrole

                    <!-- Articles Card - Visible only to admin role -->
                    @role('admin')
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900">Articles Management</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Manage and publish articles for your website.
                            </p>
                            <div class="mt-4">
                                <a href="{{ route('articles.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                    Manage Articles
                                </a>
                            </div>
                        </div>
                    </div>
                    @endrole

                    @can('view pages')
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900">إدارة الصفحات</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                إنشاء وإدارة صفحات الموقع والمحتوى العام.
                            </p>
                            <div class="mt-4 space-x-2">
                                <a href="{{ route('pages.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700">
                                    إدارة الصفحات
                                </a>
                                <a href="{{ route('pages.public') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    عرض الصفحات
                                </a>
                            </div>
                        </div>
                    </div>
                    @endcan

                    <!-- Membership Types Card - Visible only to admin role -->
                    @role('admin')
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900">💎 إدارة العضويات</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                إنشاء وإدارة أنواع العضويات والاشتراكات المدفوعة.
                            </p>
                            <div class="mt-4">
                                <a href="{{ route('membership-types.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700">
                                    إدارة العضويات
                                </a>
                            </div>
                        </div>
                    </div>
                    @endrole

                    <!-- Public Pages Card - Visible to all authenticated users -->
                    @auth
                    @cannot('view pages')
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900">صفحات الموقع</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                تصفح صفحات الموقع والمحتوى المتاح.
                            </p>
                            <div class="mt-4">
                                <a href="{{ route('pages.public') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    تصفح الصفحات
                                </a>
                            </div>
                        </div>
                    </div>
                    @endcannot
                    @endauth
                </div>
            </div>
        </div>
    </div>
</x-app-layout>