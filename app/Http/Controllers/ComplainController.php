<?php

namespace App\Http\Controllers;

use App\Models\Complain;
use App\Models\ComplainCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\HashidsHelper;

class ComplainController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = ComplainCategory::orderBy('name', 'asc')->get();
        return view('complains.index', compact('categories'));
    }

    /**
     * Get complains data for DataTables
     */
    public function getComplainsData(Request $request)
    {
        try {
            if ($request->ajax()) {
                $user = auth()->user();
                $branchId = $user->branch_id;
                $companyId = $user->company_id;

                $categoryId = $request->get('category_id');
                
                $complains = Complain::with(['customer', 'category', 'respondedBy', 'branch'])
                    ->where('company_id', $companyId)
                    ->when($branchId, function ($query) use ($branchId) {
                        return $query->where('branch_id', $branchId);
                    })
                    ->when($categoryId, function ($query) use ($categoryId) {
                        return $query->where('complain_category_id', $categoryId);
                    })
                    ->select('complains.*')
                    ->orderBy('complains.created_at', 'desc');

                return DataTables::eloquent($complains)
                    ->addIndexColumn()
                    ->addColumn('customer_name', function ($complain) {
                        return $complain->customer->name ?? 'N/A';
                    })
                    ->addColumn('customer_number', function ($complain) {
                        return $complain->customer->customerNo ?? 'N/A';
                    })
                    ->addColumn('category_name', function ($complain) {
                        return $complain->category->name ?? 'N/A';
                    })
                    ->addColumn('priority_badge', function ($complain) {
                        $priority = $complain->category->priority ?? 'medium';
                        $badgeColor = match($priority) {
                            'low' => 'success',
                            'medium' => 'warning',
                            'high' => 'danger',
                            default => 'secondary',
                        };
                        return '<span class="badge bg-' . $badgeColor . '">' . ucfirst($priority) . '</span>';
                    })
                    ->addColumn('status_badge', function ($complain) {
                        $badgeColor = $complain->status_badge;
                        return '<span class="badge bg-' . $badgeColor . '">' . ucfirst(str_replace('_', ' ', $complain->status)) . '</span>';
                    })
                    ->addColumn('description_short', function ($complain) {
                        return Str::limit($complain->description, 50);
                    })
                    ->addColumn('responded_by_name', function ($complain) {
                        return $complain->respondedBy->name ?? 'N/A';
                    })
                    ->addColumn('created_at', function ($complain) {
                        return $complain->created_at->format('Y-m-d H:i:s');
                    })
                    ->addColumn('actions', function ($complain) {
                        $encodedId = HashidsHelper::encode($complain->id);
                        $html = '<div class="d-flex gap-2">';
                        $html .= '<a href="' . route('complains.show', $encodedId) . '" class="btn btn-sm btn-outline-info" title="View">';
                        $html .= '<i class="bx bx-show"></i>';
                        $html .= '</a>';
                        if ($complain->status !== 'resolved' && $complain->status !== 'closed') {
                            $html .= '<a href="' . route('complains.edit', $encodedId) . '" class="btn btn-sm btn-outline-warning" title="Respond">';
                            $html .= '<i class="bx bx-edit"></i>';
                            $html .= '</a>';
                        }
                        $html .= '</div>';
                        return $html;
                    })
                    ->rawColumns(['status_badge', 'priority_badge', 'actions'])
                    ->make(true);
            }
            
            return response()->json(['error' => 'Invalid request'], 400);
        } catch (\Exception $e) {
            \Log::error('Error loading complains data: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load complains data: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($encodedId)
    {
        $id = HashidsHelper::decode($encodedId);
        if (empty($id)) {
            abort(404);
        }

        $complain = Complain::with(['customer', 'category', 'respondedBy', 'branch', 'company'])
            ->findOrFail($id[0]);

        return view('complains.show', compact('complain', 'encodedId'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($encodedId)
    {
        $id = HashidsHelper::decode($encodedId);
        if (empty($id)) {
            abort(404);
        }

        $complain = Complain::with(['customer', 'category', 'respondedBy'])
            ->findOrFail($id[0]);

        return view('complains.edit', compact('complain', 'encodedId'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $encodedId)
    {
        $id = HashidsHelper::decode($encodedId);
        if (empty($id)) {
            abort(404);
        }

        $complain = Complain::findOrFail($id[0]);

        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,resolved,closed',
            'response' => 'required|string|min:10',
        ]);

        $complain->update([
            'status' => $validated['status'],
            'response' => $validated['response'],
            'responded_by' => auth()->id(),
            'responded_at' => now(),
        ]);

        return redirect()->route('complains.show', $encodedId)
            ->with('success', 'Complain response updated successfully.');
    }
}
