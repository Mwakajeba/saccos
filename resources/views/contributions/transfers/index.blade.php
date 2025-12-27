@extends('layouts.main')

@section('title', 'Contributions Transfers')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Contributions', 'url' => route('contributions.index'), 'icon' => 'bx bx-donate-heart'],
            ['label' => 'Transfers', 'url' => '#', 'icon' => 'bx bx-transfer']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase text-info">Contributions Transfers</h6>
            <a href="{{ route('contributions.transfers.create') }}" class="btn btn-info">
                <i class="bx bx-plus me-1"></i> New Transfer
            </a>
        </div>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="transfersTable" class="table table-striped table-bordered" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>TrxId</th>
                                        <th>Date</th>
                                        <th>From Customer</th>
                                        <th>From Product</th>
                                        <th>To Customer</th>
                                        <th>To Product</th>
                                        <th class="text-end">Amount</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded via AJAX -->
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
        // Initialize DataTable with Ajax
        var table = $('#transfersTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("contributions.transfers.data") }}',
                type: 'GET',
                error: function(xhr, error, code) {
                    console.error('DataTables Ajax Error:', error, code);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to load transfers data. Please refresh the page.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            columns: [
                { data: 'trx_id', name: 'trx_id', title: 'TrxId' },
                { data: 'date_formatted', name: 'date', title: 'Date' },
                { data: 'source_customer', name: 'source_customer', title: 'From Customer' },
                { data: 'source_product', name: 'source_product', title: 'From Product' },
                { data: 'destination_customer', name: 'destination_customer', title: 'To Customer' },
                { data: 'destination_product', name: 'destination_product', title: 'To Product' },
                { 
                    data: 'amount_formatted', 
                    name: 'amount', 
                    title: 'Amount',
                    className: 'text-end'
                },
                { data: 'description_text', name: 'description', title: 'Description' }
            ],
            responsive: true,
            order: [[1, 'desc']], // Order by date descending (newest first)
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            language: {
                search: "",
                searchPlaceholder: "Search transfers...",
                processing: '<i class="bx bx-loader-alt bx-spin"></i> Loading transfers...'
            }
        });
    });
</script>
@endpush

