<?php

namespace App\Models;

use App\Enums\EmailLogStatus;
use App\Enums\EmailMailType;
use App\Enums\EmailSkipReason;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailLog extends Model
{
    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'mail_type',
        'recipient_email',
        'subject',
        'status',
        'skip_reason',
        'error_message',
        'subject_user_id',
        'recipient_user_id',
        'triggered_by_user_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'mail_type' => EmailMailType::class,
            'status' => EmailLogStatus::class,
            'skip_reason' => EmailSkipReason::class,
            'created_at' => 'datetime',
        ];
    }

    public function subjectUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subject_user_id');
    }

    public function recipientUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by_user_id');
    }
}
