@extends('layouts.main')

@section('title', 'Share Register Report')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-file'],
                ['label' => 'Share Reports', 'url' => route('reports.shares'), 'icon' => 'bx bx-bar-chart-square'],
                ['label' => 'Share Register', 'url' => '#', 'icon' => 'bx bx-list-ul']
            ]" />
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="mb-0 text-uppercase">SHARE REGISTER REPORT</h6>
                    <p class="text-muted mb-0">View all share accounts with certificate numbers, member details, and share balances</p>
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
                            <form method="GET" action="{{ route('reports.shares.share-register') }}" id="filterForm">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label for="share_product_id" class="form-label fw-bold">
                                            <i class="bx bx-package me-1"></i>Share Product
                                        </label>
                                        <select class="form-select" id="share_product_id" name="share_product_id">
                                            <option value="">All Products</option>
                                            @foreach($shareProducts as $product)
                                                <option value="{{ $product->id }}" {{ $shareProductId == $product->id ? 'selected' : '' }}>
                                                    {{ $product->share_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="status" class="form-label fw-bold">
                                            <i class="bx bx-check-circle me-1"></i>Status
                                        </label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="">All Status</option>
                                            <option value="active" {{ $status == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ $status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                            <option value="closed" {{ $status == 'closed' ? 'selected' : '' }}>Closed</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="as_of_date" class="form-label fw-bold">
                                            <i class="bx bx-calendar me-1"></i>As Of Date
                                        </label>
                                        <input type="date" class="form-control" id="as_of_date" name="as_of_date" value="{{ $asOfDate }}">
                                    </div>
                                    @if($branches->count() > 1)
                                    <div class="col-md-2">
                                        <label for="branch_id" class="form-label fw-bold">
                                            <i class="bx bx-buildings me-1"></i>Branch
                                        </label>
                                        <select class="form-select" id="branch_id" name="branch_id">
                                            <option value="all" {{ $branchId == 'all' ? 'selected' : '' }}>All Branches</option>
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                                                    {{ $branch->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @endif
                                    <div class="col-md-3 d-flex align-items-end">
                                        <div class="btn-group w-100" role="group">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-search me-1"></i> Filter
                                            </button>
                                            <a href="{{ route('reports.shares.share-register') }}" class="btn btn-secondary">
                                                <i class="bx bx-refresh me-1"></i> Clear
                                            </a>
                                            <a href="{{ route('reports.shares.share-register', array_merge(request()->all(), ['export' => 'pdf'])) }}" class="btn btn-info" target="_blank">
                                                <i class="bx bx-download me-1"></i> Export PDF
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            @if($accounts->count() > 0)
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card border-primary">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-md rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <i class="bx bx-user fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-muted mb-1">Total Accounts</p>
                                    <h4 class="mb-0 text-primary">{{ $accounts->count() }}</h4>
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
                                        <i class="bx bx-bar-chart-alt-2 fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-muted mb-1">Total Shares</p>
                                    <h4 class="mb-0 text-success">{{ number_format($accounts->sum('share_balance'), 2) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-info">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-md rounded-circle bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <i class="bx bx-dollar fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-muted mb-1">Total Value</p>
                                    <h4 class="mb-0 text-info">{{ number_format($accounts->sum(fn($acc) => $acc->share_balance * $acc->nominal_value), 2) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-warning">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-md rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <i class="bx bx-check-circle fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-muted mb-1">Active Accounts</p>
                                    <h4 class="mb-0 text-warning">{{ $accounts->where('status', 'active')->count() }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Data Table -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="bx bx-list-ul me-2"></i>Share Accounts
                                @if($accounts->count() > 0)
                                    <span class="badge bg-primary ms-2">{{ $accounts->count() }} records</span>
                                @endif
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($accounts->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-striped align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center" style="width: 50px;">SN</th>
                                                <th>Certificate #</th>
                                                <th>Account #</th>
                                                <th>Member Name</th>
                                                <th>Member #</th>
                                                <th>Share Product</th>
                                                <th class="text-end">Share Balance</th>
                                                <th class="text-end">Nominal Value</th>
                                                <th class="text-end">Total Value</th>
                                                <th>Opening Date</th>
                                                <th class="text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($accounts as $index => $account)
                                            <tr>
                                                <td class="text-center">{{ $index + 1 }}</td>
                                                <td>
                                                    <span class="badge bg-info text-dark">{{ $account->certificate_number ?? 'N/A' }}</span>
                                                </td>
                                                <td>
                                                    <strong class="text-primary">{{ $account->account_number }}</strong>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="bx bx-user me-2 text-muted"></i>
                                                        <span>{{ $account->customer->name ?? 'N/A' }}</span>
                                                    </div>
                                                </td>
                                                <td>{{ $account->customer->customerNo ?? 'N/A' }}</td>
                                                <td>
                                                    <span class="badge bg-secondary">{{ $account->shareProduct->share_name ?? 'N/A' }}</span>
                                                </td>
                                                <td class="text-end">
                                                    <strong>{{ number_format($account->share_balance, 2) }}</strong>
                                                </td>
                                                <td class="text-end">{{ number_format($account->nominal_value, 2) }}</td>
                                                <td class="text-end">
                                                    <strong class="text-success">{{ number_format($account->share_balance * $account->nominal_value, 2) }}</strong>
                                                </td>
                                                <td>{{ $account->opening_date ? $account->opening_date->format('Y-m-d') : 'N/A' }}</td>
                                                <td class="text-center">
                                                    @if($account->status === 'active')
                                                        <span class="badge bg-success">Active</span>
                                                    @elseif($account->status === 'inactive')
                                                        <span class="badge bg-warning text-dark">Inactive</span>
                                                    @elseif($account->status === 'closed')
                                                        <span class="badge bg-danger">Closed</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ ucfirst($account->status) }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <td colspan="6" class="text-end fw-bold">TOTAL:</td>
                                                <td class="text-end fw-bold text-primary">{{ number_format($accounts->sum('share_balance'), 2) }}</td>
                                                <td></td>
                                                <td class="text-end fw-bold text-success">{{ number_format($accounts->sum(fn($acc) => $acc->share_balance * $acc->nominal_value), 2) }}</td>
                                                <td colspan="2"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <div class="mb-3">
                                        <i class="bx bx-search-alt-2 fs-1 text-muted"></i>
                                    </div>
                                    <h5 class="text-muted">No Share Accounts Found</h5>
                                    <p class="text-muted">No share accounts match the selected filter criteria. Please adjust your filters and try again.</p>
                                    <a href="{{ route('reports.shares.share-register') }}" class="btn btn-primary">
                                        <i class="bx bx-refresh me-1"></i> Clear Filters
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
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
    
    .avatar-md {
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endpush
