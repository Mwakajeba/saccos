@extends('layouts.main')
@section('title', 'Edit Complain Category')

@section('content')
<div class="page-wrapper">
    <div class="page-content"> 
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'Complain Categories', 'url' => route('settings.complain-categories.index'), 'icon' => 'bx bx-message-square-dots'],
            ['label' => 'Edit Category', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />
        <h6 class="mb-0 text-uppercase">EDIT COMPLAIN CATEGORY</h6>
        <hr/>
        
        <div class="row">
            <!-- Left Column: Form -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        @include('settings.complain-categories.form', ['complainCategory' => $complainCategory])
                    </div>
                </div>
            </div>

            <!-- Right Column: Information & Guidelines -->
            <div class="col-lg-4">
                <div class="card border-info">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Information & Guidelines</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h6 class="text-primary"><i class="bx bx-help-circle me-2"></i>Editing Complain Category</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    You can modify the <strong>Category Name</strong> if needed
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Update the <strong>Description</strong> to reflect changes
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Adjust the <strong>Priority Level</strong> as needed
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Click "Update" to save your changes
                                </li>
                            </ul>
                        </div>

                        <hr>

                        <div class="mb-4">
                            <h6 class="text-primary"><i class="bx bx-info-circle me-2"></i>Priority Levels</h6>
                            <div class="mb-2">
                                <span class="badge bg-success me-2">Low</span>
                                <small class="text-muted">For general inquiries or non-urgent complaints</small>
                            </div>
                            <div class="mb-2">
                                <span class="badge bg-warning me-2">Medium</span>
                                <small class="text-muted">For standard complaints that require attention</small>
                            </div>
                            <div class="mb-2">
                                <span class="badge bg-danger me-2">High</span>
                                <small class="text-muted">For urgent complaints requiring immediate action</small>
                            </div>
                        </div>

                        <hr>

                        <div class="mb-4">
                            <h6 class="text-warning"><i class="bx bx-error-circle me-2"></i>Important Notes</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="bx bx-info-circle text-info me-2"></i>
                                    Category names must be unique
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-info-circle text-info me-2"></i>
                                    Changes will affect all complaints using this category
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-info-circle text-info me-2"></i>
                                    Priority changes will update complaint urgency levels
                                </li>
                            </ul>
                        </div>

                        <div class="alert alert-warning mb-0">
                            <i class="bx bx-error-circle me-2"></i>
                            <strong>Warning:</strong> Be careful when editing categories as changes may affect existing complaints.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
