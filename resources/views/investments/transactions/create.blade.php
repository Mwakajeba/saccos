@extends('layouts.main')

@section('title', 'Create Transaction')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Investment', 'url' => '#', 'icon' => 'bx bx-trending-up'],
            ['label' => 'Transactions', 'url' => route('investments.transactions.index'), 'icon' => 'bx bx-transfer'],
            ['label' => 'Create Transaction', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">CREATE UTT TRANSACTION</h6>
            <a href="{{ route('investments.transactions.index') }}" class="btn btn-secondary">
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

                <form action="{{ route('investments.transactions.store') }}" method="POST" id="transactionForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="utt_fund_id" class="form-label">UTT Fund <span class="text-danger">*</span></label>
                                <select class="form-select" id="utt_fund_id" name="utt_fund_id" required>
                                    <option value="">Select Fund</option>
                                    @foreach($funds as $fund)
                                        <option value="{{ $fund->id }}" {{ old('utt_fund_id') == $fund->id ? 'selected' : '' }}>{{ $fund->fund_name }} ({{ $fund->fund_code }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="transaction_type" class="form-label">Transaction Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="transaction_type" name="transaction_type" required>
                                    <option value="BUY" {{ old('transaction_type') == 'BUY' ? 'selected' : '' }}>Subscription (BUY)</option>
                                    <option value="SELL" {{ old('transaction_type') == 'SELL' ? 'selected' : '' }}>Redemption (SELL)</option>
                                    <option value="REINVESTMENT" {{ old('transaction_type') == 'REINVESTMENT' ? 'selected' : '' }}>Reinvestment</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="trade_date" class="form-label">Trade Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="trade_date" name="trade_date" value="{{ old('trade_date', date('Y-m-d')) }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="nav_date" class="form-label">NAV Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="nav_date" name="nav_date" value="{{ old('nav_date', date('Y-m-d')) }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="settlement_date" class="form-label">Settlement Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="settlement_date" name="settlement_date" value="{{ old('settlement_date', date('Y-m-d')) }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="units" class="form-label">Units <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="units" name="units" value="{{ old('units') }}" step="0.0001" min="0.0001" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="nav_per_unit" class="form-label">NAV per Unit <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="nav_per_unit" name="nav_per_unit" value="{{ old('nav_per_unit') }}" step="0.0001" min="0.0001" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="total_cash_value" class="form-label">Total Cash Value <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="total_cash_value" name="total_cash_value" value="{{ old('total_cash_value') }}" step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="bank_account_id" class="form-label">Bank Account <span class="text-danger">*</span></label>
                                <select class="form-select" id="bank_account_id" name="bank_account_id" required>
                                    <option value="">Select Bank Account</option>
                                    @foreach($bankAccounts as $account)
                                        <option value="{{ $account->id }}" {{ old('bank_account_id') == $account->id ? 'selected' : '' }}>{{ $account->account_name }} - {{ $account->account_number }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <input type="text" class="form-control" id="description" name="description" value="{{ old('description') }}">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('investments.transactions.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i> Create Transaction
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
        // Auto-calculate total cash value
        $('#units, #nav_per_unit').on('input', function() {
            var units = parseFloat($('#units').val()) || 0;
            var nav = parseFloat($('#nav_per_unit').val()) || 0;
            var total = units * nav;
            $('#total_cash_value').val(total.toFixed(2));
        });
    });
</script>
@endpush

