<?php

namespace App\Http\Requests\Admin;

use App\Services\Admin\AdminUserService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $actor = $this->user();
        $userId = $this->integer('user_id') ?: session(AdminUserService::SESSION_ASSIGNING_USER);

        if (! $userId) {
            return false;
        }

        $target = \App\Models\User::query()->find($userId);

        return $actor && $target && app(AdminUserService::class)->canAssignRoleTo($actor, $target);
    }

    public function rules(): array
    {
        $allowed = collect(app(AdminUserService::class)->assignableRolesForSelect($this->user()))
            ->pluck('value')
            ->all();

        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'assigned_role' => ['required', 'string', Rule::in($allowed)],
        ];
    }
}
