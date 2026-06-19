<?php

namespace App\Models;

use App\Models\Concerns\UsesSoftDeletes;
use App\Enums\FinanceCollectionType;
use App\Enums\FinancePaymentMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceCollection extends Model
{
    use UsesSoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'collection_type',
        'main_member_id',
        'maintenance_charge_setting_id',
        'amount',
        'maintenance_base_amount',
        'received_on',
        'payment_mode',
        'reference',
        'notes',
        'recorded_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'collection_type' => FinanceCollectionType::class,
            'payment_mode' => FinancePaymentMode::class,
            'amount' => 'decimal:2',
            'maintenance_base_amount' => 'decimal:2',
            'received_on' => 'date',
        ];
    }

    public function mainMember(): BelongsTo
    {
        return $this->belongsTo(User::class, 'main_member_id');
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
