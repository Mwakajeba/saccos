@extends('layouts.main')

@section('title', 'Contributions Transactions Report')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Contributions', 'url' => route('contributions.index'), 'icon' => 'bx bx-donate-heart'],
            ['label' => 'Transactions Report', 'url' => '#', 'icon' => 'bx bx-list-ul']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase text-info">Contributions Transactions Report</h6>
        </div>
        <hr />

        <!-- Filters Card -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('contributions.reports.transactions') }}" id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Contribution Product</label>
                            <select name="product_id" id="product_id" class="form-select">
                                <option value="">All Products</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ $productId == $product->id ? 'selected' : '' }}>
                                        {{ $product->product_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-info w-100">
                                <i class="bx bx-filter me-1"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Card -->
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="card border-primary">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Opening Balance</h6>
                        <h4 class="mb-0 text-primary">{{ number_format($openingBalance, 2) }} TZS</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-success">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Total Credits</h6>
                        <h4 class="mb-0 text-success">{{ number_format(collect($transactionsData)->sum('credit'), 2) }} TZS</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-danger">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Total Debits</h6>
                        <h4 class="mb-0 text-danger">{{ number_format(collect($transactionsData)->sum('debit'), 2) }} TZS</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="transactionsTable">
                        <thead class="table-info">
                            <tr>
                                <th>TrxId</th>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Product</th>
                                <th class="text-end">CR</th>
                                <th class="text-end">DR</th>
                                <th class="text-end">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactionsData as $transaction)
                                <tr class="{{ $transaction['trx_id'] === 'OB' ? 'table-warning fw-bold' : '' }}">
                                    <td>{{ $transaction['trx_id'] }}</td>
                                    <td>{{ $transaction['date'] }}</td>
                                    <td>{{ $transaction['description'] }}</td>
                                    <td>{{ $transaction['product_name'] }}</td>
                                    <td class="text-end text-success">
                                        @if($transaction['credit'] > 0)
                                            {{ number_format($transaction['credit'], 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end text-danger">
                                        @if($transaction['debit'] > 0)
                                            {{ number_format($transaction['debit'], 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold {{ $transaction['balance'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($transaction['balance'], 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No transactions found</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-info">
                            <tr>
                                <th colspan="4" class="text-end">Closing Balance:</th>
                                <th class="text-end text-success">
                                    {{ number_format(collect($transactionsData)->sum('credit'), 2) }}
                                </th>
                                <th class="text-end text-danger">
                                    {{ number_format(collect($transactionsData)->sum('debit'), 2) }}
                                </th>
                                <th class="text-end fw-bold">
                                    @php
                                        $closingBalance = $openingBalance + collect($transactionsData)->sum('credit') - collect($transactionsData)->sum('debit');
                                    @endphp
                                    <span class="{{ $closingBalance >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($closingBalance, 2) }}
                                    </span>
                                </th>
                            </tr>
                        </tfoot>
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
        // Initialize DataTable
        $('#transactionsTable').DataTable({
            order: [[1, 'asc']], // Sort by date ascending
            pageLength: 50,
            lengthMenu: [[20, 50, 100, -1], [20, 50, 100, "All"]],
            dom: 'Bfrtip',
            buttons: [
                'copy', 'excel', 'pdf', 'print'
            ],
            columnDefs: [
                { orderable: false, targets: [] } // Allow all columns to be sortable
            ]
        });
    });
</script>
@endpush
