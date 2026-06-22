<?php

namespace App\Models;

use App\Enums\FinancePaymentMode;
use App\Enums\FinancePaymentReceiptKind;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancePaymentReceipt extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'receipt_number',
        'receipt_kind',
        'main_member_id',
        'worker_id',
        'finance_collection_id',
        'finance_expense_id',
        'paid_on',
        'payment_mode',
        'reference',
        'notes',
        'total_amount',
        'recorded_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'receipt_kind' => FinancePaymentReceiptKind::class,
            'paid_on' => 'date',
            'payment_mode' => FinancePaymentMode::class,
            'total_amount' => 'decimal:2',
        ];
    }

    public function mainMember(): BelongsTo
    {
        return $this->belongsTo(User::class, 'main_member_id');
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function financeCollection(): BelongsTo
    {
        return $this->belongsTo(FinanceCollection::class);
    }

    public function financeExpense(): BelongsTo
    {
        return $this->belongsTo(FinanceExpense::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(FinancePaymentReceiptLine::class)->orderBy('sort_order');
    }

    public function downloadFilename(): string
    {
        return str_replace('/', '-', $this->receipt_number).'.pdf';
    }
}
