@extends('layouts.main')
@section('title', 'Create Complain Category')

@section('content')
<div class="page-wrapper">
    <div class="page-content"> 
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'Complain Categories', 'url' => route('settings.complain-categories.index'), 'icon' => 'bx bx-message-square-dots'],
            ['label' => 'Create Category', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">CREATE COMPLAIN CATEGORY</h6>
        <hr/>
        
        <div class="row">
            <!-- Left Column: Form -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        @include('settings.complain-categories.form')
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
                            <h6 class="text-primary"><i class="bx bx-help-circle me-2"></i>How to Create a Complain Category</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Enter a clear and descriptive <strong>Category Name</strong>
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Provide a detailed <strong>Description</strong> to explain the category
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Select an appropriate <strong>Priority Level</strong> (Low, Medium, or High)
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    Click "Save" to create the category
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
                            <h6 class="text-primary"><i class="bx bx-lightbulb me-2"></i>Best Practices</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="bx bx-check text-info me-2"></i>
                                    Use clear, concise category names
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check text-info me-2"></i>
                                    Provide detailed descriptions for better categorization
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check text-info me-2"></i>
                                    Assign priority based on complaint urgency
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check text-info me-2"></i>
                                    Review existing categories to avoid duplicates
                                </li>
                            </ul>
                        </div>

                        <div class="alert alert-info mb-0">
                            <i class="bx bx-info-circle me-2"></i>
                            <strong>Note:</strong> Category names must be unique. Once created, categories can be edited or deleted as needed.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
