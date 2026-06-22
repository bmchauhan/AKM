<?php

namespace App\Models;

use App\Enums\HouseType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HouseUnit extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'house_type',
        'house_number',
        'current_ownership_id',
    ];

    protected function casts(): array
    {
        return [
            'house_type' => HouseType::class,
        ];
    }

    public function label(): string
    {
        return $this->house_type->value.' '.$this->house_number;
    }

    public function ownerships(): HasMany
    {
        return $this->hasMany(HouseOwnership::class)->orderByDesc('started_at');
    }

    public function currentOwnership(): BelongsTo
    {
        return $this->belongsTo(HouseOwnership::class, 'current_ownership_id');
    }

    public function activeOwnership(): HasOne
    {
        return $this->hasOne(HouseOwnership::class)->whereNull('ended_at')->latestOfMany('started_at');
    }

    public function financeCollections(): HasMany
    {
        return $this->hasMany(FinanceCollection::class);
    }

    public function maintenanceEntries(): HasMany
    {
        return $this->hasMany(MaintenanceMonthlyEntry::class);
    }

    public function visitorEntries(): HasMany
    {
        return $this->hasMany(VisitorEntry::class);
    }

    public function isVacant(): bool
    {
        return $this->current_ownership_id === null;
    }
}
