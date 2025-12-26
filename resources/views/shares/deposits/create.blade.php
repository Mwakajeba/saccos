@extends('layouts.main')

@section('title', 'Create Share Deposit')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Shares Management', 'url' => route('shares.management'), 'icon' => 'bx bx-bar-chart-square'],
            ['label' => 'Share Deposits', 'url' => route('shares.deposits.index'), 'icon' => 'bx bx-right-arrow-alt'],
            ['label' => 'Create', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase text-success">Add Share Deposit</h6>
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

                        <form action="{{ route('shares.deposits.store') }}" method="POST" id="shareDepositForm">
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
                                        <strong>Current Balance:</strong> <span id="currentBalance">-</span> shares<br>
                                        <strong>Min Purchase:</strong> <span id="minPurchase">-</span><br>
                                        <strong>Max Purchase:</strong> <span id="maxPurchase">-</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Deposit Date -->
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Deposit Date <span class="text-danger">*</span></label>
                                    <input type="date" name="deposit_date" 
                                           class="form-control @error('deposit_date') is-invalid @enderror"
                                           value="{{ old('deposit_date', date('Y-m-d')) }}" required>
                                    @error('deposit_date') 
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
                                           value="{{ old('deposit_amount') }}" required>
                                    @error('deposit_amount') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                    <small class="text-muted" id="amountHelper"></small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Number of Shares <span class="text-danger">*</span></label>
                                    <input type="number" name="number_of_shares" id="number_of_shares" step="0.0001" min="0.0001"
                                           class="form-control @error('number_of_shares') is-invalid @enderror"
                                           value="{{ old('number_of_shares') }}" required>
                                    @error('number_of_shares') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                    <small class="text-muted">Calculated automatically based on nominal price</small>
                                </div>
                            </div>

                            <!-- Charge Information (if applicable) -->
                            <div id="chargeInfo" class="alert alert-warning mb-3" style="display: none;">
                                <h6 class="mb-2">Charge Information</h6>
                                <div>
                                    <strong>Charge Type:</strong> <span id="chargeType">-</span><br>
                                    <strong>Charge Amount:</strong> <span id="chargeAmount">-</span>
                                </div>
                            </div>

                            <!-- Total Amount -->
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Total Amount</label>
                                    <input type="text" id="total_amount" class="form-control" readonly 
                                           value="0.00" style="background-color: #f8f9fa; font-weight: bold;">
                                    <small class="text-muted">Deposit Amount + Charge Amount</small>
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
                                            <i class="bx bx-save me-1"></i> Save
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
                            <h6 class="text-primary">Instructions</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Select a share account from the dropdown
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Enter the deposit amount
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Number of shares will be calculated automatically
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Charges (if any) will be applied automatically
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Select bank account (required) and add reference if needed
                                </li>
                            </ul>
                        </div>

                        <hr>

                        <div class="alert alert-info mb-0">
                            <small>
                                <i class="bx bx-info-circle me-1"></i>
                                <strong>Note:</strong> The deposit will automatically update the share account balance when saved.
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
                    url: '{{ route("shares.deposits.getAccountDetails") }}',
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
                        $('#minPurchase').text(response.minimum_purchase_amount ? formatCurrency(response.minimum_purchase_amount) : 'No limit');
                        $('#maxPurchase').text(response.maximum_purchase_amount ? formatCurrency(response.maximum_purchase_amount) : 'No limit');
                        
                        // Show account details
                        $('#accountDetails').show();
                        
                        // Update amount helper text
                        let helperText = '';
                        if (response.minimum_purchase_amount) {
                            helperText = 'Minimum: ' + formatCurrency(response.minimum_purchase_amount);
                        }
                        if (response.maximum_purchase_amount) {
                            helperText += (helperText ? ' | ' : '') + 'Maximum: ' + formatCurrency(response.maximum_purchase_amount);
                        }
                        $('#amountHelper').text(helperText);
                        
                        // Show charge info if applicable
                        if (response.has_charges) {
                            $('#chargeType').text(response.charge_type === 'fixed' ? 'Fixed Amount' : 'Percentage');
                            $('#chargeInfo').show();
                        } else {
                            $('#chargeInfo').hide();
                        }
                        
                        // Calculate shares when deposit amount changes
                        calculateShares();
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
                $('#chargeInfo').hide();
                accountDetails = null;
            }
        });

        // Handle deposit amount change
        $('#deposit_amount').on('input', function() {
            calculateShares();
            calculateTotal();
        });

        // Handle number of shares change
        $('#number_of_shares').on('input', function() {
            calculateTotal();
        });


        // Calculate number of shares based on deposit amount and nominal price
        function calculateShares() {
            const depositAmount = parseFloat($('#deposit_amount').val()) || 0;
            const nominalPrice = accountDetails ? parseFloat(accountDetails.nominal_price) : 0;
            
            if (nominalPrice > 0 && depositAmount > 0) {
                const shares = depositAmount / nominalPrice;
                $('#number_of_shares').val(shares.toFixed(4));
            }
        }

        // Calculate total amount (deposit + charges)
        function calculateTotal() {
            const depositAmount = parseFloat($('#deposit_amount').val()) || 0;
            let chargeAmount = 0;
            
            if (accountDetails && accountDetails.has_charges && accountDetails.charge_amount) {
                if (accountDetails.charge_type === 'fixed') {
                    chargeAmount = parseFloat(accountDetails.charge_amount);
                } else if (accountDetails.charge_type === 'percentage') {
                    chargeAmount = (depositAmount * parseFloat(accountDetails.charge_amount)) / 100;
                }
                
                $('#chargeAmount').text(formatCurrency(chargeAmount));
            }
            
            const totalAmount = depositAmount + chargeAmount;
            $('#total_amount').val(formatCurrency(totalAmount));
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
        $('#shareDepositForm').on('submit', function(e) {
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

            const depositAmount = parseFloat($('#deposit_amount').val()) || 0;
            
            // Validate minimum purchase amount
            if (accountDetails.minimum_purchase_amount && depositAmount < parseFloat(accountDetails.minimum_purchase_amount)) {
                e.preventDefault();
                Swal.fire({
                    title: 'Validation Error',
                    text: 'Deposit amount must be at least ' + formatCurrency(accountDetails.minimum_purchase_amount),
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return false;
            }

            // Validate maximum purchase amount
            if (accountDetails.maximum_purchase_amount && depositAmount > parseFloat(accountDetails.maximum_purchase_amount)) {
                e.preventDefault();
                Swal.fire({
                    title: 'Validation Error',
                    text: 'Deposit amount must not exceed ' + formatCurrency(accountDetails.maximum_purchase_amount),
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

