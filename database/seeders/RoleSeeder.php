<?php

namespace Database\Seeders;

use App\Enums\RoleType;
use App\Enums\UserRole;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Super Admin',
                'short_form' => 'SA',
                'slug' => UserRole::SuperAdmin->value,
                'description' => 'System super administrator with full access to the admin panel and settings.',
                'is_system' => true,
                'role_type' => RoleType::SuperAdmin->value,
                'is_leadership' => false,
            ],
            [
                'name' => 'Chief Committee Member',
                'short_form' => 'CCM',
                'slug' => 'chief_committee_member',
                'description' => 'Head of the management committee.',
                'is_system' => false,
                'role_type' => RoleType::Committee->value,
                'is_leadership' => true,
            ],
            [
                'name' => 'Vice Chief Committee Member',
                'short_form' => 'VCCM',
                'slug' => 'vice_chief_committee_member',
                'description' => 'Deputy head of the management committee.',
                'is_system' => false,
                'role_type' => RoleType::Committee->value,
                'is_leadership' => true,
            ],
            [
                'name' => 'Finance Committee Member',
                'short_form' => 'FCM',
                'slug' => 'finance_committee_member',
                'description' => 'Committee member responsible for society finances.',
                'is_system' => false,
                'role_type' => RoleType::Committee->value,
                'is_leadership' => false,
            ],
            [
                'name' => 'Committee Member',
                'short_form' => 'CM',
                'slug' => 'committee_member',
                'description' => 'General management committee member.',
                'is_system' => false,
                'role_type' => RoleType::Committee->value,
                'is_leadership' => false,
            ],
        ];

        foreach ($roles as $role) {
            Role::query()->updateOrCreate(
                ['slug' => $role['slug']],
                $role,
            );
        }
    }
}
