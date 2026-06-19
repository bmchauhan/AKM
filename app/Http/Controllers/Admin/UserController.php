<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MembershipRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignUserRoleRequest;
use App\Http\Requests\Admin\DestroyUserRequest;
use App\Http\Requests\Admin\HouseholdMembersRequest;
use App\Http\Requests\Admin\OpenAssignUserRoleRequest;
use App\Http\Requests\Admin\OpenUserEditRequest;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Services\Admin\AdminMemberService;
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
        private readonly AdminMemberService $members,
    ) {}

    public function index(Request $request): View
    {
        $actor = auth()->user();
        $list = $this->users->listForScreen($actor, [
            'house_type' => $request->string('house_type')->toString(),
            'house_number' => $request->string('house_number')->toString(),
            'name' => $request->string('name')->toString(),
            'role' => $request->string('role')->toString(),
        ]);

        $assigningUser = $this->users->assigningUser();
        $shouldOpenAssignModal = $request->query('open') === 'assign-role'
            || (session()->has('errors') && old('user_id') !== null);

        if ($assigningUser && ! $shouldOpenAssignModal) {
            $this->users->clearAssigningUser();
            $assigningUser = null;
        }

        return view('admin.users.index', [
            'users' => $list['users'],
            'filters' => $list['filters'],
            'roleOptions' => $list['roleOptions'],
            'assignableRoles' => $this->users->canAssignRoles($actor)
                ? $this->users->assignableRolesForSelect($actor, $assigningUser)
                : [],
            'assigningUser' => $assigningUser,
            'openAssignRoleModal' => $assigningUser !== null && $shouldOpenAssignModal,
            'currentAssignRole' => $assigningUser
                ? $this->users->currentRoleAssignmentKey($assigningUser)
                : '',
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
        $result = $this->users->create(
            auth()->user(),
            $request->validated(),
            $request->file('id_proof'),
            $request->file('profile_image'),
        );

        if ($result->credentialsEmailed) {
            Toast::success(__('messages.users_created_with_email'));
        } elseif ($result->credentialsSkippedDueToDisabled) {
            Toast::success(__('messages.users_created_emails_disabled'));
        } elseif (filled($result->user->email)) {
            Toast::warning(__('messages.users_created_email_failed'));
        } else {
            Toast::success(__('messages.users_created_no_email'));
        }

        return redirect()->route('admin.users.index');
    }

    public function openEdit(OpenUserEditRequest $request): RedirectResponse
    {
        $user = User::query()->findOrFail($request->integer('user_id'));

        if ($this->isHouseholdMember($user)) {
            $this->users->clearEditingUser();
            $this->members->assertCanManage(auth()->user(), $user);
            $this->members->rememberEditingMember($user);

            return redirect()->route('admin.members.edit');
        }

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

        if ($this->isHouseholdMember($user)) {
            $this->users->clearEditingUser();
            $this->members->rememberEditingMember($user);

            return redirect()->route('admin.members.edit');
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

    public function openAssignRole(OpenAssignUserRoleRequest $request): RedirectResponse
    {
        $user = User::query()->findOrFail($request->integer('user_id'));
        $this->users->rememberAssigningUser($user);

        return redirect()->route('admin.users.index', ['open' => 'assign-role']);
    }

    public function cancelAssignRole(): RedirectResponse
    {
        $this->users->clearAssigningUser();

        return redirect()->route('admin.users.index');
    }

    public function assignRole(AssignUserRoleRequest $request): RedirectResponse
    {
        $user = User::query()->findOrFail($request->integer('user_id'));

        $this->users->assignRole(
            auth()->user(),
            $user,
            $request->string('assigned_role')->toString(),
        );

        $this->users->clearAssigningUser();

        Toast::success(__('messages.users_role_assigned'));

        return redirect()->route('admin.users.index');
    }

    private function isHouseholdMember(User $user): bool
    {
        return in_array($user->membership_type, [
            MembershipRole::FamilyMember->value,
            MembershipRole::RentalMember->value,
        ], true);
    }
}
