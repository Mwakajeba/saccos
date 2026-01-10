@extends('layouts.main')

@section('title', 'UTT Funds')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Investment', 'url' => '#', 'icon' => 'bx bx-trending-up'],
            ['label' => 'UTT Funds', 'url' => '#', 'icon' => 'bx bx-package']
        ]" />

        <div class="row">
            <div class="col-md-4">
                <div class="card border-primary border-2 filter-card" data-status="">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Total Funds</h6>
                                <h4 class="mb-0">{{ $totalFunds }}</h4>
                            </div>
                            <div class="fs-3 text-primary">
                                <i class="bx bx-package"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-success border-2 filter-card" data-status="Active">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Active Funds</h6>
                                <h4 class="mb-0">{{ $activeFunds }}</h4>
                            </div>
                            <div class="fs-3 text-success">
                                <i class="bx bx-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-secondary border-2 filter-card" data-status="Closed">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Closed Funds</h6>
                                <h4 class="mb-0">{{ $closedFunds }}</h4>
                            </div>
                            <div class="fs-3 text-secondary">
                                <i class="bx bx-x-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
            <h6 class="mb-0 text-uppercase">UTT FUNDS</h6>
            <a href="{{ route('investments.funds.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Add UTT Fund
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
                    <table class="table table-bordered table-striped nowrap" id="fundsTable">
                        <thead>
                            <tr>
                                <th>Fund Name</th>
                                <th>Fund Code</th>
                                <th>Currency</th>
                                <th>Investment Horizon</th>
                                <th>Expense Ratio</th>
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
    .filter-card {
        cursor: pointer;
        transition: all 0.3s;
    }
    .filter-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .dataTables_processing {
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 20px;
        font-size: 16px;
        z-index: 9999;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        window.currentStatusFilter = '';

        var table = $('#fundsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("investments.funds.data") }}',
                type: 'GET',
                data: function(d) {
                    if (window.currentStatusFilter) {
                        d.status = window.currentStatusFilter;
                    }
                },
                error: function(xhr, error, code) {
                    console.error('DataTables Ajax Error:', error, code);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to load funds data. Please refresh the page.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            columns: [
                { data: 'fund_name', name: 'fund_name', title: 'Fund Name' },
                { data: 'fund_code', name: 'fund_code', title: 'Fund Code' },
                { data: 'currency', name: 'currency', title: 'Currency' },
                { data: 'horizon_badge', name: 'investment_horizon', title: 'Investment Horizon' },
                { data: 'expense_ratio_formatted', name: 'expense_ratio', title: 'Expense Ratio' },
                { data: 'status_badge', name: 'status', title: 'Status' },
                { data: 'actions', name: 'actions', title: 'Actions', orderable: false, searchable: false }
            ],
            responsive: true,
            order: [[0, 'asc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            language: {
                search: "",
                searchPlaceholder: "Search funds...",
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
            }
        });

        // Handle filter card clicks
        $('.filter-card').on('click', function() {
            var status = $(this).data('status');
            window.currentStatusFilter = status;

            // Update card styles
            $('.filter-card').removeClass('border-primary border-2');
            $(this).addClass('border-primary border-2');

            // Reload table
            table.ajax.reload(null, false);
        });

        // Set initial active filter
        $('.filter-card[data-status=""]').addClass('border-primary border-2');
    });
</script>
@endpush

