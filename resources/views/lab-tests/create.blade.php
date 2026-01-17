@extends('layouts.main')

@section('title', 'Request Lab Test')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Consultations', 'url' => route('consultations.index'), 'icon' => 'bx bx-clinic'],
            ['label' => 'Request Lab Test', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">REQUEST LAB TEST</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="alert alert-info">
                            <strong>Patient:</strong> {{ $consultation->customer->name }} ({{ $consultation->customer->customerNo }})
                            <br>
                            <strong>Consultation:</strong> {{ $consultation->consultation_number }}
                        </div>

                        <form action="{{ route('lab-tests.store', $consultationEncodedId) }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Test Name <span class="text-danger">*</span></label>
                                    <input type="text" name="test_name" class="form-control @error('test_name') is-invalid @enderror" 
                                           value="{{ old('test_name') }}" required>
                                    @error('test_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Test Description</label>
                                    <textarea name="test_description" class="form-control @error('test_description') is-invalid @enderror" rows="3">{{ old('test_description') }}</textarea>
                                    @error('test_description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Clinical Notes</label>
                                    <textarea name="clinical_notes" class="form-control @error('clinical_notes') is-invalid @enderror" rows="3">{{ old('clinical_notes') }}</textarea>
                                    @error('clinical_notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Instructions</label>
                                    <textarea name="instructions" class="form-control @error('instructions') is-invalid @enderror" rows="3">{{ old('instructions') }}</textarea>
                                    @error('instructions')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('consultations.show', $consultationEncodedId) }}" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Request Lab Test</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
