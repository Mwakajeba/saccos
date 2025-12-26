@extends('layouts.main')

@section('title', 'Share Deposits')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Shares Management', 'url' => route('shares.management'), 'icon' => 'bx bx-bar-chart-square'],
            ['label' => 'Share Deposits', 'url' => '#', 'icon' => 'bx bx-right-arrow-alt']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">SHARE DEPOSITS</h6>
            <a href="{{ route('shares.deposits.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Add Share Deposit
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

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered table-striped nowrap" id="shareDepositsTable">
                        <thead>
                            <tr>
                                <th>SN</th>
                                <th>Account Number</th>
                                <th>Member Name</th>
                                <th>Member Number</th>
                                <th>Share Product</th>
                                <th>Deposit Date</th>
                                <th>Deposit Amount</th>
                                <th>Number of Shares</th>
                                <th>Charge Amount</th>
                                <th>Total Amount</th>
                                <th>Payment Method</th>
                                <th>Status</th>
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

@push('styles')
<style>
    .dataTables_processing {
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 20px;
        font-size: 16px;
        z-index: 9999;
    }
    .dataTables_length label,
    .dataTables_filter label {
        font-weight: 500;
        margin-bottom: 0;
    }
    .dataTables_filter input {
        border-radius: 6px;
        border: 1px solid #ddd;
        padding: 8px 12px;
        margin-left: 8px;
    }
    .table-responsive .table {
        margin-bottom: 0;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        var table = $('#shareDepositsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("shares.deposits.data") }}',
                type: 'GET',
                error: function(xhr, error, code) {
                    console.error('DataTables Ajax Error:', error, code);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to load share deposits data. Please refresh the page.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', title: 'SN', orderable: false, searchable: false, className: 'text-center' },
                { data: 'account_number', name: 'account_number', title: 'Account Number' },
                { data: 'customer_name', name: 'customer_name', title: 'Member Name' },
                { data: 'customer_number', name: 'customer_number', title: 'Member Number' },
                { data: 'share_product_name', name: 'share_product_name', title: 'Share Product' },
                { data: 'deposit_date_formatted', name: 'deposit_date', title: 'Deposit Date' },
                { data: 'deposit_amount_formatted', name: 'deposit_amount', title: 'Deposit Amount' },
                { data: 'number_of_shares_formatted', name: 'number_of_shares', title: 'Number of Shares' },
                { data: 'charge_amount_formatted', name: 'charge_amount', title: 'Charge Amount' },
                { data: 'total_amount_formatted', name: 'total_amount', title: 'Total Amount' },
                { data: 'payment_method', name: 'payment_method', title: 'Payment Method' },
                { data: 'status_badge', name: 'status', title: 'Status' },
                { data: 'actions', name: 'actions', title: 'Actions', orderable: false, searchable: false }
            ],
            responsive: true,
            order: [[5, 'desc']], // Order by Deposit Date descending
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            language: {
                search: "",
                searchPlaceholder: "Search share deposits...",
                processing: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>',
                emptyTable: "No share deposits found",
                info: "Showing _START_ to _END_ of _TOTAL_ share deposits",
                infoEmpty: "Showing 0 to 0 of 0 share deposits",
                infoFiltered: "(filtered from _MAX_ total share deposits)",
                lengthMenu: "Show _MENU_ share deposits per page",
                zeroRecords: "No matching share deposits found"
            },
            columnDefs: [
                {
                    targets: 0, // SN column
                    className: 'text-center'
                },
                {
                    targets: -1, // Actions column
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }
            ],
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
            drawCallback: function(settings) {
                $('[data-bs-toggle="tooltip"]').tooltip();
            }
        });

        // Handle delete button clicks
        $('#shareDepositsTable').on('click', '.delete-btn', function(e) {
            e.preventDefault();
            var depositId = $(this).data('id');
            var depositName = $(this).data('name');

            Swal.fire({
                title: 'Are you sure?',
                text: `You want to delete ${depositName}? This action cannot be undone!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("shares.deposits.destroy", ":id") }}'.replace(':id', depositId),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        beforeSend: function() {
                            Swal.fire({
                                title: 'Deleting...',
                                text: 'Please wait while we delete the share deposit.',
                                allowOutsideClick: false,
                                didOpen: () => { Swal.showLoading(); }
                            });
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            });
                            table.ajax.reload(null, false); // Reload DataTable
                        },
                        error: function(xhr) {
                            console.error('Delete Error:', xhr.responseText);
                            Swal.fire({
                                title: 'Error!',
                                text: 'Failed to delete share deposit. ' + (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : ''),
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
