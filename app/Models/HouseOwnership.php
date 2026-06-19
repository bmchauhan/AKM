<?php

namespace App\Models;

use App\Enums\HouseTransferType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HouseOwnership extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'house_unit_id',
        'main_member_user_id',
        'started_at',
        'ended_at',
        'transfer_type',
        'notes',
        'transferred_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'date',
            'ended_at' => 'date',
            'transfer_type' => HouseTransferType::class,
        ];
    }

    public function houseUnit(): BelongsTo
    {
        return $this->belongsTo(HouseUnit::class);
    }

    public function mainMember(): BelongsTo
    {
        return $this->belongsTo(User::class, 'main_member_user_id');
    }

    public function transferredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferred_by_user_id');
    }

    public function isActive(): bool
    {
        return $this->ended_at === null;
    }
}
