<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\Customer;
use App\Models\LabTest;
use App\Helpers\HashidsHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class ConsultationController extends Controller
{
    /**
     * Display a listing of consultations
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $consultations = Consultation::with(['customer', 'doctor'])
                ->where('branch_id', auth()->user()->branch_id)
                ->select('consultations.*');

            return DataTables::eloquent($consultations)
                ->addColumn('customer_name', function ($consultation) {
                    return $consultation->customer->name ?? '';
                })
                ->addColumn('doctor_name', function ($consultation) {
                    return $consultation->doctor->name ?? '';
                })
                ->addColumn('status', function ($consultation) {
                    $badgeClass = $consultation->status == 'active' ? 'success' : ($consultation->status == 'completed' ? 'info' : 'danger');
                    return '<span class="badge bg-' . $badgeClass . '">' . ucfirst($consultation->status) . '</span>';
                })
                ->addColumn('actions', function ($consultation) {
                    $encodedId = HashidsHelper::encode($consultation->id);
                    return '<a href="' . route('consultations.show', $encodedId) . '" class="btn btn-sm btn-primary">
                                <i class="bx bx-show"></i> View
                            </a>';
                })
                ->rawColumns(['status', 'actions'])
                ->make(true);
        }

        return view('consultations.index');
    }

    /**
     * Show the form for creating a new consultation
     */
    public function create()
    {
        $customers = Customer::where('branch_id', auth()->user()->branch_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
        
        return view('consultations.create', compact('customers'));
    }

    /**
     * Store a newly created consultation
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'consultation_date' => 'required|date',
            'chief_complaint' => 'nullable|string',
            'history_of_present_illness' => 'nullable|string',
            'physical_examination' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'treatment_plan' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();
            
            $user = auth()->user();
            $consultation = Consultation::create([
                'consultation_number' => $this->generateConsultationNumber(),
                'customer_id' => $request->customer_id,
                'doctor_id' => $user->id,
                'consultation_date' => $request->consultation_date,
                'chief_complaint' => $request->chief_complaint,
                'history_of_present_illness' => $request->history_of_present_illness,
                'physical_examination' => $request->physical_examination,
                'diagnosis' => $request->diagnosis,
                'treatment_plan' => $request->treatment_plan,
                'notes' => $request->notes,
                'status' => 'active',
                'branch_id' => $user->branch_id,
                'company_id' => $user->company_id,
                'created_by' => $user->id,
            ]);

            DB::commit();

            return redirect()->route('consultations.show', HashidsHelper::encode($consultation->id))
                ->with('success', 'Consultation created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating consultation: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to create consultation: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified consultation
     */
    public function show($encodedId)
    {
        $id = HashidsHelper::decode($encodedId)[0] ?? null;
        if (!$id) {
            abort(404);
        }

        $consultation = Consultation::with(['customer', 'doctor', 'labTests.bill', 'labTests.result'])
            ->findOrFail($id);

        return view('consultations.show', compact('consultation', 'encodedId'));
    }

    /**
     * Show the form for editing the consultation
     */
    public function edit($encodedId)
    {
        $id = HashidsHelper::decode($encodedId)[0] ?? null;
        if (!$id) {
            abort(404);
        }

        $consultation = Consultation::findOrFail($id);
        $customers = Customer::where('branch_id', auth()->user()->branch_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('consultations.edit', compact('consultation', 'customers', 'encodedId'));
    }

    /**
     * Update the consultation
     */
    public function update(Request $request, $encodedId)
    {
        $id = HashidsHelper::decode($encodedId)[0] ?? null;
        if (!$id) {
            abort(404);
        }

        $request->validate([
            'consultation_date' => 'required|date',
            'chief_complaint' => 'nullable|string',
            'history_of_present_illness' => 'nullable|string',
            'physical_examination' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'treatment_plan' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,completed,cancelled',
        ]);

        try {
            $consultation = Consultation::findOrFail($id);
            $consultation->update($request->only([
                'consultation_date',
                'chief_complaint',
                'history_of_present_illness',
                'physical_examination',
                'diagnosis',
                'treatment_plan',
                'notes',
                'status',
            ]));

            return redirect()->route('consultations.show', $encodedId)
                ->with('success', 'Consultation updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating consultation: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to update consultation: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the consultation
     */
    public function destroy($encodedId)
    {
        $id = HashidsHelper::decode($encodedId)[0] ?? null;
        if (!$id) {
            abort(404);
        }

        try {
            $consultation = Consultation::findOrFail($id);
            $consultation->delete();

            return redirect()->route('consultations.index')
                ->with('success', 'Consultation deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting consultation: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to delete consultation: ' . $e->getMessage());
        }
    }

    /**
     * Generate unique consultation number
     */
    private function generateConsultationNumber()
    {
        $year = date('Y');
        $last = Consultation::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $number = $last ? ((int) substr($last->consultation_number, -6)) + 1 : 1;
        return 'CONS-' . $year . '-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }
}
