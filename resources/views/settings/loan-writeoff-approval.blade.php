@extends('layouts.main')

@section('title', 'Loan Write-off Approval Settings')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'Loan Write-off Approval', 'url' => '#', 'icon' => 'bx bx-x-circle']
        ]" />
        <h6 class="mb-0 text-uppercase">LOAN WRITE-OFF APPROVAL SETTINGS</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        @can('manage system settings')
                        <h4 class="card-title mb-4">Loan Write-off Approval Configuration</h4>

                        @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bx bx-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        @endif
                        @if(isset($errors) && $errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bx bx-error-circle me-2"></i>
                            <ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        @endif

                        @php
                            $requireApprovalEnabled = filter_var(old('require_approval_for_all', $settings->require_approval_for_all ?? false), FILTER_VALIDATE_BOOLEAN);
                        @endphp
                        <form action="{{ route('settings.loan-writeoff-approval.update') }}" method="POST" id="loan_writeoff_approval_form">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="require_approval_for_all" id="require_approval_for_all_value" value="{{ $requireApprovalEnabled ? '1' : '0' }}">

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="require_approval_for_all" value="1" {{ $requireApprovalEnabled ? 'checked' : '' }} aria-label="Require Approval for All Write-offs">
                                        <label class="form-check-label" for="require_approval_for_all">Require Approval for All Write-offs</label>
                                        <small class="form-text text-muted d-block">If checked, all write-offs require approval regardless of amount</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row" id="direct_post_note" style="display:{{ $requireApprovalEnabled ? 'none' : 'block' }};">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <i class="bx bx-info-circle me-2"></i>
                                        When disabled, write-offs below the auto-approval limit are posted directly. Configure the limit and levels for amounts above it.
                                    </div>
                                </div>
                            </div>

                            <div id="approval_config" style="display:{{ $requireApprovalEnabled ? 'block' : 'none' }};">
                                <div class="row mb-3">
                                    <div class="col-md-6" id="auto_limit_block">
                                        <label for="auto_approval_limit" class="form-label">Auto-approval Limit (TZS)</label>
                                        <input type="number" class="form-control" id="auto_approval_limit" name="auto_approval_limit" step="0.01" min="0" value="{{ old('auto_approval_limit', $settings->auto_approval_limit ?? 0) }}" placeholder="0">
                                        <small class="form-text text-muted">Write-offs below this amount skip approval. Set 0 when requiring approval for all.</small>
                                    </div>
                                    <div class="col-md-6" id="levels_block">
                                        <label for="approval_levels" class="form-label">Number of Approval Levels</label>
                                        <select class="form-select" id="approval_levels" name="approval_levels">
                                            @foreach([1,2,3,4,5] as $n)
                                            <option value="{{ $n }}" {{ old('approval_levels', $settings->approval_levels ?? 2) == $n ? 'selected' : '' }}>{{ $n }} Level(s)</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row mt-4" id="assignments_block">
                                    <div class="col-12">
                                        <h6 class="mb-3">Approval Assignments (by Role or User)</h6>
                                        @php
                                            $initialApproversByLevel = [];
                                            for ($lev = 1; $lev <= 5; $lev++) {
                                                $typ = old("level{$lev}_approval_type", optional($settings)->{"level{$lev}_approval_type"} ?? 'role');
                                                $appr = (array) old("level{$lev}_approvers", optional($settings)->{"level{$lev}_approvers"} ?? []);
                                                $initialApproversByLevel[$lev] = array_map(function ($a) use ($typ) {
                                                    return $typ === 'role' ? 'role_' . $a : 'user_' . $a;
                                                }, $appr);
                                            }
                                        @endphp
                                        @foreach([1,2,3,4,5] as $level)
                                        <div class="card mb-3 level-card" data-level="{{ $level }}">
                                            <div class="card-header"><h6 class="mb-0">Level {{ $level }} Approvers</h6></div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Approval Type</label>
                                                        <select class="form-select level_approval_type" id="level{{ $level }}_approval_type" name="level{{ $level }}_approval_type">
                                                            <option value="role" {{ old("level{$level}_approval_type", $settings->{"level{$level}_approval_type"} ?? 'role') == 'role' ? 'selected' : '' }}>By Role</option>
                                                            <option value="user" {{ old("level{$level}_approval_type", $settings->{"level{$level}_approval_type"} ?? 'role') == 'user' ? 'selected' : '' }}>By User</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Approvers</label>
                                                        <select class="form-select level_approvers" id="level{{ $level }}_approvers" name="level{{ $level }}_approvers[]" multiple>
                                                            {{-- Options filled by JS based on approval type (role vs user) --}}
                                                        </select>
                                                        <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Update Settings</button>
                                    <a href="{{ route('settings.index') }}" class="btn btn-secondary"><i class="bx bx-arrow-back me-1"></i> Back</a>
                                </div>
                            </div>
                        </form>
                        @else
                        <div class="alert alert-warning"><i class="bx bx-lock me-2"></i>You don't have permission to manage these settings.</div>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const reqAll = document.getElementById('require_approval_for_all');
    const requireApprovalHidden = document.getElementById('require_approval_for_all_value');
    const approvalConfig = document.getElementById('approval_config');
    const directNote = document.getElementById('direct_post_note');
    const levelsSelect = document.getElementById('approval_levels');
    const form = document.getElementById('loan_writeoff_approval_form');

    function syncRequireApprovalHidden() {
        if (requireApprovalHidden && reqAll) {
            requireApprovalHidden.value = reqAll.checked ? '1' : '0';
        }
    }
    if (form) {
        form.addEventListener('submit', function() {
            syncRequireApprovalHidden();
        });
    }
    reqAll.addEventListener('change', syncRequireApprovalHidden);

    function toggle() {
        const enabled = reqAll.checked;
        approvalConfig.style.display = enabled ? 'block' : 'none';
        directNote.style.display = enabled ? 'none' : 'block';
        approvalConfig.querySelectorAll('input, select').forEach(el => { el.disabled = !enabled; });
        if (enabled) toggleLevels();
    }
    function toggleLevels() {
        const n = parseInt(levelsSelect.value) || 5;
        document.querySelectorAll('.level-card').forEach(card => {
            card.style.display = parseInt(card.dataset.level) <= n ? 'block' : 'none';
        });
    }
    toggle();
    reqAll.addEventListener('change', toggle);
    levelsSelect.addEventListener('change', toggleLevels);

    // Dynamic approver options: show roles or users based on approval type (like payment voucher)
    @php
        $rolesNames = isset($roles) ? $roles->pluck('name')->values() : collect();
        $usersArr = isset($users) ? $users->map(function($u) { return ['id' => $u->id, 'name' => $u->name, 'email' => $u->email ?? '']; })->values() : collect();
    @endphp
    const rolesData = @json($rolesNames);
    const usersData = @json($usersArr);
    const initialApproversByLevel = @json($initialApproversByLevel);

    function updateApproverOptions(level) {
        const typeSelect = document.getElementById('level' + level + '_approval_type');
        const approverSelect = document.getElementById('level' + level + '_approvers');
        if (!typeSelect || !approverSelect) return;
        const selectedType = typeSelect.value;
        const previouslySelected = Array.from(approverSelect.selectedOptions).map(function(o) { return o.value; });
        approverSelect.innerHTML = '';
        if (selectedType === 'role') {
            rolesData.forEach(function(name) {
                var opt = document.createElement('option');
                opt.value = 'role_' + name;
                opt.textContent = (name.charAt(0).toUpperCase() + name.slice(1)) + ' (Role)';
                if (previouslySelected.indexOf(opt.value) !== -1) opt.selected = true;
                approverSelect.appendChild(opt);
            });
        } else if (selectedType === 'user') {
            usersData.forEach(function(u) {
                var opt = document.createElement('option');
                opt.value = 'user_' + u.id;
                opt.textContent = u.name + ' (' + (u.email || '') + ')';
                if (previouslySelected.indexOf(opt.value) !== -1) opt.selected = true;
                approverSelect.appendChild(opt);
            });
        }
    }

    function initApproverOptions(level) {
        const typeSelect = document.getElementById('level' + level + '_approval_type');
        const approverSelect = document.getElementById('level' + level + '_approvers');
        if (!typeSelect || !approverSelect) return;
        const selectedType = typeSelect.value;
        const initialSelected = initialApproversByLevel[level] || [];
        approverSelect.innerHTML = '';
        if (selectedType === 'role') {
            rolesData.forEach(function(name) {
                var opt = document.createElement('option');
                opt.value = 'role_' + name;
                opt.textContent = (name.charAt(0).toUpperCase() + name.slice(1)) + ' (Role)';
                if (initialSelected.indexOf(opt.value) !== -1) opt.selected = true;
                approverSelect.appendChild(opt);
            });
        } else if (selectedType === 'user') {
            usersData.forEach(function(u) {
                var opt = document.createElement('option');
                opt.value = 'user_' + u.id;
                opt.textContent = u.name + ' (' + (u.email || '') + ')';
                if (initialSelected.indexOf(opt.value) !== -1) opt.selected = true;
                approverSelect.appendChild(opt);
            });
        }
        typeSelect.addEventListener('change', function() {
            updateApproverOptions(level);
        });
    }

    for (var l = 1; l <= 5; l++) {
        initApproverOptions(l);
    }
});
</script>
@endpush
