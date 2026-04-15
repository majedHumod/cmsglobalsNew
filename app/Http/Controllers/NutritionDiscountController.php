<?php

namespace App\Http\Controllers;

use App\Models\NutritionDiscount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class NutritionDiscountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $discounts = NutritionDiscount::orderBy('created_at', 'desc')->paginate(10);
        return view('nutrition-discounts.index', compact('discounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('nutrition-discounts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'discount_percentage' => 'required|integer|min:1|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        
        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('nutrition-discounts', 'public');
            $data['image'] = $imagePath;
        }

        $data['is_active'] = $request->has('is_active');

        NutritionDiscount::create($data);

        return redirect()->route('nutrition-discounts.index')
            ->with('success', __('Nutrition discount created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(NutritionDiscount $nutritionDiscount)
    {
        return view('nutrition-discounts.show', compact('nutritionDiscount'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(NutritionDiscount $nutritionDiscount)
    {
        return view('nutrition-discounts.edit', compact('nutritionDiscount'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, NutritionDiscount $nutritionDiscount)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'discount_percentage' => 'required|integer|min:1|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        
        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($nutritionDiscount->image) {
                Storage::disk('public')->delete($nutritionDiscount->image);
            }
            $imagePath = $request->file('image')->store('nutrition-discounts', 'public');
            $data['image'] = $imagePath;
        }

        $data['is_active'] = $request->has('is_active');

        $nutritionDiscount->update($data);

        return redirect()->route('nutrition-discounts.index')
            ->with('success', __('Nutrition discount updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NutritionDiscount $nutritionDiscount)
    {
        // Delete image if exists
        if ($nutritionDiscount->image) {
            Storage::disk('public')->delete($nutritionDiscount->image);
        }

        $nutritionDiscount->delete();

        return redirect()->route('nutrition-discounts.index')
            ->with('success', __('Nutrition discount deleted successfully.'));
    }

    /**
     * Toggle the active status of the discount
     */
    public function toggleStatus(NutritionDiscount $nutritionDiscount)
    {
        $nutritionDiscount->update(['is_active' => !$nutritionDiscount->is_active]);

        $status = $nutritionDiscount->is_active ? 'activated' : 'deactivated';
        
        return redirect()->route('nutrition-discounts.index')
            ->with('success', __("Nutrition discount {$status} successfully."));
    }

    /**
     * Display active discounts for frontend
     */
    public function frontend()
    {
        $discounts = NutritionDiscount::active()
            ->valid()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('nutrition-discounts.frontend', compact('discounts'));
    }
}
