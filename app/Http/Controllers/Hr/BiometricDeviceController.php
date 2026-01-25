<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\BiometricDevice;
use App\Models\Hr\BiometricLog;
use App\Models\Hr\BiometricEmployeeMapping;
use App\Models\Hr\Employee;
use App\Services\Hr\BiometricService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class BiometricDeviceController extends Controller
{
    protected $biometricService;

    public function __construct(BiometricService $biometricService)
    {
        $this->biometricService = $biometricService;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $devices = BiometricDevice::where('company_id', current_company_id())
                ->with('branch')
                ->orderBy('device_code');

            return DataTables::of($devices)
                ->addIndexColumn()
                ->addColumn('branch_name', function ($device) {
                    return $device->branch ? $device->branch->name : 'All Branches';
                })
                ->addColumn('connection_info', function ($device) {
                    if ($device->ip_address) {
                        return $device->ip_address . ($device->port ? ':' . $device->port : '');
                    }
                    return $device->connection_type;
                })
                ->addColumn('sync_status', function ($device) {
                    if (!$device->last_sync_at) {
                        return '<span class="badge bg-secondary">Never Synced</span>';
                    }
                    
                    $minutesAgo = $device->last_sync_at->diffInMinutes(now());
                    if ($minutesAgo < $device->sync_interval_minutes) {
                        return '<span class="badge bg-success">Synced ' . $minutesAgo . 'm ago</span>';
                    } else {
                        return '<span class="badge bg-warning">Sync Due</span>';
                    }
                })
                ->addColumn('status_badge', function ($device) {
                    $badge = $device->is_active ? 'success' : 'secondary';
                    $text = $device->is_active ? 'Active' : 'Inactive';
                    return '<span class="badge bg-' . $badge . '">' . $text . '</span>';
                })
                ->addColumn('action', function ($device) {
                    $viewBtn = '<a href="' . route('hr.biometric-devices.show', $device->id) . '" class="btn btn-sm btn-outline-info me-1"><i class="bx bx-show"></i></a>';
                    $editBtn = '<a href="' . route('hr.biometric-devices.edit', $device->id) . '" class="btn btn-sm btn-outline-primary me-1"><i class="bx bx-edit"></i></a>';
                    $syncBtn = '<button class="btn btn-sm btn-outline-success sync-btn me-1" data-id="' . $device->id . '"><i class="bx bx-sync"></i></button>';
                    $deleteBtn = '<button class="btn btn-sm btn-outline-danger delete-btn" data-id="' . $device->id . '" data-name="' . $device->device_name . '"><i class="bx bx-trash"></i></button>';
                    return $viewBtn . $editBtn . $syncBtn . $deleteBtn;
                })
                ->rawColumns(['sync_status', 'status_badge', 'action'])
                ->make(true);
        }

        return view('hr-payroll.biometric-devices.index');
    }

    public function create()
    {
        $branches = \App\Models\Branch::where('company_id', current_company_id())
            ->orderBy('name')
            ->get();

        return view('hr-payroll.biometric-devices.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'device_code' => [
                'required',
                'string',
                'max:50',
                \Illuminate\Validation\Rule::unique('hr_biometric_devices')->where(function ($query) {
                    return $query->where('company_id', current_company_id());
                })
            ],
            'device_name' => 'required|string|max:200',
            'device_type' => 'required|in:fingerprint,face,card,palm',
            'device_model' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'ip_address' => 'nullable|ip',
            'port' => 'nullable|integer|min:1|max:65535',
            'connection_type' => 'required|in:api,tcp,udp,file_import',
            'timezone' => 'required|string|max:50',
            'auto_sync' => 'boolean',
            'sync_interval_minutes' => 'required|integer|min:1|max:1440',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $device = BiometricDevice::create(array_merge($validated, [
                'company_id' => current_company_id(),
            ]));

            // Generate API credentials
            $device->generateApiKey();

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Biometric device created successfully.',
                    'redirect' => route('hr.biometric-devices.show', $device->id)
                ]);
            }

            return redirect()->route('hr.biometric-devices.show', $device->id)
                ->with('success', 'Biometric device created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to create device: ' . $e->getMessage()]);
        }
    }

    public function show(BiometricDevice $biometricDevice)
    {
        if ($biometricDevice->company_id !== current_company_id()) {
            abort(403, 'Unauthorized access.');
        }

        $logs = BiometricLog::where('device_id', $biometricDevice->id)
            ->orderBy('punch_time', 'desc')
            ->limit(50)
            ->with('employee')
            ->get();

        $mappings = BiometricEmployeeMapping::where('device_id', $biometricDevice->id)
            ->where('is_active', true)
            ->with('employee')
            ->get();

        $stats = [
            'total_logs' => BiometricLog::where('device_id', $biometricDevice->id)->count(),
            'pending_logs' => BiometricLog::where('device_id', $biometricDevice->id)->where('status', 'pending')->count(),
            'processed_logs' => BiometricLog::where('device_id', $biometricDevice->id)->where('status', 'processed')->count(),
            'failed_logs' => BiometricLog::where('device_id', $biometricDevice->id)->where('status', 'failed')->count(),
            'mapped_employees' => $mappings->count(),
        ];

        return view('hr-payroll.biometric-devices.show', compact('biometricDevice', 'logs', 'mappings', 'stats'));
    }

    public function edit(BiometricDevice $biometricDevice)
    {
        if ($biometricDevice->company_id !== current_company_id()) {
            abort(403, 'Unauthorized access.');
        }

        $branches = \App\Models\Branch::where('company_id', current_company_id())
            ->orderBy('name')
            ->get();

        return view('hr-payroll.biometric-devices.edit', compact('biometricDevice', 'branches'));
    }

    public function update(Request $request, BiometricDevice $biometricDevice)
    {
        if ($biometricDevice->company_id !== current_company_id()) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'device_code' => [
                'required',
                'string',
                'max:50',
                \Illuminate\Validation\Rule::unique('hr_biometric_devices')->ignore($biometricDevice->id)->where(function ($query) {
                    return $query->where('company_id', current_company_id());
                })
            ],
            'device_name' => 'required|string|max:200',
            'device_type' => 'required|in:fingerprint,face,card,palm',
            'device_model' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'ip_address' => 'nullable|ip',
            'port' => 'nullable|integer|min:1|max:65535',
            'connection_type' => 'required|in:api,tcp,udp,file_import',
            'timezone' => 'required|string|max:50',
            'auto_sync' => 'boolean',
            'sync_interval_minutes' => 'required|integer|min:1|max:1440',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $biometricDevice->update($validated);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Biometric device updated successfully.'
                ]);
            }

            return redirect()->route('hr.biometric-devices.show', $biometricDevice->id)
                ->with('success', 'Biometric device updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to update device: ' . $e->getMessage()]);
        }
    }

    public function sync(BiometricDevice $biometricDevice)
    {
        if ($biometricDevice->company_id !== current_company_id()) {
            abort(403, 'Unauthorized access.');
        }

        $result = $this->biometricService->syncDevice($biometricDevice);
        
        // Process pending logs after sync
        $processResult = $this->biometricService->processPendingLogs($biometricDevice->id);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'logs_processed' => $processResult['processed'] ?? 0,
        ]);
    }

    public function regenerateApiKey(BiometricDevice $biometricDevice)
    {
        if ($biometricDevice->company_id !== current_company_id()) {
            abort(403, 'Unauthorized access.');
        }

        $apiKey = $biometricDevice->generateApiKey();

        return response()->json([
            'success' => true,
            'message' => 'API key regenerated successfully.',
            'api_key' => $apiKey,
        ]);
    }

    public function mapEmployee(Request $request, BiometricDevice $biometricDevice)
    {
        if ($biometricDevice->company_id !== current_company_id()) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'employee_id' => 'required|exists:hr_employees,id',
            'device_user_id' => 'required|string|max:100',
            'device_user_name' => 'nullable|string|max:200',
        ]);

        $employee = Employee::where('company_id', current_company_id())
            ->findOrFail($validated['employee_id']);

        $mapping = $this->biometricService->mapEmployeeToDevice(
            $employee,
            $biometricDevice,
            $validated['device_user_id'],
            $validated['device_user_name'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Employee mapped successfully.',
            'mapping' => $mapping,
        ]);
    }

    public function unmapEmployee(BiometricDevice $biometricDevice, Employee $employee)
    {
        if ($biometricDevice->company_id !== current_company_id()) {
            abort(403, 'Unauthorized access.');
        }

        $this->biometricService->unmapEmployeeFromDevice($employee, $biometricDevice);

        return response()->json([
            'success' => true,
            'message' => 'Employee unmapped successfully.',
        ]);
    }

    public function processPendingLogs(BiometricDevice $biometricDevice)
    {
        if ($biometricDevice->company_id !== current_company_id()) {
            abort(403, 'Unauthorized access.');
        }

        $result = $this->biometricService->processPendingLogs($biometricDevice->id);

        return response()->json([
            'success' => true,
            'message' => 'Pending logs processed.',
            'result' => $result,
        ]);
    }

    public function destroy(BiometricDevice $biometricDevice)
    {
        if ($biometricDevice->company_id !== current_company_id()) {
            abort(403, 'Unauthorized access.');
        }

        $biometricDevice->delete();

        return response()->json([
            'success' => true,
            'message' => 'Biometric device deleted successfully.'
        ]);
    }
}

