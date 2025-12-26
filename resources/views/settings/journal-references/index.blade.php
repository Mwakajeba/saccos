@extends('layouts.main')

@section('title', 'Journal References')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'Journal References', 'url' => '#', 'icon' => 'bx bx-book']
        ]" />
        <h6 class="mb-0 text-uppercase">JOURNAL REFERENCES</h6>
        <hr/>

        <div class="card radius-10">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">List of Journal References</h4>
                    <a href="{{ route('settings.journal-references.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus"></i> Add Journal Reference
                    </a>
                </div>

                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bx bx-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered dt-responsive nowrap" id="journalReferenceTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Reference</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($journalReferences as $index => $reference)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $reference->name }}</td>
                                    <td>{{ $reference->reference }}</td>
                                    <td>
                                        @if($reference->is_active)
                                        <span class="badge bg-success">Active</span>
                                        @else
                                        <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ $reference->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('settings.journal-references.edit', $reference->hash_id) }}" class="btn btn-sm btn-outline-primary me-1">
                                            <i class="bx bx-edit"></i> Edit
                                        </a>

                                        <form action="{{ route('settings.journal-references.destroy', $reference->hash_id) }}" method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" data-name="{{ $reference->name }}" onclick="return confirm('Are you sure you want to delete this journal reference?')">
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
        $('#journalReferenceTable').DataTable({
            responsive: true,
            order: [[1, 'asc']], // Order by name
            pageLength: 10,
            language: {
                search: "",
                searchPlaceholder: "Search journal references..."
            },
            columnDefs: [
                { targets: -1, orderable: false, searchable: false, responsivePriority: 1 }
            ]
        });
    });
</script>
@endpush

