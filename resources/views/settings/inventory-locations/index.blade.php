@extends('layouts.main')

@section('title', 'Inventory Locations')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'Inventory Locations', 'url' => '#', 'icon' => 'bx bx-map']
        ]" />
        <h6 class="mb-0 text-uppercase">INVENTORY LOCATIONS</h6>
        <hr/>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bx bx-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bx bx-x-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">Manage Locations</h4>
                    <div>
                        @can('manage inventory settings')
                        <a href="{{ route('settings.inventory.locations.create') }}" class="btn btn-primary btn-sm">
                            <i class="bx bx-plus me-1"></i> Add Location
                        </a>
                        @endcan
                        <a href="{{ route('settings.index') }}" class="btn btn-secondary btn-sm">
                            <i class="bx bx-arrow-back me-1"></i> Back to Settings
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Branch</th>
                                <th>Manager</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($locations as $location)
                            <tr>
                                <td>{{ $location->name }}</td>
                                <td>{{ $location->branch->name ?? 'N/A' }}</td>
                                <td>{{ $location->manager->name ?? 'N/A' }}</td>
                                <td>{{ $location->description ?? '-' }}</td>
                                <td>
                                    @if($location->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('settings.inventory.locations.show', Hashids::encode($location->id)) }}" 
                                           class="btn btn-sm btn-info" title="View">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        @can('manage inventory settings')
                                        <a href="{{ route('settings.inventory.locations.edit', Hashids::encode($location->id)) }}" 
                                           class="btn btn-sm btn-warning" title="Edit">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                        <form action="{{ route('settings.inventory.locations.destroy', Hashids::encode($location->id)) }}" 
                                              method="POST" class="d-inline" 
                                              onsubmit="return confirm('Are you sure you want to delete this location?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No inventory locations found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
