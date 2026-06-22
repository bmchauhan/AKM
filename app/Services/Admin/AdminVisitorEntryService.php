<?php

namespace App\Services\Admin;

use App\Enums\MembershipRole;
use App\Enums\VisitorEntryStatus;
use App\Models\HouseUnit;
use App\Models\User;
use App\Models\VisitorEntry;
use App\Traits\HandlesUploads;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class AdminVisitorEntryService
{
    use HandlesUploads;

    public function __construct(
        private readonly VisitorEntryMailService $visitorMail,
    ) {}

    /**
     * @param  array{
     *     house_type?: string,
     *     search?: string,
     *     host_type?: string,
     *     status?: string,
     *     rental_only?: bool,
     *     females_only?: bool,
     *     date_from?: string,
     *     date_to?: string,
     * }  $filters
     */
    public function paginatedList(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));

        return VisitorEntry::query()
            ->with(['houseUnit', 'host', 'loggedBy'])
            ->when(filled($filters['house_type'] ?? null), function ($query) use ($filters) {
                $query->whereHas('houseUnit', fn ($houseQuery) => $houseQuery->where('house_type', $filters['house_type']));
            })
            ->when($search !== '', function ($query) use ($search) {
                $term = '%'.$search.'%';
                $query->where(function ($inner) use ($term, $search) {
                    $inner->where('visitor_name', 'like', $term)
                        ->orWhere('visitor_contact', 'like', $term)
                        ->orWhere('vehicle_number', 'like', $term)
                        ->orWhereHas('houseUnit', fn ($houseQuery) => $houseQuery->where('house_number', 'like', $term))
                        ->orWhereHas('host', function ($hostQuery) use ($term) {
                            $hostQuery->where('first_name', 'like', $term)
                                ->orWhere('last_name', 'like', $term)
                                ->orWhere('name', 'like', $term);
                        });
                });
            })
            ->when(filled($filters['host_type'] ?? null), fn ($query) => $query->where('host_type', $filters['host_type']))
            ->when(filled($filters['status'] ?? null), fn ($query) => $query->where('status', $filters['status']))
            ->when(filter_var($filters['rental_only'] ?? false, FILTER_VALIDATE_BOOL), function ($query) {
                $query->where('host_type', MembershipRole::RentalMember->value);
            })
            ->when(filter_var($filters['females_only'] ?? false, FILTER_VALIDATE_BOOL), fn ($query) => $query->where('female_count', '>', 0))
            ->when(filled($filters['date_from'] ?? null), fn ($query) => $query->whereDate('entry_at', '>=', $filters['date_from']))
            ->when(filled($filters['date_to'] ?? null), fn ($query) => $query->whereDate('entry_at', '<=', $filters['date_to']))
            ->orderByDesc('entry_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return Collection<int, array{id: int, label: string}>
     */
    public function occupiedHousesForSelect(): Collection
    {
        return HouseUnit::query()
            ->with('currentOwnership.mainMember')
            ->whereNotNull('current_ownership_id')
            ->orderBy('house_type')
            ->orderBy('house_number')
            ->get()
            ->map(fn (HouseUnit $house) => [
                'id' => $house->id,
                'label' => $house->label(),
            ]);
    }

    /**
     * @return Collection<int, array{
     *     id: int,
     *     name: string,
     *     membership_type: string,
     *     membership_label: string,
     *     is_rental: bool
     * }>
     */
    public function hostsForHouse(HouseUnit $house): Collection
    {
        $mainMember = $house->currentOwnership?->mainMember;

        if (! $mainMember) {
            return collect();
        }

        $household = User::query()
            ->where('linked_main_member_id', $mainMember->id)
            ->whereIn('membership_type', [
                MembershipRole::FamilyMember->value,
                MembershipRole::RentalMember->value,
            ])
            ->orderBy('membership_type')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return collect([$mainMember])
            ->concat($household)
            ->map(function (User $user) {
                $role = MembershipRole::from($user->membership_type);

                return [
                    'id' => $user->id,
                    'name' => $user->fullName(),
                    'membership_type' => $role->value,
                    'membership_label' => $this->translatedHostType($role),
                    'is_rental' => $user->isRentalMember(),
                ];
            });
    }

    /**
     * @return LengthAwarePaginator<int, VisitorEntry>
     */
    public function todaysActive(int $perPage = 20): LengthAwarePaginator
    {
        return VisitorEntry::query()
            ->with(['houseUnit', 'host'])
            ->where('status', VisitorEntryStatus::Active->value)
            ->whereDate('entry_at', Carbon::today())
            ->orderByDesc('entry_at')
            ->paginate($perPage);
    }

    /**
     * @param  array{
     *     house_unit_id: int,
     *     host_user_id: int,
     *     visitor_name: string,
     *     visitor_contact: string,
     *     party_size: int,
     *     male_count?: int,
     *     female_count?: int,
     *     children_count?: int,
     *     vehicle_number?: ?string,
     *     purpose?: ?string,
     *     notes?: ?string,
     * }  $data
     */
    public function create(
        User $actor,
        array $data,
        UploadedFile $idProof,
        ?UploadedFile $photo = null,
    ): VisitorEntry {
        $house = HouseUnit::query()->findOrFail($data['house_unit_id']);
        $host = User::query()->findOrFail($data['host_user_id']);

        $this->assertHostBelongsToHouse($house, $host);

        $entry = VisitorEntry::query()->create([
            'house_unit_id' => $house->id,
            'host_user_id' => $host->id,
            'host_type' => $host->membership_type,
            'visitor_name' => $data['visitor_name'],
            'visitor_contact' => $data['visitor_contact'],
            'party_size' => $data['party_size'],
            'male_count' => $data['male_count'] ?? 0,
            'female_count' => $data['female_count'] ?? 0,
            'children_count' => $data['children_count'] ?? 0,
            'photo_path' => $photo
                ? $this->storePublicUpload($photo, 'visitors/photos')
                : null,
            'id_proof_path' => $this->storePublicUpload($idProof, 'visitors/id-proofs'),
            'vehicle_number' => $data['vehicle_number'] ?? null,
            'purpose' => $data['purpose'] ?? null,
            'notes' => $data['notes'] ?? null,
            'entry_at' => now(),
            'status' => VisitorEntryStatus::Active,
            'logged_by_user_id' => $actor->id,
        ]);

        if ($host->isRentalMember()) {
            $this->visitorMail->notifyMainMemberOfRentalVisit($entry, $actor);
        }

        return $entry;
    }

    public function checkout(VisitorEntry $entry): VisitorEntry
    {
        if ($entry->status !== VisitorEntryStatus::Active) {
            throw ValidationException::withMessages([
                'visitor' => [__('messages.visitors_checkout_not_active')],
            ]);
        }

        $entry->update([
            'exit_at' => now(),
            'status' => VisitorEntryStatus::Exited,
        ]);

        return $entry->fresh(['houseUnit', 'host', 'loggedBy']);
    }

    public function delete(VisitorEntry $entry): void
    {
        $this->deletePublicUpload($entry->photo_path);
        $this->deletePublicUpload($entry->id_proof_path);
        $entry->delete();
    }

    private function assertHostBelongsToHouse(HouseUnit $house, User $host): void
    {
        $mainMember = $house->currentOwnership?->mainMember;

        if (! $mainMember) {
            throw ValidationException::withMessages([
                'house_unit_id' => [__('messages.visitors_house_vacant')],
            ]);
        }

        $valid = $host->id === $mainMember->id
            || ($host->linked_main_member_id === $mainMember->id && in_array($host->membership_type, [
                MembershipRole::FamilyMember->value,
                MembershipRole::RentalMember->value,
            ], true));

        if (! $valid) {
            throw ValidationException::withMessages([
                'host_user_id' => [__('messages.visitors_host_invalid')],
            ]);
        }
    }

    private function translatedHostType(MembershipRole $role): string
    {
        return match ($role) {
            MembershipRole::MainMember => __('messages.visitors_host_main'),
            MembershipRole::FamilyMember => __('messages.visitors_host_family'),
            MembershipRole::RentalMember => __('messages.visitors_host_rental'),
        };
    }
}
