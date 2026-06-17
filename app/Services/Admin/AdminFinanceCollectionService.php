<?php

namespace App\Services\Admin;

use App\Enums\FinanceCollectionType;
use App\Enums\FinancePaymentMode;
use App\Models\FinanceCollection;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class AdminFinanceCollectionService
{
    public const SESSION_EDITING_COLLECTION = 'admin.finance.editing_collection_id';

    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly AdminFinanceFundSettingService $fundSettings,
        private readonly AdminFinanceMaintenanceChargeService $maintenanceCharges,
    ) {}

    /**
     * @param  array{collection_type?: string, main_member_id?: string, date_from?: string, date_to?: string}  $filters
     * @return array{
     *     collections: LengthAwarePaginator,
     *     filters: array{collection_type: string, main_member_id: string, date_from: string, date_to: string},
     *     collectionTypes: list<array{value: string, label: string}>,
     *     mainMembers: list<array{value: int, label: string, house_type: ?string, house_number: ?string}>,
     *     paymentModes: list<array{value: string, label: string}>
     * }
     */
    public function listForScreen(array $filters = []): array
    {
        $normalized = $this->normalizeFilters($filters);

        $collections = $this->filteredQuery($normalized)
            ->with(['mainMember', 'recordedBy'])
            ->latest('received_on')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $collections->getCollection()->transform(fn (FinanceCollection $item) => $this->mapListRow($item));

        return [
            'collections' => $collections,
            'filters' => $normalized,
            'collectionTypes' => $this->collectionTypesForSelect(excludeMaintenance: true),
            'mainMembers' => $this->mainMembersForSelect(),
            'paymentModes' => $this->paymentModesForSelect(),
        ];
    }

    /**
     * @param  array{collection_type?: string, main_member_id?: string, date_from?: string, date_to?: string}  $filters
     */
    public function exportRows(array $filters = []): Collection
    {
        return $this->filteredQuery($this->normalizeFilters($filters))
            ->with(['mainMember', 'recordedBy'])
            ->latest('received_on')
            ->latest('id')
            ->get();
    }

    /**
     * @param  array{collection_type?: string, main_member_id?: string, date_from?: string, date_to?: string}  $filters
     */
    public function filteredQuery(array $filters): Builder
    {
        $query = FinanceCollection::query();

        if (filled($filters['collection_type'])) {
            $query->where('collection_type', $filters['collection_type']);
        }

        if (filled($filters['main_member_id'])) {
            $query->where('main_member_id', (int) $filters['main_member_id']);
        }

        if (filled($filters['date_from'])) {
            $query->whereDate('received_on', '>=', $filters['date_from']);
        }

        if (filled($filters['date_to'])) {
            $query->whereDate('received_on', '<=', $filters['date_to']);
        }

        return $query;
    }

    /**
     * @param  array{collection_type?: string, main_member_id?: string, date_from?: string, date_to?: string}  $filters
     * @return array{collection_type: string, main_member_id: string, date_from: string, date_to: string}
     */
    public function normalizeFilters(array $filters): array
    {
        return [
            'collection_type' => (string) ($filters['collection_type'] ?? ''),
            'main_member_id' => (string) ($filters['main_member_id'] ?? ''),
            'date_from' => (string) ($filters['date_from'] ?? ''),
            'date_to' => (string) ($filters['date_to'] ?? ''),
        ];
    }

    /**
     * @return array{
     *     collectionTypes: list<array{value: string, label: string}>,
     *     mainMembers: list<array{value: int, label: string, house_type: ?string, house_number: ?string}>,
     *     paymentModes: list<array{value: string, label: string}>,
     *     defaultCollectionType: string
     * }
     */
    public function createFormData(?string $defaultType = null): array
    {
        return [
            'collectionTypes' => $this->collectionTypesForSelect(excludeMaintenance: true),
            'mainMembers' => $this->mainMembersForSelect(),
            'paymentModes' => $this->paymentModesForSelect(),
            'defaultCollectionType' => $defaultType ?? FinanceCollectionType::ClubhouseBooking->value,
        ];
    }

    public function rememberEditingCollection(FinanceCollection $collection): void
    {
        session([self::SESSION_EDITING_COLLECTION => $collection->id]);
    }

    public function editingCollection(): ?FinanceCollection
    {
        $collectionId = session(self::SESSION_EDITING_COLLECTION);

        if (! $collectionId) {
            return null;
        }

        return FinanceCollection::query()
            ->with(['mainMember', 'recordedBy'])
            ->find((int) $collectionId);
    }

    public function clearEditingCollection(): void
    {
        session()->forget(self::SESSION_EDITING_COLLECTION);
    }

    /**
     * @param  array{
     *     collection_type: string,
     *     main_member_id?: ?int,
     *     amount: float|int|string,
     *     received_on: string,
     *     payment_mode?: ?string,
     *     reference?: ?string,
     *     notes?: ?string
     * }  $data
     */
    public function create(User $actor, array $data): FinanceCollection
    {
        if (($data['collection_type'] ?? '') === FinanceCollectionType::Maintenance->value) {
            throw ValidationException::withMessages([
                'collection_type' => [__('messages.finance_collection_maintenance_use_ledger')],
            ]);
        }

        return FinanceCollection::query()->create([
            ...$this->resolveCollectionPayload($data),
            'recorded_by_user_id' => $actor->id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(FinanceCollection $collection, array $data): FinanceCollection
    {
        $collection->update($this->resolveCollectionPayload($data));

        return $collection->fresh(['mainMember', 'recordedBy', 'maintenanceChargeSetting']);
    }

    public function delete(FinanceCollection $collection): void
    {
        $collection->delete();
    }

    /**
     * @return list<array{value: int, label: string, house_type: ?string, house_number: ?string}>
     */
    public function mainMembersForSelect(): array
    {
        return $this->users->mainMembersForSelect()
            ->map(fn (User $member) => [
                'value' => $member->id,
                'label' => $this->mainMemberSelectLabel($member),
                'house_type' => $member->house_type?->value,
                'house_number' => $member->house_number,
            ])
            ->all();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function collectionTypesForSelect(bool $excludeMaintenance = false): array
    {
        return collect(FinanceCollectionType::cases())
            ->when($excludeMaintenance, fn ($types) => $types->reject(
                fn (FinanceCollectionType $type) => $type === FinanceCollectionType::Maintenance,
            ))
            ->map(fn (FinanceCollectionType $type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function collectionTypesForEdit(?FinanceCollection $collection = null): array
    {
        $type = $collection?->collection_type;
        $isMaintenance = $type === FinanceCollectionType::Maintenance
            || $type?->value === FinanceCollectionType::Maintenance->value;

        return $this->collectionTypesForSelect(excludeMaintenance: ! $isMaintenance);
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function paymentModesForSelect(): array
    {
        return collect(FinancePaymentMode::cases())
            ->map(fn (FinancePaymentMode $mode) => [
                'value' => $mode->value,
                'label' => $mode->label(),
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function mapListRow(FinanceCollection $item): array
    {
        $type = $item->collection_type instanceof FinanceCollectionType
            ? $item->collection_type
            : FinanceCollectionType::from((string) $item->collection_type);

        return [
            'id' => $item->id,
            'collection_type' => $type->value,
            'collection_type_label' => $type->label(),
            'main_member_name' => $item->mainMember?->fullName(),
            'house' => $item->mainMember?->houseLabel(),
            'amount' => $this->fundSettings->formatMoney($item->amount),
            'maintenance_base' => $item->maintenance_base_amount !== null
                ? $this->fundSettings->formatMoney($item->maintenance_base_amount)
                : null,
            'received_on' => $item->received_on->format('d M Y'),
            'payment_mode' => $item->payment_mode?->label(),
            'reference' => $item->reference,
            'notes' => $item->notes,
            'recorded_by' => $item->recordedBy?->fullName() ?? '—',
        ];
    }

    private function mainMemberSelectLabel(User $member): string
    {
        $house = $member->houseLabel();

        if (! $house) {
            return $member->fullName();
        }

        return __('messages.finance_main_member_option', [
            'name' => $member->fullName(),
            'house' => $house,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function resolveCollectionPayload(array $data): array
    {
        $type = FinanceCollectionType::from($data['collection_type']);

        $shared = [
            'collection_type' => $data['collection_type'],
            'received_on' => $data['received_on'],
            'payment_mode' => $data['payment_mode'] ?? null,
            'reference' => $data['reference'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];

        if ($type !== FinanceCollectionType::Maintenance) {
            return [
                ...$shared,
                'main_member_id' => null,
                'maintenance_charge_setting_id' => null,
                'amount' => $data['amount'],
                'maintenance_base_amount' => null,
            ];
        }

        $charge = $this->maintenanceCharges->chargeOnDate($data['received_on']);

        if (! $charge) {
            throw ValidationException::withMessages([
                'received_on' => [__('messages.finance_maintenance_charge_not_found')],
            ]);
        }

        $base = round((float) $data['maintenance_base_amount'], 2);
        $amount = round((float) $data['amount'], 2);

        if (abs($base - (float) $charge->monthly_amount) > 0.01) {
            throw ValidationException::withMessages([
                'maintenance_base_amount' => [__('messages.finance_maintenance_charge_mismatch')],
            ]);
        }

        if ($amount < 0.01) {
            throw ValidationException::withMessages([
                'amount' => [__('messages.finance_collection_amount_too_low')],
            ]);
        }

        return [
            ...$shared,
            'main_member_id' => $data['main_member_id'],
            'maintenance_charge_setting_id' => $charge->id,
            'maintenance_base_amount' => $base,
            'amount' => $amount,
        ];
    }
}
