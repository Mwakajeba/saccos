@extends('layouts.main')

@section('title', 'Opening Balance Logs')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'Opening Balance Logs', 'url' => '#', 'icon' => 'bx bx-history']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase text-dark">OPENING BALANCE LOGS</h6>
            <div class="d-flex gap-2">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-outline-primary filter-btn active" data-type="all">All</button>
                    <button type="button" class="btn btn-sm btn-outline-success filter-btn" data-type="contribution">Contributions</button>
                    <button type="button" class="btn btn-sm btn-outline-info filter-btn" data-type="share">Shares</button>
                </div>
                <a href="{{ route('settings.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Back to Settings
                </a>
            </div>
        </div>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="openingBalanceLogsTable" class="table table-striped table-bordered" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Date</th>
                                        <th>Customer No</th>
                                        <th>Customer Name</th>
                                        <th>Product</th>
                                        <th>Account Number</th>
                                        <th>Amount</th>
                                        <th>Journal Reference</th>
                                        <th>Transaction Reference</th>
                                        <th>Description</th>
                                        <th>User</th>
                                        <th>Branch</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded via AJAX -->
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

@push('scripts')
<script>
    $(document).ready(function() {
        var currentType = 'all';
        
        // Initialize DataTable with Ajax
        var table = $('#openingBalanceLogsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("settings.opening-balance-logs.data") }}',
                type: 'GET',
                data: function(d) {
                    d.type = currentType;
                }
            },
            columns: [
                { data: 'type_badge', name: 'type', orderable: false, searchable: false },
                { data: 'date_formatted', name: 'date', orderable: true, searchable: false },
                { data: 'customer_no', name: 'customer.customerNo', orderable: false, searchable: true },
                { data: 'customer_name', name: 'customer.name', orderable: false, searchable: true },
                { data: 'product_name', name: 'product_name', orderable: false, searchable: true },
                { data: 'account_number', name: 'account_number', orderable: false, searchable: true },
                { data: 'amount_formatted', name: 'amount', orderable: true, searchable: false },
                { data: 'journal_reference', name: 'journal.reference', orderable: false, searchable: true },
                { data: 'transaction_reference_formatted', name: 'transaction_reference', orderable: false, searchable: true },
                { data: 'description', name: 'description', orderable: false, searchable: true },
                { data: 'user_name', name: 'user.name', orderable: false, searchable: true },
                { data: 'branch_name', name: 'branch.name', orderable: false, searchable: true },
            ],
            order: [[1, 'desc']], // Order by date descending
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            language: {
                processing: '<i class="bx bx-loader bx-spin font-medium-2"></i> Loading...'
            },
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
            drawCallback: function() {
                // Add any custom styling or callbacks here
            }
        });
        
        // Handle filter button clicks
        $('.filter-btn').on('click', function() {
            $('.filter-btn').removeClass('active');
            $(this).addClass('active');
            currentType = $(this).data('type');
            table.ajax.reload();
        });
    });
</script>
@endpush

