@extends('layouts.main')

@section('title', 'Complain Categories')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'Complain Categories', 'url' => '#', 'icon' => 'bx bx-message-square-dots']
        ]" />
        <h6 class="mb-0 text-uppercase">COMPLAIN CATEGORIES</h6>
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
                    <h4 class="card-title mb-0">List of Complain Categories</h4>
                    <a href="{{ route('settings.complain-categories.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus"></i> Add Category
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered dt-responsive nowrap" id="complainCategoryTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Category Name</th>
                                <th>Description</th>
                                <th>Priority</th>
                                <th class="text-center">Complains Count</th>
                                <th>Created At</th>
                                <th>Updated At</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $index => $category)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $category->name }}</td>
                                    <td>{{ Str::limit($category->description ?? 'N/A', 50) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $category->priority_badge }}">
                                            {{ ucfirst($category->priority) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info">
                                            {{ $category->complains_count ?? 0 }}
                                        </span>
                                    </td>
                                    <td>{{ $category->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td>{{ $category->updated_at->format('Y-m-d H:i:s') }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('settings.complain-categories.edit', $category) }}" class="btn btn-sm btn-outline-warning">
                                            <i class="bx bx-edit"></i> Edit
                                        </a>

                                        <form action="{{ route('settings.complain-categories.destroy', $category) }}" method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" data-name="{{ $category->name }}">
                                                <i class="bx bx-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
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
        $('#complainCategoryTable').DataTable({
            responsive: true,
            order: [[3, 'desc'], [1, 'asc']], // Order by priority desc, then name asc
            pageLength: 10,
            language: {
                search: "",
                searchPlaceholder: "Search categories..."
            },
            columnDefs: [
                { targets: 4, orderable: true, searchable: false }, // Complains Count column
                { targets: -1, orderable: false, searchable: false, responsivePriority: 1 } // Actions column
            ]
        });

        // Delete confirmation
        $('.delete-form').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const categoryName = form.find('button').data('name');
            
            Swal.fire({
                title: 'Are you sure?',
                text: `You want to delete "${categoryName}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.off('submit').submit();
                }
            });
        });
    });
</script>
@endpush
