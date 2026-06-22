<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Finance\CollectionController;
use App\Http\Controllers\Admin\Finance\ExpenseController;
use App\Http\Controllers\Admin\Finance\FinanceController;
use App\Http\Controllers\Admin\Finance\FundSettingController;
use App\Http\Controllers\Admin\Finance\MaintenanceChargeController;
use App\Http\Controllers\Admin\Finance\MaintenanceChargeLookupController;
use App\Http\Controllers\Admin\Finance\MaintenanceLedgerController;
use App\Http\Controllers\Admin\Finance\MyPaymentController;
use App\Http\Controllers\Admin\Finance\WorkerSalaryController;
use App\Http\Controllers\Admin\Workers\WorkerController;
use App\Http\Controllers\Admin\HouseController;
use App\Http\Controllers\Admin\HouseTransferController;
use App\Http\Controllers\Admin\VisitorEntryController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PasswordController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\Settings\EmailSettingsController;
use App\Http\Controllers\Admin\Settings\ModuleController;
use App\Http\Controllers\Admin\Settings\PermissionController;
use App\Http\Controllers\Admin\Settings\RoleController;
use App\Http\Controllers\Admin\LandingPage\DirectoryRoleController;
use App\Http\Controllers\Admin\LandingPage\UsefulDirectoryController;
use App\Http\Controllers\Frontend\CommitteeController;
use App\Http\Controllers\Frontend\UsefulDirectoryController as FrontendUsefulDirectoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('frontend.home');
})->name('home');

Route::view('/home-old', 'frontend.home-old')->name('home.old');

Route::view('/news', 'frontend.news')->name('news');
Route::view('/privacy', 'frontend.privacy')->name('privacy');
Route::view('/terms', 'frontend.terms')->name('terms');
Route::get('/our-committee', CommitteeController::class)->name('committee');
Route::get('/useful-directory', FrontendUsefulDirectoryController::class)->name('useful-directory');

Route::get('/lang/{locale}', function (string $locale) {
    if (! in_array($locale, ['en', 'hi', 'gu'], true)) {
        abort(400);
    }

    session(['locale' => $locale]);

    return back();
})->name('lang.switch');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::get('/reset-password', [PasswordController::class, 'edit'])->name('password.edit');
        Route::put('/reset-password', [PasswordController::class, 'update'])->name('password.update');

        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])
                ->middleware('admin.module:users_all,read')
                ->name('index');
            Route::get('/create', [UserController::class, 'create'])
                ->middleware('admin.module:users_add,create')
                ->name('create');
            Route::post('/', [UserController::class, 'store'])
                ->middleware('admin.module:users_add,create')
                ->name('store');
            Route::post('/household-members', [UserController::class, 'householdMembers'])
                ->middleware('admin.module:users_all,read')
                ->name('household-members');
            Route::post('/open-edit', [UserController::class, 'openEdit'])
                ->middleware('admin.module:users_all,update')
                ->name('open-edit');
            Route::get('/edit', [UserController::class, 'edit'])
                ->middleware('admin.module:users_all,update')
                ->name('edit');
            Route::put('/edit', [UserController::class, 'update'])
                ->middleware('admin.module:users_all,update')
                ->name('update');
            Route::post('/open-assign-role', [UserController::class, 'openAssignRole'])
                ->middleware('admin.module:users_all,update')
                ->name('open-assign-role');
            Route::get('/cancel-assign-role', [UserController::class, 'cancelAssignRole'])
                ->middleware('admin.module:users_all,update')
                ->name('cancel-assign-role');
            Route::post('/assign-role', [UserController::class, 'assignRole'])
                ->middleware('admin.module:users_all,update')
                ->name('assign-role');
            Route::delete('/', [UserController::class, 'destroy'])
                ->middleware('admin.module:users_all,delete')
                ->name('destroy');
        });

        Route::prefix('finance')->name('finance.')->group(function () {
            Route::get('/', [FinanceController::class, 'index'])
                ->middleware('admin.module:finance_overview,read')
                ->name('index');
            Route::get('/fund-setting', [FundSettingController::class, 'show'])
                ->middleware('admin.module:finance_fund_setting,read')
                ->name('fund-setting.show');
            Route::put('/fund-setting', [FundSettingController::class, 'update'])
                ->middleware('admin.module:finance_fund_setting,update')
                ->name('fund-setting.update');

            Route::get('/maintenance-charges', [MaintenanceChargeController::class, 'index'])
                ->middleware('admin.module:finance_maintenance_charges,read')
                ->name('maintenance-charges.index');
            Route::post('/maintenance-charges', [MaintenanceChargeController::class, 'store'])
                ->middleware('admin.module:finance_maintenance_charges,update')
                ->name('maintenance-charges.store');
            Route::post('/maintenance-charges/open-edit', [MaintenanceChargeController::class, 'openEdit'])
                ->middleware('admin.module:finance_maintenance_charges,update')
                ->name('maintenance-charges.open-edit');
            Route::get('/maintenance-charges/cancel-edit', [MaintenanceChargeController::class, 'cancelEdit'])
                ->middleware('admin.module:finance_maintenance_charges,update')
                ->name('maintenance-charges.cancel-edit');
            Route::put('/maintenance-charges/edit', [MaintenanceChargeController::class, 'update'])
                ->middleware('admin.module:finance_maintenance_charges,update')
                ->name('maintenance-charges.update');

            Route::get('/maintenance-charge', [MaintenanceChargeLookupController::class, 'show'])
                ->middleware('admin.module:finance_maintenance_charges,read')
                ->name('maintenance-charge.show');

            Route::post('/maintenance-ledger/apply-bulk-payment', [MaintenanceLedgerController::class, 'applyBulkPayment'])
                ->middleware('admin.module:finance_maintenance_ledger,update')
                ->name('maintenance-ledger.apply-bulk-payment');
            Route::get('/maintenance-ledger/house', [MaintenanceLedgerController::class, 'house'])
                ->middleware('admin.module:finance_house_ledger,read')
                ->name('maintenance-ledger.house');
            Route::get('/maintenance-ledger', [MaintenanceLedgerController::class, 'index'])
                ->middleware('admin.module:finance_maintenance_ledger,read')
                ->name('maintenance-ledger.index');
            Route::post('/maintenance-ledger/generate', [MaintenanceLedgerController::class, 'generate'])
                ->middleware('admin.module:finance_maintenance_ledger,update')
                ->name('maintenance-ledger.generate');
            Route::post('/maintenance-ledger/sync-missing', [MaintenanceLedgerController::class, 'syncMissing'])
                ->middleware('admin.module:finance_maintenance_ledger,update')
                ->name('maintenance-ledger.sync-missing');
            Route::post('/maintenance-ledger/open-edit', [MaintenanceLedgerController::class, 'openEdit'])
                ->middleware('admin.module:finance_maintenance_ledger,update')
                ->name('maintenance-ledger.open-edit');
            Route::get('/maintenance-ledger/cancel-edit', [MaintenanceLedgerController::class, 'cancelEdit'])
                ->middleware('admin.module:finance_maintenance_ledger,update')
                ->name('maintenance-ledger.cancel-edit');
            Route::put('/maintenance-ledger/edit', [MaintenanceLedgerController::class, 'update'])
                ->middleware('admin.module:finance_maintenance_ledger,update')
                ->name('maintenance-ledger.update');
            Route::post('/maintenance-ledger/mark-paid', [MaintenanceLedgerController::class, 'markPaid'])
                ->middleware('admin.module:finance_maintenance_ledger,update')
                ->name('maintenance-ledger.mark-paid');
            Route::get('/maintenance-ledger/export', [MaintenanceLedgerController::class, 'export'])
                ->middleware('admin.module:finance_maintenance_ledger,read')
                ->name('maintenance-ledger.export');

            Route::get('/collections', [CollectionController::class, 'index'])
                ->middleware('admin.module:finance_collections,read')
                ->name('collections.index');
            Route::post('/collections', [CollectionController::class, 'store'])
                ->middleware('admin.module:finance_collections,create')
                ->name('collections.store');
            Route::post('/collections/open-edit', [CollectionController::class, 'openEdit'])
                ->middleware('admin.module:finance_collections,update')
                ->name('collections.open-edit');
            Route::get('/collections/cancel-edit', [CollectionController::class, 'cancelEdit'])
                ->middleware('admin.module:finance_collections,update')
                ->name('collections.cancel-edit');
            Route::put('/collections/edit', [CollectionController::class, 'update'])
                ->middleware('admin.module:finance_collections,update')
                ->name('collections.update');
            Route::delete('/collections', [CollectionController::class, 'destroy'])
                ->middleware('admin.module:finance_collections,delete')
                ->name('collections.destroy');
            Route::get('/collections/export', [CollectionController::class, 'export'])
                ->middleware('admin.module:finance_collections,read')
                ->name('collections.export');

            Route::get('/expenses', [ExpenseController::class, 'index'])
                ->middleware('admin.module:finance_expenses,read')
                ->name('expenses.index');
            Route::post('/expenses', [ExpenseController::class, 'store'])
                ->middleware('admin.module:finance_expenses,create')
                ->name('expenses.store');
            Route::post('/expenses/open-edit', [ExpenseController::class, 'openEdit'])
                ->middleware('admin.module:finance_expenses,update')
                ->name('expenses.open-edit');
            Route::get('/expenses/cancel-edit', [ExpenseController::class, 'cancelEdit'])
                ->middleware('admin.module:finance_expenses,update')
                ->name('expenses.cancel-edit');
            Route::put('/expenses/edit', [ExpenseController::class, 'update'])
                ->middleware('admin.module:finance_expenses,update')
                ->name('expenses.update');
            Route::delete('/expenses', [ExpenseController::class, 'destroy'])
                ->middleware('admin.module:finance_expenses,delete')
                ->name('expenses.destroy');
            Route::get('/expenses/export', [ExpenseController::class, 'export'])
                ->middleware('admin.module:finance_expenses,read')
                ->name('expenses.export');

            Route::get('/worker-salary', [WorkerSalaryController::class, 'show'])
                ->middleware('admin.module:finance_expenses,read')
                ->name('worker-salary.show');

            Route::get('/my-payments', [MyPaymentController::class, 'index'])
                ->name('my-payments.index');
            Route::get('/my-payments/receipts/{receipt}/download', [MyPaymentController::class, 'downloadReceipt'])
                ->name('my-payments.receipt.download');
        });

        Route::prefix('workers')->name('workers.')->group(function () {
            Route::get('/', [WorkerController::class, 'index'])
                ->middleware('admin.module:workers,read')
                ->name('index');
            Route::post('/', [WorkerController::class, 'store'])
                ->middleware('admin.module:workers,create')
                ->name('store');
            Route::post('/create-login', [WorkerController::class, 'createLogin'])
                ->name('create-login');
            Route::post('/open-edit', [WorkerController::class, 'openEdit'])
                ->middleware('admin.module:workers,update')
                ->name('open-edit');
            Route::get('/cancel-edit', [WorkerController::class, 'cancelEdit'])
                ->middleware('admin.module:workers,update')
                ->name('cancel-edit');
            Route::put('/edit', [WorkerController::class, 'update'])
                ->middleware('admin.module:workers,update')
                ->name('update');
            Route::post('/salary', [WorkerController::class, 'storeSalary'])
                ->middleware('admin.module:workers,update')
                ->name('salary.store');
            Route::delete('/', [WorkerController::class, 'destroy'])
                ->middleware('admin.module:workers,delete')
                ->name('destroy');
        });

        Route::prefix('members')->name('members.')->group(function () {
            Route::get('/', [MemberController::class, 'index'])
                ->middleware('admin.module:members_all,read')
                ->name('index');
            Route::post('/select-household', [MemberController::class, 'selectHousehold'])
                ->middleware('admin.module:members_all,read')
                ->name('select-household');
            Route::get('/create', [MemberController::class, 'create'])
                ->middleware('admin.module:members_add,create')
                ->name('create');
            Route::post('/', [MemberController::class, 'store'])
                ->middleware('admin.module:members_add,create')
                ->name('store');
            Route::post('/open-edit', [MemberController::class, 'openEdit'])
                ->middleware('admin.module:members_all,update')
                ->name('open-edit');
            Route::get('/edit', [MemberController::class, 'edit'])
                ->middleware('admin.module:members_all,update')
                ->name('edit');
            Route::put('/edit', [MemberController::class, 'update'])
                ->middleware('admin.module:members_all,update')
                ->name('update');
            Route::delete('/', [MemberController::class, 'destroy'])
                ->middleware('admin.module:members_all,delete')
                ->name('destroy');
        });

        Route::prefix('visitors')->name('visitors.')->group(function () {
            Route::get('/', [VisitorEntryController::class, 'index'])
                ->middleware('admin.module:visitors_all,read')
                ->name('index');
            Route::get('/log', [VisitorEntryController::class, 'log'])
                ->middleware('admin.module:visitors_log,create')
                ->name('log');
            Route::post('/', [VisitorEntryController::class, 'store'])
                ->middleware('admin.module:visitors_log,create')
                ->name('store');
            Route::get('/today', [VisitorEntryController::class, 'today'])
                ->middleware('admin.module:visitors_log,read')
                ->name('today');
            Route::post('/house-hosts', [VisitorEntryController::class, 'houseHosts'])
                ->middleware('admin.module:visitors_log,read')
                ->name('house-hosts');
            Route::post('/checkout', [VisitorEntryController::class, 'checkout'])
                ->middleware('admin.module:visitors_log,update')
                ->name('checkout');
            Route::get('/{visitorEntry}', [VisitorEntryController::class, 'show'])
                ->middleware('admin.module:visitors_all,read')
                ->name('show');
            Route::delete('/', [VisitorEntryController::class, 'destroy'])
                ->middleware('admin.module:visitors_all,delete')
                ->name('destroy');
        });

        Route::prefix('houses')->name('houses.')->group(function () {
            Route::get('/transfers/history', [HouseController::class, 'transferHistory'])
                ->middleware('admin.module:houses_all,read')
                ->name('transfers.history');
            Route::get('/', [HouseController::class, 'index'])
                ->middleware('admin.module:houses_all,read')
                ->name('index');
            Route::get('/{house}', [HouseController::class, 'show'])
                ->middleware('admin.module:houses_all,read')
                ->name('show');
            Route::get('/{house}/transfer', [HouseTransferController::class, 'create'])
                ->middleware('admin.module:houses_transfer,create')
                ->name('transfer.create');
            Route::post('/{house}/transfer', [HouseTransferController::class, 'store'])
                ->middleware('admin.module:houses_transfer,create')
                ->name('transfer.store');
        });

        Route::prefix('landing-page')->name('landing-page.')->group(function () {
            Route::get('/directory-roles', [DirectoryRoleController::class, 'index'])
                ->middleware('admin.module:landing_page_directory_roles,read')
                ->name('directory-roles.index');
            Route::post('/directory-roles', [DirectoryRoleController::class, 'store'])
                ->middleware('admin.module:landing_page_directory_roles,create')
                ->name('directory-roles.store');
            Route::post('/directory-roles/open-edit', [DirectoryRoleController::class, 'openEdit'])
                ->middleware('admin.module:landing_page_directory_roles,update')
                ->name('directory-roles.open-edit');
            Route::get('/directory-roles/cancel-edit', [DirectoryRoleController::class, 'cancelEdit'])
                ->middleware('admin.module:landing_page_directory_roles,update')
                ->name('directory-roles.cancel-edit');
            Route::put('/directory-roles/edit', [DirectoryRoleController::class, 'update'])
                ->middleware('admin.module:landing_page_directory_roles,update')
                ->name('directory-roles.update');
            Route::delete('/directory-roles', [DirectoryRoleController::class, 'destroy'])
                ->middleware('admin.module:landing_page_directory_roles,delete')
                ->name('directory-roles.destroy');

            Route::get('/useful-directory', [UsefulDirectoryController::class, 'index'])
                ->middleware('admin.module:landing_page_useful_directory,read')
                ->name('useful-directory.index');
            Route::post('/useful-directory', [UsefulDirectoryController::class, 'store'])
                ->middleware('admin.module:landing_page_useful_directory,create')
                ->name('useful-directory.store');
            Route::post('/useful-directory/open-edit', [UsefulDirectoryController::class, 'openEdit'])
                ->middleware('admin.module:landing_page_useful_directory,update')
                ->name('useful-directory.open-edit');
            Route::get('/useful-directory/cancel-edit', [UsefulDirectoryController::class, 'cancelEdit'])
                ->middleware('admin.module:landing_page_useful_directory,update')
                ->name('useful-directory.cancel-edit');
            Route::put('/useful-directory/edit', [UsefulDirectoryController::class, 'update'])
                ->middleware('admin.module:landing_page_useful_directory,update')
                ->name('useful-directory.update');
            Route::delete('/useful-directory', [UsefulDirectoryController::class, 'destroy'])
                ->middleware('admin.module:landing_page_useful_directory,delete')
                ->name('useful-directory.destroy');
        });

        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', fn () => redirect()->route('admin.settings.roles.index'))
                ->middleware('admin.module:settings_roles,read')
                ->name('index');
            Route::get('/modules', [ModuleController::class, 'index'])
                ->middleware('admin.module:settings_modules,read')
                ->name('modules.index');
            Route::get('/permissions', [PermissionController::class, 'index'])
                ->middleware('admin.module:settings_permissions,read')
                ->name('permissions.index');
            Route::put('/permissions', [PermissionController::class, 'sync'])
                ->middleware('admin.module:settings_permissions,update')
                ->name('permissions.sync');
            Route::get('/roles', [RoleController::class, 'index'])
                ->middleware('admin.module:settings_roles,read')
                ->name('roles.index');
            Route::put('/roles', [RoleController::class, 'sync'])
                ->middleware('admin.module:settings_roles,update')
                ->name('roles.sync');
            Route::post('/roles/assign-user', [RoleController::class, 'assignUser'])
                ->middleware('admin.module:settings_roles,update')
                ->name('roles.assign-user');
            Route::get('/email', [EmailSettingsController::class, 'index'])
                ->middleware('admin.module:settings_email,read')
                ->name('email.index');
            Route::put('/email', [EmailSettingsController::class, 'update'])
                ->middleware('admin.module:settings_email,update')
                ->name('email.update');
        });
    });
});
