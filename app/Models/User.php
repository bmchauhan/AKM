<?php

namespace App\Models;

use App\Models\Concerns\UsesSoftDeletes;
use App\Enums\AdminModule;
use App\Enums\Gender;
use App\Enums\HouseType;
use App\Enums\MembershipRole;
use App\Enums\ModulePermissionAction;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Services\Admin\ModulePermissionService;
use App\Support\SuperAdminOnlyModules;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, UsesSoftDeletes;

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
        'membership_type',
        'committee_role',
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

    public function committeeRoleRecord(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'committee_role', 'slug');
    }

    public function mainMember(): BelongsTo
    {
        return $this->belongsTo(self::class, 'linked_main_member_id');
    }

    public function familyMembers(): HasMany
    {
        return $this->hasMany(self::class, 'linked_main_member_id');
    }

    public function householdMembers(): HasMany
    {
        return $this->hasMany(self::class, 'linked_main_member_id')
            ->whereIn('membership_type', [
                MembershipRole::FamilyMember->value,
                MembershipRole::RentalMember->value,
            ]);
    }

    public function roleLabel(): string
    {
        if ($this->isSuperAdmin()) {
            return $this->roleRecord?->name ?? 'Super Admin';
        }

        $parts = [];

        if (filled($this->membership_type)) {
            $parts[] = MembershipRole::from($this->membership_type)->shortForm();
        }

        if (filled($this->committee_role)) {
            $parts[] = $this->committeeRoleRecord?->short_form
                ?? strtoupper(substr(str_replace('_', '', $this->committee_role), 0, 4));
        }

        return $parts !== [] ? implode(' · ', $parts) : '—';
    }

    public function roleLabelWithShortForm(): string
    {
        if ($this->isSuperAdmin()) {
            $name = $this->roleRecord?->name ?? 'Super Admin';
            $short = $this->roleRecord?->short_form ?? 'SA';

            return $name.' ('.$short.')';
        }

        $parts = [];

        if (filled($this->membership_type)) {
            $membership = MembershipRole::from($this->membership_type);
            $parts[] = $membership->label().' ('.$membership->shortForm().')';
        }

        if (filled($this->committee_role)) {
            $name = $this->committeeRoleRecord?->name
                ?? ucwords(str_replace('_', ' ', $this->committee_role));
            $short = $this->committeeRoleRecord?->short_form
                ?? strtoupper(substr(str_replace('_', '', $this->committee_role), 0, 4));
            $parts[] = $name.' ('.$short.')';
        }

        return $parts !== [] ? implode(' · ', $parts) : '—';
    }

    public static function syncLegacyRole(?string $membershipType, ?string $committeeRole, bool $isSuperAdmin = false): string
    {
        if ($isSuperAdmin) {
            return UserRole::SuperAdmin->value;
        }

        return $committeeRole ?? $membershipType ?? 'member';
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

    public function hasCommitteeRole(): bool
    {
        return filled($this->committee_role);
    }

    public function isChiefCommitteeMember(): bool
    {
        return $this->committee_role === 'chief_committee_member';
    }

    public function isViceChiefCommitteeMember(): bool
    {
        return $this->committee_role === 'vice_chief_committee_member';
    }

    public function hasCommitteeLeadership(): bool
    {
        if (! $this->hasCommitteeRole()) {
            return false;
        }

        if ($this->relationLoaded('committeeRoleRecord')) {
            return (bool) $this->committeeRoleRecord?->is_leadership;
        }

        return (bool) Role::query()
            ->where('slug', $this->committee_role)
            ->value('is_leadership');
    }

    public function isMainMember(): bool
    {
        return $this->membership_type === MembershipRole::MainMember->value;
    }

    public function isFamilyMember(): bool
    {
        return $this->membership_type === MembershipRole::FamilyMember->value;
    }

    public function isRentalMember(): bool
    {
        return $this->membership_type === MembershipRole::RentalMember->value;
    }

    /**
     * Committee (or SA) users who may add/edit household members for any main member — not only their own.
     * Requires committee Users create or update (pivot), so MM+committee still needs explicit Users access.
     */
    public function canChooseHouseholdScope(): bool
    {
        return $this->isMainMember() && $this->canManageAnyHousehold();
    }

    public function canManageAnyHousehold(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (! $this->hasCommitteeRole()) {
            return false;
        }

        $permissions = app(ModulePermissionService::class);
        $committeeRole = (string) $this->committee_role;

        return $permissions->roleCanOnModule($committeeRole, 'users_all', ModulePermissionAction::Create)
            || $permissions->roleCanOnModule($committeeRole, 'users_all', ModulePermissionAction::Update);
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

        if (SuperAdminOnlyModules::isSuperAdminOnlySlug($moduleKey)) {
            return false;
        }

        $permissionAction = $action instanceof ModulePermissionAction
            ? $action
            : ModulePermissionAction::from($action);

        if ($this->isMainMember() && $this->isMembersModuleSlug($moduleKey)) {
            return true;
        }

        $moduleRecord = Module::query()->where('slug', $moduleKey)->first();

        if ($moduleRecord && ! $moduleRecord->is_permission_target) {
            return $this->canOnAdminModuleGroup($moduleKey, $permissionAction);
        }

        if ($this->hasCommitteeRole()) {
            return app(ModulePermissionService::class)->roleCanOnModule(
                (string) $this->committee_role,
                $moduleKey,
                $permissionAction,
            );
        }

        return false;
    }

    public function canOnAdminModuleGroup(string $parentSlug, ModulePermissionAction|string $action = 'read'): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (SuperAdminOnlyModules::isSuperAdminOnlySlug($parentSlug)) {
            return false;
        }

        $permissionAction = $action instanceof ModulePermissionAction
            ? $action
            : ModulePermissionAction::from($action);

        if ($parentSlug === AdminModule::Members->value && $this->isMainMember()) {
            return true;
        }

        if ($this->hasCommitteeRole()) {
            return app(ModulePermissionService::class)->roleCanOnModuleGroup(
                (string) $this->committee_role,
                $parentSlug,
                $permissionAction,
            );
        }

        return false;
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

        $permissionAction = $action instanceof ModulePermissionAction
            ? $action
            : ModulePermissionAction::from($action);

        $moduleSlug = $permissionAction === ModulePermissionAction::Create ? 'users_add' : 'users_all';

        return $this->canOnAdminModule($moduleSlug, $permissionAction);
    }

    /** @see Gate ability `members.manage` */
    public function canManageMember(self $target, ModulePermissionAction|string $action = 'update'): bool
    {
        if (! in_array($target->membership_type, [
            MembershipRole::FamilyMember->value,
            MembershipRole::RentalMember->value,
        ], true)) {
            return false;
        }

        $permissionAction = $action instanceof ModulePermissionAction
            ? $action
            : ModulePermissionAction::from($action);

        $moduleSlug = $permissionAction === ModulePermissionAction::Create ? 'members_add' : 'members_all';

        if (! $this->canOnAdminModule($moduleSlug, $permissionAction)) {
            return false;
        }

        if ($this->isMainMember() && ! $this->canManageAnyHousehold()) {
            return (int) $target->linked_main_member_id === $this->id;
        }

        return true;
    }

    private function isMembersModuleSlug(string $moduleKey): bool
    {
        return in_array($moduleKey, [
            AdminModule::Members->value,
            'members_all',
            'members_add',
        ], true);
    }
}
