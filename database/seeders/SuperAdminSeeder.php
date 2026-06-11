<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Services\Auth\UserRegistrationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Validator;

class SuperAdminSeeder extends Seeder
{
    public function run(UserRegistrationService $registrationService): void
    {
        if (\App\Models\User::query()->where('email', 'sa@gmail.com')->exists()) {
            return;
        }

        $data = [
            'name' => 'Super Admin',
            'email' => 'sa@gmail.com',
            'username' => 'admin',
            'password' => 'admin@123',
            'password_confirmation' => 'admin@123',
            'role' => UserRole::SuperAdmin->value,
        ];

        $validator = Validator::make($data, (new RegisterUserRequest)->rules());

        if ($validator->fails()) {
            return;
        }

        $registrationService->register($validator->validated());
    }
}
