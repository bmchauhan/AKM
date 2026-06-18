<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $roles = [
            [
                'slug' => 'services',
                'name_en' => 'Services',
                'name_hi' => 'सेवाएं',
                'name_gu' => 'સેવાઓ',
                'sort_order' => 1,
                'supports_committee_link' => false,
                'is_system' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'government',
                'name_en' => 'Government',
                'name_hi' => 'सरकार',
                'name_gu' => 'સરકાર',
                'sort_order' => 2,
                'supports_committee_link' => false,
                'is_system' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'emergency',
                'name_en' => 'Emergency',
                'name_hi' => 'आपातकाल',
                'name_gu' => 'કટોકટી',
                'sort_order' => 3,
                'supports_committee_link' => false,
                'is_system' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'committee',
                'name_en' => 'Committee',
                'name_hi' => 'समिति',
                'name_gu' => 'સમિતિ',
                'sort_order' => 4,
                'supports_committee_link' => true,
                'is_system' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($roles as $role) {
            DB::table('useful_directory_roles')->updateOrInsert(
                ['slug' => $role['slug']],
                $role,
            );
        }

        if (! Schema::hasColumn('useful_directory_contacts', 'directory_role_id')) {
            Schema::table('useful_directory_contacts', function (Blueprint $table) {
                $table->foreignId('directory_role_id')->nullable()->after('id')->constrained('useful_directory_roles')->cascadeOnDelete();
            });
        }

        $roleIds = DB::table('useful_directory_roles')->pluck('id', 'slug');

        if (Schema::hasColumn('useful_directory_contacts', 'category')) {
            DB::table('useful_directory_contacts')
                ->orderBy('id')
                ->get(['id', 'category'])
                ->each(function ($contact) use ($roleIds) {
                    $slug = $contact->category ?: 'services';
                    $roleId = $roleIds->get($slug) ?? $roleIds->get('services');

                    DB::table('useful_directory_contacts')
                        ->where('id', $contact->id)
                        ->update(['directory_role_id' => $roleId]);
                });
        }

        if (Schema::hasColumn('useful_directory_contacts', 'category')) {
            Schema::table('useful_directory_contacts', function (Blueprint $table) {
                $table->unsignedBigInteger('directory_role_id')->nullable(false)->change();
                $table->dropIndex(['category', 'is_active', 'sort_order']);
                $table->dropColumn('category');
            });
        }

        if (! $this->indexExists('useful_directory_contacts', 'ud_contacts_role_active_sort_idx')) {
            Schema::table('useful_directory_contacts', function (Blueprint $table) {
                $table->index(['directory_role_id', 'is_active', 'sort_order'], 'ud_contacts_role_active_sort_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('useful_directory_contacts', 'directory_role_id')) {
            if ($this->indexExists('useful_directory_contacts', 'ud_contacts_role_active_sort_idx')) {
                Schema::table('useful_directory_contacts', function (Blueprint $table) {
                    $table->dropIndex('ud_contacts_role_active_sort_idx');
                });
            }
        }

        if (! Schema::hasColumn('useful_directory_contacts', 'category')) {
            Schema::table('useful_directory_contacts', function (Blueprint $table) {
                $table->string('category', 40)->default('services')->after('id');
            });
        }

        $roleSlugs = DB::table('useful_directory_roles')->pluck('slug', 'id');

        if (Schema::hasColumn('useful_directory_contacts', 'category')) {
            DB::table('useful_directory_contacts')
                ->orderBy('id')
                ->get(['id', 'directory_role_id'])
                ->each(function ($contact) use ($roleSlugs) {
                    DB::table('useful_directory_contacts')
                        ->where('id', $contact->id)
                        ->update(['category' => $roleSlugs->get($contact->directory_role_id, 'services')]);
                });
        }

        if (Schema::hasColumn('useful_directory_contacts', 'directory_role_id')) {
            Schema::table('useful_directory_contacts', function (Blueprint $table) {
                $table->dropConstrainedForeignId('directory_role_id');
                $table->index(['category', 'is_active', 'sort_order']);
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select('SHOW INDEX FROM `'.$table.'` WHERE Key_name = ?', [$indexName]);

        return $indexes !== [];
    }
};
