<?php

namespace App\Http\Controllers\Admin\LandingPage;

use App\Http\Controllers\Admin\LandingPage\Concerns\OpensUsefulDirectoryFormModal;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LandingPage\DestroyUsefulDirectoryContactRequest;
use App\Http\Requests\Admin\LandingPage\OpenEditUsefulDirectoryContactRequest;
use App\Http\Requests\Admin\LandingPage\StoreUsefulDirectoryContactRequest;
use App\Http\Requests\Admin\LandingPage\UpdateUsefulDirectoryContactRequest;
use App\Services\UsefulDirectoryContactSyncService;
use App\Services\Admin\AdminUsefulDirectoryService;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UsefulDirectoryController extends Controller
{
    use OpensUsefulDirectoryFormModal;

    public function __construct(
        private readonly AdminUsefulDirectoryService $directory,
    ) {}

    public function index(Request $request): View
    {
        app(UsefulDirectoryContactSyncService::class)->syncCommitteeMembers();

        $editingContact = $this->directory->editingContact();

        return view('admin.landing-page.useful-directory.index', [
            ...$this->directory->listForScreen($request->string('tab')->toString()),
            'openAddModal' => $this->shouldOpenUsefulDirectoryModal($request, 'add'),
            'openEditModal' => $editingContact !== null || $this->shouldOpenUsefulDirectoryModal($request, 'edit'),
            'editingContact' => $editingContact,
            'editingCommitteeOptions' => $editingContact
                ? $this->directory->committeeMemberOptions($editingContact->id, $editingContact->user_id)
                : [],
        ]);
    }

    public function store(StoreUsefulDirectoryContactRequest $request): RedirectResponse
    {
        $contact = $this->directory->create($request->validated())->load('directoryRole');

        Toast::success(__('messages.useful_directory_created'));

        return redirect()->route('admin.landing-page.useful-directory.index', [
            'tab' => $contact->directoryRole?->slug,
        ]);
    }

    public function openEdit(OpenEditUsefulDirectoryContactRequest $request): RedirectResponse
    {
        $contact = UsefulDirectoryContact::query()->with('directoryRole')->findOrFail($request->integer('contact_id'));
        $this->directory->rememberEditingContact($contact);

        return redirect()->route('admin.landing-page.useful-directory.index', [
            'tab' => $contact->directoryRole?->slug,
        ]);
    }

    public function cancelEdit(): RedirectResponse
    {
        $contact = $this->directory->editingContact();
        $tab = $contact?->directoryRole?->slug;
        $this->directory->clearEditingContact();

        return redirect()->route('admin.landing-page.useful-directory.index', array_filter(['tab' => $tab]));
    }

    public function update(UpdateUsefulDirectoryContactRequest $request): RedirectResponse
    {
        $contact = $this->directory->editingContact();

        if (! $contact) {
            return redirect()->route('admin.landing-page.useful-directory.index');
        }

        $contact = $this->directory->update($contact, $request->validated());
        $this->directory->clearEditingContact();

        Toast::success(__('messages.useful_directory_updated'));

        return redirect()->route('admin.landing-page.useful-directory.index', [
            'tab' => $contact->directoryRole?->slug,
        ]);
    }

    public function destroy(DestroyUsefulDirectoryContactRequest $request): RedirectResponse
    {
        $contact = UsefulDirectoryContact::query()->with('directoryRole')->findOrFail($request->integer('contact_id'));
        $tab = $contact->directoryRole?->slug;

        $this->directory->delete($contact);

        if ((int) session(AdminUsefulDirectoryService::SESSION_EDITING_CONTACT) === $contact->id) {
            $this->directory->clearEditingContact();
        }

        Toast::success(__('messages.useful_directory_deleted'));

        return redirect()->route('admin.landing-page.useful-directory.index', array_filter(['tab' => $tab]));
    }
}
