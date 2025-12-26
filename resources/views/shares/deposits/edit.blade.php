@extends('layouts.main')

@section('title', 'Edit Share Deposit')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Shares Management', 'url' => route('shares.management'), 'icon' => 'bx bx-bar-chart-square'],
            ['label' => 'Share Deposits', 'url' => route('shares.deposits.index'), 'icon' => 'bx bx-right-arrow-alt'],
            ['label' => 'Edit', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase text-warning">EDIT SHARE DEPOSIT</h6>
            <a href="{{ route('shares.deposits.index') }}" class="btn btn-success">
                <i class="bx bx-list-ul me-1"></i> Share Deposits List
            </a>
        </div>
        <hr />

        <div class="row">
            <!-- Left Column - Form -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bx bx-error-circle me-2"></i>
                                Please fix the following errors:
                                <ul class="mb-0 mt-2">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{ route('shares.deposits.update', Vinkla\Hashids\Facades\Hashids::encode($deposit->id)) }}" method="POST" id="shareDepositForm">
                            @csrf
                            @method('PUT')

                            <!-- Share Account -->
                            <div class="row mb-3">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Share Account <span class="text-danger">*</span></label>
                                    <select name="share_account_id" id="share_account_id" 
                                            class="form-select select2-single @error('share_account_id') is-invalid @enderror" required>
                                        <option value="">Select share account</option>
                                        @foreach($shareAccounts as $account)
                                            <option value="{{ $account->id }}" 
                                                {{ old('share_account_id', $deposit->share_account_id) == $account->id ? 'selected' : '' }}>
                                                {{ $account->account_number }} - {{ $account->customer->name ?? 'N/A' }} ({{ $account->shareProduct->share_name ?? 'N/A' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('share_account_id') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>
                            </div>

                            <!-- Deposit Date -->
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Deposit Date <span class="text-danger">*</span></label>
                                    <input type="date" name="deposit_date" 
                                           class="form-control @error('deposit_date') is-invalid @enderror"
                                           value="{{ old('deposit_date', $deposit->deposit_date ? $deposit->deposit_date->format('Y-m-d') : '') }}" required>
                                    @error('deposit_date') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="pending" {{ old('status', $deposit->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="approved" {{ old('status', $deposit->status) == 'approved' ? 'selected' : '' }}>Approved</option>
                                        <option value="rejected" {{ old('status', $deposit->status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    </select>
                                    @error('status') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>
                            </div>

                            <!-- Deposit Amount and Number of Shares -->
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Deposit Amount <span class="text-danger">*</span></label>
                                    <input type="number" name="deposit_amount" id="deposit_amount" step="0.01" min="0.01"
                                           class="form-control @error('deposit_amount') is-invalid @enderror"
                                           value="{{ old('deposit_amount', $deposit->deposit_amount) }}" required>
                                    @error('deposit_amount') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Number of Shares <span class="text-danger">*</span></label>
                                    <input type="number" name="number_of_shares" id="number_of_shares" step="0.0001" min="0.0001"
                                           class="form-control @error('number_of_shares') is-invalid @enderror"
                                           value="{{ old('number_of_shares', $deposit->number_of_shares) }}" required>
                                    @error('number_of_shares') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>
                            </div>

                            <!-- Bank Account -->
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Bank Account <span class="text-danger">*</span></label>
                                    <select name="bank_account_id" id="bank_account_id"
                                            class="form-select select2-single @error('bank_account_id') is-invalid @enderror" required>
                                        <option value="">Select bank account</option>
                                        @foreach($bankAccounts as $bankAccount)
                                            <option value="{{ $bankAccount->id }}" 
                                                {{ old('bank_account_id', $deposit->bank_account_id) == $bankAccount->id ? 'selected' : '' }}>
                                                {{ $bankAccount->name }} ({{ $bankAccount->account_number }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('bank_account_id') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Cheque Number</label>
                                    <input type="text" name="cheque_number" 
                                           class="form-control @error('cheque_number') is-invalid @enderror"
                                           value="{{ old('cheque_number', $deposit->cheque_number) }}"
                                           placeholder="Optional cheque number">
                                    @error('cheque_number') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>
                            </div>

                            <!-- Transaction Reference -->
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Transaction Reference</label>
                                    <input type="text" name="transaction_reference" 
                                           class="form-control @error('transaction_reference') is-invalid @enderror"
                                           value="{{ old('transaction_reference', $deposit->transaction_reference) }}"
                                           placeholder="Optional transaction reference">
                                    @error('transaction_reference') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="row mb-3">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" rows="3" 
                                              class="form-control @error('notes') is-invalid @enderror"
                                              placeholder="Optional notes">{{ old('notes', $deposit->notes) }}</textarea>
                                    @error('notes') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-warning">
                                            <i class="bx bx-save me-1"></i> Update
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Column - Information -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6 class="text-primary">Deposit Details</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <strong>Deposit ID:</strong><br>
                                    <span class="text-muted">#{{ $deposit->id }}</span>
                                </li>
                                <li class="mb-2">
                                    <strong>Account Number:</strong><br>
                                    <span class="text-muted">{{ $deposit->shareAccount->account_number ?? 'N/A' }}</span>
                                </li>
                                <li class="mb-2">
                                    <strong>Current Deposit Amount:</strong><br>
                                    <span class="text-muted">{{ number_format($deposit->deposit_amount, 2) }}</span>
                                </li>
                                <li class="mb-2">
                                    <strong>Current Shares:</strong><br>
                                    <span class="text-muted">{{ number_format($deposit->number_of_shares, 4) }}</span>
                                </li>
                                <li class="mb-2">
                                    <strong>Charge Amount:</strong><br>
                                    <span class="text-muted">{{ number_format($deposit->charge_amount ?? 0, 2) }}</span>
                                </li>
                                <li class="mb-2">
                                    <strong>Total Amount:</strong><br>
                                    <span class="text-muted">{{ number_format($deposit->total_amount, 2) }}</span>
                                </li>
                            </ul>
                        </div>

                        <hr>

                        <div class="alert alert-warning mb-0">
                            <small>
                                <i class="bx bx-info-circle me-1"></i>
                                <strong>Note:</strong> Changing the deposit amount or shares will update the share account balance accordingly.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2-single').select2({
            placeholder: 'Select an option',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5'
        });

        // Initialize Select2 for bank account
        $('#bank_account_id').select2({
            placeholder: 'Select bank account',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5'
        });
    });
</script>
@endpush

