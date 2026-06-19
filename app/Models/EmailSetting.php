<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailSetting extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'emails_enabled',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'emails_enabled' => 'boolean',
        ];
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }
}
