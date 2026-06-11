<?php

namespace App\Repositories\Contracts;

use App\Enums\ModulePermissionAction;
use App\Models\Module;
use Illuminate\Support\Collection;

interface ModuleRepositoryInterface
{
    public function allOrdered(): Collection;

    public function findBySlug(string $slug): ?Module;

    public function roleCanOnModule(string $roleSlug, string $moduleSlug, ModulePermissionAction|string $action): bool;

    public function syncRolePermissions(int $roleId, array $permissions): void;
}
