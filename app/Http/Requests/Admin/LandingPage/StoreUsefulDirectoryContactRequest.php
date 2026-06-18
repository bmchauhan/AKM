<?php

namespace App\Http\Requests\Admin\LandingPage;

class StoreUsefulDirectoryContactRequest extends UsefulDirectoryContactRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('landing_page_useful_directory.create') ?? false;
    }
}
