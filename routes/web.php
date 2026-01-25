<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\OtpEmailController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ActivityLogsController;
use App\Http\Controllers\FiletypeController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CashCollateralTypeController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\AccountClassGroupController;
use App\Http\Controllers\AccountingController;
use App\Http\Controllers\ChartAccountController;
use App\Http\Controllers\Accounting\SupplierController;
use App\Http\Controllers\Accounting\PaymentVoucherController;
use App\Http\Controllers\Accounting\BillPurchaseController;
use App\Http\Controllers\Accounting\ReceiptVoucherController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\Accounting\BankReconciliationController;
use App\Http\Controllers\Accounting\Reports\BankReconciliationReportController;
use App\Http\Controllers\Accounting\BudgetController;
use App\Http\Controllers\Accounting\FeeController;
use App\Http\Controllers\Accounting\PenaltyController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ComplainController;
use App\Http\Controllers\LoanProductController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupMemberController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\LoanTopUpController;
use App\Http\Controllers\LoanReportController;
use App\Http\Controllers\LoanRepaymentController;
use App\Http\Controllers\LoanCollateralController;
use App\Http\Controllers\CashCollateralController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoanCalculatorController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\Accounting\Reports\BalanceSheetReportController as NewBalanceSheetReportController;
use App\Http\Controllers\Reports\BotBalanceSheetController;
use App\Http\Controllers\Reports\BotIncomeStatementController;
use App\Http\Controllers\Reports\BotSectoralLoansController;
use App\Http\Controllers\Reports\BotInterestRatesController;
use App\Http\Controllers\Reports\BotLiquidAssetsController;
use App\Http\Controllers\Reports\BotComplaintsReportController;
use App\Http\Controllers\Reports\BotDepositsBorrowingsController;
use App\Http\Controllers\Reports\BotAgentBankingController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\Reports\BotLoansDisbursedController;
use App\Http\Controllers\Reports\BotGeographicalDistributionController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\LaravelLogsController;
use App\Http\Controllers\ContributionController;
use App\Http\Controllers\ContributionAccountController;

//Sales,purchases and invntory
use App\Http\Controllers\Inventory\ItemController;
use App\Http\Controllers\Inventory\CategoryController;
use App\Http\Controllers\Inventory\MovementController;
use App\Http\Controllers\Inventory\TransferController;
use App\Http\Controllers\Inventory\WriteOffController;
use App\Http\Controllers\TransferRequestController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\Purchase\PurchaseRequisitionController;
use App\Http\Controllers\Purchase\QuotationController;
use App\Http\Controllers\Purchase\OrderController;
use App\Http\Controllers\Sales\DeliveryController;
use App\Http\Controllers\Sales\SalesInvoiceController;
use App\Http\Controllers\Sales\CreditNoteController;
use App\Http\Controllers\Sales\SalesOrderController;
use App\Http\Controllers\Sales\SalesProformaController;
use App\Http\Controllers\Sales\CashSaleController;
use App\Http\Controllers\Sales\PosSaleController;
use App\Http\Controllers\ChangeBranchController;
use App\Http\Controllers\Inventory\OpeningBalanceController;

// Add other main app routes here
Route::get('/dashboard/loan-product-disbursement', [DashboardController::class, 'loanProductDisbursement'])->middleware('auth');
Route::get('/dashboard/delinquency-loan-buckets', [DashboardController::class, 'delinquencyLoanBuckets'])->middleware('auth');
Route::get('/dashboard/monthly-collections', [DashboardController::class, 'monthlyCollections'])->middleware('auth');
// API route for bank accounts
Route::get('/api/bank-accounts', [\App\Http\Controllers\Api\BankAccountController::class, 'index']);

// Contribution API routes
Route::get('/api/customers/{customerId}/contribution-products', [ContributionController::class, 'getCustomerProducts'])->name('api.customers.contribution-products')->middleware('auth');
Route::get('/api/contribution-accounts/balance', [ContributionController::class, 'getAccountBalance'])->name('api.contribution-accounts.balance')->middleware('auth');

// Customer Mobile API Routes
Route::post('/api/customer/login', [\App\Http\Controllers\Api\CustomerAuthController::class, 'login']);
Route::post('/api/customer/profile', [\App\Http\Controllers\Api\CustomerAuthController::class, 'profile']);
Route::post('/api/customer/loans', [\App\Http\Controllers\Api\CustomerAuthController::class, 'loans']);
Route::post('/api/customer/group-members', [\App\Http\Controllers\Api\CustomerAuthController::class, 'groupMembers']);
Route::get('/api/customer/loan-products', [\App\Http\Controllers\Api\CustomerAuthController::class, 'loanProducts']);
Route::post('/api/customer/update-photo', [\App\Http\Controllers\Api\CustomerAuthController::class, 'updatePhoto']);
Route::post('/api/customer/update-password', [\App\Http\Controllers\Api\CustomerAuthController::class, 'updatePassword']);
Route::post('/api/customer/contributions', [\App\Http\Controllers\Api\CustomerAuthController::class, 'contributions']);
Route::post('/api/customer/shares', [\App\Http\Controllers\Api\CustomerAuthController::class, 'shares']);
Route::post('/api/customer/contribution-transactions', [\App\Http\Controllers\Api\CustomerAuthController::class, 'contributionTransactions']);
Route::post('/api/customer/share-transactions', [\App\Http\Controllers\Api\CustomerAuthController::class, 'shareTransactions']);
Route::post('/api/customer/loan-application', [\App\Http\Controllers\Api\CustomerAuthController::class, 'submitLoanApplication']);
Route::get('/api/customer/filetypes', [\App\Http\Controllers\Api\CustomerAuthController::class, 'filetypes']);
Route::post('/api/customer/loan-documents', [\App\Http\Controllers\Api\CustomerAuthController::class, 'loanDocuments']);
Route::post('/api/customer/loan-documents/upload', [\App\Http\Controllers\Api\CustomerAuthController::class, 'uploadLoanDocument']);
Route::get('/api/customer/complain-categories', [\App\Http\Controllers\Api\CustomerAuthController::class, 'getComplainCategories']);
Route::post('/api/customer/complain', [\App\Http\Controllers\Api\CustomerAuthController::class, 'submitComplain']);
Route::post('/api/customer/complains', [\App\Http\Controllers\Api\CustomerAuthController::class, 'getCustomerComplains']);
Route::post('/api/customer/next-of-kin', [\App\Http\Controllers\Api\CustomerAuthController::class, 'getNextOfKin']);
Route::post('/api/customer/announcements', [\App\Http\Controllers\Api\CustomerAuthController::class, 'getAnnouncements']);

Route::post('/receipts/store', [\App\Http\Controllers\ReceiptController::class, 'store'])->name('receipts.store');

// Route::middleware(['auth'])->group(function () {
Route::get('/change-branch', [\App\Http\Controllers\ChangeBranchController::class, 'show'])->name('change-branch');
Route::post('/change-branch', [\App\Http\Controllers\ChangeBranchController::class, 'change'])->name('change-branch.submit');
//     Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
// Group Loans AJAX
Route::get('group-loans-ajax/{group}', [\App\Http\Controllers\GroupLoanAjaxController::class, 'index'])->name('group.loans.ajax');
//     // Add other main app routes here
// });
Route::post('/loans/{hashid}/writeoff', [\App\Http\Controllers\LoanController::class, 'confirmWriteoff'])->name('loans.writeoff.confirm');
Route::get('/loans/{hashid}/writeoff', [\App\Http\Controllers\LoanController::class, 'writeoff'])->name('loans.writeoff');
// // ...existing code...

Route::get('loans/data', [LoanController::class, 'getLoansData'])->name('loans.data');
Route::post('loans/calculate-summary', [LoanController::class, 'calculateSummary'])
    ->name('loans.calculate-summary')
    ->middleware('auth');
// Group Members AJAX
Route::get('group-members-ajax/{group}', [\App\Http\Controllers\GroupMemberAjaxController::class, 'index'])->name('group.members.ajax');
// Loans in Arrears (30+ days)
Route::get('arrears-loans', [\App\Http\Controllers\ArrearsLoanController::class, 'index'])->name('arrears.loans.list');
Route::get('arrears-loans/pdf', [\App\Http\Controllers\ArrearsLoanController::class, 'exportPdf'])->name('arrears.loans.pdf');


Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');


Route::get('/login', [AuthController::class, 'showLoginForm']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::get('/subscription-expired', function () {
    return view('auth.subscription-expired');
})->name('subscription.expired');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::get('/verify-sms', [AuthController::class, 'showVerificationForm'])->name('verify-sms');
Route::post('/verify-sms', [AuthController::class, 'verifySmsCode']);

Route::get('/forgotPassword', [AuthController::class, 'showForgotPasswordForm'])->name('forgotPassword');
Route::post('/forgotPassword', [AuthController::class, 'forgotPassword']);

Route::get('/verify-otp-password', [AuthController::class, 'showVerificationForm'])->name('verify-otp-password');
Route::post('/verify-otp-password', [AuthController::class, 'verifyPasswordCode']);

Route::get('/reset-password', [AuthController::class, 'showNewPasswordForm'])->name('new-password-form');
Route::post('/reset-password', [AuthController::class, 'storeNewPassword']);

Route::get('/resend-otp/{phone}', [AuthController::class, 'resendOtp'])->name('resend.otp');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Shares Management Routes
Route::middleware(['auth'])->prefix('shares')->name('shares.')->group(function () {
    Route::get('/management', function () {
        return view('shares.management');
    })->name('management');

    // Share Products Routes
    Route::resource('products', \App\Http\Controllers\ShareProductController::class)->except(['show']);
    Route::get('products/data', [\App\Http\Controllers\ShareProductController::class, 'getShareProductsData'])->name('products.data');
    Route::get('products/{encodedId}', [\App\Http\Controllers\ShareProductController::class, 'show'])->name('products.show');
    Route::patch('products/{encodedId}/toggle-status', [\App\Http\Controllers\ShareProductController::class, 'toggleStatus'])->name('products.toggle-status');

    // Share Accounts Routes (data route must come BEFORE resource to avoid route conflicts)
    Route::get('accounts/data', [\App\Http\Controllers\ShareAccountController::class, 'getShareAccountsData'])->name('accounts.data');
    Route::get('accounts/export', [\App\Http\Controllers\ShareAccountController::class, 'export'])->name('accounts.export');
    Route::get('accounts/download-template', [\App\Http\Controllers\ShareAccountController::class, 'downloadTemplate'])->name('accounts.download-template');
    Route::post('accounts/import', [\App\Http\Controllers\ShareAccountController::class, 'import'])->name('accounts.import');
    Route::get('accounts/{encodedId}/certificate', [\App\Http\Controllers\ShareAccountController::class, 'printCertificate'])->name('accounts.certificate');
    Route::post('accounts/{encodedId}/change-status', [\App\Http\Controllers\ShareAccountController::class, 'changeStatus'])->name('accounts.change-status');
    Route::get('accounts/{encodedId}/transactions/data', [\App\Http\Controllers\ShareAccountController::class, 'getAccountTransactionsData'])->name('accounts.transactions.data');
    Route::get('accounts/{encodedId}/statement/export', [\App\Http\Controllers\ShareAccountController::class, 'exportStatement'])->name('accounts.statement.export');
    Route::resource('accounts', \App\Http\Controllers\ShareAccountController::class);

    // Share Opening Balance Routes
    Route::get('opening-balance', [\App\Http\Controllers\ShareAccountController::class, 'openingBalanceIndex'])->name('opening-balance.index');
    Route::get('opening-balance/download-template', [\App\Http\Controllers\ShareAccountController::class, 'downloadOpeningBalanceTemplate'])->name('opening-balance.download-template');
    Route::post('opening-balance/import', [\App\Http\Controllers\ShareAccountController::class, 'importOpeningBalance'])->name('opening-balance.import');

    // Share Deposits Routes (data route must come BEFORE resource to avoid route conflicts)
    Route::get('deposits/data', [\App\Http\Controllers\ShareDepositController::class, 'getShareDepositsData'])->name('deposits.data');
    Route::get('deposits/export', [\App\Http\Controllers\ShareDepositController::class, 'export'])->name('deposits.export');
    Route::get('deposits/import', [\App\Http\Controllers\ShareDepositController::class, 'import'])->name('deposits.import');
    Route::post('deposits/import', [\App\Http\Controllers\ShareDepositController::class, 'importStore'])->name('deposits.import.store');
    Route::get('deposits/download-template', [\App\Http\Controllers\ShareDepositController::class, 'downloadTemplate'])->name('deposits.download-template');
    Route::post('deposits/import-opening-balance', [\App\Http\Controllers\ShareDepositController::class, 'importOpeningBalance'])->name('deposits.import-opening-balance');
    Route::get('deposits/download-opening-balance-template', [\App\Http\Controllers\ShareDepositController::class, 'downloadOpeningBalanceTemplate'])->name('deposits.download-opening-balance-template');
    Route::post('deposits/{id}/change-status', [\App\Http\Controllers\ShareDepositController::class, 'changeStatus'])->name('deposits.change-status');
    Route::resource('deposits', \App\Http\Controllers\ShareDepositController::class);

    Route::get('/withdrawals', function () {
        return view('shares.withdrawals.index');
    })->name('withdrawals.index');

    Route::get('/transfers', function () {
        return view('shares.transfers.index');
    })->name('transfers.index');
});

// Dividends Management Routes
Route::middleware(['auth'])->prefix('dividends')->name('dividends.')->group(function () {
    // Profit Allocations Routes
    Route::get('/profit-allocations', [\App\Http\Controllers\DividendController::class, 'profitAllocations'])->name('profit-allocations');
    Route::get('/profit-allocations/data', [\App\Http\Controllers\DividendController::class, 'getProfitAllocationsData'])->name('profit-allocations.data');
    Route::get('/profit-allocations/create', [\App\Http\Controllers\DividendController::class, 'createProfitAllocation'])->name('profit-allocations.create');
    Route::post('/profit-allocations/calculate-profit', [\App\Http\Controllers\DividendController::class, 'calculateProfit'])->name('profit-allocations.calculate-profit');
    Route::post('/profit-allocations', [\App\Http\Controllers\DividendController::class, 'storeProfitAllocation'])->name('profit-allocations.store');
    Route::get('/profit-allocations/{encodedId}', [\App\Http\Controllers\DividendController::class, 'showProfitAllocation'])->name('profit-allocations.show');
    Route::get('/profit-allocations/{encodedId}/edit', [\App\Http\Controllers\DividendController::class, 'editProfitAllocation'])->name('profit-allocations.edit');
    Route::put('/profit-allocations/{encodedId}', [\App\Http\Controllers\DividendController::class, 'updateProfitAllocation'])->name('profit-allocations.update');
    Route::delete('/profit-allocations/{encodedId}', [\App\Http\Controllers\DividendController::class, 'destroyProfitAllocation'])->name('profit-allocations.destroy');
    Route::patch('/profit-allocations/{encodedId}/change-status', [\App\Http\Controllers\DividendController::class, 'changeProfitAllocationStatus'])->name('profit-allocations.change-status');

    // Dividends Routes
    Route::get('/dividends', [\App\Http\Controllers\DividendController::class, 'dividends'])->name('dividends');
    Route::get('/dividends/data', [\App\Http\Controllers\DividendController::class, 'getDividendsData'])->name('dividends.data');
    Route::get('/dividends/create', [\App\Http\Controllers\DividendController::class, 'createDividend'])->name('dividends.create');
    Route::post('/dividends', [\App\Http\Controllers\DividendController::class, 'storeDividend'])->name('dividends.store');
    Route::get('/dividends/{encodedId}', [\App\Http\Controllers\DividendController::class, 'showDividend'])->name('dividends.show');
    Route::post('/dividends/{encodedId}/calculate', [\App\Http\Controllers\DividendController::class, 'calculateDividends'])->name('dividends.calculate');
    Route::post('/dividends/payments/{encodedId}/process', [\App\Http\Controllers\DividendController::class, 'processPayment'])->name('dividends.process-payment');
});

// Laravel Logs Route
Route::get('/log', [LaravelLogsController::class, 'index'])->name('laravel-logs.index')->middleware('auth');
Route::post('/log/clear', [LaravelLogsController::class, 'clearLogs'])->name('laravel-logs.clear')->middleware('auth');

// Language switching
Route::get('/language/{locale}', [LanguageController::class, 'switchLanguage'])->name('language.switch');
// Test language route
Route::get('/test-language', function () {
    return view('test-language');
})->name('test.language');

Route::get('/request-email-otp', [OtpEmailController::class, 'showEmailForm'])->name('email-otp-form');
Route::post('/send-email-otp', [OtpEmailController::class, 'sendOtpEmail'])->name('email-otp-send');


// Reports Route
Route::get('/reports', [App\Http\Controllers\ReportsController::class, 'index'])->middleware('auth')->name('reports.index');
Route::get('/reports/loans', [App\Http\Controllers\ReportsController::class, 'loans'])->middleware('auth')->name('reports.loans');
Route::get('/reports/customers', [App\Http\Controllers\ReportsController::class, 'customers'])->middleware('auth')->name('reports.customers');
Route::get('/reports/shares', [App\Http\Controllers\ReportsController::class, 'shares'])->middleware('auth')->name('reports.shares');
Route::get('/reports/contributions', [App\Http\Controllers\ReportsController::class, 'contributions'])->middleware('auth')->name('reports.contributions');
Route::get("/reports/customers/list", [App\Http\Controllers\Reports\CustomerListReportController::class, "index"])->middleware("auth")->name("reports.customers.list");
Route::get("/reports/customers/list/export", [App\Http\Controllers\Reports\CustomerListReportController::class, "export"])->middleware("auth")->name("reports.customers.list.export");
Route::get("/reports/customers/list/export-pdf", [App\Http\Controllers\Reports\CustomerListReportController::class, "exportPdf"])->middleware("auth")->name("reports.customers.list.export-pdf");
Route::get("/reports/customers/activity", [App\Http\Controllers\Reports\CustomerActivityReportController::class, "index"])->middleware("auth")->name("reports.customers.activity");
Route::get("/reports/customers/activity/export", [App\Http\Controllers\Reports\CustomerActivityReportController::class, "export"])->middleware("auth")->name("reports.customers.activity.export");
Route::get("/reports/customers/activity/export-pdf", [App\Http\Controllers\Reports\CustomerActivityReportController::class, "exportPdf"])->middleware("auth")->name("reports.customers.activity.export-pdf");
Route::get("/reports/customers/performance", [App\Http\Controllers\Reports\CustomerPerformanceReportController::class, "index"])->middleware("auth")->name("reports.customers.performance");
Route::get("/reports/customers/performance/export", [App\Http\Controllers\Reports\CustomerPerformanceReportController::class, "export"])->middleware("auth")->name("reports.customers.performance.export");
Route::get("/reports/customers/performance/export-pdf", [App\Http\Controllers\Reports\CustomerPerformanceReportController::class, "exportPdf"])->middleware("auth")->name("reports.customers.performance.export-pdf");
Route::get("/reports/customers/demographics", [App\Http\Controllers\Reports\CustomerDemographicsReportController::class, "index"])->middleware("auth")->name("reports.customers.demographics");
Route::get("/reports/customers/demographics/export", [App\Http\Controllers\Reports\CustomerDemographicsReportController::class, "export"])->middleware("auth")->name("reports.customers.demographics.export");
Route::get("/reports/customers/demographics/export-pdf", [App\Http\Controllers\Reports\CustomerDemographicsReportController::class, "exportPdf"])->middleware("auth")->name("reports.customers.demographics.export-pdf");
Route::get("/reports/customers/risk-assessment", [App\Http\Controllers\Reports\CustomerRiskAssessmentReportController::class, "index"])->middleware("auth")->name("reports.customers.risk-assessment");
Route::get("/reports/customers/risk-assessment/export", [App\Http\Controllers\Reports\CustomerRiskAssessmentReportController::class, "export"])->middleware("auth")->name("reports.customers.risk-assessment.export");
Route::get("/reports/customers/risk-assessment/export-pdf", [App\Http\Controllers\Reports\CustomerRiskAssessmentReportController::class, "exportPdf"])->middleware("auth")->name("reports.customers.risk-assessment.export-pdf");
Route::get("/reports/customers/communication", [App\Http\Controllers\Reports\CustomerCommunicationReportController::class, "index"])->middleware("auth")->name("reports.customers.communication");
Route::get("/reports/customers/communication/export", [App\Http\Controllers\Reports\CustomerCommunicationReportController::class, "export"])->middleware("auth")->name("reports.customers.communication.export");
Route::get("/reports/customers/communication/export-pdf", [App\Http\Controllers\Reports\CustomerCommunicationReportController::class, "exportPdf"])->middleware("auth")->name("reports.customers.communication.export-pdf");

// Share Reports Routes
Route::prefix('reports/shares')->middleware('auth')->name('reports.shares.')->group(function () {
    Route::get('/share-register', [App\Http\Controllers\Reports\ShareReportController::class, 'shareRegister'])->name('share-register');
    Route::get('/member-ledger', [App\Http\Controllers\Reports\ShareReportController::class, 'memberLedger'])->name('member-ledger');
});

// Contribution Reports Routes
Route::prefix('reports/contributions')->middleware('auth')->name('reports.contributions.')->group(function () {
    Route::get('/contribution-register', [App\Http\Controllers\Reports\ContributionReportController::class, 'contributionRegister'])->name('contribution-register');
    Route::get('/member-ledger', [App\Http\Controllers\Reports\ContributionReportController::class, 'memberLedger'])->name('member-ledger');
});

Route::get('/reports/bot', [App\Http\Controllers\ReportsController::class, 'bot'])->middleware('auth')->name('reports.bot');
// BOT Balance Sheet & Income Statement
Route::prefix('reports/bot')->middleware('auth')->name('reports.bot.')->group(function () {

    // Accounting Reports Index
    Route::get("/accounting-reports", function () {
        return view("reports.index");
    })->name("index");
    Route::get('/balance-sheet', [BotBalanceSheetController::class, 'index'])->name('balance-sheet');
    Route::get('/balance-sheet/export', [BotBalanceSheetController::class, 'export'])->name('balance-sheet.export');
    Route::get('/income-statement', [BotIncomeStatementController::class, 'index'])->name('income-statement');
    Route::get('/income-statement/export', [BotIncomeStatementController::class, 'export'])->name('income-statement.export');
    Route::get('/sectoral-loans', [BotSectoralLoansController::class, 'index'])->name('sectoral-loans');
    Route::get('/sectoral-loans/export', [BotSectoralLoansController::class, 'export'])->name('sectoral-loans.export');
    Route::get('/interest-rates', [BotInterestRatesController::class, 'index'])->name('interest-rates');
    Route::get('/interest-rates/export', [BotInterestRatesController::class, 'export'])->name('interest-rates.export');
    Route::get('/liquid-assets', [BotLiquidAssetsController::class, 'index'])->name('liquid-assets');
    Route::get('/liquid-assets/export', [BotLiquidAssetsController::class, 'export'])->name('liquid-assets.export');
    Route::get('/complaints', [BotComplaintsReportController::class, 'index'])->name('complaints');
    Route::get('/complaints/export', [BotComplaintsReportController::class, 'export'])->name('complaints.export');
    Route::get('/deposits-borrowings', [BotDepositsBorrowingsController::class, 'index'])->name('deposits-borrowings');
    Route::get('/deposits-borrowings/export', [BotDepositsBorrowingsController::class, 'export'])->name('deposits-borrowings.export');
    Route::get('/agent-banking', [BotAgentBankingController::class, 'index'])->name('agent-banking');
    Route::get('/agent-banking/export', [BotAgentBankingController::class, 'export'])->name('agent-banking.export');
    Route::get('/loans-disbursed', [BotLoansDisbursedController::class, 'index'])->name('loans-disbursed');
    Route::get('/loans-disbursed/export', [BotLoansDisbursedController::class, 'export'])->name('loans-disbursed.export');
    Route::get('/geographical-distribution', [BotGeographicalDistributionController::class, 'index'])->name('geographical-distribution');
    Route::get('/geographical-distribution/export', [BotGeographicalDistributionController::class, 'export'])->name('geographical-distribution.export');
});

////////////////////////////////////////ROLES & PERMISSIONSMANAGEMENT /////////////////////////////////////////////
Route::middleware(['auth'])->group(function () {
    // Explicit route model binding for Role
    Route::model('role', \App\Models\Role::class);

    // Roles management
    Route::get('roles', [RolePermissionController::class, 'index'])->name('roles.index');
    Route::get('roles/create', [RolePermissionController::class, 'create'])->name('roles.create');
    Route::post('roles', [RolePermissionController::class, 'store'])->name('roles.store');
    Route::get('roles/{role}', [RolePermissionController::class, 'show'])->name('roles.show');
    Route::get('roles/{role}/edit', [RolePermissionController::class, 'edit'])->name('roles.edit');
    Route::match(['PUT', 'PATCH'], 'roles/{role}', [RolePermissionController::class, 'update'])->name('roles.update');
    Route::delete('roles/{role}', [RolePermissionController::class, 'destroy'])->name('roles.destroy');

    // Menu management for roles
    Route::get('roles/{role}/menus', [RolePermissionController::class, 'manageMenus'])->name('roles.menus');
    Route::post('roles/{role}/menus/assign', [RolePermissionController::class, 'assignMenus'])->name('roles.menus.assign');
    Route::delete('roles/{role}/menus/remove', [RolePermissionController::class, 'removeMenu'])->name('roles.menus.remove');

    // Permissions management
    Route::get('permissions', [RolePermissionController::class, 'permissions'])->name('permissions.index');
    Route::post('permissions', [RolePermissionController::class, 'createPermission'])->name('permissions.store');
    Route::delete('permissions/{permission}', [RolePermissionController::class, 'deletePermission'])->name('permissions.destroy');



    // User role assignment
    Route::post('users/{user}/assign-roles', [RolePermissionController::class, 'assignToUser'])->name('users.assign-roles');
    Route::delete('users/{user}/remove-role', [RolePermissionController::class, 'removeFromUser'])->name('users.remove-role');

    // Role statistics
    Route::get('roles-stats', [RolePermissionController::class, 'getStats'])->name('roles.stats');
});
////////////////////////////////////////////// END ROLES & PERMISSIONS MANAGEMENT //////////////////////////////////////////

////////////////////////////////////////////// USER MANAGEMENT /////////////////////////////////////////////////////

// Additional user routes (must come BEFORE resource route)
Route::get('/users/profile', [UserController::class, 'profile'])->name('users.profile')->middleware('auth');
Route::put('/users/profile', [UserController::class, 'updateProfile'])->name('users.profile.update')->middleware('auth');

Route::resource('users', UserController::class)->middleware(['auth', 'company.scope']);

// Additional user routes that require user parameter

Route::patch('/users/{user}/status', [UserController::class, 'changeStatus'])->name('users.status')->middleware(['auth', 'company.scope']);
Route::post('/users/{user}/roles', [UserController::class, 'assignRoles'])->name('users.roles')->middleware(['auth', 'company.scope']);
Route::post('/users/{user}/assign-branches', [UserController::class, 'assignBranches'])->name('users.assign-branches')->middleware(['auth', 'company.scope']);

////////////////////////////////////////////// END /////////////////////////////////////////////////////////////////

////////////////////////////////////////////// SETTINGS ROUTES ////////////////////////////////////////////////

Route::prefix('settings')->name('settings.')->middleware(['auth', 'company.scope'])->group(function () {

    //Filetypes settings
    Route::resource('filetypes', FiletypeController::class);

    //Journal References settings
    Route::resource('journal-references', \App\Http\Controllers\JournalReferenceController::class);

    //Complain Categories settings
    Route::resource('complain-categories', \App\Http\Controllers\Settings\ComplainCategoryController::class);

    //Announcements settings
    Route::resource('announcements', \App\Http\Controllers\Settings\AnnouncementController::class);

    Route::get('/', [SettingsController::class, 'index'])->name('index');

    // Company Settings
    Route::get('/company', [SettingsController::class, 'companySettings'])->name('company');
    Route::put('/company', [SettingsController::class, 'updateCompanySettings'])->name('company.update');

    // Branch Settings
    Route::get('/branches', [SettingsController::class, 'branchSettings'])->name('branches');
    Route::get('/branches/create', [SettingsController::class, 'createBranch'])->name('branches.create');
    Route::post('/branches', [SettingsController::class, 'storeBranch'])->name('branches.store');
    Route::get('/branches/{branch}/edit', [SettingsController::class, 'editBranch'])->name('branches.edit');
    Route::put('/branches/{branch}', [SettingsController::class, 'updateBranch'])->name('branches.update');
    Route::delete('/branches/{branch}', [SettingsController::class, 'destroyBranch'])->name('branches.destroy');

    // User Settings
    Route::get('/user', [SettingsController::class, 'userSettings'])->name('user');
    Route::put('/user', [SettingsController::class, 'updateUserSettings'])->name('user.update');

    // System Settings
    Route::get('/system', [SettingsController::class, 'systemSettings'])->name('system');
    Route::put('/system', [SettingsController::class, 'updateSystemSettings'])->name('system.update');
    Route::post('/system/reset', [SettingsController::class, 'resetSystemSettings'])->name('system.reset');
    Route::post('/system/test-email', [SettingsController::class, 'testEmailConfig'])->name('system.test-email');

    // Backup Settings
    Route::get('/backup', [SettingsController::class, 'backupSettings'])->name('backup');
    Route::post('/backup/create', [SettingsController::class, 'createBackup'])->name('backup.create');
    Route::post('/backup/restore', [SettingsController::class, 'restoreBackup'])->name('backup.restore');
    Route::get('/backup/{hash_id}/download', [SettingsController::class, 'downloadBackup'])->name('backup.download');
    Route::delete('/backup/{hash_id}', [SettingsController::class, 'deleteBackup'])->name('backup.delete');
    Route::post('/backup/clean', [SettingsController::class, 'cleanOldBackups'])->name('backup.clean');

    // AI Assistant Settings
    Route::get('/ai', [SettingsController::class, 'aiAssistantSettings'])->name('ai');
    Route::post('/ai/chat', [SettingsController::class, 'aiChat'])->name('ai.chat')->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    Route::get('/ai/test', function () {
        return response()->json([
            'csrf_token' => csrf_token(),
            'status' => 'success',
            'message' => 'AI Assistant connection test successful'
        ]);
    })->name('ai.test');

    // Penalty Settings
    Route::get('/penalty', [SettingsController::class, 'penaltySettings'])->name('penalty');
    Route::put('/penalty', [SettingsController::class, 'updatePenaltySettings'])->name('penalty.update');
    //////logs route///
    Route::get('/logs', [ActivityLogsController::class, 'index'])->name('logs.index');

    // Fees Settings
    Route::get('/fees', [SettingsController::class, 'feesSettings'])->name('fees');
    Route::put('/fees', [SettingsController::class, 'updateFeesSettings'])->name('fees.update');

    // SMS Settings
    Route::get('/sms', [SettingsController::class, 'smsSettings'])->name('sms');
    Route::put('/sms', [SettingsController::class, 'updateSmsSettings'])->name('sms.update');
    Route::post('/sms/test', [SettingsController::class, 'testSmsSettings'])->name('sms.test');

    // Payment Voucher Approval Settings
    Route::get('/payment-voucher-approval', [SettingsController::class, 'paymentVoucherApprovalSettings'])->name('payment-voucher-approval');
    Route::put('/payment-voucher-approval', [SettingsController::class, 'updatePaymentVoucherApprovalSettings'])->name('payment-voucher-approval.update');

    // Opening Balance Accounts Settings
    Route::get('/opening-balance-accounts', [SettingsController::class, 'openingBalanceAccountsSettings'])->name('opening-balance-accounts');
    Route::put('/opening-balance-accounts', [SettingsController::class, 'updateOpeningBalanceAccountsSettings'])->name('opening-balance-accounts.update');

    // Opening Balance Logs
    Route::get('/opening-balance-logs', [SettingsController::class, 'openingBalanceLogsIndex'])->name('opening-balance-logs.index');
    Route::get('/opening-balance-logs/data', [SettingsController::class, 'getOpeningBalanceLogsData'])->name('opening-balance-logs.data');

        // Inventory Settings
    Route::get('/inventory', [SettingsController::class, 'inventorySettings'])->name('inventory');
    Route::put('/inventory', [SettingsController::class, 'updateInventorySettings'])->name('inventory.update');

    // Inventory Locations
    Route::get('/inventory-settings/locations', [SettingsController::class, 'inventoryLocations'])->name('inventory.locations.index');
    Route::get('/inventory-settings/locations/create', [SettingsController::class, 'createInventoryLocation'])->name('inventory.locations.create');
    Route::post('/inventory-settings/locations', [SettingsController::class, 'storeInventoryLocation'])->name('inventory.locations.store');
    Route::get('/inventory-settings/locations/{location}', [SettingsController::class, 'showInventoryLocation'])->name('inventory.locations.show');
    Route::get('/inventory-settings/locations/{location}/edit', [SettingsController::class, 'editInventoryLocation'])->name('inventory.locations.edit');
    Route::put('/inventory-settings/locations/{location}', [SettingsController::class, 'updateInventoryLocation'])->name('inventory.locations.update');
    Route::delete('/inventory-settings/locations/{location}', [SettingsController::class, 'destroyInventoryLocation'])->name('inventory.locations.destroy');

    // Bulk Email Settings (Super Admin only)
    Route::middleware(['role:super-admin'])->group(function () {
        Route::get('/bulk-email', [\App\Http\Controllers\BulkEmailController::class, 'index'])->name('bulk-email');
        Route::post('/bulk-email/send', [\App\Http\Controllers\BulkEmailController::class, 'send'])->name('bulk-email.send');
        Route::get('/bulk-email/recipients', [\App\Http\Controllers\BulkEmailController::class, 'getRecipients'])->name('bulk-email.recipients');
    });
});

////////////////////////////////////////////// END SETTINGS ROUTES /////////////////////////////////////////////


////////////////////////////////////////////// INVENTORY MANAGEMENT ///////////////////////////////////////////

Route::prefix('inventory')->name('inventory.')->middleware(['auth', 'company.scope'])->group(function () {
    // Inventory Management Dashboard
    Route::get('/', [InventoryController::class, 'index'])->name('index');

    // Inventory Items
    Route::get('/items', [ItemController::class, 'index'])->name('items.index');
    Route::get('/items/create', [ItemController::class, 'create'])->name('items.create');
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');
    Route::post('/items/import', [ItemController::class, 'import'])->name('items.import');
    Route::get('/items/import-status/{batchId}', [ItemController::class, 'importStatus'])->name('items.import-status');
    Route::get('/items/download-template', [ItemController::class, 'downloadTemplate'])->name('items.download-template');
    Route::get('/items/export', [ItemController::class, 'export'])->name('items.export');
    Route::get('/items/{encodedId}', [ItemController::class, 'show'])->name('items.show');
    Route::get('/items/{encodedId}/movements', [ItemController::class, 'movements'])->name('items.movements');
    Route::get('/items/{encodedId}/stock', [ItemController::class, 'getItemStock'])->name('items.stock');
    Route::get('/items/{encodedId}/edit', [ItemController::class, 'edit'])->name('items.edit');
    Route::put('/items/{encodedId}', [ItemController::class, 'update'])->name('items.update');
    Route::delete('/items/{encodedId}', [ItemController::class, 'destroy'])->name('items.destroy');

    // Stock Reports
    Route::get('/stock-report', [ItemController::class, 'getStockReport'])->name('stock.report');
    Route::get('/location/{locationId}/stock', [ItemController::class, 'getLocationStock'])->name('location.stock');

    // Inventory Categories (use hash ids)
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{encodedId}', [CategoryController::class, 'show'])->name('categories.show');
    Route::get('/categories/{encodedId}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{encodedId}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{encodedId}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Stock Movements
    Route::get('/movements', [MovementController::class, 'index'])->name('movements.index');
    Route::get('/movements/create', [MovementController::class, 'create'])->name('movements.create');
    Route::post('/movements', [MovementController::class, 'store'])->name('movements.store');
    Route::get('/movements/{movement}', [MovementController::class, 'show'])->name('movements.show');
    Route::get('/movements/{movement}/edit', [MovementController::class, 'edit'])->name('movements.edit');
    Route::put('/movements/{movement}', [MovementController::class, 'update'])->name('movements.update');
    Route::delete('/movements/{movement}', [MovementController::class, 'destroy'])->name('movements.destroy');

    // Write-offs
    Route::get('/write-offs', [WriteOffController::class, 'index'])->name('write-offs.index');
    Route::get('/write-offs/create', [WriteOffController::class, 'create'])->name('write-offs.create');
    Route::post('/write-offs', [WriteOffController::class, 'store'])->name('write-offs.store');
    Route::get('/write-offs/{movement}', [WriteOffController::class, 'show'])->name('write-offs.show');
    Route::get('/write-offs/{movement}/edit', [WriteOffController::class, 'edit'])->name('write-offs.edit');
    Route::put('/write-offs/{movement}', [WriteOffController::class, 'update'])->name('write-offs.update');
    Route::delete('/write-offs/{movement}', [WriteOffController::class, 'destroy'])->name('write-offs.destroy');

    // Opening Balances
    Route::get('/opening-balances', [OpeningBalanceController::class, 'index'])->name('opening-balances.index');
    Route::get('/opening-balances/create', [OpeningBalanceController::class, 'create'])->name('opening-balances.create');
    Route::post('/opening-balances', [OpeningBalanceController::class, 'store'])->name('opening-balances.store');
    Route::post('/opening-balances/import', [OpeningBalanceController::class, 'import'])->name('opening-balances.import');
    Route::get('/opening-balances/download-template', [OpeningBalanceController::class, 'downloadTemplate'])->name('opening-balances.download-template');
    Route::get('/opening-balances/{openingBalance}', [OpeningBalanceController::class, 'show'])->name('opening-balances.show');
    Route::get('/opening-balances/{openingBalance}/edit', [OpeningBalanceController::class, 'edit'])->name('opening-balances.edit');
    Route::put('/opening-balances/{openingBalance}', [OpeningBalanceController::class, 'update'])->name('opening-balances.update');
    Route::delete('/opening-balances/{openingBalance}', [OpeningBalanceController::class, 'destroy'])->name('opening-balances.destroy');

    // API: Get locations by branch (must be before parameterized routes)
    Route::get('/api/branches/{branchId}/locations', [TransferController::class, 'getBranchLocations'])->name('api.branches.locations');

    // Transfers
    Route::get('/transfers', [TransferController::class, 'index'])->name('transfers.index');
    Route::get('/transfers/create', [TransferController::class, 'create'])->name('transfers.create');
    Route::post('/transfers', [TransferController::class, 'store'])->name('transfers.store');
    Route::get('/transfers/{transfer}', [TransferController::class, 'show'])->name('transfers.show');
    Route::get('/transfers/{transfer}/edit', [TransferController::class, 'edit'])->name('transfers.edit');
    Route::put('/transfers/{transfer}', [TransferController::class, 'update'])->name('transfers.update');
    Route::delete('/transfers/{transfer}', [TransferController::class, 'destroy'])->name('transfers.destroy');

    // Bulk Transfer Operations
    Route::post('/transfers/bulk-delete', [TransferController::class, 'bulkDelete'])->name('transfers.bulk-delete');
    Route::get('/transfers/bulk-edit', [TransferController::class, 'bulkEdit'])->name('transfers.bulk-edit');
    Route::put('/transfers/bulk-update', [TransferController::class, 'bulkUpdate'])->name('transfers.bulk-update');

    // Transfer Requests
    Route::get('/transfer-requests', [TransferRequestController::class, 'index'])->name('transfer-requests.index');
    Route::get('/transfer-requests/create', [TransferRequestController::class, 'create'])->name('transfer-requests.create');
    Route::post('/transfer-requests', [TransferRequestController::class, 'store'])->name('transfer-requests.store');
    Route::get('/transfer-requests/{transferRequest}', [TransferRequestController::class, 'show'])->name('transfer-requests.show');
    Route::get('/transfer-requests/{transferRequest}/edit', [TransferRequestController::class, 'edit'])->name('transfer-requests.edit');
    Route::put('/transfer-requests/{transferRequest}', [TransferRequestController::class, 'update'])->name('transfer-requests.update');
    Route::post('/transfer-requests/{transferRequest}/approve', [TransferRequestController::class, 'approve'])->name('transfer-requests.approve');
    Route::post('/transfer-requests/{transferRequest}/reject', [TransferRequestController::class, 'reject'])->name('transfer-requests.reject');


    // Inventory Count Routes
    Route::prefix('counts')->name('counts.')->group(function () {
        Route::get('/', [App\Http\Controllers\Inventory\InventoryCountController::class, 'index'])->name('index');

        // Count Periods
        Route::get('/periods/create', [App\Http\Controllers\Inventory\InventoryCountController::class, 'createPeriod'])->name('periods.create');
        Route::post('/periods', [App\Http\Controllers\Inventory\InventoryCountController::class, 'storePeriod'])->name('periods.store');
        Route::get('/periods/{encodedId}', [App\Http\Controllers\Inventory\InventoryCountController::class, 'showPeriod'])->name('periods.show');

        // Count Sessions
        Route::get('/sessions/create/{periodEncodedId}', [App\Http\Controllers\Inventory\InventoryCountController::class, 'createSession'])->name('sessions.create');
        Route::post('/sessions/{periodEncodedId}', [App\Http\Controllers\Inventory\InventoryCountController::class, 'storeSession'])->name('sessions.store');
        Route::get('/sessions/{encodedId}', [App\Http\Controllers\Inventory\InventoryCountController::class, 'showSession'])->name('sessions.show');
        Route::post('/sessions/{encodedId}/freeze', [App\Http\Controllers\Inventory\InventoryCountController::class, 'freezeSession'])->name('sessions.freeze');
        Route::post('/sessions/{encodedId}/start-counting', [App\Http\Controllers\Inventory\InventoryCountController::class, 'startCounting'])->name('sessions.start-counting');
        Route::post('/sessions/{encodedId}/complete-counting', [App\Http\Controllers\Inventory\InventoryCountController::class, 'completeCounting'])->name('sessions.complete-counting');
        Route::post('/sessions/{encodedId}/approve', [App\Http\Controllers\Inventory\InventoryCountController::class, 'approveCountSession'])->name('sessions.approve');
        Route::post('/sessions/{encodedId}/reject', [App\Http\Controllers\Inventory\InventoryCountController::class, 'rejectCountSession'])->name('sessions.reject');
        Route::get('/sessions/{encodedId}/variances', [App\Http\Controllers\Inventory\InventoryCountController::class, 'showVariances'])->name('sessions.variances');
        Route::get('/sessions/{encodedId}/export-counting-sheets-pdf', [App\Http\Controllers\Inventory\InventoryCountController::class, 'exportCountingSheetsPdf'])->name('sessions.export-counting-sheets-pdf');
        Route::get('/sessions/{encodedId}/export-counting-sheets-excel', [App\Http\Controllers\Inventory\InventoryCountController::class, 'exportCountingSheetsExcel'])->name('sessions.export-counting-sheets-excel');
        Route::get('/sessions/{encodedId}/assign-team', [App\Http\Controllers\Inventory\InventoryCountController::class, 'showTeamAssignment'])->name('sessions.assign-team');
        Route::post('/sessions/{encodedId}/assign-team', [App\Http\Controllers\Inventory\InventoryCountController::class, 'assignTeam'])->name('sessions.assign-team.store');
        Route::get('/sessions/{encodedId}/download-counting-template', [App\Http\Controllers\Inventory\InventoryCountController::class, 'downloadCountingTemplate'])->name('sessions.download-counting-template');
        Route::post('/sessions/{encodedId}/upload-counting-excel', [App\Http\Controllers\Inventory\InventoryCountController::class, 'uploadCountingExcel'])->name('sessions.upload-counting-excel');

        // Count Entries
        Route::get('/entries/{encodedId}', [App\Http\Controllers\Inventory\InventoryCountController::class, 'showEntry'])->name('entries.show');
        Route::post('/entries/{encodedId}/update-physical-qty', [App\Http\Controllers\Inventory\InventoryCountController::class, 'updatePhysicalQuantity'])->name('entries.update-physical-qty');
        Route::post('/entries/{encodedId}/recount', [App\Http\Controllers\Inventory\InventoryCountController::class, 'requestRecount'])->name('entries.recount');
        Route::post('/entries/{encodedId}/verify', [App\Http\Controllers\Inventory\InventoryCountController::class, 'verifyEntry'])->name('entries.verify');

        // Variances
        Route::post('/variances/{encodedId}/investigation', [App\Http\Controllers\Inventory\InventoryCountController::class, 'updateVarianceInvestigation'])->name('variances.investigation');

        // Adjustments
        Route::get('/sessions/{encodedId}/adjustments', [App\Http\Controllers\Inventory\InventoryCountController::class, 'showAdjustments'])->name('sessions.adjustments');
        Route::get('/adjustments/create/{varianceId}', [App\Http\Controllers\Inventory\InventoryCountController::class, 'createAdjustmentForm'])->name('adjustments.create-form');
        Route::post('/adjustments/create/{varianceId}', [App\Http\Controllers\Inventory\InventoryCountController::class, 'createAdjustment'])->name('adjustments.create');
        Route::post('/adjustments/bulk-create/{encodedId}', [App\Http\Controllers\Inventory\InventoryCountController::class, 'bulkCreateAdjustments'])->name('adjustments.bulk-create');
        Route::post('/adjustments/bulk-approve/{encodedId}', [App\Http\Controllers\Inventory\InventoryCountController::class, 'bulkApproveAdjustments'])->name('adjustments.bulk-approve');
        Route::post('/adjustments/bulk-post/{encodedId}', [App\Http\Controllers\Inventory\InventoryCountController::class, 'bulkPostAdjustmentsToGL'])->name('adjustments.bulk-post');
        Route::get('/adjustments/{encodedId}', [App\Http\Controllers\Inventory\InventoryCountController::class, 'showAdjustment'])->name('adjustments.show');
        Route::post('/adjustments/{encodedId}/approve', [App\Http\Controllers\Inventory\InventoryCountController::class, 'approveAdjustment'])->name('adjustments.approve');
        Route::post('/adjustments/{encodedId}/reject', [App\Http\Controllers\Inventory\InventoryCountController::class, 'rejectAdjustment'])->name('adjustments.reject');
        Route::post('/adjustments/{encodedId}/post-to-gl', [App\Http\Controllers\Inventory\InventoryCountController::class, 'postAdjustmentToGL'])->name('adjustments.post-to-gl');
    });

    // Inventory Reports Routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [App\Http\Controllers\Inventory\InventoryReportController::class, 'index'])->name('index');
        Route::get('/stock-on-hand', [App\Http\Controllers\Inventory\InventoryReportController::class, 'stockOnHand'])->name('stock-on-hand');
        Route::get('/stock-on-hand/export/excel', [App\Http\Controllers\Inventory\InventoryReportController::class, 'stockOnHandExportExcel'])->name('stock-on-hand.export.excel');
        Route::get('/stock-on-hand/export/pdf', [App\Http\Controllers\Inventory\InventoryReportController::class, 'stockOnHandExportPdf'])->name('stock-on-hand.export.pdf');
        Route::get('/stock-valuation', [App\Http\Controllers\Inventory\InventoryReportController::class, 'stockValuation'])->name('stock-valuation');
        Route::get('/movement-register', [App\Http\Controllers\Inventory\InventoryReportController::class, 'movementRegister'])->name('movement-register');
        Route::get('/movement-register/export/excel', [App\Http\Controllers\Inventory\InventoryReportController::class, 'movementRegisterExportExcel'])->name('movement-register.export.excel');
        Route::get('/movement-register/export/pdf', [App\Http\Controllers\Inventory\InventoryReportController::class, 'movementRegisterExportPdf'])->name('movement-register.export.pdf');
        Route::get('/aging-stock', [App\Http\Controllers\Inventory\InventoryReportController::class, 'agingStock'])->name('aging-stock');
        Route::get('/reorder', [App\Http\Controllers\Inventory\InventoryReportController::class, 'reorderReport'])->name('reorder');
        Route::get('/reorder/export/excel', [App\Http\Controllers\Inventory\InventoryReportController::class, 'reorderReportExportExcel'])->name('reorder.export.excel');
        Route::get('/reorder/export/pdf', [App\Http\Controllers\Inventory\InventoryReportController::class, 'reorderReportExportPdf'])->name('reorder.export.pdf');
        Route::get('/over-understock', [App\Http\Controllers\Inventory\InventoryReportController::class, 'overUnderstock'])->name('over-understock');
        Route::get('/over-understock/export/excel', [App\Http\Controllers\Inventory\InventoryReportController::class, 'overUnderstockExportExcel'])->name('over-understock.export.excel');
        Route::get('/over-understock/export/pdf', [App\Http\Controllers\Inventory\InventoryReportController::class, 'overUnderstockExportPdf'])->name('over-understock.export.pdf');
        Route::get('/item-ledger', [App\Http\Controllers\Inventory\InventoryReportController::class, 'itemLedger'])->name('item-ledger');
        Route::get('/item-ledger/export/excel', [App\Http\Controllers\Inventory\InventoryReportController::class, 'itemLedgerExportExcel'])->name('item-ledger.export.excel');
        Route::get('/item-ledger/export/pdf', [App\Http\Controllers\Inventory\InventoryReportController::class, 'itemLedgerExportPdf'])->name('item-ledger.export.pdf');
        Route::get('/cost-changes', [App\Http\Controllers\Inventory\InventoryReportController::class, 'costChanges'])->name('cost-changes');
        Route::get('/cost-changes/export/excel', [App\Http\Controllers\Inventory\InventoryReportController::class, 'costChangesExportExcel'])->name('cost-changes.export.excel');
        Route::get('/cost-changes/export/pdf', [App\Http\Controllers\Inventory\InventoryReportController::class, 'costChangesExportPdf'])->name('cost-changes.export.pdf');
        Route::get('/stock-take-variance', [App\Http\Controllers\Inventory\InventoryReportController::class, 'stockTakeVariance'])->name('stock-take-variance');
        Route::get('/full-inventory-count', [App\Http\Controllers\Inventory\InventoryReportController::class, 'fullInventoryCountReport'])->name('full-inventory-count');
        Route::get('/variance-summary', [App\Http\Controllers\Inventory\InventoryReportController::class, 'varianceSummaryReport'])->name('variance-summary');
        Route::get('/variance-value', [App\Http\Controllers\Inventory\InventoryReportController::class, 'varianceValueReport'])->name('variance-value');
        Route::get('/high-value-scorecard', [App\Http\Controllers\Inventory\InventoryReportController::class, 'highValueItemsScorecard'])->name('high-value-scorecard');
        Route::get('/expiry-damaged-stock', [App\Http\Controllers\Inventory\InventoryReportController::class, 'expiryDamagedStockReport'])->name('expiry-damaged-stock');
        Route::get('/cycle-count-performance', [App\Http\Controllers\Inventory\InventoryReportController::class, 'cycleCountPerformanceReport'])->name('cycle-count-performance');
        Route::get('/year-end-stock-valuation', [App\Http\Controllers\Inventory\InventoryReportController::class, 'yearEndStockValuationReport'])->name('year-end-stock-valuation');
        Route::get('/location-bin', [App\Http\Controllers\Inventory\InventoryReportController::class, 'locationBin'])->name('location-bin');
        Route::get('/category-brand-mix', [App\Http\Controllers\Inventory\InventoryReportController::class, 'categoryBrandMix'])->name('category-brand-mix');
        Route::get('/category-brand-mix/export/excel', [App\Http\Controllers\Inventory\InventoryReportController::class, 'categoryBrandMixExportExcel'])->name('category-brand-mix.export.excel');
        Route::get('/category-brand-mix/export/pdf', [App\Http\Controllers\Inventory\InventoryReportController::class, 'categoryBrandMixExportPdf'])->name('category-brand-mix.export.pdf');
        Route::get('/profit-margin', [App\Http\Controllers\Inventory\InventoryReportController::class, 'profitMargin'])->name('profit-margin');
        Route::get('/profit-margin/export/excel', [App\Http\Controllers\Inventory\InventoryReportController::class, 'profitMarginExportExcel'])->name('profit-margin.export.excel');
        Route::get('/profit-margin/export/pdf', [App\Http\Controllers\Inventory\InventoryReportController::class, 'profitMarginExportPdf'])->name('profit-margin.export.pdf');
        Route::get('/inventory-value-summary', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryValueSummary'])->name('inventory-value-summary');
        Route::get('/inventory-value-summary/export/pdf', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryValueSummaryExportPdf'])->name('inventory-value-summary.export.pdf');
        Route::get('/inventory-value-summary/export/excel', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryValueSummaryExportExcel'])->name('inventory-value-summary.export.excel');

        // Inventory Quantity Summary
        Route::get('/inventory-quantity-summary', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryQuantitySummary'])->name('inventory-quantity-summary');
        Route::get('/inventory-quantity-summary/export/pdf', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryQuantitySummaryExportPdf'])->name('inventory-quantity-summary.export.pdf');
        Route::get('/inventory-quantity-summary/export/excel', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryQuantitySummaryExportExcel'])->name('inventory-quantity-summary.export.excel');

        // Inventory Profit Margin
        Route::get('/inventory-profit-margin', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryProfitMargin'])->name('inventory-profit-margin');
        Route::get('/inventory-profit-margin/export/pdf', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryProfitMarginExportPdf'])->name('inventory-profit-margin.export.pdf');
        Route::get('/inventory-profit-margin/export/excel', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryProfitMarginExportExcel'])->name('inventory-profit-margin.export.excel');

        // Inventory Price List
        Route::get('/inventory-price-list', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryPriceList'])->name('inventory-price-list');
        Route::get('/inventory-price-list/export/pdf', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryPriceListExportPdf'])->name('inventory-price-list.export.pdf');
        Route::get('/inventory-price-list/export/excel', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryPriceListExportExcel'])->name('inventory-price-list.export.excel');

        // Inventory Costing Calculation Worksheet
        Route::get('/inventory-costing-worksheet', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryCostingWorksheet'])->name('inventory-costing-worksheet');
        Route::get('/inventory-costing-worksheet/export/pdf', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryCostingWorksheetExportPdf'])->name('inventory-costing-worksheet.export.pdf');
        Route::get('/inventory-costing-worksheet/export/excel', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryCostingWorksheetExportExcel'])->name('inventory-costing-worksheet.export.excel');

        // Inventory Quantity by Location
        Route::get('/inventory-quantity-by-location', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryQuantityByLocation'])->name('inventory-quantity-by-location');
        Route::get('/inventory-quantity-by-location/export/pdf', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryQuantityByLocationExportPdf'])->name('inventory-quantity-by-location.export.pdf');
        Route::get('/inventory-quantity-by-location/export/excel', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryQuantityByLocationExportExcel'])->name('inventory-quantity-by-location.export.excel');

        // Inventory Transfer Movement Report
        Route::get('/inventory-transfer-movement', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryTransferMovement'])->name('inventory-transfer-movement');
        Route::get('/inventory-transfer-movement/export/pdf', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryTransferMovementExportPdf'])->name('inventory-transfer-movement.export.pdf');
        Route::get('/inventory-transfer-movement/export/excel', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryTransferMovementExportExcel'])->name('inventory-transfer-movement.export.excel');

        // Inventory Aging Report
        Route::get('/inventory-aging', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryAging'])->name('inventory-aging');
        Route::get('/inventory-aging/export/pdf', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryAgingExportPdf'])->name('inventory-aging.export.pdf');
        Route::get('/inventory-aging/export/excel', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryAgingExportExcel'])->name('inventory-aging.export.excel');

        // Category Performance Report
        Route::get('/category-performance', [App\Http\Controllers\Inventory\InventoryReportController::class, 'categoryPerformance'])->name('category-performance');
        Route::get('/category-performance/export/pdf', [App\Http\Controllers\Inventory\InventoryReportController::class, 'categoryPerformanceExportPdf'])->name('category-performance.export.pdf');
        Route::get('/category-performance/export/excel', [App\Http\Controllers\Inventory\InventoryReportController::class, 'categoryPerformanceExportExcel'])->name('category-performance.export.excel');

        // Expiry Reports
        Route::prefix('expiry')->name('expiry.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Inventory\ExpiryReportController::class, 'index'])->name('index');
            Route::get('/expiring-soon', [\App\Http\Controllers\Inventory\ExpiryReportController::class, 'expiringSoon'])->name('expiring-soon');
            Route::get('/expired', [\App\Http\Controllers\Inventory\ExpiryReportController::class, 'expired'])->name('expired');
            Route::post('/stock-details', [\App\Http\Controllers\Inventory\ExpiryReportController::class, 'stockDetails'])->name('stock-details');
        });
    });
});

////////////////////////////////////////////// SUBSCRIPTION MANAGEMENT ///////////////////////////////////////////

Route::prefix('subscriptions')->name('subscriptions.')->middleware(['auth', 'role:super-admin'])->group(function () {
    // Subscription Dashboard
    Route::get('/dashboard', [SubscriptionController::class, 'dashboard'])->name('dashboard');

    // Subscription CRUD
    Route::get('/', [SubscriptionController::class, 'index'])->name('index');
    Route::get('/create', [SubscriptionController::class, 'create'])->name('create');
    Route::post('/', [SubscriptionController::class, 'store'])->name('store');
    Route::get('/{subscription}', [SubscriptionController::class, 'show'])->name('show');
    Route::get('/{subscription}/edit', [SubscriptionController::class, 'edit'])->name('edit');
    Route::put('/{subscription}', [SubscriptionController::class, 'update'])->name('update');
    Route::delete('/{subscription}', [SubscriptionController::class, 'destroy'])->name('destroy');

    // Subscription Actions
    Route::post('/{subscription}/mark-paid', [SubscriptionController::class, 'markAsPaid'])->name('mark-paid');
    Route::post('/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('cancel');
    Route::post('/{subscription}/renew', [SubscriptionController::class, 'renew'])->name('renew');
    Route::post('/{subscription}/extend', [SubscriptionController::class, 'extend'])->name('extend');
});

// Ticker Messages API - Only for subscription expiry alerts
Route::get('/api/ticker-messages', function () {
    // Get subscription alerts - only show ticker if there are expiring subscriptions
    $expiringSubscriptions = \App\Models\Subscription::where('status', 'active')
        ->where('end_date', '<=', now()->addDays(5))
        ->where('end_date', '>=', now())
        ->with('company')
        ->get();

    // If no expiring subscriptions, return empty messages to hide ticker
    if ($expiringSubscriptions->count() == 0) {
        return response()->json([
            'success' => true,
            'messages' => [],
            'show_ticker' => false,
            'timestamp' => now()->toISOString()
        ]);
    }

    $messages = [];
    $now = now();

    // Build subscription expiry messages
    foreach ($expiringSubscriptions as $subscription) {
        $daysLeft = floor($now->diffInDays($subscription->end_date, false));
        $urgency = $daysLeft <= 1 ? 'urgent' : ($daysLeft <= 3 ? 'warning' : 'info');

        $daysText = $daysLeft == 0 ? 'expires today' : ($daysLeft == 1 ? 'expires tomorrow' : "expires in {$daysLeft} days");

        $messages[] = [
            'text' => "⚠️ URGENT: {$subscription->company->name} subscription ({$subscription->plan_name}) {$daysText} - Amount: " . number_format($subscription->amount, 2) . " {$subscription->currency}",
            'type' => $urgency,
            'icon' => 'bx-credit-card',
            'subscription_id' => $subscription->id,
            'company_name' => $subscription->company->name,
            'days_left' => $daysLeft,
            'expiry_date' => $subscription->end_date->format('M d, Y')
        ];
    }

    // Add a general reminder message
    $messages[] = [
        'text' => "🔔 Action Required: Please renew expiring subscriptions to avoid service interruption",
        'type' => 'urgent',
        'icon' => 'bx-bell'
    ];

    return response()->json([
        'success' => true,
        'messages' => $messages,
        'show_ticker' => true,
        'expiring_count' => $expiringSubscriptions->count(),
        'timestamp' => $now->toISOString()
    ]);
})->middleware('auth');

////////////////////////////////////////////// END SUBSCRIPTION MANAGEMENT ///////////////////////////////////////////

////////////////////////////////////////////// BRANCH MANAGEMENT ///////////////////////////////////////////////////

//Route::resource('branches', BranchController::class)->middleware('auth');

//Route::resource('companies', CompanyController::class)->middleware('auth');

Route::resource('cash_collateral_types', CashCollateralTypeController::class)->middleware('auth');

////////////////////////////////////////////// END /////////////////////////////////////////////////////////////////

////////////////////////////////////////////// SUPER ADMIN ROUTES ////////////////////////////////////////////////

Route::prefix('super-admin')->name('super-admin.')->middleware(['auth', 'role:super-admin'])->group(function () {

    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('super-admin.dashboard');
    // Companies
    Route::get('/companies', [SuperAdminController::class, 'companies'])->name('companies');
    Route::get('/companies/create', [SuperAdminController::class, 'createCompany'])->name('companies.create');
    Route::post('/companies', [SuperAdminController::class, 'storeCompany'])->name('companies.store');
    Route::get('/companies/{company}', [SuperAdminController::class, 'showCompany'])->name('companies.show');
    Route::get('/companies/{company}/edit', [SuperAdminController::class, 'editCompany'])->name('companies.edit');
    Route::put('/companies/{company}', [SuperAdminController::class, 'updateCompany'])->name('companies.update');
    Route::delete('/companies/{company}', [SuperAdminController::class, 'destroyCompany'])->name('companies.destroy');

    // Branches
    Route::get('/branches', [SuperAdminController::class, 'branches'])->name('branches');

    // Users
    Route::get('/users', [SuperAdminController::class, 'users'])->name('users');
});

////////////////////////////////////////////// END SUPER ADMIN ROUTES /////////////////////////////////////////////

////////////////////////////////////////////// ACCOUNTING MANAGEMENT ///////////////////////////////////////////////

Route::prefix('accounting')->name('accounting.')->middleware('auth')->group(function () {
    // Accounting Dashboard
    Route::get('/', [AccountingController::class, 'index'])->name('index');

    // Account Class Groups
    Route::get('/account-class-groups', [AccountClassGroupController::class, 'index'])->name('account-class-groups.index');

    // Payment Voucher Approval Routes
    Route::prefix('payment-vouchers')->name('payment-vouchers.')->group(function () {
        Route::get('/pending-approvals', [PaymentVoucherController::class, 'pendingApprovals'])->name('pending-approvals');
        Route::get('/{paymentVoucher}/approval', [PaymentVoucherController::class, 'showApproval'])->name('approval');
        Route::post('/{paymentVoucher}/approve', [PaymentVoucherController::class, 'approve'])->name('approve');
        Route::post('/{paymentVoucher}/reject', [PaymentVoucherController::class, 'reject'])->name('reject');
        Route::get("/data", [PaymentVoucherController::class, "getPaymentVouchersData"])->name("data");
        Route::resource("", PaymentVoucherController::class)->parameters(["" => "paymentVoucher"]);
        Route::get("/{paymentVoucher}/download-attachment", [PaymentVoucherController::class, "downloadAttachment"])->name("download-attachment");
        Route::delete("/{paymentVoucher}/remove-attachment", [PaymentVoucherController::class, "removeAttachment"])->name("remove-attachment");
        Route::get("/{paymentVoucher}/export-pdf", [PaymentVoucherController::class, "exportPdf"])->name("export-pdf");
    });
    Route::get('/account-class-groups/create', [AccountClassGroupController::class, 'create'])->name('account-class-groups.create');
    Route::post('/account-class-groups', [AccountClassGroupController::class, 'store'])->name('account-class-groups.store');
    Route::get('/account-class-groups/{encodedId}', [AccountClassGroupController::class, 'show'])->name('account-class-groups.show');
    Route::get('/account-class-groups/{encodedId}/edit', [AccountClassGroupController::class, 'edit'])->name('account-class-groups.edit');
    Route::put('/account-class-groups/{encodedId}', [AccountClassGroupController::class, 'update'])->name('account-class-groups.update');
    Route::delete('/account-class-groups/{encodedId}', [AccountClassGroupController::class, 'destroy'])->name('account-class-groups.destroy');

    // Chart Accounts
    Route::get('/chart-accounts', [ChartAccountController::class, 'index'])->name('chart-accounts.index');
    Route::get('/chart-accounts/data', [ChartAccountController::class, 'getChartAccountsData'])->name('chart-accounts.data');
    Route::get('/chart-accounts/create', [ChartAccountController::class, 'create'])->name('chart-accounts.create');
    Route::post('/chart-accounts', [ChartAccountController::class, 'store'])->name('chart-accounts.store');
    Route::get('/chart-accounts/{encodedId}', [ChartAccountController::class, 'show'])->name('chart-accounts.show');
    Route::get('/chart-accounts/{encodedId}/edit', [ChartAccountController::class, 'edit'])->name('chart-accounts.edit');
    Route::put('/chart-accounts/{encodedId}', [ChartAccountController::class, 'update'])->name('chart-accounts.update');
    Route::delete('/chart-accounts/{encodedId}', [ChartAccountController::class, 'destroy'])->name('chart-accounts.destroy');

    // Suppliers
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/suppliers/data', [SupplierController::class, 'getSuppliersData'])->name('suppliers.data');
    Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::get('/suppliers/{encodedId}', [SupplierController::class, 'show'])->name('suppliers.show');
    Route::get('/suppliers/{encodedId}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
    Route::put('/suppliers/{encodedId}', [SupplierController::class, 'update'])->name('suppliers.update');
    Route::patch('/suppliers/{encodedId}/status', [SupplierController::class, 'changeStatus'])->name('suppliers.changeStatus');
    Route::delete('/suppliers/{encodedId}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');


    // Bill and Payment PDF Export Routes
    Route::get('/bill-purchases/{billPurchase}/export-pdf', [BillPurchaseController::class, 'exportPdf'])->name('bill-purchases.export-pdf');
    Route::get('/payments/{payment}/export-pdf', [BillPurchaseController::class, 'exportPaymentPdf'])->name('bill-payments.export-pdf');

    // Receipt Vouchers
    Route::get('/receipt-vouchers', [ReceiptVoucherController::class, 'index'])->name('receipt-vouchers.index');
    Route::get('/receipt-vouchers/data', [ReceiptVoucherController::class, 'getReceiptVouchersData'])->name('receipt-vouchers.data');
    Route::get('/receipt-vouchers/create', [ReceiptVoucherController::class, 'create'])->name('receipt-vouchers.create');
    Route::post('/receipt-vouchers', [ReceiptVoucherController::class, 'store'])->name('receipt-vouchers.store');
    Route::get('/receipt-vouchers/{encodedId}', [ReceiptVoucherController::class, 'show'])->name('receipt-vouchers.show');
    Route::get('/receipt-vouchers/{encodedId}/edit', [ReceiptVoucherController::class, 'edit'])->name('receipt-vouchers.edit');
    Route::put('/receipt-vouchers/{encodedId}', [ReceiptVoucherController::class, 'update'])->name('receipt-vouchers.update');
    Route::delete('/receipt-vouchers/{encodedId}', [ReceiptVoucherController::class, 'destroy'])->name('receipt-vouchers.destroy');
    Route::get('/receipt-vouchers/{encodedId}/download-attachment', [ReceiptVoucherController::class, 'downloadAttachment'])->name('receipt-vouchers.download-attachment');
    Route::delete('/receipt-vouchers/{encodedId}/remove-attachment', [ReceiptVoucherController::class, 'removeAttachment'])->name('receipt-vouchers.remove-attachment');
    Route::get('/receipt-vouchers/{encodedId}/export-pdf', [ReceiptVoucherController::class, 'exportPdf'])->name('receipt-vouchers.export-pdf');
    Route::get('/receipt-vouchers-debug', [ReceiptVoucherController::class, 'debug'])->name('receipt-vouchers.debug');

    // Receipt Vouchers from Loans
    Route::get('/loans/{encodedLoanId}/create-receipt', [ReceiptVoucherController::class, 'createFromLoan'])->name('loans.create-receipt');
    Route::post('/loans/{encodedLoanId}/store-receipt', [ReceiptVoucherController::class, 'storeFromLoan'])->name('loans.store-receipt');

    // Bank Accounts
    Route::get('/bank-accounts', [BankAccountController::class, 'index'])->name('bank-accounts');
    Route::get('/bank-accounts/create', [BankAccountController::class, 'create'])->name('bank-accounts.create');
    Route::post('/bank-accounts', [BankAccountController::class, 'store'])->name('bank-accounts.store');
    Route::get('/bank-accounts/{encodedId}', [BankAccountController::class, 'show'])->name('bank-accounts.show');
    Route::get('/bank-accounts/{encodedId}/edit', [BankAccountController::class, 'edit'])->name('bank-accounts.edit');
    Route::put('/bank-accounts/{encodedId}', [BankAccountController::class, 'update'])->name('bank-accounts.update');
    Route::delete('/bank-accounts/{encodedId}', [BankAccountController::class, 'destroy'])->name('bank-accounts.destroy');

    // Bank Reconciliation
    // Keep resource for other methods but avoid conflicting show/edit
    Route::resource('bank-reconciliation', BankReconciliationController::class)->except(['show', 'edit']);
    // Use hash id for show/edit routes (must come after resource to avoid conflicts)
    Route::get('bank-reconciliation/{hash}', [BankReconciliationController::class, 'show'])->name('bank-reconciliation.show');
    Route::get('bank-reconciliation/{hash}/edit', [BankReconciliationController::class, 'edit'])->name('bank-reconciliation.edit');

    Route::post('/bank-reconciliation/{bankReconciliation}/add-bank-statement-item', [BankReconciliationController::class, 'addBankStatementItem'])->name('bank-reconciliation.add-bank-statement-item');
    Route::post('/bank-reconciliation/{hash}/match-items', [BankReconciliationController::class, 'matchItems'])->name('bank-reconciliation.match-items');
    Route::post('/bank-reconciliation/{hash}/unmatch-items', [BankReconciliationController::class, 'unmatchItems'])->name('bank-reconciliation.unmatch-items');
    Route::post('/bank-reconciliation/{hash}/confirm-book-item', [BankReconciliationController::class, 'confirmBookItem'])->name('bank-reconciliation.confirm-book-item');
    Route::post('/bank-reconciliation/{hash}/complete', [BankReconciliationController::class, 'completeReconciliation'])->name('bank-reconciliation.complete');
    Route::post('/bank-reconciliation/{hash}/update-book-balance', [BankReconciliationController::class, 'updateBookBalance'])->name('bank-reconciliation.update-book-balance');
    Route::post('/bank-reconciliation/refresh-all', [BankReconciliationController::class, 'refreshAllReconciliations'])->name('bank-reconciliation.refresh-all');

    // Bill Purchases
    Route::get('/bill-purchases', [BillPurchaseController::class, 'index'])->name('bill-purchases');
    Route::get('/bill-purchases/create', [BillPurchaseController::class, 'create'])->name('bill-purchases.create');
    Route::post('/bill-purchases', [BillPurchaseController::class, 'store'])->name('bill-purchases.store');

    // Bill Payment Management (must come before bill-purchases/{billPurchase} routes)
    Route::get('/bill-purchases/payment/{payment}', [BillPurchaseController::class, 'showPayment'])->name('bill-purchases.payment.show');
    Route::get('/bill-purchases/payment/{payment}/edit', [BillPurchaseController::class, 'editPayment'])->name('bill-purchases.payment.edit');
    Route::put('/bill-purchases/payment/{payment}', [BillPurchaseController::class, 'updatePayment'])->name('bill-purchases.payment.update');
    Route::delete('/bill-purchases/payment/{payment}', [BillPurchaseController::class, 'deletePayment'])->name('bill-purchases.payment.delete');

    Route::get('/bill-purchases/{billPurchase}', [BillPurchaseController::class, 'show'])->name('bill-purchases.show');
    Route::get('/bill-purchases/{billPurchase}/edit', [BillPurchaseController::class, 'edit'])->name('bill-purchases.edit');
    Route::put('/bill-purchases/{billPurchase}', [BillPurchaseController::class, 'update'])->name('bill-purchases.update');
    Route::delete('/bill-purchases/{billPurchase}', [BillPurchaseController::class, 'destroy'])->name('bill-purchases.destroy');
    Route::get('/bill-purchases/{billPurchase}/payment', [BillPurchaseController::class, 'showPaymentForm'])->name('bill-purchases.payment');
    Route::post('/bill-purchases/{billPurchase}/payment', [BillPurchaseController::class, 'processPayment'])->name('bill-purchases.process-payment');

    // Budget
    Route::get('/budgets', [BudgetController::class, 'index'])->name('budgets.index');
    Route::get('/budgets/create', [BudgetController::class, 'create'])->name('budgets.create');
    Route::post('/budgets', [BudgetController::class, 'store'])->name('budgets.store');
    Route::get('/budgets/import', [BudgetController::class, 'import'])->name('budgets.import');
    Route::post('/budgets/import', [BudgetController::class, 'storeImport'])->name('budgets.store-import');
    Route::get('/budgets/template/download', [BudgetController::class, 'downloadTemplate'])->name('budgets.download-template');
    Route::get('/budgets/{budget}/export/excel', [BudgetController::class, 'exportExcel'])->name('budgets.export-excel');
    Route::get('/budgets/{budget}/export/pdf', [BudgetController::class, 'exportPdf'])->name('budgets.export-pdf');


    Route::get('/budgets/{budget}', [BudgetController::class, 'show'])->name('budgets.show');
    Route::get('/budgets/{budget}/edit', [BudgetController::class, 'edit'])->name('budgets.edit');
    Route::put('/budgets/{budget}', [BudgetController::class, 'update'])->name('budgets.update');
    Route::delete('/budgets/{budget}', [BudgetController::class, 'destroy'])->name('budgets.destroy');

    // Fees
    Route::get('/fees', [FeeController::class, 'index'])->name('fees.index');
    Route::get('/fees/create', [FeeController::class, 'create'])->name('fees.create');
    Route::post('/fees', [FeeController::class, 'store'])->name('fees.store');
    Route::get('/fees/{encodedId}', [FeeController::class, 'show'])->name('fees.show');
    Route::get('/fees/{encodedId}/edit', [FeeController::class, 'edit'])->name('fees.edit');
    Route::put('/fees/{encodedId}', [FeeController::class, 'update'])->name('fees.update');
    Route::patch('/fees/{encodedId}/status', [FeeController::class, 'changeStatus'])->name('fees.changeStatus');
    Route::delete('/fees/{encodedId}', [FeeController::class, 'destroy'])->name('fees.destroy');

    // Penalties
    Route::get('/penalties', [PenaltyController::class, 'index'])->name('penalties.index');
    Route::get('/penalties/create', [PenaltyController::class, 'create'])->name('penalties.create');
    Route::post('/penalties', [PenaltyController::class, 'store'])->name('penalties.store');
    Route::get('/penalties/{encodedId}', [PenaltyController::class, 'show'])->name('penalties.show');
    Route::get('/penalties/{encodedId}/edit', [PenaltyController::class, 'edit'])->name('penalties.edit');
    Route::put('/penalties/{encodedId}', [PenaltyController::class, 'update'])->name('penalties.update');
    Route::patch('/penalties/{encodedId}/status', [PenaltyController::class, 'changeStatus'])->name('penalties.changeStatus');
    Route::delete('/penalties/{encodedId}', [PenaltyController::class, 'destroy'])->name('penalties.destroy');

    // Journal Entries CRUD
    Route::get('/journals', [JournalController::class, 'index'])->name('journals.index');
    Route::get('/journals/create', [JournalController::class, 'create'])->name('journals.create');
    Route::post('/journals', [JournalController::class, 'store'])->name('journals.store');
    Route::get('/journals/{journal}', [JournalController::class, 'show'])->name('journals.show');
    Route::get('/journals/{journal}/edit', [JournalController::class, 'edit'])->name('journals.edit');
    Route::put('/journals/{journal}', [JournalController::class, 'update'])->name('journals.update');
    Route::delete('/journals/{journal}', [JournalController::class, 'destroy'])->name('journals.destroy');
    Route::get('/journals/{journal}/export-pdf', [JournalController::class, 'exportPdf'])->name('journals.export-pdf');

    // Reports Routes
    Route::prefix('reports')->name('reports.')->group(function () {

        // Main Reports Index
        Route::get("/", function () {
            return view("reports.index");
        })->name("index");

        // Accounting Reports Index
        Route::get("/accounting-reports", function () {
            if (!auth()->user()->can('view accounting reports')) {
                abort(403, 'Unauthorized access to accounting reports.');
            }
            return view("reports.index");
        })->name("accounting");
        Route::get('/other-income', [App\Http\Controllers\Accounting\Reports\OtherIncomeReportController::class, 'index'])->name('other-income');
        // Trial Balance Report
        Route::get('/trial-balance', [App\Http\Controllers\Accounting\Reports\TrialBalanceReportController::class, 'index'])->name('trial-balance');
        Route::get('/trial-balance/export', [App\Http\Controllers\Accounting\Reports\TrialBalanceReportController::class, 'export'])->name('trial-balance.export');
        Route::get('/income-statement', [App\Http\Controllers\Accounting\Reports\IncomeStatementReportController::class, 'index'])->name('income-statement');
        Route::get('/income-statement/export', [App\Http\Controllers\Accounting\Reports\IncomeStatementReportController::class, 'export'])->name('income-statement.export');
        Route::get('/cash-book', [App\Http\Controllers\Accounting\Reports\CashBookReportController::class, 'index'])->name('cash-book');
        Route::get('/cash-book/export', [App\Http\Controllers\Accounting\Reports\CashBookReportController::class, 'export'])->name('cash-book.export');
        Route::get('/accounting-notes', [App\Http\Controllers\Accounting\Reports\AccountingNotesReportController::class, 'index'])->name('accounting-notes');
        Route::get('/accounting-notes/export', [App\Http\Controllers\Accounting\Reports\AccountingNotesReportController::class, 'export'])->name('accounting-notes.export');
        Route::get('/balance-sheet', [App\Http\Controllers\Accounting\Reports\BalanceSheetReportController::class, 'index'])->name('balance-sheet');
        Route::get('/balance-sheet/export', [App\Http\Controllers\Accounting\Reports\BalanceSheetReportController::class, 'export'])->name('balance-sheet.export');
        Route::get('/cash-flow', [App\Http\Controllers\Accounting\Reports\CashFlowReportController::class, 'index'])->name('cash-flow');
        Route::match(['GET', 'POST'], '/cash-flow/export', [App\Http\Controllers\Accounting\Reports\CashFlowReportController::class, 'export'])->name('cash-flow.export');
        Route::get('/general-ledger', [App\Http\Controllers\Accounting\Reports\GeneralLedgerReportController::class, 'index'])->name('general-ledger');
        Route::get('/general-ledger/export', [App\Http\Controllers\Accounting\Reports\GeneralLedgerReportController::class, 'export'])->name('general-ledger.export');
        Route::get('/expenses-summary', [App\Http\Controllers\Accounting\Reports\ExpensesSummaryReportController::class, 'index'])->name('expenses-summary');
        Route::get('/expenses-summary/export', [App\Http\Controllers\Accounting\Reports\ExpensesSummaryReportController::class, 'export'])->name('expenses-summary.export');
        Route::get('/accounting-notes', [App\Http\Controllers\Accounting\Reports\AccountingNotesReportController::class, 'index'])->name('accounting-notes');
        Route::get('/changes-equity', [App\Http\Controllers\Accounting\Reports\ChangesEquityReportController::class, 'index'])->name('changes-equity');
        Route::post('/changes-equity', [App\Http\Controllers\Accounting\Reports\ChangesEquityReportController::class, 'export'])->name('changes-equity.export');
        Route::get('/bank-reconciliation', [BankReconciliationReportController::class, 'index'])->name('bank-reconciliation-report');
        Route::get('/bank-reconciliation/generate', [BankReconciliationReportController::class, 'generate'])->name('bank-reconciliation-report.generate');
        Route::get('/bank-reconciliation/{bankReconciliation}/show', [BankReconciliationReportController::class, 'show'])->name('bank-reconciliation-report.show');
        Route::get('/bank-reconciliation/{bankReconciliation}/export', [BankReconciliationReportController::class, 'exportReconciliation'])->name('bank-reconciliation-report.export');
        Route::get('/budget-report', [App\Http\Controllers\Accounting\Reports\BudgetReportController::class, 'index'])->name('budget-report');
        Route::get('/budget-report/export', [App\Http\Controllers\Accounting\Reports\BudgetReportController::class, 'export'])->name('budget-report.export');
        Route::get('/budget-report/export-pdf', [App\Http\Controllers\Accounting\Reports\BudgetReportController::class, 'exportPdf'])->name('budget-report.export-pdf');

        // Fees Report
        Route::get("/fees", [App\Http\Controllers\Accounting\Reports\FeesReportController::class, "index"])->name("fees");
        Route::get("/fees/export", [App\Http\Controllers\Accounting\Reports\FeesReportController::class, "export"])->name("fees.export");

        Route::get("/fees/export-pdf", [App\Http\Controllers\Accounting\Reports\FeesReportController::class, "exportPdf"])->name("fees.export-pdf");

        // Penalties Report
        Route::get("/penalties", [App\Http\Controllers\Accounting\Reports\PenaltiesReportController::class, "index"])->name("penalties");
        Route::get("/penalties/export", [App\Http\Controllers\Accounting\Reports\PenaltiesReportController::class, "export"])->name("penalties.export");
        Route::get("/penalties/export-pdf", [App\Http\Controllers\Accounting\Reports\PenaltiesReportController::class, "exportPdf"])->name("penalties.export-pdf");
    });

    // Transaction Routes
    Route::get('/transactions/double-entries/{accountId}', [App\Http\Controllers\TransactionController::class, 'doubleEntries'])->name('transactions.doubleEntries');
    Route::get('/transactions/details/{transactionId}/{transactionType?}', [App\Http\Controllers\TransactionController::class, 'showTransactionDetails'])->name('transactions.details');
});

//route

Route::name('loans.reports.')->group(function () {
    //////LOANS REPORT ROUTE////////
    Route::get('/loan-disbursement', [LoanReportController::class, 'loanDisbursementReport'])->name('disbursed');
    Route::get('/loan-disbursement/export', [LoanReportController::class, 'exportLoanDisbursement'])->name('loan-export');
    ////////REPAYMENT ROUTE///////
    Route::get('/loan-repayments', [LoanReportController::class, 'getRepaymentReport'])->name('repayment');
    Route::get('/loan-repayment', [LoanReportController::class, 'getRepaymentReport'])->name('loan-repayment');
    Route::get('/loan-repayment/export', [LoanReportController::class, 'exportLoanRepayment'])->name('loan-export-repayment');
    // Loan Aging Report
    Route::get('/loan-aging', [LoanReportController::class, 'loanAgingReport'])->name('loan_aging');
    Route::get('/loan-aging/export-excel', [LoanReportController::class, 'exportLoanAgingToExcel'])->name('loan_aging.export_excel');
    Route::get('/loan-aging/export-pdf', [LoanReportController::class, 'exportLoanAgingToPdf'])->name('loan_aging.export_pdf');

    // Loan Portfolio Tracking Report
    Route::get('/portfolio-tracking', [LoanReportController::class, 'portfolioTrackingReport'])->name('portfolio_tracking');
    Route::get('/portfolio-tracking/export-excel', [LoanReportController::class, 'exportPortfolioTrackingToExcel'])->name('portfolio_tracking.export_excel');
    Route::get('/portfolio-tracking/export-pdf', [LoanReportController::class, 'exportPortfolioTrackingToPdf'])->name('portfolio_tracking.export_pdf');

    // Loan Aging Installment Report
    Route::get('/loan-aging-installment', [LoanReportController::class, 'loanAgingInstallmentReport'])->name('loan_aging_installment');
    Route::get('/loan-aging-installment/export-excel', [LoanReportController::class, 'exportLoanAgingInstallmentToExcel'])->name('loan_aging_installment.export_excel');
    Route::get('/loan-aging-installment/export-pdf', [LoanReportController::class, 'exportLoanAgingInstallmentToPdf'])->name('loan_aging_installment.export_pdf');

    // Reports Index - Removed to avoid conflict with main reports.index

    // Loan Arrears Report
    Route::get('/loan-arrears', [LoanReportController::class, 'loanArrearsReport'])->name('loan_arrears');
    Route::get('/loan-arrears/export-excel', [LoanReportController::class, 'exportLoanArrearsToExcel'])->name('loan_arrears.export_excel');
    Route::get('/loan-arrears/export-pdf', [LoanReportController::class, 'exportLoanArrearsToPdf'])->name('loan_arrears.export_pdf');

    // Expected vs Collected Report
    Route::get('/expected-vs-collected', [LoanReportController::class, 'expectedVsCollectedReport'])->name('expected_vs_collected');
    Route::get('/expected-vs-collected/export-excel', [LoanReportController::class, 'exportExpectedVsCollectedToExcel'])->name('expected_vs_collected.export_excel');
    Route::get('/expected-vs-collected/export-pdf', [LoanReportController::class, 'exportExpectedVsCollectedToPdf'])->name('expected_vs_collected.export_pdf');

    // Portfolio at Risk (PAR) Report
    Route::get('/portfolio-at-risk', [LoanReportController::class, 'portfolioAtRiskReport'])->name('portfolio_at_risk');
    Route::get('/portfolio-at-risk/export-excel', [LoanReportController::class, 'exportPortfolioAtRiskToExcel'])->name('portfolio_at_risk.export_excel');
    Route::get('/portfolio-at-risk/export-pdf', [LoanReportController::class, 'exportPortfolioAtRiskToPdf'])->name('portfolio_at_risk.export_pdf');

    // Internal Portfolio Analysis Report
    Route::get('/internal-portfolio-analysis', [LoanReportController::class, 'internalPortfolioAnalysisReport'])->name('internal_portfolio_analysis');
    Route::get('/internal-portfolio-analysis/export-excel', [LoanReportController::class, 'exportInternalPortfolioAnalysisToExcel'])->name('internal_portfolio_analysis.export_excel');
    Route::get('/internal-portfolio-analysis/export-pdf', [LoanReportController::class, 'exportInternalPortfolioAnalysisToPdf'])->name('internal_portfolio_analysis.export_pdf');

    // Loan Portfolio Report
    Route::get('/portfolio', [LoanReportController::class, 'portfolioReport'])->name('portfolio');
    Route::get('/portfolio/export-excel', [LoanReportController::class, 'exportPortfolioToExcel'])->name('portfolio.export_excel');
    Route::get('/portfolio/export-pdf', [LoanReportController::class, 'exportPortfolioToPdf'])->name('portfolio.export_pdf');

    // Loan Performance Report
    Route::get('/performance', [LoanReportController::class, 'performanceReport'])->name('performance');
    Route::get('/performance/export-excel', [LoanReportController::class, 'exportPerformanceToExcel'])->name('performance.export_excel');
    Route::get('/performance/export-pdf', [LoanReportController::class, 'exportPerformanceToPdf'])->name('performance.export_pdf');

    // Delinquency Report
    Route::get('/delinquency', [LoanReportController::class, 'delinquencyReport'])->name('delinquency');
    Route::get('/delinquency/export-excel', [LoanReportController::class, 'exportDelinquencyToExcel'])->name('delinquency.export_excel');
    Route::get('/delinquency/export-pdf', [LoanReportController::class, 'exportDelinquencyToPdf'])->name('delinquency.export_pdf');

    // Loan Outstanding Report
    Route::get('/loan-outstanding', [LoanReportController::class, 'loanOutstandingReport'])->name('loan_outstanding');

    // Non Performing Loan Report
    Route::get('/npl', [LoanReportController::class, 'nonPerformingLoanReport'])->name('npl');
    Route::get('/npl/export-excel', [LoanReportController::class, 'exportNPLToExcel'])->name('npl.export_excel');
    Route::get('/npl/export-pdf', [LoanReportController::class, 'exportNPLToPdf'])->name('npl.export_pdf');
});

// Loan Reports Routes (accounting.loans.reports.*)
Route::prefix('accounting/loans/reports')->name('accounting.loans.reports.')->group(function () {
    // Loan Portfolio Report
    Route::get('/portfolio', [LoanReportController::class, 'portfolioReport'])->name('portfolio');
    Route::get('/portfolio/export-excel', [LoanReportController::class, 'exportPortfolioToExcel'])->name('portfolio.export_excel');
    Route::get('/portfolio/export-pdf', [LoanReportController::class, 'exportPortfolioToPdf'])->name('portfolio.export_pdf');

    // Loan Performance Report
    Route::get('/performance', [LoanReportController::class, 'performanceReport'])->name('performance');
    Route::get('/performance/export-excel', [LoanReportController::class, 'exportPerformanceToExcel'])->name('performance.export_excel');
    Route::get('/performance/export-pdf', [LoanReportController::class, 'exportPerformanceToPdf'])->name('performance.export_pdf');

    // Delinquency Report
    Route::get('/delinquency', [LoanReportController::class, 'delinquencyReport'])->name('delinquency');
    Route::get('/delinquency/export-excel', [LoanReportController::class, 'exportDelinquencyToExcel'])->name('delinquency.export_excel');
    Route::get('/delinquency/export-pdf', [LoanReportController::class, 'exportDelinquencyToPdf'])->name('delinquency.export_pdf');

    // Loan Disbursement Report
    Route::get('/disbursed', [LoanReportController::class, 'loanDisbursementReport'])->name('disbursed');
    Route::get('/disbursed/export', [LoanReportController::class, 'exportLoanDisbursement'])->name('loan-export');

    // Loan Repayment Report
    Route::get('/repayment', [LoanReportController::class, 'getRepaymentReport'])->name('repayment');
    Route::get('/repayment/export', [LoanReportController::class, 'exportLoanRepayment'])->name('loan-export-repayment');

    // Loan Aging Report
    Route::get('/loan-aging', [LoanReportController::class, 'loanAgingReport'])->name('loan_aging');
    Route::get('/loan-aging/export-excel', [LoanReportController::class, 'exportLoanAgingToExcel'])->name('loan_aging.export_excel');
    Route::get('/loan-aging/export-pdf', [LoanReportController::class, 'exportLoanAgingToPdf'])->name('loan_aging.export_pdf');

    // Loan Aging Installment Report
    Route::get('/loan-aging-installment', [LoanReportController::class, 'loanAgingInstallmentReport'])->name('loan_aging_installment');
    Route::get('/loan-aging-installment/export-excel', [LoanReportController::class, 'exportLoanAgingInstallmentToExcel'])->name('loan_aging_installment.export_excel');
    Route::get('/loan-aging-installment/export-pdf', [LoanReportController::class, 'exportLoanAgingInstallmentToPdf'])->name('loan_aging_installment.export_pdf');

    // Loan Outstanding Report
    Route::get('/loan-outstanding', [LoanReportController::class, 'loanOutstandingReport'])->name('loan_outstanding');

    // Loan Arrears Report
    Route::get('/loan-arrears', [LoanReportController::class, 'loanArrearsReport'])->name('loan_arrears');
    Route::get('/loan-arrears/export-excel', [LoanReportController::class, 'exportLoanArrearsToExcel'])->name('loan_arrears.export_excel');
    Route::get('/loan-arrears/export-pdf', [LoanReportController::class, 'exportLoanArrearsToPdf'])->name('loan_arrears.export_pdf');

    // Expected vs Collected Report
    Route::get('/expected-vs-collected', [LoanReportController::class, 'expectedVsCollectedReport'])->name('expected_vs_collected');
    Route::get('/expected-vs-collected/export-excel', [LoanReportController::class, 'exportExpectedVsCollectedToExcel'])->name('expected_vs_collected.export_excel');
    Route::get('/expected-vs-collected/export-pdf', [LoanReportController::class, 'exportExpectedVsCollectedToPdf'])->name('expected_vs_collected.export_pdf');

    // Portfolio at Risk (PAR) Report
    Route::get('/portfolio-at-risk', [LoanReportController::class, 'portfolioAtRiskReport'])->name('portfolio_at_risk');
    Route::get('/portfolio-at-risk/export-excel', [LoanReportController::class, 'exportPortfolioAtRiskToExcel'])->name('portfolio_at_risk.export_excel');
    Route::get('/portfolio-at-risk/export-pdf', [LoanReportController::class, 'exportPortfolioAtRiskToPdf'])->name('portfolio_at_risk.export_pdf');

    // Internal Portfolio Analysis Report
    Route::get('/internal-portfolio-analysis', [LoanReportController::class, 'internalPortfolioAnalysisReport'])->name('internal_portfolio_analysis');
    Route::get('/internal-portfolio-analysis/export-excel', [LoanReportController::class, 'exportInternalPortfolioAnalysisToExcel'])->name('internal_portfolio_analysis.export_excel');
    Route::get('/internal-portfolio-analysis/export-pdf', [LoanReportController::class, 'exportInternalPortfolioAnalysisToPdf'])->name('internal_portfolio_analysis.export_pdf');

    // Non Performing Loan Report
    Route::get('/npl', [LoanReportController::class, 'nonPerformingLoanReport'])->name('npl');
    Route::get('/npl/export-excel', [LoanReportController::class, 'exportNPLToExcel'])->name('npl.export_excel');
    Route::get('/npl/export-pdf', [LoanReportController::class, 'exportNPLToPdf'])->name('npl.export_pdf');
});

////////////////////////////////////////////// END ACCOUNTING MANAGEMENT ///////////////////////////////////////////

////////////////////////////////////////////// CUSTOMER MANAGEMENT ///////////////////////////////////////////

Route::middleware(['auth'])->group(function () {
    Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('customers/data', [CustomerController::class, 'getCustomersData'])->name('customers.data');
    Route::get('customers/penalty', [CustomerController::class, 'penaltList'])->name('customers.penalty');
    Route::get('customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');

    // Bulk upload routes (must come before parameterized routes)
    Route::get('customers/bulk-upload', [CustomerController::class, 'bulkUpload'])->name('customers.bulk-upload');
    Route::post('customers/bulk-upload', [CustomerController::class, 'bulkUploadStore'])->name('customers.bulk-upload.store');
    Route::get('customers/download-sample', [CustomerController::class, 'downloadSample'])->name('customers.download-sample');

    // Documents upload/delete
    Route::post('customers/{encodedCustomerId}/documents', [CustomerController::class, 'uploadDocuments'])->name('customers.documents.upload');
    Route::delete('customers/{encodedCustomerId}/documents/{pivotId}', [CustomerController::class, 'deleteDocument'])->name('customers.documents.delete');
    Route::get('customers/{encodedCustomerId}/documents/{pivotId}/view', [CustomerController::class, 'viewDocument'])->name('customers.documents.view');
    Route::get('customers/{encodedCustomerId}/documents/{pivotId}/download', [CustomerController::class, 'downloadDocument'])->name('customers.documents.download');

    // Next of Kin routes
    Route::post('customers/{encodedCustomerId}/next-of-kin', [CustomerController::class, 'storeNextOfKin'])->name('customers.next-of-kin.store');
    Route::put('customers/{encodedCustomerId}/next-of-kin/{encodedNextOfKinId}', [CustomerController::class, 'updateNextOfKin'])->name('customers.next-of-kin.update');
    Route::delete('customers/{encodedCustomerId}/next-of-kin/{encodedNextOfKinId}', [CustomerController::class, 'deleteNextOfKin'])->name('customers.next-of-kin.delete');

    // Parameterized routes (must come after specific routes)
    Route::post('customers/{customerId}/send-message', [CustomerController::class, 'sendMessage'])->name('customers.send-message');
    Route::post('customers/{encodedId}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');
    Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
});

////////////////////////////////////////////// END CUSTOMER MANAGEMENT ///////////////////////////////////////////

////////////////////////////////////////////// COMPLAINS MANAGEMENT ///////////////////////////////////////////

Route::middleware(['auth'])->group(function () {
    Route::get('complains', [ComplainController::class, 'index'])->name('complains.index');
    Route::get('complains/data', [ComplainController::class, 'getComplainsData'])->name('complains.data');
    Route::get('complains/{encodedId}', [ComplainController::class, 'show'])->name('complains.show');
    Route::get('complains/{encodedId}/edit', [ComplainController::class, 'edit'])->name('complains.edit');
    Route::put('complains/{encodedId}', [ComplainController::class, 'update'])->name('complains.update');
});

////////////////////////////////////////////// END COMPLAINS MANAGEMENT ///////////////////////////////////////////

//////////////////////////////////////////////////// CONTRIBUTIONS //////////////////////////////////////////////////////////////

Route::get('/contributions', [ContributionController::class, 'index'])->name('contributions.index')->middleware('auth');
Route::get('/contributions/products', [ContributionController::class, 'products'])->name('contributions.products.index')->middleware('auth');
Route::get('/contributions/products/data', [ContributionController::class, 'getContributionProductsData'])->name('contributions.products.data')->middleware('auth');
Route::get('/contributions/products/create', [ContributionController::class, 'productsCreate'])->name('contributions.products.create')->middleware('auth');
Route::post('/contributions/products', [ContributionController::class, 'productsStore'])->name('contributions.products.store')->middleware('auth');
Route::get('/contributions/products/{encodedId}', [ContributionController::class, 'productsShow'])->name('contributions.products.show')->middleware('auth');
Route::get('/contributions/products/{encodedId}/transactions/data', [ContributionController::class, 'getProductTransactionsData'])->name('contributions.products.transactions.data')->middleware('auth');
Route::get('/contributions/products/{encodedId}/edit', [ContributionController::class, 'productsEdit'])->name('contributions.products.edit')->middleware('auth');
Route::put('/contributions/products/{encodedId}', [ContributionController::class, 'productsUpdate'])->name('contributions.products.update')->middleware('auth');
Route::delete('/contributions/products/{encodedId}', [ContributionController::class, 'productsDestroy'])->name('contributions.products.destroy')->middleware('auth');
Route::get('/contributions/accounts', [ContributionAccountController::class, 'index'])->name('contributions.accounts.index')->middleware('auth');
Route::get('/contributions/accounts/data', [ContributionAccountController::class, 'getContributionAccountsData'])->name('contributions.accounts.data')->middleware('auth');
Route::get('/contributions/accounts/create', [ContributionAccountController::class, 'create'])->name('contributions.accounts.create')->middleware('auth');
Route::post('/contributions/accounts', [ContributionAccountController::class, 'store'])->name('contributions.accounts.store')->middleware('auth');
Route::get('/contributions/accounts/{encodedId}', [ContributionAccountController::class, 'show'])->name('contributions.accounts.show')->middleware('auth');
Route::get('/contributions/accounts/{encodedId}/transactions/data', [ContributionAccountController::class, 'getAccountTransactionsData'])->name('contributions.accounts.transactions.data')->middleware('auth');
Route::get('/contributions/accounts/{encodedId}/statement/export', [ContributionAccountController::class, 'exportStatement'])->name('contributions.accounts.statement.export')->middleware('auth');
Route::post('/contributions/accounts/{encodedId}/toggle-status', [ContributionAccountController::class, 'toggleStatus'])->name('contributions.accounts.toggle-status')->middleware('auth');
Route::delete('/contributions/accounts/{encodedId}', [ContributionAccountController::class, 'destroy'])->name('contributions.accounts.destroy')->middleware('auth');
Route::get('/contributions/deposits', [ContributionController::class, 'deposits'])->name('contributions.deposits.index')->middleware('auth');
Route::get('/contributions/deposits/data', [ContributionController::class, 'getDepositsData'])->name('contributions.deposits.data')->middleware('auth');
Route::get('/contributions/deposits/create', [ContributionController::class, 'depositsCreate'])->name('contributions.deposits.create')->middleware('auth');
Route::post('/contributions/deposits', [ContributionController::class, 'depositsStore'])->name('contributions.deposits.store')->middleware('auth');
Route::get('/contributions/deposits/bulk-create', [ContributionController::class, 'depositsBulkCreate'])->name('contributions.deposits.bulk-create')->middleware('auth');
Route::get('/contributions/deposits/download-template', [ContributionController::class, 'downloadDepositTemplate'])->name('contributions.deposits.download-template')->middleware('auth');
Route::post('/contributions/deposits/bulk-store', [ContributionController::class, 'depositsBulkStore'])->name('contributions.deposits.bulk-store')->middleware('auth');
Route::get('/contributions/deposits/bulk-progress/{jobId}', [ContributionController::class, 'getBulkDepositProgress'])->name('contributions.deposits.bulk-progress')->middleware('auth');
Route::get('/contributions/deposits/download-failed/{jobId}', [ContributionController::class, 'downloadFailedDeposits'])->name('contributions.deposits.download-failed')->middleware('auth');
Route::get('/contributions/opening-balance', [ContributionController::class, 'openingBalanceIndex'])->name('contributions.opening-balance.index')->middleware('auth');
Route::get('/contributions/opening-balance/download-template', [ContributionController::class, 'downloadOpeningBalanceTemplate'])->name('contributions.opening-balance.download-template')->middleware('auth');
Route::post('/contributions/opening-balance/import', [ContributionController::class, 'importOpeningBalance'])->name('contributions.opening-balance.import')->middleware('auth');
Route::get('/contributions/withdrawals', [ContributionController::class, 'withdrawals'])->name('contributions.withdrawals.index')->middleware('auth');
Route::get('/contributions/withdrawals/data', [ContributionController::class, 'getWithdrawalsData'])->name('contributions.withdrawals.data')->middleware('auth');
Route::get('/contributions/withdrawals/create', [ContributionController::class, 'withdrawalsCreate'])->name('contributions.withdrawals.create')->middleware('auth');
Route::post('/contributions/withdrawals', [ContributionController::class, 'withdrawalsStore'])->name('contributions.withdrawals.store')->middleware('auth');
Route::get('/contributions/transfers', [ContributionController::class, 'transfers'])->name('contributions.transfers.index')->middleware('auth');
Route::get('/contributions/transfers/data', [ContributionController::class, 'getTransfersData'])->name('contributions.transfers.data')->middleware('auth');
Route::get('/contributions/transfers/create', [ContributionController::class, 'transfersCreate'])->name('contributions.transfers.create')->middleware('auth');
Route::post('/contributions/transfers', [ContributionController::class, 'transfersStore'])->name('contributions.transfers.store')->middleware('auth');
Route::get('/contributions/transfers/pending', [ContributionController::class, 'pendingTransfers'])->name('contributions.transfers.pending')->middleware('auth');
Route::get('/contributions/reports/balance', [ContributionController::class, 'balanceReport'])->name('contributions.reports.balance')->middleware('auth');
Route::get('/contributions/reports/transactions', [ContributionController::class, 'transactionsReport'])->name('contributions.reports.transactions')->middleware('auth');

// Investment (UTT) Routes
use App\Http\Controllers\InvestmentController;

Route::middleware(['auth'])->prefix('investments')->name('investments.')->group(function () {
    // Funds Management
    Route::get('/funds', [InvestmentController::class, 'fundsIndex'])->name('funds.index');
    Route::get('/funds/data', [InvestmentController::class, 'getFundsData'])->name('funds.data');
    Route::get('/funds/create', [InvestmentController::class, 'fundsCreate'])->name('funds.create');
    Route::post('/funds', [InvestmentController::class, 'fundsStore'])->name('funds.store');
    Route::get('/funds/{encodedId}', [InvestmentController::class, 'fundsShow'])->name('funds.show');
    Route::get('/funds/{encodedId}/edit', [InvestmentController::class, 'fundsEdit'])->name('funds.edit');
    Route::put('/funds/{encodedId}', [InvestmentController::class, 'fundsUpdate'])->name('funds.update');
    Route::post('/funds/{encodedId}/toggle-status', [InvestmentController::class, 'fundsToggleStatus'])->name('funds.toggle-status');

    // Holdings Management
    Route::get('/holdings', [InvestmentController::class, 'holdingsIndex'])->name('holdings.index');
    Route::get('/holdings/data', [InvestmentController::class, 'getHoldingsData'])->name('holdings.data');

    // Transactions Management
    Route::get('/transactions', [InvestmentController::class, 'transactionsIndex'])->name('transactions.index');
    Route::get('/transactions/data', [InvestmentController::class, 'getTransactionsData'])->name('transactions.data');
    Route::get('/transactions/create', [InvestmentController::class, 'transactionsCreate'])->name('transactions.create');
    Route::post('/transactions', [InvestmentController::class, 'transactionsStore'])->name('transactions.store');
    Route::get('/transactions/{encodedId}', [InvestmentController::class, 'transactionsShow'])->name('transactions.show');
    Route::post('/transactions/{encodedId}/approve', [InvestmentController::class, 'transactionsApprove'])->name('transactions.approve');
    Route::post('/transactions/{encodedId}/settle', [InvestmentController::class, 'transactionsSettle'])->name('transactions.settle');
    Route::post('/transactions/{encodedId}/cancel', [InvestmentController::class, 'transactionsCancel'])->name('transactions.cancel');

    // NAV Prices Management
    Route::get('/nav-prices', [InvestmentController::class, 'navPricesIndex'])->name('nav-prices.index');
    Route::get('/nav-prices/data', [InvestmentController::class, 'getNavPricesData'])->name('nav-prices.data');
    Route::get('/nav-prices/create', [InvestmentController::class, 'navPricesCreate'])->name('nav-prices.create');
    Route::post('/nav-prices', [InvestmentController::class, 'navPricesStore'])->name('nav-prices.store');

    // Cash Flows Management
    Route::get('/cash-flows', [InvestmentController::class, 'cashFlowsIndex'])->name('cash-flows.index');
    Route::get('/cash-flows/data', [InvestmentController::class, 'getCashFlowsData'])->name('cash-flows.data');

    // Reconciliations Management
    Route::get('/reconciliations', [InvestmentController::class, 'reconciliationsIndex'])->name('reconciliations.index');
    Route::get('/reconciliations/data', [InvestmentController::class, 'getReconciliationsData'])->name('reconciliations.data');
    Route::get('/reconciliations/create', [InvestmentController::class, 'reconciliationsCreate'])->name('reconciliations.create');
    Route::post('/reconciliations', [InvestmentController::class, 'reconciliationsStore'])->name('reconciliations.store');

    // Valuation & Reports
    Route::get('/valuation', [InvestmentController::class, 'getPortfolioValuation'])->name('valuation');

    // Member View (Read-Only)
    Route::get('/member-view', [InvestmentController::class, 'memberView'])->name('member-view');
});

//////////////////////////////////////////////////// END CONTRIBUTIONS //////////////////////////////////////////////////////////////

///////////////////////////////////////////////////ASSETS MANAGEMENT //////////////////////////////////////////////////////////////
// Asset settings
Route::prefix('asset-management')->name('assets.')->middleware(['auth', 'company.scope'])->group(function () {
    // Main Assets Dashboard
    Route::get('/', [App\Http\Controllers\Asset\AssetsController::class, 'index'])->name('index');
    
    // Movements / Transfers
    Route::get('/movements', [App\Http\Controllers\Asset\AssetMovementController::class, 'index'])->name('movements.index');
    Route::get('/movements/data', [App\Http\Controllers\Asset\AssetMovementController::class, 'data'])->name('movements.data');
    Route::get('/movements/create', [App\Http\Controllers\Asset\AssetMovementController::class, 'create'])->name('movements.create');
    Route::post('/movements', [App\Http\Controllers\Asset\AssetMovementController::class, 'store'])->name('movements.store');
    // Specific routes must come before the generic {id} route
    Route::post('/movements/{id}/approve', [App\Http\Controllers\Asset\AssetMovementController::class, 'approve'])->name('movements.approve');
    Route::post('/movements/{id}/complete', [App\Http\Controllers\Asset\AssetMovementController::class, 'complete'])->name('movements.complete');
    Route::post('/movements/{id}/reject', [App\Http\Controllers\Asset\AssetMovementController::class, 'reject'])->name('movements.reject');
    Route::get('/movements/{id}', [App\Http\Controllers\Asset\AssetMovementController::class, 'show'])->name('movements.show');

    // Lookups
    Route::get('/movements/lookup/departments', [App\Http\Controllers\Asset\AssetMovementController::class, 'departmentsByBranch'])->name('movements.lookup.departments');
    Route::get('/movements/lookup/users', [App\Http\Controllers\Asset\AssetMovementController::class, 'usersByBranch'])->name('movements.lookup.users');
    Route::get('/movements/lookup/asset-details', [App\Http\Controllers\Asset\AssetMovementController::class, 'assetDetails'])->name('movements.lookup.asset-details');
    Route::get('/settings', [App\Http\Controllers\AssetsController::class, 'settings'])->name('settings.index');
    Route::post('/settings', [App\Http\Controllers\AssetsController::class, 'updateSettings'])->name('settings.update');
    // Categories
    Route::get('/categories', [App\Http\Controllers\Asset\AssetCategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/data', [App\Http\Controllers\Asset\AssetCategoryController::class, 'data'])->name('categories.data');
    Route::get('/categories/create', [App\Http\Controllers\Asset\AssetCategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [App\Http\Controllers\Asset\AssetCategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{id}/edit', [App\Http\Controllers\Asset\AssetCategoryController::class, 'edit'])->name('categories.edit');
    Route::get('/categories/{id}', [App\Http\Controllers\Asset\AssetCategoryController::class, 'show'])->name('categories.show');
    Route::put('/categories/{id}', [App\Http\Controllers\Asset\AssetCategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}', [App\Http\Controllers\Asset\AssetCategoryController::class, 'destroy'])->name('categories.destroy');

    // Registry
    Route::get('/registry', [App\Http\Controllers\Asset\AssetRegistryController::class, 'index'])->name('registry.index');
    Route::get('/registry/data', [App\Http\Controllers\Asset\AssetRegistryController::class, 'data'])->name('registry.data');
    Route::get('/registry/create', [App\Http\Controllers\Asset\AssetRegistryController::class, 'create'])->name('registry.create');
    Route::post('/registry', [App\Http\Controllers\Asset\AssetRegistryController::class, 'store'])->name('registry.store');
    Route::post('/registry/import', [App\Http\Controllers\Asset\AssetRegistryController::class, 'import'])->name('registry.import');
    Route::get('/registry/download-template', [App\Http\Controllers\Asset\AssetRegistryController::class, 'downloadTemplate'])->name('registry.download-template');
    Route::get('/registry/{id}', [App\Http\Controllers\Asset\AssetRegistryController::class, 'show'])->name('registry.show');
    Route::get('/registry/{id}/depreciation-history', [App\Http\Controllers\Asset\AssetRegistryController::class, 'depreciationHistory'])->name('registry.depreciation-history');
    Route::get('/registry/{id}/depreciation-history/data', [App\Http\Controllers\Asset\AssetRegistryController::class, 'depreciationHistoryData'])->name('registry.depreciation-history-data');
    Route::get('/registry/{id}/edit', [App\Http\Controllers\Asset\AssetRegistryController::class, 'edit'])->name('registry.edit');
    Route::put('/registry/{id}', [App\Http\Controllers\Asset\AssetRegistryController::class, 'update'])->name('registry.update');
    Route::delete('/registry/{id}', [App\Http\Controllers\Asset\AssetRegistryController::class, 'destroy'])->name('registry.destroy');

    // Opening Assets
    Route::get('/openings', [App\Http\Controllers\Asset\OpeningAssetsController::class, 'index'])->name('openings.index');
    Route::get('/openings/data', [App\Http\Controllers\Asset\OpeningAssetsController::class, 'data'])->name('openings.data');
    Route::get('/openings/create', [App\Http\Controllers\Asset\OpeningAssetsController::class, 'create'])->name('openings.create');
    Route::post('/openings', [App\Http\Controllers\Asset\OpeningAssetsController::class, 'store'])->name('openings.store');
    Route::post('/openings/import', [App\Http\Controllers\Asset\OpeningAssetsController::class, 'import'])->name('openings.import');
    Route::get('/openings/download-template', [App\Http\Controllers\Asset\OpeningAssetsController::class, 'downloadTemplate'])->name('openings.download-template');
    Route::get('/openings/{id}', [App\Http\Controllers\Asset\OpeningAssetsController::class, 'show'])->name('openings.show');
    Route::delete('/openings/{id}', [App\Http\Controllers\Asset\OpeningAssetsController::class, 'destroy'])->name('openings.destroy');

    // Depreciation Management
    Route::get('/depreciation', [App\Http\Controllers\Asset\DepreciationController::class, 'index'])->name('depreciation.index');
    Route::post('/depreciation/process', [App\Http\Controllers\Asset\DepreciationController::class, 'process'])->name('depreciation.process');
    Route::get('/depreciation/history', [App\Http\Controllers\Asset\DepreciationController::class, 'history'])->name('depreciation.history');
    Route::get('/depreciation/history/data', [App\Http\Controllers\Asset\DepreciationController::class, 'historyData'])->name('depreciation.history.data');
    Route::get('/depreciation/forecast/{id}', [App\Http\Controllers\Asset\DepreciationController::class, 'forecast'])->name('depreciation.forecast');

    // Tax Depreciation Management
    Route::get('/tax-depreciation', [App\Http\Controllers\Asset\TaxDepreciationController::class, 'index'])->name('tax-depreciation.index');
    Route::post('/tax-depreciation/process', [App\Http\Controllers\Asset\TaxDepreciationController::class, 'process'])->name('tax-depreciation.process');
    Route::get('/tax-depreciation/history', [App\Http\Controllers\Asset\TaxDepreciationController::class, 'history'])->name('tax-depreciation.history');
    Route::get('/tax-depreciation/history/data', [App\Http\Controllers\Asset\TaxDepreciationController::class, 'historyData'])->name('tax-depreciation.history.data');

    // Tax Depreciation Reports
    Route::get('/tax-depreciation/reports/tra-schedule', [App\Http\Controllers\Asset\TaxDepreciationReportController::class, 'traSchedule'])->name('tax-depreciation.reports.tra-schedule');
    Route::get('/tax-depreciation/reports/tra-schedule/data', [App\Http\Controllers\Asset\TaxDepreciationReportController::class, 'traScheduleData'])->name('tax-depreciation.reports.tra-schedule.data');
    Route::get('/tax-depreciation/reports/book-tax-reconciliation', [App\Http\Controllers\Asset\TaxDepreciationReportController::class, 'bookTaxReconciliation'])->name('tax-depreciation.reports.book-tax-reconciliation');
    Route::get('/tax-depreciation/reports/book-tax-reconciliation/data', [App\Http\Controllers\Asset\TaxDepreciationReportController::class, 'bookTaxReconciliationData'])->name('tax-depreciation.reports.book-tax-reconciliation.data');

    // Deferred Tax Management
    Route::get('/deferred-tax', [App\Http\Controllers\Asset\DeferredTaxController::class, 'index'])->name('deferred-tax.index');
    Route::post('/deferred-tax/process', [App\Http\Controllers\Asset\DeferredTaxController::class, 'process'])->name('deferred-tax.process');
    Route::get('/deferred-tax/schedule', [App\Http\Controllers\Asset\DeferredTaxController::class, 'schedule'])->name('deferred-tax.schedule');
    Route::get('/deferred-tax/schedule/data', [App\Http\Controllers\Asset\DeferredTaxController::class, 'scheduleData'])->name('deferred-tax.schedule.data');

    // Revaluation & Impairment Settings
    Route::get('/revaluations/settings', [App\Http\Controllers\Assets\RevaluationSettingsController::class, 'index'])->name('revaluations.settings');
    Route::put('/revaluations/settings/category/{id}', [App\Http\Controllers\Assets\RevaluationSettingsController::class, 'updateCategory'])->name('revaluations.settings.update-category');
    Route::post('/revaluations/settings/bulk-update', [App\Http\Controllers\Assets\RevaluationSettingsController::class, 'updateBulk'])->name('revaluations.settings.bulk-update');

    // Revaluation & Impairment Management
    Route::prefix('revaluations')->name('revaluations.')->group(function () {
        Route::get('/', [App\Http\Controllers\Assets\AssetRevaluationController::class, 'index'])->name('index');
        Route::get('/data', [App\Http\Controllers\Assets\AssetRevaluationController::class, 'data'])->name('data');
        Route::get('/create', [App\Http\Controllers\Assets\AssetRevaluationController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Assets\AssetRevaluationController::class, 'store'])->name('store');
        Route::get('/{id}', [App\Http\Controllers\Assets\AssetRevaluationController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [App\Http\Controllers\Assets\AssetRevaluationController::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\Assets\AssetRevaluationController::class, 'update'])->name('update');
        Route::post('/{id}/submit', [App\Http\Controllers\Assets\AssetRevaluationController::class, 'submitForApproval'])->name('submit');
        Route::post('/{id}/approve', [App\Http\Controllers\Assets\AssetRevaluationController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [App\Http\Controllers\Assets\AssetRevaluationController::class, 'reject'])->name('reject');
        Route::post('/{id}/post-gl', [App\Http\Controllers\Assets\AssetRevaluationController::class, 'postToGL'])->name('post-gl');
        Route::delete('/{id}', [App\Http\Controllers\Assets\AssetRevaluationController::class, 'destroy'])->name('destroy');

        // Batch operations
        Route::get('/batch/{id}', [App\Http\Controllers\Assets\AssetRevaluationController::class, 'showBatch'])->name('batch.show');
        Route::post('/batch/{id}/submit', [App\Http\Controllers\Assets\AssetRevaluationController::class, 'submitBatchForApproval'])->name('batch.submit');
        Route::post('/batch/{id}/approve', [App\Http\Controllers\Assets\AssetRevaluationController::class, 'approveBatch'])->name('batch.approve');
        Route::post('/batch/{id}/reject', [App\Http\Controllers\Assets\AssetRevaluationController::class, 'rejectBatch'])->name('batch.reject');
    });

    Route::prefix('impairments')->name('impairments.')->group(function () {
        Route::get('/', [App\Http\Controllers\Assets\AssetImpairmentController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Assets\AssetImpairmentController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Assets\AssetImpairmentController::class, 'store'])->name('store');
        Route::get('/{id}', [App\Http\Controllers\Assets\AssetImpairmentController::class, 'show'])->name('show');
        Route::get('/{id}/create-reversal', [App\Http\Controllers\Assets\AssetImpairmentController::class, 'createReversal'])->name('create-reversal');
        Route::post('/{id}/reversal', [App\Http\Controllers\Assets\AssetImpairmentController::class, 'storeReversal'])->name('store-reversal');
        Route::post('/{id}/submit', [App\Http\Controllers\Assets\AssetImpairmentController::class, 'submitForApproval'])->name('submit');
        Route::post('/{id}/approve', [App\Http\Controllers\Assets\AssetImpairmentController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [App\Http\Controllers\Assets\AssetImpairmentController::class, 'reject'])->name('reject');
        Route::post('/{id}/post-gl', [App\Http\Controllers\Assets\AssetImpairmentController::class, 'postToGL'])->name('post-gl');
        Route::delete('/{id}', [App\Http\Controllers\Assets\AssetImpairmentController::class, 'destroy'])->name('destroy');
    });

    // Asset Disposal Management
    Route::prefix('disposals')->name('disposals.')->group(function () {
        Route::get('/', [App\Http\Controllers\Assets\AssetDisposalController::class, 'index'])->name('index');
        Route::get('/data', [App\Http\Controllers\Assets\AssetDisposalController::class, 'data'])->name('data');
        Route::get('/create', [App\Http\Controllers\Assets\AssetDisposalController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Assets\AssetDisposalController::class, 'store'])->name('store');

        // Disposal Reason Codes Management - MUST come before /{id} route
        Route::prefix('reason-codes')->name('reason-codes.')->group(function () {
            Route::get('/', [App\Http\Controllers\Assets\DisposalReasonCodeController::class, 'index'])->name('index');
            Route::get('/data', [App\Http\Controllers\Assets\DisposalReasonCodeController::class, 'data'])->name('data');
            Route::get('/create', [App\Http\Controllers\Assets\DisposalReasonCodeController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Assets\DisposalReasonCodeController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [App\Http\Controllers\Assets\DisposalReasonCodeController::class, 'edit'])->name('edit');
            Route::put('/{id}', [App\Http\Controllers\Assets\DisposalReasonCodeController::class, 'update'])->name('update');
            Route::delete('/{id}', [App\Http\Controllers\Assets\DisposalReasonCodeController::class, 'destroy'])->name('destroy');
        });

        // Specific routes must come before the generic {id} route
        Route::get('/{id}', [App\Http\Controllers\Assets\AssetDisposalController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [App\Http\Controllers\Assets\AssetDisposalController::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\Assets\AssetDisposalController::class, 'update'])->name('update');
        Route::post('/{id}/submit', [App\Http\Controllers\Assets\AssetDisposalController::class, 'submitForApproval'])->name('submit');
        Route::post('/{id}/approve', [App\Http\Controllers\Assets\AssetDisposalController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [App\Http\Controllers\Assets\AssetDisposalController::class, 'reject'])->name('reject');
        Route::post('/{id}/post-gl', [App\Http\Controllers\Assets\AssetDisposalController::class, 'postToGL'])->name('post-gl');
        Route::post('/{id}/record-receivable', [App\Http\Controllers\Assets\AssetDisposalController::class, 'recordReceivable'])->name('record-receivable');
        Route::delete('/{id}', [App\Http\Controllers\Assets\AssetDisposalController::class, 'destroy'])->name('destroy');
    });

    // Held for Sale (HFS) Management
    Route::prefix('hfs')->name('hfs.')->group(function () {
        // HFS Requests
        Route::prefix('requests')->name('requests.')->group(function () {
            Route::get('/', [App\Http\Controllers\Assets\Hfs\HfsRequestController::class, 'index'])->name('index');
            Route::get('/data', [App\Http\Controllers\Assets\Hfs\HfsRequestController::class, 'data'])->name('data');
            Route::get('/create', [App\Http\Controllers\Assets\Hfs\HfsRequestController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Assets\Hfs\HfsRequestController::class, 'store'])->name('store');
            Route::get('/{id}', [App\Http\Controllers\Assets\Hfs\HfsRequestController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [App\Http\Controllers\Assets\Hfs\HfsRequestController::class, 'edit'])->name('edit');
            Route::put('/{id}', [App\Http\Controllers\Assets\Hfs\HfsRequestController::class, 'update'])->name('update');
            Route::post('/{id}/submit', [App\Http\Controllers\Assets\Hfs\HfsRequestController::class, 'submitForApproval'])->name('submit');
            Route::post('/{id}/approve', [App\Http\Controllers\Assets\Hfs\HfsRequestController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [App\Http\Controllers\Assets\Hfs\HfsRequestController::class, 'reject'])->name('reject');
            Route::post('/{id}/cancel', [App\Http\Controllers\Assets\Hfs\HfsRequestController::class, 'cancel'])->name('cancel');
            Route::get('/{id}/validate', [App\Http\Controllers\Assets\Hfs\HfsRequestController::class, 'validateHfsRequest'])->name('validate');
        });

        // HFS Valuations
        Route::prefix('valuations')->name('valuations.')->group(function () {
            Route::get('/{hfsId}/create', [App\Http\Controllers\Assets\Hfs\HfsValuationController::class, 'create'])->name('create');
            Route::post('/{hfsId}', [App\Http\Controllers\Assets\Hfs\HfsValuationController::class, 'store'])->name('store');
            Route::put('/{hfsId}/{valuationId}', [App\Http\Controllers\Assets\Hfs\HfsValuationController::class, 'update'])->name('update');
        });

        // HFS Disposals
        Route::prefix('disposals')->name('disposals.')->group(function () {
            Route::get('/{hfsId}/create', [App\Http\Controllers\Assets\Hfs\HfsDisposalController::class, 'create'])->name('create');
            Route::post('/{hfsId}', [App\Http\Controllers\Assets\Hfs\HfsDisposalController::class, 'store'])->name('store');
        });

        // Discontinued Operations
        Route::prefix('discontinued')->name('discontinued.')->group(function () {
            Route::post('/{hfsId}/tag', [App\Http\Controllers\Assets\Hfs\HfsDiscontinuedController::class, 'tagAsDiscontinued'])->name('tag');
            Route::put('/{hfsId}/criteria', [App\Http\Controllers\Assets\Hfs\HfsDiscontinuedController::class, 'updateCriteria'])->name('update-criteria');
            Route::get('/{hfsId}/check', [App\Http\Controllers\Assets\Hfs\HfsDiscontinuedController::class, 'checkCriteria'])->name('check');
        });

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/movement-schedule', [App\Http\Controllers\Assets\Hfs\HfsReportController::class, 'movementSchedule'])->name('movement-schedule');
            Route::get('/valuation-details', [App\Http\Controllers\Assets\Hfs\HfsReportController::class, 'valuationDetails'])->name('valuation-details');
            Route::get('/discontinued-ops', [App\Http\Controllers\Assets\Hfs\HfsReportController::class, 'discontinuedOpsNote'])->name('discontinued-ops');
            Route::get('/overdue', [App\Http\Controllers\Assets\Hfs\HfsReportController::class, 'overdueReport'])->name('overdue');
            Route::get('/audit-trail', [App\Http\Controllers\Assets\Hfs\HfsReportController::class, 'auditTrail'])->name('audit-trail');
            Route::get('/audit-trail/{hfsId}', [App\Http\Controllers\Assets\Hfs\HfsReportController::class, 'auditTrail'])->name('audit-trail.detail');
        });
    });

    // Maintenance Management
    Route::prefix('maintenance')->name('maintenance.')->group(function () {
        // Dashboard
        Route::get('/', [App\Http\Controllers\Assets\MaintenanceController::class, 'index'])->name('index');
        Route::get('/settings', [App\Http\Controllers\Assets\MaintenanceController::class, 'settings'])->name('settings');
        Route::post('/settings', [App\Http\Controllers\Assets\MaintenanceController::class, 'updateSettings'])->name('settings.update');

        // Maintenance Types
        Route::get('/types', [App\Http\Controllers\Assets\MaintenanceTypeController::class, 'index'])->name('types.index');
        Route::get('/types/data', [App\Http\Controllers\Assets\MaintenanceTypeController::class, 'index'])->name('types.data');
        Route::get('/types/create', [App\Http\Controllers\Assets\MaintenanceTypeController::class, 'create'])->name('types.create');
        Route::post('/types', [App\Http\Controllers\Assets\MaintenanceTypeController::class, 'store'])->name('types.store');
        Route::get('/types/{id}/edit', [App\Http\Controllers\Assets\MaintenanceTypeController::class, 'edit'])->name('types.edit');
        Route::put('/types/{id}', [App\Http\Controllers\Assets\MaintenanceTypeController::class, 'update'])->name('types.update');
        Route::delete('/types/{id}', [App\Http\Controllers\Assets\MaintenanceTypeController::class, 'destroy'])->name('types.destroy');

        // Maintenance Requests
        Route::get('/requests', [App\Http\Controllers\Assets\MaintenanceRequestController::class, 'index'])->name('requests.index');
        Route::get('/requests/data', [App\Http\Controllers\Assets\MaintenanceRequestController::class, 'index'])->name('requests.data');
        Route::get('/requests/create', [App\Http\Controllers\Assets\MaintenanceRequestController::class, 'create'])->name('requests.create');
        Route::post('/requests', [App\Http\Controllers\Assets\MaintenanceRequestController::class, 'store'])->name('requests.store');
        Route::get('/requests/{id}', [App\Http\Controllers\Assets\MaintenanceRequestController::class, 'show'])->name('requests.show');
        Route::get('/requests/{id}/edit', [App\Http\Controllers\Assets\MaintenanceRequestController::class, 'edit'])->name('requests.edit');
        Route::put('/requests/{id}', [App\Http\Controllers\Assets\MaintenanceRequestController::class, 'update'])->name('requests.update');
        Route::post('/requests/{id}/approve', [App\Http\Controllers\Assets\MaintenanceRequestController::class, 'approve'])->name('requests.approve');
        Route::post('/requests/{id}/reject', [App\Http\Controllers\Assets\MaintenanceRequestController::class, 'reject'])->name('requests.reject');
        Route::delete('/requests/{id}', [App\Http\Controllers\Assets\MaintenanceRequestController::class, 'destroy'])->name('requests.destroy');

        // Work Orders
        Route::get('/work-orders', [App\Http\Controllers\Assets\WorkOrderController::class, 'index'])->name('work-orders.index');
        Route::get('/work-orders/data', [App\Http\Controllers\Assets\WorkOrderController::class, 'index'])->name('work-orders.data');
        Route::get('/work-orders/create', [App\Http\Controllers\Assets\WorkOrderController::class, 'create'])->name('work-orders.create');
        Route::post('/work-orders', [App\Http\Controllers\Assets\WorkOrderController::class, 'store'])->name('work-orders.store');
        Route::get('/work-orders/{id}', [App\Http\Controllers\Assets\WorkOrderController::class, 'show'])->name('work-orders.show');
        Route::get('/work-orders/{id}/edit', [App\Http\Controllers\Assets\WorkOrderController::class, 'edit'])->name('work-orders.edit');
        Route::put('/work-orders/{id}', [App\Http\Controllers\Assets\WorkOrderController::class, 'update'])->name('work-orders.update');
        Route::post('/work-orders/{id}/approve', [App\Http\Controllers\Assets\WorkOrderController::class, 'approve'])->name('work-orders.approve');
        Route::get('/work-orders/{id}/execute', [App\Http\Controllers\Assets\WorkOrderController::class, 'execute'])->name('work-orders.execute');
        Route::post('/work-orders/{id}/add-cost', [App\Http\Controllers\Assets\WorkOrderController::class, 'addCost'])->name('work-orders.add-cost');
        Route::post('/work-orders/{id}/complete', [App\Http\Controllers\Assets\WorkOrderController::class, 'complete'])->name('work-orders.complete');
        Route::get('/work-orders/{id}/review', [App\Http\Controllers\Assets\WorkOrderController::class, 'review'])->name('work-orders.review');
        Route::post('/work-orders/{id}/classify', [App\Http\Controllers\Assets\WorkOrderController::class, 'classify'])->name('work-orders.classify');
        Route::delete('/work-orders/{id}', [App\Http\Controllers\Assets\WorkOrderController::class, 'destroy'])->name('work-orders.destroy');
    });

    // Intangible Assets
    Route::prefix('intangible')->name('intangible.')->group(function () {
        Route::get('/', [App\Http\Controllers\Intangible\IntangibleAssetController::class, 'index'])->name('index');
        Route::get('/data', [App\Http\Controllers\Intangible\IntangibleAssetController::class, 'data'])->name('data');
        Route::get('/create', [App\Http\Controllers\Intangible\IntangibleAssetController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Intangible\IntangibleAssetController::class, 'store'])->name('store');

        // Intangible cost components
        Route::prefix('assets/{asset}/cost-components')->name('cost-components.')->group(function () {
            Route::get('/', [App\Http\Controllers\Intangible\IntangibleCostComponentController::class, 'index'])->name('index');
            Route::get('/data', [App\Http\Controllers\Intangible\IntangibleCostComponentController::class, 'data'])->name('data');
            Route::get('/create', [App\Http\Controllers\Intangible\IntangibleCostComponentController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Intangible\IntangibleCostComponentController::class, 'store'])->name('store');
            Route::get('/{component}/edit', [App\Http\Controllers\Intangible\IntangibleCostComponentController::class, 'edit'])->name('edit');
            Route::put('/{component}', [App\Http\Controllers\Intangible\IntangibleCostComponentController::class, 'update'])->name('update');
            Route::delete('/{component}', [App\Http\Controllers\Intangible\IntangibleCostComponentController::class, 'destroy'])->name('destroy');
            Route::get('/export', [App\Http\Controllers\Intangible\IntangibleCostComponentController::class, 'export'])->name('export');
        });

        // Intangible amortisation
        Route::get('/amortisation', [App\Http\Controllers\Intangible\IntangibleAmortisationController::class, 'index'])->name('amortisation.index');
        Route::post('/amortisation/process', [App\Http\Controllers\Intangible\IntangibleAmortisationController::class, 'process'])->name('amortisation.process');

        // Intangible impairment
        Route::get('/impairments/create', [App\Http\Controllers\Intangible\IntangibleImpairmentController::class, 'create'])->name('impairments.create');
        Route::post('/impairments', [App\Http\Controllers\Intangible\IntangibleImpairmentController::class, 'store'])->name('impairments.store');

        // Intangible disposal
        Route::get('/disposals/create', [App\Http\Controllers\Intangible\IntangibleDisposalController::class, 'create'])->name('disposals.create');
        Route::post('/disposals', [App\Http\Controllers\Intangible\IntangibleDisposalController::class, 'store'])->name('disposals.store');

        // Intangible categories
        Route::get('/categories', [App\Http\Controllers\Intangible\IntangibleCategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/data', [App\Http\Controllers\Intangible\IntangibleCategoryController::class, 'data'])->name('categories.data');
        Route::get('/categories/create', [App\Http\Controllers\Intangible\IntangibleCategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [App\Http\Controllers\Intangible\IntangibleCategoryController::class, 'store'])->name('categories.store');
    });
});

///////////// end // assets routes //////////////////////

////////////////////////////////////////////// PURCHASE MANAGEMENT ///////////////////////////////////////////

Route::prefix('purchases')->name('purchases.')->middleware(['auth', 'company.scope'])->group(function () {
    Route::get('/', [PurchaseController::class, 'index'])->name('index');

    // Purchase Requisitions
    Route::prefix('requisitions')->name('requisitions.')->group(function () {
        Route::get('/', [PurchaseRequisitionController::class, 'index'])->name('index');
        Route::get('/data', [PurchaseRequisitionController::class, 'data'])->name('data');
        Route::get('/create', [PurchaseRequisitionController::class, 'create'])->name('create');
        Route::post('/', [PurchaseRequisitionController::class, 'store'])->name('store');
        Route::post('/check-budget', [PurchaseRequisitionController::class, 'checkBudget'])->name('check-budget');
        Route::get('/{requisition}', [PurchaseRequisitionController::class, 'show'])->name('show');
        Route::post('/{requisition}/submit', [PurchaseRequisitionController::class, 'submit'])->name('submit');
        Route::post('/{requisition}/choose-supplier-create-po', [PurchaseRequisitionController::class, 'chooseSupplierAndCreatePo'])->name('choose-supplier-create-po');
        Route::post('/{requisition}/approve', [PurchaseRequisitionController::class, 'approve'])->name('approve');
        Route::post('/{requisition}/reject', [PurchaseRequisitionController::class, 'reject'])->name('reject');
        Route::post('/{requisition}/set-preferred-supplier', [PurchaseRequisitionController::class, 'setPreferredSupplierFromQuotation'])->name('set-preferred-supplier');
        Route::delete('/{requisition}', [PurchaseRequisitionController::class, 'destroy'])->name('destroy');
    });

    // Purchase Quotations
    Route::get('quotations', [QuotationController::class, 'index'])->name('quotations.index');
    Route::get('quotations/data', [QuotationController::class, 'data'])->name('quotations.data');
    Route::get('quotations/create', [QuotationController::class, 'create'])->name('quotations.create');
    Route::post('quotations', [QuotationController::class, 'store'])->name('quotations.store');
    Route::get('quotations/{quotation}', [QuotationController::class, 'show'])->name('quotations.show');
    Route::get('quotations/{quotation}/edit', [QuotationController::class, 'edit'])->name('quotations.edit');
    Route::put('quotations/{quotation}', [QuotationController::class, 'update'])->name('quotations.update');
    Route::delete('quotations/{quotation}', [QuotationController::class, 'destroy'])->name('quotations.destroy');
    Route::put('quotations/{quotation}/status', [QuotationController::class, 'updateStatus'])->name('quotations.updateStatus');
    Route::post('quotations/{quotation}/send-email', [QuotationController::class, 'sendEmail'])->name('quotations.send-email');
    Route::get('quotations/{quotation}/print', [QuotationController::class, 'print'])->name('quotations.print');

    // Purchase Orders
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/create', [OrderController::class, 'create'])->name('orders.create');
    Route::get('orders/create-from-stock', [OrderController::class, 'createFromStock'])->name('orders.create-from-stock');
    Route::post('orders', [OrderController::class, 'store'])->name('orders.store');
    // GRN from Order
    Route::get('orders/{encodedId}/grn/create', [OrderController::class, 'createGrnForm'])->name('orders.grn.create');
    Route::post('orders/{encodedId}/grn', [OrderController::class, 'storeGrn'])->name('orders.grn.store');

    // Standalone GRN
    Route::get('grn/create', [OrderController::class, 'createGrnForm'])->name('grn.create');
    Route::post('grn/standalone', [OrderController::class, 'storeStandaloneGrn'])->name('grn.store-standalone');

    // GRN CRUD
    Route::get('grn/{grn}', [OrderController::class, 'grnShow'])->name('grn.show');
    Route::get('grn/{grn}/print', [OrderController::class, 'grnPrint'])->name('grn.print');
    Route::get('grn/{grn}/edit', [OrderController::class, 'grnEdit'])->name('grn.edit');
    Route::put('grn/{grn}', [OrderController::class, 'grnUpdate'])->name('grn.update');
    Route::put('grn/{grn}/qc-items', [OrderController::class, 'grnUpdateLineQc'])->name('grn.qc-items.update');
    Route::put('grn/{grn}/qc', [OrderController::class, 'grnUpdateQc'])->name('grn.qc.update');
    Route::delete('grn/{grn}', [OrderController::class, 'grnDestroy'])->name('grn.destroy');
    Route::get('orders/{encodedId}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('orders/{encodedId}/edit', [OrderController::class, 'edit'])->name('orders.edit');
    Route::put('orders/{encodedId}', [OrderController::class, 'update'])->name('orders.update');
    Route::delete('orders/{encodedId}', [OrderController::class, 'destroy'])->name('orders.destroy');
    Route::put('orders/{encodedId}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::get('orders/{encodedId}/print', [OrderController::class, 'print'])->name('orders.print');
    Route::get('orders/convert-from-quotation/{quotation}', [OrderController::class, 'convertFromQuotation'])->name('orders.convert-from-quotation');

    // GRN Management
    Route::get('grn', [OrderController::class, 'grnIndex'])->name('grn.index');

    // Cash Purchases
    Route::prefix('cash-purchases')->name('cash-purchases.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Purchase\CashPurchaseController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Purchase\CashPurchaseController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Purchase\CashPurchaseController::class, 'store'])->name('store');
        Route::get('/{encodedId}', [\App\Http\Controllers\Purchase\CashPurchaseController::class, 'show'])->name('show');
        Route::get('/{encodedId}/edit', [\App\Http\Controllers\Purchase\CashPurchaseController::class, 'edit'])->name('edit');
        Route::get('/{encodedId}/export-pdf', [\App\Http\Controllers\Purchase\CashPurchaseController::class, 'exportPdf'])->name('export-pdf');
        Route::put('/{encodedId}', [\App\Http\Controllers\Purchase\CashPurchaseController::class, 'update'])->name('update');
        Route::delete('/{encodedId}', [\App\Http\Controllers\Purchase\CashPurchaseController::class, 'destroy'])->name('destroy');
    });

    // Opening Balances (Purchases)
    Route::get('opening-balances', [\App\Http\Controllers\Purchase\OpeningBalanceController::class, 'index'])->name('opening-balances.index');
    Route::get('opening-balances/create', [\App\Http\Controllers\Purchase\OpeningBalanceController::class, 'create'])->name('opening-balances.create');
    Route::post('opening-balances', [\App\Http\Controllers\Purchase\OpeningBalanceController::class, 'store'])->name('opening-balances.store');
    Route::get('opening-balances/{encodedId}', [\App\Http\Controllers\Purchase\OpeningBalanceController::class, 'show'])->name('opening-balances.show');

    // Debit Notes
    Route::prefix('debit-notes')->name('debit-notes.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Purchase\DebitNoteController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Purchase\DebitNoteController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Purchase\DebitNoteController::class, 'store'])->name('store');
        Route::get('/invoice-items/{invoice}', [\App\Http\Controllers\Purchase\DebitNoteController::class, 'invoiceItemsJson'])->name('invoice-items');
        Route::get('/{debitNote}', [\App\Http\Controllers\Purchase\DebitNoteController::class, 'show'])->name('show');
        Route::get('/{debitNote}/edit', [\App\Http\Controllers\Purchase\DebitNoteController::class, 'edit'])->name('edit');
        Route::put('/{debitNote}', [\App\Http\Controllers\Purchase\DebitNoteController::class, 'update'])->name('update');
        Route::delete('/{debitNote}', [\App\Http\Controllers\Purchase\DebitNoteController::class, 'destroy'])->name('destroy');
        Route::post('/{debitNote}/approve', [\App\Http\Controllers\Purchase\DebitNoteController::class, 'approve'])->name('approve');
        Route::post('/{debitNote}/apply', [\App\Http\Controllers\Purchase\DebitNoteController::class, 'apply'])->name('apply');
        Route::post('/{debitNote}/cancel', [\App\Http\Controllers\Purchase\DebitNoteController::class, 'cancel'])->name('cancel');
        Route::get('/api/inventory-item', [\App\Http\Controllers\Purchase\DebitNoteController::class, 'getInventoryItem'])->name('api.inventory-item');
    });
});

// Purchases Reports
Route::prefix('purchases/reports')->name('purchases.reports.')->middleware(['auth', 'company.scope'])->group(function () {
    Route::get('/', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'index'])->name('index');
    Route::get('/purchase-requisition', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'purchaseRequisitionReport'])->name('purchase-requisition');
    Route::get('/po-register', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'purchaseOrderRegister'])->name('purchase-order-register');
    Route::get('/po-register/export/pdf', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'exportPurchaseOrderRegisterPdf'])->name('purchase-order-register.export.pdf');
    Route::get('/po-register/export/excel', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'exportPurchaseOrderRegisterExcel'])->name('purchase-order-register.export.excel');
    Route::get('/po-vs-grn', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'poVsGrn'])->name('po-vs-grn');
    Route::get('/po-vs-grn/export/pdf', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'exportPoVsGrnPdf'])->name('po-vs-grn.export.pdf');
    Route::get('/po-vs-grn/export/excel', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'exportPoVsGrnExcel'])->name('po-vs-grn.export.excel');
    Route::get('/grn-variance', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'grnVariance'])->name('grn-variance');
    Route::get('/grn-variance/export/pdf', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'exportGrnVariancePdf'])->name('grn-variance.export.pdf');
    Route::get('/grn-variance/export/excel', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'exportGrnVarianceExcel'])->name('grn-variance.export.excel');
    Route::get('/invoice-register', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'invoiceRegister'])->name('invoice-register');
    Route::get('/invoice-register/export/pdf', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'exportInvoiceRegisterPdf'])->name('invoice-register.export.pdf');
    Route::get('/invoice-register/export/excel', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'exportInvoiceRegisterExcel'])->name('invoice-register.export.excel');
    Route::get('/supplier-statement', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'supplierStatement'])->name('supplier-statement');
    Route::get('/supplier-statement/export/pdf', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'exportSupplierStatementPdf'])->name('supplier-statement.export.pdf');
    Route::get('/supplier-statement/export/excel', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'exportSupplierStatementExcel'])->name('supplier-statement.export.excel');
    Route::get('/supplier-statement-old', [\App\Http\Controllers\Purchase\SupplierStatementController::class, 'index'])->name('supplier-statement.index');
    Route::post('/supplier-statement', [\App\Http\Controllers\Purchase\SupplierStatementController::class, 'generate'])->name('supplier-statement.generate');
    Route::post('/supplier-statement/export-pdf', [\App\Http\Controllers\Purchase\SupplierStatementController::class, 'exportPdf'])->name('supplier-statement.export-pdf');
    Route::post('/supplier-statement/export-excel', [\App\Http\Controllers\Purchase\SupplierStatementController::class, 'exportExcel'])->name('supplier-statement.export-excel');
    Route::get('/payables-aging', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'payablesAging'])->name('payables-aging');
    Route::get('/payables-aging/export/pdf', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'exportPayablesAgingPdf'])->name('payables-aging.export.pdf');
    Route::get('/payables-aging/export/excel', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'exportPayablesAgingExcel'])->name('payables-aging.export.excel');
    Route::get('/outstanding-invoices', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'outstandingInvoices'])->name('outstanding-invoices');
    Route::get('/paid-invoices', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'paidInvoices'])->name('paid-invoices');
    Route::get('/supplier-credit-note', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'supplierCreditNoteReport'])->name('supplier-credit-note');
    Route::get('/po-invoice-variance', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'poInvoiceVariance'])->name('po-invoice-variance');
    Route::get('/purchase-returns', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'purchaseReturnsReport'])->name('purchase-returns');
    Route::get('/purchase-by-supplier', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'purchaseBySupplier'])->name('purchase-by-supplier');
    Route::get('/purchase-by-item', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'purchaseByItem'])->name('purchase-by-item');
    Route::get('/purchase-forecast', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'purchaseForecast'])->name('purchase-forecast');
    Route::get('/supplier-tax', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'supplierTax'])->name('supplier-tax');
    Route::get('/payment-schedule', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'paymentSchedule'])->name('payment-schedule');
    Route::get('/three-way-matching-exception', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'threeWayMatchingException'])->name('three-way-matching-exception');
    Route::get('/supplier-performance', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'supplierPerformance'])->name('supplier-performance');
    Route::get('/purchase-price-variance', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'purchasePriceVariance'])->name('purchase-price-variance');
});

// Purchase Invoices
Route::middleware(['auth', 'company.scope'])->group(function () {
    Route::get('/purchases/purchase-invoices', [\App\Http\Controllers\Purchase\PurchaseInvoiceController::class, 'index'])->name('purchases.purchase-invoices.index');
    Route::get('/purchases/purchase-invoices/create', [\App\Http\Controllers\Purchase\PurchaseInvoiceController::class, 'create'])->name('purchases.purchase-invoices.create');
    Route::post('/purchases/purchase-invoices', [\App\Http\Controllers\Purchase\PurchaseInvoiceController::class, 'store'])->name('purchases.purchase-invoices.store');
    // Import routes must come BEFORE parameterized routes to avoid route conflicts
    Route::get('/purchases/purchase-invoices/import', [\App\Http\Controllers\Purchase\PurchaseInvoiceController::class, 'showImportForm'])->name('purchases.purchase-invoices.import');
    Route::post('/purchases/purchase-invoices/import-from-csv', [\App\Http\Controllers\Purchase\PurchaseInvoiceController::class, 'importFromCsv'])->name('purchases.purchase-invoices.import-from-csv');
    // Parameterized routes come after specific routes
    Route::get('/purchases/purchase-invoices/{encodedId}', [\App\Http\Controllers\Purchase\PurchaseInvoiceController::class, 'show'])->name('purchases.purchase-invoices.show');
    Route::get('/purchases/purchase-invoices/{encodedId}/edit', [\App\Http\Controllers\Purchase\PurchaseInvoiceController::class, 'edit'])->name('purchases.purchase-invoices.edit');
    Route::put('/purchases/purchase-invoices/{encodedId}', [\App\Http\Controllers\Purchase\PurchaseInvoiceController::class, 'update'])->name('purchases.purchase-invoices.update');
    Route::delete('/purchases/purchase-invoices/{encodedId}', [\App\Http\Controllers\Purchase\PurchaseInvoiceController::class, 'destroy'])->name('purchases.purchase-invoices.destroy');
    Route::get('/purchases/purchase-invoices/{encodedId}/payment', [\App\Http\Controllers\Purchase\PurchaseInvoiceController::class, 'paymentForm'])->name('purchases.purchase-invoices.payment-form');
    Route::post('/purchases/purchase-invoices/{encodedId}/payment', [\App\Http\Controllers\Purchase\PurchaseInvoiceController::class, 'recordPayment'])->name('purchases.purchase-invoices.record-payment');
    Route::get('/purchases/purchase-invoices/{encodedId}/export-pdf', [\App\Http\Controllers\Purchase\PurchaseInvoiceController::class, 'exportPdf'])->name('purchases.purchase-invoices.export-pdf');
    Route::post('/purchases/purchase-invoices/{encodedId}/send-email', [\App\Http\Controllers\Purchase\PurchaseInvoiceController::class, 'sendEmail'])->name('purchases.purchase-invoices.send-email');
    Route::delete('/purchases/purchase-invoices/{encodedId}/payment/{paymentEncodedId}', [\App\Http\Controllers\Purchase\PurchaseInvoiceController::class, 'destroyPayment'])->name('purchases.purchase-invoices.payment.destroy');
    Route::get('/purchases/purchase-invoices/{encodedId}/payment/{paymentEncodedId}/edit', [\App\Http\Controllers\Purchase\PurchaseInvoiceController::class, 'editPayment'])->name('purchases.purchase-invoices.payment.edit');
    Route::put('/purchases/purchase-invoices/{encodedId}/payment/{paymentEncodedId}', [\App\Http\Controllers\Purchase\PurchaseInvoiceController::class, 'updatePayment'])->name('purchases.purchase-invoices.payment.update');
    Route::get('/purchases/purchase-invoices/{encodedId}/payment/{paymentEncodedId}/print', [\App\Http\Controllers\Purchase\PurchaseInvoiceController::class, 'printPaymentReceipt'])->name('purchases.purchase-invoices.payment.print');
    Route::post('/purchases/purchase-invoices/{encodedId}/reprocess-items', [\App\Http\Controllers\Purchase\PurchaseInvoiceController::class, 'reprocessItems'])->name('purchases.purchase-invoices.reprocess-items');
});

////////////////////////////////////////////// END PURCHASE MANAGEMENT ///////////////////////////////////////////

////////////////////////////////////////////// LOAN PRODUCT MANAGEMENT ///////////////////////////////////////////

Route::middleware(['auth'])->group(function () {
    Route::get('loan-products', [LoanProductController::class, 'index'])->name('loan-products.index');
    Route::get('loan-products/create', [LoanProductController::class, 'create'])->name('loan-products.create');
    Route::post('loan-products', [LoanProductController::class, 'store'])->name('loan-products.store');
    Route::get('loan-products/{encodedId}', [LoanProductController::class, 'show'])->name('loan-products.show');
    Route::get('loan-products/{encodedId}/edit', [LoanProductController::class, 'edit'])->name('loan-products.edit');
    Route::put('loan-products/{encodedId}', [LoanProductController::class, 'update'])->name('loan-products.update');
    Route::delete('loan-products/{encodedId}', [LoanProductController::class, 'destroy'])->name('loan-products.destroy');
    Route::patch('loan-products/{encodedId}/toggle-status', [LoanProductController::class, 'toggleStatus'])->name('loan-products.toggle-status');
});

////////////////////////////////////////////// END LOAN PRODUCT MANAGEMENT ///////////////////////////////////////////

////////////////////////////////////////////// LOAN CALCULATOR ///////////////////////////////////////////

Route::middleware(['auth'])->group(function () {
    Route::get('loan-calculator', [LoanCalculatorController::class, 'index'])->name('loan-calculator.index');
    Route::post('loan-calculator/calculate', [LoanCalculatorController::class, 'calculate'])->name('loan-calculator.calculate');
    Route::post('loan-calculator/compare', [LoanCalculatorController::class, 'compare'])->name('loan-calculator.compare');
    Route::get('loan-calculator/products', [LoanCalculatorController::class, 'products'])->name('loan-calculator.products');
    Route::get('loan-calculator/product-details', [LoanCalculatorController::class, 'productDetails'])->name('loan-calculator.product-details');
    Route::get('loan-calculator/export-pdf', [LoanCalculatorController::class, 'exportPdf'])->name('loan-calculator.export-pdf');
    Route::get('loan-calculator/export-excel', [LoanCalculatorController::class, 'exportExcel'])->name('loan-calculator.export-excel');
    Route::get('loan-calculator/history', [LoanCalculatorController::class, 'history'])->name('loan-calculator.history');
    Route::post('loan-calculator/save', [LoanCalculatorController::class, 'save'])->name('loan-calculator.save');

    // Loan size type report
    Route::get('reports/loan-size-type', [LoanReportController::class, 'loanSizeTypeReport'])->name('reports.loan-size-type');
    Route::get('reports/loan-size-type/export', [LoanReportController::class, 'loanSizeTypeExport'])->name('reports.loan-size-type.export');
    Route::get('reports/loan-size-type/export-pdf', [LoanReportController::class, 'loanSizeTypeExportPdf'])->name('reports.loan-size-type.export-pdf');

    // Monthly performance report
    Route::get('reports/monthly-performance', [LoanReportController::class, 'monthlyPerformanceReport'])->name('reports.monthly-performance');
    Route::get('reports/monthly-performance/export', [LoanReportController::class, 'monthlyPerformanceExport'])->name('reports.monthly-performance.export');
    Route::get('reports/monthly-performance/export-pdf', [LoanReportController::class, 'monthlyPerformanceExportPdf'])->name('reports.monthly-performance.export-pdf');

    // New Balance Sheet report
    Route::get('reports/balance-sheet', [NewBalanceSheetReportController::class, 'index'])->name('reports.balance-sheet');

    // Simple SMS send endpoint for navbar modal
    Route::post('sms/send', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string|max:500'
        ]);
        try {
            $result = \App\Helpers\SmsHelper::send($validated['phone'], $validated['message']);
            if (is_array($result) && isset($result['success'])) {
                return response()->json($result);
            }
            return response()->json(['success' => true, 'response' => $result]);
        } catch (\Throwable $e) {
            \Log::error('SMS send failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'SMS send failed: ' . $e->getMessage()], 500);
        }
    })->name('sms.send');
});

////////////////////////////////////////////// END LOAN CALCULATOR ///////////////////////////////////////////

////////////////////////////////////////////// GROUP MANAGEMENT ///////////////////////////////////////////

Route::middleware(['auth'])->group(function () {
    Route::get('groups', [GroupController::class, 'index'])->name('groups.index');
    Route::get('groups/create', [GroupController::class, 'create'])->name('groups.create');
    Route::post('groups', [GroupController::class, 'store'])->name('groups.store');
    Route::get('groups/{encodedId}', [GroupController::class, 'show'])->name('groups.show');
    Route::get('groups/{encodedId}/edit', [GroupController::class, 'edit'])->name('groups.edit');
    Route::put('groups/{encodedId}', [GroupController::class, 'update'])->name('groups.update'); // Badilisha 'GroupController' na jina la controller yako halisi.
    Route::delete('groups/{encodedId}', [GroupController::class, 'destroy'])->name('groups.destroy');
    Route::get('groups/{encodedId}/payment', [GroupController::class, 'payment'])->name('groups.payment');

    // Specific repayment route - MUST come BEFORE catch-all route to avoid conflicts
    Route::post('repayments/settle-loan', [LoanRepaymentController::class, 'storeSettlementRepayment'])->name('repayments.settle');

    // Group repayment route - catch-all, must come AFTER specific routes
    Route::post('repayments/{encodedId}', [GroupController::class, 'groupStore'])->name('groups.groupStore');

    // Group member management routes
    Route::delete('groups/{encodedId}/members/{memberId}', [GroupController::class, 'removeMember'])->name('groups.members.remove');
    Route::post('groups/{encodedId}/transfer-member', [GroupController::class, 'transferMember'])->name('groups.members.transfer');
    Route::get('groups/{encodedId}/members-for-transfer', [GroupController::class, 'getMembersForTransfer'])->name('groups.members.for-transfer');
});
////////////////////////////////////////////// GROUP MEMBER MANAGEMENT ///////////////////////////////////////////

Route::middleware(['auth'])->group(function () {
    Route::get('groups/{encodedId}/members/create', [GroupMemberController::class, 'create'])->name('group-members.create');
    Route::post('groups/{encodedId}/members', [GroupMemberController::class, 'store'])->name('group-members.store');
    Route::delete('groups/{encodedId}/members/{member}', [GroupMemberController::class, 'destroy'])->name('group-members.destroy');
});

////////////////////////////////////////////// END GROUP MANAGEMENT ///////////////////////////////////////////

////////////////////////////////////////////// LOAN MANAGEMENT ///////////////////////////////////////////

Route::middleware(['auth'])->group(function () {
    Route::get('loans', [LoanController::class, 'index'])->name('loans.index');
    Route::get('loans/{encodedId}/fees-receipt', [LoanController::class, 'feesReceipt'])->name('loans.fees_receipt');
    Route::get('loans/list', [LoanController::class, 'listLoans'])->name('loans.list');
    Route::get('loans/writtenoff/data', [LoanController::class, 'getWrittenOffLoansData'])->name('loans.writtenoff.data');
    Route::get('loans/writtenoff', function () {
        $loans = \App\Models\Loan::with(['customer', 'product', 'branch'])
            ->where('status', 'written_off')
            ->get();
        return view('loans.written_off', compact('loans'));
    })->name('loans.writtenoff');
    Route::get('loans/chart-accounts/{type}', [LoanController::class, 'getChartAccountsByType'])->name('loans.chart-accounts');
    Route::post('loans/import', [LoanController::class, 'importLoans'])->name('loans.import');
    Route::get('loans/import-template', [LoanController::class, 'downloadTemplate'])->name('loans.import-template');
    
    // Bulk Repayment Import Routes
    Route::post('loans/repayments/bulk-import', [LoanController::class, 'bulkRepaymentImport'])->name('loans.repayments.bulk-import');
    Route::get('loans/repayments/import-template', [LoanController::class, 'downloadRepaymentTemplate'])->name('loans.repayments.import-template');
    
    Route::get('loans/status/{status}', [LoanController::class, 'loansByStatus'])->name('loans.by-status');

    // Opening Balance Routes for loans
    Route::get('loans/opening-balance/template', [LoanController::class, 'downloadOpeningBalanceTemplate'])->name('loans.opening-balance.template');
    Route::post('loans/opening-balance', [LoanController::class, 'storeOpeningBalance'])->name('loans.opening-balance.store');

    // New Loan Application Routes (must come BEFORE general loan routes)
    Route::get('loans/application', [LoanController::class, 'applicationIndex'])->name('loans.application.index');
    Route::get('loans/application/create', [LoanController::class, 'applicationCreate'])->name('loans.application.create');
    Route::post('loans/application', [LoanController::class, 'applicationStore'])->name('loans.application.store');
    Route::get('loans/application/{encodedId}', [LoanController::class, 'applicationShow'])->name('loans.application.show');
    Route::get('loans/application/{encodedId}/edit', [LoanController::class, 'applicationEdit'])->name('loans.application.edit');
    Route::put('loans/application/{encodedId}', [LoanController::class, 'applicationUpdate'])->name('loans.application.update');
    Route::patch('loans/application/{encodedId}/approve', [LoanController::class, 'applicationApprove'])->name('loans.application.approve');
    Route::patch('loans/application/{encodedId}/reject', [LoanController::class, 'applicationReject'])->name('loans.application.reject');
    Route::delete('loans/application/{encodedId}', [LoanController::class, 'applicationDelete'])->name('loans.application.delete');

    // Manual change status endpoint (used by UI change-status button)
    Route::post('loans/change-status', [LoanController::class, 'changeStatus'])->name('loans.change-status');

    // General loan routes (must come AFTER specific routes)
    Route::get('loans/create', [LoanController::class, 'create'])->name('loans.create');
    Route::post('loans', [LoanController::class, 'store'])->name('loans.store');
    Route::get('loans/{loan}', [LoanController::class, 'show'])->name('loans.show');
    Route::get('loans/{encodedId}/edit', [LoanController::class, 'edit'])->name('loans.edit');
    Route::put('loans/{encodedId}', [LoanController::class, 'update'])->name('loans.update');
    Route::get('loans/{encodedId}/top-up', [LoanTopUpController::class, 'show'])->name('loans.top_up');
    Route::post('loans/{encodedId}/top-up', [LoanTopUpController::class, 'store'])->name('loans.top_up.store');
    Route::delete('loans/{loan}', [LoanController::class, 'destroy'])->name('loans.destroy');
    Route::get('loans/applist', [LoanController::class, 'appList'])->name('loans.applist');
    Route::get('loans/appcreate', [LoanController::class, 'appCreate'])->name('loans.appcreate');
    Route::post('loans/appstore', [LoanController::class, 'appStore'])->name('loans.appstore');
    Route::get('loans/{loan}/appedit', [LoanController::class, 'appEdit'])->name('loans.appedit');
    Route::put('loans/{encodedId}/appupdate', [LoanController::class, 'appUpdate'])->name('loans.appupdate');
    Route::delete('loans/{loan}/appdestroy', [LoanController::class, 'appDestroy'])->name('loans.appdestroy');
    Route::get('loans/{loan}/appshow', [LoanController::class, 'appShow'])->name('loans.appshow');
    Route::post('/loan-files', [LoanController::class, 'loanDocument'])->name('loan-documents.store');
    Route::delete('/loan-documents/{loanFile}', [LoanController::class, 'destroyLoanDocument'])->name('loan-documents.destroy');
    Route::post('/loans/{loan}/guarantors', [LoanController::class, 'addGuarantor'])->name('loans.addGuarantor');
    Route::delete('/loans/{loan}/guarantors/{guarantor}', [LoanController::class, 'removeGuarantor'])->name('loans.removeGuarantor');
    Route::get('/loans/{encodedId}/export-details', [LoanController::class, 'exportLoanDetails'])->name('loans.export-details');

    // Loan Restructuring Routes
    Route::get('/loans/{encodedId}/restructure', [LoanController::class, 'restructure'])->name('loans.restructure');
    Route::post('/loans/{encodedId}/restructure/process', [LoanController::class, 'processRestructure'])->name('loans.restructure.process');

    // Loan Collateral Routes
    Route::post('/loan-collaterals', [LoanCollateralController::class, 'store'])->name('loan-collaterals.store');
    Route::get('/loan-collaterals/{collateral}', [LoanCollateralController::class, 'show'])->name('loan-collaterals.show');
    Route::put('/loan-collaterals/{collateral}', [LoanCollateralController::class, 'update'])->name('loan-collaterals.update');
    Route::patch('/loan-collaterals/{collateral}/status', [LoanCollateralController::class, 'updateStatus'])->name('loan-collaterals.update-status');
    Route::delete('/loan-collaterals/{collateral}', [LoanCollateralController::class, 'destroy'])->name('loan-collaterals.destroy');
    Route::delete('/loan-collaterals/{collateral}/remove-file', [LoanCollateralController::class, 'removeFile'])->name('loan-collaterals.remove-file');

    // Loan Repayment Routes
    Route::post('/repayments', [LoanRepaymentController::class, 'store'])->name('repayments.store');
    // Note: /repayments/settle-loan route is defined in GROUP MANAGEMENT section to avoid route conflicts
    Route::get('/repayments/history/{loanId}', [LoanRepaymentController::class, 'getRepaymentHistory'])->name('repayments.history');
    Route::get('/repayments/schedule/{scheduleId}', [LoanRepaymentController::class, 'getScheduleDetails'])->name('repayments.schedule-details');
    Route::post('/repayments/remove-penalty/{scheduleId}', [LoanRepaymentController::class, 'removePenalty'])->name('repayments.remove-penalty');
    Route::post('/repayments/calculate-schedule/{loanId}', [LoanRepaymentController::class, 'calculateSchedule'])->name('repayments.calculate-schedule');
    Route::post('/repayments/bulk', [LoanRepaymentController::class, 'bulkRepayment'])->name('repayments.bulk');
    Route::delete('/repayments/bulk-delete', [LoanRepaymentController::class, 'bulkDestroy'])->name('repayments.bulk-delete');

    // Repayment CRUD Routes
    Route::get('/repayments/{id}/edit', [LoanRepaymentController::class, 'edit'])->name('repayments.edit');
    Route::put('/repayments/{id}', [LoanRepaymentController::class, 'update'])->name('repayments.update');
    Route::delete('/repayments/{id}', [LoanRepaymentController::class, 'destroy'])->name('repayments.destroy');
    Route::get('/repayments/{id}/print', [LoanRepaymentController::class, 'printReceipt'])->name('repayments.print');
});

////////////////////////////////////////////// END LOAN MANAGEMENT ///////////////////////////////////////////

// Loan Approval Routes
Route::middleware(['auth'])->group(function () {
    Route::post('/loans/{encodedId}/check', [LoanController::class, 'checkLoan'])->name('loans.check');
    Route::post('/loans/{encodedId}/approve', [LoanController::class, 'approveLoan'])->name('loans.approve');
    Route::post('/loans/{encodedId}/authorize', [LoanController::class, 'authorizeLoan'])->name('loans.authorize');
    Route::post('/loans/{encodedId}/disburse', [LoanController::class, 'disburseLoan'])->name('loans.disburse');
    Route::post('/loans/{encodedId}/reject', [LoanController::class, 'rejectLoan'])->name('loans.reject');
    Route::post('/loans/{encodedId}/default', [LoanController::class, 'defaultLoan'])->name('loans.default');
    Route::post('/loans/{encodedId}/settle', [LoanController::class, 'settleRepayment'])->name('loans.settle');
});

////////////////////////////////////////////// CASHCOLLATERALS MANAGEMENT ///////////////////////////////////////////

Route::middleware(['auth'])->prefix('cash_collaterals')->group(function () {
    Route::get('/', [CashCollateralController::class, 'index'])->name('cash_collaterals.index');
    Route::get('/create', [CashCollateralController::class, 'create'])->name('cash_collaterals.create');
    Route::post('/', [CashCollateralController::class, 'store'])->name('cash_collaterals.store');
    Route::get('/{cashcollateral}', [CashCollateralController::class, 'show'])->name('cash_collaterals.show');
    Route::get('/{cashcollateral}/edit', [CashCollateralController::class, 'edit'])->name('cash_collaterals.edit');
    Route::delete('/{cashcollateral}/delete', [CashCollateralController::class, 'destroy'])->name('cash_collaterals.destroy');
    Route::put('/{cashcollateral}', [CashCollateralController::class, 'update'])->name('cash_collaterals.update');


    // Direct Receipt and Payment Routes for Cash Collateral
    Route::get('/receipts/{receipt}/edit', [CashCollateralController::class, 'editReceipt'])->name('receipts.edit');
    Route::put('/receipts/{receipt}', [CashCollateralController::class, 'updateReceipt'])->name('receipts.update');
    Route::delete('/receipts/{receipt}', [CashCollateralController::class, 'deleteReceipt'])->name('receipts.destroy');

    Route::get('/payments/{payment}/edit', [CashCollateralController::class, 'editPayment'])->name('payments.edit');
    Route::put('/payments/{payment}', [CashCollateralController::class, 'updatePayment'])->name('payments.update');
    Route::delete('/payments/{payment}', [CashCollateralController::class, 'deletePayment'])->name('payments.destroy');

    // Deposit and Withdrawal routes
    Route::get('/{cashcollateral}/deposit', [CashCollateralController::class, 'deposit'])->name('cash_collaterals.deposit');
    Route::post('/deposit-store', [CashCollateralController::class, 'depositStore'])->name('cash_collaterals.depositStore');
    Route::get('/print-deposit-receipt/{id}', [CashCollateralController::class, 'printDepositReceipt'])->name('cash_collaterals.printDepositReceipt');
    Route::get('/{cashcollateral}/withdraw', [CashCollateralController::class, 'withdraw'])->name('cash_collaterals.withdraw');
    Route::post('/withdraw-store', [CashCollateralController::class, 'withdrawStore'])->name('cash_collaterals.withdrawStore');
    Route::get('/print-withdrawal-receipt/{id}', [CashCollateralController::class, 'printWithdrawalReceipt'])->name('cash_collaterals.printWithdrawalReceipt');
});

////////////////////////////////////////////// END CASHCOLLATERALS  MANAGEMENT ///////////////////////////////////////////

Route::get('/get-districts/{regionId}', [LocationController::class, 'getDistricts']);

// Chat routes
Route::middleware(['auth'])->group(function () {
    Route::get('/chat', [App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/messages/{user}', [App\Http\Controllers\ChatController::class, 'fetchMessages'])->name('chat.messages');
    Route::post('/chat/send', [App\Http\Controllers\ChatController::class, 'sendMessage'])->name('chat.send');
    Route::post('/chat/mark-read', [App\Http\Controllers\ChatController::class, 'markAsRead'])->name('chat.mark-read');
    Route::get('/chat/unread-count', [App\Http\Controllers\ChatController::class, 'getUnreadCount'])->name('chat.unread-count');
    Route::post('/chat/clear', [App\Http\Controllers\ChatController::class, 'clearChat'])->name('chat.clear');
    Route::get('/chat/online-users', [App\Http\Controllers\ChatController::class, 'getOnlineUsers'])->name('chat.online-users');
    Route::get('/chat/download/{messageId}', [App\Http\Controllers\ChatController::class, 'downloadFile'])->name('chat.download');
});

// Calendar Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/calendar', [App\Http\Controllers\CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/loan-messages', [App\Http\Controllers\LoanMessagesController::class, 'getMessages'])->name('loan-messages.get');
});

Route::post('sms/bulk', [App\Http\Controllers\DashboardController::class, 'sendBulkSms'])->name('sms.bulk');

// Email Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/emails/compose', [EmailController::class, 'index'])->name('emails.compose');
    Route::get('/emails/microfinances', [EmailController::class, 'getMicrofinances'])->name('emails.microfinances');
    Route::post('/emails/send', [EmailController::class, 'sendBulkEmails'])->name('emails.send');
    Route::post('/emails/test', [EmailController::class, 'testEmail'])->name('emails.test');
});


Route::post('/logout', function (\Illuminate\Http\Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/')->with('success', 'You have been successfully logged out.');
})->middleware('auth')->name('logout');
