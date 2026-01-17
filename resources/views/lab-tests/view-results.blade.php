@extends('layouts.main')

@section('title', 'View Lab Test Results')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Lab Tests', 'url' => route('lab-tests.index'), 'icon' => 'bx bx-test-tube'],
            ['label' => 'View Results', 'url' => '#', 'icon' => 'bx bx-show']
        ]" />
        <h6 class="mb-0 text-uppercase">LAB TEST RESULTS</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card radius-10">
                    <div class="card-body">
                        <h6 class="mb-3">Test Information</h6>
                        <table class="table table-borderless mb-4">
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
                        </table>

                        <hr>
                        <h6 class="mb-3">Test Results</h6>
                        
                        @if($labTest->result)
                            <div class="mb-3">
                                <strong>Results:</strong>
                                <p class="mt-2">{{ $labTest->result->results ?? 'N/A' }}</p>
                            </div>

                            @if($labTest->result->findings)
                            <div class="mb-3">
                                <strong>Findings:</strong>
                                <p class="mt-2">{{ $labTest->result->findings }}</p>
                            </div>
                            @endif

                            @if($labTest->result->interpretation)
                            <div class="mb-3">
                                <strong>Interpretation:</strong>
                                <p class="mt-2">{{ $labTest->result->interpretation }}</p>
                            </div>
                            @endif

                            @if($labTest->result->recommendations)
                            <div class="mb-3">
                                <strong>Recommendations:</strong>
                                <p class="mt-2">{{ $labTest->result->recommendations }}</p>
                            </div>
                            @endif

                            @if($labTest->result->result_file)
                            <div class="mb-3">
                                <strong>Result File:</strong>
                                <br>
                                <a href="{{ route('lab-test-results.download', \App\Helpers\HashidsHelper::encode($labTest->result->id)) }}" 
                                   class="btn btn-sm btn-primary mt-2">
                                    <i class="bx bx-download"></i> Download File
                                </a>
                            </div>
                            @endif
                        @else
                            <div class="alert alert-warning">
                                No results available yet.
                            </div>
                        @endif

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('lab-tests.show', $encodedId) }}" class="btn btn-secondary">Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
