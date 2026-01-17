@extends('layouts.main')

@php
    use Vinkla\Hashids\Facades\Hashids;
@endphp

@section('title', 'Announcements')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'Announcements', 'url' => '#', 'icon' => 'bx bx-bullhorn']
        ]" />
        <h6 class="mb-0 text-uppercase">ANNOUNCEMENTS</h6>
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
                    <h4 class="card-title mb-0">List of Announcements</h4>
                    <a href="{{ route('settings.announcements.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus"></i> Add Announcement
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered dt-responsive nowrap" id="announcementTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Message</th>
                                <th>Color</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Created By</th>
                                <th>Created At</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($announcements as $index => $announcement)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        @if($announcement->image_path)
                                            <img src="{{ asset('storage/' . $announcement->image_path) }}" 
                                                 alt="{{ $announcement->title }}" 
                                                 style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">
                                        @else
                                            <span class="text-muted">No Image</span>
                                        @endif
                                    </td>
                                    <td>{{ $announcement->title }}</td>
                                    <td>{{ Str::limit($announcement->message, 50) }}</td>
                                    <td>
                                        <span class="badge" style="background-color: {{ $announcement->color }};">
                                            {{ ucfirst($announcement->color) }}
                                        </span>
                                    </td>
                                    <td>{{ $announcement->order }}</td>
                                    <td>
                                        @if($announcement->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ $announcement->start_date ? $announcement->start_date->format('Y-m-d') : '-' }}</td>
                                    <td>{{ $announcement->end_date ? $announcement->end_date->format('Y-m-d') : '-' }}</td>
                                    <td>{{ $announcement->creator->name ?? 'N/A' }}</td>
                                    <td>{{ $announcement->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="text-center">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <a href="{{ route('settings.announcements.edit', Hashids::encode($announcement->id)) }}" 
                                               class="btn btn-sm btn-warning" title="Edit">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                            <form action="{{ route('settings.announcements.destroy', Hashids::encode($announcement->id)) }}" 
                                                  method="POST" 
                                                  onsubmit="return confirm('Are you sure you want to delete this announcement?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
                                        </div>
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
        $('#announcementTable').DataTable({
            responsive: true,
            order: [[5, 'asc']], // Order by order column
        });
    });
</script>
@endpush
