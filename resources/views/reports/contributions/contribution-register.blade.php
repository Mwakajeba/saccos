@extends('layouts.main')

@section('title', 'Contribution Register Report')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-file'],
                ['label' => 'Contribution Reports', 'url' => route('reports.contributions'), 'icon' => 'bx bx-bar-chart-square'],
                ['label' => 'Contribution Register', 'url' => '#', 'icon' => 'bx bx-list-ul']
            ]" />
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="mb-0 text-uppercase">CONTRIBUTION REGISTER REPORT</h6>
                    <p class="text-muted mb-0">View all contribution accounts with member details and contribution balances</p>
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
                            <form method="GET" action="{{ route('reports.contributions.contribution-register') }}" id="filterForm">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label for="contribution_product_id" class="form-label fw-bold">
                                            <i class="bx bx-package me-1"></i>Contribution Product
                                        </label>
                                        <select class="form-select" id="contribution_product_id" name="contribution_product_id">
                                            <option value="">All Products</option>
                                            @foreach($contributionProducts as $product)
                                                <option value="{{ $product->id }}" {{ $contributionProductId == $product->id ? 'selected' : '' }}>
                                                    {{ $product->product_name }}
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
                                            <a href="{{ route('reports.contributions.contribution-register') }}" class="btn btn-secondary">
                                                <i class="bx bx-refresh me-1"></i> Clear
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end">
                                        <div class="btn-group w-100" role="group">
                                            <a href="{{ route('reports.contributions.contribution-register', array_merge(request()->all(), ['export' => 'pdf'])) }}" class="btn btn-danger" target="_blank">
                                                <i class="bx bxs-file-pdf me-1"></i> PDF
                                            </a>
                                            <a href="{{ route('reports.contributions.contribution-register', array_merge(request()->all(), ['export' => 'excel'])) }}" class="btn btn-success">
                                                <i class="bx bxs-file-export me-1"></i> Excel
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
                                        <i class="bx bx-dollar fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-muted mb-1">Total Balance</p>
                                    <h4 class="mb-0 text-success">{{ number_format($accounts->sum('balance'), 2) }}</h4>
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
                <div class="col-md-3">
                    <div class="card border-info">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-md rounded-circle bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <i class="bx bx-trending-up fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-muted mb-1">Average Balance</p>
                                    <h4 class="mb-0 text-info">{{ $accounts->count() > 0 ? number_format($accounts->avg('balance'), 2) : '0.00' }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Table -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bx bx-list-ul me-2"></i>Contribution Register
                                <span class="badge bg-primary ms-2">{{ $accounts->count() }} {{ Str::plural('account', $accounts->count()) }}</span>
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover align-middle">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>#</th>
                                            <th>Account No</th>
                                            <th>Member Name</th>
                                            <th>Member No</th>
                                            <th>Product</th>
                                            <th>Branch</th>
                                            <th class="text-end">Balance</th>
                                            <th>Status</th>
                                            <th>Opened Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($accounts as $index => $account)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td><strong>{{ $account->account_number }}</strong></td>
                                            <td>{{ $account->customer->name ?? 'N/A' }}</td>
                                            <td>{{ $account->customer->customerNo ?? 'N/A' }}</td>
                                            <td>{{ $account->contributionProduct->product_name ?? 'N/A' }}</td>
                                            <td>{{ $account->branch->name ?? 'N/A' }}</td>
                                            <td class="text-end"><strong>{{ number_format($account->balance, 2) }}</strong></td>
                                            <td>
                                                @if($account->status === 'active')
                                                    <span class="badge bg-success">Active</span>
                                                @elseif($account->status === 'inactive')
                                                    <span class="badge bg-warning">Inactive</span>
                                                @else
                                                    <span class="badge bg-secondary">Closed</span>
                                                @endif
                                            </td>
                                            <td>{{ $account->opening_date ? date('d M Y', strtotime($account->opening_date)) : 'N/A' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light fw-bold">
                                        <tr>
                                            <td colspan="6" class="text-end">TOTAL:</td>
                                            <td class="text-end">{{ number_format($accounts->sum('balance'), 2) }}</td>
                                            <td colspan="2"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="bx bx-folder-open display-1 text-muted mb-3"></i>
                            <h5 class="text-muted">No contribution accounts found</h5>
                            <p class="text-muted">Try adjusting your filter criteria</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection
