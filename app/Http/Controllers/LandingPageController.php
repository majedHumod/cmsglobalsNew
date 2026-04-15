<?php

namespace App\Http\Controllers;

use App\Models\LandingPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\TenantCache;

class LandingPageController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin'])->except(['show']);
    }

    /**
     * Display the landing page
     */
    public function show()
    {
        // Use cached landing page with optimized queries
        $landingPage = Cache::remember(TenantCache::key('active_landing_page_full'), 1800, function () {
            return LandingPage::getActive();
        });
        
        if (!$landingPage) {
            // If no landing page is active, redirect to login
            return redirect()->route('login');
        }
        
        return view('landing-page.show', compact('landingPage'));
    }

    /**
     * Display a listing of landing pages
     */
    public function index()
    {
        $landingPages = LandingPage::with('user')->latest()->get();
        return view('admin.landing-pages.index', compact('landingPages'));
    }

    /**
     * Show the form for creating a new landing page
     */
    public function create()
    {
        return view('admin.landing-pages.create');
    }

    /**
     * Store a newly created landing page
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'header_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'header_text_color' => 'required|string|max:7',
            'show_join_button' => 'boolean',
            'join_button_text' => 'nullable|string|max:50',
            'join_button_url' => 'nullable|string|max:255',
            'join_button_color' => 'nullable|string|max:7',
            'content' => 'required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        try {
            // Handle header image upload
            if ($request->hasFile('header_image')) {
                $imagePath = $request->file('header_image')->store('landing-pages', 'public');
                $validated['header_image'] = $imagePath;
            }

            // Set default values
            $validated['user_id'] = auth()->id();
            $validated['show_join_button'] = $request->has('show_join_button');
            $validated['is_active'] = $request->has('is_active');

            // Create landing page
            $landingPage = LandingPage::create($validated);

            // If this landing page is active, deactivate others
            if ($landingPage->is_active) {
                LandingPage::where('id', '!=', $landingPage->id)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }

            // Clear cache
            LandingPage::clearCache();

            return redirect()->route('admin.landing-pages.index')
                ->with('success', 'تم إنشاء الصفحة الرئيسية بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error creating landing page: ' . $e->getMessage());
            return back()->withInput()->with('error', 'حدث خطأ أثناء إنشاء الصفحة الرئيسية: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the landing page
     */
    public function edit(LandingPage $landingPage)
    {
        return view('admin.landing-pages.edit', compact('landingPage'));
    }

    /**
     * Update the landing page
     */
    public function update(Request $request, LandingPage $landingPage)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'header_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'header_text_color' => 'required|string|max:7',
            'join_button_text' => 'nullable|string|max:50',
            'join_button_url' => 'nullable|string|max:255',
            'join_button_color' => 'nullable|string|max:7',
            'content' => 'required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
        ]);

        try {
            // Handle header image upload
            if ($request->hasFile('header_image')) {
                // Delete old image if exists
                if ($landingPage->header_image) {
                    Storage::disk('public')->delete($landingPage->header_image);
                }
                $imagePath = $request->file('header_image')->store('landing-pages', 'public');
                $validated['header_image'] = $imagePath;
            }

            // Set boolean values
            $validated['show_join_button'] = $request->has('show_join_button');
            $validated['is_active'] = $request->has('is_active');

            // Update landing page
            $landingPage->update($validated);

            // If this landing page is active, deactivate others
            if ($validated['is_active']) {
                LandingPage::where('id', '!=', $landingPage->id)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }

            // Clear cache
            LandingPage::clearCache();

            return redirect()->route('admin.landing-pages.index')
                ->with('success', 'تم تحديث الصفحة الرئيسية بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error updating landing page: ' . $e->getMessage());
            return back()->withInput()->with('error', 'حدث خطأ أثناء تحديث الصفحة الرئيسية: ' . $e->getMessage());
        }
    }

    /**
     * Remove the landing page
     */
    public function destroy(LandingPage $landingPage)
    {
        try {
            // Delete header image if exists
            if ($landingPage->header_image) {
                Storage::disk('public')->delete($landingPage->header_image);
            }

            $landingPage->delete();

            // Clear cache
            LandingPage::clearCache();

            return redirect()->route('admin.landing-pages.index')
                ->with('success', 'تم حذف الصفحة الرئيسية بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error deleting landing page: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء حذف الصفحة الرئيسية: ' . $e->getMessage());
        }
    }

    /**
     * Set landing page as active
     */
    public function setActive(LandingPage $landingPage)
    {
        try {
            // Deactivate all landing pages
            LandingPage::where('is_active', true)->update(['is_active' => false]);

            // Activate the selected landing page
            $landingPage->update(['is_active' => true]);

            // Clear cache
            LandingPage::clearCache();

            return redirect()->route('admin.landing-pages.index')
                ->with('success', 'تم تفعيل الصفحة الرئيسية بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error activating landing page: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء تفعيل الصفحة الرئيسية: ' . $e->getMessage());
        }
    }
}