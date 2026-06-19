<?php

namespace App\Services\Admin;

use App\Models\EmailSetting;
use App\Models\User;

class EmailSettingsService
{
    public function current(): EmailSetting
    {
        return EmailSetting::query()
            ->with('updatedBy')
            ->orderBy('id')
            ->firstOrFail();
    }

    public function emailsEnabled(): bool
    {
        return (bool) $this->current()->emails_enabled;
    }

    public function update(User $actor, bool $emailsEnabled): EmailSetting
    {
        $setting = $this->current();
        $setting->update([
            'emails_enabled' => $emailsEnabled,
            'updated_by_user_id' => $actor->id,
        ]);

        return $setting->fresh(['updatedBy']);
    }
}
