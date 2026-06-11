<?php

namespace App\Http\Middleware;

use App\Enums\AdminModule;
use App\Enums\ModulePermissionAction;
use App\Support\AdminGateAbility;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminModuleAccess
{
    public function handle(Request $request, Closure $next, string $module, string $action = 'read'): Response
    {
        $adminModule = AdminModule::tryFrom($module);
        $permissionAction = ModulePermissionAction::tryFrom($action);

        if (! $adminModule || ! $permissionAction) {
            abort(403, __('messages.admin_module_forbidden'));
        }

        $ability = AdminGateAbility::name($adminModule, $permissionAction);

        if (! $request->user()?->can($ability)) {
            abort(403, __('messages.admin_module_forbidden'));
        }

        return $next($request);
    }
}
