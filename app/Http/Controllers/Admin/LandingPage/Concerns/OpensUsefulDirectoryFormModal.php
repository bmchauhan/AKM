<?php

namespace App\Http\Controllers\Admin\LandingPage\Concerns;

use Illuminate\Http\Request;

trait OpensUsefulDirectoryFormModal
{
    protected function shouldOpenUsefulDirectoryModal(Request $request, string $form): bool
    {
        if (old('_useful_directory_form') === $form && session()->has('errors')) {
            return true;
        }

        return $request->query('open') === $form;
    }
}
