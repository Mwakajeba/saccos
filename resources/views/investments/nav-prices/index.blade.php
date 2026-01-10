@extends('layouts.main')

@section('title', 'NAV Prices')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Investment', 'url' => '#', 'icon' => 'bx bx-trending-up'],
            ['label' => 'NAV Prices', 'url' => '#', 'icon' => 'bx bx-line-chart']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">DAILY NAV PRICES</h6>
            <a href="{{ route('investments.nav-prices.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Enter NAV Price
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

                <div class="mb-3">
                    <label for="fund_filter" class="form-label">Filter by Fund:</label>
                    <select class="form-select" id="fund_filter" style="max-width: 300px;">
                        <option value="">All Funds</option>
                        @foreach($funds as $fund)
                            <option value="{{ $fund->id }}">{{ $fund->fund_name }} ({{ $fund->fund_code }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped nowrap" id="navPricesTable">
                        <thead>
                            <tr>
                                <th>Fund Name</th>
                                <th>Fund Code</th>
                                <th>NAV Date</th>
                                <th>NAV per Unit</th>
                                <th>Entered By</th>
                                <th>Date Entered</th>
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

@push('scripts')
<script>
    $(document).ready(function() {
        var table = $('#navPricesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("investments.nav-prices.data") }}',
                type: 'GET',
                data: function(d) {
                    var fundId = $('#fund_filter').val();
                    if (fundId) {
                        d.utt_fund_id = fundId;
                    }
                },
                error: function(xhr, error, code) {
                    console.error('DataTables Ajax Error:', error, code);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to load NAV prices data. Please refresh the page.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            columns: [
                { data: 'fund_name', name: 'fund_name', title: 'Fund Name' },
                { data: 'fund_code', name: 'fund_code', title: 'Fund Code' },
                { data: 'nav_date', name: 'nav_date', title: 'NAV Date' },
                { data: 'nav_formatted', name: 'nav_per_unit', title: 'NAV per Unit' },
                { data: 'entered_by_name', name: 'entered_by_name', title: 'Entered By' },
                { data: 'created_at', name: 'created_at', title: 'Date Entered' }
            ],
            responsive: true,
            order: [[2, 'desc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            language: {
                search: "",
                searchPlaceholder: "Search NAV prices...",
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
            }
        });

        $('#fund_filter').on('change', function() {
            table.ajax.reload(null, false);
        });
    });
</script>
@endpush

