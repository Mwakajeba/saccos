@extends('layouts.main')

@section('title', 'Interest on Saving Details')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Contributions', 'url' => route('contributions.index'), 'icon' => 'bx bx-donate-heart'],
            ['label' => 'Interest on Saving', 'url' => route('contributions.interest-on-saving.index'), 'icon' => 'bx bx-calculator'],
            ['label' => 'Details', 'url' => '#', 'icon' => 'bx bx-show']
        ]" />

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 text-uppercase">INTEREST ON SAVING DETAILS - {{ $calculationDate }}</h6>
                <a href="{{ route('contributions.interest-on-saving.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back"></i> Back to Summary
                </a>
            </div>
            <hr />

            <!-- Summary Card -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card border-info">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="bx bx-stats"></i> Summary for
                                {{ $summary->calculation_date->format('l, F d, Y') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h4 class="text-primary">{{ number_format($summary->total_accounts) }}</h4>
                                        <p class="text-muted mb-0">Total Accounts</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h4 class="text-success">{{ number_format($summary->total_customers) }}</h4>
                                        <p class="text-muted mb-0">Total Customers</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h4 class="text-info">{{ number_format($summary->total_interest_amount, 2) }}</h4>
                                        <p class="text-muted mb-0">Total Interest</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h4 class="text-warning">{{ number_format($summary->total_net_amount, 2) }}</h4>
                                        <p class="text-muted mb-0">Total Net Amount</p>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Total Balance:</strong> {{ number_format($summary->total_balance, 2) }}
                                </div>
                                <div class="col-md-4">
                                    <strong>Total Withholding:</strong>
                                    {{ number_format($summary->total_withholding_amount, 2) }}
                                </div>
                                <div class="col-md-4">
                                    <strong>Processed:</strong>
                                    <span class="badge bg-success">{{ $summary->processed_count }}</span>
                                    <strong class="ms-2">Skipped:</strong>
                                    <span class="badge bg-secondary">{{ $summary->skipped_count }}</span>
                                    @if($summary->error_count > 0)
                                        <strong class="ms-2">Errors:</strong>
                                        <span class="badge bg-danger">{{ $summary->error_count }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Details Table -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bx bx-list-ul"></i> Customer Details ({{ $interestRecords->count() }}
                        records)</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Customer Name</th>
                                    <th>Customer Number</th>
                                    <th>Account Number</th>
                                    <th>Product Name</th>
                                    <th class="text-end">Balance</th>
                                    <th class="text-end">Interest Rate</th>
                                    <th class="text-end">Interest Amount</th>
                                    <th class="text-end">Withholding</th>
                                    <th class="text-end">Net Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($interestRecords as $index => $record)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $record->customer->name ?? 'N/A' }}</td>
                                        <td>{{ $record->customer->customerNo ?? 'N/A' }}</td>
                                        <td>{{ $record->contributionAccount->account_number ?? 'N/A' }}</td>
                                        <td>{{ $record->contributionProduct->product_name ?? 'N/A' }}</td>
                                        <td class="text-end">
                                            {{ number_format($record->account_balance_at_interest_calculation ?? 0, 2) }}</td>
                                        <td class="text-end">{{ number_format($record->interest_rate ?? 0, 2) }}%</td>
                                        <td class="text-end">{{ number_format($record->interest_amount_gained ?? 0, 2) }}</td>
                                        <td class="text-end">{{ number_format($record->withholding_amount ?? 0, 2) }}</td>
                                        <td class="text-end"><strong>{{ number_format($record->net_amount ?? 0, 2) }}</strong>
                                        </td>
                                        <td>
                                            @if($record->posted)
                                                <span class="badge bg-success">Posted</span>
                                            @else
                                                <span class="badge bg-warning"
                                                    title="{{ $record->reason ?? 'Waiting for approval' }}">
                                                    Pending
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center">No records found for this date.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="table-info">
                                    <th colspan="5" class="text-end">TOTALS:</th>
                                    <th class="text-end">
                                        {{ number_format($interestRecords->sum('account_balance_at_interest_calculation'), 2) }}
                                    </th>
                                    <th class="text-end">-</th>
                                    <th class="text-end">
                                        {{ number_format($interestRecords->sum('interest_amount_gained'), 2) }}</th>
                                    <th class="text-end">{{ number_format($interestRecords->sum('withholding_amount'), 2) }}
                                    </th>
                                    <th class="text-end">
                                        <strong>{{ number_format($interestRecords->sum('net_amount'), 2) }}</strong></th>
                                    <th>-</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .table tfoot th {
            background-color: #d1ecf1;
        }
    </style>
@endpush