<?php

namespace App\Http\Requests\Admin;

use App\Services\Admin\AdminUserService;
use Illuminate\Foundation\Http\FormRequest;

class OpenAssignUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $actor = $this->user();
        $target = \App\Models\User::query()->find($this->integer('user_id'));

        return $actor
            && $target
            && app(AdminUserService::class)->canAssignRoleTo($actor, $target);
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
