<?php

namespace App\Models;

use App\Enums\AdminModule;
use App\Enums\Gender;
use App\Enums\HouseType;
use App\Enums\MembershipRole;
use App\Enums\ModulePermissionAction;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Services\Admin\ModulePermissionService;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'middle_name',
        'last_name',
        'caste',
        'gender',
        'house_type',
        'house_number',
        'mobile_number',
        'alternate_number',
        'id_proof_path',
        'profile_image_path',
        'email',
        'username',
        'password',
        'role',
        'linked_main_member_id',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => 'string',
            'gender' => Gender::class,
            'house_type' => HouseType::class,
        ];
    }

    public function roleRecord(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role', 'slug');
    }

    public function mainMember(): BelongsTo
    {
        return $this->belongsTo(self::class, 'linked_main_member_id');
    }

    public function familyMembers(): HasMany
    {
        return $this->hasMany(self::class, 'linked_main_member_id');
    }

    public function roleLabel(): string
    {
        return $this->roleRecord?->name
            ?? ucwords(str_replace('_', ' ', (string) $this->role));
    }

    public function fullName(): string
    {
        return trim(collect([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        ])->filter()->implode(' ')) ?: (string) $this->name;
    }

    public function houseLabel(): ?string
    {
        if (! $this->house_type || ! $this->house_number) {
            return null;
        }

        return $this->house_type->value.' '.$this->house_number;
    }

    public function profileImageUrl(): ?string
    {
        return $this->profile_image_path
            ? Storage::disk('public')->url($this->profile_image_path)
            : null;
    }

    public function idProofUrl(): ?string
    {
        return $this->id_proof_path
            ? Storage::disk('public')->url($this->id_proof_path)
            : null;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin->value;
    }

    public function isChiefCommitteeMember(): bool
    {
        return $this->role === 'chief_committee_member';
    }

    public function isViceChiefCommitteeMember(): bool
    {
        return $this->role === 'vice_chief_committee_member';
    }

    public function hasCommitteeLeadership(): bool
    {
        return $this->isChiefCommitteeMember() || $this->isViceChiefCommitteeMember();
    }

    public function isMainMember(): bool
    {
        return $this->role === MembershipRole::MainMember->value;
    }

    public function isFamilyMember(): bool
    {
        return $this->role === MembershipRole::FamilyMember->value;
    }

    /**
     * Domain permission check — used by Gates and middleware. In Blade use @can('{module}.{action}').
     */
    public function canOnAdminModule(AdminModule|string $module, ModulePermissionAction|string $action = 'read'): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $moduleKey = $module instanceof AdminModule ? $module->value : $module;
        $permissionAction = $action instanceof ModulePermissionAction
            ? $action
            : ModulePermissionAction::from($action);

        return app(ModulePermissionService::class)->roleCanOnModule(
            (string) $this->role,
            $moduleKey,
            $permissionAction,
        );
    }

    public function canAccessAdminModule(AdminModule|string $module): bool
    {
        return $this->canOnAdminModule($module, ModulePermissionAction::Read);
    }

    /** @see Gate ability `users.read` */
    public function canAccessUsersModule(): bool
    {
        return $this->canOnAdminModule(AdminModule::Users, ModulePermissionAction::Read);
    }

    /** @see Gate ability `settings.read` */
    public function canAccessSettingsModule(): bool
    {
        return $this->canOnAdminModule(AdminModule::Settings, ModulePermissionAction::Read);
    }

    /** @see Gate ability `users.manage` */
    public function canManageUser(self $target, ModulePermissionAction|string $action = 'update'): bool
    {
        if ($target->isSuperAdmin() && ! $this->isSuperAdmin()) {
            return false;
        }

        return $this->canOnAdminModule(AdminModule::Users, $action);
    }

    /** @see Gate ability `members.manage` */
    public function canManageMember(self $target, ModulePermissionAction|string $action = 'update'): bool
    {
        if (! in_array($target->role, [
            MembershipRole::FamilyMember->value,
            MembershipRole::RentalMember->value,
        ], true)) {
            return false;
        }

        if (! $this->canOnAdminModule(AdminModule::Members, $action)) {
            return false;
        }

        if ($this->isMainMember()) {
            return (int) $target->linked_main_member_id === $this->id;
        }

        return true;
    }
}
