@extends('layouts.main')

@section('title', 'UTT Transactions')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Investment', 'url' => '#', 'icon' => 'bx bx-trending-up'],
            ['label' => 'Transactions', 'url' => '#', 'icon' => 'bx bx-transfer']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">UTT INVESTMENT TRANSACTIONS</h6>
            <a href="{{ route('investments.transactions.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> New Transaction
            </a>
        </div>
        <hr />

        <div class="card">
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered table-striped nowrap" id="transactionsTable">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Fund</th>
                                <th>Type</th>
                                <th>Trade Date</th>
                                <th>Units</th>
                                <th>NAV</th>
                                <th>Cash Value</th>
                                <th>Status</th>
                                <th>Maker</th>
                                <th>Checker</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded via Ajax -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        var table = $('#transactionsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("investments.transactions.data") }}',
                type: 'GET',
                error: function(xhr, error, code) {
                    console.error('DataTables Ajax Error:', error, code);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to load transactions data. Please refresh the page.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            columns: [
                { data: 'reference_number', name: 'reference_number', title: 'Reference' },
                { data: 'fund_name', name: 'fund_name', title: 'Fund' },
                { data: 'type_badge', name: 'transaction_type', title: 'Type' },
                { data: 'trade_date', name: 'trade_date', title: 'Trade Date' },
                { data: 'units_formatted', name: 'units', title: 'Units' },
                { data: 'nav_formatted', name: 'nav_per_unit', title: 'NAV' },
                { data: 'cash_value_formatted', name: 'total_cash_value', title: 'Cash Value' },
                { data: 'status_badge', name: 'status', title: 'Status' },
                { data: 'maker_name', name: 'maker_name', title: 'Maker' },
                { data: 'checker_name', name: 'checker_name', title: 'Checker' },
                { data: 'actions', name: 'actions', title: 'Actions', orderable: false, searchable: false }
            ],
            responsive: true,
            order: [[3, 'desc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            language: {
                search: "",
                searchPlaceholder: "Search transactions...",
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
            }
        });

        // Approve transaction
        $(document).on('click', '.approve-btn', function() {
            var transactionId = $(this).data('id');
            Swal.fire({
                title: 'Approve Transaction?',
                text: 'Are you sure you want to approve this transaction?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Approve',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("investments.transactions.approve", ":id") }}'.replace(':id', transactionId),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire('Success!', response.message, 'success').then(() => {
                                table.ajax.reload(null, false);
                            });
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', xhr.responseJSON?.error || 'Failed to approve transaction', 'error');
                        }
                    });
                }
            });
        });

        // Settle transaction
        $(document).on('click', '.settle-btn', function() {
            var transactionId = $(this).data('id');
            Swal.fire({
                title: 'Settle Transaction?',
                text: 'This will update the holdings. Are you sure?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#007bff',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Settle',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("investments.transactions.settle", ":id") }}'.replace(':id', transactionId),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire('Success!', response.message, 'success').then(() => {
                                table.ajax.reload(null, false);
                            });
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', xhr.responseJSON?.error || 'Failed to settle transaction', 'error');
                        }
                    });
                }
            });
        });

        // Cancel transaction
        $(document).on('click', '.cancel-btn', function() {
            var transactionId = $(this).data('id');
            Swal.fire({
                title: 'Cancel Transaction?',
                input: 'textarea',
                inputLabel: 'Reason for cancellation',
                inputPlaceholder: 'Enter cancellation reason...',
                inputAttributes: {
                    'aria-label': 'Enter cancellation reason'
                },
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Cancel',
                cancelButtonText: 'Cancel',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Please provide a cancellation reason';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("investments.transactions.cancel", ":id") }}'.replace(':id', transactionId),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            rejection_reason: result.value
                        },
                        success: function(response) {
                            Swal.fire('Success!', response.message, 'success').then(() => {
                                table.ajax.reload(null, false);
                            });
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', xhr.responseJSON?.error || 'Failed to cancel transaction', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush

