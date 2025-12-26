@extends('layouts.main')

@section('title', 'Edit Share Transfer')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Shares Management', 'url' => route('shares.management'), 'icon' => 'bx bx-bar-chart-square'],
            ['label' => 'Share Transfers', 'url' => route('shares.transfers.index'), 'icon' => 'bx bx-transfer'],
            ['label' => 'Edit', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase text-primary">Edit Share Transfer</h6>
            <a href="{{ route('shares.transfers.index') }}" class="btn btn-success">
                <i class="bx bx-list-ul me-1"></i> Share Transfers List
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

                        <form action="{{ route('shares.transfers.update', \Vinkla\Hashids\Facades\Hashids::encode($transfer->id)) }}" method="POST" id="shareTransferForm">
                            @method('PUT')
                            @csrf

                            <!-- From Account (Source) -->
                            <div class="row mb-3">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">From Account (Source) <span class="text-danger">*</span></label>
                                    <select name="from_account_id" id="from_account_id" 
                                            class="form-select select2-single @error('from_account_id') is-invalid @enderror" required>
                                        <option value="">Select source account</option>
                                        @foreach($shareAccounts as $account)
                                            <option value="{{ $account->id }}" 
                                                {{ old('from_account_id', $transfer->from_account_id) == $account->id ? 'selected' : '' }}>
                                                {{ $account->account_number }} - {{ $account->customer->name ?? 'N/A' }} ({{ $account->shareProduct->share_name ?? 'N/A' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('from_account_id') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>
                            </div>

                            <!-- From Account Details Display -->
                            <div id="fromAccountDetails" class="alert alert-info mb-3" style="display: none;">
                                <h6 class="mb-2">From Account Details</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Account Number:</strong> <span id="fromAccountNumber">-</span><br>
                                        <strong>Customer Name:</strong> <span id="fromCustomerName">-</span><br>
                                        <strong>Share Product:</strong> <span id="fromShareProductName">-</span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Nominal Price:</strong> <span id="fromNominalPrice">-</span><br>
                                        <strong>Available Balance:</strong> <span id="fromCurrentBalance">-</span> shares
                                    </div>
                                </div>
                            </div>

                            <!-- To Account (Destination) -->
                            <div class="row mb-3">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">To Account (Destination) <span class="text-danger">*</span></label>
                                    <select name="to_account_id" id="to_account_id" 
                                            class="form-select select2-single @error('to_account_id') is-invalid @enderror" required>
                                        <option value="">Select destination account</option>
                                        @foreach($shareAccounts as $account)
                                            <option value="{{ $account->id }}" 
                                                {{ old('to_account_id', $transfer->to_account_id) == $account->id ? 'selected' : '' }}>
                                                {{ $account->account_number }} - {{ $account->customer->name ?? 'N/A' }} ({{ $account->shareProduct->share_name ?? 'N/A' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('to_account_id') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>
                            </div>

                            <!-- To Account Details Display -->
                            <div id="toAccountDetails" class="alert alert-success mb-3" style="display: none;">
                                <h6 class="mb-2">To Account Details</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Account Number:</strong> <span id="toAccountNumber">-</span><br>
                                        <strong>Customer Name:</strong> <span id="toCustomerName">-</span><br>
                                        <strong>Share Product:</strong> <span id="toShareProductName">-</span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Nominal Price:</strong> <span id="toNominalPrice">-</span><br>
                                        <strong>Current Balance:</strong> <span id="toCurrentBalance">-</span> shares
                                    </div>
                                </div>
                            </div>

                            <!-- Transfer Date and Status -->
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Transfer Date <span class="text-danger">*</span></label>
                                    <input type="date" name="transfer_date" 
                                           class="form-control @error('transfer_date') is-invalid @enderror"
                                           value="{{ old('transfer_date', $transfer->transfer_date ? $transfer->transfer_date->format('Y-m-d') : date('Y-m-d')) }}" required>
                                    @error('transfer_date') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="pending" {{ old('status', $transfer->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="approved" {{ old('status', $transfer->status) == 'approved' ? 'selected' : '' }}>Approved</option>
                                        <option value="rejected" {{ old('status', $transfer->status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    </select>
                                    @error('status') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>
                            </div>

                            <!-- Number of Shares and Transfer Amount -->
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Number of Shares <span class="text-danger">*</span></label>
                                    <input type="number" name="number_of_shares" id="number_of_shares" step="0.0001" min="0.0001"
                                           class="form-control @error('number_of_shares') is-invalid @enderror"
                                           value="{{ old('number_of_shares', $transfer->number_of_shares) }}" required>
                                    @error('number_of_shares') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                    <small class="text-muted" id="sharesHelper">Enter number of shares to transfer</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Transfer Amount</label>
                                    <input type="text" id="transfer_amount" class="form-control" readonly 
                                           value="0.00" style="background-color: #f8f9fa; font-weight: bold;">
                                    <small class="text-muted">Calculated automatically (Shares × Nominal Price)</small>
                                </div>
                            </div>

                            <!-- Transfer Fee Information -->
                            <div id="transferFeeInfo" class="alert alert-warning mb-3" style="display: none;">
                                <h6 class="mb-2">Transfer Fee Information</h6>
                                <div>
                                    <strong>Fee Type:</strong> <span id="transferFeeType">-</span><br>
                                    <strong>Fee Amount:</strong> <span id="transferFeeAmount">-</span>
                                </div>
                            </div>

                            <!-- Bank Account (for fee payment) -->
                            <div id="bankAccountSection" class="row mb-3" style="display: none;">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Bank Account <span id="bankAccountRequired" class="text-danger" style="display: none;">*</span></label>
                                    <select name="bank_account_id" id="bank_account_id"
                                            class="form-select select2-single @error('bank_account_id') is-invalid @enderror">
                                        <option value="">Select bank account</option>
                                        @foreach($bankAccounts as $bankAccount)
                                            <option value="{{ $bankAccount->id }}" 
                                                {{ old('bank_account_id', $transfer->bank_account_id) == $bankAccount->id ? 'selected' : '' }}>
                                                {{ $bankAccount->name }} ({{ $bankAccount->account_number }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('bank_account_id') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                    <small class="text-muted">Required if transfer fee is applicable</small>
                                </div>
                            </div>

                            <!-- Journal Reference -->
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Journal Reference</label>
                                    <select name="journal_reference_id" id="journal_reference_id"
                                            class="form-select select2-single @error('journal_reference_id') is-invalid @enderror">
                                        <option value="">Select journal reference (optional)</option>
                                        @foreach($journalReferences as $journalRef)
                                            <option value="{{ $journalRef->id }}" 
                                                {{ old('journal_reference_id', $transfer->journal_reference_id) == $journalRef->id ? 'selected' : '' }}>
                                                {{ $journalRef->name }} ({{ $journalRef->reference }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('journal_reference_id') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                    <small class="text-muted">Optional - will use product's default if not provided</small>
                                </div>
                            </div>

                            <!-- Transaction Reference -->
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Transaction Reference</label>
                                    <input type="text" name="transaction_reference" 
                                           class="form-control @error('transaction_reference') is-invalid @enderror"
                                           value="{{ old('transaction_reference', $transfer->transaction_reference) }}"
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
                                              placeholder="Optional notes">{{ old('notes', $transfer->notes) }}</textarea>
                                    @error('notes') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bx bx-save me-1"></i> Update Transfer
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
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6 class="text-primary">Instructions</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Select source account (from)
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Select destination account (to)
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Both accounts must be for the same share product
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Enter the number of shares to transfer
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Transfer amount will be calculated automatically
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Transfer fees (if any) will be calculated automatically
                                </li>
                            </ul>
                        </div>

                        <hr>

                        <div class="alert alert-info mb-0">
                            <small>
                                <i class="bx bx-info-circle me-1"></i>
                                <strong>Note:</strong> The transfer will update both account balances when saved. GL transactions will be created automatically.
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

        let fromAccountDetails = null;
        let toAccountDetails = null;

        // Handle from account selection change
        $('#from_account_id').on('change', function() {
            const accountId = $(this).val();
            
            if (accountId) {
                loadAccountDetails(accountId, 'from', function() {
                    // If to account is also selected, validate they're the same product
                    if ($('#to_account_id').val()) {
                        validateAccounts();
                    }
                    calculateTransfer();
                });
            } else {
                $('#fromAccountDetails').hide();
                fromAccountDetails = null;
                calculateTransfer();
            }
        });

        // Handle to account selection change
        $('#to_account_id').on('change', function() {
            const accountId = $(this).val();
            
            if (accountId) {
                loadAccountDetails(accountId, 'to', function() {
                    // Validate accounts are compatible
                    if ($('#from_account_id').val()) {
                        validateAccounts();
                    }
                });
            } else {
                $('#toAccountDetails').hide();
                toAccountDetails = null;
            }
        });

        // Load account details via AJAX
        function loadAccountDetails(accountId, type, callback) {
            $.ajax({
                url: '{{ route("shares.transfers.getAccountDetails") }}',
                type: 'GET',
                data: { account_id: accountId },
                success: function(response) {
                    if (type === 'from') {
                        fromAccountDetails = response;
                        $('#fromAccountNumber').text(response.account_number);
                        $('#fromCustomerName').text(response.customer_name);
                        $('#fromShareProductName').text(response.share_product_name);
                        $('#fromNominalPrice').text(formatCurrency(response.nominal_price));
                        $('#fromCurrentBalance').text(formatNumber(response.current_balance, 4));
                        $('#fromAccountDetails').show();
                        
                        $('#sharesHelper').text('Available: ' + formatNumber(response.current_balance, 4) + ' shares');
                        $('#number_of_shares').attr('max', response.current_balance);
                    } else {
                        toAccountDetails = response;
                        $('#toAccountNumber').text(response.account_number);
                        $('#toCustomerName').text(response.customer_name);
                        $('#toShareProductName').text(response.share_product_name);
                        $('#toNominalPrice').text(formatCurrency(response.nominal_price));
                        $('#toCurrentBalance').text(formatNumber(response.current_balance, 4));
                        $('#toAccountDetails').show();
                    }
                    
                    if (callback) callback();
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
        }

        // Validate that both accounts are for the same product
        function validateAccounts() {
            if (fromAccountDetails && toAccountDetails) {
                const fromProductId = $('#from_account_id option:selected').data('product-id') || fromAccountDetails.share_product_name;
                const toProductId = $('#to_account_id option:selected').data('product-id') || toAccountDetails.share_product_name;
                
                // Check if product names match (simpler check)
                if (fromAccountDetails.share_product_name !== toAccountDetails.share_product_name) {
                    Swal.fire({
                        title: 'Invalid Selection',
                        text: 'Both accounts must be for the same share product.',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                    $('#to_account_id').val('').trigger('change');
                    return false;
                }
            }
            return true;
        }

        // Handle number of shares change
        $('#number_of_shares').on('input', function() {
            calculateTransfer();
        });

        // Calculate transfer amount and fee
        function calculateTransfer() {
            const numberOfShares = parseFloat($('#number_of_shares').val()) || 0;
            let nominalPrice = 0;
            let transferFee = 0;
            let transferFeeType = null;
            let transferFeeAmount = 0;
            
            // Use from account details for calculations
            if (fromAccountDetails) {
                nominalPrice = parseFloat(fromAccountDetails.nominal_price) || 0;
                transferFeeAmount = parseFloat(fromAccountDetails.transfer_fee) || 0;
                transferFeeType = fromAccountDetails.transfer_fee_type;
            }
            
            if (nominalPrice > 0 && numberOfShares > 0) {
                const transferAmount = numberOfShares * nominalPrice;
                $('#transfer_amount').val(formatCurrency(transferAmount));
                
                // Calculate transfer fee
                if (transferFeeAmount > 0) {
                    if (transferFeeType === 'fixed') {
                        transferFee = transferFeeAmount;
                    } else if (transferFeeType === 'percentage') {
                        transferFee = (transferAmount * transferFeeAmount) / 100;
                    }
                    
                    $('#transferFeeType').text(transferFeeType === 'fixed' ? 'Fixed Amount' : 'Percentage');
                    $('#transferFeeAmount').text(formatCurrency(transferFee));
                    $('#transferFeeInfo').show();
                    
                    // Show bank account field if fee exists
                    $('#bankAccountSection').show();
                    $('#bank_account_id').attr('required', true);
                    $('#bankAccountRequired').show();
                } else {
                    $('#transferFeeInfo').hide();
                    $('#bankAccountSection').hide();
                    $('#bank_account_id').removeAttr('required');
                    $('#bankAccountRequired').hide();
                }
            } else {
                $('#transfer_amount').val('0.00');
                $('#transferFeeInfo').hide();
                $('#bankAccountSection').hide();
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
        $('#shareTransferForm').on('submit', function(e) {
            if (!fromAccountDetails || !toAccountDetails) {
                e.preventDefault();
                Swal.fire({
                    title: 'Validation Error',
                    text: 'Please select both source and destination accounts',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return false;
            }

            // Validate same product
            if (fromAccountDetails.share_product_name !== toAccountDetails.share_product_name) {
                e.preventDefault();
                Swal.fire({
                    title: 'Validation Error',
                    text: 'Both accounts must be for the same share product.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return false;
            }

            // Validate different accounts
            if ($('#from_account_id').val() === $('#to_account_id').val()) {
                e.preventDefault();
                Swal.fire({
                    title: 'Validation Error',
                    text: 'Source and destination accounts must be different.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return false;
            }

            const numberOfShares = parseFloat($('#number_of_shares').val()) || 0;
            const availableBalance = parseFloat(fromAccountDetails.current_balance) || 0;
            
            // Check if sufficient balance
            if (numberOfShares > availableBalance) {
                e.preventDefault();
                Swal.fire({
                    title: 'Validation Error',
                    text: 'Insufficient share balance in source account. Available: ' + formatNumber(availableBalance, 4) + ' shares',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return false;
            }

            // Check if bank account is required (when fee exists)
            const transferFee = parseFloat($('#transferFeeAmount').text().replace(/,/g, '')) || 0;
            if (transferFee > 0 && !$('#bank_account_id').val()) {
                e.preventDefault();
                Swal.fire({
                    title: 'Validation Error',
                    text: 'Bank account is required when transfer fee is applicable.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return false;
            }
        });

        // Load account details for pre-selected accounts (edit mode)
        @if($transfer->from_account_id)
        setTimeout(function() {
            loadAccountDetails('{{ $transfer->from_account_id }}', 'from', function() {
                calculateTransfer();
            });
        }, 500);
        @endif
        
        @if($transfer->to_account_id)
        setTimeout(function() {
            loadAccountDetails('{{ $transfer->to_account_id }}', 'to');
        }, 500);
        @endif
    });
</script>
@endpush

