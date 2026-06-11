<?php

namespace App\Services\Auth;

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

        Auth::login($user, $remember);
    }

    public function logout(): void
    {
        Auth::logout();
    }
}
