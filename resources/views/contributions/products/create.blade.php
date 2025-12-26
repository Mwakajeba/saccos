@extends('layouts.main')

@section('title', 'Create Contribution Product')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Contributions', 'url' => route('contributions.index'), 'icon' => 'bx bx-donate-heart'],
            ['label' => 'Contribution Products', 'url' => route('contributions.products.index'), 'icon' => 'bx bx-package'],
            ['label' => 'Create Product', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        
        <h6 class="mb-0 text-uppercase">CREATE CONTRIBUTION PRODUCT</h6>
        <hr />

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

        <form id="contributionProductForm" action="{{ route('contributions.products.store') }}" method="POST">
            @csrf
            
            <div class="row">
                <!-- Basic Information -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bx bx-info-circle me-2"></i>Basic Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Product Name <span class="text-danger">*</span></label>
                                    <input type="text" name="product_name" class="form-control @error('product_name') is-invalid @enderror"
                                        value="{{ old('product_name') }}" required>
                                    @error('product_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Interest (%) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="interest" class="form-control @error('interest') is-invalid @enderror"
                                        value="{{ old('interest') }}" required>
                                    @error('interest') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Category <span class="text-danger">*</span></label>
                                    <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                                        <option value="">Select Category</option>
                                        <option value="Voluntary" {{ old('category') == 'Voluntary' ? 'selected' : '' }}>Voluntary</option>
                                        <option value="Mandatory" {{ old('category') == 'Mandatory' ? 'selected' : '' }}>Mandatory</option>
                                    </select>
                                    @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Auto Create <span class="text-danger">*</span></label>
                                    <select name="auto_create" class="form-select @error('auto_create') is-invalid @enderror" required>
                                        <option value="">Select</option>
                                        <option value="Yes" {{ old('auto_create') == 'Yes' ? 'selected' : '' }}>Yes</option>
                                        <option value="No" {{ old('auto_create') == 'No' ? 'selected' : '' }}>No</option>
                                    </select>
                                    @error('auto_create') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Interest Settings -->
                <div class="col-12 mt-3">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="bx bx-calculator me-2"></i>Interest Settings</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Compound Period <span class="text-danger">*</span></label>
                                    <select name="compound_period" class="form-select @error('compound_period') is-invalid @enderror" required>
                                        <option value="">Select</option>
                                        <option value="Daily" {{ old('compound_period') == 'Daily' ? 'selected' : '' }}>Daily</option>
                                        <option value="Monthly" {{ old('compound_period') == 'Monthly' ? 'selected' : '' }}>Monthly</option>
                                    </select>
                                    @error('compound_period') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Interest Posting Period</label>
                                    <select name="interest_posting_period" class="form-select @error('interest_posting_period') is-invalid @enderror">
                                        <option value="">Select</option>
                                        <option value="Monthly" {{ old('interest_posting_period') == 'Monthly' ? 'selected' : '' }}>Monthly</option>
                                        <option value="Quarterly" {{ old('interest_posting_period') == 'Quarterly' ? 'selected' : '' }}>Quarterly</option>
                                        <option value="Annually" {{ old('interest_posting_period') == 'Annually' ? 'selected' : '' }}>Annually</option>
                                    </select>
                                    @error('interest_posting_period') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Interest Calculation Type <span class="text-danger">*</span></label>
                                    <select name="interest_calculation_type" class="form-select @error('interest_calculation_type') is-invalid @enderror" required>
                                        <option value="">Select</option>
                                        <option value="Daily" {{ old('interest_calculation_type') == 'Daily' ? 'selected' : '' }}>Daily</option>
                                        <option value="Monthly" {{ old('interest_calculation_type') == 'Monthly' ? 'selected' : '' }}>Monthly</option>
                                        <option value="Annually" {{ old('interest_calculation_type') == 'Annually' ? 'selected' : '' }}>Annually</option>
                                    </select>
                                    @error('interest_calculation_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lock-in Period Settings -->
                <div class="col-12 mt-3">
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="bx bx-lock me-2"></i>Lock-in Period Settings</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Lockin Period Frequency <span class="text-danger">*</span></label>
                                    <input type="number" name="lockin_period_frequency" class="form-control @error('lockin_period_frequency') is-invalid @enderror"
                                        value="{{ old('lockin_period_frequency') }}" required>
                                    @error('lockin_period_frequency') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Lockin Period Frequency Type <span class="text-danger">*</span></label>
                                    <select name="lockin_period_frequency_type" class="form-select @error('lockin_period_frequency_type') is-invalid @enderror" required>
                                        <option value="">Select</option>
                                        <option value="Days" {{ old('lockin_period_frequency_type') == 'Days' ? 'selected' : '' }}>Days</option>
                                        <option value="Months" {{ old('lockin_period_frequency_type') == 'Months' ? 'selected' : '' }}>Months</option>
                                    </select>
                                    @error('lockin_period_frequency_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Balance Settings -->
                <div class="col-12 mt-3">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bx bx-money me-2"></i>Balance Settings</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Automatic Opening Balance <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="automatic_opening_balance" class="form-control @error('automatic_opening_balance') is-invalid @enderror"
                                        value="{{ old('automatic_opening_balance', 0) }}" required>
                                    @error('automatic_opening_balance') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Minimum Balance for Interest Calculations <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="minimum_balance_for_interest_calculations" class="form-control @error('minimum_balance_for_interest_calculations') is-invalid @enderror"
                                        value="{{ old('minimum_balance_for_interest_calculations', 0) }}" required>
                                    @error('minimum_balance_for_interest_calculations') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="can_withdraw" id="can_withdraw" 
                                            {{ old('can_withdraw') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="can_withdraw">
                                            Can Withdraw
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charges -->
                <div class="col-12 mt-3">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0"><i class="bx bx-receipt me-2"></i>Charges</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="has_charge" id="has_charge" 
                                            {{ old('has_charge') ? 'checked' : '' }} onchange="toggleChargeFields()">
                                        <label class="form-check-label" for="has_charge">
                                            Has Charge
                                        </label>
                                    </div>
                                </div>

                                <div id="chargeFields" style="display: none;">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Charge</label>
                                        <select name="charge_id" class="form-select @error('charge_id') is-invalid @enderror">
                                            <option value="">Select Charge</option>
                                            @foreach(\App\Models\Fee::where('status', 'active')->get() as $fee)
                                            <option value="{{ $fee->id }}" {{ old('charge_id') == $fee->id ? 'selected' : '' }}>
                                                {{ $fee->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('charge_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Charge Type</label>
                                        <select name="charge_type" class="form-select @error('charge_type') is-invalid @enderror">
                                            <option value="">Select</option>
                                            <option value="Fixed" {{ old('charge_type') == 'Fixed' ? 'selected' : '' }}>Fixed</option>
                                            <option value="Percentage" {{ old('charge_type') == 'Percentage' ? 'selected' : '' }}>Percentage</option>
                                        </select>
                                        @error('charge_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Charge Amount</label>
                                        <input type="number" step="0.01" name="charge_amount" class="form-control @error('charge_amount') is-invalid @enderror"
                                            value="{{ old('charge_amount') }}">
                                        @error('charge_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bank Account & Journal Reference -->
                <div class="col-12 mt-3">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="bx bx-bank me-2"></i>Bank Account & Journal Reference</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Bank Account <span class="text-danger">*</span></label>
                                    <select name="bank_account_id" class="form-select @error('bank_account_id') is-invalid @enderror" required>
                                        <option value="">Select Bank Account</option>
                                        @foreach($chartAccounts as $account)
                                        <option value="{{ $account->id }}" {{ old('bank_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->account_name }} ({{ $account->account_code }})
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('bank_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Journal Reference (Contribution Transfer) <span class="text-danger">*</span></label>
                                    <select name="journal_reference_id" class="form-select @error('journal_reference_id') is-invalid @enderror" required>
                                        <option value="">Select Journal Reference</option>
                                        @foreach($journalReferences as $journalRef)
                                        <option value="{{ $journalRef->id }}" {{ old('journal_reference_id') == $journalRef->id ? 'selected' : '' }}>
                                            {{ $journalRef->name }} ({{ $journalRef->reference }})
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('journal_reference_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Riba Journal (Journal ya riba juu ya contribution) <span class="text-danger">*</span></label>
                                    <select name="riba_journal_id" class="form-select @error('riba_journal_id') is-invalid @enderror" required>
                                        <option value="">Select Riba Journal</option>
                                        @foreach($journalReferences as $journalRef)
                                        <option value="{{ $journalRef->id }}" {{ old('riba_journal_id') == $journalRef->id ? 'selected' : '' }}>
                                            {{ $journalRef->name }} ({{ $journalRef->reference }})
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('riba_journal_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Pay Loan Journal (Journal ya kulipa mkopo kwa contribution) <span class="text-danger">*</span></label>
                                    <select name="pay_loan_journal_id" class="form-select @error('pay_loan_journal_id') is-invalid @enderror" required>
                                        <option value="">Select Pay Loan Journal</option>
                                        @foreach($journalReferences as $journalRef)
                                        <option value="{{ $journalRef->id }}" {{ old('pay_loan_journal_id') == $journalRef->id ? 'selected' : '' }}>
                                            {{ $journalRef->name }} ({{ $journalRef->reference }})
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('pay_loan_journal_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chart Accounts -->
                <div class="col-12 mt-3">
                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0"><i class="bx bx-list-ul me-2"></i>Chart Accounts</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Liability Account <span class="text-danger">*</span></label>
                                    <select name="liability_account_id" class="form-select @error('liability_account_id') is-invalid @enderror" required>
                                        <option value="">Select Liability Account</option>
                                        @foreach($chartAccounts as $account)
                                        <option value="{{ $account->id }}" {{ old('liability_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->account_name }} ({{ $account->account_code }})
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('liability_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Expense Account <span class="text-danger">*</span></label>
                                    <select name="expense_account_id" class="form-select @error('expense_account_id') is-invalid @enderror" required>
                                        <option value="">Select Expense Account</option>
                                        @foreach($chartAccounts as $account)
                                        <option value="{{ $account->id }}" {{ old('expense_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->account_name }} ({{ $account->account_code }})
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('expense_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Riba Payable Account <span class="text-danger">*</span></label>
                                    <select name="riba_payable_account_id" class="form-select @error('riba_payable_account_id') is-invalid @enderror" required>
                                        <option value="">Select Riba Payable Account</option>
                                        @foreach($chartAccounts as $account)
                                        <option value="{{ $account->id }}" {{ old('riba_payable_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->account_name }} ({{ $account->account_code }})
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('riba_payable_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Withholding Account <span class="text-danger">*</span></label>
                                    <select name="withholding_account_id" class="form-select @error('withholding_account_id') is-invalid @enderror" required>
                                        <option value="">Select Withholding Account</option>
                                        @foreach($chartAccounts as $account)
                                        <option value="{{ $account->id }}" {{ old('withholding_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->account_name }} ({{ $account->account_code }})
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('withholding_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Withholding Percentage</label>
                                    <input type="number" step="0.01" name="withholding_percentage" class="form-control @error('withholding_percentage') is-invalid @enderror"
                                        value="{{ old('withholding_percentage') }}">
                                    @error('withholding_percentage') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Riba Payable Journal <span class="text-danger">*</span></label>
                                    <select name="riba_payable_journal_id" class="form-select @error('riba_payable_journal_id') is-invalid @enderror" required>
                                        <option value="">Select Riba Payable Journal</option>
                                        @foreach($journalReferences as $journalRef)
                                        <option value="{{ $journalRef->id }}" {{ old('riba_payable_journal_id') == $journalRef->id ? 'selected' : '' }}>
                                            {{ $journalRef->name }} ({{ $journalRef->reference }})
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('riba_payable_journal_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="col-12 mt-4">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('contributions.products.index') }}" class="btn btn-secondary">
                            <i class="bx bx-x me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i> Save Product
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleChargeFields() {
        const hasCharge = document.getElementById('has_charge').checked;
        const chargeFields = document.getElementById('chargeFields');
        
        if (hasCharge) {
            chargeFields.style.display = 'block';
            // Make charge fields required
            chargeFields.querySelectorAll('select, input').forEach(field => {
                if (field.name !== 'charge_id') {
                    field.setAttribute('required', 'required');
                }
            });
        } else {
            chargeFields.style.display = 'none';
            // Remove required attribute
            chargeFields.querySelectorAll('select, input').forEach(field => {
                field.removeAttribute('required');
                field.value = '';
            });
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleChargeFields();
        
        // Handle form submission via AJAX
        const form = document.getElementById('contributionProductForm');
        const submitBtn = document.getElementById('submitBtn');
        const cancelBtn = document.getElementById('cancelBtn');
        const btnText = submitBtn.querySelector('.btn-text');
        const originalText = btnText.textContent;
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Disable buttons
            submitBtn.disabled = true;
            cancelBtn.style.pointerEvents = 'none';
            cancelBtn.style.opacity = '0.6';
            
            // Show loading state
            btnText.textContent = 'Saving...';
            submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i> <span class="btn-text">Saving...</span>';
            
            // Show SweetAlert loading
            Swal.fire({
                title: 'Processing...',
                html: 'Please wait while we save the contribution product.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Prepare form data
            const formData = new FormData(form);
            
            // Submit via AJAX
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                if (response.ok) {
                    return response.json().catch(() => {
                        // If response is not JSON (redirect), return success
                        return { success: true, redirect: true };
                    });
                } else {
                    return response.json().then(data => {
                        throw { validation: true, errors: data.errors || data };
                    });
                }
            })
            .then(data => {
                if (data.redirect || data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message || 'Contribution product created successfully!',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = '{{ route("contributions.products.index") }}';
                    });
                } else {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message || 'Contribution product created successfully!',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = '{{ route("contributions.products.index") }}';
                    });
                }
            })
            .catch(error => {
                // Re-enable buttons
                submitBtn.disabled = false;
                cancelBtn.style.pointerEvents = 'auto';
                cancelBtn.style.opacity = '1';
                btnText.textContent = originalText;
                submitBtn.innerHTML = '<i class="bx bx-save me-1"></i> <span class="btn-text">' + originalText + '</span>';
                
                if (error.validation) {
                    // Handle validation errors
                    let errorMessage = 'Please fix the following errors:\n\n';
                    if (error.errors) {
                        if (typeof error.errors === 'object') {
                            Object.keys(error.errors).forEach(key => {
                                errorMessage += `• ${error.errors[key][0]}\n`;
                            });
                        } else {
                            errorMessage += error.errors;
                        }
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: errorMessage,
                        confirmButtonText: 'OK'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'An error occurred while saving the product. Please try again.',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });
    });
</script>
@endpush

