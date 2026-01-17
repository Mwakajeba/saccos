<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Helpers\HashidsHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $announcements = Announcement::with('creator')
            ->orderBy('order', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('settings.announcements.index', compact('announcements'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('settings.announcements.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'icon' => 'nullable|string|max:50',
            'color' => 'required|in:blue,green,orange,red,purple,yellow',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $announcement = new Announcement($validated);
        $announcement->company_id = Auth::user()->company_id;
        $announcement->branch_id = Auth::user()->branch_id;
        $announcement->created_by = Auth::id();
        // Default to active if checkbox is checked, otherwise default to true for new announcements
        $announcement->is_active = $request->has('is_active') ? ($request->input('is_active') == '1' || $request->input('is_active') === true) : true;
        $announcement->order = $request->order ?? 0;

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('announcements', 'public');
            $announcement->image_path = $imagePath;
        }

        $announcement->save();

        return redirect()->route('settings.announcements.index')
            ->with('success', 'Announcement created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($encodedId)
    {
        $id = HashidsHelper::decode($encodedId)[0] ?? null;
        $announcement = Announcement::findOrFail($id);
        
        return view('settings.announcements.show', compact('announcement'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($encodedId)
    {
        $id = HashidsHelper::decode($encodedId)[0] ?? null;
        $announcement = Announcement::findOrFail($id);
        
        return view('settings.announcements.edit', compact('announcement'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $encodedId)
    {
        $id = HashidsHelper::decode($encodedId)[0] ?? null;
        $announcement = Announcement::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'icon' => 'nullable|string|max:50',
            'color' => 'required|in:blue,green,orange,red,purple,yellow',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $announcement->fill($validated);
        $announcement->is_active = $request->has('is_active') ? true : false;
        $announcement->order = $request->order ?? $announcement->order;

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($announcement->image_path && Storage::disk('public')->exists($announcement->image_path)) {
                Storage::disk('public')->delete($announcement->image_path);
            }
            $imagePath = $request->file('image')->store('announcements', 'public');
            $announcement->image_path = $imagePath;
        }

        $announcement->save();

        return redirect()->route('settings.announcements.index')
            ->with('success', 'Announcement updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($encodedId)
    {
        $id = HashidsHelper::decode($encodedId)[0] ?? null;
        $announcement = Announcement::findOrFail($id);

        // Delete image if exists
        if ($announcement->image_path && Storage::disk('public')->exists($announcement->image_path)) {
            Storage::disk('public')->delete($announcement->image_path);
        }

        $announcement->delete();

        return redirect()->route('settings.announcements.index')
            ->with('success', 'Announcement deleted successfully.');
    }
}
