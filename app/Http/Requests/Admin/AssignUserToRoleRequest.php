<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use App\Services\Admin\CommitteeRoleRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignUserToRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin()
            && $this->user()?->can('settings_roles.update');
    }

    public function rules(): array
    {
        $actor = $this->user();
        $registry = app(CommitteeRoleRegistry::class);
        $allowed = $actor
            ? array_merge(
                [UserRole::SuperAdmin->value],
                $registry->committeeSlugs(),
            )
            : [];

        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'role_slug' => ['required', 'string', Rule::in($allowed)],
        ];
    }
}
