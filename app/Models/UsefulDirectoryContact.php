<?php

namespace App\Models;

use App\Enums\UsefulDirectorySource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsefulDirectoryContact extends Model
{
    protected $fillable = [
        'directory_role_id',
        'source',
        'user_id',
        'title',
        'contact_name',
        'phone_primary',
        'phone_secondary',
        'notes',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'source' => UsefulDirectorySource::class,
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function directoryRole(): BelongsTo
    {
        return $this->belongsTo(UsefulDirectoryRole::class, 'directory_role_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isCommitteeMemberLink(): bool
    {
        return $this->source === UsefulDirectorySource::CommitteeMember && $this->user_id !== null;
    }

    public function displayContactName(): string
    {
        if ($this->isCommitteeMemberLink() && $this->relationLoaded('user') && $this->user) {
            return $this->user->fullName();
        }

        return (string) ($this->contact_name ?? '');
    }

    public function displayPhonePrimary(): ?string
    {
        if ($this->isCommitteeMemberLink() && $this->relationLoaded('user') && $this->user) {
            return $this->user->mobile_number ?: $this->user->alternate_number;
        }

        return $this->phone_primary;
    }

    public function displayPhoneSecondary(): ?string
    {
        if ($this->isCommitteeMemberLink()) {
            return null;
        }

        return $this->phone_secondary;
    }

    public function displayPosition(): string
    {
        if ($this->isCommitteeMemberLink() && $this->relationLoaded('user') && $this->user) {
            return $this->user->committeeRoleRecord?->name ?? $this->title;
        }

        return $this->title;
    }
}
