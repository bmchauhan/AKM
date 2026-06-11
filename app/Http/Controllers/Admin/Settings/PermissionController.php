<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SyncModulePermissionsRequest;
use App\Services\Admin\ModulePermissionService;
use App\Support\Toast;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function __construct(
        private readonly ModulePermissionService $permissions,
    ) {}

    public function index(): View
    {
        return view('admin.settings.permissions', $this->permissions->screenData());
    }

    public function sync(SyncModulePermissionsRequest $request): JsonResponse|RedirectResponse
    {
        $this->permissions->syncForRole(
            (int) $request->validated('role_id'),
            $request->validated('permissions'),
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('messages.permissions_saved'),
                ...$this->permissions->screenData(),
            ]);
        }

        Toast::success(__('messages.permissions_saved'));

        return redirect()->route('admin.settings.permissions.index');
    }
}
