<?php

namespace App\Http\Controllers\Admin\Finance\Concerns;

use Illuminate\Http\Request;

trait OpensFinanceFormModal
{
    protected function shouldOpenFinanceModal(Request $request, string $form): bool
    {
        if (old('_finance_form') === $form && session()->has('errors')) {
            return true;
        }

        return $request->query('open') === $form;
    }
}
