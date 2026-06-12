<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PasswordController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\Settings\ModuleController;
use App\Http\Controllers\Admin\Settings\PermissionController;
use App\Http\Controllers\Admin\Settings\RoleController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('frontend.home');
});

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
                ->middleware('admin.module:users,read')
                ->name('index');
            Route::get('/create', [UserController::class, 'create'])
                ->middleware('admin.module:users,create')
                ->name('create');
            Route::post('/', [UserController::class, 'store'])
                ->middleware('admin.module:users,create')
                ->name('store');
            Route::post('/household-members', [UserController::class, 'householdMembers'])
                ->middleware('admin.module:users,read')
                ->name('household-members');
            Route::post('/open-edit', [UserController::class, 'openEdit'])
                ->middleware('admin.module:users,update')
                ->name('open-edit');
            Route::get('/edit', [UserController::class, 'edit'])
                ->middleware('admin.module:users,update')
                ->name('edit');
            Route::put('/edit', [UserController::class, 'update'])
                ->middleware('admin.module:users,update')
                ->name('update');
            Route::delete('/', [UserController::class, 'destroy'])
                ->middleware('admin.module:users,delete')
                ->name('destroy');
        });

        Route::prefix('members')->name('members.')->group(function () {
            Route::get('/', [MemberController::class, 'index'])
                ->middleware('admin.module:members,read')
                ->name('index');
            Route::post('/select-household', [MemberController::class, 'selectHousehold'])
                ->middleware('admin.module:members,read')
                ->name('select-household');
            Route::get('/create', [MemberController::class, 'create'])
                ->middleware('admin.module:members,create')
                ->name('create');
            Route::post('/', [MemberController::class, 'store'])
                ->middleware('admin.module:members,create')
                ->name('store');
            Route::post('/open-edit', [MemberController::class, 'openEdit'])
                ->middleware('admin.module:members,update')
                ->name('open-edit');
            Route::get('/edit', [MemberController::class, 'edit'])
                ->middleware('admin.module:members,update')
                ->name('edit');
            Route::put('/edit', [MemberController::class, 'update'])
                ->middleware('admin.module:members,update')
                ->name('update');
            Route::delete('/', [MemberController::class, 'destroy'])
                ->middleware('admin.module:members,delete')
                ->name('destroy');
        });

        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', fn () => redirect()->route('admin.settings.roles.index'))
                ->middleware('admin.module:settings,read')
                ->name('index');
            Route::get('/modules', [ModuleController::class, 'index'])
                ->middleware('admin.module:settings,read')
                ->name('modules.index');
            Route::get('/permissions', [PermissionController::class, 'index'])
                ->middleware('admin.module:settings,read')
                ->name('permissions.index');
            Route::put('/permissions', [PermissionController::class, 'sync'])
                ->middleware('admin.module:settings,update')
                ->name('permissions.sync');
            Route::get('/roles', [RoleController::class, 'index'])
                ->middleware('admin.module:settings,read')
                ->name('roles.index');
            Route::put('/roles', [RoleController::class, 'sync'])
                ->middleware('admin.module:settings,update')
                ->name('roles.sync');
        });
    });
});
