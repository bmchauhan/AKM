<?php

namespace App\Support;

final class SuperAdminOnlyModules
{
    public static function isSuperAdminOnlySlug(string $slug): bool
    {
        return in_array($slug, ['settings', 'landing_page'], true)
            || str_starts_with($slug, 'settings_')
            || str_starts_with($slug, 'landing_page_');
    }
}
