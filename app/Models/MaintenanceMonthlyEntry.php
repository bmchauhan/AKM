<?php

namespace App\Models;

use App\Enums\FinancePaymentMode;
use App\Enums\MaintenanceMonthEntryStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceMonthlyEntry extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'billing_month',
        'main_member_id',
        'house_unit_id',
        'maintenance_charge_setting_id',
        'charge_amount',
        'amount_paid',
        'status',
        'paid_on',
        'payment_mode',
        'reference',
        'notes',
        'recorded_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'billing_month' => 'date',
            'charge_amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'status' => MaintenanceMonthEntryStatus::class,
            'paid_on' => 'date',
            'payment_mode' => FinancePaymentMode::class,
        ];
    }

    public function mainMember(): BelongsTo
    {
        return $this->belongsTo(User::class, 'main_member_id');
    }

    public function houseUnit(): BelongsTo
    {
        return $this->belongsTo(HouseUnit::class);
    }

    public function maintenanceChargeSetting(): BelongsTo
    {
        return $this->belongsTo(MaintenanceChargeSetting::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }
}
