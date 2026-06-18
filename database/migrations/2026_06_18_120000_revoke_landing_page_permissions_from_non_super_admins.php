<?php

use App\Services\Admin\ModulePermissionService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(ModulePermissionService::class)->revokeSuperAdminOnlyModulesFromNonSuperAdmins();
    }

    public function down(): void
    {
        // Permissions are re-seeded via SubModulePermissionSeeder on fresh installs.
    }
};
