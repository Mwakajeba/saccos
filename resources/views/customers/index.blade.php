@extends('layouts.main')

@section('title', 'Customer Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Customers', 'url' => '#', 'icon' => 'bx bx-group']
             ]" />
        <h6 class="mb-0 text-uppercase">CUSTOMER LIST</h6>
        <hr />

        <!-- Dashboard Stats -->
        <div class="row row-cols-1 row-cols-lg-3">
            <div class="col mb-4">
                <div class="card radius-10 cursor-pointer filter-card" data-status="active" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Total Active Customers</p>
                            <h4 class="mb-0">{{ $activeCount ?? 0 }}</h4>
                        </div>
                        <div class="widgets-icons bg-gradient-success text-white"><i class='bx bx-check-circle'></i></div>
                    </div>
                </div>
            </div>
            <div class="col mb-4">
                <div class="card radius-10 cursor-pointer filter-card" data-status="inactive" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Total Inactive Customers</p>
                            <h4 class="mb-0">{{ $inactiveCount ?? 0 }}</h4>
                        </div>
                        <div class="widgets-icons bg-gradient-danger text-white"><i class='bx bx-x-circle'></i></div>
                    </div>
                </div>
            </div>
            <div class="col mb-4">
                <div class="card radius-10 cursor-pointer filter-card" data-status="" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">All Customers</p>
                            <h4 class="mb-0">{{ ($activeCount ?? 0) + ($inactiveCount ?? 0) }}</h4>
                        </div>
                        <div class="widgets-icons bg-gradient-primary text-white"><i class='bx bx-group'></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customers Table -->
        <div class="row">
            <div class="col-12">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="card-title mb-0">Customers List</h6>
                            <div>
                                @can('create customer')
                                <a href="{{ route('customers.bulk-upload') }}" class="btn btn-success me-2">
                                    <i class="bx bx-upload"></i> Bulk Upload
                                </a>
                                <a href="{{ route('customers.create') }}" class="btn btn-primary">
                                    <i class="bx bx-plus"></i> Add Customer
                                </a>
                                @endcan
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped nowrap" id="customersTable">
                                <thead>
                                    <tr>
                                        <th>Customer No</th>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Region</th>
                                        <th>District</th>
                                        <th>Branch</th>
                                        <th>Category</th>
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
    
    .avatar {
        flex-shrink: 0;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize status filter (default: show all customers)
        window.currentStatusFilter = '';

        // Initialize DataTable with Ajax
        var table = $('#customersTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("customers.data") }}',
                type: 'GET',
                timeout: 60000, // 60 second timeout
                data: function(d) {
                    // Add status filter to AJAX request only if set
                    if (window.currentStatusFilter) {
                        d.status = window.currentStatusFilter;
                    }
                },
                error: function(xhr, error, code) {
                    console.error('DataTables Ajax Error:', error, code, xhr);
                    var errorMessage = 'Failed to load customers data.';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    } else if (error === 'timeout') {
                        errorMessage = 'Request timed out. Please try again.';
                    } else if (error === 'parsererror') {
                        errorMessage = 'Failed to parse response. Please check the console for details.';
                    }
                    Swal.fire({
                        title: 'Error!',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            columns: [
                { data: 'customerNo', name: 'customerNo', title: 'Customer No', orderable: true, searchable: false },
                { data: 'avatar_name', name: 'name', title: 'Name', orderable: true, searchable: true },
                { data: 'phone1', name: 'phone1', title: 'Phone', orderable: true, searchable: true },
                { data: 'region_name', name: 'region_name', title: 'Region', orderable: false, searchable: false },
                { data: 'district_name', name: 'district_name', title: 'District', orderable: false, searchable: false },
                { data: 'branch_name', name: 'branch_name', title: 'Branch', orderable: false, searchable: false },
                { data: 'category', name: 'category', title: 'Category', orderable: true, searchable: true },
                { data: 'actions', name: 'actions', title: 'Actions', orderable: false, searchable: false }
            ],
            responsive: true,
            order: [[0, 'desc']], // Order by customerNo descending
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            language: {
                search: "",
                searchPlaceholder: "Search customers...",
                processing: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>',
                emptyTable: "No customers found",
                info: "Showing _START_ to _END_ of _TOTAL_ customers",
                infoEmpty: "Showing 0 to 0 of 0 customers",
                infoFiltered: "(filtered from _MAX_ total customers)",
                lengthMenu: "Show _MENU_ customers per page",
                zeroRecords: "No matching customers found"
            },
            columnDefs: [
                {
                    targets: -1, // Actions column
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    targets: [0, 1, 2], // Priority columns for responsive
                    responsivePriority: 2
                }
            ],
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
            drawCallback: function(settings) {
                // Reinitialize tooltips after each draw
                $('[data-bs-toggle="tooltip"]').tooltip();
            }
        });

        // Handle delete button clicks
        $('#customersTable').on('click', '.delete-btn', function(e) {
            e.preventDefault();
            
            var customerId = $(this).data('id');
            var customerName = $(this).data('name');
            
            Swal.fire({
                title: 'Are you sure?',
                text: `You want to delete customer "${customerName}"? This action cannot be undone!`,
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
                        'action': '{{ route("customers.destroy", ":id") }}'.replace(':id', customerId)
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
                        text: 'Please wait while we delete the customer.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    form.submit();
                }
            });
        });

        // Handle block/unblock button clicks
        $('#customersTable').on('click', '.toggle-status-btn', function(e) {
            e.preventDefault();
            
            var customerId = $(this).data('id');
            var customerName = $(this).data('name');
            var currentStatus = $(this).data('status');
            var isActive = currentStatus === 'active';
            var actionText = isActive ? 'block' : 'unblock';
            
            Swal.fire({
                title: 'Are you sure?',
                text: `You want to ${actionText} customer "${customerName}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: isActive ? '#f0ad4e' : '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `Yes, ${actionText} it!`,
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Processing...',
                        text: `Please wait while we ${actionText} the customer.`,
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Make AJAX request
                    $.ajax({
                        url: '{{ route("customers.toggle-status", ":id") }}'.replace(':id', customerId),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Success!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                // Reload table to reflect changes
                                table.ajax.reload(null, false);
                            });
                        },
                        error: function(xhr) {
                            var errorMessage = 'Failed to update customer status.';
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

        // Highlight "All Customers" card by default
        $('.filter-card[data-status=""]').addClass('border-primary border-2');

        // Handle filter card clicks
        $('.filter-card').on('click', function() {
            var status = $(this).data('status');
            window.currentStatusFilter = status || '';
            
            // Update card styles to show active filter
            $('.filter-card').removeClass('border-primary border-2');
            $(this).addClass('border-primary border-2');
            
            // Reload table with new filter
            table.ajax.reload(null, false);
        });

        // Refresh table data function
        window.refreshCustomersTable = function() {
            table.ajax.reload(null, false);
        };
    });
</script>
@endpush