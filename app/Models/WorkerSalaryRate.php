<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerSalaryRate extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'worker_id',
        'monthly_salary',
        'effective_from',
        'notes',
        'set_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'monthly_salary' => 'decimal:2',
            'effective_from' => 'date',
        ];
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function setBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'set_by_user_id');
    }
}
