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
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-info" id="exportBtn">
                    <i class="bx bx-export me-1"></i> Export
                </button>
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#importOpeningBalanceModal">
                    <i class="bx bx-transfer me-1"></i> Import Opening Balance
                </button>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bx bx-import me-1"></i> Import Share Deposits
                </button>
                <a href="{{ route('shares.deposits.create') }}" class="btn btn-primary">
                    <i class="bx bx-plus me-1"></i> Add Share Deposit
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
                                    @foreach($shareProducts as $product)
                                        <option value="{{ $product->id }}">{{ $product->share_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filter_status" class="form-label">Status</label>
                                <select id="filter_status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filter_date_from" class="form-label">Date From</label>
                                <input type="date" id="filter_date_from" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label for="filter_date_to" class="form-label">Date To</label>
                                <input type="date" id="filter_date_to" class="form-control">
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
        var table = $('#shareDepositsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("shares.deposits.data") }}',
                type: 'GET',
                data: function(d) {
                    d.share_product_id = $('#filter_share_product').val();
                    d.status = $('#filter_status').val();
                    d.date_from = $('#filter_date_from').val();
                    d.date_to = $('#filter_date_to').val();
                },
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
                { data: 'bank_account_name', name: 'bank_account_name', title: 'Bank Account' },
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

        // Handle filter application
        $('#applyFilters').on('click', function() {
            table.ajax.reload();
        });

        // Handle filter clearing
        $('#clearFilters').on('click', function() {
            $('#filter_share_product').val('');
            $('#filter_status').val('');
            $('#filter_date_from').val('');
            $('#filter_date_to').val('');
            table.ajax.reload();
        });

        // Handle export button
        $('#exportBtn').on('click', function() {
            const shareProductId = $('#filter_share_product').val();
            const status = $('#filter_status').val();
            const dateFrom = $('#filter_date_from').val();
            const dateTo = $('#filter_date_to').val();
            
            // Build export URL with current filters
            let url = '{{ route("shares.deposits.export") }}?';
            const params = [];
            if (shareProductId) params.push('share_product_id=' + encodeURIComponent(shareProductId));
            if (status) params.push('status=' + encodeURIComponent(status));
            if (dateFrom) params.push('date_from=' + encodeURIComponent(dateFrom));
            if (dateTo) params.push('date_to=' + encodeURIComponent(dateTo));
            
            url += params.join('&');
            window.open(url, '_blank');
        });

        // Handle change status button clicks
        $('#shareDepositsTable').on('click', '.change-status-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const depositId = $(this).data('id');
            const depositName = $(this).data('name');
            const currentStatus = $(this).data('status');
            
            // Status options
            const statusOptions = {
                'pending': { label: 'Pending', color: '#ffc107' },
                'approved': { label: 'Approved', color: '#28a745' },
                'rejected': { label: 'Rejected', color: '#dc3545' }
            };
            
            // Build options HTML
            let optionsHtml = '';
            Object.keys(statusOptions).forEach(status => {
                const selected = status === currentStatus ? 'selected' : '';
                optionsHtml += `<option value="${status}" ${selected}>${statusOptions[status].label}</option>`;
            });
            
            Swal.fire({
                title: 'Change Deposit Status',
                html: `
                    <p>Deposit: <strong>${depositName}</strong></p>
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
                        text: 'Please wait while we update the deposit status.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Make AJAX request
                    $.ajax({
                        url: '{{ route("shares.deposits.change-status", ":id") }}'.replace(':id', depositId),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'PATCH',
                            status: newStatus
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Success!',
                                text: 'Deposit status updated successfully.',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                table.ajax.reload(null, false);
                            });
                        },
                        error: function(xhr) {
                            let errorMessage = 'Failed to update deposit status.';
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

        // Handle template download - use event delegation for modal button
        $(document).on('click', '#downloadTemplateBtn', function(e) {
            e.preventDefault();
            
            const shareProductId = $('#share_product_id_import').val();
            
            if (!shareProductId) {
                Swal.fire({
                    title: 'Validation Error',
                    text: 'Please select a share product',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // Build URL for download with share product parameter
            const url = '{{ route("shares.deposits.download-template") }}?share_product_id=' + encodeURIComponent(shareProductId);
            
            // Open in new window to trigger download
            window.location.href = url;
        });

        // Handle opening balance template download - use event delegation for modal button
        $(document).on('click', '#downloadOpeningBalanceTemplateBtn', function(e) {
            e.preventDefault();
            
            const shareProductId = $('#share_product_id_opening_balance').val();
            const openingBalanceDate = $('#opening_balance_date').val();
            
            if (!shareProductId) {
                Swal.fire({
                    title: 'Validation Error',
                    text: 'Please select a share product',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            if (!openingBalanceDate) {
                Swal.fire({
                    title: 'Validation Error',
                    text: 'Please select an opening balance date',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // Build URL for download with parameters
            const url = '{{ route("shares.deposits.download-opening-balance-template") }}?share_product_id=' + encodeURIComponent(shareProductId) + '&opening_balance_date=' + encodeURIComponent(openingBalanceDate);
            
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
                    <i class="bx bx-import me-2"></i>Import Share Deposits
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('shares.deposits.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Instructions:</strong>
                        <ol class="mb-0 mt-2">
                            <li>Select a share product</li>
                            <li>Click "Download Template" to get Excel file with share accounts for the selected product</li>
                            <li>Fill in the Excel file with deposit data (deposit_date, deposit_amount, bank_account_name, etc.)</li>
                            <li>Upload the filled Excel file</li>
                        </ol>
                    </div>

                    <div class="mb-3">
                        <label for="share_product_id_import" class="form-label">Share Product <span class="text-danger">*</span></label>
                        <select name="share_product_id_import" id="share_product_id_import" 
                                class="form-select @error('share_product_id_import') is-invalid @enderror" required>
                            <option value="">Select Share Product</option>
                            @foreach($shareProducts ?? [] as $product)
                                <option value="{{ $product->id }}" {{ old('share_product_id_import') == $product->id ? 'selected' : '' }}>
                                    {{ $product->share_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('share_product_id_import') 
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

<!-- Import Opening Balance Modal -->
<div class="modal fade" id="importOpeningBalanceModal" tabindex="-1" aria-labelledby="importOpeningBalanceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importOpeningBalanceModalLabel">
                    <i class="bx bx-transfer me-2"></i>Import Opening Balance
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('shares.deposits.import-opening-balance') }}" method="POST" enctype="multipart/form-data" id="importOpeningBalanceForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Instructions:</strong>
                        <ol class="mb-0 mt-2">
                            <li>Select a share product</li>
                            <li>Select the opening balance date</li>
                            <li>Click "Download Template" to get Excel file with share accounts for the selected product</li>
                            <li>Fill in the Excel file with opening balance amounts (opening_balance_amount column)</li>
                            <li>Upload the filled Excel file</li>
                        </ol>
                        <div class="mt-2">
                            <strong>Note:</strong> This will use the SHARES opening balance account configured in Settings > Opening Balance Accounts.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="share_product_id_opening_balance" class="form-label">Share Product <span class="text-danger">*</span></label>
                        <select name="share_product_id" id="share_product_id_opening_balance" 
                                class="form-select @error('share_product_id') is-invalid @enderror" required>
                            <option value="">Select Share Product</option>
                            @foreach($shareProducts ?? [] as $product)
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
                        <label for="opening_balance_date" class="form-label">Opening Balance Date <span class="text-danger">*</span></label>
                        <input type="date" name="opening_balance_date" id="opening_balance_date" 
                               class="form-control @error('opening_balance_date') is-invalid @enderror"
                               value="{{ old('opening_balance_date', date('Y-m-d')) }}" required>
                        @error('opening_balance_date') 
                            <div class="invalid-feedback">{{ $message }}</div> 
                        @enderror
                    </div>

                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-primary" id="downloadOpeningBalanceTemplateBtn">
                            <i class="bx bx-download me-1"></i> Download Template
                        </button>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Upload Excel File <span class="text-danger">*</span></label>
                        <input type="file" name="import_file" id="import_file_opening_balance" 
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
                    <button type="submit" class="btn btn-warning">
                        <i class="bx bx-upload me-1"></i> Import Opening Balance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush
