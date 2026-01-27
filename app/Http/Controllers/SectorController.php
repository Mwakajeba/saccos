<?php

namespace App\Http\Controllers;

use App\Models\Sector;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SectorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $sectors = Sector::query();

            return DataTables::of($sectors)
                ->addIndexColumn()
                ->addColumn('status', function ($sector) {
                    if ($sector->status === 'active') {
                        return '<span class="badge bg-success">Active</span>';
                    } else {
                        return '<span class="badge bg-danger">Inactive</span>';
                    }
                })
                ->addColumn('actions', function ($sector) {
                    $actions = '';
                    if (auth()->user()->can('edit sector')) {
                        $actions .= '<a href="' . route('settings.sectors.edit', $sector->id) . '" class="btn btn-sm btn-warning"><i class="bx bx-edit"></i></a> ';
                    }
                    if (auth()->user()->can('delete sector')) {
                        $actions .= '<button type="button" class="btn btn-sm btn-danger delete-sector" data-id="' . $sector->id . '" data-name="' . $sector->name . '"><i class="bx bx-trash"></i></button>';
                    }
                    return $actions ?: '<span class="text-muted">No actions</span>';
                })
                ->rawColumns(['status', 'actions'])
                ->make(true);
        }

        return view('settings.sectors.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_if(!auth()->user()->can('create sector'), 403, 'Unauthorized action.');
        return view('settings.sectors.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_if(!auth()->user()->can('create sector'), 403, 'Unauthorized action.');
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:sectors,name',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        Sector::create($validated);

        return redirect()->route('settings.sectors.index')->with('success', 'Sector created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $sector = Sector::findOrFail($id);
        return view('settings.sectors.show', compact('sector'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        abort_if(!auth()->user()->can('edit sector'), 403, 'Unauthorized action.');
        $sector = Sector::findOrFail($id);
        return view('settings.sectors.edit', compact('sector'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        abort_if(!auth()->user()->can('edit sector'), 403, 'Unauthorized action.');
        
        $sector = Sector::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:sectors,name,' . $sector->id,
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $sector->update($validated);

        return redirect()->route('settings.sectors.index')->with('success', 'Sector updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (!auth()->user()->can('delete sector')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }
        
        try {
            $sector = Sector::findOrFail($id);
            $sector->delete();
            return response()->json(['success' => true, 'message' => 'Sector deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete sector: ' . $e->getMessage()], 500);
        }
    }
}
