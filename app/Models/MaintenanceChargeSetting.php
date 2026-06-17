<?php

namespace App\Models;

use App\Enums\MaintenanceChargeStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceChargeSetting extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'monthly_amount',
        'effective_from',
        'end_date',
        'status',
        'notes',
        'set_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'monthly_amount' => 'decimal:2',
            'effective_from' => 'date',
            'end_date' => 'date',
            'status' => MaintenanceChargeStatus::class,
        ];
    }

    public function setBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'set_by_user_id');
    }

    public function isEffectiveOn(string $date): bool
    {
        if ($this->status !== MaintenanceChargeStatus::Active) {
            return false;
        }

        if ($this->effective_from->toDateString() > $date) {
            return false;
        }

        if ($this->end_date && $this->end_date->toDateString() < $date) {
            return false;
        }

        return true;
    }
}
