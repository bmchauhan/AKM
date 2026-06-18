<?php

namespace Database\Seeders;

use App\Enums\UsefulDirectorySource;
use App\Models\UsefulDirectoryContact;
use App\Models\UsefulDirectoryRole;
use App\Services\UsefulDirectoryContactSyncService;
use Illuminate\Database\Seeder;

class UsefulDirectorySeeder extends Seeder
{
    public function run(): void
    {
        $synced = app(UsefulDirectoryContactSyncService::class)->syncCommitteeMembers();

        $this->command?->info(sprintf('Synced %d committee member directory contact(s).', $synced));

        $this->seedManualContacts();

        $this->command?->info('Useful directory demo contacts seeded.');
    }

    private function seedManualContacts(): void
    {
        $roles = UsefulDirectoryRole::query()
            ->whereIn('slug', ['services', 'government', 'emergency'])
            ->get()
            ->keyBy('slug');

        $definitions = [
            'services' => [
                [
                    'title' => 'Electrician',
                    'contact_name' => 'Rajesh Patel',
                    'phone_primary' => '9876543210',
                    'phone_secondary' => '9876543211',
                    'sort_order' => 1,
                    'notes' => 'Available for society wiring and repairs.',
                ],
                [
                    'title' => 'Plumber',
                    'contact_name' => 'Mukesh Sharma',
                    'phone_primary' => '9876543220',
                    'phone_secondary' => null,
                    'sort_order' => 2,
                    'notes' => 'Pipeline and bathroom fitting.',
                ],
                [
                    'title' => 'Sweeper',
                    'contact_name' => 'Kamlesh Singh',
                    'phone_primary' => '9876543230',
                    'phone_secondary' => null,
                    'sort_order' => 3,
                    'notes' => 'Common area cleaning support.',
                ],
            ],
            'government' => [
                [
                    'title' => 'Talati',
                    'contact_name' => 'Gram Panchayat Office',
                    'phone_primary' => '0278-2567890',
                    'phone_secondary' => null,
                    'sort_order' => 1,
                    'notes' => 'Revenue and village records.',
                ],
                [
                    'title' => 'Sarpanch',
                    'contact_name' => 'Shri Kantibhai Rana',
                    'phone_primary' => '9876543240',
                    'phone_secondary' => '0278-2567891',
                    'sort_order' => 2,
                    'notes' => 'Gram Panchayat head.',
                ],
            ],
            'emergency' => [
                [
                    'title' => 'Ambulance',
                    'contact_name' => 'Emergency Ambulance',
                    'phone_primary' => '108',
                    'phone_secondary' => null,
                    'sort_order' => 1,
                    'notes' => null,
                ],
                [
                    'title' => 'Police',
                    'contact_name' => 'Police Control Room',
                    'phone_primary' => '100',
                    'phone_secondary' => '0278-2561000',
                    'sort_order' => 2,
                    'notes' => null,
                ],
                [
                    'title' => 'Fire Brigade',
                    'contact_name' => 'Fire Emergency',
                    'phone_primary' => '101',
                    'phone_secondary' => null,
                    'sort_order' => 3,
                    'notes' => null,
                ],
            ],
        ];

        foreach ($definitions as $slug => $contacts) {
            $role = $roles->get($slug);

            if (! $role) {
                continue;
            }

            foreach ($contacts as $contact) {
                UsefulDirectoryContact::query()->firstOrCreate(
                    [
                        'directory_role_id' => $role->id,
                        'title' => $contact['title'],
                        'contact_name' => $contact['contact_name'],
                    ],
                    [
                        'source' => UsefulDirectorySource::Manual,
                        'phone_primary' => $contact['phone_primary'],
                        'phone_secondary' => $contact['phone_secondary'],
                        'notes' => $contact['notes'],
                        'sort_order' => $contact['sort_order'],
                        'is_active' => true,
                    ],
                );
            }
        }
    }
}
