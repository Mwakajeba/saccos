@extends('layouts.main')

@section('title', 'Enter NAV Price')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Investment', 'url' => '#', 'icon' => 'bx bx-trending-up'],
            ['label' => 'NAV Prices', 'url' => route('investments.nav-prices.index'), 'icon' => 'bx bx-line-chart'],
            ['label' => 'Enter NAV Price', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">ENTER NAV PRICE</h6>
            <a href="{{ route('investments.nav-prices.index') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back
            </a>
        </div>
        <hr />

        <div class="row">
            <!-- Left Column: Form -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{ route('investments.nav-prices.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="utt_fund_id" class="form-label">UTT Fund <span class="text-danger">*</span></label>
                                        <select class="form-select" id="utt_fund_id" name="utt_fund_id" required>
                                            <option value="">Select Fund</option>
                                            @foreach($funds as $fund)
                                                <option value="{{ $fund->id }}" {{ old('utt_fund_id') == $fund->id ? 'selected' : '' }}>{{ $fund->fund_name }} ({{ $fund->fund_code }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nav_date" class="form-label">NAV Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="nav_date" name="nav_date" value="{{ old('nav_date', date('Y-m-d')) }}" required>
                                        <small class="form-text text-muted">Cannot be a future date</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nav_per_unit" class="form-label">NAV per Unit <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="nav_per_unit" name="nav_per_unit" value="{{ old('nav_per_unit') }}" step="0.0001" min="0.0001" required>
                                        <small class="form-text text-muted">Must be greater than 0</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="notes" class="form-label">Notes</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('investments.nav-prices.index') }}" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Enter NAV Price
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Column: Information & Guidelines -->
            <div class="col-lg-4">
                <div class="card border-info">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Information & Guidelines</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h6 class="text-primary"><i class="bx bx-help-circle me-2"></i>How to Enter NAV Price</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Select the <strong>UTT Fund</strong> for which you want to enter the NAV
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Choose the <strong>NAV Date</strong> (cannot be a future date)
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Enter the <strong>NAV per Unit</strong> value (must be greater than 0)
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Optionally add <strong>Notes</strong> for reference
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Click "Enter NAV Price" to save
                                </li>
                            </ul>
                        </div>

                        <hr>

                        <div class="mb-4">
                            <h6 class="text-primary"><i class="bx bx-info-circle me-2"></i>Important Notes</h6>
                            <div class="alert alert-warning mb-2">
                                <i class="bx bx-error-circle me-2"></i>
                                <strong>Unique Constraint:</strong> Only one NAV per fund per date is allowed. If a NAV already exists for the selected fund and date, you will need to use a different date.
                            </div>
                            <div class="mb-2">
                                <i class="bx bx-calendar text-info me-2"></i>
                                <strong>Date Restriction:</strong> NAV dates cannot be in the future. Use today's date or a past date.
                            </div>
                            <div class="mb-2">
                                <i class="bx bx-calculator text-info me-2"></i>
                                <strong>NAV Value:</strong> The NAV per unit must be a positive decimal number (minimum 0.0001).
                            </div>
                        </div>

                        <hr>

                        <div class="mb-4">
                            <h6 class="text-primary"><i class="bx bx-lightbulb me-2"></i>Best Practices</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="bx bx-check text-info me-2"></i>
                                    Enter NAV prices daily for accurate portfolio valuation
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check text-info me-2"></i>
                                    Verify NAV values before submission to ensure accuracy
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check text-info me-2"></i>
                                    Use notes field to document any special circumstances
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check text-info me-2"></i>
                                    Review existing NAV prices to avoid duplicate entries
                                </li>
                            </ul>
                        </div>

                        <div class="alert alert-info mb-0">
                            <i class="bx bx-info-circle me-2"></i>
                            <strong>Tip:</strong> NAV prices are used to calculate the current value of holdings and unrealized gains/losses. Ensure accuracy for proper financial reporting.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

