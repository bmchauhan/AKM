<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\UpdateEmailSettingsRequest;
use App\Services\Admin\AdminEmailSettingsService;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmailSettingsController extends Controller
{
    public function __construct(
        private readonly AdminEmailSettingsService $emailSettings,
    ) {}

    public function index(): View
    {
        return view('admin.settings.email', $this->emailSettings->screenData());
    }

    public function update(UpdateEmailSettingsRequest $request): RedirectResponse
    {
        $this->emailSettings->update(
            $request->user(),
            $request->boolean('emails_enabled'),
        );

        Toast::success(__('messages.email_settings_saved'));

        return redirect()->route('admin.settings.email.index');
    }
}
