<?php

namespace App\Models;

use App\Enums\MembershipRole;
use App\Enums\VisitorEntryStatus;
use App\Models\Concerns\UsesSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class VisitorEntry extends Model
{
    use UsesSoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'house_unit_id',
        'host_user_id',
        'host_type',
        'visitor_name',
        'visitor_contact',
        'party_size',
        'male_count',
        'female_count',
        'children_count',
        'photo_path',
        'id_proof_path',
        'vehicle_number',
        'purpose',
        'notes',
        'entry_at',
        'exit_at',
        'status',
        'logged_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'entry_at' => 'datetime',
            'exit_at' => 'datetime',
            'status' => VisitorEntryStatus::class,
            'party_size' => 'integer',
            'male_count' => 'integer',
            'female_count' => 'integer',
            'children_count' => 'integer',
        ];
    }

    public function houseUnit(): BelongsTo
    {
        return $this->belongsTo(HouseUnit::class);
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    public function loggedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by_user_id');
    }

    public function isRentalVisit(): bool
    {
        return $this->host_type === MembershipRole::RentalMember->value;
    }

    public function photoUrl(): ?string
    {
        return $this->photo_path
            ? Storage::disk('public')->url($this->photo_path)
            : null;
    }

    public function idProofUrl(): ?string
    {
        return $this->id_proof_path
            ? Storage::disk('public')->url($this->id_proof_path)
            : null;
    }
}
