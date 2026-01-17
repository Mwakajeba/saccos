@extends('layouts.main')

@section('title', 'Consultation Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Consultations', 'url' => route('consultations.index'), 'icon' => 'bx bx-clinic'],
            ['label' => $consultation->consultation_number, 'url' => '#', 'icon' => 'bx bx-show']
        ]" />
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">CONSULTATION DETAILS</h6>
            <div class="d-flex gap-2">
                <a href="{{ route('lab-tests.create', $encodedId) }}" class="btn btn-success">
                    <i class="bx bx-plus"></i> Request Lab Test
                </a>
                <a href="{{ route('consultations.edit', $encodedId) }}" class="btn btn-primary">
                    <i class="bx bx-edit"></i> Edit
                </a>
            </div>
        </div>
        <hr />

        <div class="row">
            <div class="col-md-8">
                <div class="card radius-10">
                    <div class="card-body">
                        <h6 class="mb-3">Consultation Information</h6>
                        <table class="table table-borderless">
                            <tr>
                                <th width="200">Consultation Number:</th>
                                <td>{{ $consultation->consultation_number }}</td>
                            </tr>
                            <tr>
                                <th>Patient:</th>
                                <td>{{ $consultation->customer->name }} ({{ $consultation->customer->customerNo }})</td>
                            </tr>
                            <tr>
                                <th>Doctor:</th>
                                <td>{{ $consultation->doctor->name }}</td>
                            </tr>
                            <tr>
                                <th>Date:</th>
                                <td>{{ $consultation->consultation_date->format('d M Y') }}</td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <span class="badge bg-{{ $consultation->status == 'active' ? 'success' : ($consultation->status == 'completed' ? 'info' : 'danger') }}">
                                        {{ ucfirst($consultation->status) }}
                                    </span>
                                </td>
                            </tr>
                            @if($consultation->chief_complaint)
                            <tr>
                                <th>Chief Complaint:</th>
                                <td>{{ $consultation->chief_complaint }}</td>
                            </tr>
                            @endif
                            @if($consultation->diagnosis)
                            <tr>
                                <th>Diagnosis:</th>
                                <td>{{ $consultation->diagnosis }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card radius-10">
                    <div class="card-body">
                        <h6 class="mb-3">Lab Tests</h6>
                        @if($consultation->labTests->count() > 0)
                            <div class="list-group">
                                @foreach($consultation->labTests as $labTest)
                                    <a href="{{ route('lab-tests.show', \App\Helpers\HashidsHelper::encode($labTest->id)) }}" 
                                       class="list-group-item list-group-item-action">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $labTest->test_name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $labTest->test_number }}</small>
                                            </div>
                                            <span class="badge bg-{{ $labTest->status == 'results_sent_to_doctor' ? 'success' : 'warning' }}">
                                                {{ str_replace('_', ' ', ucwords($labTest->status)) }}
                                            </span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">No lab tests requested yet.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
