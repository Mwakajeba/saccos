<?php

namespace App\Http\Controllers;

use App\Models\LabTest;
use App\Models\Consultation;
use App\Models\LabTestBill;
use App\Models\LabTestResult;
use App\Helpers\HashidsHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class LabTestController extends Controller
{
    /**
     * Display a listing of lab tests (for lab staff to review)
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending_review');
        
        if ($request->ajax()) {
            $labTests = LabTest::with(['customer', 'doctor'])
                ->where('branch_id', auth()->user()->branch_id)
                ->where('status', $status)
                ->select('lab_tests.*');

            return DataTables::eloquent($labTests)
                ->addColumn('customer_name', function ($labTest) {
                    return $labTest->customer->name ?? '';
                })
                ->addColumn('doctor_name', function ($labTest) {
                    return $labTest->doctor->name ?? '';
                })
                ->addColumn('status', function ($labTest) {
                    return '<span class="badge bg-warning">' . str_replace('_', ' ', ucwords($labTest->status)) . '</span>';
                })
                ->addColumn('actions', function ($labTest) {
                    $encodedId = HashidsHelper::encode($labTest->id);
                    return '<a href="' . route('lab-tests.show', $encodedId) . '" class="btn btn-sm btn-primary">
                                <i class="bx bx-show"></i> View
                            </a>';
                })
                ->rawColumns(['status', 'actions'])
                ->make(true);
        }

        return view('lab-tests.index', compact('status'));
    }

    /**
     * Show the form for creating a new lab test (from consultation)
     */
    public function create($consultationEncodedId)
    {
        $consultationId = HashidsHelper::decode($consultationEncodedId)[0] ?? null;
        if (!$consultationId) {
            abort(404);
        }

        $consultation = Consultation::with('customer')->findOrFail($consultationId);
        
        return view('lab-tests.create', compact('consultation', 'consultationEncodedId'));
    }

    /**
     * Store a newly created lab test
     */
    public function store(Request $request, $consultationEncodedId)
    {
        $consultationId = HashidsHelper::decode($consultationEncodedId)[0] ?? null;
        if (!$consultationId) {
            abort(404);
        }

        $request->validate([
            'test_name' => 'required|string|max:255',
            'test_description' => 'nullable|string',
            'clinical_notes' => 'nullable|string',
            'instructions' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();
            
            $consultation = Consultation::findOrFail($consultationId);
            $user = auth()->user();

            $labTest = LabTest::create([
                'test_number' => $this->generateTestNumber(),
                'consultation_id' => $consultation->id,
                'customer_id' => $consultation->customer_id,
                'doctor_id' => $consultation->doctor_id,
                'test_name' => $request->test_name,
                'test_description' => $request->test_description,
                'clinical_notes' => $request->clinical_notes,
                'instructions' => $request->instructions,
                'status' => 'pending_review', // Created by doctor, needs lab review
                'branch_id' => $user->branch_id,
                'company_id' => $user->company_id,
                'created_by' => $user->id,
            ]);

            DB::commit();

            return redirect()->route('consultations.show', $consultationEncodedId)
                ->with('success', 'Lab test requested successfully. Waiting for lab review.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating lab test: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to create lab test: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified lab test
     */
    public function show($encodedId)
    {
        $id = HashidsHelper::decode($encodedId)[0] ?? null;
        if (!$id) {
            abort(404);
        }

        $labTest = LabTest::with([
            'consultation.customer',
            'customer',
            'doctor',
            'bill',
            'result'
        ])->findOrFail($id);

        return view('lab-tests.show', compact('labTest', 'encodedId'));
    }

    /**
     * Review lab test and create bill (Lab staff action)
     */
    public function review(Request $request, $encodedId)
    {
        $id = HashidsHelper::decode($encodedId)[0] ?? null;
        if (!$id) {
            abort(404);
        }

        $request->validate([
            'amount' => 'required|numeric|min:0',
            'due_date' => 'nullable|date',
        ]);

        try {
            DB::beginTransaction();
            
            $labTest = LabTest::findOrFail($id);
            
            if (!$labTest->canBeReviewed()) {
                return redirect()->back()
                    ->with('error', 'This lab test cannot be reviewed in its current status.');
            }

            $user = auth()->user();

            // Create bill
            $bill = LabTestBill::create([
                'bill_number' => $this->generateBillNumber(),
                'lab_test_id' => $labTest->id,
                'customer_id' => $labTest->customer_id,
                'amount' => $request->amount,
                'paid_amount' => 0,
                'bill_date' => now(),
                'due_date' => $request->due_date ?? now()->addDays(7),
                'payment_status' => 'pending',
                'branch_id' => $user->branch_id,
                'company_id' => $user->company_id,
                'created_by' => $user->id,
            ]);

            // Update lab test status
            $labTest->update([
                'status' => 'pending_payment',
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('lab-tests.show', $encodedId)
                ->with('success', 'Lab test reviewed and bill created. Sent to cashier for payment.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error reviewing lab test: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to review lab test: ' . $e->getMessage());
        }
    }

    /**
     * Mark test as taken (Lab staff action after payment)
     */
    public function takeTest($encodedId)
    {
        $id = HashidsHelper::decode($encodedId)[0] ?? null;
        if (!$id) {
            abort(404);
        }

        try {
            $labTest = LabTest::with('bill')->findOrFail($id);
            
            if (!$labTest->canTakeTest()) {
                return redirect()->back()
                    ->with('error', 'Test cannot be taken. Payment must be completed first.');
            }

            $labTest->update([
                'status' => 'test_taken',
                'test_taken_by' => auth()->id(),
                'test_taken_at' => now(),
            ]);

            return redirect()->route('lab-tests.show', $encodedId)
                ->with('success', 'Test sample taken from patient. You can now submit results.');
        } catch (\Exception $e) {
            Log::error('Error taking test: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to mark test as taken: ' . $e->getMessage());
        }
    }

    /**
     * Submit test results (Lab staff action)
     */
    public function submitResults(Request $request, $encodedId)
    {
        $id = HashidsHelper::decode($encodedId)[0] ?? null;
        if (!$id) {
            abort(404);
        }

        $request->validate([
            'results' => 'nullable|string',
            'findings' => 'nullable|string',
            'interpretation' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'result_file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        try {
            DB::beginTransaction();
            
            $labTest = LabTest::findOrFail($id);
            
            if (!$labTest->canSubmitResults()) {
                return redirect()->back()
                    ->with('error', 'Results cannot be submitted. Test must be taken first.');
            }

            $user = auth()->user();
            $filePath = null;

            if ($request->hasFile('result_file')) {
                $file = $request->file('result_file');
                $fileName = 'lab_result_' . $labTest->test_number . '_' . time() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('lab_results', $fileName, 'public');
            }

            // Create or update result
            $result = LabTestResult::updateOrCreate(
                ['lab_test_id' => $labTest->id],
                [
                    'customer_id' => $labTest->customer_id,
                    'results' => $request->results,
                    'findings' => $request->findings,
                    'interpretation' => $request->interpretation,
                    'recommendations' => $request->recommendations,
                    'result_file' => $filePath,
                    'status' => 'submitted',
                    'submitted_by' => $user->id,
                    'submitted_at' => now(),
                    'branch_id' => $user->branch_id,
                    'company_id' => $user->company_id,
                ]
            );

            // Update lab test status
            $labTest->update([
                'status' => 'results_submitted',
                'results_submitted_by' => $user->id,
                'results_submitted_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('lab-tests.show', $encodedId)
                ->with('success', 'Test results submitted successfully. You can now send to doctor.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error submitting results: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to submit results: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Send results to doctor (Lab staff action)
     */
    public function sendToDoctor($encodedId)
    {
        $id = HashidsHelper::decode($encodedId)[0] ?? null;
        if (!$id) {
            abort(404);
        }

        try {
            DB::beginTransaction();
            
            $labTest = LabTest::with('result')->findOrFail($id);
            
            if (!$labTest->canSendToDoctor()) {
                return redirect()->back()
                    ->with('error', 'Results cannot be sent. Results must be submitted first.');
            }

            if (!$labTest->result) {
                return redirect()->back()
                    ->with('error', 'No results found. Please submit results first.');
            }

            $user = auth()->user();

            // Update result status
            $labTest->result->update([
                'status' => 'sent_to_doctor',
                'sent_to_doctor_by' => $user->id,
                'sent_to_doctor_at' => now(),
            ]);

            // Update lab test status
            $labTest->update([
                'status' => 'results_sent_to_doctor',
            ]);

            DB::commit();

            return redirect()->route('lab-tests.show', $encodedId)
                ->with('success', 'Results sent to doctor successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error sending results to doctor: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to send results to doctor: ' . $e->getMessage());
        }
    }

    /**
     * View results (Doctor action)
     */
    public function viewResults($encodedId)
    {
        $id = HashidsHelper::decode($encodedId)[0] ?? null;
        if (!$id) {
            abort(404);
        }

        try {
            $labTest = LabTest::with('result')->findOrFail($id);
            
            if (!$labTest->result || $labTest->result->status !== 'sent_to_doctor') {
                return redirect()->back()
                    ->with('error', 'Results are not available or not yet sent.');
            }

            // Mark as viewed
            $labTest->result->update([
                'status' => 'viewed_by_doctor',
                'viewed_by_doctor' => auth()->id(),
                'viewed_at' => now(),
            ]);

            return view('lab-tests.view-results', compact('labTest', 'encodedId'));
        } catch (\Exception $e) {
            Log::error('Error viewing results: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to view results: ' . $e->getMessage());
        }
    }

    /**
     * Generate unique test number
     */
    private function generateTestNumber()
    {
        $year = date('Y');
        $last = LabTest::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $number = $last ? ((int) substr($last->test_number, -6)) + 1 : 1;
        return 'LAB-' . $year . '-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique bill number
     */
    private function generateBillNumber()
    {
        $year = date('Y');
        $last = LabTestBill::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $number = $last ? ((int) substr($last->bill_number, -6)) + 1 : 1;
        return 'LAB-BILL-' . $year . '-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }
}
