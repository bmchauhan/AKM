<?php

namespace App\Providers;

use App\Enums\ModulePermissionAction;
use App\Models\Module;
use App\Models\User;
use App\Services\Admin\ModulePermissionService;
use App\Support\AdminGateAbility;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
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

        $this->registerModuleGates();
    }

    private function registerModuleGates(): void
    {
        if (! Schema::hasTable('modules') || ! Schema::hasColumn('modules', 'is_permission_target')) {
            return;
        }

        $permissionService = app(ModulePermissionService::class);

        Module::query()
            ->where('is_permission_target', true)
            ->orderBy('sort_order')
            ->each(function (Module $module) use ($permissionService) {
                foreach (ModulePermissionAction::cases() as $permissionAction) {
                    $ability = AdminGateAbility::name($module->slug, $permissionAction);

                    Gate::define($ability, fn (User $user): bool => $user->canOnAdminModule(
                        $module->slug,
                        $permissionAction,
                    ));
                }
            });

        Module::query()
            ->whereNull('parent_id')
            ->where('is_permission_target', false)
            ->orderBy('sort_order')
            ->each(function (Module $parent) use ($permissionService) {
                foreach (ModulePermissionAction::cases() as $permissionAction) {
                    $ability = AdminGateAbility::name($parent->slug, $permissionAction);

                    Gate::define($ability, fn (User $user): bool => $user->canOnAdminModuleGroup(
                        $parent->slug,
                        $permissionAction,
                    ));
                }
            });
    }
}
