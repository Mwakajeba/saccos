@extends('layouts.main')

@section('title', 'Add Recovery Receipt')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Loans', 'url' => route('loans.index'), 'icon' => 'bx bx-credit-card'],
            ['label' => 'Write-off History', 'url' => route('loans.writeoffs.index'), 'icon' => 'bx bx-list-ul'],
            ['label' => 'Write-off #' . $writeoff->id, 'url' => route('loans.writeoffs.show', $writeoff), 'icon' => 'bx bx-detail'],
            ['label' => 'Add Receipt', 'url' => '#', 'icon' => 'bx bx-receipt'],
        ]" />
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h6 class="mb-0 text-uppercase">Add Recovery Receipt</h6>
            <a href="{{ route('loans.writeoffs.show', $writeoff) }}" class="btn btn-secondary"><i class="bx bx-arrow-back me-1"></i> Back</a>
        </div>

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Write-off Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1 text-muted small">Loan</p>
                        <p class="mb-2 fw-bold">
                            @if($writeoff->loan)
                                <a href="{{ route('loans.show', \Vinkla\Hashids\Facades\Hashids::encode($writeoff->loan_id)) }}">
                                    {{ $writeoff->loan->loanNo ?? $writeoff->loan_id }}
                                </a>
                            @else
                                {{ $writeoff->loan_id }}
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1 text-muted small">Customer</p>
                        <p class="mb-2 fw-bold">{{ optional($writeoff->customer)->name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1 text-muted small">Written-off Amount</p>
                        <p class="mb-2 fw-bold">TZS {{ number_format($writeoff->outstanding, 2) }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1 text-muted small">Type</p>
                        <p class="mb-2"><span class="badge bg-warning text-dark">{{ ucfirst($writeoff->writeoff_type) }}</span></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h6 class="card-title mb-4"><i class="bx bx-receipt me-2"></i>Receipt Details</h6>
                <p class="text-muted small mb-4">Record a recovery payment. Amount will be debited to the selected bank and credited to the loan provision income account (from loan product).</p>

                <form method="POST" action="{{ route('loans.writeoffs.receipt.store', $writeoff) }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" name="payment_date" id="payment_date" class="form-control @error('payment_date') is-invalid @enderror"
                                    value="{{ old('payment_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
                                @error('payment_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount (TZS) <span class="text-danger">*</span></label>
                                <input type="number" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror"
                                    step="0.01" min="0.01" value="{{ old('amount') }}" required>
                                @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">Written-off amount: TZS {{ number_format($writeoff->outstanding, 2) }}</small>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="bank_account_id" class="form-label">Bank Account <span class="text-danger">*</span></label>
                                <select name="bank_account_id" id="bank_account_id" class="form-select @error('bank_account_id') is-invalid @enderror" required>
                                    <option value="">-- Select Bank Account --</option>
                                    @foreach($bankAccounts as $bankAccount)
                                        <option value="{{ $bankAccount->id }}" {{ old('bank_account_id') == $bankAccount->id ? 'selected' : '' }}>
                                            {{ $bankAccount->name }} - {{ $bankAccount->account_number }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('bank_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="description" class="form-label">Description <span class="text-muted">(optional)</span></label>
                                <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="2"
                                    placeholder="e.g. Partial recovery from customer">{{ old('description') }}</textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('loans.writeoffs.show', $writeoff) }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-success"><i class="bx bx-check me-1"></i>Record Receipt</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
