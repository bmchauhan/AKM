<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocietyFundSetting extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'opening_balance',
        'opening_balance_effective_date',
        'notes',
        'set_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'opening_balance_effective_date' => 'date',
        ];
    }

    public function setBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'set_by_user_id');
    }
}
