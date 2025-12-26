@extends('layouts.main')

@section('title', 'Share Withdrawals')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Shares Management', 'url' => route('shares.management'), 'icon' => 'bx bx-bar-chart-square'],
            ['label' => 'Share Withdrawals', 'url' => '#', 'icon' => 'bx bx-up-arrow-circle']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">SHARE WITHDRAWALS</h6>
            <a href="{{ route('shares.withdrawals.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Add Share Withdrawal
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
                    <table class="table table-bordered table-striped nowrap" id="shareWithdrawalsTable">
                        <thead>
                            <tr>
                                <th>SN</th>
                                <th>Member Name</th>
                                <th>Member Number</th>
                                <th>Share Product</th>
                                <th>Withdrawal Date</th>
                                <th>Number of Shares</th>
                                <th>Withdrawal Amount</th>
                                <th>Withdrawal Fee</th>
                                <th>Net Amount</th>
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
        var table = $('#shareWithdrawalsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("shares.withdrawals.data") }}',
                type: 'GET',
                error: function(xhr, error, code) {
                    console.error('DataTables Ajax Error:', error, code);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to load share withdrawals data. Please refresh the page.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', title: 'SN', orderable: false, searchable: false, className: 'text-center' },
                { data: 'customer_name', name: 'customer_name', title: 'Member Name' },
                { data: 'customer_number', name: 'customer_number', title: 'Member Number' },
                { data: 'share_product_name', name: 'share_product_name', title: 'Share Product' },
                { data: 'withdrawal_date_formatted', name: 'withdrawal_date', title: 'Withdrawal Date' },
                { data: 'number_of_shares_formatted', name: 'number_of_shares', title: 'Number of Shares' },
                { data: 'withdrawal_amount_formatted', name: 'withdrawal_amount', title: 'Withdrawal Amount' },
                { data: 'withdrawal_fee_formatted', name: 'withdrawal_fee', title: 'Withdrawal Fee' },
                { data: 'total_amount_formatted', name: 'total_amount', title: 'Net Amount' },
                { data: 'bank_account_name', name: 'bank_account_name', title: 'Bank Account' },
                { data: 'status_badge', name: 'status', title: 'Status' },
                { data: 'actions', name: 'actions', title: 'Actions', orderable: false, searchable: false }
            ],
            responsive: true,
            order: [[4, 'desc']], // Order by Withdrawal Date descending
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            language: {
                search: "",
                searchPlaceholder: "Search share withdrawals...",
                processing: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>',
                emptyTable: "No share withdrawals found",
                info: "Showing _START_ to _END_ of _TOTAL_ share withdrawals",
                infoEmpty: "Showing 0 to 0 of 0 share withdrawals",
                infoFiltered: "(filtered from _MAX_ total share withdrawals)",
                lengthMenu: "Show _MENU_ share withdrawals per page",
                zeroRecords: "No matching share withdrawals found"
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
        $(document).on('click', '#shareWithdrawalsTable .delete-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var withdrawalId = $(this).data('id');
            var withdrawalName = $(this).data('name');

            Swal.fire({
                title: 'Are you sure?',
                text: `You want to delete ${withdrawalName}? This action cannot be undone!`,
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
                        url: '{{ route("shares.withdrawals.destroy", ":id") }}'.replace(':id', withdrawalId),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        beforeSend: function() {
                            Swal.fire({
                                title: 'Deleting...',
                                text: 'Please wait while we delete the share withdrawal.',
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
                                text: response.message || 'Share withdrawal has been deleted successfully.',
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
                            
                            let errorMessage = 'Failed to delete share withdrawal.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else if (xhr.status === 404) {
                                errorMessage = 'Share withdrawal not found.';
                            } else if (xhr.status === 403) {
                                errorMessage = 'You do not have permission to delete this withdrawal.';
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
