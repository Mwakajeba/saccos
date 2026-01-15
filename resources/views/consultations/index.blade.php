@extends('layouts.main')

@section('title', 'Consultations')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Consultations', 'url' => '#', 'icon' => 'bx bx-clinic']
        ]" />
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">CONSULTATIONS</h6>
            <a href="{{ route('consultations.create') }}" class="btn btn-primary">
                <i class="bx bx-plus"></i> New Consultation
            </a>
        </div>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="consultationsTable">
                                <thead>
                                    <tr>
                                        <th>Consultation No</th>
                                        <th>Patient</th>
                                        <th>Doctor</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#consultationsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("consultations.index") }}',
            columns: [
                { data: 'consultation_number', name: 'consultation_number' },
                { data: 'customer_name', name: 'customer.name' },
                { data: 'doctor_name', name: 'doctor.name' },
                { data: 'consultation_date', name: 'consultation_date' },
                { data: 'status', name: 'status' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ]
        });
    });
</script>
@endpush
