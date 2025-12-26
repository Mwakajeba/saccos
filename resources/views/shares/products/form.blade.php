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

        <!-- Share purchase increment -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Share purchase increment</label>
            <input type="number" name="share_purchase_increment" step="0.01" min="0"
                   class="form-control @error('share_purchase_increment') is-invalid @enderror"
                   value="{{ old('share_purchase_increment', $shareProduct->share_purchase_increment ?? '') }}" 
                   placeholder="Purchase increment (e.g., 1000)">
            @error('share_purchase_increment') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <!-- Period Settings Section -->
    <div class="row mt-4">
        <!-- Minimum active period -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Minimum active period <span class="text-danger">*</span></label>
            <input type="number" name="minimum_active_period" min="1"
                   class="form-control @error('minimum_active_period') is-invalid @enderror"
                   value="{{ old('minimum_active_period', $shareProduct->minimum_active_period ?? '') }}" 
                   placeholder="Minimum active period" required>
            @error('minimum_active_period') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Minimum active period type -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Minimum active period type <span class="text-danger">*</span></label>
            <select name="minimum_active_period_type" 
                    class="form-select @error('minimum_active_period_type') is-invalid @enderror" required>
                <option value="">---select---</option>
                @foreach($periodTypes as $key => $value)
                    <option value="{{ $key }}" 
                        {{ old('minimum_active_period_type', $shareProduct->minimum_active_period_type ?? 'Days') == $key ? 'selected' : '' }}>
                        {{ $value }}
                    </option>
                @endforeach
            </select>
            @error('minimum_active_period_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Allow dividends for inactive member -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Allow dividends for inactive member <span class="text-danger">*</span></label>
            <select name="allow_dividends_for_inactive_member" 
                    class="form-select @error('allow_dividends_for_inactive_member') is-invalid @enderror" required>
                <option value="">---select---</option>
                @foreach($yesNoOptions as $key => $value)
                    <option value="{{ $key }}" 
                        {{ old('allow_dividends_for_inactive_member', ($shareProduct->allow_dividends_for_inactive_member ?? false) ? 'Yes' : 'No') == $key ? 'selected' : '' }}>
                        {{ $value }}
                    </option>
                @endforeach
            </select>
            @error('allow_dividends_for_inactive_member') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <!-- Dividend Management Section -->
    <div class="row mt-4">
        <div class="col-12">
            <h5 class="mb-3 text-primary">Dividend Management</h5>
        </div>

        <!-- Dividend rate -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Dividend rate (%)</label>
            <input type="number" name="dividend_rate" step="0.01" min="0" max="100"
                   class="form-control @error('dividend_rate') is-invalid @enderror"
                   value="{{ old('dividend_rate', $shareProduct->dividend_rate ?? '') }}" 
                   placeholder="Annual dividend rate">
            @error('dividend_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Dividend calculation method -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Dividend calculation method</label>
            <select name="dividend_calculation_method" 
                    class="form-select @error('dividend_calculation_method') is-invalid @enderror">
                <option value="">---select---</option>
                @foreach($dividendCalculationMethods ?? [] as $key => $value)
                    <option value="{{ $key }}" 
                        {{ old('dividend_calculation_method', $shareProduct->dividend_calculation_method ?? '') == $key ? 'selected' : '' }}>
                        {{ $value }}
                    </option>
                @endforeach
            </select>
            @error('dividend_calculation_method') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Dividend payment frequency -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Dividend payment frequency</label>
            <select name="dividend_payment_frequency" 
                    class="form-select @error('dividend_payment_frequency') is-invalid @enderror">
                <option value="">---select---</option>
                @foreach($dividendPaymentFrequencies ?? [] as $key => $value)
                    <option value="{{ $key }}" 
                        {{ old('dividend_payment_frequency', $shareProduct->dividend_payment_frequency ?? '') == $key ? 'selected' : '' }}>
                        {{ $value }}
                    </option>
                @endforeach
            </select>
            @error('dividend_payment_frequency') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Dividend payment month -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Dividend payment month</label>
            <select name="dividend_payment_month" 
                    class="form-select @error('dividend_payment_month') is-invalid @enderror">
                <option value="">---select month---</option>
                @foreach($months ?? [] as $key => $value)
                    <option value="{{ $key }}" 
                        {{ old('dividend_payment_month', $shareProduct->dividend_payment_month ?? '') == $key ? 'selected' : '' }}>
                        {{ $value }}
                    </option>
                @endforeach
            </select>
            @error('dividend_payment_month') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Dividend payment day -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Dividend payment day</label>
            <select name="dividend_payment_day" 
                    class="form-select @error('dividend_payment_day') is-invalid @enderror">
                <option value="">---select day---</option>
                @foreach($days ?? [] as $key => $value)
                    <option value="{{ $key }}" 
                        {{ old('dividend_payment_day', $shareProduct->dividend_payment_day ?? '') == $key ? 'selected' : '' }}>
                        {{ $value }}
                    </option>
                @endforeach
            </select>
            @error('dividend_payment_day') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Minimum balance for dividend -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Minimum balance for dividend</label>
            <input type="number" name="minimum_balance_for_dividend" step="0.01" min="0"
                   class="form-control @error('minimum_balance_for_dividend') is-invalid @enderror"
                   value="{{ old('minimum_balance_for_dividend', $shareProduct->minimum_balance_for_dividend ?? '') }}" 
                   placeholder="Minimum balance">
            @error('minimum_balance_for_dividend') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <!-- Lockin Period Section -->
    <div class="row mt-4">
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

    <!-- Subscription & Availability Section -->
    <div class="row mt-4">
        <div class="col-12">
            <h5 class="mb-3 text-primary">Subscription & Availability</h5>
        </div>

        <!-- Opening date -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Opening date</label>
            <input type="date" name="opening_date"
                   class="form-control @error('opening_date') is-invalid @enderror"
                   value="{{ old('opening_date', ($shareProduct->opening_date ?? null) ? (\Carbon\Carbon::parse($shareProduct->opening_date)->format('Y-m-d')) : '') }}">
            @error('opening_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Closing date -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Closing date</label>
            <input type="date" name="closing_date"
                   class="form-control @error('closing_date') is-invalid @enderror"
                   value="{{ old('closing_date', ($shareProduct->closing_date ?? null) ? (\Carbon\Carbon::parse($shareProduct->closing_date)->format('Y-m-d')) : '') }}">
            @error('closing_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Maximum total shares -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Maximum total shares</label>
            <input type="number" name="maximum_total_shares" step="0.01" min="0"
                   class="form-control @error('maximum_total_shares') is-invalid @enderror"
                   value="{{ old('maximum_total_shares', $shareProduct->maximum_total_shares ?? '') }}" 
                   placeholder="Maximum total shares">
            @error('maximum_total_shares') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Allow new subscriptions -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Allow new subscriptions</label>
            <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" name="allow_new_subscriptions" value="1" id="allow_new_subscriptions"
                       {{ old('allow_new_subscriptions', $shareProduct->allow_new_subscriptions ?? true) ? 'checked' : '' }}>
                <label class="form-check-label" for="allow_new_subscriptions">
                    Allow new subscriptions
                </label>
            </div>
            @error('allow_new_subscriptions') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Allow additional purchases -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Allow additional purchases</label>
            <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" name="allow_additional_purchases" value="1" id="allow_additional_purchases"
                       {{ old('allow_additional_purchases', $shareProduct->allow_additional_purchases ?? true) ? 'checked' : '' }}>
                <label class="form-check-label" for="allow_additional_purchases">
                    Allow additional purchases
                </label>
            </div>
            @error('allow_additional_purchases') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <!-- Charges Section -->
    <div class="row mt-4">
        <div class="col-12">
            <h5 class="mb-3 text-primary">Charges</h5>
        </div>
        <div class="col-md-12 mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="has_charges" value="1" id="has_charges"
                       {{ old('has_charges', $shareProduct->has_charges ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="has_charges">
                    Has charges
                </label>
            </div>
        </div>

        <!-- Conditional Charge Fields -->
        <div class="col-md-4 mb-3" id="charge_fields_div" style="display: none;">
            <label class="form-label">Charges <span class="text-danger">*</span></label>
            <select name="charge_id" id="charge_id"
                    class="form-select @error('charge_id') is-invalid @enderror">
                <option value="">---select charges---</option>
                @foreach($fees ?? [] as $fee)
                    <option value="{{ $fee->id }}" 
                        {{ old('charge_id', $shareProduct->charge_id ?? '') == $fee->id ? 'selected' : '' }}>
                        {{ $fee->name }}
                    </option>
                @endforeach
            </select>
            @error('charge_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-4 mb-3" id="charge_type_div" style="display: none;">
            <label class="form-label">Charge Types <span class="text-danger">*</span></label>
            <select name="charge_type" id="charge_type"
                    class="form-select @error('charge_type') is-invalid @enderror">
                <option value="">---select charge type---</option>
                @foreach($chargeTypes ?? [] as $key => $value)
                    <option value="{{ $key }}" 
                        {{ old('charge_type', $shareProduct->charge_type ?? '') == $key ? 'selected' : '' }}>
                        {{ $value }}
                    </option>
                @endforeach
            </select>
            @error('charge_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-4 mb-3" id="charge_amount_div" style="display: none;">
            <label class="form-label">Charge Amount <span class="text-danger">*</span></label>
            <input type="number" name="charge_amount" id="charge_amount" step="0.01" min="0"
                   class="form-control @error('charge_amount') is-invalid @enderror"
                   value="{{ old('charge_amount', $shareProduct->charge_amount ?? '') }}" 
                   placeholder="Charge amount">
            @error('charge_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <!-- Transfer Rules Section -->
    <div class="row mt-4">
        <div class="col-12">
            <h5 class="mb-3 text-primary">Transfer Rules</h5>
        </div>

        <!-- Allow share transfers -->
        <div class="col-md-4 mb-3">
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

        <!-- Transfer fee -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Transfer fee</label>
            <input type="number" name="transfer_fee" step="0.01" min="0"
                   class="form-control @error('transfer_fee') is-invalid @enderror"
                   value="{{ old('transfer_fee', $shareProduct->transfer_fee ?? '') }}" 
                   placeholder="Transfer fee">
            @error('transfer_fee') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Transfer fee type -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Transfer fee type</label>
            <select name="transfer_fee_type" 
                    class="form-select @error('transfer_fee_type') is-invalid @enderror">
                <option value="">---select---</option>
                @foreach($chargeTypes ?? [] as $key => $value)
                    <option value="{{ $key }}" 
                        {{ old('transfer_fee_type', $shareProduct->transfer_fee_type ?? '') == $key ? 'selected' : '' }}>
                        {{ $value }}
                    </option>
                @endforeach
            </select>
            @error('transfer_fee_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <!-- Withdrawal Rules Section -->
    <div class="row mt-4">
        <div class="col-12">
            <h5 class="mb-3 text-primary">Withdrawal Rules</h5>
        </div>

        <!-- Allow share withdrawals -->
        <div class="col-md-4 mb-3">
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

        <!-- Withdrawal fee -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Withdrawal fee</label>
            <input type="number" name="withdrawal_fee" step="0.01" min="0"
                   class="form-control @error('withdrawal_fee') is-invalid @enderror"
                   value="{{ old('withdrawal_fee', $shareProduct->withdrawal_fee ?? '') }}" 
                   placeholder="Withdrawal fee">
            @error('withdrawal_fee') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Withdrawal fee type -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Withdrawal fee type</label>
            <select name="withdrawal_fee_type" 
                    class="form-select @error('withdrawal_fee_type') is-invalid @enderror">
                <option value="">---select---</option>
                @foreach($chargeTypes ?? [] as $key => $value)
                    <option value="{{ $key }}" 
                        {{ old('withdrawal_fee_type', $shareProduct->withdrawal_fee_type ?? '') == $key ? 'selected' : '' }}>
                        {{ $value }}
                    </option>
                @endforeach
            </select>
            @error('withdrawal_fee_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Withdrawal notice period -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Withdrawal notice period</label>
            <input type="number" name="withdrawal_notice_period" min="0"
                   class="form-control @error('withdrawal_notice_period') is-invalid @enderror"
                   value="{{ old('withdrawal_notice_period', $shareProduct->withdrawal_notice_period ?? '') }}" 
                   placeholder="Notice period">
            @error('withdrawal_notice_period') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Withdrawal notice period type -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Withdrawal notice period type</label>
            <select name="withdrawal_notice_period_type" 
                    class="form-select @error('withdrawal_notice_period_type') is-invalid @enderror">
                <option value="">---select---</option>
                @foreach($periodTypes ?? [] as $key => $value)
                    <option value="{{ $key }}" 
                        {{ old('withdrawal_notice_period_type', $shareProduct->withdrawal_notice_period_type ?? '') == $key ? 'selected' : '' }}>
                        {{ $value }}
                    </option>
                @endforeach
            </select>
            @error('withdrawal_notice_period_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Minimum withdrawal amount -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Minimum withdrawal amount</label>
            <input type="number" name="minimum_withdrawal_amount" step="0.01" min="0"
                   class="form-control @error('minimum_withdrawal_amount') is-invalid @enderror"
                   value="{{ old('minimum_withdrawal_amount', $shareProduct->minimum_withdrawal_amount ?? '') }}" 
                   placeholder="Minimum withdrawal">
            @error('minimum_withdrawal_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Maximum withdrawal amount -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Maximum withdrawal amount</label>
            <input type="number" name="maximum_withdrawal_amount" step="0.01" min="0"
                   class="form-control @error('maximum_withdrawal_amount') is-invalid @enderror"
                   value="{{ old('maximum_withdrawal_amount', $shareProduct->maximum_withdrawal_amount ?? '') }}" 
                   placeholder="Maximum withdrawal">
            @error('maximum_withdrawal_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Allow partial withdrawal -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Allow partial withdrawal</label>
            <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" name="allow_partial_withdrawal" value="1" id="allow_partial_withdrawal"
                       {{ old('allow_partial_withdrawal', $shareProduct->allow_partial_withdrawal ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="allow_partial_withdrawal">
                    Allow partial withdrawal
                </label>
            </div>
            @error('allow_partial_withdrawal') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <!-- Bank Account & Journal Reference Section -->
    <div class="row mt-4">
        <div class="col-12">
            <h5 class="mb-3 text-primary">Bank Account & Journal Reference</h5>
        </div>
        <!-- Journal reference (share transfer) - Bank account field removed as per requirements -->
        <div class="col-md-6 mb-3">
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
    </div>

    <!-- Chart Accounts Section -->
    <div class="row mt-4">
        <div class="col-12">
            <h5 class="mb-3 text-primary">Chart Accounts</h5>
        </div>
        <!-- Hrms code (Optional as per user requirement) -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Hrms code</label>
            <input type="text" name="hrms_code" 
                   class="form-control @error('hrms_code') is-invalid @enderror"
                   value="{{ old('hrms_code', $shareProduct->hrms_code ?? '') }}" 
                   placeholder="Hrms code">
            @error('hrms_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
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

        <!-- Share capital account -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Share capital account</label>
            <select name="share_capital_account_id" 
                    class="form-select select2-single @error('share_capital_account_id') is-invalid @enderror">
                <option value="">Select account</option>
                @foreach($chartAccounts as $account)
                    <option value="{{ $account->id }}" 
                        {{ old('share_capital_account_id', $shareProduct->share_capital_account_id ?? '') == $account->id ? 'selected' : '' }}>
                        {{ $account->account_code }} - {{ $account->account_name }}
                    </option>
                @endforeach
            </select>
            @error('share_capital_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Fee Income account -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Fee Income account</label>
            <select name="fee_income_account_id" 
                    class="form-select select2-single @error('fee_income_account_id') is-invalid @enderror">
                <option value="">Select account</option>
                @foreach($chartAccounts as $account)
                    <option value="{{ $account->id }}" 
                        {{ old('fee_income_account_id', $shareProduct->fee_income_account_id ?? '') == $account->id ? 'selected' : '' }}>
                        {{ $account->account_code }} - {{ $account->account_name }}
                    </option>
                @endforeach
            </select>
            @error('fee_income_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
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

        // Function to toggle charge fields visibility
        function toggleChargeFields() {
            var hasCharges = document.getElementById('has_charges');
            var isChecked = hasCharges && hasCharges.checked;
            
            // Toggle visibility of charge fields
            var chargeFieldsDiv = document.getElementById('charge_fields_div');
            var chargeTypeDiv = document.getElementById('charge_type_div');
            var chargeAmountDiv = document.getElementById('charge_amount_div');
            
            if (chargeFieldsDiv) chargeFieldsDiv.style.display = isChecked ? '' : 'none';
            if (chargeTypeDiv) chargeTypeDiv.style.display = isChecked ? '' : 'none';
            if (chargeAmountDiv) chargeAmountDiv.style.display = isChecked ? '' : 'none';
            
            // Make fields required/optional based on checkbox state
            var chargeId = document.getElementById('charge_id');
            var chargeType = document.getElementById('charge_type');
            var chargeAmount = document.getElementById('charge_amount');
            
            if (chargeId) chargeId.required = isChecked;
            if (chargeType) chargeType.required = isChecked;
            if (chargeAmount) chargeAmount.required = isChecked;
            
            // Clear values if unchecked
            if (!isChecked) {
                if (chargeId) chargeId.value = '';
                if (chargeType) chargeType.value = '';
                if (chargeAmount) chargeAmount.value = '';
            }
        }

        // Initialize on page load
        toggleChargeFields();

        // Add event listener to checkbox
        var hasChargesCheckbox = document.getElementById('has_charges');
        if (hasChargesCheckbox) {
            hasChargesCheckbox.addEventListener('change', toggleChargeFields);
        }
    });
</script>
@endpush

