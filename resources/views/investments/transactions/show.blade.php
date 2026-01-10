@extends('layouts.main')

@section('title', 'Transaction Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Investment', 'url' => '#', 'icon' => 'bx bx-trending-up'],
            ['label' => 'Transactions', 'url' => route('investments.transactions.index'), 'icon' => 'bx bx-transfer'],
            ['label' => 'Transaction Details', 'url' => '#', 'icon' => 'bx bx-show']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">TRANSACTION DETAILS</h6>
            <a href="{{ route('investments.transactions.index') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back
            </a>
        </div>
        <hr />

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Transaction Information</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Reference Number:</strong>
                                <p>{{ $transaction->reference_number }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Fund:</strong>
                                <p>{{ $transaction->uttFund->fund_name }} ({{ $transaction->uttFund->fund_code }})</p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Transaction Type:</strong>
                                <p>
                                    <span class="badge bg-{{ $transaction->transaction_type == 'BUY' ? 'success' : ($transaction->transaction_type == 'SELL' ? 'danger' : 'info') }}">
                                        {{ $transaction->transaction_type }}
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <strong>Status:</strong>
                                <p>
                                    <span class="badge bg-{{ $transaction->status == 'SETTLED' ? 'success' : ($transaction->status == 'APPROVED' ? 'info' : ($transaction->status == 'PENDING' ? 'warning' : 'danger')) }}">
                                        {{ $transaction->status }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Trade Date:</strong>
                                <p>{{ $transaction->trade_date->format('M d, Y') }}</p>
                            </div>
                            <div class="col-md-4">
                                <strong>NAV Date:</strong>
                                <p>{{ $transaction->nav_date->format('M d, Y') }}</p>
                            </div>
                            <div class="col-md-4">
                                <strong>Settlement Date:</strong>
                                <p>{{ $transaction->settlement_date->format('M d, Y') }}</p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Units:</strong>
                                <p class="h5">{{ number_format($transaction->units, 4) }}</p>
                            </div>
                            <div class="col-md-4">
                                <strong>NAV per Unit:</strong>
                                <p class="h5">{{ number_format($transaction->nav_per_unit, 4) }}</p>
                            </div>
                            <div class="col-md-4">
                                <strong>Total Cash Value:</strong>
                                <p class="h5 text-primary">{{ number_format($transaction->total_cash_value, 2) }}</p>
                            </div>
                        </div>
                        @if($transaction->description)
                        <div class="mb-3">
                            <strong>Description:</strong>
                            <p>{{ $transaction->description }}</p>
                        </div>
                        @endif
                        @if($transaction->rejection_reason)
                        <div class="mb-3">
                            <strong>Rejection Reason:</strong>
                            <p class="text-danger">{{ $transaction->rejection_reason }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Approval Information</h5>
                        <div class="mb-3">
                            <strong>Maker:</strong>
                            <p>{{ $transaction->maker->name ?? 'N/A' }}</p>
                        </div>
                        @if($transaction->checker)
                        <div class="mb-3">
                            <strong>Checker:</strong>
                            <p>{{ $transaction->checker->name }}</p>
                        </div>
                        @endif
                        @if($transaction->approved_at)
                        <div class="mb-3">
                            <strong>Approved At:</strong>
                            <p>{{ $transaction->approved_at->format('M d, Y H:i') }}</p>
                        </div>
                        @endif
                        @if($transaction->settled_at)
                        <div class="mb-3">
                            <strong>Settled At:</strong>
                            <p>{{ $transaction->settled_at->format('M d, Y H:i') }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

