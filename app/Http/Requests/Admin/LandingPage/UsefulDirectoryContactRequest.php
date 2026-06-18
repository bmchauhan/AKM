<?php

namespace App\Http\Requests\Admin\LandingPage;

use App\Enums\UsefulDirectorySource;
use App\Models\UsefulDirectoryContact;
use App\Models\UsefulDirectoryRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

abstract class UsefulDirectoryContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $source = $this->input('source', UsefulDirectorySource::Manual->value);

        $rules = [
            'directory_role_id' => ['required', 'integer', Rule::exists('useful_directory_roles', 'id')->where('is_active', true)],
            'source' => ['required', 'string', Rule::enum(UsefulDirectorySource::class)],
            'title' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ];

        if ($source === UsefulDirectorySource::CommitteeMember->value) {
            $rules['user_id'] = [
                'required',
                'integer',
                Rule::exists('users', 'id')->whereNotNull('committee_role'),
                Rule::unique('useful_directory_contacts', 'user_id')->ignore($this->editingContactId()),
            ];
        } else {
            $rules['title'] = ['required', 'string', 'max:255'];
            $rules['contact_name'] = ['required', 'string', 'max:255'];
            $rules['phone_primary'] = ['required', 'string', 'max:20'];
            $rules['phone_secondary'] = ['nullable', 'string', 'max:20'];
            $rules['user_id'] = ['nullable'];
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $roleId = (int) $this->input('directory_role_id');
            $role = UsefulDirectoryRole::query()->find($roleId);
            $source = $this->input('source');

            if (! $role) {
                return;
            }

            if ($source === UsefulDirectorySource::Manual->value && $role->supports_committee_link) {
                $validator->errors()->add(
                    'source',
                    __('messages.useful_directory_committee_requires_link'),
                );
            }

            if ($source === UsefulDirectorySource::CommitteeMember->value && ! $role->supports_committee_link) {
                $validator->errors()->add(
                    'directory_role_id',
                    __('messages.useful_directory_committee_role_required'),
                );
            }
        });
    }

    protected function editingContactId(): ?int
    {
        return null;
    }
}
