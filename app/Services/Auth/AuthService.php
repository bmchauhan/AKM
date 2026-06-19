<?php

namespace App\Services\Auth;

use App\Enums\OwnershipStatus;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    public function attemptLogin(string $login, string $password, bool $remember = false): void
    {
        $user = $this->users->findByEmailOrUsername($login);

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => [__('messages.auth_failed')],
            ]);
        }

        if ($user->ownership_status === OwnershipStatus::FormerOwner) {
            throw ValidationException::withMessages([
                'login' => [__('messages.auth_former_owner_blocked')],
            ]);
        }

        Auth::login($user, $remember);
    }

    public function logout(): void
    {
        Auth::logout();
    }

    public function updatePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (! Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => [__('messages.password_current_invalid')],
            ]);
        }

        $user->update([
            'password' => $newPassword,
        ]);
    }
}
