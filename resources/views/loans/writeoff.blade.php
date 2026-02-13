@php
    $outstandingAmount = $loan->getTotalOutstandingAmount();
@endphp

@extends('layouts.main')
@section('title', 'Write Off Loan')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Loans', 'url' => route('loans.list'), 'icon' => 'bx bx-money'],
            ['label' => 'Write Off Loan', 'url' => '#', 'icon' => 'bx bx-block']
        ]" />
        <h6 class="mb-0 text-uppercase">WRITE OFF LOAN</h6>
        <hr/>
        <div class="card border-danger shadow">
            <div class="card-body">
                @if ($outstandingAmount <= 0)
                    <div class="alert alert-info mb-0">
                        <i class="bx bx-info-circle me-2"></i>
                        This loan has no outstanding balance and cannot be written off.
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('loans.show', $hashid) }}" class="btn btn-secondary"><i class="bx bx-arrow-back"></i> Back to Loan</a>
                    </div>
                @else
                <form id="writeoff-form" method="POST" action="{{ route('loans.writeoff.confirm', $hashid) }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="outstanding" value="{{ $outstandingAmount }}">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Loan ID</label>
                            <div class="form-control bg-light">{{ $loan->loanNo ?? $loan->id }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Borrower</label>
                            <div class="form-control bg-light">{{ $loan->customer->name ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Outstanding Amount</label>
                            <div class="form-control bg-light">TZS {{ number_format($outstandingAmount, 2) }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Status</label>
                            <div class="form-control bg-light">{{ $loan->status }}</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="writeoff_date" class="form-label fw-bold">Write-Off Date <span class="text-danger">*</span></label>
                        <input type="date" name="writeoff_date" id="writeoff_date" class="form-control @error('writeoff_date') is-invalid @enderror"
                            value="{{ old('writeoff_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
                        @error('writeoff_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="form-text text-muted">The date to use for GL posting and reporting. Cannot be a future date.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Write-Off Type</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="writeoff_type" id="direct_writeoff" value="direct" checked>
                            <label class="form-check-label" for="direct_writeoff">Direct Write Off</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="writeoff_type" id="provision_writeoff" value="provision">
                            <label class="form-check-label" for="provision_writeoff">Using Provision</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label fw-bold">Reason for Write-Off</label>
                        <textarea name="reason" id="reason" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="policy_reference" class="form-label fw-bold">Policy Reference <span class="text-muted">(optional)</span></label>
                            <input type="text" name="policy_reference" id="policy_reference" class="form-control @error('policy_reference') is-invalid @enderror"
                                value="{{ old('policy_reference') }}" placeholder="e.g. Board resolution no., policy section">
                            @error('policy_reference') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="external_reference" class="form-label fw-bold">External Reference <span class="text-muted">(optional)</span></label>
                            <input type="text" name="external_reference" id="external_reference" class="form-control @error('external_reference') is-invalid @enderror"
                                value="{{ old('external_reference') }}" placeholder="e.g. Case no., court reference">
                            @error('external_reference') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="document" class="form-label fw-bold">Supporting Document <span class="text-muted">(optional)</span></label>
                        <input type="file" name="document" id="document" class="form-control @error('document') is-invalid @enderror"
                            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        @error('document') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="form-text text-muted">e.g. board resolution, death certificate, bankruptcy order. Max 10MB. PDF, JPG, PNG, DOC, DOCX.</small>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-danger"><i class="bx bx-check"></i> Confirm Write Off</button>
                        <a href="{{ route('loans.show', $hashid) }}" class="btn btn-secondary"><i class="bx bx-arrow-back"></i> Cancel</a>
                    </div>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('writeoff-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const outstanding = form.querySelector('input[name="outstanding"]')?.value || '0';
            const amount = parseFloat(outstanding).toLocaleString('en-US', { minimumFractionDigits: 2 });

            Swal.fire({
                title: 'Confirm Write-Off',
                html: `Are you sure you want to write off this loan?<br><br><strong>TZS ${amount}</strong> will be written off. This will mark the loan as written off and post GL entries.<br><br>This action requires approval before it is finalized.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Submit Write-Off',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    }
});
</script>
@endpush
