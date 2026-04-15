<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PageController extends Controller
{
    public function __construct()
    {
        // تحديث الصلاحيات لتشمل page_manager
        $this->middleware(['auth', 'role:admin|page_manager'])->except(['show', 'publicIndex']);
    }

    public function index()
    {
        $pages = Page::with('user')
            ->when(!auth()->user()->hasRole('admin'), function ($query) {
                return $query->where('user_id', auth()->id());
            })
            ->latest()
            ->get();

        return view('pages.index', compact('pages'));
    }

    public function create()
    {
        return view('pages.create');
    }

    public function store(Request $request)
    {
        Log::info('Page store method called', ['request_data' => $request->all()]);

        try {
            $validated = $request->validate([
                'title' => 'required|max:255',
                'content' => 'required',
                'excerpt' => 'nullable|string',
                'meta_title' => 'nullable|max:255',
                'meta_description' => 'nullable|max:160',
                'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'access_level' => 'required|in:public,authenticated,admin,user,page_manager,membership',
                'is_premium' => 'boolean',
                'required_membership_types' => 'nullable|array',
                'required_membership_types.*' => 'exists:membership_types,id',
                'menu_order' => 'nullable|integer|min:0',
                'published_at' => 'nullable|date'
            ]);

            Log::info('Validation passed', ['validated_data' => $validated]);

            // إنشاء slug من العنوان
            $validated['slug'] = Str::slug($validated['title']);
            
            // التأكد من أن الـ slug فريد
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Page::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }

            // رفع الصورة المميزة
            if ($request->hasFile('featured_image')) {
                $imagePath = $request->file('featured_image')->store('pages', 'public');
                $validated['featured_image'] = $imagePath;
                Log::info('Image uploaded', ['path' => $imagePath]);
            }

            // تعيين المستخدم الحالي
            $validated['user_id'] = auth()->id();
            
            // معالجة القيم المنطقية
            $validated['is_published'] = $request->has('is_published') ? 1 : 0;
            $validated['show_in_menu'] = $request->has('show_in_menu') ? 1 : 0;
            $validated['is_premium'] = $request->has('is_premium') ? 1 : 0;
            
            // تخزين required_membership_types كنص JSON
            $selectedMembershipTypes = array_values(array_filter(array_map('intval', $request->input('required_membership_types', []))));
            if ($validated['access_level'] === 'membership' && count($selectedMembershipTypes) === 0) {
                return back()->withInput()->withErrors(['required_membership_types' => 'يجب اختيار نوع عضوية واحد على الأقل عند اختيار مستوى الوصول "أعضاء العضويات المدفوعة"']);
            }
            $validated['required_membership_types'] = json_encode($selectedMembershipTypes);
           
            // تعيين تاريخ النشر إذا كانت الصفحة منشورة
            if ($validated['is_published'] && !$validated['published_at']) {
                $validated['published_at'] = now();
            }

            // تنظيف المحتوى من أي أكواد ضارة (اختياري)
            $validated['content'] = $this->sanitizeContent($validated['content']);

            Log::info('Final data before creation', ['final_data' => $validated]);

            $page = Page::create($validated);

            Log::info('Page created successfully', ['page_id' => $page->id]);

            return redirect()->route('pages.index')->with('success', 'تم إنشاء الصفحة بنجاح.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in page creation', ['errors' => $e->errors()]);
            return back()->withInput()->withErrors($e->errors());
        } catch (\Exception $e) {
            Log::error('Error creating page', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withInput()->withErrors(['error' => 'حدث خطأ أثناء إنشاء الصفحة: ' . $e->getMessage()]);
        }
    }

    public function show($slug)
    {
        $page = Page::select([
                'id', 'title', 'slug', 'content', 'excerpt', 'meta_title', 
                'meta_description', 'featured_image', 'access_level', 
                'required_membership_types', 'is_published', 'published_at', 
                'user_id', 'created_at', 'updated_at'
            ])
            ->where('slug', $slug)
            ->where('is_published', true)
            ->with(['user:id,name'])
            ->firstOrFail();

        // التحقق من صلاحية الوصول
        if (!$page->canAccess(auth()->user())) {
            if (!auth()->check()) {
                return redirect()->route('login')->with('error', 'يجب تسجيل الدخول للوصول لهذه الصفحة.');
            }
            
            abort(403, 'ليس لديك صلاحية للوصول لهذه الصفحة.');
        }

        return view('pages.show', compact('page'));
    }

    public function edit(Page $page)
    {
        // التحقق من الصلاحيات
        if (!auth()->user()->hasRole('admin') && $page->user_id !== auth()->id()) {
            abort(403);
        }

        return view('pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        try {
            // التحقق من الصلاحيات
            if (!auth()->user()->hasRole('admin') && $page->user_id !== auth()->id()) {
                abort(403);
            }

            $validated = $request->validate([
                'title' => 'required|max:255',
                'content' => 'required',
                'excerpt' => 'nullable|string',
                'meta_title' => 'nullable|max:255',
                'meta_description' => 'nullable|max:160',
                'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'access_level' => 'required|in:public,authenticated,admin,user,page_manager,membership',
                'is_premium' => 'boolean',
                'required_membership_types' => 'nullable|array',
                'required_membership_types.*' => 'exists:membership_types,id',
                'menu_order' => 'nullable|integer|min:0',
                'published_at' => 'nullable|date'
            ]);

            // تحديث الـ slug إذا تغير العنوان
            if ($validated['title'] !== $page->title) {
                $newSlug = Str::slug($validated['title']);
                $originalSlug = $newSlug;
                $counter = 1;
                while (Page::where('slug', $newSlug)->where('id', '!=', $page->id)->exists()) {
                    $newSlug = $originalSlug . '-' . $counter;
                    $counter++;
                }
                $validated['slug'] = $newSlug;
            }

            // رفع صورة جديدة
            if ($request->hasFile('featured_image')) {
                // حذف الصورة القديمة
                if ($page->featured_image) {
                    Storage::disk('public')->delete($page->featured_image);
                }
                $imagePath = $request->file('featured_image')->store('pages', 'public');
                $validated['featured_image'] = $imagePath;
            }

            // معالجة القيم المنطقية
            $validated['is_published'] = $request->has('is_published') ? 1 : 0;
            $validated['show_in_menu'] = $request->has('show_in_menu') ? 1 : 0;
            $validated['is_premium'] = $request->has('is_premium') ? 1 : 0;

            // تخزين required_membership_types كنص JSON
            $selectedMembershipTypes = array_values(array_filter(array_map('intval', $request->input('required_membership_types', []))));
            if ($validated['access_level'] === 'membership' && count($selectedMembershipTypes) === 0) {
                return back()->withInput()->withErrors(['required_membership_types' => 'يجب اختيار نوع عضوية واحد على الأقل عند اختيار مستوى الوصول "أعضاء العضويات المدفوعة"']);
            }
            $validated['required_membership_types'] = json_encode($selectedMembershipTypes);
           
            // تعيين تاريخ النشر إذا كانت الصفحة منشورة لأول مرة
            if ($validated['is_published'] && !$page->is_published && !$validated['published_at']) {
                $validated['published_at'] = now();
            }

            // تنظيف المحتوى من أي أكواد ضارة (اختياري)
            $validated['content'] = $this->sanitizeContent($validated['content']);

            $page->update($validated);

            return redirect()->route('pages.index')->with('success', 'تم تحديث الصفحة بنجاح.');

        } catch (\Exception $e) {
            Log::error('Error updating page', ['error' => $e->getMessage()]);
            return back()->withInput()->withErrors(['error' => 'حدث خطأ أثناء تحديث الصفحة: ' . $e->getMessage()]);
        }
    }

    public function destroy(Page $page)
    {
        try {
            // التحقق من الصلاحيات
            if (!auth()->user()->hasRole('admin') && $page->user_id !== auth()->id()) {
                abort(403);
            }

            // حذف الصورة المميزة
            if ($page->featured_image) {
                Storage::disk('public')->delete($page->featured_image);
            }

            $page->delete();

            return redirect()->route('pages.index')->with('success', 'تم حذف الصفحة بنجاح.');

        } catch (\Exception $e) {
            Log::error('Error deleting page', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'حدث خطأ أثناء حذف الصفحة: ' . $e->getMessage()]);
        }
    }

    public function publicIndex()
    {
        $pages = Page::published()
            ->accessibleBy(auth()->user())
            ->with('user')
            ->latest('published_at')
            ->paginate(12);

        return view('pages.public', compact('pages'));
    }

    /**
     * تنظيف المحتوى من الأكواد الضارة (اختياري)
     */
    private function sanitizeContent($content)
    {
        // يمكنك إضافة منطق تنظيف المحتوى هنا إذا كنت تريد
        // مثل إزالة الـ JavaScript أو تنظيف HTML
        
        // في الوقت الحالي سنعيد المحتوى كما هو
        // لأن TinyMCE يقوم بالتنظيف بالفعل
        return $content;
    }
}