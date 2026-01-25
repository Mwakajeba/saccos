@extends('layouts.main')

@section('title', 'Book vs Tax Reconciliation')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Assets', 'url' => route('assets.index'), 'icon' => 'bx bx-cabinet'],
            ['label' => 'Tax Depreciation Reports', 'url' => '#', 'icon' => 'bx bx-file'],
            ['label' => 'Book vs Tax Reconciliation', 'url' => '#', 'icon' => 'bx bx-line-chart']
        ]" />

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Book vs Tax Reconciliation Report</h4>
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
                                <label class="form-label">As Of Date <span class="text-danger">*</span></label>
                                <input type="date" id="as_of_date" name="as_of_date" class="form-control" value="{{ $asOfDate }}" required>
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
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-line-chart me-2"></i>Book vs Tax Reconciliation - As Of <span id="report-date"></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="reconciliation-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Asset Code</th>
                                        <th>Asset Name</th>
                                        <th>Tax Class</th>
                                        <th class="text-end">Book Cost</th>
                                        <th class="text-end">Book Accum Dep</th>
                                        <th class="text-end">Book NBV</th>
                                        <th class="text-end">Tax Cost</th>
                                        <th class="text-end">Tax Accum Dep</th>
                                        <th class="text-end">Tax WDV</th>
                                        <th class="text-end">Temporary Difference</th>
                                        <th class="text-end">Depreciation Difference</th>
                                    </tr>
                                </thead>
                                <tbody id="reconciliation-tbody">
                                    <!-- Data will be loaded here -->
                                </tbody>
                            </table>
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
    $('#generate_report').on('click', function() {
        generateReport();
    });

    function generateReport() {
        const asOfDate = $('#as_of_date').val();

        if (!asOfDate) {
            Swal.fire('Error!', 'Please select a date.', 'error');
            return;
        }

        $.ajax({
            url: '{{ route('assets.tax-depreciation.reports.book-tax-reconciliation.data') }}',
            type: 'GET',
            data: {
                as_of_date: asOfDate
            },
            success: function(response) {
                if (response.success && response.data) {
                    renderReport(response.data, asOfDate);
                    $('#report-date').text(response.as_of_date);
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

    function renderReport(data, asOfDate) {
        let html = '';
        let totals = {
            book_cost: 0,
            book_accum_dep: 0,
            book_nbv: 0,
            tax_cost: 0,
            tax_accum_dep: 0,
            tax_wdv: 0,
            temporary_difference: 0,
            depreciation_difference: 0
        };

        data.forEach(function(item) {
            html += '<tr>';
            html += '<td>' + (item.asset.code || 'N/A') + '</td>';
            html += '<td>' + (item.asset.name || 'N/A') + '</td>';
            html += '<td>' + (item.asset.tax_class ? item.asset.tax_class.class_code : 'N/A') + '</td>';
            html += '<td class="text-end">' + formatCurrency(item.book_cost) + '</td>';
            html += '<td class="text-end">' + formatCurrency(item.book_accum_dep) + '</td>';
            html += '<td class="text-end">' + formatCurrency(item.book_nbv) + '</td>';
            html += '<td class="text-end">' + formatCurrency(item.tax_cost) + '</td>';
            html += '<td class="text-end">' + formatCurrency(item.tax_accum_dep) + '</td>';
            html += '<td class="text-end">' + formatCurrency(item.tax_wdv) + '</td>';
            
            const tempDiffClass = item.temporary_difference >= 0 ? 'text-danger' : 'text-success';
            html += '<td class="text-end ' + tempDiffClass + '">' + formatCurrency(item.temporary_difference) + '</td>';
            
            const depDiffClass = item.depreciation_difference >= 0 ? 'text-success' : 'text-danger';
            html += '<td class="text-end ' + depDiffClass + '">' + formatCurrency(item.depreciation_difference) + '</td>';
            html += '</tr>';

            totals.book_cost += parseFloat(item.book_cost);
            totals.book_accum_dep += parseFloat(item.book_accum_dep);
            totals.book_nbv += parseFloat(item.book_nbv);
            totals.tax_cost += parseFloat(item.tax_cost);
            totals.tax_accum_dep += parseFloat(item.tax_accum_dep);
            totals.tax_wdv += parseFloat(item.tax_wdv);
            totals.temporary_difference += parseFloat(item.temporary_difference);
            totals.depreciation_difference += parseFloat(item.depreciation_difference);
        });

        // Add totals row
        html += '<tr class="table-warning fw-bold">';
        html += '<td colspan="3">Total</td>';
        html += '<td class="text-end">' + formatCurrency(totals.book_cost) + '</td>';
        html += '<td class="text-end">' + formatCurrency(totals.book_accum_dep) + '</td>';
        html += '<td class="text-end">' + formatCurrency(totals.book_nbv) + '</td>';
        html += '<td class="text-end">' + formatCurrency(totals.tax_cost) + '</td>';
        html += '<td class="text-end">' + formatCurrency(totals.tax_accum_dep) + '</td>';
        html += '<td class="text-end">' + formatCurrency(totals.tax_wdv) + '</td>';
        
        const tempDiffTotalClass = totals.temporary_difference >= 0 ? 'text-danger' : 'text-success';
        html += '<td class="text-end ' + tempDiffTotalClass + '">' + formatCurrency(totals.temporary_difference) + '</td>';
        
        const depDiffTotalClass = totals.depreciation_difference >= 0 ? 'text-success' : 'text-danger';
        html += '<td class="text-end ' + depDiffTotalClass + '">' + formatCurrency(totals.depreciation_difference) + '</td>';
        html += '</tr>';

        $('#reconciliation-tbody').html(html);
    }

    function formatCurrency(amount) {
        return 'TZS ' + parseFloat(amount).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function exportToExcel() {
        Swal.fire('Info', 'Excel export functionality will be implemented soon.', 'info');
    }
});
</script>
@endpush

