@extends('layouts.main')

@section('title', 'Interest on Saving')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Contributions', 'url' => route('contributions.index'), 'icon' => 'bx bx-donate-heart'],
            ['label' => 'Interest on Saving', 'url' => '#', 'icon' => 'bx bx-calculator']
        ]" />
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">INTEREST ON SAVING</h6>
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
                    <table class="table table-bordered table-striped nowrap" id="interestOnSavingTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Day</th>
                                <th>Customer Name</th>
                                <th>Customer Number</th>
                                <th>Account Number</th>
                                <th>Product Name</th>
                                <th>Balance</th>
                                <th>Interest Rate</th>
                                <th>Interest Amount</th>
                                <th>Withholding</th>
                                <th>Net Amount</th>
                                <th>Status</th>
                                <th>Reason</th>
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
        var table = $('#interestOnSavingTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("contributions.interest-on-saving.data") }}',
                type: 'GET',
                error: function(xhr, error, code) {
                    console.error('DataTables Ajax Error:', error, code);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to load interest on saving data. Please refresh the page.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            columns: [
                { data: 'calculation_date_formatted', name: 'calculation_date', title: 'Date' },
                { data: 'day_of_calculation', name: 'date_of_calculation', title: 'Day' },
                { data: 'customer_name', name: 'customer.name', title: 'Customer Name' },
                { data: 'customer_number', name: 'customer.customerNo', title: 'Customer Number' },
                { data: 'account_number', name: 'contributionAccount.account_number', title: 'Account Number' },
                { data: 'product_name', name: 'contributionProduct.product_name', title: 'Product Name' },
                { data: 'balance_formatted', name: 'account_balance_at_interest_calculation', title: 'Balance' },
                { data: 'interest_rate_formatted', name: 'interest_rate', title: 'Interest Rate' },
                { data: 'interest_amount_formatted', name: 'interest_amount_gained', title: 'Interest Amount' },
                { data: 'withholding_amount_formatted', name: 'withholding_amount', title: 'Withholding' },
                { data: 'net_amount_formatted', name: 'net_amount', title: 'Net Amount' },
                { data: 'posted_badge', name: 'posted', title: 'Status' },
                { data: 'reason_text', name: 'reason', title: 'Reason' }
            ],
            responsive: true,
            order: [[0, 'desc']], // Order by date descending
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            language: {
                search: "",
                searchPlaceholder: "Search interest records...",
                processing: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>',
                emptyTable: "No interest records found",
                info: "Showing _START_ to _END_ of _TOTAL_ interest records",
                infoEmpty: "Showing 0 to 0 of 0 interest records",
                infoFiltered: "(filtered from _MAX_ total interest records)",
                lengthMenu: "Show _MENU_ interest records per page",
                zeroRecords: "No matching interest records found"
            },
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
            drawCallback: function(settings) {
                // Reinitialize tooltips after each draw
                $('[data-bs-toggle="tooltip"]').tooltip();
            }
        });

        // Refresh table data function
        window.refreshInterestOnSavingTable = function() {
            table.ajax.reload(null, false);
        };
    });
</script>
@endpush
