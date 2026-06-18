<?php

namespace App\Http\Requests\Admin\LandingPage;

use App\Services\Admin\AdminUsefulDirectoryService;

class UpdateUsefulDirectoryContactRequest extends UsefulDirectoryContactRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('landing_page_useful_directory.update') ?? false;
    }

    protected function editingContactId(): ?int
    {
        return app(AdminUsefulDirectoryService::class)->editingContact()?->id;
    }
}
