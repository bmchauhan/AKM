<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignUserToRoleRequest;
use App\Http\Requests\Admin\SyncRolesRequest;
use App\Services\Admin\AdminUserService;
use App\Services\Admin\CommitteeRoleRegistry;
use App\Services\Admin\RoleService;
use App\Support\Toast;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function __construct(
        private readonly RoleService $roleService,
        private readonly AdminUserService $userService,
        private readonly CommitteeRoleRegistry $committeeRoles,
    ) {}

    public function index(): View|RedirectResponse
    {
        $actor = auth()->user();

        return view('admin.settings.roles', [
            'roles' => $this->roleService->listForScreen(),
            'usersForAssign' => $actor?->isSuperAdmin()
                ? $this->userService->usersForRoleAssignmentSelect()
                : [],
            'assignableRoleSlugs' => $actor?->isSuperAdmin()
                ? array_merge(
                    [UserRole::SuperAdmin->value],
                    $this->committeeRoles->committeeSlugs(),
                )
                : [],
            'permissionsUrl' => route('admin.settings.permissions.index'),
        ]);
    }

    public function sync(SyncRolesRequest $request): JsonResponse|RedirectResponse
    {
        $createdSlugs = $this->roleService->sync(
            $request->validated('roles'),
            $request->validated('deleted_ids') ?? [],
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('messages.roles_saved'),
                'roles' => $this->roleService->listForScreen(),
                'created_slugs' => $createdSlugs,
                'permissions_url' => route('admin.settings.permissions.index'),
                'created_hint' => $createdSlugs !== []
                    ? __('messages.roles_created_set_permissions')
                    : null,
            ]);
        }

        Toast::success(__('messages.roles_saved'));

        if ($createdSlugs !== []) {
            Toast::info(__('messages.roles_created_set_permissions'));
        }

        return redirect()->route('admin.settings.roles.index');
    }

    public function assignUser(AssignUserToRoleRequest $request): RedirectResponse
    {
        $user = \App\Models\User::query()->findOrFail($request->integer('user_id'));
        $roleSlug = $request->string('role_slug')->toString();

        if (! $this->userService->isAssignableRoleSlug($roleSlug)) {
            Toast::error(__('messages.users_role_not_assignable'));

            return redirect()->route('admin.settings.roles.index');
        }

        $this->userService->assignRole(auth()->user(), $user, $roleSlug);

        Toast::success(__('messages.users_role_assigned'));

        return redirect()->route('admin.settings.roles.index');
    }
}
