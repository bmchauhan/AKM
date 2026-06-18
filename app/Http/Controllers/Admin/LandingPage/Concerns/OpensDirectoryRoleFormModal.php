<?php

namespace App\Http\Controllers\Admin\LandingPage\Concerns;

use Illuminate\Http\Request;

trait OpensDirectoryRoleFormModal
{
    protected function shouldOpenDirectoryRoleModal(Request $request, string $form): bool
    {
        if (old('_directory_role_form') === $form && session()->has('errors')) {
            return true;
        }

        return $request->query('open') === $form;
    }
}
