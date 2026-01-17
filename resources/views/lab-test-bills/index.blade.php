@extends('layouts.main')

@section('title', 'Lab Test Bills')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Lab Test Bills', 'url' => '#', 'icon' => 'bx bx-money']
        ]" />
        <h6 class="mb-0 text-uppercase">LAB TEST BILLS</h6>
        <hr />

        <div class="row mb-3">
            <div class="col-12">
                <div class="btn-group" role="group">
                    <a href="{{ route('lab-test-bills.index', ['payment_status' => 'pending']) }}" 
                       class="btn btn-{{ $status == 'pending' ? 'primary' : 'outline-primary' }}">
                        Pending
                    </a>
                    <a href="{{ route('lab-test-bills.index', ['payment_status' => 'paid']) }}" 
                       class="btn btn-{{ $status == 'paid' ? 'primary' : 'outline-primary' }}">
                        Paid
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="labTestBillsTable">
                                <thead>
                                    <tr>
                                        <th>Bill Number</th>
                                        <th>Test Number</th>
                                        <th>Patient</th>
                                        <th>Amount</th>
                                        <th>Paid</th>
                                        <th>Balance</th>
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
        $('#labTestBillsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("lab-test-bills.index") }}',
                data: { payment_status: '{{ $status }}' }
            },
            columns: [
                { data: 'bill_number', name: 'bill_number' },
                { data: 'test_number', name: 'labTest.test_number' },
                { data: 'customer_name', name: 'customer.name' },
                { data: 'amount', name: 'amount' },
                { data: 'paid_amount', name: 'paid_amount' },
                { data: 'balance', name: 'balance' },
                { data: 'payment_status', name: 'payment_status' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ]
        });
    });
</script>
@endpush
