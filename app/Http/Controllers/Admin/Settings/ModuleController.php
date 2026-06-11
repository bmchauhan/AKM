<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Services\Admin\ModuleService;
use Illuminate\View\View;

class ModuleController extends Controller
{
    public function __construct(
        private readonly ModuleService $modules,
    ) {}

    public function index(): View
    {
        return view('admin.settings.modules', [
            'modules' => $this->modules->listForScreen(),
        ]);
    }
}
