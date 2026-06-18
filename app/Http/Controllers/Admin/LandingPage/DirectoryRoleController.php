<?php

namespace App\Http\Controllers\Admin\LandingPage;

use App\Http\Controllers\Admin\LandingPage\Concerns\OpensDirectoryRoleFormModal;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LandingPage\DestroyDirectoryRoleRequest;
use App\Http\Requests\Admin\LandingPage\OpenEditDirectoryRoleRequest;
use App\Http\Requests\Admin\LandingPage\StoreDirectoryRoleRequest;
use App\Http\Requests\Admin\LandingPage\UpdateDirectoryRoleRequest;
use App\Models\UsefulDirectoryRole;
use App\Services\Admin\AdminUsefulDirectoryRoleService;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DirectoryRoleController extends Controller
{
    use OpensDirectoryRoleFormModal;

    public function __construct(
        private readonly AdminUsefulDirectoryRoleService $roles,
    ) {}

    public function index(Request $request): View
    {
        $editingRole = $this->roles->editingRole();

        return view('admin.landing-page.directory-roles.index', [
            ...$this->roles->listForScreen(),
            'openAddModal' => $this->shouldOpenDirectoryRoleModal($request, 'add'),
            'openEditModal' => $editingRole !== null || $this->shouldOpenDirectoryRoleModal($request, 'edit'),
            'editingRole' => $editingRole,
        ]);
    }

    public function store(StoreDirectoryRoleRequest $request): RedirectResponse
    {
        $this->roles->create($request->validated());

        Toast::success(__('messages.directory_roles_created'));

        return redirect()->route('admin.landing-page.directory-roles.index');
    }

    public function openEdit(OpenEditDirectoryRoleRequest $request): RedirectResponse
    {
        $role = UsefulDirectoryRole::query()->findOrFail($request->integer('role_id'));
        $this->roles->rememberEditingRole($role);

        return redirect()->route('admin.landing-page.directory-roles.index');
    }

    public function cancelEdit(): RedirectResponse
    {
        $this->roles->clearEditingRole();

        return redirect()->route('admin.landing-page.directory-roles.index');
    }

    public function update(UpdateDirectoryRoleRequest $request): RedirectResponse
    {
        $role = $this->roles->editingRole();

        if (! $role) {
            return redirect()->route('admin.landing-page.directory-roles.index');
        }

        $this->roles->update($role, $request->validated());
        $this->roles->clearEditingRole();

        Toast::success(__('messages.directory_roles_updated'));

        return redirect()->route('admin.landing-page.directory-roles.index');
    }

    public function destroy(DestroyDirectoryRoleRequest $request): RedirectResponse
    {
        $role = UsefulDirectoryRole::query()->findOrFail($request->integer('role_id'));

        $this->roles->delete($role);

        if ((int) session(AdminUsefulDirectoryRoleService::SESSION_EDITING_ROLE) === $role->id) {
            $this->roles->clearEditingRole();
        }

        Toast::success(__('messages.directory_roles_deleted'));

        return redirect()->route('admin.landing-page.directory-roles.index');
    }
}
