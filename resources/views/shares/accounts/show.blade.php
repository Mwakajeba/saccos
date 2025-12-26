@extends('layouts.main')

@section('title', 'Share Account Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Shares Management', 'url' => route('shares.management'), 'icon' => 'bx bx-bar-chart-square'],
            ['label' => 'Share Accounts', 'url' => route('shares.accounts.index'), 'icon' => 'bx bx-wallet'],
            ['label' => 'Account Details', 'url' => '#', 'icon' => 'bx bx-info-circle']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h6 class="mb-0 text-uppercase">SHARE ACCOUNT DETAILS</h6>
            <div class="d-flex gap-2">
                <a href="{{ route('shares.accounts.edit', Vinkla\Hashids\Facades\Hashids::encode($shareAccount->id)) }}" class="btn btn-primary">
                    <i class="bx bx-edit me-1"></i> Edit
                </a>
                <a href="{{ route('shares.accounts.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Back to List
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bx bx-wallet me-2"></i>Account Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Account Number:</strong></div>
                            <div class="col-sm-8">{{ $shareAccount->account_number }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Member Name:</strong></div>
                            <div class="col-sm-8">{{ $shareAccount->customer->name ?? 'N/A' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Member Number:</strong></div>
                            <div class="col-sm-8">{{ $shareAccount->customer->customerNo ?? 'N/A' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Share Product:</strong></div>
                            <div class="col-sm-8">{{ $shareAccount->shareProduct->share_name ?? 'N/A' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Share Balance:</strong></div>
                            <div class="col-sm-8">{{ number_format($shareAccount->share_balance, 2) }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Nominal Value:</strong></div>
                            <div class="col-sm-8">{{ number_format($shareAccount->nominal_value, 2) }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Opening Date:</strong></div>
                            <div class="col-sm-8">{{ $shareAccount->opening_date ? $shareAccount->opening_date->format('Y-m-d') : 'N/A' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Last Transaction Date:</strong></div>
                            <div class="col-sm-8">{{ $shareAccount->last_transaction_date ? $shareAccount->last_transaction_date->format('Y-m-d') : 'N/A' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Status:</strong></div>
                            <div class="col-sm-8">
                                @if($shareAccount->status == 'active')
                                    <span class="badge bg-success">Active</span>
                                @elseif($shareAccount->status == 'inactive')
                                    <span class="badge bg-warning">Inactive</span>
                                @else
                                    <span class="badge bg-danger">Closed</span>
                                @endif
                            </div>
                        </div>
                        @if($shareAccount->notes)
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Notes:</strong></div>
                            <div class="col-sm-8">{{ $shareAccount->notes }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

