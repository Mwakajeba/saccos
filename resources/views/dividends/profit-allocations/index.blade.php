@extends('layouts.main')

@section('title', 'Profit Allocations')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Shares Management', 'url' => route('shares.management'), 'icon' => 'bx bx-bar-chart-square'],
            ['label' => 'Profit Allocations', 'url' => '#', 'icon' => 'bx bx-bar-chart']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">PROFIT ALLOCATIONS</h6>
            <a href="{{ route('dividends.profit-allocations.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Create Profit Allocation
            </a>
        </div>
        <hr />

        <div class="card">
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered table-striped nowrap" id="profitAllocationsTable">
                        <thead>
                            <tr>
                                <th>SN</th>
                                <th>Reference Number</th>
                                <th>Financial Year</th>
                                <th>Allocation Date</th>
                                <th>Total Profit</th>
                                <th>Dividend Amount</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
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
        var table = $('#profitAllocationsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('dividends.profit-allocations.data') }}",
                type: "GET"
            },
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'reference_number', name: 'reference_number'},
                {data: 'financial_year', name: 'financial_year'},
                {data: 'allocation_date', name: 'allocation_date'},
                {data: 'total_profit', name: 'total_profit', render: function(data) {
                    return parseFloat(data).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                }},
                {data: 'dividend_amount', name: 'dividend_amount', render: function(data) {
                    return parseFloat(data).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                }},
                {data: 'status', name: 'status', render: function(data) {
                    var badge = 'secondary';
                    if (data === 'approved') badge = 'success';
                    else if (data === 'posted') badge = 'primary';
                    return '<span class="badge bg-' + badge + '">' + data.toUpperCase() + '</span>';
                }},
                {data: 'actions', name: 'actions', orderable: false, searchable: false}
            ],
            order: [[0, 'desc']]
        });

        // Delete button handler
        $(document).on('click', '.delete-btn', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');

            Swal.fire({
                title: 'Are you sure?',
                text: 'You are about to delete profit allocation: ' + name,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('dividends.profit-allocations.destroy', ':id') }}".replace(':id', id),
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Deleted!', response.message, 'success');
                                table.draw();
                            } else {
                                Swal.fire('Error!', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            var message = xhr.responseJSON?.message || 'Failed to delete profit allocation';
                            Swal.fire('Error!', message, 'error');
                        }
                    });
                }
            });
        });

        // Change status button handler
        $(document).on('click', '.change-status-btn', function() {
            var id = $(this).data('id');
            var currentStatus = $(this).data('status');

            // Define status options
            var statusOptions = ['draft', 'approved', 'posted', 'rejected'];
            var statusLabels = {
                'draft': 'Draft',
                'approved': 'Approved',
                'posted': 'Posted',
                'rejected': 'Rejected'
            };

            // Create options HTML
            var optionsHtml = statusOptions.map(function(status) {
                var selected = status === currentStatus ? 'selected' : '';
                return '<option value="' + status + '" ' + selected + '>' + statusLabels[status] + '</option>';
            }).join('');

            Swal.fire({
                title: 'Change Status',
                html: '<p>Current Status: <strong>' + statusLabels[currentStatus] + '</strong></p>' +
                      '<label for="newStatus" class="form-label">Select New Status:</label>' +
                      '<select id="newStatus" class="form-select">' + optionsHtml + '</select>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Update Status',
                cancelButtonText: 'Cancel',
                didOpen: () => {
                    $('#newStatus').select2({
                        dropdownParent: Swal.getContainer(),
                        width: '100%'
                    });
                },
                preConfirm: () => {
                    return $('#newStatus').val();
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    $.ajax({
                        url: "{{ route('dividends.profit-allocations.change-status', ':id') }}".replace(':id', id),
                        type: 'PATCH',
                        data: {
                            _token: '{{ csrf_token() }}',
                            status: result.value
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Updated!', response.message, 'success');
                                table.draw();
                            } else {
                                Swal.fire('Error!', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            var message = xhr.responseJSON?.message || 'Failed to update status';
                            Swal.fire('Error!', message, 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush

