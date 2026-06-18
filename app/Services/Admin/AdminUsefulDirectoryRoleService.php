<?php

namespace App\Services\Admin;

use App\Models\UsefulDirectoryRole;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminUsefulDirectoryRoleService
{
    public const SESSION_EDITING_ROLE = 'admin.landing_page.directory_roles.editing_role_id';

    /**
     * @return array{roles: LengthAwarePaginator}
     */
    public function listForScreen(): array
    {
        $roles = UsefulDirectoryRole::query()
            ->withCount('contacts')
            ->orderBy('sort_order')
            ->orderBy('name_en')
            ->paginate(20);

        $roles->getCollection()->transform(fn (UsefulDirectoryRole $role) => $this->mapListRow($role));

        return ['roles' => $roles];
    }

    /**
     * @return list<array{id: int, slug: string, label: string, supports_committee_link: bool}>
     */
    public function activeTabOptions(): array
    {
        return UsefulDirectoryRole::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name_en')
            ->get()
            ->map(fn (UsefulDirectoryRole $role) => $role->toTabOption())
            ->all();
    }

    public function resolveTabSlug(string $tab): string
    {
        $role = UsefulDirectoryRole::query()
            ->where('is_active', true)
            ->where('slug', $tab)
            ->first();

        if ($role) {
            return $role->slug;
        }

        return UsefulDirectoryRole::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->value('slug') ?? 'services';
    }

    public function findActiveBySlug(string $slug): ?UsefulDirectoryRole
    {
        return UsefulDirectoryRole::query()
            ->where('is_active', true)
            ->where('slug', $slug)
            ->first();
    }

    public function committeeLinkRole(): ?UsefulDirectoryRole
    {
        return UsefulDirectoryRole::query()
            ->where('is_active', true)
            ->where('supports_committee_link', true)
            ->orderBy('sort_order')
            ->first();
    }

    /**
     * @param  array{
     *     slug?: ?string,
     *     name_en: string,
     *     name_hi?: ?string,
     *     name_gu?: ?string,
     *     sort_order?: int|string,
     *     supports_committee_link?: bool|string|int,
     *     is_active?: bool|string|int
     * }  $data
     */
    public function create(array $data): UsefulDirectoryRole
    {
        $supportsCommittee = filter_var($data['supports_committee_link'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $slug = $this->resolveSlug($data);

        if ($supportsCommittee) {
            $this->clearOtherCommitteeLinkRoles();
        }

        return UsefulDirectoryRole::query()->create([
            'slug' => $slug,
            'name_en' => $data['name_en'],
            'name_hi' => $data['name_hi'] ?? null,
            'name_gu' => $data['name_gu'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'supports_committee_link' => $supportsCommittee,
            'is_system' => false,
            'is_active' => filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    /**
     * @param  array{
     *     slug?: ?string,
     *     name_en: string,
     *     name_hi?: ?string,
     *     name_gu?: ?string,
     *     sort_order?: int|string,
     *     supports_committee_link?: bool|string|int,
     *     is_active?: bool|string|int
     * }  $data
     */
    public function update(UsefulDirectoryRole $role, array $data): UsefulDirectoryRole
    {
        $supportsCommittee = filter_var($data['supports_committee_link'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($supportsCommittee) {
            $this->clearOtherCommitteeLinkRoles($role->id);
        }

        $role->update([
            'name_en' => $data['name_en'],
            'name_hi' => $data['name_hi'] ?? null,
            'name_gu' => $data['name_gu'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'supports_committee_link' => $supportsCommittee,
            'is_active' => filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ]);

        return $role->fresh();
    }

    public function delete(UsefulDirectoryRole $role): void
    {
        if ($role->is_system) {
            throw ValidationException::withMessages([
                'role_id' => __('messages.directory_roles_system_delete_forbidden'),
            ]);
        }

        if ($role->contacts()->exists()) {
            throw ValidationException::withMessages([
                'role_id' => __('messages.directory_roles_has_contacts'),
            ]);
        }

        $role->delete();
    }

    public function rememberEditingRole(UsefulDirectoryRole $role): void
    {
        session([self::SESSION_EDITING_ROLE => $role->id]);
    }

    public function clearEditingRole(): void
    {
        session()->forget(self::SESSION_EDITING_ROLE);
    }

    public function editingRole(): ?UsefulDirectoryRole
    {
        $id = session(self::SESSION_EDITING_ROLE);

        if (! $id) {
            return null;
        }

        return UsefulDirectoryRole::query()->find($id);
    }

    /**
     * @return array<string, mixed>
     */
    public function mapListRow(UsefulDirectoryRole $role): array
    {
        return [
            'id' => $role->id,
            'slug' => $role->slug,
            'name_en' => $role->name_en,
            'name_hi' => $role->name_hi ?: '—',
            'name_gu' => $role->name_gu ?: '—',
            'localized_name' => $role->localizedName(),
            'sort_order' => $role->sort_order,
            'supports_committee_link' => $role->supports_committee_link,
            'is_system' => $role->is_system,
            'is_active' => $role->is_active,
            'contacts_count' => $role->contacts_count ?? $role->contacts()->count(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveSlug(array $data): string
    {
        $base = Str::slug($data['slug'] ?? $data['name_en']);
        $slug = $base !== '' ? $base : 'directory-role';
        $candidate = $slug;
        $suffix = 1;

        while (UsefulDirectoryRole::query()->where('slug', $candidate)->exists()) {
            $candidate = $slug.'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function clearOtherCommitteeLinkRoles(?int $exceptId = null): void
    {
        UsefulDirectoryRole::query()
            ->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))
            ->update(['supports_committee_link' => false]);
    }
}
