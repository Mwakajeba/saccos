@extends('layouts.main')

@section('title', 'Create Inventory Location')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'Inventory Locations', 'url' => route('settings.inventory.locations.index'), 'icon' => 'bx bx-map'],
            ['label' => 'Create Location', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">CREATE INVENTORY LOCATION</h6>
        <hr/>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('settings.inventory.locations.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="name" class="form-label">
                                    Location Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="name" id="name" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="branch_id" class="form-label">
                                    Branch <span class="text-danger">*</span>
                                </label>
                                <select name="branch_id" id="branch_id" 
                                        class="form-select @error('branch_id') is-invalid @enderror" required>
                                    <option value="">Select Branch</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('branch_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="manager_id" class="form-label">Manager</label>
                                <select name="manager_id" id="manager_id" 
                                        class="form-select @error('manager_id') is-invalid @enderror">
                                    <option value="">Select Manager (Optional)</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('manager_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('manager_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea name="description" id="description" rows="3" 
                                          class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" 
                                           name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('settings.inventory.locations.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-x me-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Create Location
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-primary">
                    <div class="card-body">
                        <h5 class="card-title text-primary">
                            <i class="bx bx-info-circle me-2"></i>Guidelines
                        </h5>
                        
                        <div class="mb-3">
                            <h6 class="fw-bold">Location Name</h6>
                            <p class="small">Give a descriptive name like "Main Warehouse", "Store Room A", etc.</p>
                        </div>

                        <div class="mb-3">
                            <h6 class="fw-bold">Branch Assignment</h6>
                            <p class="small">Each location must be assigned to a branch for proper tracking.</p>
                        </div>

                        <div class="mb-0">
                            <h6 class="fw-bold">Manager</h6>
                            <p class="small">Optionally assign a user responsible for this location's inventory.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
