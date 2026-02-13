@extends('layouts.main')

@section('title', 'Write-off Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Loans', 'url' => route('loans.index'), 'icon' => 'bx bx-credit-card'],
            ['label' => 'Write-off History', 'url' => route('loans.writeoffs.index'), 'icon' => 'bx bx-list-ul'],
            ['label' => 'Write-off #' . $writeoff->id, 'url' => '#', 'icon' => 'bx bx-detail'],
        ]" />
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h6 class="mb-0 text-uppercase">Write-off Details</h6>
            <div class="d-flex gap-2">
                @if($writeoff->writeoff_type === 'provision' && $writeoff->status === 'posted' && !$writeoff->reversed_by_id)
                    <a href="{{ route('loans.writeoffs.receipt.create', $writeoff) }}" class="btn btn-success"><i class="bx bx-receipt me-1"></i> Add receipt</a>
                @endif
                <a href="{{ route('loans.writeoffs.index') }}" class="btn btn-secondary"><i class="bx bx-arrow-back me-1"></i> Back</a>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bx bx-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title mb-4">Write-off Information</h6>
                        <div class="row mb-3">
                            <div class="col-md-6"><strong>Loan:</strong></div>
                            <div class="col-md-6">
                                @if($writeoff->loan)
                                <a href="{{ route('loans.show', \Vinkla\Hashids\Facades\Hashids::encode($writeoff->loan_id)) }}">
                                    {{ $writeoff->loan->loanNo ?? $writeoff->loan_id }}
                                </a>
                                @else
                                {{ $writeoff->loan_id }}
                                @endif
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6"><strong>Customer:</strong></div>
                            <div class="col-md-6">{{ optional($writeoff->customer)->name ?? 'N/A' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6"><strong>Amount:</strong></div>
                            <div class="col-md-6">TZS {{ number_format($writeoff->outstanding, 2) }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6"><strong>Type:</strong></div>
                            <div class="col-md-6">
                                <span class="badge {{ $writeoff->writeoff_type === 'direct' ? 'bg-danger' : 'bg-warning text-dark' }}">
                                    {{ ucfirst($writeoff->writeoff_type) }}
                                </span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6"><strong>Status:</strong></div>
                            <div class="col-md-6">
                                @if($writeoff->status === 'pending')
                                <span class="badge bg-warning text-dark">Pending Approval</span>
                                @elseif($writeoff->status === 'rejected')
                                <span class="badge bg-secondary">Rejected</span>
                                @elseif($writeoff->status === 'reversal')
                                <span class="badge bg-info">Reversal</span>
                                @elseif($writeoff->isReversed())
                                <span class="badge bg-secondary">Reversed</span>
                                @else
                                <span class="badge bg-success">Posted</span>
                                @endif
                            </div>
                        </div>
                        @if($writeoff->isReversal() && $writeoff->reversalOf)
                        <div class="row mb-3">
                            <div class="col-md-6"><strong>Reversal of:</strong></div>
                            <div class="col-md-6">
                                <a href="{{ route('loans.writeoffs.show', $writeoff->reversalOf) }}">Write-off #{{ $writeoff->reversal_of_id }}</a>
                            </div>
                        </div>
                        @endif
                        @if($writeoff->isReversed() && $writeoff->reversedBy)
                        <div class="row mb-3">
                            <div class="col-md-6"><strong>Reversed by:</strong></div>
                            <div class="col-md-6">
                                <a href="{{ route('loans.writeoffs.show', $writeoff->reversedBy) }}">Write-off #{{ $writeoff->reversed_by_id }}</a>
                            </div>
                        </div>
                        @endif
                        <div class="row mb-3">
                            <div class="col-md-6"><strong>Write-off Date:</strong></div>
                            <div class="col-md-6">{{ ($writeoff->writeoff_date ?? $writeoff->created_at)->format('d M Y') }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6"><strong>Reason:</strong></div>
                            <div class="col-md-6">{{ $writeoff->reason }}</div>
                        </div>
                        @if($writeoff->policy_reference)
                        <div class="row mb-3">
                            <div class="col-md-6"><strong>Policy Reference:</strong></div>
                            <div class="col-md-6">{{ $writeoff->policy_reference }}</div>
                        </div>
                        @endif
                        @if($writeoff->external_reference)
                        <div class="row mb-3">
                            <div class="col-md-6"><strong>External Reference:</strong></div>
                            <div class="col-md-6">{{ $writeoff->external_reference }}</div>
                        </div>
                        @endif
                        @if($writeoff->document_path)
                        <div class="row mb-3">
                            <div class="col-md-6"><strong>Supporting Document:</strong></div>
                            <div class="col-md-6">
                                <a href="{{ asset('storage/' . $writeoff->document_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bx bx-file me-1"></i> View Document
                                </a>
                            </div>
                        </div>
                        @endif
                        <div class="row mb-3">
                            <div class="col-md-6"><strong>Submitted By:</strong></div>
                            <div class="col-md-6">{{ optional($writeoff->createdBy)->name ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>

                @if($writeoff->approvals->count() > 0)
                <div class="card mt-4">
                    <div class="card-header"><h6 class="mb-0">Approval History</h6></div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <thead><tr><th>Level</th><th>Approver</th><th>Status</th><th>Date</th><th>Notes</th></tr></thead>
                            <tbody>
                                @foreach($writeoff->approvals->sortBy('approval_level') as $approval)
                                <tr>
                                    <td>{{ $approval->approval_level }}</td>
                                    <td>{{ $approval->approver_name }}</td>
                                    <td>
                                        @if($approval->status === 'approved')
                                        <span class="badge bg-success">Approved</span>
                                        @elseif($approval->status === 'rejected')
                                        <span class="badge bg-secondary">Rejected</span>
                                        @else
                                        <span class="badge bg-warning text-dark">Pending</span>
                                        @endif
                                    </td>
                                    <td>{{ $approval->approved_at?->format('d M Y H:i') ?? $approval->rejected_at?->format('d M Y H:i') ?? '-' }}</td>
                                    <td>{{ Str::limit($approval->notes, 40) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>

            @if($writeoff->isReversible() && auth()->user()->can('write off loan'))
            <div class="col-lg-4">
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white"><h6 class="mb-0">Reverse Write-off</h6></div>
                    <div class="card-body">
                        <p class="text-muted small">Reverse this write-off to restore the loan. This will create reversing GL entries and restore the loan status to {{ $writeoff->previous_loan_status ?? 'active' }}.</p>
                        <form action="{{ route('loans.writeoffs.reverse', $writeoff) }}" method="POST" onsubmit="return confirm('Are you sure you want to reverse this write-off? This action cannot be undone.');">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Reason for Reversal <span class="text-danger">*</span></label>
                                <input type="text" name="reason" class="form-control" required maxlength="255" placeholder="e.g. Posted in error">
                            </div>
                            <button type="submit" class="btn btn-danger w-100"><i class="bx bx-undo me-1"></i> Reverse Write-off</button>
                        </form>
                    </div>
                </div>
            </div>
            @elseif($writeoff->status === 'pending' && $canApprove && auth()->user()->can('write off loan'))
            <div class="col-lg-4">
                <div class="card border-warning">
                    <div class="card-header bg-warning text-dark">{{ $currentApproval->approval_level === 1 ? 'Approve' : 'Approve ' . $currentApproval->approval_level }}</div>
                    <div class="card-body">
                        <p class="text-muted small">You are approving this write-off at {{ $currentApproval->approval_level === 1 ? 'Level 1' : 'Level ' . $currentApproval->approval_level }}.</p>
                        <form action="{{ route('loans.writeoffs.approve', $writeoff) }}" method="POST" class="mb-3">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Notes (optional)</label>
                                <textarea name="notes" class="form-control" rows="2"></textarea>
                            </div>
                            <button type="submit" class="btn btn-success w-100"><i class="bx bx-check me-1"></i> Approve</button>
                        </form>
                        <form action="{{ route('loans.writeoffs.reject', $writeoff) }}" method="POST" onsubmit="return confirm('Are you sure you want to reject this write-off?');">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Rejection Reason (required)</label>
                                <textarea name="notes" class="form-control" rows="2" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-outline-danger w-100"><i class="bx bx-x me-1"></i> Reject</button>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
