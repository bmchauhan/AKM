<?php

namespace App\Services\Admin;

use App\Enums\MaintenanceChargeStatus;
use App\Models\MaintenanceChargeSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AdminFinanceMaintenanceChargeService
{
    public const SESSION_EDITING_CHARGE = 'admin.finance.editing_maintenance_charge_id';

    public function __construct(
        private readonly AdminFinanceFundSettingService $fundSettings,
    ) {}

    /**
     * @return array{
     *     charges: LengthAwarePaginator,
     *     currentCharge: ?array<string, mixed>
     * }
     */
    public function listForScreen(): array
    {
        $charges = MaintenanceChargeSetting::query()
            ->with('setBy')
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->paginate(20);

        $charges->getCollection()->transform(fn (MaintenanceChargeSetting $charge) => $this->mapListRow($charge));

        return [
            'charges' => $charges,
            'currentCharge' => $this->currentChargeForScreen(),
        ];
    }

    public function chargeOnDate(string|Carbon $onDate): ?MaintenanceChargeSetting
    {
        $date = $onDate instanceof Carbon ? $onDate->toDateString() : $onDate;

        return MaintenanceChargeSetting::query()
            ->where('status', MaintenanceChargeStatus::Active)
            ->whereDate('effective_from', '<=', $date)
            ->where(function ($query) use ($date): void {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $date);
            })
            ->orderByDesc('effective_from')
            ->first();
    }

    public function chargeLookup(string $receivedOn): ?array
    {
        $charge = $this->chargeOnDate($receivedOn);

        if (! $charge) {
            return null;
        }

        return [
            'maintenance_charge_setting_id' => $charge->id,
            'monthly_amount' => (float) $charge->monthly_amount,
            'formatted_amount' => $this->fundSettings->formatMoney($charge->monthly_amount),
            'effective_from' => $charge->effective_from->format('d M Y'),
            'end_date' => $charge->end_date?->format('d M Y'),
        ];
    }

    /**
     * @param  array{
     *     monthly_amount: float|int|string,
     *     effective_from: string,
     *     end_date?: ?string,
     *     notes?: ?string
     * }  $data
     */
    public function create(User $actor, array $data): MaintenanceChargeSetting
    {
        $effectiveFrom = Carbon::parse($data['effective_from'])->toDateString();

        $this->closePriorOpenRates($effectiveFrom);

        return MaintenanceChargeSetting::query()->create([
            'monthly_amount' => $data['monthly_amount'],
            'effective_from' => $effectiveFrom,
            'end_date' => $data['end_date'] ?? null,
            'status' => MaintenanceChargeStatus::Active,
            'notes' => $data['notes'] ?? null,
            'set_by_user_id' => $actor->id,
        ]);
    }

    /**
     * @param  array{
     *     status: string,
     *     end_date?: ?string,
     *     notes?: ?string
     * }  $data
     */
    public function update(MaintenanceChargeSetting $charge, array $data): MaintenanceChargeSetting
    {
        $charge->update([
            'status' => $data['status'],
            'end_date' => $data['end_date'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return $charge->fresh('setBy');
    }

    public function rememberEditingCharge(MaintenanceChargeSetting $charge): void
    {
        session([self::SESSION_EDITING_CHARGE => $charge->id]);
    }

    public function editingCharge(): ?MaintenanceChargeSetting
    {
        $chargeId = session(self::SESSION_EDITING_CHARGE);

        if (! $chargeId) {
            return null;
        }

        return MaintenanceChargeSetting::query()->with('setBy')->find((int) $chargeId);
    }

    public function clearEditingCharge(): void
    {
        session()->forget(self::SESSION_EDITING_CHARGE);
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function statusesForSelect(): array
    {
        return collect(MaintenanceChargeStatus::cases())
            ->map(fn (MaintenanceChargeStatus $status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ])
            ->all();
    }

    /**
     * @return ?array<string, mixed>
     */
    private function currentChargeForScreen(): ?array
    {
        $charge = $this->chargeOnDate(now());

        if (! $charge) {
            return null;
        }

        return [
            'formatted_amount' => $this->fundSettings->formatMoney($charge->monthly_amount),
            'effective_from' => $charge->effective_from->format('d M Y'),
            'end_date' => $charge->end_date?->format('d M Y'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapListRow(MaintenanceChargeSetting $charge): array
    {
        $status = $charge->status instanceof MaintenanceChargeStatus
            ? $charge->status
            : MaintenanceChargeStatus::from((string) $charge->status);

        return [
            'id' => $charge->id,
            'monthly_amount' => $this->fundSettings->formatMoney($charge->monthly_amount),
            'effective_from' => $charge->effective_from->format('d M Y'),
            'end_date' => $charge->end_date?->format('d M Y') ?? '—',
            'status' => $status->value,
            'status_label' => $status->label(),
            'is_active' => $status === MaintenanceChargeStatus::Active,
            'notes' => $charge->notes,
            'set_by' => $charge->setBy?->fullName() ?? '—',
        ];
    }

    private function closePriorOpenRates(string $effectiveFrom): void
    {
        $previousEndDate = Carbon::parse($effectiveFrom)->subDay()->toDateString();

        MaintenanceChargeSetting::query()
            ->where('status', MaintenanceChargeStatus::Active)
            ->whereNull('end_date')
            ->whereDate('effective_from', '<', $effectiveFrom)
            ->update(['end_date' => $previousEndDate]);
    }
}
