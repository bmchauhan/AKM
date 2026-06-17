<?php

namespace App\Http\Controllers\Admin\Workers\Concerns;

use Illuminate\Http\Request;

trait OpensWorkerFormModal
{
    protected function shouldOpenWorkerModal(Request $request, string $form): bool
    {
        if (old('_worker_form') === $form && session()->has('errors')) {
            return true;
        }

        return $request->query('open') === $form;
    }
}
