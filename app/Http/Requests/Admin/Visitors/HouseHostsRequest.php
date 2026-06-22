<?php

namespace App\Http\Requests\Admin\Visitors;

use Illuminate\Foundation\Http\FormRequest;

class HouseHostsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('visitors_log.read') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'house_unit_id' => ['required', 'integer', 'exists:house_units,id'],
        ];
    }
}
