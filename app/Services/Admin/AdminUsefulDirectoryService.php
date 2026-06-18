<?php

namespace App\Services\Admin;

use App\Enums\RoleType;
use App\Enums\UsefulDirectorySource;
use App\Enums\UserRole;
use App\Models\UsefulDirectoryContact;
use App\Models\UsefulDirectoryRole;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminUsefulDirectoryService
{
    public const SESSION_EDITING_CONTACT = 'admin.landing_page.useful_directory.editing_contact_id';

    public function __construct(
        private readonly AdminUsefulDirectoryRoleService $directoryRoles,
    ) {}

    /**
     * @return array{
     *     contacts: LengthAwarePaginator,
     *     activeTab: string,
     *     activeRole: ?UsefulDirectoryRole,
     *     categories: list<array{id: int, slug: string, label: string, supports_committee_link: bool}>,
     *     committeeMemberOptions: list<array{id: int, name: string, position: string, phone: ?string}>,
     *     sourceOptions: list<array{value: string, label: string}>,
     *     committeeRoleId: ?int
     * }
     */
    public function listForScreen(string $tab): array
    {
        $activeTab = $this->directoryRoles->resolveTabSlug($tab);
        $activeRole = $this->directoryRoles->findActiveBySlug($activeTab);
        $committeeRole = $this->directoryRoles->committeeLinkRole();

        $contacts = UsefulDirectoryContact::query()
            ->with(['user.committeeRoleRecord', 'directoryRole'])
            ->when($activeRole, fn ($query) => $query->where('directory_role_id', $activeRole->id))
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate(20)
            ->withQueryString();

        $contacts->getCollection()->transform(fn (UsefulDirectoryContact $contact) => $this->mapListRow($contact));

        return [
            'contacts' => $contacts,
            'activeTab' => $activeTab,
            'activeRole' => $activeRole,
            'categories' => $this->directoryRoles->activeTabOptions(),
            'committeeMemberOptions' => $this->committeeMemberOptions(),
            'sourceOptions' => $this->sourceOptions(),
            'committeeRoleId' => $committeeRole?->id,
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function sourceOptions(): array
    {
        return [
            ['value' => UsefulDirectorySource::Manual->value, 'label' => __('messages.useful_directory_source_manual')],
            ['value' => UsefulDirectorySource::CommitteeMember->value, 'label' => __('messages.useful_directory_source_committee')],
        ];
    }

    /**
     * @return list<array{id: int, name: string, position: string, phone: ?string}>
     */
    public function committeeMemberOptions(?int $exceptContactId = null, ?int $includeUserId = null): array
    {
        $linkedUserIds = UsefulDirectoryContact::query()
            ->where('source', UsefulDirectorySource::CommitteeMember)
            ->when($exceptContactId, fn ($query) => $query->where('id', '!=', $exceptContactId))
            ->whereNotNull('user_id')
            ->pluck('user_id');

        return User::query()
            ->whereNotNull('committee_role')
            ->where('role', '!=', UserRole::SuperAdmin->value)
            ->whereHas('committeeRoleRecord', fn ($query) => $query->where('role_type', RoleType::Committee->value))
            ->with('committeeRoleRecord')
            ->orderBy('first_name')
            ->get()
            ->reject(fn (User $user) => $linkedUserIds->contains($user->id) && $user->id !== $includeUserId)
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->fullName(),
                'position' => $user->committeeRoleRecord?->name ?? __('messages.committee_member_generic'),
                'phone' => $user->mobile_number ?: $user->alternate_number,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): UsefulDirectoryContact
    {
        return UsefulDirectoryContact::query()->create($this->normalizePayload($data));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(UsefulDirectoryContact $contact, array $data): UsefulDirectoryContact
    {
        $contact->update($this->normalizePayload($data, $contact));

        return $contact->fresh(['user.committeeRoleRecord', 'directoryRole']);
    }

    public function delete(UsefulDirectoryContact $contact): void
    {
        $contact->delete();
    }

    public function rememberEditingContact(UsefulDirectoryContact $contact): void
    {
        session([self::SESSION_EDITING_CONTACT => $contact->id]);
    }

    public function clearEditingContact(): void
    {
        session()->forget(self::SESSION_EDITING_CONTACT);
    }

    public function editingContact(): ?UsefulDirectoryContact
    {
        $id = session(self::SESSION_EDITING_CONTACT);

        if (! $id) {
            return null;
        }

        return UsefulDirectoryContact::query()
            ->with(['user.committeeRoleRecord', 'directoryRole'])
            ->find($id);
    }

    /**
     * @return array<string, mixed>
     */
    public function mapListRow(UsefulDirectoryContact $contact): array
    {
        return [
            'id' => $contact->id,
            'role_slug' => $contact->directoryRole?->slug,
            'category_label' => $contact->directoryRole?->localizedName() ?? '—',
            'source' => $contact->source->value,
            'source_label' => $contact->source === UsefulDirectorySource::CommitteeMember
                ? __('messages.useful_directory_source_committee')
                : __('messages.useful_directory_source_manual'),
            'title' => $contact->displayPosition(),
            'contact_name' => $contact->displayContactName(),
            'phone_primary' => $contact->displayPhonePrimary() ?? '—',
            'phone_secondary' => $contact->displayPhoneSecondary(),
            'notes' => $contact->notes,
            'sort_order' => $contact->sort_order,
            'is_active' => $contact->is_active,
            'is_committee_link' => $contact->isCommitteeMemberLink(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizePayload(array $data, ?UsefulDirectoryContact $existing = null): array
    {
        $source = UsefulDirectorySource::from($data['source']);
        $role = UsefulDirectoryRole::query()->findOrFail((int) $data['directory_role_id']);

        if ($source === UsefulDirectorySource::CommitteeMember) {
            if (! $role->supports_committee_link) {
                $role = $this->directoryRoles->committeeLinkRole()
                    ?? throw new \InvalidArgumentException('No committee directory role configured.');
            }

            $user = User::query()
                ->with('committeeRoleRecord')
                ->whereNotNull('committee_role')
                ->findOrFail((int) $data['user_id']);

            return [
                'directory_role_id' => $role->id,
                'source' => $source->value,
                'user_id' => $user->id,
                'title' => $data['title'] ?: ($user->committeeRoleRecord?->name ?? __('messages.committee_member_generic')),
                'contact_name' => null,
                'phone_primary' => null,
                'phone_secondary' => null,
                'notes' => $data['notes'] ?? null,
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'is_active' => filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            ];
        }

        return [
            'directory_role_id' => $role->id,
            'source' => $source->value,
            'user_id' => null,
            'title' => $data['title'],
            'contact_name' => $data['contact_name'],
            'phone_primary' => $data['phone_primary'] ?? null,
            'phone_secondary' => $data['phone_secondary'] ?? null,
            'notes' => $data['notes'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ];
    }
}
