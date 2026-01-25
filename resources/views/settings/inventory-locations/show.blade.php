@extends('layouts.main')

@section('title', 'View Inventory Location')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'Inventory Locations', 'url' => route('settings.inventory.locations.index'), 'icon' => 'bx bx-map'],
            ['label' => 'View Location', 'url' => '#', 'icon' => 'bx bx-show']
        ]" />
        <h6 class="mb-0 text-uppercase">INVENTORY LOCATION DETAILS</h6>
        <hr/>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">{{ $location->name }}</h4>
                    <div>
                        @can('manage inventory settings')
                        <a href="{{ route('settings.inventory.locations.edit', Hashids::encode($location->id)) }}" 
                           class="btn btn-warning btn-sm">
                            <i class="bx bx-edit me-1"></i> Edit
                        </a>
                        @endcan
                        <a href="{{ route('settings.inventory.locations.index') }}" class="btn btn-secondary btn-sm">
                            <i class="bx bx-arrow-back me-1"></i> Back
                        </a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Location Name:</th>
                                <td>{{ $location->name }}</td>
                            </tr>
                            <tr>
                                <th>Branch:</th>
                                <td>{{ $location->branch->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Manager:</th>
                                <td>{{ $location->manager->name ?? 'Not assigned' }}</td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    @if($location->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Description:</th>
                                <td>{{ $location->description ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Created:</th>
                                <td>{{ $location->created_at->format('d M Y, h:i A') }}</td>
                            </tr>
                            <tr>
                                <th>Last Updated:</th>
                                <td>{{ $location->updated_at->format('d M Y, h:i A') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
