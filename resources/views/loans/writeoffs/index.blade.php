@extends('layouts.main')

@section('title', 'Loan Write-offs')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Loans', 'url' => route('loans.index'), 'icon' => 'bx bx-credit-card'],
            ['label' => 'Written Off Loans', 'url' => route('loans.writtenoff'), 'icon' => 'bx bx-x-circle'],
            ['label' => 'Write-off History', 'url' => '#', 'icon' => 'bx bx-list-ul'],
        ]" />
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h6 class="mb-0 text-uppercase">Loan Write-off History</h6>
                <p class="mb-0 text-muted">View all loan write-off transactions</p>
            </div>
            <a href="{{ route('loans.writtenoff') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back to Written Off Loans
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bx bx-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card border-danger">
                    <div class="card-body text-center">
                        <i class="bx bx-x-circle fs-1 text-danger mb-2"></i>
                        <h4 class="mb-1">{{ $writeoffs->total() }}</h4>
                        <p class="text-muted mb-0">Total Write-offs</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card border-dark">
                    <div class="card-body text-center">
                        <i class="bx bx-money fs-1 text-dark mb-2"></i>
                        <h4 class="mb-1">TZS {{ number_format($totalAmount, 2) }}</h4>
                        <p class="text-muted mb-0">Total Amount Written Off</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Loan No</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Reason</th>
                                <th>Recorded By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($writeoffs as $writeoff)
                                <tr>
                                    <td>{{ ($writeoff->writeoff_date ?? $writeoff->created_at)->format('d M Y') }}</td>
                                    <td>
                                        @if($writeoff->loan)
                                            <a href="{{ route('loans.show', \Vinkla\Hashids\Facades\Hashids::encode($writeoff->loan_id)) }}">
                                                {{ $writeoff->loan->loanNo ?? $writeoff->loan_id }}
                                            </a>
                                        @else
                                            {{ $writeoff->loan_id }}
                                        @endif
                                    </td>
                                    <td>{{ optional($writeoff->customer)->name ?? 'N/A' }}</td>
                                    <td>TZS {{ number_format($writeoff->outstanding, 2) }}</td>
                                    <td>
                                        <span class="badge {{ $writeoff->writeoff_type === 'direct' ? 'bg-danger' : 'bg-warning text-dark' }}">
                                            {{ ucfirst($writeoff->writeoff_type) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($writeoff->status === 'pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @elseif($writeoff->status === 'rejected')
                                            <span class="badge bg-secondary">Rejected</span>
                                        @elseif($writeoff->status === 'reversal')
                                            <span class="badge bg-info">Reversal</span>
                                        @elseif($writeoff->reversed_by_id)
                                            <span class="badge bg-secondary">Reversed</span>
                                        @else
                                            <span class="badge bg-success">Posted</span>
                                        @endif
                                    </td>
                                    <td>{{ Str::limit($writeoff->reason, 50) }}</td>
                                    <td>{{ optional($writeoff->createdBy)->name ?? 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('loans.writeoffs.show', $writeoff) }}" class="btn btn-sm btn-outline-primary">View</a>
                                        @if($writeoff->writeoff_type === 'provision' && $writeoff->status === 'posted' && !$writeoff->reversed_by_id)
                                            <a href="{{ route('loans.writeoffs.receipt.create', $writeoff) }}" class="btn btn-sm btn-outline-success" title="Add recovery receipt">
                                                <i class="bx bx-receipt"></i> Add receipt
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">No write-off transactions found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $writeoffs->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
