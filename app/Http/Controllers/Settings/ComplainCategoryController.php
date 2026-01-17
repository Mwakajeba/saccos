<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ComplainCategory;
use Illuminate\Http\Request;

class ComplainCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = ComplainCategory::withCount('complains')
            ->orderBy('priority', 'desc')
            ->orderBy('name', 'asc')
            ->get();
        
        return view('settings.complain-categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('settings.complain-categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:complain_categories,name',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high',
        ]);

        ComplainCategory::create($validated);

        return redirect()->route('settings.complain-categories.index')
            ->with('success', 'Complain category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ComplainCategory $complainCategory)
    {
        return view('settings.complain-categories.show', compact('complainCategory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ComplainCategory $complainCategory)
    {
        return view('settings.complain-categories.edit', compact('complainCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ComplainCategory $complainCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:complain_categories,name,' . $complainCategory->id,
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high',
        ]);

        $complainCategory->update($validated);

        return redirect()->route('settings.complain-categories.index')
            ->with('success', 'Complain category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ComplainCategory $complainCategory)
    {
        $complainCategory->delete();

        return redirect()->route('settings.complain-categories.index')
            ->with('success', 'Complain category deleted successfully.');
    }
}
