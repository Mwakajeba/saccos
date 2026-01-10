@extends('layouts.main')

@section('title', 'Investment Portfolio')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Investment Portfolio', 'url' => '#', 'icon' => 'bx bx-trending-up']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">SACCO INVESTMENT PORTFOLIO</h6>
        </div>
        <hr />

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card border-primary">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="mb-0">Total Portfolio Value</h5>
                                <h2 class="text-primary mb-0">{{ number_format($totalValue, 2) }} TZS</h2>
                            </div>
                            <div class="fs-1 text-primary">
                                <i class="bx bx-trending-up"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Fund-by-Fund Breakdown</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Fund Name</th>
                                <th>Fund Code</th>
                                <th>Investment Horizon</th>
                                <th>Current NAV</th>
                                <th>NAV Date</th>
                                <th>Current Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($portfolio as $item)
                                <tr>
                                    <td>{{ $item['fund_name'] }}</td>
                                    <td>{{ $item['fund_code'] }}</td>
                                    <td>
                                        <span class="badge bg-{{ $item['investment_horizon'] == 'LONG-TERM' ? 'info' : 'warning' }}">
                                            {{ $item['investment_horizon'] }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($item['current_nav'], 4) }}</td>
                                    <td>{{ $item['nav_date'] ? \Carbon\Carbon::parse($item['nav_date'])->format('M d, Y') : 'N/A' }}</td>
                                    <td class="fw-bold">{{ number_format($item['current_value'], 2) }} TZS</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No investment data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="alert alert-info mt-4">
            <i class="bx bx-info-circle me-2"></i>
            <strong>Note:</strong> This is a read-only view of the SACCO's investment portfolio. Investment values are calculated based on the latest available NAV (Net Asset Value) prices. For detailed transaction information, please contact your investment officer.
        </div>
    </div>
</div>
@endsection

