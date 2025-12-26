@extends('layouts.main')

@section('title', 'Share Transfers')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Shares Management', 'url' => route('shares.management'), 'icon' => 'bx bx-bar-chart-square'],
            ['label' => 'Share Transfers', 'url' => '#', 'icon' => 'bx bx-transfer']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">SHARE TRANSFERS</h6>
            <a href="{{ route('shares.transfers.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Add Share Transfer
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
                    <table class="table table-bordered table-striped nowrap" id="shareTransfersTable">
                        <thead>
                            <tr>
                                <th>SN</th>
                                <th>From Member</th>
                                <th>From Member No</th>
                                <th>To Member</th>
                                <th>To Member No</th>
                                <th>Share Product</th>
                                <th>Transfer Date</th>
                                <th>Number of Shares</th>
                                <th>Transfer Amount</th>
                                <th>Transfer Fee</th>
                                <th>Bank Account</th>
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
        var table = $('#shareTransfersTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("shares.transfers.data") }}',
                type: 'GET',
                error: function(xhr, error, code) {
                    console.error('DataTables Ajax Error:', error, code);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to load share transfers data. Please refresh the page.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', title: 'SN', orderable: false, searchable: false, className: 'text-center' },
                { data: 'from_customer_name', name: 'from_customer_name', title: 'From Member' },
                { data: 'from_customer_number', name: 'from_customer_number', title: 'From Member No' },
                { data: 'to_customer_name', name: 'to_customer_name', title: 'To Member' },
                { data: 'to_customer_number', name: 'to_customer_number', title: 'To Member No' },
                { data: 'share_product_name', name: 'share_product_name', title: 'Share Product' },
                { data: 'transfer_date_formatted', name: 'transfer_date', title: 'Transfer Date' },
                { data: 'number_of_shares_formatted', name: 'number_of_shares', title: 'Number of Shares' },
                { data: 'transfer_amount_formatted', name: 'transfer_amount', title: 'Transfer Amount' },
                { data: 'transfer_fee_formatted', name: 'transfer_fee', title: 'Transfer Fee' },
                { data: 'bank_account_name', name: 'bank_account_name', title: 'Bank Account' },
                { data: 'status_badge', name: 'status', title: 'Status' },
                { data: 'actions', name: 'actions', title: 'Actions', orderable: false, searchable: false }
            ],
            responsive: true,
            order: [[6, 'desc']], // Order by Transfer Date descending
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            language: {
                search: "",
                searchPlaceholder: "Search share transfers...",
                processing: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>',
                emptyTable: "No share transfers found",
                info: "Showing _START_ to _END_ of _TOTAL_ share transfers",
                infoEmpty: "Showing 0 to 0 of 0 share transfers",
                infoFiltered: "(filtered from _MAX_ total share transfers)",
                lengthMenu: "Show _MENU_ share transfers per page",
                zeroRecords: "No matching share transfers found"
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

        // Handle delete button clicks using event delegation
        $(document).on('click', '#shareTransfersTable .delete-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var transferId = $(this).data('id');
            var transferName = $(this).data('name');

            Swal.fire({
                title: 'Are you sure?',
                text: `You want to delete ${transferName}? This action cannot be undone!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("shares.transfers.destroy", ":id") }}'.replace(':id', transferId),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        beforeSend: function() {
                            Swal.fire({
                                title: 'Deleting...',
                                text: 'Please wait while we delete the share transfer.',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                showConfirmButton: false,
                                didOpen: () => { 
                                    Swal.showLoading(); 
                                }
                            });
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: response.message || 'Share transfer has been deleted successfully.',
                                icon: 'success',
                                confirmButtonText: 'OK',
                                timer: 2000,
                                timerProgressBar: true
                            }).then(() => {
                                table.ajax.reload(null, false); // Reload DataTable without resetting pagination
                            });
                        },
                        error: function(xhr) {
                            console.error('Delete Error:', xhr.responseText);
                            
                            let errorMessage = 'Failed to delete share transfer.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else if (xhr.status === 404) {
                                errorMessage = 'Share transfer not found.';
                            } else if (xhr.status === 403) {
                                errorMessage = 'You do not have permission to delete this transfer.';
                            } else if (xhr.status === 500) {
                                errorMessage = 'An internal server error occurred. Please try again later.';
                            }
                            
                            Swal.fire({
                                title: 'Error!',
                                text: errorMessage,
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
