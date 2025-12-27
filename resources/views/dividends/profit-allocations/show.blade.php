@php
    use Vinkla\Hashids\Facades\Hashids;
@endphp

@extends('layouts.main')

@section('title', 'Profit Allocation Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Shares Management', 'url' => route('shares.management'), 'icon' => 'bx bx-bar-chart-square'],
                ['label' => 'Profit Allocations', 'url' => route('dividends.profit-allocations'), 'icon' => 'bx bx-bar-chart'],
                ['label' => 'Allocation Details', 'url' => '#', 'icon' => 'bx bx-info-circle']
            ]" />
            <div class="d-flex gap-2">
                <a href="{{ route('dividends.profit-allocations') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Back to List
                </a>
            </div>
        </div>

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

        <!-- Allocation Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h3 class="text-primary mb-1">Profit Allocation</h3>
                                <p class="text-muted mb-0">
                                    <strong>Reference:</strong> {{ $profitAllocation->reference_number ?? 'N/A' }} | 
                                    <strong>Financial Year:</strong> {{ $profitAllocation->financial_year }} |
                                    <strong>Date:</strong> {{ $profitAllocation->allocation_date->format('M d, Y') }}
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                @if($profitAllocation->status == 'approved')
                                    <span class="badge bg-success fs-6">Approved</span>
                                @elseif($profitAllocation->status == 'posted')
                                    <span class="badge bg-primary fs-6">Posted</span>
                                @else
                                    <span class="badge bg-secondary fs-6">{{ ucfirst($profitAllocation->status) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bx bx-dollar-circle text-primary" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="card-title text-muted mb-1">Total Profit</h5>
                        <h4 class="text-primary mb-0">{{ number_format($profitAllocation->total_profit, 2) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bx bx-shield text-success" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="card-title text-muted mb-1">Statutory Reserve</h5>
                        <h4 class="text-success mb-0">{{ number_format($profitAllocation->statutory_reserve_amount, 2) }}</h4>
                        <small class="text-muted">({{ number_format($profitAllocation->statutory_reserve_percentage, 2) }}%)</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bx bx-graduation text-info" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="card-title text-muted mb-1">Education Fund</h5>
                        <h4 class="text-info mb-0">{{ number_format($profitAllocation->education_fund_amount, 2) }}</h4>
                        <small class="text-muted">({{ number_format($profitAllocation->education_fund_percentage, 2) }}%)</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bx bx-money text-warning" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="card-title text-muted mb-1">Dividend Amount</h5>
                        <h4 class="text-warning mb-0">{{ number_format($profitAllocation->dividend_amount, 2) }}</h4>
                        <small class="text-muted">({{ number_format($profitAllocation->dividend_percentage, 2) }}%)</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Allocation Details -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bx bx-bar-chart me-2"></i>ALLOCATION BREAKDOWN</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Allocation Type</th>
                                        <th class="text-end">Percentage</th>
                                        <th class="text-end">Amount</th>
                                        <th>Account</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Statutory Reserve</strong></td>
                                        <td class="text-end">{{ number_format($profitAllocation->statutory_reserve_percentage, 2) }}%</td>
                                        <td class="text-end">{{ number_format($profitAllocation->statutory_reserve_amount, 2) }}</td>
                                        <td>
                                            @if($profitAllocation->statutoryReserveAccount)
                                                {{ $profitAllocation->statutoryReserveAccount->account_name }} ({{ $profitAllocation->statutoryReserveAccount->account_code }})
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @if($profitAllocation->education_fund_amount > 0)
                                    <tr>
                                        <td><strong>Education Fund</strong></td>
                                        <td class="text-end">{{ number_format($profitAllocation->education_fund_percentage, 2) }}%</td>
                                        <td class="text-end">{{ number_format($profitAllocation->education_fund_amount, 2) }}</td>
                                        <td>
                                            @if($profitAllocation->educationFundAccount)
                                                {{ $profitAllocation->educationFundAccount->account_name }} ({{ $profitAllocation->educationFundAccount->account_code }})
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endif
                                    @if($profitAllocation->community_fund_amount > 0)
                                    <tr>
                                        <td><strong>Community Fund</strong></td>
                                        <td class="text-end">{{ number_format($profitAllocation->community_fund_percentage, 2) }}%</td>
                                        <td class="text-end">{{ number_format($profitAllocation->community_fund_amount, 2) }}</td>
                                        <td>
                                            @if($profitAllocation->communityFundAccount)
                                                {{ $profitAllocation->communityFundAccount->account_name }} ({{ $profitAllocation->communityFundAccount->account_code }})
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endif
                                    <tr class="table-warning">
                                        <td><strong>Dividend to Members</strong></td>
                                        <td class="text-end"><strong>{{ number_format($profitAllocation->dividend_percentage, 2) }}%</strong></td>
                                        <td class="text-end"><strong>{{ number_format($profitAllocation->dividend_amount, 2) }}</strong></td>
                                        <td>
                                            @if($profitAllocation->dividendPayableAccount)
                                                {{ $profitAllocation->dividendPayableAccount->account_name }} ({{ $profitAllocation->dividendPayableAccount->account_code }})
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @if($profitAllocation->other_allocation_amount > 0)
                                    <tr>
                                        <td><strong>Other Allocation</strong> 
                                            @if($profitAllocation->other_allocation_description)
                                                <br><small class="text-muted">({{ $profitAllocation->other_allocation_description }})</small>
                                            @endif
                                        </td>
                                        <td class="text-end">{{ number_format($profitAllocation->other_allocation_percentage, 2) }}%</td>
                                        <td class="text-end">{{ number_format($profitAllocation->other_allocation_amount, 2) }}</td>
                                        <td>
                                            @if($profitAllocation->otherAllocationAccount)
                                                {{ $profitAllocation->otherAllocationAccount->account_name }} ({{ $profitAllocation->otherAllocationAccount->account_code }})
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endif
                                    <tr class="table-primary">
                                        <td><strong>TOTAL</strong></td>
                                        <td class="text-end">
                                            <strong>
                                                {{ number_format(
                                                    $profitAllocation->statutory_reserve_percentage + 
                                                    $profitAllocation->education_fund_percentage + 
                                                    $profitAllocation->community_fund_percentage + 
                                                    $profitAllocation->dividend_percentage + 
                                                    ($profitAllocation->other_allocation_percentage ?? 0), 
                                                    2
                                                ) }}%
                                            </strong>
                                        </td>
                                        <td class="text-end"><strong>{{ number_format($profitAllocation->total_profit, 2) }}</strong></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>ADDITIONAL INFORMATION</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <th width="40%">Branch:</th>
                                <td>{{ $profitAllocation->branch->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Company:</th>
                                <td>{{ $profitAllocation->company->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Created By:</th>
                                <td>{{ $profitAllocation->creator->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Created At:</th>
                                <td>{{ $profitAllocation->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                            @if($profitAllocation->updated_at != $profitAllocation->created_at)
                            <tr>
                                <th>Last Updated:</th>
                                <td>{{ $profitAllocation->updated_at->format('M d, Y H:i') }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bx bx-note me-2"></i>NOTES</h6>
                    </div>
                    <div class="card-body">
                        @if($profitAllocation->notes)
                            <p class="mb-0">{{ $profitAllocation->notes }}</p>
                        @else
                            <p class="text-muted mb-0">No notes available</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Associated Dividends -->
        @if($profitAllocation->dividends->count() > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bx bx-dollar me-2"></i>ASSOCIATED DIVIDENDS</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Dividend Number</th>
                                        <th>Share Product</th>
                                        <th>Declaration Date</th>
                                        <th class="text-end">Total Amount</th>
                                        <th>Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($profitAllocation->dividends as $dividend)
                                    <tr>
                                        <td>{{ $dividend->dividend_number ?? 'N/A' }}</td>
                                        <td>{{ $dividend->shareProduct->share_name ?? 'N/A' }}</td>
                                        <td>{{ $dividend->declaration_date ? $dividend->declaration_date->format('M d, Y') : 'N/A' }}</td>
                                        <td class="text-end">{{ number_format($dividend->total_dividend_amount, 2) }}</td>
                                        <td>
                                            @if($dividend->status == 'paid')
                                                <span class="badge bg-success">Paid</span>
                                            @elseif($dividend->status == 'approved')
                                                <span class="badge bg-primary">Approved</span>
                                            @elseif($dividend->status == 'calculated')
                                                <span class="badge bg-info">Calculated</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($dividend->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('dividends.dividends.show', Hashids::encode($dividend->id)) }}" class="btn btn-sm btn-info" title="View">
                                                <i class="bx bx-show"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Declare Dividend Button -->
        @if($profitAllocation->status == 'approved' && $profitAllocation->dividend_amount > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm border-warning">
                    <div class="card-body text-center">
                        <h5 class="mb-3">Declare Dividend from This Allocation</h5>
                        <p class="text-muted mb-3">You can declare dividends for share products using the allocated dividend amount.</p>
                        <a href="{{ route('dividends.dividends.create') }}?profit_allocation_id={{ $profitAllocation->id }}" class="btn btn-primary">
                            <i class="bx bx-plus-circle me-1"></i> Declare Dividend
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

