@extends('layouts.main')

@section('title', 'Create UTT Fund')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Investment', 'url' => '#', 'icon' => 'bx bx-trending-up'],
            ['label' => 'UTT Funds', 'url' => route('investments.funds.index'), 'icon' => 'bx bx-package'],
            ['label' => 'Create Fund', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">CREATE UTT FUND</h6>
            <a href="{{ route('investments.funds.index') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back
            </a>
        </div>
        <hr />

        <div class="card">
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('investments.funds.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fund_name" class="form-label">Fund Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="fund_name" name="fund_name" value="{{ old('fund_name') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fund_code" class="form-label">Fund Code / Symbol <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="fund_code" name="fund_code" value="{{ old('fund_code') }}" required>
                                <small class="form-text text-muted">Unique identifier for the fund</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="currency" class="form-label">Currency <span class="text-danger">*</span></label>
                                <select class="form-select" id="currency" name="currency" required>
                                    <option value="TZS" {{ old('currency', 'TZS') == 'TZS' ? 'selected' : '' }}>TZS</option>
                                    <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD</option>
                                    <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="investment_horizon" class="form-label">Investment Horizon <span class="text-danger">*</span></label>
                                <select class="form-select" id="investment_horizon" name="investment_horizon" required>
                                    <option value="SHORT-TERM" {{ old('investment_horizon') == 'SHORT-TERM' ? 'selected' : '' }}>Short-Term</option>
                                    <option value="LONG-TERM" {{ old('investment_horizon', 'LONG-TERM') == 'LONG-TERM' ? 'selected' : '' }}>Long-Term</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="expense_ratio" class="form-label">Expense Ratio (%)</label>
                                <input type="number" class="form-control" id="expense_ratio" name="expense_ratio" value="{{ old('expense_ratio') }}" step="0.0001" min="0" max="100">
                                <small class="form-text text-muted">Optional</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="Active" {{ old('status', 'Active') == 'Active' ? 'selected' : '' }}>Active</option>
                                    <option value="Closed" {{ old('status') == 'Closed' ? 'selected' : '' }}>Closed</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="branch_id" class="form-label">Branch</label>
                                <select class="form-select" id="branch_id" name="branch_id">
                                    <option value="">Select Branch (Optional)</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                    </div>

                    <!-- Chart Accounts Section -->
                    <div class="card border-info mt-4">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="bx bx-book-open me-2"></i>Chart Accounts Configuration</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="investment_account_id" class="form-label">UTT Investment (Asset) <span class="text-danger">*</span></label>
                                        <select class="form-select select2-single" id="investment_account_id" name="investment_account_id" required>
                                            <option value="">Select Asset Account</option>
                                            @foreach($assetAccounts as $account)
                                                <option value="{{ $account->id }}" {{ old('investment_account_id') == $account->id ? 'selected' : '' }}>
                                                    {{ $account->account_code }} - {{ $account->account_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">Asset account for UTT investments</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="income_account_id" class="form-label">UTT Investment Income <span class="text-danger">*</span></label>
                                        <select class="form-select select2-single" id="income_account_id" name="income_account_id" required>
                                            <option value="">Select Income Account</option>
                                            @foreach($incomeAccounts as $account)
                                                <option value="{{ $account->id }}" {{ old('income_account_id') == $account->id ? 'selected' : '' }}>
                                                    {{ $account->account_code }} - {{ $account->account_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">Income account for UTT investment gains</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="loss_account_id" class="form-label">UTT Investment Loss (Expense) <span class="text-danger">*</span></label>
                                        <select class="form-select select2-single" id="loss_account_id" name="loss_account_id" required>
                                            <option value="">Select Expense Account</option>
                                            @foreach($expenseAccounts as $account)
                                                <option value="{{ $account->id }}" {{ old('loss_account_id') == $account->id ? 'selected' : '' }}>
                                                    {{ $account->account_code }} - {{ $account->account_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">Expense account for UTT investment losses</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('investments.funds.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i> Create Fund
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize Select2 for chart account dropdowns
        if (typeof $ !== 'undefined' && $.fn.select2) {
            $('#investment_account_id, #income_account_id, #loss_account_id').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: function() {
                    return $(this).find('option:first').text();
                },
                allowClear: true
            });
        }
    });
</script>
@endpush
