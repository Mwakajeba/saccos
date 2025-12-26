<?php

namespace App\Http\Controllers;

use App\Models\JournalReference;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;

class JournalReferenceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;
        $companyId = $user->company_id;
        
        $journalReferences = JournalReference::where('company_id', $companyId)
            ->where(function($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->latest()
            ->get();
            
        return view('settings.journal-references.index', compact('journalReferences'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('settings.journal-references.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'reference' => 'required|string|max:255',
        ]);

        $validated['company_id'] = auth()->user()->company_id;
        $validated['branch_id'] = auth()->user()->branch_id;

        JournalReference::create($validated);

        return redirect()->route('settings.journal-references.index')
            ->with('success', 'Journal reference created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JournalReference $journal_reference)
    {
        return view('settings.journal-references.edit', compact('journal_reference'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JournalReference $journal_reference)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'reference' => 'required|string|max:255',
        ]);

        $journal_reference->update($validated);

        return redirect()->route('settings.journal-references.index')
            ->with('success', 'Journal reference updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JournalReference $journal_reference)
    {
        $journal_reference->delete();

        return redirect()->route('settings.journal-references.index')
            ->with('success', 'Journal reference deleted successfully.');
    }
}
