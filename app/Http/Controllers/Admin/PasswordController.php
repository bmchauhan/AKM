<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Services\Auth\AuthService;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    public function edit(): View
    {
        return view('admin.password.edit');
    }

    public function update(UpdatePasswordRequest $request): RedirectResponse
    {
        $this->authService->updatePassword(
            auth()->user(),
            $request->validated('current_password'),
            $request->validated('password'),
        );

        Toast::success(__('messages.password_updated'));

        return back();
    }
}
