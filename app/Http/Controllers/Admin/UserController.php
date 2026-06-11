<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Services\Admin\AdminUserService;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private readonly AdminUserService $users,
    ) {}

    public function index(): View
    {
        return view('admin.users.index', [
            'users' => $this->users->listForScreen(),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'roles' => $this->users->rolesForSelect(auth()->user()),
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

    public function edit(User $user): View
    {
        $this->users->assertCanManage(auth()->user(), $user);

        return view('admin.users.edit', [
            'user' => $user,
            'roles' => $this->users->rolesForSelect(auth()->user()),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->users->update(
            auth()->user(),
            $user,
            $request->validated(),
            $request->file('id_proof'),
            $request->file('profile_image'),
        );

        Toast::success(__('messages.users_updated'));

        return redirect()->route('admin.users.index');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->users->delete($user, auth()->user());

        Toast::success(__('messages.users_deleted'));

        return redirect()->route('admin.users.index');
    }
}
