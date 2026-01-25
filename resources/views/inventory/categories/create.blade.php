@extends('layouts.main')
@section('title', 'Create Category')

@section('content')
<div class="page-wrapper">
    <div class="page-content"> 
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Inventory', 'url' => '#', 'icon' => 'bx bx-package'],
            ['label' => 'Categories', 'url' => route('inventory.categories.index'), 'icon' => 'bx bx-category'],
            ['label' => 'Create Category', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">CREATE NEW CATEGORY</h6>
        <hr/>
        
        <div class="row">
            <!-- Left Side - Form -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        @include('inventory.categories.form')
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Guidelines -->
            <div class="col-lg-4">
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bx bx-info-circle"></i> Guidelines</h6>
                    </div>
                    <div class="card-body">
                        <h6 class="text-primary"><i class="bx bx-category"></i> Category Information</h6>
                        <p class="small">Categories help organize your inventory items into meaningful groups for better management and reporting.</p>
                        
                        <hr>
                        
                        <h6 class="text-primary"><i class="bx bx-code-alt"></i> Category Code</h6>
                        <p class="small mb-0"><strong>Format:</strong> Use short, unique codes</p>
                        <p class="small mb-0"><strong>Examples:</strong> ELEC, FURN, STAT</p>
                        <p class="small">The code helps with quick identification and filtering.</p>
                        
                        <hr>
                        
                        <h6 class="text-primary"><i class="bx bx-text"></i> Category Name</h6>
                        <p class="small">Use clear, descriptive names that make it easy to identify the type of items in this category.</p>
                        
                        <hr>
                        
                        <h6 class="text-primary"><i class="bx bx-detail"></i> Description</h6>
                        <p class="small">Provide additional details about what items belong in this category. This helps users select the correct category when adding new items.</p>
                        
                        <hr>
                        
                        <div class="alert alert-info border-info">
                            <i class="bx bx-bulb"></i> <strong>Tip:</strong>
                            <p class="small mb-0">Create categories based on how you want to report on your inventory - by department, product type, or usage.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
