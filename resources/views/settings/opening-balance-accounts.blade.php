@extends('layouts.main')

@section('title', 'Opening Balance Accounts Settings')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'Opening Balance Accounts', 'url' => '#', 'icon' => 'bx bx-bar-chart-alt-2']
        ]" />
        <h6 class="mb-0 text-uppercase">OPENING BALANCE ACCOUNTS SETTINGS</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Opening Balance Accounts Configuration</h4>

                        @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bx bx-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        @endif

                        @if(isset($errors) && $errors->any())
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

                        <form action="{{ route('settings.opening-balance-accounts.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <!-- SHARES Opening Balance Account -->
                                <div class="col-md-12 mb-4">
                                    <div class="card border-primary">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0"><i class="bx bx-bar-chart-square me-2"></i>SHARES Opening Balance Account</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="shares_opening_balance_account_id" class="form-label">Chart Account</label>
                                                <select name="shares_opening_balance_account_id" id="shares_opening_balance_account_id" 
                                                        class="form-select select2-single @error('shares_opening_balance_account_id') is-invalid @enderror">
                                                    <option value="">Select chart account</option>
                                                    @foreach($chartAccounts as $account)
                                                        <option value="{{ $account->id }}" 
                                                            {{ old('shares_opening_balance_account_id', $sharesOpeningBalanceAccountId) == $account->id ? 'selected' : '' }}>
                                                            {{ $account->account_code }} - {{ $account->account_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('shares_opening_balance_account_id') 
                                                    <div class="invalid-feedback">{{ $message }}</div> 
                                                @enderror
                                                <small class="text-muted">Select the chart account to use for SHARES opening balances.</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- SAVINGS Opening Balance Account -->
                                <div class="col-md-12 mb-4">
                                    <div class="card border-success">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0"><i class="bx bx-wallet me-2"></i>SAVINGS Opening Balance Account</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="savings_opening_balance_account_id" class="form-label">Chart Account</label>
                                                <select name="savings_opening_balance_account_id" id="savings_opening_balance_account_id" 
                                                        class="form-select select2-single @error('savings_opening_balance_account_id') is-invalid @enderror">
                                                    <option value="">Select chart account</option>
                                                    @foreach($chartAccounts as $account)
                                                        <option value="{{ $account->id }}" 
                                                            {{ old('savings_opening_balance_account_id', $savingsOpeningBalanceAccountId) == $account->id ? 'selected' : '' }}>
                                                            {{ $account->account_code }} - {{ $account->account_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('savings_opening_balance_account_id') 
                                                    <div class="invalid-feedback">{{ $message }}</div> 
                                                @enderror
                                                <small class="text-muted">Select the chart account to use for SAVINGS opening balances.</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- DEPOSITS Opening Balance Account -->
                                <div class="col-md-12 mb-4">
                                    <div class="card border-info">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="mb-0"><i class="bx bx-credit-card me-2"></i>DEPOSITS Opening Balance Account</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="deposits_opening_balance_account_id" class="form-label">Chart Account</label>
                                                <select name="deposits_opening_balance_account_id" id="deposits_opening_balance_account_id" 
                                                        class="form-select select2-single @error('deposits_opening_balance_account_id') is-invalid @enderror">
                                                    <option value="">Select chart account</option>
                                                    @foreach($chartAccounts as $account)
                                                        <option value="{{ $account->id }}" 
                                                            {{ old('deposits_opening_balance_account_id', $depositsOpeningBalanceAccountId) == $account->id ? 'selected' : '' }}>
                                                            {{ $account->account_code }} - {{ $account->account_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('deposits_opening_balance_account_id') 
                                                    <div class="invalid-feedback">{{ $message }}</div> 
                                                @enderror
                                                <small class="text-muted">Select the chart account to use for DEPOSITS opening balances.</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('settings.index') }}" class="btn btn-secondary">
                                            <i class="bx bx-arrow-back me-1"></i> Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bx bx-save me-1"></i> Save Settings
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
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
        // Initialize Select2 for all dropdowns
        $('.select2-single').select2({
            placeholder: 'Select chart account',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5'
        });
    });
</script>
@endpush
