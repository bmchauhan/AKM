<?php

namespace App\Services\Admin;

use App\Models\User;

class AdminEmailSettingsService
{
    public function __construct(
        private readonly EmailSettingsService $settings,
        private readonly EmailLogService $logs,
    ) {}

    /**
     * @return array{
     *     setting: \App\Models\EmailSetting,
     *     logs: \Illuminate\Contracts\Pagination\LengthAwarePaginator
     * }
     */
    public function screenData(): array
    {
        return [
            'setting' => $this->settings->current(),
            'logs' => $this->logs->paginated(),
        ];
    }

    public function update(User $actor, bool $emailsEnabled): void
    {
        $this->settings->update($actor, $emailsEnabled);
    }
}
