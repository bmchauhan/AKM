<?php

namespace App\Models;

use App\Models\Concerns\UsesSoftDeletes;
use App\Enums\RoleType;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use UsesSoftDeletes;

    protected $fillable = [
        'name',
        'short_form',
        'slug',
        'description',
        'is_system',
        'role_type',
        'is_leadership',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'is_leadership' => 'boolean',
            'role_type' => RoleType::class,
        ];
    }

    public function isSuperAdminRole(): bool
    {
        return $this->role_type === RoleType::SuperAdmin
            || $this->slug === UserRole::SuperAdmin->value;
    }

    public function isCommitteeRole(): bool
    {
        return $this->role_type === RoleType::Committee;
    }

    /**
     * @param  Builder<Role>  $query
     * @return Builder<Role>
     */
    public function scopeCommittee(Builder $query): Builder
    {
        return $query->where('role_type', RoleType::Committee->value);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role', 'slug');
    }

    public function committeeUsers(): HasMany
    {
        return $this->hasMany(User::class, 'committee_role', 'slug');
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class)
            ->withPivot(['can_create', 'can_read', 'can_update', 'can_delete'])
            ->withTimestamps();
    }
}
