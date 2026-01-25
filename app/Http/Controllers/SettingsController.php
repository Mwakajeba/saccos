<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Backup;
use App\Services\BackupService;
use App\Services\AiAssistantService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\SystemSetting;
use App\Models\ChartAccount;
use Vinkla\Hashids\Facades\Hashids;

class SettingsController extends Controller
{
    public function __construct()
    {
        // Middleware is applied in routes/web.php
    }

    public function index()
    {
        $company = Company::find(current_company_id());
        $branches = Branch::forCompany()->active()->get();

        return view('settings.index', compact('company', 'branches'));
    }

    public function companySettings()
    {
        $company = Company::find(current_company_id());

        return view('settings.company', compact('company'));
    }

    public function updateCompanySettings(Request $request)
    {
        $company = Company::find(current_company_id());

        // Custom validation for email to handle existing email
        $emailRules = 'required|email';
        if ($request->email !== $company->email) {
            $emailRules .= '|unique:companies,email,' . $company->id . ',id';
        }

        // Custom validation for license_number to handle existing license
        $licenseRules = 'required|string';
        if ($request->license_number !== $company->license_number) {
            $licenseRules .= '|unique:companies,license_number,' . $company->id . ',id';
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => $emailRules,
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'license_number' => $licenseRules,
            'registration_date' => 'required|date',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'bg_color' => 'nullable|string|max:7',
            'txt_color' => 'nullable|string|max:7',
        ]);

        $data = $request->except('logo');

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($company->logo && Storage::disk('public')->exists($company->logo)) {
                Storage::disk('public')->delete($company->logo);
            }

            $logo = $request->file('logo');
            $logoName = 'company_' . $company->id . '_' . time() . '.' . $logo->getClientOriginalExtension();
            $logoPath = $logo->storeAs('uploads/companies', $logoName, 'public');
            $data['logo'] = $logoPath;
        }

        $company->update($data);

        return redirect()->route('settings.company')->with('success', 'Company settings updated successfully!');
    }

    public function branchSettings()
    {
        $branches = Branch::forCompany()->paginate(10);

        return view('settings.branches', compact('branches'));
    }

    public function createBranch()
    {
        return view('settings.branches.create');
    }

    public function storeBranch(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'branch_name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:branches,email,NULL,id,company_id,' . current_company_id(),
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'location' => 'nullable|string',
            'manager_name' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $branch = Branch::create([
            'company_id' => current_company_id(),
            'name' => $request->name,
            'branch_name' => $request->branch_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'location' => $request->location,
            'manager_name' => $request->manager_name,
            'branch_id' => \Illuminate\Support\Str::uuid(),
            'status' => $request->status,
        ]);

        return redirect()->route('settings.branches')->with('success', 'Branch created successfully!');
    }

    public function editBranch(Branch $branch)
    {
        // Ensure branch belongs to current company
        if ($branch->company_id !== current_company_id()) {
            abort(403, 'Unauthorized access.');
        }

        return view('settings.branches.edit', compact('branch'));
    }

    public function updateBranch(Request $request, Branch $branch)
    {
        // Ensure branch belongs to current company
        if ($branch->company_id !== current_company_id()) {
            abort(403, 'Unauthorized access.');
        }

        // Custom validation for email to handle existing email
        $emailRules = 'nullable|email';
        if ($request->email !== $branch->email) {
            $emailRules .= '|unique:branches,email,' . $branch->id . ',id,company_id,' . current_company_id();
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'branch_name' => 'required|string|max:255',
            'email' => $emailRules,
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'location' => 'nullable|string',
            'manager_name' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $branch->update($request->all());

        return redirect()->route('settings.branches')->with('success', 'Branch updated successfully!');
    }

    public function destroyBranch(Branch $branch)
    {
        // Ensure branch belongs to current company
        if ($branch->company_id !== current_company_id()) {
            abort(403, 'Unauthorized access.');
        }

        // Check if branch has users
        if ($branch->users()->count() > 0) {
            return redirect()->route('settings.branches')->with('error', 'Cannot delete branch with active users.');
        }

        $branch->delete();

        return redirect()->route('settings.branches')->with('success', 'Branch deleted successfully!');
    }

    public function userSettings()
    {
        $user = auth()->user();
        $user->load(['branch', 'company', 'roles']);

        return view('settings.user', compact('user'));
    }

    public function updateUserSettings(Request $request)
    {
        $user = auth()->user();

        // Custom validation for email to handle existing email
        $emailRules = 'nullable|email';
        if ($request->email !== $user->email) {
            $emailRules .= '|unique:users,email,' . $user->id . ',id,company_id,' . current_company_id();
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users,phone,' . $user->id . ',id,company_id,' . current_company_id(),
            'email' => $emailRules,
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);

        // Verify current password if changing password
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
        }

        $userData = [
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
        ];

        if ($request->filled('new_password')) {
            $userData['password'] = Hash::make($request->new_password);
        }

        $user->update($userData);

        return redirect()->route('settings.user')->with('success', 'User settings updated successfully!');
    }

    public function systemSettings()
    {
        // Check permissions for system configurations
        if (!auth()->user()->can('view system configurations') && 
            !auth()->user()->can('manage system settings') && 
            !auth()->user()->hasRole('admin')) {
            abort(403, 'You do not have permission to view system configurations.');
        }

        $groups = [
            'general' => 'General Settings',
            'email' => 'Email Configuration',
            'security' => 'Security Settings',
            'backup' => 'Backup Configuration',
            'maintenance' => 'Maintenance Settings',
            'microfinance' => 'Microfinance Settings'
        ];

        $groupIcons = [
            'general' => 'bx-cog',
            'email' => 'bx-envelope',
            'security' => 'bx-shield',
            'backup' => 'bx-data',
            'maintenance' => 'bx-wrench',
            'microfinance' => 'bx-money'
        ];

        $timezones = [
            'Africa/Dar_es_Salaam',
            'Africa/Nairobi',
            'Africa/Kampala',
            'Africa/Kigali',
            'Africa/Bujumbura',
            'UTC',
            'America/New_York',
            'America/Chicago',
            'America/Denver',
            'America/Los_Angeles',
            'Europe/London',
            'Europe/Paris',
            'Europe/Berlin',
            'Asia/Tokyo',
            'Asia/Shanghai',
            'Asia/Kolkata',
            'Australia/Sydney',
            'Africa/Cairo',
            'Africa/Lagos',
            'America/Sao_Paulo',
            'Pacific/Auckland'
        ];

        $settings = [];
        foreach ($groups as $groupKey => $groupName) {
            $settings[$groupKey] = \App\Models\SystemSetting::getByGroup($groupKey);
        }

        return view('settings.system', compact('groups', 'groupIcons', 'timezones', 'settings'));
    }

    public function updateSystemSettings(Request $request)
    {
        // Check permissions for editing system configurations
        if (!auth()->user()->can('edit system configurations') && 
            !auth()->user()->can('manage system settings') && 
            !auth()->user()->hasRole('admin')) {
            abort(403, 'You do not have permission to edit system configurations.');
        }

        $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string',
        ]);

        try {
            foreach ($request->settings as $key => $value) {
                $setting = \App\Models\SystemSetting::where('key', $key)->first();

                if ($setting) {
                    // Handle different input types
                    if ($setting->type === 'boolean') {
                        $value = $value === '1' || $value === 'true' || $value === 'on';
                    } elseif ($setting->type === 'integer') {
                        $value = (int) $value;
                    }

                    $setting->update(['value' => $value]);
                }
            }

            // Clear cache
            \App\Models\SystemSetting::clearCache();

            // Apply security settings to configuration
            $this->applySecuritySettings();

            return redirect()->route('settings.system')->with('success', 'System settings updated successfully!');
        } catch (\Exception $e) {
            return redirect()->route('settings.system')->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }

    /**
     * Apply security settings to Laravel configuration
     */
    private function applySecuritySettings()
    {
        $securityConfig = \App\Services\SystemSettingService::getSecurityConfig();
        
        // Apply session lifetime
        if (isset($securityConfig['session_lifetime'])) {
            config(['session.lifetime' => $securityConfig['session_lifetime']]);
        }
    }

    public function resetSystemSettings()
    {
        // Check permissions for managing system configurations
        if (!auth()->user()->can('manage system configurations') && 
            !auth()->user()->can('manage system settings') && 
            !auth()->user()->hasRole('admin')) {
            abort(403, 'You do not have permission to reset system configurations.');
        }

        try {
            \App\Models\SystemSetting::truncate();
            \App\Models\SystemSetting::initializeDefaults();

            return redirect()->route('settings.system')->with('success', 'System settings reset to defaults successfully!');
        } catch (\Exception $e) {
            return redirect()->route('settings.system')->with('error', 'Failed to reset settings: ' . $e->getMessage());
        }
    }



    /**
     * Test email configuration
     */
    public function testEmailConfig()
    {
        try {
            $result = \App\Services\SystemSettingService::testEmailConfig();

            if ($result['success']) {
                return response()->json(['success' => true, 'message' => $result['message']]);
            } else {
                return response()->json(['success' => false, 'message' => $result['message']], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Email test failed: ' . $e->getMessage()], 500);
        }
    }

    public function backupSettings()
    {
        // Check permissions for backup settings
        if (!auth()->user()->can('view backup settings') && 
            !auth()->user()->can('manage system settings') && 
            !auth()->user()->hasRole('admin')) {
            abort(403, 'You do not have permission to view backup settings.');
        }

        $backupService = new BackupService();
        $backups = Backup::forCompany()->orderBy('created_at', 'desc')->paginate(10);
        $stats = $backupService->getBackupStats();

        return view('settings.backup', compact('backups', 'stats'));
    }

    public function createBackup(Request $request)
    {
        // Check permissions for creating backups
        if (!auth()->user()->can('create backup') && 
            !auth()->user()->can('manage system settings') && 
            !auth()->user()->hasRole('admin')) {
            abort(403, 'You do not have permission to create backups.');
        }

        $request->validate([
            'type' => 'required|in:database,files,full',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $backupService = new BackupService();

            switch ($request->type) {
                case 'database':
                    $backup = $backupService->createDatabaseBackup($request->description);
                    break;
                case 'files':
                    $backup = $backupService->createFilesBackup($request->description);
                    break;
                case 'full':
                    $backup = $backupService->createFullBackup($request->description);
                    break;
                default:
                    throw new \Exception('Invalid backup type');
            }

            return redirect()->route('settings.backup')->with('success', ucfirst($request->type) . ' backup created successfully!');

        } catch (\Exception $e) {
            return redirect()->route('settings.backup')->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    public function restoreBackup(Request $request)
    {
        // Check permissions for restoring backups
        if (!auth()->user()->can('restore backup') && 
            !auth()->user()->can('manage system settings') && 
            !auth()->user()->hasRole('admin')) {
            abort(403, 'You do not have permission to restore backups.');
        }

        $request->validate([
            'backup_id' => 'required|exists:backups,id',
        ]);

        try {
            $backup = Backup::forCompany()->findOrFail($request->backup_id);
            $backupService = new BackupService();

            $backupService->restoreBackup($backup);

            return redirect()->route('settings.backup')->with('success', 'Backup restored successfully!');

        } catch (\Exception $e) {
            return redirect()->route('settings.backup')->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }

    public function downloadBackup($hash_id)
    {
        // Check permissions for downloading backups
        if (!auth()->user()->can('view backup settings') && 
            !auth()->user()->can('manage system settings') && 
            !auth()->user()->hasRole('admin')) {
            abort(403, 'You do not have permission to download backups.');
        }

        // Decode hash ID to get backup ID
        $id = Hashids::decode($hash_id);
        if (empty($id)) {
            abort(404, 'Backup not found.');
        }

        $backup = Backup::forCompany()->find($id[0]);
        if (!$backup) {
            abort(404, 'Backup not found.');
        }

        $fullPath = storage_path('app/' . $backup->file_path);
        if (!file_exists($fullPath)) {
            abort(404, 'Backup file not found.');
        }

        return response()->download($fullPath, $backup->filename);
    }

    public function deleteBackup($hash_id)
    {
        // Check permissions for deleting backups
        if (!auth()->user()->can('delete backup') && 
            !auth()->user()->can('manage system settings') && 
            !auth()->user()->hasRole('admin')) {
            abort(403, 'You do not have permission to delete backups.');
        }

        // Decode hash ID to get backup ID
        $id = Hashids::decode($hash_id);
        if (empty($id)) {
            abort(404, 'Backup not found.');
        }

        $backup = Backup::forCompany()->find($id[0]);
        if (!$backup) {
            abort(404, 'Backup not found.');
        }

        try {
            $backup->deleteFile();
            $backup->delete();

            return redirect()->route('settings.backup')->with('success', 'Backup deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->route('settings.backup')->with('error', 'Delete failed: ' . $e->getMessage());
        }
    }

    public function cleanOldBackups(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        try {
            $backupService = new BackupService();
            $deletedCount = $backupService->cleanOldBackups($request->days);

            return redirect()->route('settings.backup')->with('success', "{$deletedCount} old backups cleaned successfully!");

        } catch (\Exception $e) {
            return redirect()->route('settings.backup')->with('error', 'Clean failed: ' . $e->getMessage());
        }
    }

    /**
     * AI Assistant Settings
     */
    public function aiAssistantSettings()
    {
        return view('settings.ai-assistant');
    }

    /**
     * Handle AI chat requests
     */
    public function aiChat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        try {
            $aiService = new AiAssistantService();
            $response = $aiService->processMessage($request->message);

            return response()->json([
                'success' => true,
                'response' => $response
            ]);

        } catch (\Exception $e) {
            \Log::error('AI Chat Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'AI processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Penalty Settings
     */
    public function penaltySettings()
    {
        return view('settings.penalty');
    }

    /**
     * Update Penalty Settings
     */
    public function updatePenaltySettings(Request $request)
    {
        $request->validate([
            'late_payment_penalty' => 'required|numeric|min:0|max:100',
            'penalty_grace_period' => 'required|integer|min:0|max:365',
            'penalty_calculation_method' => 'required|in:percentage,fixed',
            'penalty_currency' => 'required|string|max:10',
        ]);

        try {
            // Update penalty settings logic here
            // This would typically save to a settings table or config file

            return redirect()->route('settings.penalty')->with('success', 'Penalty settings updated successfully!');
        } catch (\Exception $e) {
            return redirect()->route('settings.penalty')->with('error', 'Failed to update penalty settings: ' . $e->getMessage());
        }
    }

    /**
     * Fees Settings
     */
    public function feesSettings()
    {
        return view('settings.fees');
    }

    /**
     * Update Fees Settings
     */
    public function updateFeesSettings(Request $request)
    {
        $request->validate([
            'service_fee_percentage' => 'required|numeric|min:0|max:100',
            'transaction_fee' => 'required|numeric|min:0',
            'minimum_fee' => 'required|numeric|min:0',
            'maximum_fee' => 'required|numeric|min:0',
            'fee_currency' => 'required|string|max:10',
        ]);

        try {
            // Update fees settings logic here
            // This would typically save to a settings table or config file

            return redirect()->route('settings.fees')->with('success', 'Fees settings updated successfully!');
        } catch (\Exception $e) {
            return redirect()->route('settings.fees')->with('error', 'Failed to update fees settings: ' . $e->getMessage());
        }
    }

    /**
     * SMS Settings
     */
    public function smsSettings()
    {
        return view('settings.sms');
    }

    /**
     * Update SMS Settings
     */
    public function updateSmsSettings(Request $request)
    {
        $request->validate([
            'sms_url' => 'required|url',
            'sms_senderid' => 'required|string|max:255',
            'sms_key' => 'required|string|max:255',
            'sms_token' => 'required|string|max:255',
            'test_phone' => 'nullable|string|max:20',
        ]);

        try {
            // Update .env file with SMS settings
            $envKeys = [
                'BEEM_SMS_URL' => $request->sms_url,
                'BEEM_SENDER_ID' => $request->sms_senderid,
                'BEEM_API_KEY' => $request->sms_key,
                'BEEM_SECRET_KEY' => $request->sms_token,
            ];

            // Also set fallback SMS_* keys
            $envKeys['SMS_URL'] = $request->sms_url;
            $envKeys['SMS_SENDERID'] = $request->sms_senderid;
            $envKeys['SMS_KEY'] = $request->sms_key;
            $envKeys['SMS_TOKEN'] = $request->sms_token;

            foreach ($envKeys as $key => $value) {
                if (!update_env_file($key, $value)) {
                    throw new \Exception("Failed to update {$key} in .env file");
                }
            }

            // Clear config cache to reload .env values
            \Artisan::call('config:clear');

            // If test phone is provided, send test SMS
            if ($request->filled('test_phone')) {
                // Temporarily update config to use new values for testing
                config([
                    'services.sms.senderid' => $request->sms_senderid,
                    'services.sms.token' => $request->sms_token,
                    'services.sms.key' => $request->sms_key,
                    'services.sms.url' => $request->sms_url,
                ]);

                $testResult = \App\Helpers\SmsHelper::test($request->test_phone);
                
                if ($testResult['success'] ?? false) {
                    return redirect()->route('settings.sms')->with('success', 'SMS settings updated and test SMS sent successfully!');
                } else {
                    return redirect()->route('settings.sms')
                        ->with('success', 'SMS settings updated successfully!')
                        ->with('warning', 'Test SMS failed: ' . ($testResult['error'] ?? 'Unknown error'));
                }
            }

            return redirect()->route('settings.sms')->with('success', 'SMS settings updated successfully! Please note that you may need to restart your application server for changes to take full effect.');
        } catch (\Exception $e) {
            \Log::error('SMS Settings Update Error: ' . $e->getMessage());
            return redirect()->route('settings.sms')->with('error', 'Failed to update SMS settings: ' . $e->getMessage());
        }
    }

    /**
     * Test SMS Configuration
     */
    public function testSmsSettings(Request $request)
    {
        $request->validate([
            'test_phone' => 'required|string|max:20',
            'sms_url' => 'nullable|url',
            'sms_senderid' => 'nullable|string|max:255',
            'sms_key' => 'nullable|string|max:255',
            'sms_token' => 'nullable|string|max:255',
        ]);

        try {
            // If form values are provided, use them temporarily for testing
            if ($request->filled('sms_url') && $request->filled('sms_senderid') && 
                $request->filled('sms_key') && $request->filled('sms_token')) {
                // Temporarily update config to use form values
                config([
                    'services.sms.senderid' => $request->sms_senderid,
                    'services.sms.token' => $request->sms_token,
                    'services.sms.key' => $request->sms_key,
                    'services.sms.url' => $request->sms_url,
                ]);
            }

            $result = \App\Helpers\SmsHelper::test($request->test_phone);
            
            if ($result['success'] ?? false) {
                return response()->json([
                    'success' => true,
                    'message' => 'Test SMS sent successfully! Please check the recipient phone.'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['error'] ?? 'Failed to send test SMS'
                ], 400);
            }
        } catch (\Exception $e) {
            \Log::error('SMS Test Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Payment Voucher Approval Settings
     */
    public function paymentVoucherApprovalSettings()
    {
        $user = Auth::user();
        
        // Load roles and users for dropdowns
        $roles = \Spatie\Permission\Models\Role::all();
        $users = \App\Models\User::where('company_id', $user->company_id)->get();
        
        // Load existing approval settings
        $settings = \App\Models\PaymentVoucherApprovalSetting::where('company_id', $user->company_id)->first();
        
        return view('settings.payment-voucher-approval', compact('roles', 'users', 'settings'));
    }

    /**
     * Update Payment Voucher Approval Settings
     */
    public function updatePaymentVoucherApprovalSettings(Request $request)
    {
        $requireAll = $request->has('require_approval_for_all');

        $baseRules = [
            'require_approval_for_all' => 'boolean',
        ];

        $approvalRules = [
            'approval_levels' => 'required|integer|min:1|max:5',
            'level1_approval_type' => 'required|in:role,user',
            'level1_approvers' => 'required|array|min:1',
            'level2_approval_type' => 'nullable|in:role,user',
            'level2_approvers' => 'nullable|array',
            'level3_approval_type' => 'nullable|in:role,user',
            'level3_approvers' => 'nullable|array',
            'level4_approval_type' => 'nullable|in:role,user',
            'level4_approvers' => 'nullable|array',
            'level5_approval_type' => 'nullable|in:role,user',
            'level5_approvers' => 'nullable|array',
        ];

        $rules = $requireAll ? array_merge($baseRules, $approvalRules) : $baseRules;
        $request->validate($rules);

        try {
            $user = Auth::user();
            $companyId = $user->company_id;

            // Find or create approval settings for the company
            $settings = \App\Models\PaymentVoucherApprovalSetting::firstOrCreate(
                ['company_id' => $companyId],
                [
                    'approval_levels' => 1,
                    'require_approval_for_all' => false,
                ]
            );

            // Update settings
            $updateData = [
                'require_approval_for_all' => $requireAll,
            ];
            if ($requireAll) {
                // Only approval_levels is strictly needed; keep others as previously configured
                $updateData = array_merge($updateData, [
                    'approval_levels' => $request->approval_levels,
                ]);
            }
            $settings->update($updateData);

            // Update approval assignments
            if ($requireAll) {
                $approvalLevels = (int) $request->approval_levels;
                
                for ($level = 1; $level <= $approvalLevels; $level++) {
                    $approvalType = $request->{"level{$level}_approval_type"};
                    $approvers = $request->{"level{$level}_approvers"} ?? [];

                    if ($approvalType && !empty($approvers)) {
                        // Process approver IDs - extract actual IDs from "user_X" or "role_X" format
                        $processedApprovers = [];
                        foreach ($approvers as $approver) {
                            if (str_starts_with($approver, 'user_')) {
                                $userId = (int) str_replace('user_', '', $approver);
                                $processedApprovers[] = $userId;
                            } elseif (str_starts_with($approver, 'role_')) {
                                $roleName = str_replace('role_', '', $approver);
                                $processedApprovers[] = $roleName;
                            }
                        }

                        $settings->update([
                            "level{$level}_approval_type" => $approvalType,
                            "level{$level}_approvers" => $processedApprovers,
                        ]);
                    }
                }
                
                // Clear unused levels
                for ($level = $approvalLevels + 1; $level <= 5; $level++) {
                    $settings->update([
                        "level{$level}_approval_type" => null,
                        "level{$level}_approvers" => null,
                    ]);
                }
            } else {
                // When approvals are disabled, set levels to 0 and clear approvers only.
                // Do NOT set level1_approval_type (non-nullable) to null.
                $settings->update([
                    'approval_levels' => 0,
                    'level1_approvers' => null,
                    'level2_approvers' => null,
                    'level3_approvers' => null,
                    'level4_approvers' => null,
                    'level5_approvers' => null,
                ]);
            }

            return redirect()->route('settings.payment-voucher-approval')->with('success', 'Payment voucher approval settings updated successfully!');
        } catch (\Exception $e) {
            return redirect()->route('settings.payment-voucher-approval')->with('error', 'Failed to update payment voucher approval settings: ' . $e->getMessage());
        }
    }

    /**
     * Show opening balance accounts settings form
     */
    public function openingBalanceAccountsSettings()
    {
        // Get chart accounts for dropdown
        $chartAccounts = ChartAccount::orderBy('account_name')->get();

        // Get current settings
        $sharesOpeningBalanceAccountId = SystemSetting::getValue('shares_opening_balance_account_id', null);
        $savingsOpeningBalanceAccountId = SystemSetting::getValue('savings_opening_balance_account_id', null);
        $depositsOpeningBalanceAccountId = SystemSetting::getValue('deposits_opening_balance_account_id', null);

        return view('settings.opening-balance-accounts', compact(
            'chartAccounts',
            'sharesOpeningBalanceAccountId',
            'savingsOpeningBalanceAccountId',
            'depositsOpeningBalanceAccountId'
        ));
    }

    /**
     * Update opening balance accounts settings
     */
    public function updateOpeningBalanceAccountsSettings(Request $request)
    {
        // Validate lines if provided, otherwise validate old format for backward compatibility
        if ($request->has('lines')) {
            $request->validate([
                'lines' => 'required|array|min:1',
                'lines.*.category' => 'required|in:Shares,Contributions,Loans',
                'lines.*.chart_account_id' => 'required|exists:chart_accounts,id',
            ]);

            // Group by category and get the first account for each category (for backward compatibility)
            $lines = $request->lines;
            $sharesAccountId = null;
            $contributionsAccountId = null;
            $loansAccountId = null;

            foreach ($lines as $line) {
                if ($line['category'] === 'Shares' && !$sharesAccountId) {
                    $sharesAccountId = $line['chart_account_id'];
                } elseif ($line['category'] === 'Contributions' && !$contributionsAccountId) {
                    $contributionsAccountId = $line['chart_account_id'];
                } elseif ($line['category'] === 'Loans' && !$loansAccountId) {
                    $loansAccountId = $line['chart_account_id'];
                }
            }

            // Save settings (maintaining backward compatibility with existing keys)
            SystemSetting::setValue(
                'shares_opening_balance_account_id',
                $sharesAccountId,
                'integer',
                'accounting',
                'SHARES Opening Balance Account',
                'Chart account for SHARES opening balances'
            );

            SystemSetting::setValue(
                'savings_opening_balance_account_id',
                $contributionsAccountId,
                'integer',
                'accounting',
                'SAVINGS Opening Balance Account',
                'Chart account for SAVINGS/Contributions opening balances'
            );

            SystemSetting::setValue(
                'deposits_opening_balance_account_id',
                $loansAccountId,
                'integer',
                'accounting',
                'DEPOSITS Opening Balance Account',
                'Chart account for DEPOSITS/Loans opening balances'
            );

            // Also save the full lines array as JSON for future use
            SystemSetting::setValue(
                'opening_balance_accounts_lines',
                json_encode($lines),
                'json',
                'accounting',
                'Opening Balance Accounts Lines',
                'Full configuration of opening balance accounts by category'
            );
        } else {
            // Backward compatibility with old format
            $request->validate([
                'shares_opening_balance_account_id' => 'nullable|exists:chart_accounts,id',
                'savings_opening_balance_account_id' => 'nullable|exists:chart_accounts,id',
                'deposits_opening_balance_account_id' => 'nullable|exists:chart_accounts,id',
            ]);

            SystemSetting::setValue(
                'shares_opening_balance_account_id',
                $request->shares_opening_balance_account_id,
                'integer',
                'accounting',
                'SHARES Opening Balance Account',
                'Chart account for SHARES opening balances'
            );

            SystemSetting::setValue(
                'savings_opening_balance_account_id',
                $request->savings_opening_balance_account_id,
                'integer',
                'accounting',
                'SAVINGS Opening Balance Account',
                'Chart account for SAVINGS opening balances'
            );

            SystemSetting::setValue(
                'deposits_opening_balance_account_id',
                $request->deposits_opening_balance_account_id,
                'integer',
                'accounting',
                'DEPOSITS Opening Balance Account',
                'Chart account for DEPOSITS opening balances'
            );
        }

        return redirect()->route('settings.opening-balance-accounts')
            ->with('success', 'Opening balance accounts settings updated successfully.');
    }

    /**
     * Show opening balance logs index page
     */
    public function openingBalanceLogsIndex()
    {
        return view('settings.opening-balance-logs.index');
    }

    /**
     * Get opening balance logs data for DataTable
     */
    public function getOpeningBalanceLogsData(Request $request)
    {
        if ($request->ajax()) {
            $user = auth()->user();
            $branchId = $user->branch_id;
            $companyId = $user->company_id;
            $type = $request->get('type', 'all'); // 'contribution', 'share', or 'all'

            $logs = \App\Models\OpeningBalanceLog::with([
                'customer',
                'contributionAccount',
                'contributionProduct',
                'shareAccount',
                'shareProduct',
                'journal',
                'user',
                'branch'
            ])
            ->where('branch_id', $branchId);
            
            // Filter by type if specified
            if ($type !== 'all') {
                $logs->where('type', $type);
            }
            
            $logs->select('opening_balance_logs.*');

            return \Yajra\DataTables\Facades\DataTables::eloquent($logs)
                ->addColumn('type_badge', function ($log) {
                    $color = $log->type === 'share' ? 'primary' : 'success';
                    $label = $log->type === 'share' ? 'Share' : 'Contribution';
                    return '<span class="badge bg-' . $color . '">' . $label . '</span>';
                })
                ->addColumn('customer_name', function ($log) {
                    return $log->customer ? $log->customer->name : 'N/A';
                })
                ->addColumn('customer_no', function ($log) {
                    return $log->customer ? $log->customer->customerNo : 'N/A';
                })
                ->addColumn('product_name', function ($log) {
                    if ($log->type === 'share') {
                        return $log->shareProduct ? $log->shareProduct->share_name : 'N/A';
                    }
                    return $log->contributionProduct ? $log->contributionProduct->product_name : 'N/A';
                })
                ->addColumn('account_number', function ($log) {
                    if ($log->type === 'share') {
                        return $log->shareAccount ? $log->shareAccount->account_number : 'N/A';
                    }
                    return $log->contributionAccount ? $log->contributionAccount->account_number : 'N/A';
                })
                ->addColumn('amount_formatted', function ($log) {
                    return number_format($log->amount, 2);
                })
                ->addColumn('date_formatted', function ($log) {
                    return $log->date ? $log->date->format('M d, Y') : 'N/A';
                })
                ->addColumn('journal_reference', function ($log) {
                    return $log->journal ? $log->journal->reference : 'N/A';
                })
                ->addColumn('user_name', function ($log) {
                    return $log->user ? $log->user->name : 'N/A';
                })
                ->addColumn('branch_name', function ($log) {
                    return $log->branch ? $log->branch->name : 'N/A';
                })
                ->addColumn('transaction_reference_formatted', function ($log) {
                    return $log->transaction_reference ?: 'N/A';
                })
                ->orderColumn('date', 'date DESC')
                ->rawColumns(['type_badge', 'customer_name', 'customer_no', 'product_name', 'account_number', 'amount_formatted', 'date_formatted', 'journal_reference', 'user_name', 'branch_name', 'transaction_reference_formatted'])
                ->make(true);
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }

    // Inventory Settings
    public function inventorySettings()
    {
        $settings = SystemSetting::first();
        
        return view('settings.inventory', compact('settings'));
    }

    public function updateInventorySettings(Request $request)
    {
        $request->validate([
            'inventory_cost_method' => 'required|in:FIFO,LIFO,AVCO,Specific Identification',
            'enable_negative_stock' => 'boolean',
            'auto_generate_item_codes' => 'boolean',
        ]);

        $settings = SystemSetting::first();
        
        if (!$settings) {
            $settings = new SystemSetting();
        }

        $settings->inventory_cost_method = $request->inventory_cost_method;
        $settings->enable_negative_stock = $request->has('enable_negative_stock');
        $settings->auto_generate_item_codes = $request->has('auto_generate_item_codes');
        $settings->save();

        return redirect()->back()->with('success', 'Inventory settings updated successfully.');
    }

    // Inventory Locations
    public function inventoryLocations()
    {
        $locations = \App\Models\InventoryLocation::where('company_id', current_company_id())
            ->with(['branch', 'manager'])
            ->get();
        
        return view('settings.inventory-locations.index', compact('locations'));
    }

    public function createInventoryLocation()
    {
        $branches = Branch::forCompany()->active()->get();
        $users = \App\Models\User::where('company_id', current_company_id())->get();
        
        return view('settings.inventory-locations.create', compact('branches', 'users'));
    }

    public function storeInventoryLocation(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'branch_id' => 'required|exists:branches,id',
            'manager_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ]);

        $location = new \App\Models\InventoryLocation();
        $location->name = $request->name;
        $location->description = $request->description;
        $location->branch_id = $request->branch_id;
        $location->manager_id = $request->manager_id;
        $location->is_active = $request->has('is_active');
        $location->company_id = current_company_id();
        $location->created_by = Auth::id();
        $location->save();

        return redirect()->route('settings.inventory.locations.index')
            ->with('success', 'Inventory location created successfully.');
    }

    public function showInventoryLocation($id)
    {
        $decoded = Hashids::decode($id);
        $locationId = !empty($decoded) ? $decoded[0] : null;

        if (!$locationId) {
            return redirect()->route('settings.inventory.locations.index')
                ->with('error', 'Invalid location ID');
        }

        $location = \App\Models\InventoryLocation::with(['branch', 'manager'])
            ->findOrFail($locationId);
        
        return view('settings.inventory-locations.show', compact('location'));
    }

    public function editInventoryLocation($id)
    {
        $decoded = Hashids::decode($id);
        $locationId = !empty($decoded) ? $decoded[0] : null;

        if (!$locationId) {
            return redirect()->route('settings.inventory.locations.index')
                ->with('error', 'Invalid location ID');
        }

        $location = \App\Models\InventoryLocation::findOrFail($locationId);
        $branches = Branch::forCompany()->active()->get();
        $users = \App\Models\User::where('company_id', current_company_id())->get();
        
        return view('settings.inventory-locations.edit', compact('location', 'branches', 'users'));
    }

    public function updateInventoryLocation(Request $request, $id)
    {
        $decoded = Hashids::decode($id);
        $locationId = !empty($decoded) ? $decoded[0] : null;

        if (!$locationId) {
            return redirect()->route('settings.inventory.locations.index')
                ->with('error', 'Invalid location ID');
        }

        $location = \App\Models\InventoryLocation::findOrFail($locationId);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'branch_id' => 'required|exists:branches,id',
            'manager_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ]);

        $location->name = $request->name;
        $location->description = $request->description;
        $location->branch_id = $request->branch_id;
        $location->manager_id = $request->manager_id;
        $location->is_active = $request->has('is_active');
        $location->save();

        return redirect()->route('settings.inventory.locations.index')
            ->with('success', 'Inventory location updated successfully.');
    }

    public function destroyInventoryLocation($id)
    {
        $decoded = Hashids::decode($id);
        $locationId = !empty($decoded) ? $decoded[0] : null;

        if (!$locationId) {
            return redirect()->route('settings.inventory.locations.index')
                ->with('error', 'Invalid location ID');
        }

        $location = \App\Models\InventoryLocation::findOrFail($locationId);
        
        // Check if location has any stock items
        $hasStock = \App\Models\InventoryStockLevel::where('location_id', $location->id)->exists();
        
        if ($hasStock) {
            return redirect()->route('settings.inventory.locations.index')
                ->with('error', 'Cannot delete location with existing stock.');
        }

        $location->delete();

        return redirect()->route('settings.inventory.locations.index')
            ->with('success', 'Inventory location deleted successfully.');
    }
}
