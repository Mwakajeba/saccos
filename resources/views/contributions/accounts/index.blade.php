@extends('layouts.main')

@section('title', 'Contribution Accounts')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Contributions', 'url' => route('contributions.index'), 'icon' => 'bx bx-donate-heart'],
            ['label' => 'Contribution Accounts', 'url' => '#', 'icon' => 'bx bx-wallet']
        ]" />
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">CONTRIBUTION ACCOUNTS</h6>
            <a href="{{ route('contributions.accounts.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Add New Account
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
                    <table class="table table-bordered table-striped nowrap" id="contributionAccountsTable">
                        <thead>
                            <tr>
                                <th>Account Number</th>
                                <th>Customer Name</th>
                                <th>Customer Number</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th>Opening Date</th>
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
    /* Custom DataTables styling */
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
        // Initialize DataTable with Ajax
        var table = $('#contributionAccountsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("contributions.accounts.data") }}',
                type: 'GET',
                error: function(xhr, error, code) {
                    console.error('DataTables Ajax Error:', error, code);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to load contribution accounts data. Please refresh the page.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            columns: [
                { data: 'account_number', name: 'account_number', title: 'Account Number' },
                { data: 'customer_name', name: 'customer.name', title: 'Customer Name' },
                { data: 'customer_number', name: 'customer.customerNo', title: 'Customer Number' },
                { data: 'product_name', name: 'contributionProduct.product_name', title: 'Product Name' },
                { data: 'product_category', name: 'contributionProduct.category', title: 'Category' },
                { data: 'balance_formatted', name: 'balance', title: 'Balance' },
                { data: 'status_badge', name: 'status', title: 'Status' },
                { data: 'opening_date_formatted', name: 'opening_date', title: 'Opening Date' },
                { data: 'actions', name: 'actions', title: 'Actions', orderable: false, searchable: false }
            ],
            responsive: true,
            order: [[0, 'asc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            language: {
                search: "",
                searchPlaceholder: "Search contribution accounts...",
                processing: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>',
                emptyTable: "No contribution accounts found",
                info: "Showing _START_ to _END_ of _TOTAL_ contribution accounts",
                infoEmpty: "Showing 0 to 0 of 0 contribution accounts",
                infoFiltered: "(filtered from _MAX_ total contribution accounts)",
                lengthMenu: "Show _MENU_ contribution accounts per page",
                zeroRecords: "No matching contribution accounts found"
            },
            columnDefs: [
                {
                    targets: -1, // Actions column
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }
            ],
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
            drawCallback: function(settings) {
                // Reinitialize tooltips after each draw
                $('[data-bs-toggle="tooltip"]').tooltip();
            }
        });


        // Refresh table data function
        window.refreshContributionAccountsTable = function() {
            table.ajax.reload(null, false);
        };

        // Delete button handler
        $(document).on('click', '.delete-btn', function(e) {
            e.preventDefault();
            var accountId = $(this).data('id');
            var accountName = $(this).data('name');

            Swal.fire({
                title: 'Are you sure?',
                text: "You want to delete contribution account " + accountName + "? This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create a form to submit the delete request
                    var form = $('<form>', {
                        'method': 'POST',
                        'action': '{{ route("contributions.accounts.destroy", ":id") }}'.replace(':id', accountId)
                    });

                    // Add CSRF token
                    form.append($('<input>', {
                        'type': 'hidden',
                        'name': '_token',
                        'value': '{{ csrf_token() }}'
                    }));

                    // Add method spoofing for DELETE
                    form.append($('<input>', {
                        'type': 'hidden',
                        'name': '_method',
                        'value': 'DELETE'
                    }));

                    // Submit form via AJAX
                    $.ajax({
                        url: form.attr('action'),
                        method: 'POST',
                        data: form.serialize(),
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    table.ajax.reload(null, false);
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: response.error || 'Failed to delete account',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        error: function(xhr) {
                            var errorMessage = 'Failed to delete account';
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                errorMessage = xhr.responseJSON.error;
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

        // Block button handler
        $(document).on('click', '.block-btn', function(e) {
            e.preventDefault();
            var accountId = $(this).data('id');
            var accountName = $(this).data('name');

            Swal.fire({
                title: 'Block Account?',
                text: "Are you sure you want to block account " + accountName + "? This will prevent withdrawals and transfers, but deposits will still be allowed.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f0ad4e',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, block it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("contributions.accounts.toggle-status", ":id") }}'.replace(':id', accountId),
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    title: 'Blocked!',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    table.ajax.reload(null, false);
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: response.error || 'Failed to block account',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        error: function(xhr) {
                            var errorMessage = 'Failed to block account';
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                errorMessage = xhr.responseJSON.error;
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

        // Unblock button handler
        $(document).on('click', '.unblock-btn', function(e) {
            e.preventDefault();
            var accountId = $(this).data('id');
            var accountName = $(this).data('name');

            Swal.fire({
                title: 'Unblock Account?',
                text: "Are you sure you want to unblock account " + accountName + "? This will allow withdrawals and transfers again.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#5cb85c',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, unblock it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("contributions.accounts.toggle-status", ":id") }}'.replace(':id', accountId),
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    title: 'Unblocked!',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    table.ajax.reload(null, false);
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: response.error || 'Failed to unblock account',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        error: function(xhr) {
                            var errorMessage = 'Failed to unblock account';
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                errorMessage = xhr.responseJSON.error;
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

