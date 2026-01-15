@extends('layouts.main')

@section('title', 'Edit Consultation')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Consultations', 'url' => route('consultations.index'), 'icon' => 'bx bx-clinic'],
            ['label' => 'Edit Consultation', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />
        <h6 class="mb-0 text-uppercase">EDIT CONSULTATION</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card radius-10">
                    <div class="card-body">
                        <form action="{{ route('consultations.update', $encodedId) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Patient</label>
                                    <input type="text" class="form-control" value="{{ $consultation->customer->name }} ({{ $consultation->customer->customerNo }})" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Consultation Date <span class="text-danger">*</span></label>
                                    <input type="date" name="consultation_date" class="form-control @error('consultation_date') is-invalid @enderror" 
                                           value="{{ old('consultation_date', $consultation->consultation_date->format('Y-m-d')) }}" required>
                                    @error('consultation_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="active" {{ old('status', $consultation->status) == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="completed" {{ old('status', $consultation->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled" {{ old('status', $consultation->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Chief Complaint</label>
                                    <textarea name="chief_complaint" class="form-control @error('chief_complaint') is-invalid @enderror" rows="3">{{ old('chief_complaint', $consultation->chief_complaint) }}</textarea>
                                    @error('chief_complaint')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">History of Present Illness</label>
                                    <textarea name="history_of_present_illness" class="form-control @error('history_of_present_illness') is-invalid @enderror" rows="3">{{ old('history_of_present_illness', $consultation->history_of_present_illness) }}</textarea>
                                    @error('history_of_present_illness')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Physical Examination</label>
                                    <textarea name="physical_examination" class="form-control @error('physical_examination') is-invalid @enderror" rows="3">{{ old('physical_examination', $consultation->physical_examination) }}</textarea>
                                    @error('physical_examination')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Diagnosis</label>
                                    <textarea name="diagnosis" class="form-control @error('diagnosis') is-invalid @enderror" rows="3">{{ old('diagnosis', $consultation->diagnosis) }}</textarea>
                                    @error('diagnosis')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Treatment Plan</label>
                                    <textarea name="treatment_plan" class="form-control @error('treatment_plan') is-invalid @enderror" rows="3">{{ old('treatment_plan', $consultation->treatment_plan) }}</textarea>
                                    @error('treatment_plan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $consultation->notes) }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('consultations.show', $encodedId) }}" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update Consultation</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
