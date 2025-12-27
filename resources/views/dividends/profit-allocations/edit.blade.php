@php
    use Vinkla\Hashids\Facades\Hashids;
@endphp

@extends('layouts.main')

@section('title', 'Edit Profit Allocation')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Shares Management', 'url' => route('shares.management'), 'icon' => 'bx bx-bar-chart-square'],
            ['label' => 'Profit Allocations', 'url' => route('dividends.profit-allocations'), 'icon' => 'bx bx-bar-chart'],
            ['label' => 'Edit', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">EDIT PROFIT ALLOCATION</h6>
            <a href="{{ route('dividends.profit-allocations.show', Hashids::encode($profitAllocation->id)) }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back
            </a>
        </div>
        <hr />

        <div class="card">
            <div class="card-body">
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('dividends.profit-allocations.update', Hashids::encode($profitAllocation->id)) }}" method="POST" id="profitAllocationForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="allocation_date" class="form-label">Allocation Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="allocation_date" name="allocation_date" value="{{ old('allocation_date', $profitAllocation->allocation_date->format('Y-m-d')) }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="financial_year" class="form-label">Financial Year <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="financial_year" name="financial_year" value="{{ old('financial_year', $profitAllocation->financial_year) }}" min="2000" max="2100" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="total_profit" class="form-label">Total Profit <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="total_profit" name="total_profit" step="0.01" min="0" value="{{ old('total_profit', $profitAllocation->total_profit) }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Total Allocation Percentage</label>
                                <input type="text" class="form-control" id="total_percentage" readonly value="0.00%" style="background-color: #f8f9fa;">
                                <small class="text-danger" id="percentage_error"></small>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h6 class="mb-3">Allocation Breakdown</h6>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="statutory_reserve_percentage" class="form-label">Statutory Reserve (%) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control percentage-input" id="statutory_reserve_percentage" name="statutory_reserve_percentage" step="0.01" min="0" max="100" value="{{ old('statutory_reserve_percentage', $profitAllocation->statutory_reserve_percentage) }}" required>
                                <small class="text-muted">Amount: <span id="statutory_reserve_amount_display">0.00</span></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="statutory_reserve_account_id" class="form-label">Statutory Reserve Account <span class="text-danger">*</span></label>
                                <select class="form-select select2-single" id="statutory_reserve_account_id" name="statutory_reserve_account_id" required>
                                    <option value="">Select Account</option>
                                    @foreach($chartAccounts as $account)
                                        <option value="{{ $account->id }}" {{ old('statutory_reserve_account_id', $profitAllocation->statutory_reserve_account_id) == $account->id ? 'selected' : '' }}>
                                            {{ $account->account_name }} ({{ $account->account_code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="education_fund_percentage" class="form-label">Education Fund (%) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control percentage-input" id="education_fund_percentage" name="education_fund_percentage" step="0.01" min="0" max="100" value="{{ old('education_fund_percentage', $profitAllocation->education_fund_percentage) }}" required>
                                <small class="text-muted">Amount: <span id="education_fund_amount_display">0.00</span></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="education_fund_account_id" class="form-label">Education Fund Account</label>
                                <select class="form-select select2-single" id="education_fund_account_id" name="education_fund_account_id">
                                    <option value="">Select Account</option>
                                    @foreach($chartAccounts as $account)
                                        <option value="{{ $account->id }}" {{ old('education_fund_account_id', $profitAllocation->education_fund_account_id) == $account->id ? 'selected' : '' }}>
                                            {{ $account->account_name }} ({{ $account->account_code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="community_fund_percentage" class="form-label">Community Fund (%) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control percentage-input" id="community_fund_percentage" name="community_fund_percentage" step="0.01" min="0" max="100" value="{{ old('community_fund_percentage', $profitAllocation->community_fund_percentage) }}" required>
                                <small class="text-muted">Amount: <span id="community_fund_amount_display">0.00</span></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="community_fund_account_id" class="form-label">Community Fund Account</label>
                                <select class="form-select select2-single" id="community_fund_account_id" name="community_fund_account_id">
                                    <option value="">Select Account</option>
                                    @foreach($chartAccounts as $account)
                                        <option value="{{ $account->id }}" {{ old('community_fund_account_id', $profitAllocation->community_fund_account_id) == $account->id ? 'selected' : '' }}>
                                            {{ $account->account_name }} ({{ $account->account_code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dividend_percentage" class="form-label">Dividend to Members (%) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control percentage-input" id="dividend_percentage" name="dividend_percentage" step="0.01" min="0" max="100" value="{{ old('dividend_percentage', $profitAllocation->dividend_percentage) }}" required>
                                <small class="text-muted">Amount: <span id="dividend_amount_display">0.00</span></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dividend_payable_account_id" class="form-label">Dividend Payable Account <span class="text-danger">*</span></label>
                                <select class="form-select select2-single" id="dividend_payable_account_id" name="dividend_payable_account_id" required>
                                    <option value="">Select Account</option>
                                    @foreach($chartAccounts as $account)
                                        <option value="{{ $account->id }}" {{ old('dividend_payable_account_id', $profitAllocation->dividend_payable_account_id) == $account->id ? 'selected' : '' }}>
                                            {{ $account->account_name }} ({{ $account->account_code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="other_allocation_percentage" class="form-label">Other Allocation (%)</label>
                                <input type="number" class="form-control percentage-input" id="other_allocation_percentage" name="other_allocation_percentage" step="0.01" min="0" max="100" value="{{ old('other_allocation_percentage', $profitAllocation->other_allocation_percentage ?? 0) }}">
                                <small class="text-muted">Amount: <span id="other_allocation_amount_display">0.00</span></small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="other_allocation_account_id" class="form-label">Other Allocation Account</label>
                                <select class="form-select select2-single" id="other_allocation_account_id" name="other_allocation_account_id">
                                    <option value="">Select Account</option>
                                    @foreach($chartAccounts as $account)
                                        <option value="{{ $account->id }}" {{ old('other_allocation_account_id', $profitAllocation->other_allocation_account_id) == $account->id ? 'selected' : '' }}>
                                            {{ $account->account_name }} ({{ $account->account_code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="other_allocation_description" class="form-label">Other Allocation Description</label>
                                <input type="text" class="form-control" id="other_allocation_description" name="other_allocation_description" value="{{ old('other_allocation_description', $profitAllocation->other_allocation_description) }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes', $profitAllocation->notes) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('dividends.profit-allocations.show', Hashids::encode($profitAllocation->id)) }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i> Update Profit Allocation
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
        function calculateAmounts() {
            var totalProfit = parseFloat($('#total_profit').val()) || 0;
            var statutoryReserve = parseFloat($('#statutory_reserve_percentage').val()) || 0;
            var educationFund = parseFloat($('#education_fund_percentage').val()) || 0;
            var communityFund = parseFloat($('#community_fund_percentage').val()) || 0;
            var dividend = parseFloat($('#dividend_percentage').val()) || 0;
            var other = parseFloat($('#other_allocation_percentage').val()) || 0;

            $('#statutory_reserve_amount_display').text((totalProfit * statutoryReserve / 100).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#education_fund_amount_display').text((totalProfit * educationFund / 100).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#community_fund_amount_display').text((totalProfit * communityFund / 100).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#dividend_amount_display').text((totalProfit * dividend / 100).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#other_allocation_amount_display').text((totalProfit * other / 100).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

            var totalPercentage = statutoryReserve + educationFund + communityFund + dividend + other;
            $('#total_percentage').val(totalPercentage.toFixed(2) + '%');

            if (Math.abs(totalPercentage - 100) > 0.01) {
                $('#total_percentage').addClass('is-invalid');
                $('#percentage_error').text('Total must equal 100%');
            } else {
                $('#total_percentage').removeClass('is-invalid');
                $('#percentage_error').text('');
            }
        }

        $('#total_profit, .percentage-input').on('input', calculateAmounts);
        calculateAmounts();

        // Initialize Select2 for account dropdowns
        $('.select2-single').select2({
            placeholder: 'Select Account',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5'
        });
    });
</script>
@endpush

