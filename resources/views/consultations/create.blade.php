@extends('layouts.main')

@section('title', 'New Consultation')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Consultations', 'url' => route('consultations.index'), 'icon' => 'bx bx-clinic'],
            ['label' => 'New Consultation', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">NEW CONSULTATION</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card radius-10">
                    <div class="card-body">
                        <form action="{{ route('consultations.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Patient <span class="text-danger">*</span></label>
                                    <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                                        <option value="">Select Patient</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }} ({{ $customer->customerNo }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('customer_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Consultation Date <span class="text-danger">*</span></label>
                                    <input type="date" name="consultation_date" class="form-control @error('consultation_date') is-invalid @enderror" 
                                           value="{{ old('consultation_date', date('Y-m-d')) }}" required>
                                    @error('consultation_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Chief Complaint</label>
                                    <textarea name="chief_complaint" class="form-control @error('chief_complaint') is-invalid @enderror" rows="3">{{ old('chief_complaint') }}</textarea>
                                    @error('chief_complaint')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">History of Present Illness</label>
                                    <textarea name="history_of_present_illness" class="form-control @error('history_of_present_illness') is-invalid @enderror" rows="3">{{ old('history_of_present_illness') }}</textarea>
                                    @error('history_of_present_illness')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Physical Examination</label>
                                    <textarea name="physical_examination" class="form-control @error('physical_examination') is-invalid @enderror" rows="3">{{ old('physical_examination') }}</textarea>
                                    @error('physical_examination')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Diagnosis</label>
                                    <textarea name="diagnosis" class="form-control @error('diagnosis') is-invalid @enderror" rows="3">{{ old('diagnosis') }}</textarea>
                                    @error('diagnosis')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Treatment Plan</label>
                                    <textarea name="treatment_plan" class="form-control @error('treatment_plan') is-invalid @enderror" rows="3">{{ old('treatment_plan') }}</textarea>
                                    @error('treatment_plan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('consultations.index') }}" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Save Consultation</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
