<?php

namespace App\Services\Admin;

use App\Enums\Gender;
use App\Enums\HouseType;
use App\Enums\WorkerType;
use App\Models\User;
use App\Models\Worker;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Auth\UserAccountProvisioningService;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WorkerLoginService
{
    public const SESSION_CREDENTIALS = 'admin.workers.created_login_credentials';

    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly UserAccountProvisioningService $provisioning,
    ) {}

    /**
     * @return array{user: User, username: string, plain_password: string}
     */
    public function createLoginProfile(Worker $worker, User $actor): array
    {
        if ($worker->user_id) {
            throw ValidationException::withMessages([
                'worker_id' => [__('messages.workers_login_already_exists')],
            ]);
        }

        if ($worker->worker_type !== WorkerType::SecurityGuard) {
            throw ValidationException::withMessages([
                'worker_id' => [__('messages.workers_login_security_only')],
            ]);
        }

        if (! $worker->is_active) {
            throw ValidationException::withMessages([
                'worker_id' => [__('messages.workers_login_inactive')],
            ]);
        }

        [$firstName, $lastName] = $this->splitName($worker->name);
        $plainPassword = $this->provisioning->generatePassword();
        $username = $this->generateUsername($worker, $firstName);

        $user = $this->users->create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'middle_name' => null,
            'name' => trim($worker->name),
            'caste' => null,
            'gender' => Gender::Other->value,
            'house_type' => HouseType::A->value,
            'house_number' => '0',
            'mobile_number' => $worker->mobile_number,
            'alternate_number' => null,
            'email' => null,
            'username' => $username,
            'password' => $plainPassword,
            'membership_type' => null,
            'committee_role' => 'security_guard',
            'role' => User::syncLegacyRole(null, 'security_guard'),
            'profile_image_path' => $worker->profile_image_path,
        ]);

        $worker->update(['user_id' => $user->id]);

        return [
            'user' => $user,
            'username' => $username,
            'plain_password' => $plainPassword,
        ];
    }

    public function rememberCredentials(Worker $worker, string $username, string $plainPassword): void
    {
        session([
            self::SESSION_CREDENTIALS => [
                'worker_id' => $worker->id,
                'worker_name' => $worker->name,
                'username' => $username,
                'password' => $plainPassword,
            ],
        ]);
    }

    /**
     * @return array{worker_id: int, worker_name: string, username: string, password: string}|null
     */
    public function pullFlashedCredentials(): ?array
    {
        $payload = session(self::SESSION_CREDENTIALS);

        session()->forget(self::SESSION_CREDENTIALS);

        return is_array($payload) ? $payload : null;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), 2) ?: [];

        $firstName = $parts[0] ?? 'Worker';
        $lastName = $parts[1] ?? 'Staff';

        return [$firstName, $lastName];
    }

    private function generateUsername(Worker $worker, string $firstName): string
    {
        $typeCode = match ($worker->worker_type) {
            WorkerType::SecurityGuard => 'sg',
            WorkerType::Sweeper => 'sw',
            WorkerType::GarbageCollector => 'gc',
            WorkerType::Gardener => 'gd',
        };

        $namePart = Str::lower(preg_replace('/[^a-z0-9]/', '', $firstName) ?: 'worker');
        $namePart = substr($namePart, 0, 8);
        $base = $typeCode.$namePart.$worker->id;
        $candidate = $base;
        $suffix = 1;

        while ($this->users->usernameExists($candidate)) {
            $candidate = $base.$suffix;
            $suffix++;
        }

        return $candidate;
    }
}
