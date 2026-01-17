@extends('layouts.main')

@section('title', 'View Complain')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Complains', 'url' => route('complains.index'), 'icon' => 'bx bx-message-square-dots'],
            ['label' => 'View Complain', 'url' => '#', 'icon' => 'bx bx-show']
        ]" />
        <h6 class="mb-0 text-uppercase">VIEW COMPLAIN</h6>
        <hr/>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bx bx-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Complain Details</h6>
                            <div>
                                <span class="badge bg-{{ $complain->status_badge }} me-2">
                                    {{ ucfirst(str_replace('_', ' ', $complain->status)) }}
                                </span>
                                @if($complain->category)
                                <span class="badge bg-{{ $complain->category->priority_badge }}">
                                    Priority: {{ ucfirst($complain->category->priority) }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Customer:</strong>
                                <p>{{ $complain->customer->name ?? 'N/A' }} ({{ $complain->customer->customerNo ?? 'N/A' }})</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Category:</strong>
                                <p>{{ $complain->category->name ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Submitted Date:</strong>
                                <p>{{ $complain->created_at->format('Y-m-d H:i:s') }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Branch:</strong>
                                <p>{{ $complain->branch->name ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="mb-3">
                            <strong>Description:</strong>
                            <div class="mt-2 p-3 bg-light rounded">
                                <p class="mb-0">{{ $complain->description }}</p>
                            </div>
                        </div>

                        @if($complain->response)
                        <div class="mb-3">
                            <strong>Response:</strong>
                            <div class="mt-2 p-3 bg-info bg-opacity-10 rounded">
                                <p class="mb-0">{{ $complain->response }}</p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Responded By:</strong>
                                <p>{{ $complain->respondedBy->name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Responded At:</strong>
                                <p>{{ $complain->responded_at ? $complain->responded_at->format('Y-m-d H:i:s') : 'N/A' }}</p>
                            </div>
                        </div>
                        @endif

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('complains.index') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i> Back
                            </a>
                            @if($complain->status !== 'resolved' && $complain->status !== 'closed')
                            <a href="{{ route('complains.edit', $encodedId) }}" class="btn btn-primary">
                                <i class="bx bx-edit me-1"></i> Respond
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
