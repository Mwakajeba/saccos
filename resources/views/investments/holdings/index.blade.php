@extends('layouts.main')

@section('title', 'Holdings Register')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Investment', 'url' => '#', 'icon' => 'bx bx-trending-up'],
            ['label' => 'Holdings Register', 'url' => '#', 'icon' => 'bx bx-bar-chart']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">SACCO UTT HOLDINGS REGISTER</h6>
        </div>
        <hr />

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped nowrap" id="holdingsTable">
                        <thead>
                            <tr>
                                <th>Fund Name</th>
                                <th>Fund Code</th>
                                <th>Total Units</th>
                                <th>Average Cost</th>
                                <th>Current NAV</th>
                                <th>Current Value</th>
                                <th>Unrealized Gain/Loss</th>
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
        var table = $('#holdingsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("investments.holdings.data") }}',
                type: 'GET',
                error: function(xhr, error, code) {
                    console.error('DataTables Ajax Error:', error, code);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to load holdings data. Please refresh the page.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            columns: [
                { data: 'fund_name', name: 'fund_name', title: 'Fund Name' },
                { data: 'fund_code', name: 'fund_code', title: 'Fund Code' },
                { data: 'total_units_formatted', name: 'total_units', title: 'Total Units' },
                { data: 'average_cost_formatted', name: 'average_acquisition_cost', title: 'Average Cost' },
                { data: 'current_nav', name: 'current_nav', title: 'Current NAV' },
                { data: 'current_value', name: 'current_value', title: 'Current Value' },
                { data: 'unrealized_gain', name: 'unrealized_gain', title: 'Unrealized Gain/Loss' }
            ],
            responsive: true,
            order: [[0, 'asc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            language: {
                search: "",
                searchPlaceholder: "Search holdings...",
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
            }
        });
    });
</script>
@endpush

