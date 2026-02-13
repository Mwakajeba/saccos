@extends('layouts.main')

@section('title', 'Record Income Distribution')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Investment Management', 'url' => route('investments.index'), 'icon' => 'bx bx-trending-up'],
            ['label' => 'Cash Flows', 'url' => route('investments.cash-flows.index'), 'icon' => 'bx bx-dollar-circle'],
            ['label' => 'Record Income Distribution', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">RECORD INCOME DISTRIBUTION</h6>
            <a href="{{ route('investments.cash-flows.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bx bx-arrow-back me-1"></i> Back to Cash Flows
            </a>
        </div>
        <hr />

        <div class="card">
            <div class="card-body">
                <form action="{{ route('investments.cash-flows.income-distribution.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="utt_fund_id" class="form-label">UTT Fund <span class="text-danger">*</span></label>
                                <select class="form-select select2-single" id="utt_fund_id" name="utt_fund_id" required>
                                    <option value="">Select Fund</option>
                                    @foreach($funds as $fund)
                                        <option value="{{ $fund->id }}" {{ old('utt_fund_id') == $fund->id ? 'selected' : '' }}>{{ $fund->fund_name }} ({{ $fund->fund_code }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="transaction_date" class="form-label">Transaction Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="transaction_date" name="transaction_date" value="{{ old('transaction_date', date('Y-m-d')) }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount (TZS) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="amount" name="amount" value="{{ old('amount') }}" step="0.01" min="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="bank_account_id" class="form-label">Bank Account (Received Into) <span class="text-danger">*</span></label>
                                <select class="form-select select2-single" id="bank_account_id" name="bank_account_id" required>
                                    <option value="">Select Bank Account</option>
                                    @foreach($bankAccounts as $account)
                                        <option value="{{ $account->id }}" {{ old('bank_account_id') == $account->id ? 'selected' : '' }}>{{ $account->name }} - {{ $account->account_number }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <input type="text" class="form-control" id="description" name="description" value="{{ old('description') }}" placeholder="e.g. Dividend distribution Q1 2025">
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('investments.cash-flows.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i> Record Income Distribution
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
