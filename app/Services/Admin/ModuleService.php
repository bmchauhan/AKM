<?php

namespace App\Services\Admin;

use App\Models\Module;
use App\Repositories\Contracts\ModuleRepositoryInterface;

class ModuleService
{
    public function __construct(
        private readonly ModuleRepositoryInterface $modules,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function listForScreen(): array
    {
        $rows = [];

        $parents = Module::query()
            ->with(['children' => fn ($query) => $query->orderBy('sort_order')->orderBy('name')])
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        foreach ($parents as $parent) {
            $rows[] = $this->mapModule($parent, 0);

            foreach ($parent->children as $child) {
                $rows[] = $this->mapModule($child, 1);
            }
        }

        return $rows;
    }

    /**
     * @return array<string, mixed>
     */
    private function mapModule(Module $module, int $depth): array
    {
        return [
            'id' => $module->id,
            'slug' => $module->slug,
            'name' => $module->name,
            'description' => $module->description ?? '',
            'is_system' => $module->is_system,
            'is_permission_target' => $module->is_permission_target,
            'sort_order' => $module->sort_order,
            'parent_id' => $module->parent_id,
            'depth' => $depth,
            'type_label' => $depth === 0
                ? __('messages.modules_type_main')
                : __('messages.modules_type_sub'),
            'roles_count' => $module->is_permission_target
                ? $module->roles()->count()
                : ($module->parent?->roles()->count() ?? 0),
        ];
    }
}
