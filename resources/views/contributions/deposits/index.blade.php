@extends('layouts.main')

@section('title', 'Contributions Deposits')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Contributions', 'url' => route('contributions.index'), 'icon' => 'bx bx-donate-heart'],
            ['label' => 'Contributions Deposits', 'url' => '#', 'icon' => 'bx bx-down-arrow-circle']
        ]" />

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 text-uppercase text-info">Contributions Deposits</h6>
                <div class="d-flex gap-2">
                    <a href="{{ route('contributions.deposits.bulk-create') }}" class="btn btn-primary">
                        <i class="bx bx-upload me-1"></i> Bulk Deposit
                    </a>
                    <a href="{{ route('contributions.deposits.create') }}" class="btn btn-info">
                        <i class="bx bx-plus me-1"></i> Add Deposit
                    </a>
                </div>
            </div>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="depositsTable" class="table table-striped table-bordered" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>TrxId</th>
                                            <th>Date</th>
                                            <th>Customer</th>
                                            <th>Product</th>
                                            <th>Amount</th>
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
        $(document).ready(function () {
            // Initialize DataTable with Ajax
            var table = $('#depositsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("contributions.deposits.data") }}',
                    type: 'GET',
                    error: function (xhr, error, code) {
                        console.error('DataTables Ajax Error:', error, code);
                        Swal.fire({
                            title: 'Error!',
                            text: 'Failed to load deposits data. Please refresh the page.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                columns: [
                    { data: 'trx_id', name: 'trx_id', title: 'TrxId' },
                    { data: 'date_formatted', name: 'date', title: 'Date' },
                    { data: 'customer_name', name: 'customer_name', title: 'Customer' },
                    { data: 'product_name', name: 'product_name', title: 'Product' },
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
                    searchPlaceholder: "Search deposits...",
                    processing: '<i class="bx bx-loader-alt bx-spin"></i> Loading deposits...'
                }
            });
        });
    </script>
@endpush