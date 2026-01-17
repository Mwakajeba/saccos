@extends('layouts.main')

@section('title', 'Complains')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Complains', 'url' => '#', 'icon' => 'bx bx-message-square-dots']
        ]" />
        <h6 class="mb-0 text-uppercase">COMPLAINS</h6>
        <hr/>

        <div class="card radius-10">
            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bx bx-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">List of Complains</h4>
                </div>

                <!-- Filter Section -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="categoryFilter" class="form-label">Filter by Category</label>
                        <select id="categoryFilter" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered dt-responsive nowrap" id="complainsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Category</th>
                                <th>Priority</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Responded By</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- DataTables will populate this -->
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
        var table = $('#complainsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("complains.data") }}',
                type: 'GET',
                data: function(d) {
                    d.category_id = $('#categoryFilter').val();
                },
                error: function(xhr, error, code) {
                    console.error('DataTables Ajax Error:', error, code);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to load complains data. Please refresh the page.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'customer_name', name: 'customer_name' },
                { data: 'category_name', name: 'category_name' },
                { data: 'priority_badge', name: 'priority', orderable: false },
                { data: 'description_short', name: 'description' },
                { data: 'status_badge', name: 'status' },
                { data: 'created_at', name: 'created_at' },
                { data: 'responded_by_name', name: 'responded_by_name' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
            ],
            responsive: true,
            order: [[6, 'desc']], // Order by date desc
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            language: {
                search: "",
                searchPlaceholder: "Search complains...",
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
            }
        });

        // Category filter change event
        $('#categoryFilter').on('change', function() {
            table.ajax.reload();
        });
    });
</script>
@endpush
