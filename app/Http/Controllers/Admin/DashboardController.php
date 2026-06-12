<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminDashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly AdminDashboardService $dashboard,
    ) {}

    public function index(): View
    {
        $data = $this->dashboard->screenData(auth()->user());

        return view('admin.dashboard', [
            'societyStats' => $data['society_stats'],
            'householdStats' => $data['household_stats'],
            'residentCard' => $data['resident_card'],
        ]);
    }
}
