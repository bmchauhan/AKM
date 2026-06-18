<?php

namespace App\Services\Admin;

use App\Models\SocietyFundSetting;
use App\Models\User;
use Illuminate\Support\Carbon;

class AdminFinanceFundSettingService
{
    /**
     * @return array{
     *     setting: ?SocietyFundSetting,
     *     is_configured: bool,
     *     formatted_opening_balance: string,
     *     formatted_effective_date: ?string
     * }
     */
    public function screenData(): array
    {
        $setting = $this->current();

        return [
            'setting' => $setting,
            'is_configured' => $setting !== null,
            'formatted_opening_balance' => $this->formatMoney($setting?->opening_balance ?? 0),
            'formatted_effective_date' => $setting?->opening_balance_effective_date?->format('d M Y'),
        ];
    }

    public function current(): ?SocietyFundSetting
    {
        return SocietyFundSetting::query()
            ->with('setBy')
            ->latest('id')
            ->first();
    }

    /**
     * @param  array{opening_balance: float|int|string, opening_balance_effective_date: string, notes?: ?string}  $data
     */
    public function save(User $actor, array $data): SocietyFundSetting
    {
        $setting = $this->current();

        $payload = [
            'opening_balance' => $data['opening_balance'],
            'opening_balance_effective_date' => $data['opening_balance_effective_date'],
            'notes' => $data['notes'] ?? null,
            'set_by_user_id' => $actor->id,
        ];

        if ($setting) {
            $setting->update($payload);

            return $setting->fresh(['setBy']);
        }

        return SocietyFundSetting::query()->create($payload)->load('setBy');
    }

    public function formatMoney(float|int|string|null $amount): string
    {
        return '₹'.number_format((float) $amount, 2);
    }

    public function formatCompactMoney(float|int|string|null $amount): string
    {
        $value = (float) $amount;
        $prefix = $value < 0 ? '-' : '';
        $abs = abs($value);

        if ($abs >= 10000000) {
            $crores = $abs / 10000000;

            return $prefix.'₹'.$this->formatCompactUnit($crores).'Cr';
        }

        if ($abs >= 100000) {
            $lakhs = $abs / 100000;

            return $prefix.'₹'.$this->formatCompactUnit($lakhs).'L';
        }

        if ($abs >= 1000) {
            $thousands = $abs / 1000;

            return $prefix.'₹'.$this->formatCompactUnit($thousands).'K';
        }

        return $prefix.'₹'.number_format($abs, 0);
    }

    private function formatCompactUnit(float $unit): string
    {
        $rounded = round($unit, 1);

        if (abs($rounded - round($rounded)) < 0.05) {
            return (string) (int) round($rounded);
        }

        return rtrim(rtrim(number_format($rounded, 1, '.', ''), '0'), '.');
    }

    public function defaultEffectiveDate(): string
    {
        return Carbon::today()->startOfYear()->toDateString();
    }
}
