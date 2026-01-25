@extends('layouts.main')

@section('title', 'Inventory Settings')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'Inventory Settings', 'url' => '#', 'icon' => 'bx bx-package']
        ]" />
        <h6 class="mb-0 text-uppercase">INVENTORY SETTINGS</h6>
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

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="card-title mb-0">Inventory Configuration</h4>
                            <a href="{{ route('settings.index') }}" class="btn btn-secondary btn-sm">
                                <i class="bx bx-arrow-back me-1"></i> Back to Settings
                            </a>
                        </div>

                        @can('manage inventory settings')
                        <form action="{{ route('settings.inventory.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="inventory_cost_method" class="form-label">
                                    Cost Method <span class="text-danger">*</span>
                                </label>
                                <select name="inventory_cost_method" id="inventory_cost_method" class="form-select @error('inventory_cost_method') is-invalid @enderror" required>
                                    <option value="">Select Cost Method</option>
                                    <option value="FIFO" {{ old('inventory_cost_method', $settings->inventory_cost_method ?? '') == 'FIFO' ? 'selected' : '' }}>
                                        FIFO (First In, First Out)
                                    </option>
                                    <option value="LIFO" {{ old('inventory_cost_method', $settings->inventory_cost_method ?? '') == 'LIFO' ? 'selected' : '' }}>
                                        LIFO (Last In, First Out)
                                    </option>
                                    <option value="AVCO" {{ old('inventory_cost_method', $settings->inventory_cost_method ?? '') == 'AVCO' ? 'selected' : '' }}>
                                        AVCO (Average Cost)
                                    </option>
                                    <option value="Specific Identification" {{ old('inventory_cost_method', $settings->inventory_cost_method ?? '') == 'Specific Identification' ? 'selected' : '' }}>
                                        Specific Identification
                                    </option>
                                </select>
                                @error('inventory_cost_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Select the inventory costing method for valuing stock
                                </small>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="enable_negative_stock" name="enable_negative_stock" value="1" 
                                        {{ old('enable_negative_stock', $settings->enable_negative_stock ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="enable_negative_stock">
                                        Enable Negative Stock
                                    </label>
                                </div>
                                <small class="form-text text-muted">
                                    Allow stock levels to go below zero
                                </small>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="auto_generate_item_codes" name="auto_generate_item_codes" value="1" 
                                        {{ old('auto_generate_item_codes', $settings->auto_generate_item_codes ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="auto_generate_item_codes">
                                        Auto-Generate Item Codes
                                    </label>
                                </div>
                                <small class="form-text text-muted">
                                    Automatically generate unique item codes
                                </small>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('settings.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-x me-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Save Changes
                                </button>
                            </div>
                        </form>
                        @else
                            <div class="alert alert-warning">
                                <i class="bx bx-lock me-2"></i>
                                You don't have permission to modify inventory settings.
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Cost Method</label>
                                <input type="text" class="form-control" value="{{ $settings->inventory_cost_method ?? 'Not set' }}" readonly>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" disabled {{ $settings->enable_negative_stock ?? false ? 'checked' : '' }}>
                                    <label class="form-check-label">Enable Negative Stock</label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" disabled {{ $settings->auto_generate_item_codes ?? false ? 'checked' : '' }}>
                                    <label class="form-check-label">Auto-Generate Item Codes</label>
                                </div>
                            </div>
                        @endcan
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
                            <h6 class="fw-bold">Cost Methods:</h6>
                            <ul class="small">
                                <li><strong>FIFO:</strong> Items purchased first are sold first</li>
                                <li><strong>LIFO:</strong> Items purchased last are sold first</li>
                                <li><strong>AVCO:</strong> Average cost of all items</li>
                                <li><strong>Specific ID:</strong> Track individual items</li>
                            </ul>
                        </div>

                        <div class="mb-3">
                            <h6 class="fw-bold">Negative Stock:</h6>
                            <p class="small">When enabled, you can sell items even when stock is zero. Useful for backorders and pre-orders.</p>
                        </div>

                        <div class="mb-0">
                            <h6 class="fw-bold">Auto Item Codes:</h6>
                            <p class="small">System will automatically generate sequential item codes like ITEM-001, ITEM-002, etc.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
