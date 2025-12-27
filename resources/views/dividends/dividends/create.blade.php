@extends('layouts.main')

@section('title', 'Create Dividend')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Shares Management', 'url' => route('shares.management'), 'icon' => 'bx bx-bar-chart-square'],
            ['label' => 'Dividends', 'url' => route('dividends.dividends'), 'icon' => 'bx bx-dollar'],
            ['label' => 'Create', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">CREATE DIVIDEND</h6>
            <a href="{{ route('dividends.dividends') }}" class="btn btn-secondary">
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

                <form action="{{ route('dividends.dividends.store') }}" method="POST" id="dividendForm">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="profit_allocation_id" class="form-label">Profit Allocation</label>
                                <select class="form-select" id="profit_allocation_id" name="profit_allocation_id">
                                    <option value="">Select Profit Allocation (Optional)</option>
                                    @foreach($profitAllocations as $allocation)
                                        <option value="{{ $allocation->id }}" {{ old('profit_allocation_id') == $allocation->id ? 'selected' : '' }}>
                                            {{ $allocation->financial_year }} - {{ number_format($allocation->dividend_amount, 2) }} ({{ $allocation->reference_number ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Select a profit allocation to link this dividend to (optional)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="share_product_id" class="form-label">Share Product <span class="text-danger">*</span></label>
                                <select class="form-select @error('share_product_id') is-invalid @enderror" id="share_product_id" name="share_product_id" required>
                                    <option value="">Select Share Product</option>
                                    @foreach($shareProducts as $product)
                                        <option value="{{ $product->id }}" {{ old('share_product_id') == $product->id ? 'selected' : '' }}>
                                            {{ $product->share_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('share_product_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="declaration_date" class="form-label">Declaration Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('declaration_date') is-invalid @enderror" id="declaration_date" name="declaration_date" value="{{ old('declaration_date', date('Y-m-d')) }}" required>
                                @error('declaration_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="financial_year" class="form-label">Financial Year <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('financial_year') is-invalid @enderror" id="financial_year" name="financial_year" value="{{ old('financial_year', date('Y')) }}" min="2000" max="2100" required>
                                @error('financial_year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="total_dividend_amount" class="form-label">Total Dividend Amount <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('total_dividend_amount') is-invalid @enderror" id="total_dividend_amount" name="total_dividend_amount" step="0.01" min="0" value="{{ old('total_dividend_amount') }}" required>
                                @error('total_dividend_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dividend_rate" class="form-label">Dividend Rate (%)</label>
                                <input type="number" class="form-control @error('dividend_rate') is-invalid @enderror" id="dividend_rate" name="dividend_rate" step="0.01" min="0" max="100" value="{{ old('dividend_rate') }}">
                                <small class="text-muted">Optional: Percentage rate for dividend calculation</small>
                                @error('dividend_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="calculation_method" class="form-label">Calculation Method <span class="text-danger">*</span></label>
                                <select class="form-select @error('calculation_method') is-invalid @enderror" id="calculation_method" name="calculation_method" required>
                                    <option value="">Select Calculation Method</option>
                                    <option value="on_share_capital" {{ old('calculation_method') == 'on_share_capital' ? 'selected' : '' }}>On Share Capital</option>
                                    <option value="on_share_value" {{ old('calculation_method') == 'on_share_value' ? 'selected' : '' }}>On Share Value</option>
                                    <option value="on_minimum_balance" {{ old('calculation_method') == 'on_minimum_balance' ? 'selected' : '' }}>On Minimum Balance</option>
                                    <option value="on_average_balance" {{ old('calculation_method') == 'on_average_balance' ? 'selected' : '' }}>On Average Balance</option>
                                </select>
                                @error('calculation_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('dividends.dividends') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i> Create Dividend
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
        // Auto-fill total_dividend_amount from profit allocation if selected
        $('#profit_allocation_id').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            if (selectedOption.val()) {
                // Extract amount from option text (format: "2025 - 1,000,000.00 (...)")
                var text = selectedOption.text();
                var match = text.match(/([\d,]+\.\d{2})/);
                if (match) {
                    var amount = match[1].replace(/,/g, '');
                    $('#total_dividend_amount').val(amount);
                }
            }
        });
    });
</script>
@endpush

