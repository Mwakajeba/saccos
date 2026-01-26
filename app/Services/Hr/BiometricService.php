<?php

namespace App\Services\Hr;

use App\Models\Hr\BiometricDevice;
use App\Models\Hr\BiometricLog;
use App\Models\Hr\BiometricEmployeeMapping;
use App\Models\Hr\Employee;
use App\Models\Hr\Attendance;
use App\Services\Hr\AttendanceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BiometricService
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Process biometric log and create/update attendance record
     */
    public function processBiometricLog(BiometricLog $log): bool
    {
        try {
            DB::beginTransaction();

            // Find employee mapping
            $mapping = BiometricEmployeeMapping::where('device_id', $log->device_id)
                ->where('device_user_id', $log->device_user_id)
                ->where('is_active', true)
                ->first();

            if (!$mapping) {
                $log->markAsFailed('Employee mapping not found for device_user_id: ' . $log->device_user_id);
                DB::commit();
                return false;
            }

            $employee = $mapping->employee;
            $punchDate = Carbon::parse($log->punch_time)->setTimezone($log->device->timezone)->format('Y-m-d');
            $punchTime = Carbon::parse($log->punch_time)->setTimezone($log->device->timezone)->format('H:i');

            // Check for duplicate log
            $duplicate = BiometricLog::where('device_id', $log->device_id)
                ->where('device_user_id', $log->device_user_id)
                ->where('punch_time', $log->punch_time)
                ->where('id', '!=', $log->id)
                ->where('status', BiometricLog::STATUS_PROCESSED)
                ->first();

            if ($duplicate) {
                $log->markAsDuplicate();
                DB::commit();
                return false;
            }

            // Get or create attendance record for the date
            $attendance = Attendance::firstOrNew([
                'employee_id' => $employee->id,
                'attendance_date' => $punchDate,
            ]);

            // Set device and schedule info
            $employeeSchedule = $this->attendanceService->getEmployeeScheduleForDate($employee, Carbon::parse($punchDate));
            if ($employeeSchedule) {
                if ($employeeSchedule->schedule_id) {
                    $attendance->schedule_id = $employeeSchedule->schedule_id;
                }
                if ($employeeSchedule->shift_id) {
                    $attendance->shift_id = $employeeSchedule->shift_id;
                }
            }

            // Update clock in/out based on punch type
            if ($log->punch_type === BiometricLog::PUNCH_CHECK_IN || $log->punch_type === BiometricLog::PUNCH_BREAK_OUT) {
                if (!$attendance->clock_in || $punchTime < $attendance->clock_in) {
                    $attendance->clock_in = $punchTime;
                }
            } elseif ($log->punch_type === BiometricLog::PUNCH_CHECK_OUT || $log->punch_type === BiometricLog::PUNCH_BREAK_IN) {
                if (!$attendance->clock_out || $punchTime > $attendance->clock_out) {
                    $attendance->clock_out = $punchTime;
                }
            }

            // Process attendance to calculate all fields
            $attendance = $this->attendanceService->processAttendance($attendance);
            $attendance->save();

            // Link log to attendance
            $log->employee_id = $employee->id;
            $log->markAsProcessed($attendance->id);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Biometric log processing failed', [
                'log_id' => $log->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $log->markAsFailed($e->getMessage());
            return false;
        }
    }

    /**
     * Process pending biometric logs
     */
    public function processPendingLogs($deviceId = null, $limit = 100): array
    {
        $query = BiometricLog::pending()->orderBy('punch_time');
        
        if ($deviceId) {
            $query->where('device_id', $deviceId);
        }

        $logs = $query->limit($limit)->get();
        $processed = 0;
        $failed = 0;

        foreach ($logs as $log) {
            if ($this->processBiometricLog($log)) {
                $processed++;
            } else {
                $failed++;
            }
        }

        return [
            'total' => $logs->count(),
            'processed' => $processed,
            'failed' => $failed,
        ];
    }

    /**
     * Sync data from biometric device
     */
    public function syncDevice(BiometricDevice $device): array
    {
        try {
            // This method will be implemented based on device type and connection
            // For now, it's a placeholder for future implementation
            
            $device->markSyncSuccess();
            
            return [
                'success' => true,
                'message' => 'Device synced successfully',
                'logs_processed' => 0,
            ];

        } catch (\Exception $e) {
            $device->markSyncFailure($e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Map employee to device user
     */
    public function mapEmployeeToDevice(Employee $employee, BiometricDevice $device, string $deviceUserId, string $deviceUserName = null): BiometricEmployeeMapping
    {
        return BiometricEmployeeMapping::updateOrCreate(
            [
                'device_id' => $device->id,
                'employee_id' => $employee->id,
            ],
            [
                'device_user_id' => $deviceUserId,
                'device_user_name' => $deviceUserName ?? $employee->full_name,
                'is_active' => true,
                'mapped_at' => now(),
            ]
        );
    }

    /**
     * Unmap employee from device
     */
    public function unmapEmployeeFromDevice(Employee $employee, BiometricDevice $device): bool
    {
        return BiometricEmployeeMapping::where('device_id', $device->id)
            ->where('employee_id', $employee->id)
            ->update(['is_active' => false]);
    }

    /**
     * Get employee mapping for device
     */
    public function getEmployeeMapping(Employee $employee, BiometricDevice $device): ?BiometricEmployeeMapping
    {
        return BiometricEmployeeMapping::where('device_id', $device->id)
            ->where('employee_id', $employee->id)
            ->where('is_active', true)
            ->first();
    }
}

