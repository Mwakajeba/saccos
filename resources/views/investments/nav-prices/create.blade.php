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

                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Note:</strong> Only one NAV per fund per date is allowed. If a NAV already exists for the selected fund and date, you will need to use a different date.
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
</div>
@endsection

