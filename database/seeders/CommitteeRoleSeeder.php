<?php

namespace Database\Seeders;

use App\Enums\MembershipRole;
use App\Enums\RoleType;
use App\Models\Module;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommitteeRoleSeeder extends Seeder
{
    /**
     * Society-specific committee roles created through the dynamic roles model.
     * Add new committee posts here for fresh installs and db:seed runs.
     *
     * @return list<array{
     *     name: string,
     *     short_form: string,
     *     slug: string,
     *     description: string,
     *     is_leadership: bool,
     *     permissions: array<string, array{can_create: bool, can_read: bool, can_update: bool, can_delete: bool}>
     * }>
     */
    private function committeeRoleDefinitions(): array
    {
        return [
            [
                'name' => 'Money Collector',
                'short_form' => 'MCOL',
                'slug' => 'money_collector',
                'description' => 'Collects maintenance and society dues from members.',
                'is_leadership' => false,
                'permissions' => [
                    'finance_collections' => [
                        'can_create' => true,
                        'can_read' => true,
                        'can_update' => true,
                        'can_delete' => false,
                    ],
                    'finance_maintenance_ledger' => [
                        'can_create' => false,
                        'can_read' => true,
                        'can_update' => true,
                        'can_delete' => false,
                    ],
                ],
            ],
        ];
    }

    public function run(): void
    {
        foreach ($this->committeeRoleDefinitions() as $definition) {
            $permissions = $definition['permissions'];
            unset($definition['permissions']);

            $role = Role::query()->updateOrCreate(
                ['slug' => $definition['slug']],
                [
                    ...$definition,
                    'is_system' => false,
                    'role_type' => RoleType::Committee->value,
                ],
            );

            $this->syncPermissions($role, $permissions);

            $this->command?->info(sprintf('Committee role seeded: %s (%s)', $role->name, $role->slug));
        }

        $this->seedDemoMoneyCollectorAssignee();
    }

    private function seedDemoMoneyCollectorAssignee(): void
    {
        if (User::query()->where('committee_role', 'money_collector')->exists()) {
            return;
        }

        $candidate = User::query()
            ->where('membership_type', MembershipRole::MainMember->value)
            ->whereNull('committee_role')
            ->where('email', 'like', '%@demo.akmyug.local')
            ->orderBy('id')
            ->first();

        if (! $candidate) {
            return;
        }

        $candidate->update([
            'committee_role' => 'money_collector',
            'role' => User::syncLegacyRole($candidate->membership_type, 'money_collector'),
        ]);

        $this->command?->info(sprintf(
            'Assigned money_collector to demo user: %s (%s)',
            $candidate->fullName(),
            $candidate->email,
        ));
    }

    /**
     * @param  array<string, array{can_create: bool, can_read: bool, can_update: bool, can_delete: bool}>  $permissions
     */
    private function syncPermissions(Role $role, array $permissions): void
    {
        $moduleIds = Module::query()
            ->whereIn('slug', array_keys($permissions))
            ->pluck('id', 'slug');

        $sync = [];

        foreach ($permissions as $moduleSlug => $access) {
            $moduleId = $moduleIds->get($moduleSlug);

            if (! $moduleId) {
                continue;
            }

            $sync[(int) $moduleId] = [
                'can_create' => (bool) ($access['can_create'] ?? false),
                'can_read' => (bool) ($access['can_read'] ?? false),
                'can_update' => (bool) ($access['can_update'] ?? false),
                'can_delete' => (bool) ($access['can_delete'] ?? false),
            ];
        }

        if ($sync !== []) {
            $role->modules()->sync($sync);
        }
    }
}
