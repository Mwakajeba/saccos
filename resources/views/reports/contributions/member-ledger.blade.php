@extends('layouts.main')

@section('title', 'Member Contribution Ledger Report')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-file'],
                ['label' => 'Contribution Reports', 'url' => route('reports.contributions'), 'icon' => 'bx bx-bar-chart-square'],
                ['label' => 'Member Ledger', 'url' => '#', 'icon' => 'bx bx-book']
            ]" />
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="mb-0 text-uppercase">MEMBER CONTRIBUTION LEDGER REPORT</h6>
                    <p class="text-muted mb-0">View detailed transaction history for a member's contribution account</p>
                </div>
            </div>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bx bx-filter me-2"></i>Select Member & Account
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('reports.contributions.member-ledger') }}">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label for="customer_id" class="form-label fw-bold">
                                            <i class="bx bx-user me-1"></i>Member
                                        </label>
                                        <select class="form-select" id="customer_id" name="customer_id" required>
                                            <option value="">Select Member</option>
                                            @foreach($customers as $customer)
                                                <option value="{{ $customer->id }}" {{ $customerId == $customer->id ? 'selected' : '' }}>
                                                    {{ $customer->name }} ({{ $customer->customerNo }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="account_id" class="form-label fw-bold">
                                            <i class="bx bx-wallet me-1"></i>Account
                                        </label>
                                        <select class="form-select" id="account_id" name="account_id" required>
                                            <option value="">Select Account</option>
                                            @foreach($accounts as $acc)
                                                <option value="{{ $acc->id }}" {{ $accountId == $acc->id ? 'selected' : '' }}>
                                                    {{ $acc->account_number }} - {{ $acc->contributionProduct->product_name ?? 'N/A' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="from_date" class="form-label fw-bold">
                                            <i class="bx bx-calendar me-1"></i>From Date
                                        </label>
                                        <input type="date" class="form-control" id="from_date" name="from_date" value="{{ $fromDate }}" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="to_date" class="form-label fw-bold">
                                            <i class="bx bx-calendar-check me-1"></i>To Date
                                        </label>
                                        <input type="date" class="form-control" id="to_date" name="to_date" value="{{ $toDate }}" required>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="bx bx-search me-1"></i> Generate
                                        </button>
                                    </div>
                                    @if($account)
                                    <div class="col-md-2 d-flex align-items-end">
                                        <div class="btn-group w-100" role="group">
                                            <a href="{{ route('reports.contributions.member-ledger', array_merge(request()->all(), ['export' => 'pdf'])) }}" class="btn btn-danger" target="_blank">
                                                <i class="bx bxs-file-pdf me-1"></i> PDF
                                            </a>
                                            <a href="{{ route('reports.contributions.member-ledger', array_merge(request()->all(), ['export' => 'excel'])) }}" class="btn btn-success">
                                                <i class="bx bxs-file-export me-1"></i> Excel
                                            </a>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            @if($account)
            <!-- Account Details -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Account Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Member:</strong> {{ $customer->name }}</p>
                                    <p><strong>Member No:</strong> {{ $customer->customerNo }}</p>
                                    <p><strong>Account No:</strong> {{ $account->account_number }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Product:</strong> {{ $account->contributionProduct->product_name ?? 'N/A' }}</p>
                                    <p><strong>Status:</strong> 
                                        @if($account->status === 'active')
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($account->status) }}</span>
                                        @endif
                                    </p>
                                    <p><strong>Current Balance:</strong> <span class="fw-bold text-success">{{ number_format($account->balance, 2) }}</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Transaction History</h5>
                            <p class="text-muted">Period: {{ date('d M Y', strtotime($fromDate)) }} to {{ date('d M Y', strtotime($toDate)) }}</p>
                            
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>Date</th>
                                            <th>Transaction Type</th>
                                            <th>Reference</th>
                                            <th class="text-end">Debit</th>
                                            <th class="text-end">Credit</th>
                                            <th class="text-end">Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="table-light fw-bold">
                                            <td colspan="5">Opening Balance</td>
                                            <td class="text-end">{{ number_format($openingBalance, 2) }}</td>
                                        </tr>
                                        @forelse($transactions as $transaction)
                                        <tr>
                                            <td>{{ date('d M Y', strtotime($transaction->transaction_date)) }}</td>
                                            <td>{{ ucfirst(str_replace('_', ' ', $transaction->transaction_type)) }}</td>
                                            <td>{{ $transaction->reference ?? '-' }}</td>
                                            <td class="text-end">{{ $transaction->transaction_type === 'withdrawal' ? number_format($transaction->amount, 2) : '-' }}</td>
                                            <td class="text-end">{{ $transaction->transaction_type === 'deposit' ? number_format($transaction->amount, 2) : '-' }}</td>
                                            <td class="text-end fw-bold">{{ number_format($transaction->running_balance, 2) }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No transactions found for the selected period</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
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
                            <i class="bx bx-search display-1 text-muted mb-3"></i>
                            <h5 class="text-muted">Select a member and account to view transactions</h5>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Load accounts when customer is selected
    $('#customer_id').change(function() {
        var customerId = $(this).val();
        if (customerId) {
            // Reload page with customer_id to load accounts
            window.location.href = '{{ route("reports.contributions.member-ledger") }}?customer_id=' + customerId;
        }
    });
});
</script>
@endpush
