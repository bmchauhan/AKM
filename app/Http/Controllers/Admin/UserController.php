<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DestroyUserRequest;
use App\Http\Requests\Admin\HouseholdMembersRequest;
use App\Http\Requests\Admin\OpenUserEditRequest;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Services\Admin\AdminUserService;
use App\Support\Toast;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private readonly AdminUserService $users,
    ) {}

    public function index(Request $request): View
    {
        $list = $this->users->listForScreen(auth()->user(), [
            'house_type' => $request->string('house_type')->toString(),
            'house_number' => $request->string('house_number')->toString(),
            'name' => $request->string('name')->toString(),
            'role' => $request->string('role')->toString(),
        ]);

        return view('admin.users.index', [
            'users' => $list['users'],
            'filters' => $list['filters'],
            'roleOptions' => $list['roleOptions'],
        ]);
    }

    public function householdMembers(HouseholdMembersRequest $request): JsonResponse
    {
        $user = User::query()->findOrFail($request->integer('user_id'));
        $payload = $this->users->householdMembersForScreen($user);

        return response()->json($payload);
    }

    public function create(): View
    {
        $actor = auth()->user();

        return view('admin.users.create', [
            'membershipTypes' => $this->users->membershipTypesForSelect($actor),
            'committeeRoles' => $this->users->committeeRolesForSelect($actor),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->users->create(
            auth()->user(),
            $request->validated(),
            $request->file('id_proof'),
            $request->file('profile_image'),
        );

        Toast::success(__('messages.users_created'));

        return redirect()->route('admin.users.index');
    }

    public function openEdit(OpenUserEditRequest $request): RedirectResponse
    {
        $user = User::query()->findOrFail($request->integer('user_id'));

        $this->users->assertCanManage(auth()->user(), $user);
        $this->users->rememberEditingUser($user);

        return redirect()->route('admin.users.edit');
    }

    public function edit(): View|RedirectResponse
    {
        $user = $this->users->editingUser();

        if (! $user) {
            return redirect()->route('admin.users.index');
        }

        $this->users->assertCanManage(auth()->user(), $user);

        $actor = auth()->user();

        return view('admin.users.edit', [
            'user' => $user,
            'membershipTypes' => $this->users->membershipTypesForSelect($actor),
            'committeeRoles' => $this->users->committeeRolesForSelect($actor),
        ]);
    }

    public function update(UpdateUserRequest $request): RedirectResponse
    {
        $user = $this->users->editingUser();

        if (! $user) {
            return redirect()->route('admin.users.index');
        }

        $this->users->update(
            auth()->user(),
            $user,
            $request->validated(),
            $request->file('id_proof'),
            $request->file('profile_image'),
        );

        $this->users->clearEditingUser();

        Toast::success(__('messages.users_updated'));

        return redirect()->route('admin.users.index');
    }

    public function destroy(DestroyUserRequest $request): RedirectResponse
    {
        $user = User::query()->findOrFail($request->integer('user_id'));

        $this->users->delete($user, auth()->user());

        Toast::success(__('messages.users_deleted'));

        return redirect()->route('admin.users.index');
    }
}
