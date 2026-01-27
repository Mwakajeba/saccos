@extends('layouts.main')

@section('title', 'Biometric Device Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'HR & Payroll', 'url' => route('hr-payroll.index'), 'icon' => 'bx bx-user'],
            ['label' => 'Biometric Devices', 'url' => route('hr.biometric-devices.index'), 'icon' => 'bx bx-fingerprint'],
            ['label' => 'Details', 'url' => '#', 'icon' => 'bx bx-show']
        ]" />
        <h6 class="mb-0 text-uppercase">Biometric Device: {{ $biometricDevice->device_name }}</h6>
        <hr />
        
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Device Information</h6>
                        <p><strong>Code:</strong> {{ $biometricDevice->device_code }}</p>
                        <p><strong>Name:</strong> {{ $biometricDevice->device_name }}</p>
                        <p><strong>Type:</strong> <span class="badge bg-info">{{ ucfirst($biometricDevice->device_type) }}</span></p>
                        <p><strong>Model:</strong> {{ $biometricDevice->device_model ?? 'N/A' }}</p>
                        <p><strong>Serial:</strong> {{ $biometricDevice->serial_number ?? 'N/A' }}</p>
                        <p><strong>Branch:</strong> {{ $biometricDevice->branch ? $biometricDevice->branch->name : 'All Branches' }}</p>
                        <p><strong>Connection:</strong> {{ strtoupper($biometricDevice->connection_type) }}</p>
                        @if($biometricDevice->ip_address)
                        <p><strong>IP Address:</strong> {{ $biometricDevice->ip_address }}{{ $biometricDevice->port ? ':' . $biometricDevice->port : '' }}</p>
                        @endif
                        <p><strong>Timezone:</strong> {{ $biometricDevice->timezone }}</p>
                        <p><strong>Status:</strong> 
                            <span class="badge bg-{{ $biometricDevice->is_active ? 'success' : 'secondary' }}">
                                {{ $biometricDevice->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </p>
                        <div class="d-flex gap-2 mt-3">
                            <a href="{{ route('hr.biometric-devices.edit', $biometricDevice->id) }}" class="btn btn-sm btn-primary">
                                <i class="bx bx-edit me-1"></i>Edit
                            </a>
                            <button class="btn btn-sm btn-success" onclick="syncDevice()">
                                <i class="bx bx-sync me-1"></i>Sync Now
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="card-title">API Credentials</h6>
                        <div class="alert alert-warning">
                            <small><strong>Keep these credentials secure!</strong> They are used to authenticate device API calls.</small>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">API Key:</label>
                            <div class="input-group">
                                <input type="text" id="api_key" class="form-control form-control-sm" value="{{ $biometricDevice->api_key }}" readonly>
                                <button class="btn btn-sm btn-outline-secondary" onclick="copyToClipboard('api_key')">
                                    <i class="bx bx-copy"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">API Secret:</label>
                            <div class="input-group">
                                <input type="password" id="api_secret" class="form-control form-control-sm" value="{{ $biometricDevice->api_secret }}" readonly>
                                <button class="btn btn-sm btn-outline-secondary" onclick="toggleSecret()">
                                    <i class="bx bx-show" id="toggleIcon"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" onclick="copyToClipboard('api_secret')">
                                    <i class="bx bx-copy"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mt-2">
                            <button class="btn btn-sm btn-warning" onclick="regenerateApiKey()">
                                <i class="bx bx-refresh me-1"></i>Regenerate API Key
                            </button>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">
                                <strong>API Endpoint:</strong><br>
                                <code>{{ url('/api/biometric/punch') }}</code><br><br>
                                <strong>Headers:</strong><br>
                                <code>X-API-Key: [API Key]</code><br>
                                <code>X-API-Secret: [API Secret]</code>
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Statistics</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="border rounded p-3">
                                    <h4 class="mb-0">{{ $stats['total_logs'] }}</h4>
                                    <small class="text-muted">Total Logs</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-3">
                                    <h4 class="mb-0 text-warning">{{ $stats['pending_logs'] }}</h4>
                                    <small class="text-muted">Pending</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-3">
                                    <h4 class="mb-0 text-success">{{ $stats['processed_logs'] }}</h4>
                                    <small class="text-muted">Processed</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-3">
                                    <h4 class="mb-0 text-danger">{{ $stats['failed_logs'] }}</h4>
                                    <small class="text-muted">Failed</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Employee Mappings ({{ $stats['mapped_employees'] }})</h6>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#mapEmployeeModal">
                            <i class="bx bx-plus me-1"></i>Map Employee
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Device User ID</th>
                                        <th>Device User Name</th>
                                        <th>Mapped At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($mappings as $mapping)
                                    <tr>
                                        <td>{{ $mapping->employee->full_name }} ({{ $mapping->employee->employee_number }})</td>
                                        <td><code>{{ $mapping->device_user_id }}</code></td>
                                        <td>{{ $mapping->device_user_name }}</td>
                                        <td>{{ $mapping->mapped_at->format('d M Y') }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-danger" onclick="unmapEmployee({{ $mapping->employee->id }})">
                                                <i class="bx bx-unlink"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No employees mapped yet</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">Recent Logs</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Employee</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($logs as $log)
                                    <tr>
                                        <td>{{ $log->punch_time->format('d M Y H:i') }}</td>
                                        <td>{{ $log->employee ? $log->employee->full_name : 'Unknown (' . $log->device_user_id . ')' }}</td>
                                        <td><span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $log->punch_type)) }}</span></td>
                                        <td>
                                            <span class="badge bg-{{ $log->status == 'processed' ? 'success' : ($log->status == 'failed' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($log->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No logs yet</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Map Employee Modal -->
<div class="modal fade" id="mapEmployeeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Map Employee to Device</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="mapEmployeeForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Employee <span class="text-danger">*</span></label>
                        <select name="employee_id" id="map_employee_id" class="form-select select2-single" required>
                            <option value="">Select Employee</option>
                            @foreach(\App\Models\Hr\Employee::where('company_id', current_company_id())->orderBy('first_name')->get() as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->employee_number }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Device User ID <span class="text-danger">*</span></label>
                        <input type="text" name="device_user_id" class="form-control" required placeholder="User ID from device">
                        <div class="form-text">The user ID stored in the biometric device</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Device User Name</label>
                        <input type="text" name="device_user_name" class="form-control" placeholder="Name in device (optional)">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Map Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.select2-single').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    $('#mapEmployeeForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '{{ route("hr.biometric-devices.map-employee", $biometricDevice->id) }}',
            type: 'POST',
            data: $(this).serialize(),
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            },
            error: function(xhr) {
                Swal.fire({icon: 'error', title: 'Error!', text: xhr.responseJSON?.message || 'Something went wrong.'});
            }
        });
    });
});

function syncDevice() {
    $.ajax({
        url: '{{ route("hr.biometric-devices.sync", $biometricDevice->id) }}',
        type: 'POST',
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        success: function(response) {
            if (response.success) {
                Swal.fire({icon: 'success', title: 'Synced!', text: response.message, timer: 3000, showConfirmButton: false});
                setTimeout(() => location.reload(), 2000);
            }
        },
        error: function(xhr) {
            Swal.fire({icon: 'error', title: 'Error!', text: xhr.responseJSON?.message || 'Something went wrong.'});
        }
    });
}

function regenerateApiKey() {
    Swal.fire({
        title: 'Regenerate API Key?',
        text: 'This will invalidate the current API key. The device will need to be reconfigured.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, regenerate!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("hr.biometric-devices.regenerate-api-key", $biometricDevice->id) }}',
                type: 'POST',
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                success: function(response) {
                    if (response.success) {
                        $('#api_key').val(response.api_key);
                        Swal.fire({icon: 'success', title: 'Regenerated!', text: response.message});
                    }
                },
                error: function(xhr) {
                    Swal.fire({icon: 'error', title: 'Error!', text: xhr.responseJSON?.message || 'Something went wrong.'});
                }
            });
        }
    });
}

function unmapEmployee(employeeId) {
    Swal.fire({
        title: 'Unmap Employee?',
        text: 'This will remove the mapping between employee and device.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, unmap!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("hr.biometric-devices.index") }}/{{ $biometricDevice->id }}/unmap-employee/' + employeeId,
                type: 'DELETE',
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                },
                error: function(xhr) {
                    Swal.fire({icon: 'error', title: 'Error!', text: xhr.responseJSON?.message || 'Something went wrong.'});
                }
            });
        }
    });
}

function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    element.select();
    document.execCommand('copy');
    Swal.fire({icon: 'success', title: 'Copied!', text: 'API ' + (elementId === 'api_key' ? 'Key' : 'Secret') + ' copied to clipboard', timer: 2000, showConfirmButton: false});
}

function toggleSecret() {
    const input = document.getElementById('api_secret');
    const icon = document.getElementById('toggleIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bx-show');
        icon.classList.add('bx-hide');
    } else {
        input.type = 'password';
        icon.classList.remove('bx-hide');
        icon.classList.add('bx-show');
    }
}
</script>
@endpush

