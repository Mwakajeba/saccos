@extends('layouts.main')

@section('title', 'UTT Fund Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Investment', 'url' => '#', 'icon' => 'bx bx-trending-up'],
            ['label' => 'UTT Funds', 'url' => route('investments.funds.index'), 'icon' => 'bx bx-package'],
            ['label' => 'Fund Details', 'url' => '#', 'icon' => 'bx bx-show']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">UTT FUND DETAILS</h6>
            <div>
                <a href="{{ route('investments.funds.edit', \Vinkla\Hashids\Facades\Hashids::encode($fund->id)) }}" class="btn btn-primary me-2">
                    <i class="bx bx-edit me-1"></i> Edit
                </a>
                <a href="{{ route('investments.funds.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Back
                </a>
            </div>
        </div>
        <hr />

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Fund Information</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Fund Name:</strong>
                                <p>{{ $fund->fund_name }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Fund Code:</strong>
                                <p>{{ $fund->fund_code }}</p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Currency:</strong>
                                <p>{{ $fund->currency }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Investment Horizon:</strong>
                                <p>
                                    <span class="badge bg-{{ $fund->investment_horizon == 'LONG-TERM' ? 'info' : 'warning' }}">
                                        {{ $fund->investment_horizon }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Expense Ratio:</strong>
                                <p>{{ $fund->expense_ratio ? number_format($fund->expense_ratio, 4) . '%' : 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Status:</strong>
                                <p>
                                    <span class="badge bg-{{ $fund->status == 'Active' ? 'success' : 'secondary' }}">
                                        {{ $fund->status }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        @if($fund->notes)
                        <div class="mb-3">
                            <strong>Notes:</strong>
                            <p>{{ $fund->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Holdings Summary</h5>
                        @if($holding)
                            <div class="mb-3">
                                <strong>Total Units:</strong>
                                <p class="h5">{{ number_format($holding->total_units, 4) }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Average Cost:</strong>
                                <p class="h5">{{ number_format($holding->average_acquisition_cost, 4) }}</p>
                            </div>
                            @if($latestNav)
                            <div class="mb-3">
                                <strong>Current NAV:</strong>
                                <p class="h5">{{ number_format($latestNav->nav_per_unit, 4) }}</p>
                                <small class="text-muted">As of {{ $latestNav->nav_date->format('M d, Y') }}</small>
                            </div>
                            <div class="mb-3">
                                <strong>Current Value:</strong>
                                <p class="h5 text-primary">{{ number_format($currentValue, 2) }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Unrealized Gain/Loss:</strong>
                                @php
                                    $gain = $holding->getUnrealizedGain();
                                @endphp
                                <p class="h5 text-{{ $gain >= 0 ? 'success' : 'danger' }}">
                                    {{ number_format($gain, 2) }}
                                </p>
                            </div>
                            @else
                            <div class="alert alert-warning">
                                <small>No NAV data available</small>
                            </div>
                            @endif
                        @else
                            <div class="alert alert-info">
                                <small>No holdings data available</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

