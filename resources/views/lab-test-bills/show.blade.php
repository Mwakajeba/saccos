@extends('layouts.main')

@section('title', 'Lab Test Bill')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Lab Test Bills', 'url' => route('lab-test-bills.index'), 'icon' => 'bx bx-money'],
            ['label' => $bill->bill_number, 'url' => '#', 'icon' => 'bx bx-show']
        ]" />
        <h6 class="mb-0 text-uppercase">LAB TEST BILL</h6>
        <hr />

        <div class="row">
            <div class="col-md-8">
                <div class="card radius-10">
                    <div class="card-body">
                        <h6 class="mb-3">Bill Information</h6>
                        <table class="table table-borderless">
                            <tr>
                                <th width="200">Bill Number:</th>
                                <td>{{ $bill->bill_number }}</td>
                            </tr>
                            <tr>
                                <th>Test Number:</th>
                                <td>{{ $bill->labTest->test_number }}</td>
                            </tr>
                            <tr>
                                <th>Test Name:</th>
                                <td>{{ $bill->labTest->test_name }}</td>
                            </tr>
                            <tr>
                                <th>Patient:</th>
                                <td>{{ $bill->customer->name }} ({{ $bill->customer->customerNo }})</td>
                            </tr>
                            <tr>
                                <th>Amount:</th>
                                <td><strong>{{ number_format($bill->amount, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <th>Paid Amount:</th>
                                <td>{{ number_format($bill->paid_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Balance:</th>
                                <td><strong class="text-{{ $bill->balance > 0 ? 'danger' : 'success' }}">{{ number_format($bill->balance, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <th>Payment Status:</th>
                                <td>
                                    <span class="badge bg-{{ $bill->payment_status == 'paid' ? 'success' : 'warning' }}">
                                        {{ ucfirst($bill->payment_status) }}
                                    </span>
                                </td>
                            </tr>
                        </table>

                        @if($bill->payment_status != 'paid')
                            <hr>
                            <h6>Process Payment</h6>
                            <form action="{{ route('lab-test-bills.process-payment', $encodedId) }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                                        <input type="number" name="paid_amount" step="0.01" max="{{ $bill->balance }}" 
                                               class="form-control" required>
                                        <small class="text-muted">Balance: {{ number_format($bill->balance, 2) }}</small>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Payment Notes</label>
                                        <textarea name="payment_notes" class="form-control" rows="3"></textarea>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-success">Process Payment</button>
                            </form>
                        @else
                            <div class="alert alert-success">
                                <strong>Payment Completed!</strong> This bill has been fully paid.
                                @if($bill->paid_at)
                                    <br>Paid on: {{ $bill->paid_at->format('d M Y H:i') }}
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
