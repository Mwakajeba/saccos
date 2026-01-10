@extends('layouts.main')

@section('title', 'Create Reconciliation')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Investment', 'url' => '#', 'icon' => 'bx bx-trending-up'],
            ['label' => 'Reconciliations', 'url' => route('investments.reconciliations.index'), 'icon' => 'bx bx-check-square'],
            ['label' => 'Create Reconciliation', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">CREATE RECONCILIATION</h6>
            <a href="{{ route('investments.reconciliations.index') }}" class="btn btn-secondary">
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

                <form action="{{ route('investments.reconciliations.store') }}" method="POST" id="reconciliationForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sacco_utt_holding_id" class="form-label">Select Holding <span class="text-danger">*</span></label>
                                <select class="form-select" id="sacco_utt_holding_id" name="sacco_utt_holding_id" required>
                                    <option value="">Select Holding</option>
                                    @foreach($holdings as $holding)
                                        <option value="{{ $holding->id }}" data-fund-id="{{ $holding->utt_fund_id }}" data-system-units="{{ $holding->total_units }}">
                                            {{ $holding->uttFund->fund_name }} ({{ $holding->uttFund->fund_code }}) - Units: {{ number_format($holding->total_units, 4) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="reconciliation_date" class="form-label">Reconciliation Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="reconciliation_date" name="reconciliation_date" value="{{ old('reconciliation_date', date('Y-m-d')) }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="statement_units" class="form-label">Statement Units <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="statement_units" name="statement_units" value="{{ old('statement_units') }}" step="0.0001" min="0" required>
                                <small class="form-text text-muted">Units from UTT statement</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="system_units_display" class="form-label">System Units</label>
                                <input type="text" class="form-control" id="system_units_display" readonly>
                                <small class="form-text text-muted">Current units in system (read-only)</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="reconciliation_notes" class="form-label">Reconciliation Notes</label>
                        <textarea class="form-control" id="reconciliation_notes" name="reconciliation_notes" rows="3">{{ old('reconciliation_notes') }}</textarea>
                    </div>

                    <input type="hidden" id="utt_fund_id" name="utt_fund_id">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('investments.reconciliations.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i> Create Reconciliation
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
        $('#sacco_utt_holding_id').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            var fundId = selectedOption.data('fund-id');
            var systemUnits = selectedOption.data('system-units');
            
            $('#utt_fund_id').val(fundId);
            $('#system_units_display').val(systemUnits ? parseFloat(systemUnits).toFixed(4) : '0.0000');
        });
    });
</script>
@endpush

