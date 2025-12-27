@extends('layouts.main')

@php
    use Vinkla\Hashids\Facades\Hashids;
@endphp

@section('title', 'Create Contribution Withdrawal')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Contributions', 'url' => route('contributions.index'), 'icon' => 'bx bx-donate-heart'],
            ['label' => 'Contributions Withdrawals', 'url' => route('contributions.withdrawals.index'), 'icon' => 'bx bx-up-arrow-circle'],
            ['label' => 'Create', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase text-info">Add Contribution Withdrawal</h6>
            <a href="{{ route('contributions.withdrawals.index') }}" class="btn btn-info">
                <i class="bx bx-list-ul me-1"></i> Withdrawals List
            </a>
        </div>
        <hr />

        <div class="row">
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

                        <form action="{{ route('contributions.withdrawals.store') }}" method="POST" id="withdrawalForm" data-has-custom-handler="true">
                            @csrf

                            <div class="row">
                                <!-- Contribution Product -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Contribution Product <span class="text-danger">*</span></label>
                                    <select name="contribution_product_id" id="contribution_product_id"
                                            class="form-select @error('contribution_product_id') is-invalid @enderror" required>
                                        <option value="">Select Contribution Product</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" 
                                                {{ old('contribution_product_id', isset($productId) && Hashids::decode($productId)[0] == $product->id ? $product->id : '') == $product->id ? 'selected' : '' }}>
                                                {{ $product->product_name }} ({{ $product->category }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('contribution_product_id') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>

                                <!-- Customer -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Customer <span class="text-danger">*</span></label>
                                    <select name="customer_id" id="customer_id"
                                            class="form-select @error('customer_id') is-invalid @enderror" required>
                                        <option value="">Select Customer</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" 
                                                {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }} ({{ $customer->customerNo }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('customer_id') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                    <small class="text-muted" id="balance-info" style="display: none;">
                                        Available Balance: <span id="available-balance" class="fw-bold">0.00</span> TZS
                                    </small>
                                </div>

                                <!-- Amount -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Withdrawal Amount <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">TZS</span>
                                        <input type="number" name="amount" id="amount" step="0.01" min="0.01"
                                               class="form-control @error('amount') is-invalid @enderror"
                                               value="{{ old('amount') }}" placeholder="0.00" required>
                                    </div>
                                    @error('amount') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>

                                <!-- Bank Account -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Bank Account <span class="text-danger">*</span></label>
                                    <select name="bank_account_id" id="bank_account_id"
                                            class="form-select @error('bank_account_id') is-invalid @enderror" required>
                                        <option value="">Select Bank Account</option>
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

                                <!-- Date -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date <span class="text-danger">*</span></label>
                                    <input type="date" name="date" id="date"
                                           class="form-control @error('date') is-invalid @enderror"
                                           value="{{ old('date', date('Y-m-d')) }}" required>
                                    @error('date') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>

                                <!-- Description -->
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" id="description" rows="3"
                                              class="form-control @error('description') is-invalid @enderror"
                                              placeholder="Enter description (optional)">{{ old('description') }}</textarea>
                                    @error('description') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-flex justify-content-end mt-4">
                                <a href="{{ route('contributions.withdrawals.index') }}" class="btn btn-secondary me-2">
                                    <i class="bx bx-arrow-back me-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-info" id="submitBtn">
                                    <i class="bx bx-save me-1"></i> Save Withdrawal
                                </button>
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
                                    Select a contribution product
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Select the customer/member
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Enter the withdrawal amount
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Select the bank account
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Set the transaction date
                                </li>
                            </ul>
                        </div>

                        <hr>

                        <div class="alert alert-info mb-0">
                            <small>
                                <i class="bx bx-info-circle me-1"></i>
                                <strong>Note:</strong> This will create a payment record, update GL transactions (credit bank, debit liability), and decrease the contribution account balance.
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
        // Initialize Select2 for dropdowns
        $('#contribution_product_id, #customer_id, #bank_account_id').select2({
            placeholder: 'Select an option',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5'
        });

        // Form submission handler
        $('#withdrawalForm').on('submit', function(e) {
            const form = $(this)[0];
            const $form = $(this);
            const submitBtn = $('#submitBtn');
            const originalHTML = submitBtn.html();

            // Prevent multiple submissions
            if (form.dataset.submitting === 'true') {
                e.preventDefault();
                return false;
            }

            // Sync Select2 values before submission
            $('#contribution_product_id, #customer_id, #bank_account_id').each(function() {
                if ($(this).data('select2')) {
                    $(this).trigger('change');
                }
            });

            // Ensure CSRF token is present
            let csrfToken = $form.find('input[name="_token"]').val();
            if (!csrfToken) {
                csrfToken = $('meta[name="csrf-token"]').attr('content');
                if (csrfToken) {
                    // Remove any existing duplicate token
                    $form.find('input[name="_token"]').remove();
                    // Add the token
                    $form.prepend('<input type="hidden" name="_token" value="' + csrfToken + '">');
                }
            }

            // Mark form as submitting and show loading state
            form.dataset.submitting = 'true';
            submitBtn.prop('disabled', true);
            submitBtn.html('<i class="bx bx-loader-alt bx-spin me-1"></i> Processing...');

            // Allow form to submit normally - don't prevent default
            // The form will submit with all the data including CSRF token

            // Reset state on timeout (in case submission fails silently)
            setTimeout(function() {
                if (form.dataset.submitting === 'true') {
                    form.dataset.submitting = 'false';
                    submitBtn.prop('disabled', false);
                    submitBtn.html(originalHTML);
                }
            }, 30000);
        });
    });
</script>
@endpush

