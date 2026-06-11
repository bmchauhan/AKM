<?php

namespace App\Support;

use App\Enums\AdminModule;
use App\Enums\ModulePermissionAction;

class AdminGateAbility
{
    public static function name(AdminModule|string $module, ModulePermissionAction|string $action): string
    {
        $moduleKey = $module instanceof AdminModule ? $module->value : $module;
        $actionKey = $action instanceof ModulePermissionAction ? $action->value : $action;

        return "{$moduleKey}.{$actionKey}";
    }
}
