@extends('layouts.main')

@section('title', 'Contribution Products')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Contributions', 'url' => route('contributions.index'), 'icon' => 'bx bx-donate-heart'],
            ['label' => 'Contribution Products', 'url' => '#', 'icon' => 'bx bx-package']
        ]" />
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">CONTRIBUTION PRODUCTS</h6>
            <a href="{{ route('contributions.products.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Add New Product
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
                    <table class="table table-bordered table-striped nowrap" id="contributionProductsTable">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Interest</th>
                                <th>Auto Create</th>
                                <th>Compound Period</th>
                                <th>Lockin Period</th>
                                <th>Can Withdraw</th>
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
        var table = $('#contributionProductsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("contributions.products.data") }}',
                type: 'GET',
                error: function(xhr, error, code) {
                    console.error('DataTables Ajax Error:', error, code);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to load contribution products data. Please refresh the page.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            columns: [
                { data: 'product_name', name: 'product_name', title: 'Product Name' },
                { data: 'category_badge', name: 'category', title: 'Category' },
                { data: 'interest_formatted', name: 'interest', title: 'Interest' },
                { data: 'auto_create', name: 'auto_create', title: 'Auto Create' },
                { data: 'compound_period', name: 'compound_period', title: 'Compound Period' },
                { data: 'lockin_period_display', name: 'lockin_period_frequency', title: 'Lockin Period' },
                { data: 'can_withdraw_badge', name: 'can_withdraw', title: 'Can Withdraw' },
                { data: 'status_badge', name: 'is_active', title: 'Status' },
                { data: 'actions', name: 'actions', title: 'Actions', orderable: false, searchable: false }
            ],
            responsive: true,
            order: [[0, 'asc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            language: {
                search: "",
                searchPlaceholder: "Search contribution products...",
                processing: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>',
                emptyTable: "No contribution products found",
                info: "Showing _START_ to _END_ of _TOTAL_ contribution products",
                infoEmpty: "Showing 0 to 0 of 0 contribution products",
                infoFiltered: "(filtered from _MAX_ total contribution products)",
                lengthMenu: "Show _MENU_ contribution products per page",
                zeroRecords: "No matching contribution products found"
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
        $('#contributionProductsTable').on('click', '.delete-btn', function(e) {
            e.preventDefault();
            
            var productId = $(this).data('id');
            var productName = $(this).data('name');
            
            Swal.fire({
                title: 'Are you sure?',
                text: `You want to delete contribution product "${productName}"? This action cannot be undone!`,
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
                        'action': '{{ route("contributions.products.destroy", ":id") }}'.replace(':id', productId)
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
                        text: 'Please wait while we delete the contribution product.',
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
        window.refreshContributionProductsTable = function() {
            table.ajax.reload(null, false);
        };
    });
</script>
@endpush

