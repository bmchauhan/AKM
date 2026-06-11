<?php

namespace App\Services\Admin;

use App\Repositories\Contracts\ModuleRepositoryInterface;

class ModuleService
{
    public function __construct(
        private readonly ModuleRepositoryInterface $modules,
    ) {}

    public function listForScreen(): array
    {
        return $this->modules->allOrdered()
            ->map(fn ($module) => [
                'id' => $module->id,
                'slug' => $module->slug,
                'name' => $module->name,
                'description' => $module->description ?? '',
                'is_system' => $module->is_system,
                'sort_order' => $module->sort_order,
                'roles_count' => $module->roles()->count(),
            ])
            ->values()
            ->all();
    }
}
