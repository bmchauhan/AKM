<?php

namespace App\Services\Admin;

use App\Enums\RoleType;
use App\Models\Role;
use Illuminate\Support\Collection;

class CommitteeRoleRegistry
{
    /** @var Collection<int, Role>|null */
    private ?Collection $committeeRoles = null;

    /**
     * @return Collection<int, Role>
     */
    public function committeeRoles(): Collection
    {
        return $this->committeeRoles ??= Role::query()
            ->where('role_type', RoleType::Committee->value)
            ->orderBy('name')
            ->get();
    }

    /**
     * @return list<string>
     */
    public function committeeSlugs(): array
    {
        return $this->committeeRoles()
            ->pluck('slug')
            ->all();
    }

    public function isCommitteeSlug(string $slug): bool
    {
        return in_array($slug, $this->committeeSlugs(), true);
    }

    public function isLeadershipSlug(string $slug): bool
    {
        $role = $this->findBySlug($slug);

        return $role?->is_leadership ?? false;
    }

    public function findBySlug(?string $slug): ?Role
    {
        if (! filled($slug)) {
            return null;
        }

        return $this->committeeRoles()->firstWhere('slug', $slug)
            ?? Role::query()->where('slug', $slug)->first();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function optionsForSelect(bool $includeEmptyOption = false): array
    {
        $options = $this->committeeRoles()
            ->map(fn (Role $role) => [
                'value' => $role->slug,
                'label' => $role->name.' ('.$role->short_form.')',
            ])
            ->values()
            ->all();

        if ($includeEmptyOption) {
            array_unshift($options, [
                'value' => '',
                'label' => __('messages.users_committee_none'),
            ]);
        }

        return $options;
    }

    public function flush(): void
    {
        $this->committeeRoles = null;
    }
}
