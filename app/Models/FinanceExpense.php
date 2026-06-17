<?php

namespace App\Models;

use App\Enums\FinanceExpenseTag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceExpense extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'expense_tag',
        'worker_id',
        'amount',
        'salary_base_amount',
        'salary_adjustment',
        'salary_adjustment_note',
        'paid_on',
        'payee_name',
        'reference',
        'notes',
        'recorded_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'expense_tag' => FinanceExpenseTag::class,
            'amount' => 'decimal:2',
            'salary_base_amount' => 'decimal:2',
            'salary_adjustment' => 'decimal:2',
            'paid_on' => 'date',
        ];
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }
}
