@extends('layouts.main')

@section('title', 'TRA Tax Depreciation Schedule')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Assets', 'url' => route('assets.index'), 'icon' => 'bx bx-cabinet'],
            ['label' => 'Tax Depreciation Reports', 'url' => '#', 'icon' => 'bx bx-file'],
            ['label' => 'TRA Tax Depreciation Schedule', 'url' => '#', 'icon' => 'bx bx-table']
        ]" />

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">TRA Tax Depreciation Schedule</h4>
                    <div class="page-title-right">
                        <button type="button" class="btn btn-outline-primary" onclick="exportToExcel()">
                            <i class="bx bx-file me-1"></i>Export Excel
                        </button>
                        <button type="button" class="btn btn-outline-secondary ms-2" onclick="window.print()">
                            <i class="bx bx-printer me-1"></i>Print
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Tax Year <span class="text-danger">*</span></label>
                                <input type="number" id="tax_year" name="tax_year" class="form-control" value="{{ $taxYear }}" min="2000" max="2100" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tax Class</label>
                                <select id="tax_class_id" name="tax_class_id" class="form-select select2-single">
                                    <option value="">All Classes</option>
                                    @foreach($taxClasses as $taxClass)
                                        <option value="{{ $taxClass->id }}" {{ $taxClassId == $taxClass->id ? 'selected' : '' }}>
                                            {{ $taxClass->class_code }} - {{ $taxClass->description }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="button" id="generate_report" class="btn btn-primary w-100">
                                    <i class="bx bx-search me-1"></i>Generate Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Results -->
        <div class="row" id="report-results" style="display: none;">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-table me-2"></i>TRA Tax Depreciation Schedule - Year <span id="report-year"></span>
                        </h5>
                    </div>
                    <div class="card-body" id="report-content">
                        <!-- Report will be loaded here -->
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
    $('.select2-single').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });

    $('#generate_report').on('click', function() {
        generateReport();
    });

    function generateReport() {
        const taxYear = $('#tax_year').val();
        const taxClassId = $('#tax_class_id').val();

        if (!taxYear) {
            Swal.fire('Error!', 'Please select a tax year.', 'error');
            return;
        }

        $.ajax({
            url: '{{ route('assets.tax-depreciation.reports.tra-schedule.data') }}',
            type: 'GET',
            data: {
                tax_year: taxYear,
                tax_class_id: taxClassId
            },
            success: function(response) {
                if (response.success && response.data) {
                    renderReport(response.data, taxYear);
                    $('#report-year').text(taxYear);
                    $('#report-results').show();
                } else {
                    Swal.fire('Error!', 'Failed to generate report.', 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error!', 'An error occurred while generating the report.', 'error');
            }
        });
    }

    function renderReport(data, taxYear) {
        let html = '';

        data.forEach(function(classData) {
            html += '<div class="mb-4">';
            html += '<h5 class="border-bottom pb-2">' + classData.tax_class.class_code + ' - ' + classData.tax_class.description + '</h5>';
            html += '<div class="table-responsive">';
            html += '<table class="table table-bordered table-sm">';
            html += '<thead class="table-light">';
            html += '<tr>';
            html += '<th>Asset Code</th><th>Asset Name</th><th class="text-end">Opening WDV</th>';
            html += '<th class="text-end">Additions</th><th class="text-end">Disposals</th>';
            html += '<th class="text-end">Tax Depreciation</th><th class="text-end">Closing WDV</th>';
            html += '</tr></thead><tbody>';

            classData.assets.forEach(function(asset) {
                html += '<tr>';
                html += '<td>' + (asset.asset.code || 'N/A') + '</td>';
                html += '<td>' + (asset.asset.name || 'N/A') + '</td>';
                html += '<td class="text-end">' + formatCurrency(asset.opening_wdv) + '</td>';
                html += '<td class="text-end">' + formatCurrency(asset.additions) + '</td>';
                html += '<td class="text-end">' + formatCurrency(asset.disposals) + '</td>';
                html += '<td class="text-end text-danger">' + formatCurrency(asset.tax_depreciation) + '</td>';
                html += '<td class="text-end">' + formatCurrency(asset.closing_wdv) + '</td>';
                html += '</tr>';
            });

            html += '<tr class="table-warning fw-bold">';
            html += '<td colspan="2">Total</td>';
            html += '<td class="text-end">' + formatCurrency(classData.total_opening_wdv) + '</td>';
            html += '<td class="text-end">' + formatCurrency(classData.total_additions) + '</td>';
            html += '<td class="text-end">' + formatCurrency(classData.total_disposals) + '</td>';
            html += '<td class="text-end">' + formatCurrency(classData.total_tax_depreciation) + '</td>';
            html += '<td class="text-end">' + formatCurrency(classData.total_closing_wdv) + '</td>';
            html += '</tr>';

            html += '</tbody></table></div></div>';
        });

        $('#report-content').html(html);
    }

    function formatCurrency(amount) {
        return 'TZS ' + parseFloat(amount).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function exportToExcel() {
        // TODO: Implement Excel export
        Swal.fire('Info', 'Excel export functionality will be implemented soon.', 'info');
    }
});
</script>
@endpush

