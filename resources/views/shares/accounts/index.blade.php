@extends('layouts.main')

@section('title', 'Share Accounts')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Shares Management', 'url' => route('shares.management'), 'icon' => 'bx bx-bar-chart-square'],
            ['label' => 'Share Accounts', 'url' => '#', 'icon' => 'bx bx-wallet']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">SHARE ACCOUNTS</h6>
            <a href="{{ route('shares.accounts.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Add Share Account
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
                    <table class="table table-bordered table-striped nowrap" id="shareAccountsTable">
                        <thead>
                            <tr>
                                <th>Account Number</th>
                                <th>Member Name</th>
                                <th>Member Number</th>
                                <th>Share Product</th>
                                <th>Share Balance</th>
                                <th>Nominal Value</th>
                                <th>Opening Date</th>
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
        var table = $('#shareAccountsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("shares.accounts.data") }}',
                type: 'GET',
                error: function(xhr, error, code) {
                    console.error('DataTables Ajax Error:', error, code);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to load share accounts data. Please refresh the page.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            columns: [
                { data: 'account_number', name: 'account_number', title: 'Account Number' },
                { data: 'customer_name', name: 'customer.name', title: 'Member Name' },
                { data: 'customer_number', name: 'customer.customerNo', title: 'Member Number' },
                { data: 'share_product_name', name: 'shareProduct.share_name', title: 'Share Product' },
                { data: 'share_balance_formatted', name: 'share_balance', title: 'Share Balance' },
                { data: 'nominal_value_formatted', name: 'nominal_value', title: 'Nominal Value' },
                { data: 'opening_date_formatted', name: 'opening_date', title: 'Opening Date' },
                { data: 'status_badge', name: 'status', title: 'Status' },
                { data: 'actions', name: 'actions', title: 'Actions', orderable: false, searchable: false }
            ],
            responsive: true,
            order: [[0, 'asc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            language: {
                search: "",
                searchPlaceholder: "Search share accounts...",
                processing: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>',
                emptyTable: "No share accounts found",
                info: "Showing _START_ to _END_ of _TOTAL_ share accounts",
                infoEmpty: "Showing 0 to 0 of 0 share accounts",
                infoFiltered: "(filtered from _MAX_ total share accounts)",
                lengthMenu: "Show _MENU_ share accounts per page",
                zeroRecords: "No matching share accounts found"
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

        // Handle delete button clicks
        $('#shareAccountsTable').on('click', '.delete-btn', function(e) {
            e.preventDefault();
            
            var accountId = $(this).data('id');
            var accountNumber = $(this).data('name');
            
            Swal.fire({
                title: 'Are you sure?',
                text: `You want to delete share account "${accountNumber}"? This action cannot be undone!`,
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
                        'action': '{{ route("shares.accounts.destroy", ":id") }}'.replace(':id', accountId)
                    });
                    
                    var csrfToken = $('<input>', {
                        'type': 'hidden',
                        'name': '_token',
                        'value': '{{ csrf_token() }}'
                    });
                    
                    var methodField = $('<input>', {
                        'type': 'hidden',
                        'name': '_method',
                        'value': 'DELETE'
                    });
                    
                    form.append(csrfToken, methodField);
                    $('body').append(form);
                    
                    // Show loading
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait while we delete the share account.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    form.submit();
                }
            });
        });

        // Refresh table data function
        window.refreshShareAccountsTable = function() {
            table.ajax.reload(null, false);
        };
    });
</script>
@endpush
