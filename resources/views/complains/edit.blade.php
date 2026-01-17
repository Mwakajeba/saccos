@extends('layouts.main')

@section('title', 'Respond to Complain')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Complains', 'url' => route('complains.index'), 'icon' => 'bx bx-message-square-dots'],
            ['label' => 'Respond to Complain', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />
        <h6 class="mb-0 text-uppercase">RESPOND TO COMPLAIN</h6>
        <hr/>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Complain Information</h6>
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

                        <div class="mb-3">
                            <strong>Description:</strong>
                            <div class="mt-2 p-3 bg-light rounded">
                                <p class="mb-0">{{ $complain->description }}</p>
                            </div>
                        </div>

                        <hr>

                        @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <form action="{{ route('complains.update', $encodedId) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="pending" {{ old('status', $complain->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="in_progress" {{ old('status', $complain->status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="resolved" {{ old('status', $complain->status) == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                    <option value="closed" {{ old('status', $complain->status) == 'closed' ? 'selected' : '' }}>Closed</option>
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="response" class="form-label">Response <span class="text-danger">*</span></label>
                                <textarea name="response" id="response" rows="6" 
                                          class="form-control @error('response') is-invalid @enderror" 
                                          required>{{ old('response', $complain->response ?? '') }}</textarea>
                                @error('response') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="form-text text-muted">
                                    Provide a detailed response to the customer's complaint.
                                </small>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('complains.show', $encodedId) }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Save Response
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
