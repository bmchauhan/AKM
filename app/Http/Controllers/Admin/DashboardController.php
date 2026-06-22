<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminDashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly AdminDashboardService $dashboard,
    ) {}

    public function index(): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user?->isSecurityGuard()) {
            return redirect()->route('admin.visitors.log');
        }

        return view('admin.dashboard', $this->dashboard->screenData($user));
    }
}
