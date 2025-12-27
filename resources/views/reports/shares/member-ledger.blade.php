@extends('layouts.main')

@section('title', 'Member Ledger Report')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-file'],
                ['label' => 'Share Reports', 'url' => route('reports.shares'), 'icon' => 'bx bx-bar-chart-square'],
                ['label' => 'Member Ledger', 'url' => '#', 'icon' => 'bx bx-book']
            ]" />
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="mb-0 text-uppercase">MEMBER LEDGER REPORT</h6>
                    <p class="text-muted mb-0">View detailed transaction history for a member's share account</p>
                </div>
            </div>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bx bx-filter me-2"></i>Filter Options
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Filter Form -->
                            <form method="GET" action="{{ route('reports.shares.member-ledger') }}" id="filterForm">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label for="account_id" class="form-label fw-bold">
                                            <i class="bx bx-wallet me-1"></i>Account
                                        </label>
                                        <select class="form-select" id="account_id" name="account_id">
                                            <option value="">Select Account</option>
                                            @foreach($shareAccounts as $acc)
                                                <option value="{{ $acc['id'] }}" {{ $accountId == $acc['id'] ? 'selected' : '' }}>
                                                    {{ $acc['label'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="customer_id" class="form-label fw-bold">
                                            <i class="bx bx-user me-1"></i>Member
                                        </label>
                                        <select class="form-select" id="customer_id" name="customer_id">
                                            <option value="">Select Member</option>
                                            @foreach($customers as $customer)
                                                <option value="{{ $customer->id }}" {{ $customerId == $customer->id ? 'selected' : '' }}>
                                                    {{ $customer->name }} ({{ $customer->customerNo }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="share_product_id" class="form-label fw-bold">
                                            <i class="bx bx-package me-1"></i>Share Product
                                        </label>
                                        <select class="form-select" id="share_product_id" name="share_product_id">
                                            <option value="">Select Product</option>
                                            @foreach($shareProducts as $product)
                                                <option value="{{ $product->id }}" {{ $shareProductId == $product->id ? 'selected' : '' }}>
                                                    {{ $product->share_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="date_from" class="form-label fw-bold">
                                            <i class="bx bx-calendar me-1"></i>Date From
                                        </label>
                                        <input type="date" class="form-control" id="date_from" name="date_from" value="{{ $dateFrom }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="date_to" class="form-label fw-bold">
                                            <i class="bx bx-calendar-check me-1"></i>Date To
                                        </label>
                                        <input type="date" class="form-control" id="date_to" name="date_to" value="{{ $dateTo }}">
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end">
                                        <div class="btn-group w-100" role="group">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-search me-1"></i> Generate
                                            </button>
                                            <a href="{{ route('reports.shares.member-ledger') }}" class="btn btn-secondary">
                                                <i class="bx bx-refresh me-1"></i> Clear
                                            </a>
                                            @if($account)
                                            <a href="{{ route('reports.shares.member-ledger', array_merge(request()->all(), ['export' => 'pdf'])) }}" class="btn btn-info" target="_blank">
                                                <i class="bx bx-download me-1"></i> Export PDF
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            @if($account)
                <!-- Account Information -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="bx bx-info-circle me-2"></i>Account Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="avatar-sm rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="bx bx-wallet"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <small class="text-muted d-block">Account Number</small>
                                                <strong class="text-primary">{{ $account->account_number }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="avatar-sm rounded-circle bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="bx bx-certificate"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <small class="text-muted d-block">Certificate Number</small>
                                                <strong class="text-info">{{ $account->certificate_number ?? 'N/A' }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="avatar-sm rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="bx bx-user"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <small class="text-muted d-block">Member Name</small>
                                                <strong>{{ $account->customer->name ?? 'N/A' }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="avatar-sm rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="bx bx-hash"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <small class="text-muted d-block">Member Number</small>
                                                <strong>{{ $account->customer->customerNo ?? 'N/A' }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="avatar-sm rounded-circle bg-secondary bg-opacity-10 text-secondary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="bx bx-package"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <small class="text-muted d-block">Share Product</small>
                                                <strong>{{ $account->shareProduct->share_name ?? 'N/A' }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="avatar-sm rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="bx bx-dollar"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <small class="text-muted d-block">Nominal Value</small>
                                                <strong>{{ number_format($account->nominal_value, 2) }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="avatar-sm rounded-circle bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="bx bx-calendar"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <small class="text-muted d-block">Opening Date</small>
                                                <strong>{{ $account->opening_date ? $account->opening_date->format('Y-m-d') : 'N/A' }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="avatar-sm rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="bx bx-check-circle"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <small class="text-muted d-block">Status</small>
                                                @if($account->status === 'active')
                                                    <span class="badge bg-success">Active</span>
                                                @elseif($account->status === 'inactive')
                                                    <span class="badge bg-warning text-dark">Inactive</span>
                                                @elseif($account->status === 'closed')
                                                    <span class="badge bg-danger">Closed</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                @if($transactions->count() > 0 || $openingBalance > 0)
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card border-info">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avatar-md rounded-circle bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                            <i class="bx bx-bar-chart-alt-2 fs-4"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <p class="text-muted mb-1">Opening Balance</p>
                                        <h4 class="mb-0 text-info">{{ number_format($openingBalance, 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-success">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avatar-md rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                            <i class="bx bx-trending-up fs-4"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <p class="text-muted mb-1">Total Transactions</p>
                                        <h4 class="mb-0 text-success">{{ $transactions->count() }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-primary">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avatar-md rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                            <i class="bx bx-down-arrow-circle fs-4"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <p class="text-muted mb-1">Total Deposits</p>
                                        <h4 class="mb-0 text-primary">{{ $transactions->where('type', 'Deposit')->count() }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-danger">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avatar-md rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                            <i class="bx bx-up-arrow-circle fs-4"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <p class="text-muted mb-1">Total Withdrawals</p>
                                        <h4 class="mb-0 text-danger">{{ $transactions->where('type', 'Withdrawal')->count() }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Transactions -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">
                                    <i class="bx bx-list-ul me-2"></i>Transaction History
                                    @if($transactions->count() > 0)
                                        <span class="badge bg-primary ms-2">{{ $transactions->count() }} transactions</span>
                                    @endif
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($transactions->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover table-striped align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="text-center" style="width: 100px;">Date</th>
                                                    <th style="width: 120px;">Type</th>
                                                    <th style="width: 120px;">Reference</th>
                                                    <th>Description</th>
                                                    <th class="text-end" style="width: 120px;">Shares</th>
                                                    <th class="text-end" style="width: 120px;">Balance</th>
                                                    <th class="text-center" style="width: 100px;">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="table-info">
                                                    <td class="text-center">
                                                        <strong>{{ \Carbon\Carbon::parse($dateFrom)->subDay()->format('Y-m-d') }}</strong>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info text-dark">Opening Balance</span>
                                                    </td>
                                                    <td>-</td>
                                                    <td><em>Starting balance for the period</em></td>
                                                    <td class="text-end">
                                                        <strong class="text-info">{{ number_format($openingBalance, 2) }}</strong>
                                                    </td>
                                                    <td class="text-end">
                                                        <strong class="text-info">{{ number_format($openingBalance, 2) }}</strong>
                                                    </td>
                                                    <td class="text-center">-</td>
                                                </tr>
                                                @foreach($transactions as $transaction)
                                                <tr>
                                                    <td class="text-center">{{ $transaction['date']->format('Y-m-d') }}</td>
                                                    <td>
                                                        @if($transaction['type'] === 'Deposit')
                                                            <span class="badge bg-success">Deposit</span>
                                                        @elseif($transaction['type'] === 'Withdrawal')
                                                            <span class="badge bg-danger">Withdrawal</span>
                                                        @elseif($transaction['type'] === 'Transfer In')
                                                            <span class="badge bg-info">Transfer In</span>
                                                        @elseif($transaction['type'] === 'Transfer Out')
                                                            <span class="badge bg-warning text-dark">Transfer Out</span>
                                                        @else
                                                            <span class="badge bg-secondary">{{ $transaction['type'] }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <code class="text-primary">{{ $transaction['reference'] }}</code>
                                                    </td>
                                                    <td>{{ $transaction['description'] }}</td>
                                                    <td class="text-end {{ $transaction['shares'] < 0 ? 'text-danger' : 'text-success' }}">
                                                        <strong>{{ $transaction['shares'] >= 0 ? '+' : '' }}{{ number_format($transaction['shares'], 2) }}</strong>
                                                    </td>
                                                    <td class="text-end">
                                                        <strong class="text-primary">{{ number_format($transaction['balance'], 2) }}</strong>
                                                    </td>
                                                    <td class="text-center">
                                                        @if($transaction['status'] === 'approved')
                                                            <span class="badge bg-success">Approved</span>
                                                        @elseif($transaction['status'] === 'pending')
                                                            <span class="badge bg-warning text-dark">Pending</span>
                                                        @elseif($transaction['status'] === 'rejected')
                                                            <span class="badge bg-danger">Rejected</span>
                                                        @else
                                                            <span class="badge bg-secondary">{{ ucfirst($transaction['status']) }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <div class="mb-3">
                                            <i class="bx bx-receipt fs-1 text-muted"></i>
                                        </div>
                                        <h5 class="text-muted">No Transactions Found</h5>
                                        <p class="text-muted">No transactions found for the selected period.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- No Account Selected -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card border-warning">
                            <div class="card-body text-center py-5">
                                <div class="mb-3">
                                    <i class="bx bx-info-circle fs-1 text-warning"></i>
                                </div>
                                <h5 class="text-warning">No Account Selected</h5>
                                <p class="text-muted">Please select an account, or select a member and share product to view the ledger.</p>
                                <p class="text-muted mb-0">
                                    <small><i class="bx bx-help-circle me-1"></i>Tip: Use the filter options above to search for an account</small>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
<style>
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: 1px solid rgba(0, 0, 0, 0.125);
    }
    
    .card-header {
        border-bottom: 2px solid rgba(0, 0, 0, 0.125);
    }
    
    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
    
    .badge {
        font-weight: 500;
        padding: 0.35em 0.65em;
    }
    
    .form-label {
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }
    
    .avatar-md, .avatar-sm {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    code {
        background-color: rgba(0, 123, 255, 0.1);
        padding: 0.2em 0.4em;
        border-radius: 3px;
        font-size: 0.9em;
    }
    
    .table-info {
        background-color: rgba(13, 110, 253, 0.1) !important;
    }
</style>
@endpush
