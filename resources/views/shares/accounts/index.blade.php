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
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-info" id="exportBtn">
                    <i class="bx bx-export me-1"></i> Export
                </button>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bx bx-import me-1"></i> Import Share Accounts
                </button>
                <a href="{{ route('shares.accounts.create') }}" class="btn btn-primary">
                    <i class="bx bx-plus me-1"></i> Add Share Account
                </a>
            </div>
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

                @if(session('import_errors') && count(session('import_errors')) > 0)
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <strong>Import Errors:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach(session('import_errors') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Filters Section -->
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="filter_share_product" class="form-label">Share Product</label>
                                <select id="filter_share_product" class="form-select">
                                    <option value="">All Products</option>
                                    @foreach($shareProducts ?? [] as $product)
                                        <option value="{{ $product->id }}">{{ $product->share_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filter_status" class="form-label">Status</label>
                                <select id="filter_status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="closed">Closed</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filter_opening_date_from" class="form-label">Opening Date From</label>
                                <input type="date" id="filter_opening_date_from" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label for="filter_opening_date_to" class="form-label">Opening Date To</label>
                                <input type="date" id="filter_opening_date_to" class="form-control">
                            </div>
                            <div class="col-md-12">
                                <button type="button" id="applyFilters" class="btn btn-primary me-2">
                                    <i class="bx bx-filter me-1"></i> Apply Filters
                                </button>
                                <button type="button" id="clearFilters" class="btn btn-secondary">
                                    <i class="bx bx-x me-1"></i> Clear Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped nowrap" id="shareAccountsTable">
                        <thead>
                            <tr>
                                <th>SN</th>
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
                data: function(d) {
                    d.share_product_id = $('#filter_share_product').val();
                    d.status = $('#filter_status').val();
                    d.opening_date_from = $('#filter_opening_date_from').val();
                    d.opening_date_to = $('#filter_opening_date_to').val();
                },
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
                { data: 'DT_RowIndex', name: 'DT_RowIndex', title: 'SN', orderable: false, searchable: false },
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
            order: [[1, 'asc']], // Order by Account Number (column index 1, since SN is index 0)
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
                // Reinitialize tooltips after each draw
                $('[data-bs-toggle="tooltip"]').tooltip();
            }
        });

        // Handle filter application
        $('#applyFilters').on('click', function() {
            table.ajax.reload();
        });

        // Handle filter clearing
        $('#clearFilters').on('click', function() {
            $('#filter_share_product').val('');
            $('#filter_status').val('');
            $('#filter_opening_date_from').val('');
            $('#filter_opening_date_to').val('');
            table.ajax.reload();
        });

        // Handle export button
        $('#exportBtn').on('click', function() {
            const shareProductId = $('#filter_share_product').val();
            const status = $('#filter_status').val();
            const openingDateFrom = $('#filter_opening_date_from').val();
            const openingDateTo = $('#filter_opening_date_to').val();
            
            // Build export URL with current filters
            let url = '{{ route("shares.accounts.export") }}?';
            const params = [];
            if (shareProductId) params.push('share_product_id=' + encodeURIComponent(shareProductId));
            if (status) params.push('status=' + encodeURIComponent(status));
            if (openingDateFrom) params.push('opening_date_from=' + encodeURIComponent(openingDateFrom));
            if (openingDateTo) params.push('opening_date_to=' + encodeURIComponent(openingDateTo));
            
            url += params.join('&');
            window.open(url, '_blank');
        });

        // Handle change status button clicks
        $('#shareAccountsTable').on('click', '.change-status-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const accountId = $(this).data('id');
            const accountNumber = $(this).data('name');
            const currentStatus = $(this).data('status');
            
            // Status options
            const statusOptions = {
                'active': { label: 'Active', color: '#28a745' },
                'inactive': { label: 'Inactive', color: '#ffc107' },
                'closed': { label: 'Closed', color: '#dc3545' }
            };
            
            // Build options HTML
            let optionsHtml = '';
            Object.keys(statusOptions).forEach(status => {
                const selected = status === currentStatus ? 'selected' : '';
                optionsHtml += `<option value="${status}" ${selected}>${statusOptions[status].label}</option>`;
            });
            
            Swal.fire({
                title: 'Change Account Status',
                html: `
                    <p>Account: <strong>${accountNumber}</strong></p>
                    <p>Current Status: <span class="badge" style="background-color: ${statusOptions[currentStatus].color}">${statusOptions[currentStatus].label}</span></p>
                    <label for="newStatus" class="form-label mt-3">Select New Status:</label>
                    <select id="newStatus" class="form-select">
                        ${optionsHtml}
                    </select>
                `,
                showCancelButton: true,
                confirmButtonText: 'Update Status',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#17a2b8',
                didOpen: () => {
                    $('#newStatus').select2({
                        dropdownParent: Swal.getContainer(),
                        width: '100%'
                    });
                },
                preConfirm: () => {
                    return $('#newStatus').val();
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    const newStatus = result.value;
                    
                    if (newStatus === currentStatus) {
                        Swal.fire({
                            title: 'No Change',
                            text: 'The selected status is the same as the current status.',
                            icon: 'info',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }
                    
                    // Show loading
                    Swal.fire({
                        title: 'Updating...',
                        text: 'Please wait while we update the account status.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Make AJAX request
                    $.ajax({
                        url: '{{ route("shares.accounts.change-status", ":id") }}'.replace(':id', accountId),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'PATCH',
                            status: newStatus
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Success!',
                                text: 'Account status updated successfully.',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                table.ajax.reload(null, false);
                            });
                        },
                        error: function(xhr) {
                            let errorMessage = 'Failed to update account status.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
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

        // Handle delete button clicks
        $('#shareAccountsTable').on('click', '.delete-btn', function(e) {
            e.preventDefault();
            
            var accountId = $(this).data('id');
            var accountNumber = $(this).data('name');
            var deleteBtn = $(this);
            
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
                    // Show loading
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait while we delete the share account.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Make AJAX delete request
                    $.ajax({
                        url: '{{ route("shares.accounts.destroy", ":id") }}'.replace(':id', accountId),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: 'Share account has been deleted successfully.',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                // Reload DataTable
                                table.ajax.reload(null, false);
                            });
                        },
                        error: function(xhr) {
                            var errorMessage = 'Failed to delete share account.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
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

        // Refresh table data function
        window.refreshShareAccountsTable = function() {
            table.ajax.reload(null, false);
        };

        // Handle template download - use event delegation for modal button
        $(document).on('click', '#downloadTemplateBtn', function(e) {
            e.preventDefault();
            
            const shareProductId = $('#import_share_product_id').val();
            const openingDate = $('#import_opening_date').val();

            if (!shareProductId) {
                Swal.fire({
                    title: 'Validation Error',
                    text: 'Please select a share product first',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return false;
            }

            if (!openingDate) {
                Swal.fire({
                    title: 'Validation Error',
                    text: 'Please select an opening date',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return false;
            }

            // Build URL with query parameters
            const url = '{{ route("shares.accounts.download-template") }}' + 
                       '?share_product_id=' + encodeURIComponent(shareProductId) + 
                       '&opening_date=' + encodeURIComponent(openingDate);
            
            // Open in new window to trigger download
            window.location.href = url;
        });
    });
</script>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">
                    <i class="bx bx-import me-2"></i>Import Share Accounts
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('shares.accounts.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Instructions:</strong>
                        <ol class="mb-0 mt-2">
                            <li>Select Share Product and Opening Date</li>
                            <li>Click "Download Template" to get Excel file with customers who don't have accounts</li>
                            <li>Fill in the Excel file with customer data</li>
                            <li>Upload the filled Excel file</li>
                        </ol>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Share Product <span class="text-danger">*</span></label>
                        <select name="share_product_id" id="import_share_product_id" class="form-select @error('share_product_id') is-invalid @enderror" required>
                            <option value="">Select share product</option>
                            @php
                                $shareProducts = \App\Models\ShareProduct::where('is_active', true)->orderBy('share_name')->get();
                            @endphp
                            @foreach($shareProducts as $product)
                                <option value="{{ $product->id }}" {{ old('share_product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->share_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('share_product_id') 
                            <div class="invalid-feedback">{{ $message }}</div> 
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Opening Date <span class="text-danger">*</span></label>
                        <input type="date" name="opening_date" id="import_opening_date" 
                               class="form-control @error('opening_date') is-invalid @enderror"
                               value="{{ old('opening_date', date('Y-m-d')) }}" required>
                        @error('opening_date') 
                            <div class="invalid-feedback">{{ $message }}</div> 
                        @enderror
                    </div>

                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-primary" id="downloadTemplateBtn">
                            <i class="bx bx-download me-1"></i> Download Template
                        </button>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Upload Excel File <span class="text-danger">*</span></label>
                        <input type="file" name="import_file" id="import_file" 
                               class="form-control @error('import_file') is-invalid @enderror"
                               accept=".xlsx,.xls" required>
                        @error('import_file') 
                            <div class="invalid-feedback">{{ $message }}</div> 
                        @enderror
                        <small class="text-muted">Only .xlsx and .xls files are allowed</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-upload me-1"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush
