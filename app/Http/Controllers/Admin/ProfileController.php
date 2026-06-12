<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateProfileRequest;
use App\Services\Admin\ProfileService;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profile,
    ) {}

    public function show(): View
    {
        return view('admin.profile.show', [
            'user' => auth()->user(),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $this->profile->update(
            auth()->user(),
            $request->validated(),
            $request->file('id_proof'),
            $request->file('profile_image'),
        );

        Toast::success(__('messages.profile_updated'));

        return redirect()->route('admin.profile');
    }
}
