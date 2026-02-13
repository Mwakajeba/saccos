@extends('layouts.main')

@section('title', 'Reconciliations')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Investment Management', 'url' => route('investments.index'), 'icon' => 'bx bx-trending-up'],
            ['label' => 'Reconciliations', 'url' => '#', 'icon' => 'bx bx-check-square']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">UTT RECONCILIATIONS</h6>
            <a href="{{ route('investments.reconciliations.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> New Reconciliation
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
                    <table class="table table-bordered table-striped nowrap" id="reconciliationsTable">
                        <thead>
                            <tr>
                                <th>Fund</th>
                                <th>Reconciliation Date</th>
                                <th>Statement Units</th>
                                <th>System Units</th>
                                <th>Variance</th>
                                <th>Status</th>
                                <th>Reconciled By</th>
                                <th>Actions</th>
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
        var table = $('#reconciliationsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("investments.reconciliations.data") }}',
                type: 'GET',
                error: function(xhr, error, code) {
                    console.error('DataTables Ajax Error:', error, code);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to load reconciliations data. Please refresh the page.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            columns: [
                { data: 'fund_name', name: 'fund_name', title: 'Fund' },
                { data: 'reconciliation_date', name: 'reconciliation_date', title: 'Reconciliation Date' },
                { data: 'statement_units_formatted', name: 'statement_units', title: 'Statement Units' },
                { data: 'system_units_formatted', name: 'system_units', title: 'System Units' },
                { data: 'variance_formatted', name: 'variance', title: 'Variance' },
                { data: 'status_badge', name: 'status', title: 'Status' },
                { data: 'reconciled_by', name: 'reconciled_by', title: 'Reconciled By' },
                { data: 'actions', name: 'actions', title: 'Actions', orderable: false, searchable: false }
            ],
            responsive: true,
            order: [[1, 'desc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            language: {
                search: "",
                searchPlaceholder: "Search reconciliations...",
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
            }
        });

        $(document).on('click', '.approve-recon-btn', function() {
            var reconId = $(this).data('id');
            Swal.fire({
                title: 'Approve Reconciliation?',
                text: 'This will mark the reconciliation as approved.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Approve',
                cancelButtonText: 'Cancel'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("investments.reconciliations.approve", ":id") }}'.replace(':id', reconId),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Success', response.message, 'success');
                                table.ajax.reload();
                            } else {
                                Swal.fire('Error', response.error || 'Failed to approve', 'error');
                            }
                        },
                        error: function(xhr) {
                            var msg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Failed to approve reconciliation';
                            Swal.fire('Error', msg, 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush

