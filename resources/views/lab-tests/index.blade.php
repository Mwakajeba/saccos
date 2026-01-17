@extends('layouts.main')

@section('title', 'Lab Tests')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Lab Tests', 'url' => '#', 'icon' => 'bx bx-test-tube']
        ]" />
        <h6 class="mb-0 text-uppercase">LAB TESTS</h6>
        <hr />

        <div class="row mb-3">
            <div class="col-12">
                <div class="btn-group" role="group">
                    <a href="{{ route('lab-tests.index', ['status' => 'pending_review']) }}" 
                       class="btn btn-{{ $status == 'pending_review' ? 'primary' : 'outline-primary' }}">
                        Pending Review
                    </a>
                    <a href="{{ route('lab-tests.index', ['status' => 'pending_payment']) }}" 
                       class="btn btn-{{ $status == 'pending_payment' ? 'primary' : 'outline-primary' }}">
                        Pending Payment
                    </a>
                    <a href="{{ route('lab-tests.index', ['status' => 'paid']) }}" 
                       class="btn btn-{{ $status == 'paid' ? 'primary' : 'outline-primary' }}">
                        Paid
                    </a>
                    <a href="{{ route('lab-tests.index', ['status' => 'test_taken']) }}" 
                       class="btn btn-{{ $status == 'test_taken' ? 'primary' : 'outline-primary' }}">
                        Test Taken
                    </a>
                    <a href="{{ route('lab-tests.index', ['status' => 'results_submitted']) }}" 
                       class="btn btn-{{ $status == 'results_submitted' ? 'primary' : 'outline-primary' }}">
                        Results Submitted
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="labTestsTable">
                                <thead>
                                    <tr>
                                        <th>Test Number</th>
                                        <th>Test Name</th>
                                        <th>Patient</th>
                                        <th>Doctor</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#labTestsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("lab-tests.index") }}',
                data: { status: '{{ $status }}' }
            },
            columns: [
                { data: 'test_number', name: 'test_number' },
                { data: 'test_name', name: 'test_name' },
                { data: 'customer_name', name: 'customer.name' },
                { data: 'doctor_name', name: 'doctor.name' },
                { data: 'status', name: 'status' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ]
        });
    });
</script>
@endpush
