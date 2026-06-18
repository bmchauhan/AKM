<?php

namespace App\Services\Frontend;

use App\Models\UsefulDirectoryContact;
use App\Models\UsefulDirectoryRole;
use App\Services\UsefulDirectoryContactSyncService;

class FrontendUsefulDirectoryService
{
    public function __construct(
        private readonly UsefulDirectoryContactSyncService $sync,
    ) {}

    /**
     * @return list<array{
     *     slug: string,
     *     label: string,
     *     contacts: list<array{title: string, name: string, phone: ?string, phone_secondary: ?string}>
     * }>
     */
    public function publicDirectory(): array
    {
        $this->sync->syncCommitteeMembers();

        return UsefulDirectoryRole::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function (UsefulDirectoryRole $role) {
                $contacts = UsefulDirectoryContact::query()
                    ->with(['user.committeeRoleRecord'])
                    ->where('directory_role_id', $role->id)
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('title')
                    ->get()
                    ->map(fn (UsefulDirectoryContact $contact) => $this->toPublicContact($contact))
                    ->values()
                    ->all();

                return [
                    'slug' => $role->slug,
                    'label' => $role->localizedName(),
                    'contacts' => $contacts,
                ];
            })
            ->filter(fn (array $group) => $group['contacts'] !== [])
            ->values()
            ->all();
    }

    /**
     * @return array{title: string, name: string, phone: ?string, phone_secondary: ?string}
     */
    private function toPublicContact(UsefulDirectoryContact $contact): array
    {
        return [
            'title' => $contact->displayPosition(),
            'name' => $contact->displayContactName(),
            'phone' => $contact->displayPhonePrimary(),
            'phone_secondary' => $contact->displayPhoneSecondary(),
        ];
    }
}
