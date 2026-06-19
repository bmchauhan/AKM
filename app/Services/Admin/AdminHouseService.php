<?php

namespace App\Services\Admin;

use App\Models\FinanceCollection;
use App\Models\HouseOwnership;
use App\Models\HouseUnit;
use App\Models\MaintenanceMonthlyEntry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AdminHouseService
{
    /**
     * @param  array{house_type?: string, search?: string, status?: string}  $filters
     */
    public function paginatedList(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $status = (string) ($filters['status'] ?? '');

        return HouseUnit::query()
            ->with(['currentOwnership.mainMember'])
            ->when(filled($filters['house_type'] ?? null), fn ($query) => $query->where('house_type', $filters['house_type']))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('house_number', 'like', '%'.$search.'%')
                        ->orWhereHas('currentOwnership.mainMember', function ($memberQuery) use ($search) {
                            $term = '%'.$search.'%';
                            $memberQuery->where('first_name', 'like', $term)
                                ->orWhere('middle_name', 'like', $term)
                                ->orWhere('last_name', 'like', $term)
                                ->orWhere('name', 'like', $term);
                        });
                });
            })
            ->when($status === 'vacant', fn ($query) => $query->whereNull('current_ownership_id'))
            ->when($status === 'occupied', fn ($query) => $query->whereNotNull('current_ownership_id'))
            ->orderBy('house_type')
            ->orderBy('house_number')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return array{
     *     house: HouseUnit,
     *     ownershipTimeline: Collection<int, array<string, mixed>>,
     *     financeByOwner: Collection<int, array<string, mixed>>
     * }
     */
    public function detail(HouseUnit $house): array
    {
        $house->load([
            'currentOwnership.mainMember',
            'ownerships.mainMember',
            'ownerships.transferredBy',
        ]);

        $ownershipTimeline = $house->ownerships->map(function (HouseOwnership $ownership) {
            return [
                'id' => $ownership->id,
                'owner_name' => $ownership->mainMember?->fullName() ?? '—',
                'owner_id' => $ownership->main_member_user_id,
                'started_at' => $ownership->started_at?->format('d M Y'),
                'ended_at' => $ownership->ended_at?->format('d M Y'),
                'is_active' => $ownership->isActive(),
                'transfer_type' => $ownership->transfer_type?->label(),
                'notes' => $ownership->notes,
                'transferred_by' => $ownership->transferredBy?->fullName(),
            ];
        });

        $financeByOwner = $house->ownerships->map(function (HouseOwnership $ownership) use ($house) {
            $memberId = $ownership->main_member_user_id;

            $collectionsTotal = FinanceCollection::query()
                ->where('house_unit_id', $house->id)
                ->where('main_member_id', $memberId)
                ->sum('amount');

            $maintenancePaid = MaintenanceMonthlyEntry::query()
                ->where('house_unit_id', $house->id)
                ->where('main_member_id', $memberId)
                ->sum('amount_paid');

            return [
                'owner_name' => $ownership->mainMember?->fullName() ?? '—',
                'period' => $ownership->started_at?->format('M Y').' — '.($ownership->ended_at?->format('M Y') ?? __('messages.houses_present')),
                'collections_total' => number_format((float) $collectionsTotal, 2),
                'maintenance_paid' => number_format((float) $maintenancePaid, 2),
                'is_active' => $ownership->isActive(),
            ];
        });

        return [
            'house' => $house,
            'ownershipTimeline' => $ownershipTimeline,
            'financeByOwner' => $financeByOwner,
        ];
    }

    /**
     * @param  array{house_type?: string, search?: string}  $filters
     */
    public function paginatedTransferHistory(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));

        $paginator = $this->transferHistoryQuery($filters, $search)
            ->paginate($perPage)
            ->withQueryString();

        return $paginator->setCollection(
            $paginator->getCollection()->map(fn (HouseOwnership $record) => $this->mapTransferLogEntry($record)),
        );
    }

    /**
     * @param  array{house_type?: string}  $filters
     */
    private function transferHistoryQuery(array $filters, string $search): Builder
    {
        return HouseOwnership::query()
            ->with(['houseUnit', 'mainMember', 'transferredBy'])
            ->whereNotNull('transferred_by_user_id')
            ->where(function (Builder $query): void {
                $query->whereNotNull('ended_at')
                    ->orWhere(function (Builder $inner): void {
                        $inner->whereNull('ended_at')
                            ->whereRaw(
                                'NOT EXISTS (
                                    SELECT 1 FROM house_ownerships AS prior
                                    WHERE prior.house_unit_id = house_ownerships.house_unit_id
                                      AND prior.id <> house_ownerships.id
                                      AND prior.ended_at IS NOT NULL
                                )',
                            );
                    });
            })
            ->when(filled($filters['house_type'] ?? null), function (Builder $query) use ($filters): void {
                $query->whereHas('houseUnit', fn (Builder $houseQuery) => $houseQuery->where('house_type', $filters['house_type']));
            })
            ->when($search !== '', function (Builder $query) use ($search): void {
                $term = '%'.$search.'%';

                $query->where(function (Builder $inner) use ($term): void {
                    $inner->whereHas('houseUnit', fn (Builder $houseQuery) => $houseQuery->where('house_number', 'like', $term))
                        ->orWhereHas('mainMember', function (Builder $memberQuery) use ($term): void {
                            $memberQuery->where('first_name', 'like', $term)
                                ->orWhere('middle_name', 'like', $term)
                                ->orWhere('last_name', 'like', $term)
                                ->orWhere('name', 'like', $term);
                        })
                        ->orWhereHas('transferredBy', function (Builder $actorQuery) use ($term): void {
                            $actorQuery->where('first_name', 'like', $term)
                                ->orWhere('last_name', 'like', $term)
                                ->orWhere('name', 'like', $term);
                        });
                });
            })
            ->orderByRaw('COALESCE(ended_at, started_at) DESC')
            ->orderByDesc('id');
    }

    /**
     * @return array<string, mixed>
     */
    private function mapTransferLogEntry(HouseOwnership $record): array
    {
        $successor = null;

        if ($record->ended_at !== null) {
            $successor = HouseOwnership::query()
                ->where('house_unit_id', $record->house_unit_id)
                ->where('id', '>', $record->id)
                ->whereDate('started_at', $record->ended_at)
                ->orderBy('id')
                ->with('mainMember')
                ->first();
        }

        $isAssignment = $record->ended_at === null;

        return [
            'id' => $record->id,
            'effective_date' => ($record->ended_at ?? $record->started_at)?->format('d M Y'),
            'house_label' => $record->houseUnit?->label() ?? '—',
            'house_id' => $record->house_unit_id,
            'from_owner' => $isAssignment
                ? __('messages.houses_transfer_from_vacant')
                : ($record->mainMember?->fullName() ?? '—'),
            'to_owner' => $isAssignment
                ? ($record->mainMember?->fullName() ?? '—')
                : ($successor?->mainMember?->fullName() ?? '—'),
            'transfer_type' => $record->transfer_type?->label() ?? '—',
            'transferred_by' => $record->transferredBy?->fullName() ?? '—',
            'notes' => $record->notes,
            'is_assignment' => $isAssignment,
        ];
    }
}
