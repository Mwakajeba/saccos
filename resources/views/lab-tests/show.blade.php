@extends('layouts.main')

@section('title', 'Lab Test Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Lab Tests', 'url' => route('lab-tests.index'), 'icon' => 'bx bx-test-tube'],
            ['label' => $labTest->test_number, 'url' => '#', 'icon' => 'bx bx-show']
        ]" />
        <h6 class="mb-0 text-uppercase">LAB TEST DETAILS</h6>
        <hr />

        <div class="row">
            <div class="col-md-8">
                <div class="card radius-10">
                    <div class="card-body">
                        <h6 class="mb-3">Test Information</h6>
                        <table class="table table-borderless">
                            <tr>
                                <th width="200">Test Number:</th>
                                <td>{{ $labTest->test_number }}</td>
                            </tr>
                            <tr>
                                <th>Test Name:</th>
                                <td>{{ $labTest->test_name }}</td>
                            </tr>
                            <tr>
                                <th>Patient:</th>
                                <td>{{ $labTest->customer->name }} ({{ $labTest->customer->customerNo }})</td>
                            </tr>
                            <tr>
                                <th>Doctor:</th>
                                <td>{{ $labTest->doctor->name }}</td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <span class="badge bg-{{ $labTest->status == 'results_sent_to_doctor' ? 'success' : 'warning' }}">
                                        {{ str_replace('_', ' ', ucwords($labTest->status)) }}
                                    </span>
                                </td>
                            </tr>
                            @if($labTest->test_description)
                            <tr>
                                <th>Description:</th>
                                <td>{{ $labTest->test_description }}</td>
                            </tr>
                            @endif
                            @if($labTest->clinical_notes)
                            <tr>
                                <th>Clinical Notes:</th>
                                <td>{{ $labTest->clinical_notes }}</td>
                            </tr>
                            @endif
                        </table>

                        @if($labTest->status == 'pending_review')
                            <hr>
                            <h6>Lab Review & Create Bill</h6>
                            <form action="{{ route('lab-tests.review', $encodedId) }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                                        <input type="number" name="amount" step="0.01" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Due Date</label>
                                        <input type="date" name="due_date" class="form-control" value="{{ date('Y-m-d', strtotime('+7 days')) }}">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-success">Review & Create Bill</button>
                            </form>
                        @endif

                        @if($labTest->status == 'paid')
                            <hr>
                            <form action="{{ route('lab-tests.take-test', $encodedId) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary">Mark Test as Taken</button>
                            </form>
                        @endif

                        @if($labTest->status == 'test_taken')
                            <hr>
                            <h6>Submit Test Results</h6>
                            <form action="{{ route('lab-tests.submit-results', $encodedId) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Results</label>
                                    <textarea name="results" class="form-control" rows="5"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Findings</label>
                                    <textarea name="findings" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Interpretation</label>
                                    <textarea name="interpretation" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Recommendations</label>
                                    <textarea name="recommendations" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Result File (PDF/DOC)</label>
                                    <input type="file" name="result_file" class="form-control" accept=".pdf,.doc,.docx">
                                </div>
                                <button type="submit" class="btn btn-success">Submit Results</button>
                            </form>
                        @endif

                        @if($labTest->status == 'results_submitted')
                            <hr>
                            <form action="{{ route('lab-tests.send-to-doctor', $encodedId) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary">Send Results to Doctor</button>
                            </form>
                        @endif

                        @if($labTest->status == 'results_sent_to_doctor' && $labTest->result)
                            <hr>
                            <h6>Test Results</h6>
                            <div class="alert alert-info">
                                <p><strong>Results:</strong> {{ $labTest->result->results }}</p>
                                @if($labTest->result->findings)
                                    <p><strong>Findings:</strong> {{ $labTest->result->findings }}</p>
                                @endif
                                @if($labTest->result->interpretation)
                                    <p><strong>Interpretation:</strong> {{ $labTest->result->interpretation }}</p>
                                @endif
                                @if($labTest->result->recommendations)
                                    <p><strong>Recommendations:</strong> {{ $labTest->result->recommendations }}</p>
                                @endif
                            </div>
                            <a href="{{ route('lab-tests.view-results', $encodedId) }}" class="btn btn-primary">View Full Results</a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                @if($labTest->bill)
                    <div class="card radius-10 mb-3">
                        <div class="card-body">
                            <h6 class="mb-3">Bill Information</h6>
                            <p><strong>Bill Number:</strong> {{ $labTest->bill->bill_number }}</p>
                            <p><strong>Amount:</strong> {{ number_format($labTest->bill->amount, 2) }}</p>
                            <p><strong>Paid:</strong> {{ number_format($labTest->bill->paid_amount, 2) }}</p>
                            <p><strong>Status:</strong> 
                                <span class="badge bg-{{ $labTest->bill->payment_status == 'paid' ? 'success' : 'warning' }}">
                                    {{ ucfirst($labTest->bill->payment_status) }}
                                </span>
                            </p>
                            @if($labTest->bill->payment_status == 'pending')
                                <a href="{{ route('lab-test-bills.show', \App\Helpers\HashidsHelper::encode($labTest->bill->id)) }}" 
                                   class="btn btn-sm btn-primary">Process Payment</a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
