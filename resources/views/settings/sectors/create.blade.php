@extends('layouts.main')
@section('title', 'Create Sector')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Settings', 'url' => route('settings.index')],
            ['label' => 'Sectors', 'url' => route('settings.sectors.index')],
            ['label' => 'Create']
        ]" />

        <div class="row">
            <!-- Right Column: Guidelines -->
            <div class="col-md-4 col-lg-3 order-md-2 mb-3">
                @include('settings.sectors.guidelines')
            </div>

            <!-- Left Column: Form -->
            <div class="col-md-8 col-lg-9 order-md-1">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="bx bx-plus me-2"></i>CREATE NEW SECTOR</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('settings.sectors.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Sector Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                        value="{{ old('name') }}" placeholder="Enter sector name" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                        rows="3" placeholder="Enter sector description">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="bx bx-save me-1"></i>Save Sector
                                </button>
                                <a href="{{ route('settings.sectors.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-x me-1"></i>Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
