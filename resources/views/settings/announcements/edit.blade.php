@extends('layouts.main')
@section('title', 'Edit Announcement')

@section('content')
<div class="page-wrapper">
    <div class="page-content"> 
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'Announcements', 'url' => route('settings.announcements.index'), 'icon' => 'bx bx-bullhorn'],
            ['label' => 'Edit Announcement', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />
        <h6 class="mb-0 text-uppercase">EDIT ANNOUNCEMENT</h6>
        <hr/>
        
        <div class="row">
            <!-- Left Column: Form -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        @include('settings.announcements.form')
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
                            <h6 class="text-primary"><i class="bx bx-image me-2"></i>Image Requirements</h6>
                            <div class="alert alert-warning">
                                <strong>Recommended Image Specifications:</strong>
                                <ul class="mb-0 mt-2">
                                    <li><strong>Dimensions:</strong> 400 x 200 pixels (2:1 aspect ratio)</li>
                                    <li><strong>File Size:</strong> Maximum 2MB</li>
                                    <li><strong>Format:</strong> JPEG, PNG, JPG, or GIF</li>
                                    <li><strong>Display Size:</strong> 110px height in app</li>
                                </ul>
                            </div>
                            <p class="small text-muted mb-0">
                                <i class="bx bx-info-circle"></i> Uploading a new image will replace the existing one. Images will be automatically resized and cropped to fit the card display.
                            </p>
                        </div>

                        <hr>

                        <div class="mb-4">
                            <h6 class="text-primary"><i class="bx bx-palette me-2"></i>Color Options</h6>
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <span class="badge" style="background-color: #0d6efd;">Blue</span>
                                <span class="badge" style="background-color: #198754;">Green</span>
                                <span class="badge" style="background-color: #fd7e14;">Orange</span>
                                <span class="badge" style="background-color: #dc3545;">Red</span>
                                <span class="badge" style="background-color: #6f42c1;">Purple</span>
                                <span class="badge" style="background-color: #ffc107; color: #000;">Yellow</span>
                            </div>
                        </div>

                        <hr>

                        <div class="mb-4">
                            <h6 class="text-primary"><i class="bx bx-lightbulb me-2"></i>Best Practices</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="bx bx-check text-info me-2"></i>
                                    Keep titles short and attention-grabbing
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check text-info me-2"></i>
                                    Use clear, concise messages
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check text-info me-2"></i>
                                    Use high-quality images with good contrast
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check text-info me-2"></i>
                                    Set start/end dates for time-sensitive announcements
                                </li>
                            </ul>
                        </div>

                        <div class="alert alert-info mb-0">
                            <i class="bx bx-info-circle me-2"></i>
                            <strong>Note:</strong> Only active announcements with valid date ranges will appear in the mobile app.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
