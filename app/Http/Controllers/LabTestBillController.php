<?php

namespace App\Http\Controllers;

use App\Models\LabTestBill;
use App\Models\LabTest;
use App\Helpers\HashidsHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class LabTestBillController extends Controller
{
    /**
     * Display a listing of lab test bills (for cashier)
     */
    public function index(Request $request)
    {
        $status = $request->get('payment_status', 'pending');
        
        if ($request->ajax()) {
            $bills = LabTestBill::with(['customer', 'labTest'])
                ->where('branch_id', auth()->user()->branch_id)
                ->where('payment_status', $status)
                ->select('lab_test_bills.*');

            return DataTables::eloquent($bills)
                ->addColumn('customer_name', function ($bill) {
                    return $bill->customer->name ?? '';
                })
                ->addColumn('test_number', function ($bill) {
                    return $bill->labTest->test_number ?? '';
                })
                ->addColumn('amount', function ($bill) {
                    return number_format($bill->amount, 2);
                })
                ->addColumn('paid_amount', function ($bill) {
                    return number_format($bill->paid_amount, 2);
                })
                ->addColumn('balance', function ($bill) {
                    return number_format($bill->balance, 2);
                })
                ->addColumn('payment_status', function ($bill) {
                    $badgeClass = $bill->payment_status == 'paid' ? 'success' : 'warning';
                    return '<span class="badge bg-' . $badgeClass . '">' . ucfirst($bill->payment_status) . '</span>';
                })
                ->addColumn('actions', function ($bill) {
                    $encodedId = HashidsHelper::encode($bill->id);
                    return '<a href="' . route('lab-test-bills.show', $encodedId) . '" class="btn btn-sm btn-primary">
                                <i class="bx bx-show"></i> View
                            </a>';
                })
                ->rawColumns(['payment_status', 'actions'])
                ->make(true);
        }

        return view('lab-test-bills.index', compact('status'));
    }

    /**
     * Display the specified bill
     */
    public function show($encodedId)
    {
        $id = HashidsHelper::decode($encodedId)[0] ?? null;
        if (!$id) {
            abort(404);
        }

        $bill = LabTestBill::with([
            'labTest.consultation.customer',
            'labTest.customer',
            'labTest.doctor',
            'customer'
        ])->findOrFail($id);

        return view('lab-test-bills.show', compact('bill', 'encodedId'));
    }

    /**
     * Process payment for lab test bill (Cashier action)
     */
    public function processPayment(Request $request, $encodedId)
    {
        $id = HashidsHelper::decode($encodedId)[0] ?? null;
        if (!$id) {
            abort(404);
        }

        $request->validate([
            'paid_amount' => 'required|numeric|min:0',
            'payment_notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();
            
            $bill = LabTestBill::with('labTest')->findOrFail($id);
            
            if ($bill->isPaid()) {
                return redirect()->back()
                    ->with('error', 'This bill is already paid.');
            }

            $paidAmount = $request->paid_amount;
            $newPaidAmount = $bill->paid_amount + $paidAmount;

            // Determine payment status
            $paymentStatus = 'partial';
            if ($newPaidAmount >= $bill->amount) {
                $paymentStatus = 'paid';
                $newPaidAmount = $bill->amount; // Don't allow overpayment
            }

            // Update bill
            $bill->update([
                'paid_amount' => $newPaidAmount,
                'payment_status' => $paymentStatus,
                'paid_by' => auth()->id(),
                'paid_at' => now(),
                'payment_notes' => $request->payment_notes,
            ]);

            // If fully paid, update lab test status
            if ($paymentStatus === 'paid') {
                $bill->labTest->update([
                    'status' => 'paid',
                ]);
            }

            DB::commit();

            $message = $paymentStatus === 'paid' 
                ? 'Payment processed successfully. Lab test is now ready for sample collection.'
                : 'Partial payment processed. Remaining balance: ' . number_format($bill->balance, 2);

            return redirect()->route('lab-test-bills.show', $encodedId)
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing payment: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to process payment: ' . $e->getMessage());
        }
    }
}
