<?php

namespace App\Providers;

use App\Enums\AdminModule;
use App\Enums\ModulePermissionAction;
use App\Models\User;
use App\Support\AdminGateAbility;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class GateServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::define('super-admin', fn (User $user): bool => $user->isSuperAdmin());

        Gate::define('users.manage', function (User $user, User $target, string $action = 'update'): bool {
            return $user->canManageUser($target, $action);
        });

        Gate::define('members.manage', function (User $user, User $target, string $action = 'update'): bool {
            return $user->canManageMember($target, $action);
        });

        foreach (AdminModule::cases() as $module) {
            foreach (ModulePermissionAction::cases() as $permissionAction) {
                $ability = AdminGateAbility::name($module, $permissionAction);

                Gate::define($ability, fn (User $user): bool => $user->canOnAdminModule(
                    $module,
                    $permissionAction,
                ));
            }
        }
    }
}
