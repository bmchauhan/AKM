<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancePaymentReceiptLine extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'finance_payment_receipt_id',
        'maintenance_monthly_entry_id',
        'billing_month',
        'description',
        'charge_amount',
        'amount_applied',
        'line_status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'billing_month' => 'date',
            'charge_amount' => 'decimal:2',
            'amount_applied' => 'decimal:2',
        ];
    }

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(FinancePaymentReceipt::class, 'finance_payment_receipt_id');
    }

    public function maintenanceEntry(): BelongsTo
    {
        return $this->belongsTo(MaintenanceMonthlyEntry::class, 'maintenance_monthly_entry_id');
    }
}
