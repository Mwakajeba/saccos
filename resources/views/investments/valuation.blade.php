@extends('layouts.main')

@section('title', 'Portfolio Valuation')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Investment Management', 'url' => route('investments.index'), 'icon' => 'bx bx-trending-up'],
            ['label' => 'Portfolio Valuation', 'url' => '#', 'icon' => 'bx bx-bar-chart']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">PORTFOLIO VALUATION</h6>
            <a href="{{ route('investments.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bx bx-arrow-back me-1"></i> Back to Investment Management
            </a>
        </div>
        <hr />

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-primary">
                    <div class="card-body">
                        <h6 class="text-muted mb-1">Total Market Value</h6>
                        <h4 class="text-primary mb-0">{{ number_format($summary['total_value'], 2) }} TZS</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-secondary">
                    <div class="card-body">
                        <h6 class="text-muted mb-1">Total Cost Basis</h6>
                        <h4 class="text-secondary mb-0">{{ number_format($summary['total_cost'], 2) }} TZS</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-{{ $summary['total_unrealized_gain'] >= 0 ? 'success' : 'danger' }}">
                    <div class="card-body">
                        <h6 class="text-muted mb-1">Unrealized Gain/Loss</h6>
                        <h4 class="text-{{ $summary['total_unrealized_gain'] >= 0 ? 'success' : 'danger' }} mb-0">
                            {{ $summary['total_unrealized_gain'] >= 0 ? '+' : '' }}{{ number_format($summary['total_unrealized_gain'], 2) }} TZS
                        </h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-info">
                    <div class="card-body">
                        <h6 class="text-muted mb-1">Total Return %</h6>
                        <h4 class="text-info mb-0">
                            {{ $summary['total_unrealized_gain'] >= 0 ? '+' : '' }}{{ number_format($summary['total_return_pct'], 2) }}%
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Portfolio Table -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Fund-by-Fund Breakdown</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Fund Name</th>
                                <th>Fund Code</th>
                                <th class="text-end">Units</th>
                                <th class="text-end">Avg Cost</th>
                                <th class="text-end">Current NAV</th>
                                <th class="text-end">Cost Basis</th>
                                <th class="text-end">Current Value</th>
                                <th class="text-end">Unrealized Gain/Loss</th>
                                <th class="text-end">Return %</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($portfolio as $item)
                                <tr>
                                    <td>{{ $item['fund_name'] }}</td>
                                    <td><code>{{ $item['fund_code'] }}</code></td>
                                    <td class="text-end">{{ number_format($item['units'], 4) }}</td>
                                    <td class="text-end">{{ number_format($item['average_cost'], 4) }}</td>
                                    <td class="text-end">{{ number_format($item['current_nav'], 4) }}</td>
                                    <td class="text-end">{{ number_format($item['cost_basis'], 2) }} TZS</td>
                                    <td class="text-end fw-bold">{{ number_format($item['current_value'], 2) }} TZS</td>
                                    <td class="text-end text-{{ $item['unrealized_gain'] >= 0 ? 'success' : 'danger' }}">
                                        {{ $item['unrealized_gain'] >= 0 ? '+' : '' }}{{ number_format($item['unrealized_gain'], 2) }} TZS
                                    </td>
                                    <td class="text-end text-{{ $item['unrealized_gain_pct'] >= 0 ? 'success' : 'danger' }}">
                                        {{ $item['unrealized_gain_pct'] >= 0 ? '+' : '' }}{{ number_format($item['unrealized_gain_pct'], 2) }}%
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">No investment holdings found. Create a fund and record transactions to see valuation.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="alert alert-info mt-4">
            <i class="bx bx-info-circle me-2"></i>
            <strong>Note:</strong> Valuation is based on the latest available NAV (Net Asset Value) prices for each fund. Unrealized gain/loss represents the difference between current market value and cost basis.
        </div>
    </div>
</div>
@endsection
