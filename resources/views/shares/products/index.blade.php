@extends('layouts.main')

@section('title', 'Share Products')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Shares Management', 'url' => route('shares.management'), 'icon' => 'bx bx-bar-chart-square'],
            ['label' => 'Share Products', 'url' => '#', 'icon' => 'bx bx-package']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">SHARE PRODUCTS</h6>
            <a href="{{ route('shares.products.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Add Share Product
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
                    <table class="table table-bordered table-striped nowrap" id="shareProductsTable">
                        <thead>
                            <tr>
                                <th>Share Name</th>
                                <th>Required Share</th>
                                <th>Nominal Price</th>
                                <th>Lockin Period</th>
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
        var table = $('#shareProductsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("shares.products.data") }}',
                type: 'GET',
                error: function(xhr, error, code) {
                    console.error('DataTables Ajax Error:', error, code);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to load share products data. Please refresh the page.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            columns: [
                { data: 'share_name', name: 'share_name', title: 'Share Name' },
                { data: 'required_share_formatted', name: 'required_share', title: 'Required Share' },
                { data: 'nominal_price_formatted', name: 'nominal_price', title: 'Nominal Price' },
                { data: 'lockin_period_display', name: 'lockin_period_frequency', title: 'Lockin Period' },
                { data: 'status_badge', name: 'is_active', title: 'Status' },
                { data: 'actions', name: 'actions', title: 'Actions', orderable: false, searchable: false }
            ],
            responsive: true,
            order: [[0, 'asc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            language: {
                search: "",
                searchPlaceholder: "Search share products...",
                processing: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>',
                emptyTable: "No share products found",
                info: "Showing _START_ to _END_ of _TOTAL_ share products",
                infoEmpty: "Showing 0 to 0 of 0 share products",
                infoFiltered: "(filtered from _MAX_ total share products)",
                lengthMenu: "Show _MENU_ share products per page",
                zeroRecords: "No matching share products found"
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

        // Handle toggle status button clicks
        $('#shareProductsTable').on('click', '.toggle-status-btn', function(e) {
            e.preventDefault();
            
            var productId = $(this).data('id');
            var productName = $(this).data('name');
            var currentStatus = $(this).data('status');
            var newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            var actionText = currentStatus === 'active' ? 'deactivate' : 'activate';
            var confirmColor = currentStatus === 'active' ? '#ffc107' : '#28a745';
            
            Swal.fire({
                title: `${actionText.charAt(0).toUpperCase() + actionText.slice(1)} Share Product?`,
                text: `Are you sure you want to ${actionText} "${productName}"?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: confirmColor,
                cancelButtonColor: '#6c757d',
                confirmButtonText: `Yes, ${actionText} it!`,
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create a form to submit the toggle status request
                    var form = $('<form>', {
                        'method': 'POST',
                        'action': '{{ route("shares.products.toggle-status", ":id") }}'.replace(':id', productId)
                    });
                    
                    var csrfToken = $('<input>', {
                        'type': 'hidden',
                        'name': '_token',
                        'value': '{{ csrf_token() }}'
                    });
                    
                    var methodField = $('<input>', {
                        'type': 'hidden',
                        'name': '_method',
                        'value': 'PATCH'
                    });
                    
                    form.append(csrfToken, methodField);
                    $('body').append(form);
                    
                    // Show loading
                    Swal.fire({
                        title: `${actionText.charAt(0).toUpperCase() + actionText.slice(1)}ing...`,
                        text: `Please wait while we ${actionText} the share product.`,
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    form.submit();
                }
            });
        });

        // Handle delete button clicks
        $('#shareProductsTable').on('click', '.delete-btn', function(e) {
            e.preventDefault();
            
            var productId = $(this).data('id');
            var productName = $(this).data('name');
            
            Swal.fire({
                title: 'Are you sure?',
                text: `You want to delete share product "${productName}"? This action cannot be undone!`,
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
                        'action': '{{ route("shares.products.destroy", ":id") }}'.replace(':id', productId)
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
                        text: 'Please wait while we delete the share product.',
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
        window.refreshShareProductsTable = function() {
            table.ajax.reload(null, false);
        };
    });
</script>
@endpush
