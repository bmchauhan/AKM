<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SyncRolesRequest;
use App\Services\Admin\RoleService;
use App\Support\Toast;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function __construct(
        private readonly RoleService $roleService,
    ) {}

    public function index(): View|RedirectResponse
    {
        return view('admin.settings.roles', [
            'roles' => $this->roleService->listForScreen(),
        ]);
    }

    public function sync(SyncRolesRequest $request): JsonResponse|RedirectResponse
    {
        $this->roleService->sync(
            $request->validated('roles'),
            $request->validated('deleted_ids') ?? [],
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('messages.roles_saved'),
                'roles' => $this->roleService->listForScreen(),
            ]);
        }

        Toast::success(__('messages.roles_saved'));

        return redirect()->route('admin.settings.roles.index');
    }
}
