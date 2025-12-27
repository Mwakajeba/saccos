@extends('layouts.main')

@section('title', 'Dividends')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Shares Management', 'url' => route('shares.management'), 'icon' => 'bx bx-bar-chart-square'],
            ['label' => 'Dividends', 'url' => '#', 'icon' => 'bx bx-dollar']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">DIVIDENDS</h6>
            <a href="{{ route('dividends.dividends.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Declare Dividend
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

                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="filter_share_product" class="form-label">Share Product</label>
                        <select id="filter_share_product" class="form-select">
                            <option value="">All Products</option>
                            @foreach($shareProducts as $product)
                                <option value="{{ $product->id }}">{{ $product->share_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="filter_status" class="form-label">Status</label>
                        <select id="filter_status" class="form-select">
                            <option value="">All Status</option>
                            <option value="draft">Draft</option>
                            <option value="calculated">Calculated</option>
                            <option value="approved">Approved</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped nowrap" id="dividendsTable">
                        <thead>
                            <tr>
                                <th>SN</th>
                                <th>Dividend Number</th>
                                <th>Share Product</th>
                                <th>Financial Year</th>
                                <th>Total Amount</th>
                                <th>Total Shares</th>
                                <th>Dividend Per Share</th>
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

@push('scripts')
<script>
    $(document).ready(function() {
        var table = $('#dividendsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('dividends.dividends.data') }}",
                type: "GET",
                data: function(d) {
                    d.share_product_id = $('#filter_share_product').val();
                    d.status = $('#filter_status').val();
                }
            },
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'dividend_number', name: 'dividend_number'},
                {data: 'share_product_name', name: 'share_product_name'},
                {data: 'financial_year', name: 'financial_year'},
                {data: 'total_dividend_amount', name: 'total_dividend_amount', render: function(data) {
                    return parseFloat(data).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                }},
                {data: 'total_shares', name: 'total_shares', render: function(data) {
                    return parseFloat(data).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                }},
                {data: 'dividend_per_share', name: 'dividend_per_share', render: function(data) {
                    return parseFloat(data).toLocaleString('en-US', {minimumFractionDigits: 4, maximumFractionDigits: 4});
                }},
                {data: 'status', name: 'status', render: function(data) {
                    var badge = 'secondary';
                    if (data === 'approved') badge = 'success';
                    else if (data === 'calculated') badge = 'info';
                    else if (data === 'paid') badge = 'primary';
                    return '<span class="badge bg-' + badge + '">' + data.toUpperCase() + '</span>';
                }},
                {data: 'actions', name: 'actions', orderable: false, searchable: false}
            ],
            order: [[0, 'desc']]
        });

        $('#filter_share_product, #filter_status').on('change', function() {
            table.draw();
        });
    });
</script>
@endpush

