<?php

namespace App\Http\Middleware;

use App\Enums\ModulePermissionAction;
use App\Models\Module;
use App\Support\AdminGateAbility;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminModuleAccess
{
    public function handle(Request $request, Closure $next, string $module, string $action = 'read'): Response
    {
        $permissionAction = ModulePermissionAction::tryFrom($action);

        if (! $permissionAction || ! Module::query()->where('slug', $module)->exists()) {
            abort(403, __('messages.admin_module_forbidden'));
        }

        $ability = AdminGateAbility::name($module, $permissionAction);

        if (! $request->user()?->can($ability)) {
            abort(403, __('messages.admin_module_forbidden'));
        }

        return $next($request);
    }
}
