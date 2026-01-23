@php
    use Vinkla\Hashids\Facades\Hashids;
    $isEdit = isset($shareProduct);
@endphp

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

<form action="{{ $isEdit ? route('shares.products.update', Hashids::encode($shareProduct->id)) : route('shares.products.store') }}" 
      method="POST">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <!-- General Details Section -->
    <div class="row">
        <div class="col-12">
            <h5 class="mb-3 text-primary">Add share</h5>
        </div>

        <!-- Share name -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Share name <span class="text-danger">*</span></label>
            <input type="text" name="share_name" 
                   class="form-control @error('share_name') is-invalid @enderror"
                   value="{{ old('share_name', $shareProduct->share_name ?? '') }}" 
                   placeholder="Share name" required>
            @error('share_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Required share -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Required share <span class="text-danger">*</span></label>
            <input type="number" name="required_share" step="0.01" min="0"
                   class="form-control @error('required_share') is-invalid @enderror"
                   value="{{ old('required_share', $shareProduct->required_share ?? '') }}" 
                   placeholder="Required share" required>
            @error('required_share') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Nominal price -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Nominal price <span class="text-danger">*</span></label>
            <input type="number" name="nominal_price" step="0.01" min="0"
                   class="form-control @error('nominal_price') is-invalid @enderror"
                   value="{{ old('nominal_price', $shareProduct->nominal_price ?? '') }}" 
                   placeholder="Nominal price" required>
            @error('nominal_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <!-- Share Purchase Limits Section -->
    <div class="row mt-4">
        <div class="col-12">
            <h5 class="mb-3 text-primary">Share Purchase Limits</h5>
        </div>

        <!-- Minimum purchase amount -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Minimum purchase amount</label>
            <input type="number" name="minimum_purchase_amount" step="0.01" min="0"
                   class="form-control @error('minimum_purchase_amount') is-invalid @enderror"
                   value="{{ old('minimum_purchase_amount', $shareProduct->minimum_purchase_amount ?? '') }}" 
                   placeholder="Minimum purchase amount">
            @error('minimum_purchase_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Maximum purchase amount -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Maximum purchase amount</label>
            <input type="number" name="maximum_purchase_amount" step="0.01" min="0"
                   class="form-control @error('maximum_purchase_amount') is-invalid @enderror"
                   value="{{ old('maximum_purchase_amount', $shareProduct->maximum_purchase_amount ?? '') }}" 
                   placeholder="Maximum purchase amount">
            @error('maximum_purchase_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Maximum shares per member -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Maximum shares per member</label>
            <input type="number" name="maximum_shares_per_member" step="0.01" min="0"
                   class="form-control @error('maximum_shares_per_member') is-invalid @enderror"
                   value="{{ old('maximum_shares_per_member', $shareProduct->maximum_shares_per_member ?? '') }}" 
                   placeholder="Maximum shares per member">
            @error('maximum_shares_per_member') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Minimum shares for membership -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Minimum shares for membership</label>
            <input type="number" name="minimum_shares_for_membership" step="0.01" min="0"
                   class="form-control @error('minimum_shares_for_membership') is-invalid @enderror"
                   value="{{ old('minimum_shares_for_membership', $shareProduct->minimum_shares_for_membership ?? '') }}" 
                   placeholder="Minimum shares for membership">
            @error('minimum_shares_for_membership') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <!-- Lockin Period Section -->
    <div class="row mt-4">
        <div class="col-12">
            <h5 class="mb-3 text-primary">Lockin Period</h5>
        </div>

        <!-- Lockin period frequency -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Lockin period frequency <span class="text-danger">*</span></label>
            <input type="number" name="lockin_period_frequency" min="1"
                   class="form-control @error('lockin_period_frequency') is-invalid @enderror"
                   value="{{ old('lockin_period_frequency', $shareProduct->lockin_period_frequency ?? '') }}" 
                   placeholder="Lockin period frequency" required>
            @error('lockin_period_frequency') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Lockin period frequency type -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Lockin period frequency type <span class="text-danger">*</span></label>
            <select name="lockin_period_frequency_type" 
                    class="form-select @error('lockin_period_frequency_type') is-invalid @enderror" required>
                <option value="">---select---</option>
                @foreach($periodTypes as $key => $value)
                    <option value="{{ $key }}" 
                        {{ old('lockin_period_frequency_type', $shareProduct->lockin_period_frequency_type ?? 'Days') == $key ? 'selected' : '' }}>
                        {{ $value }}
                    </option>
                @endforeach
            </select>
            @error('lockin_period_frequency_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <!-- Description Section -->
    <div class="row mt-4">
        <!-- Description -->
        <div class="col-md-12 mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" rows="3"
                      class="form-control @error('description') is-invalid @enderror"
                      placeholder="Description">{{ old('description', $shareProduct->description ?? '') }}</textarea>
            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <!-- Certificate Settings Section -->
    <div class="row mt-4">
        <div class="col-12">
            <h5 class="mb-3 text-primary">Certificate Settings</h5>
        </div>

        <!-- Certificate number prefix -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Certificate number prefix</label>
            <input type="text" name="certificate_number_prefix" maxlength="20"
                   class="form-control @error('certificate_number_prefix') is-invalid @enderror"
                   value="{{ old('certificate_number_prefix', $shareProduct->certificate_number_prefix ?? '') }}" 
                   placeholder="e.g., SC, SACCO">
            @error('certificate_number_prefix') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Certificate number format -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Certificate number format</label>
            <input type="text" name="certificate_number_format" maxlength="100"
                   class="form-control @error('certificate_number_format') is-invalid @enderror"
                   value="{{ old('certificate_number_format', $shareProduct->certificate_number_format ?? '') }}" 
                   placeholder="e.g., {PREFIX}-{YEAR}-{NUMBER}">
            @error('certificate_number_format') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Auto generate certificate -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Auto generate certificate</label>
            <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" name="auto_generate_certificate" value="1" id="auto_generate_certificate"
                       {{ old('auto_generate_certificate', $shareProduct->auto_generate_certificate ?? true) ? 'checked' : '' }}>
                <label class="form-check-label" for="auto_generate_certificate">
                    Auto generate certificate numbers
                </label>
            </div>
            @error('auto_generate_certificate') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <!-- Transfer & Withdrawal Rules Section -->
    <div class="row mt-4">
        <div class="col-12">
            <h5 class="mb-3 text-primary">Transfer & Withdrawal Rules</h5>
        </div>

        <!-- Allow share transfers -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Allow share transfers</label>
            <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" name="allow_share_transfers" value="1" id="allow_share_transfers"
                       {{ old('allow_share_transfers', $shareProduct->allow_share_transfers ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="allow_share_transfers">
                    Allow share transfers
                </label>
            </div>
            @error('allow_share_transfers') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Allow share withdrawals -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Allow share withdrawals</label>
            <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" name="allow_share_withdrawals" value="1" id="allow_share_withdrawals"
                       {{ old('allow_share_withdrawals', $shareProduct->allow_share_withdrawals ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="allow_share_withdrawals">
                    Allow share withdrawals
                </label>
            </div>
            @error('allow_share_withdrawals') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <!-- Accounts & Journal Reference Section -->
    <div class="row mt-4">
        <div class="col-12">
            <h5 class="mb-3 text-primary">Accounts & Journal Reference</h5>
        </div>

        <!-- Journal reference -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Journal reference (share transfer) <span class="text-danger">*</span></label>
            <select name="journal_reference_id" 
                    class="form-select select2-single @error('journal_reference_id') is-invalid @enderror" required>
                <option value="">---select journal reference---</option>
                @foreach($journalReferences ?? [] as $journalRef)
                    <option value="{{ $journalRef->id }}" 
                        {{ old('journal_reference_id', $shareProduct->journal_reference_id ?? '') == $journalRef->id ? 'selected' : '' }}>
                        {{ $journalRef->name }} ({{ $journalRef->reference }})
                    </option>
                @endforeach
            </select>
            @error('journal_reference_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Liability account -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Liability account <span class="text-danger">*</span></label>
            <select name="liability_account_id" 
                    class="form-select select2-single @error('liability_account_id') is-invalid @enderror" required>
                <option value="">Select account</option>
                @foreach($chartAccounts as $account)
                    <option value="{{ $account->id }}" 
                        {{ old('liability_account_id', $shareProduct->liability_account_id ?? '') == $account->id ? 'selected' : '' }}>
                        {{ $account->account_code }} - {{ $account->account_name }}
                    </option>
                @endforeach
            </select>
            @error('liability_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Hrms code -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Hrms code</label>
            <input type="text" name="hrms_code" 
                   class="form-control @error('hrms_code') is-invalid @enderror"
                   value="{{ old('hrms_code', $shareProduct->hrms_code ?? '') }}" 
                   placeholder="Hrms code">
            @error('hrms_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Select2 for chart account dropdowns
        $('.select2-single').select2({
            placeholder: 'Select an option',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5'
        });
    });
</script>
@endpush

