@extends('layouts.main')

@section('title', 'Create Share Withdrawal')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Shares Management', 'url' => route('shares.management'), 'icon' => 'bx bx-bar-chart-square'],
            ['label' => 'Share Withdrawals', 'url' => route('shares.withdrawals.index'), 'icon' => 'bx bx-up-arrow-circle'],
            ['label' => 'Create', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase text-warning">Add Share Withdrawal</h6>
            <a href="{{ route('shares.withdrawals.index') }}" class="btn btn-success">
                <i class="bx bx-list-ul me-1"></i> Share Withdrawals List
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

                        <form action="{{ route('shares.withdrawals.store') }}" method="POST" id="shareWithdrawalForm">
                            @csrf

                            <!-- Share Account -->
                            <div class="row mb-3">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Share Account <span class="text-danger">*</span></label>
                                    <select name="share_account_id" id="share_account_id" 
                                            class="form-select select2-single @error('share_account_id') is-invalid @enderror" required>
                                        <option value="">Select share account</option>
                                        @foreach($shareAccounts as $account)
                                            <option value="{{ $account->id }}" 
                                                {{ old('share_account_id') == $account->id ? 'selected' : '' }}
                                                data-customer-name="{{ $account->customer->name ?? 'N/A' }}"
                                                data-share-product-name="{{ $account->shareProduct->share_name ?? 'N/A' }}"
                                                data-nominal-price="{{ $account->shareProduct->nominal_price ?? 0 }}"
                                                data-current-balance="{{ $account->share_balance ?? 0 }}">
                                                {{ $account->account_number }} - {{ $account->customer->name ?? 'N/A' }} ({{ $account->shareProduct->share_name ?? 'N/A' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('share_account_id') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>
                            </div>

                            <!-- Account Details Display -->
                            <div id="accountDetails" class="alert alert-info" style="display: none;">
                                <h6 class="mb-2">Account Details</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Account Number:</strong> <span id="accountNumber">-</span><br>
                                        <strong>Customer Name:</strong> <span id="customerName">-</span><br>
                                        <strong>Share Product:</strong> <span id="shareProductName">-</span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Nominal Price:</strong> <span id="nominalPrice">-</span><br>
                                        <strong>Available Balance:</strong> <span id="currentBalance">-</span> shares<br>
                                        <strong>Min Withdrawal:</strong> <span id="minWithdrawal">-</span><br>
                                        <strong>Max Withdrawal:</strong> <span id="maxWithdrawal">-</span><br>
                                        <strong>Partial Allowed:</strong> <span id="partialAllowed">-</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Withdrawal Date -->
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Withdrawal Date <span class="text-danger">*</span></label>
                                    <input type="date" name="withdrawal_date" 
                                           class="form-control @error('withdrawal_date') is-invalid @enderror"
                                           value="{{ old('withdrawal_date', date('Y-m-d')) }}" required>
                                    @error('withdrawal_date') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>
                            </div>

                            <!-- Number of Shares and Withdrawal Amount -->
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Number of Shares <span class="text-danger">*</span></label>
                                    <input type="number" name="number_of_shares" id="number_of_shares" step="0.0001" min="0.0001"
                                           class="form-control @error('number_of_shares') is-invalid @enderror"
                                           value="{{ old('number_of_shares') }}" required>
                                    @error('number_of_shares') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                    <small class="text-muted" id="sharesHelper">Enter number of shares to withdraw</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Withdrawal Amount</label>
                                    <input type="text" id="withdrawal_amount" class="form-control" readonly 
                                           value="0.00" style="background-color: #f8f9fa; font-weight: bold;">
                                    <small class="text-muted">Calculated automatically (Shares × Nominal Price)</small>
                                </div>
                            </div>

                            <!-- Withdrawal Fee Information -->
                            <div id="withdrawalFeeInfo" class="alert alert-warning mb-3" style="display: none;">
                                <h6 class="mb-2">Withdrawal Fee Information</h6>
                                <div>
                                    <strong>Fee Type:</strong> <span id="withdrawalFeeType">-</span><br>
                                    <strong>Fee Amount:</strong> <span id="withdrawalFeeAmount">-</span>
                                </div>
                            </div>

                            <!-- Net Amount -->
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Net Amount (After Fee)</label>
                                    <input type="text" id="total_amount" class="form-control" readonly 
                                           value="0.00" style="background-color: #f8f9fa; font-weight: bold; color: #28a745;">
                                    <small class="text-muted">Withdrawal Amount - Withdrawal Fee</small>
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
                                                {{ old('bank_account_id') == $bankAccount->id ? 'selected' : '' }}>
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
                                           value="{{ old('cheque_number') }}"
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
                                           value="{{ old('transaction_reference') }}"
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
                                              placeholder="Optional notes">{{ old('notes') }}</textarea>
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
                                            <i class="bx bx-save me-1"></i> Save Withdrawal
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
                    <div class="card-header bg-warning text-white">
                        <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6 class="text-warning">Instructions</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Select a share account that allows withdrawals
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Enter the number of shares to withdraw
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Withdrawal amount will be calculated automatically
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Withdrawal fees (if any) will be deducted automatically
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Select bank account for payment
                                </li>
                            </ul>
                        </div>

                        <hr>

                        <div class="alert alert-warning mb-0">
                            <small>
                                <i class="bx bx-info-circle me-1"></i>
                                <strong>Note:</strong> The withdrawal will decrease the share account balance when saved. GL transactions will be created automatically.
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

        let accountDetails = null;

        // Handle share account selection change
        $('#share_account_id').on('change', function() {
            const accountId = $(this).val();
            
            if (accountId) {
                // Fetch account details via AJAX
                $.ajax({
                    url: '{{ route("shares.withdrawals.getAccountDetails") }}',
                    type: 'GET',
                    data: { account_id: accountId },
                    success: function(response) {
                        accountDetails = response;
                        
                        // Update account details display
                        $('#accountNumber').text(response.account_number);
                        $('#customerName').text(response.customer_name);
                        $('#shareProductName').text(response.share_product_name);
                        $('#nominalPrice').text(formatCurrency(response.nominal_price));
                        $('#currentBalance').text(formatNumber(response.current_balance, 4));
                        $('#minWithdrawal').text(response.minimum_withdrawal_amount ? formatCurrency(response.minimum_withdrawal_amount) : 'No limit');
                        $('#maxWithdrawal').text(response.maximum_withdrawal_amount ? formatCurrency(response.maximum_withdrawal_amount) : 'No limit');
                        $('#partialAllowed').text(response.allow_partial_withdrawal ? 'Yes' : 'No');
                        
                        // Show account details
                        $('#accountDetails').show();
                        
                        // Update shares helper text
                        let helperText = 'Available: ' + formatNumber(response.current_balance, 4) + ' shares';
                        if (!response.allow_partial_withdrawal) {
                            helperText += ' (Full withdrawal only)';
                        }
                        $('#sharesHelper').text(helperText);
                        
                        // Set max attribute for number of shares
                        $('#number_of_shares').attr('max', response.current_balance);
                        
                        // Show withdrawal fee info if applicable
                        if (response.withdrawal_fee && response.withdrawal_fee > 0) {
                            $('#withdrawalFeeType').text(response.withdrawal_fee_type === 'fixed' ? 'Fixed Amount' : 'Percentage');
                            $('#withdrawalFeeInfo').show();
                        } else {
                            $('#withdrawalFeeInfo').hide();
                        }
                        
                        // Calculate withdrawal amount when shares change
                        calculateWithdrawal();
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Failed to load account details.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            } else {
                $('#accountDetails').hide();
                $('#withdrawalFeeInfo').hide();
                accountDetails = null;
            }
        });

        // Handle number of shares change
        $('#number_of_shares').on('input', function() {
            calculateWithdrawal();
        });

        // Calculate withdrawal amount and net amount
        function calculateWithdrawal() {
            const numberOfShares = parseFloat($('#number_of_shares').val()) || 0;
            const nominalPrice = accountDetails ? parseFloat(accountDetails.nominal_price) : 0;
            
            if (nominalPrice > 0 && numberOfShares > 0) {
                const withdrawalAmount = numberOfShares * nominalPrice;
                $('#withdrawal_amount').val(formatCurrency(withdrawalAmount));
                
                // Calculate withdrawal fee
                let withdrawalFee = 0;
                if (accountDetails && accountDetails.withdrawal_fee && accountDetails.withdrawal_fee > 0) {
                    if (accountDetails.withdrawal_fee_type === 'fixed') {
                        withdrawalFee = parseFloat(accountDetails.withdrawal_fee);
                    } else if (accountDetails.withdrawal_fee_type === 'percentage') {
                        withdrawalFee = (withdrawalAmount * parseFloat(accountDetails.withdrawal_fee)) / 100;
                    }
                    
                    $('#withdrawalFeeAmount').text(formatCurrency(withdrawalFee));
                }
                
                // Calculate net amount (withdrawal amount - fee)
                const netAmount = withdrawalAmount - withdrawalFee;
                $('#total_amount').val(formatCurrency(netAmount));
            } else {
                $('#withdrawal_amount').val('0.00');
                $('#total_amount').val('0.00');
            }
        }

        // Format currency
        function formatCurrency(amount) {
            return parseFloat(amount || 0).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        // Format number
        function formatNumber(number, decimals) {
            return parseFloat(number || 0).toLocaleString('en-US', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            });
        }

        // Form validation
        $('#shareWithdrawalForm').on('submit', function(e) {
            if (!accountDetails) {
                e.preventDefault();
                Swal.fire({
                    title: 'Validation Error',
                    text: 'Please select a share account',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return false;
            }

            const numberOfShares = parseFloat($('#number_of_shares').val()) || 0;
            const availableBalance = parseFloat(accountDetails.current_balance) || 0;
            const nominalPrice = parseFloat(accountDetails.nominal_price) || 0;
            const withdrawalAmount = numberOfShares * nominalPrice;
            
            // Check if sufficient balance
            if (numberOfShares > availableBalance) {
                e.preventDefault();
                Swal.fire({
                    title: 'Validation Error',
                    text: 'Insufficient share balance. Available: ' + formatNumber(availableBalance, 4) + ' shares',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return false;
            }

            // Check if partial withdrawal is allowed
            if (!accountDetails.allow_partial_withdrawal && numberOfShares != availableBalance) {
                e.preventDefault();
                Swal.fire({
                    title: 'Validation Error',
                    text: 'Partial withdrawal is not allowed. You must withdraw all shares (' + formatNumber(availableBalance, 4) + ')',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return false;
            }

            // Validate minimum withdrawal amount
            if (accountDetails.minimum_withdrawal_amount && withdrawalAmount < parseFloat(accountDetails.minimum_withdrawal_amount)) {
                e.preventDefault();
                Swal.fire({
                    title: 'Validation Error',
                    text: 'Withdrawal amount must be at least ' + formatCurrency(accountDetails.minimum_withdrawal_amount),
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return false;
            }

            // Validate maximum withdrawal amount
            if (accountDetails.maximum_withdrawal_amount && withdrawalAmount > parseFloat(accountDetails.maximum_withdrawal_amount)) {
                e.preventDefault();
                Swal.fire({
                    title: 'Validation Error',
                    text: 'Withdrawal amount must not exceed ' + formatCurrency(accountDetails.maximum_withdrawal_amount),
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return false;
            }
        });

        // Trigger change if account is already selected (for form errors)
        if ($('#share_account_id').val()) {
            $('#share_account_id').trigger('change');
        }
    });
</script>
@endpush

