@extends('layouts.main')

@section('title', 'Cash Flows')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Investment', 'url' => '#', 'icon' => 'bx bx-trending-up'],
            ['label' => 'Cash Flows', 'url' => '#', 'icon' => 'bx bx-money']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">UTT INVESTMENT CASH FLOWS</h6>
        </div>
        <hr />

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped nowrap" id="cashFlowsTable">
                        <thead>
                            <tr>
                                <th>Fund</th>
                                <th>Type</th>
                                <th>Transaction Date</th>
                                <th>Amount</th>
                                <th>Direction</th>
                                <th>Classification</th>
                                <th>Reference</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded via Ajax -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        var table = $('#cashFlowsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("investments.cash-flows.data") }}',
                type: 'GET',
                error: function(xhr, error, code) {
                    console.error('DataTables Ajax Error:', error, code);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to load cash flows data. Please refresh the page.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            columns: [
                { data: 'fund_name', name: 'fund_name', title: 'Fund' },
                { data: 'type_badge', name: 'cash_flow_type', title: 'Type' },
                { data: 'transaction_date', name: 'transaction_date', title: 'Transaction Date' },
                { data: 'amount_formatted', name: 'amount', title: 'Amount' },
                { data: 'direction_badge', name: 'flow_direction', title: 'Direction' },
                { data: 'classification_badge', name: 'classification', title: 'Classification' },
                { data: 'reference_number', name: 'reference_number', title: 'Reference' }
            ],
            responsive: true,
            order: [[2, 'desc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            language: {
                search: "",
                searchPlaceholder: "Search cash flows...",
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
            }
        });
    });
</script>
@endpush

