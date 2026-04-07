<?php

namespace App\Models;

use App\Enums\UserAccessProfile;
use App\Enums\UserRole;
use App\Models\MstOrganization;
use App\Models\TrsUserOrganization;
use App\Support\Organization\OrganizationNameNormalizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable
{
    use HasRoles;
    use Notifiable;

    protected $fillable = [
        'id', 'name', 'email', 'password', 'organisasi', 'organization_id', 'jabatan', 'role', 'access_profile',
    ];

    protected $hidden = [
        'password', 'remember_token', 'role', 'access_profile',
    ];

    protected string $guard_name = 'web';

    /**
     * Get the user's full name (for display purposes).
     */
    public function getFullNameAttribute(): string
    {
        return ucfirst($this->name);
    }

    /**
     * Get all evaluations created by this user
     */
    public function evaluations()
    {
        return $this->hasMany(MstEval::class, 'user_id', 'id');
    }

    public function accessAssignments(): HasMany
    {
        return $this->hasMany(AccessAssignment::class);
    }

    public function organizationMappings(): HasMany
    {
        return $this->hasMany(TrsUserOrganization::class)
            ->orderByDesc('is_primary');
    }

    public function primaryOrganization(): BelongsTo
    {
        return $this->belongsTo(MstOrganization::class, 'organization_id', 'organization_id');
    }

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(MstOrganization::class, 'trs_userorganization', 'user_id', 'organization_id')
            ->withPivot(['is_primary', 'assigned_by'])
            ->withTimestamps()
            ->orderByPivot('is_primary', 'desc')
            ->orderBy('organization_name');
    }

    public function systemRole(): UserRole
    {
        return UserRole::tryFrom((string) $this->role) ?? UserRole::User;
    }

    public function rawRole(): ?string
    {
        $value = $this->getRawOriginal('role');

        return filled($value) ? (string) $value : null;
    }

    public function rawAccessProfile(): ?string
    {
        $value = $this->getRawOriginal('access_profile');

        return filled($value) ? (string) $value : null;
    }

    public function rawOrganizationId(): ?int
    {
        $value = $this->getRawOriginal('organization_id');

        return filled($value) ? (int) $value : null;
    }

    public function rawOrganizationName(): ?string
    {
        $value = $this->getRawOriginal('organisasi');

        return filled($value) ? (string) $value : null;
    }

    public function assignedRoleEnum(): ?UserRole
    {
        return UserRole::tryFrom((string) $this->rawRole());
    }

    public function accessProfileEnum(): ?UserAccessProfile
    {
        return UserAccessProfile::tryFrom((string) $this->access_profile);
    }

    public function isAdmin(): bool
    {
        if ($this->assignedRoleEnum() === UserRole::Admin) {
            return true;
        }

        if (! $this->exists) {
            return false;
        }

        return $this->hasRole(UserRole::Admin->value);
    }

    public function displayRoleLabel(): string
    {
        $assignedRole = $this->assignedRoleEnum();

        if (! $assignedRole) {
            return 'Pending Approval';
        }

        if ($assignedRole !== UserRole::Admin && $this->requiresAdminApproval()) {
            return 'Pending Approval';
        }

        return $assignedRole->label();
    }

    public function displayAccessProfileLabel(): string
    {
        if ($this->requiresAdminApproval() && ! $this->isAdmin()) {
            return 'Pending Approval';
        }

        return $this->accessProfileEnum()?->label() ?? '-';
    }

    public function hasAssignedRole(): bool
    {
        return $this->assignedRoleEnum() !== null;
    }

    public function hasAssignedOrganizations(): bool
    {
        return $this->rawOrganizationId() !== null
            || filled($this->rawOrganizationName())
            || $this->organizationIds() !== [];
    }

    public function requiresAdminApproval(): bool
    {
        $assignedRole = $this->assignedRoleEnum();

        if (! $assignedRole) {
            return true;
        }

        if ($assignedRole === UserRole::Admin) {
            return false;
        }

        return $this->rawOrganizationId() === null || $this->rawAccessProfile() === null;
    }

    public function isPendingApproval(): bool
    {
        return $this->requiresAdminApproval();
    }

    public function accountStatusKey(): string
    {
        if ($this->isPendingApproval()) {
            return 'pending';
        }

        return $this->isActivated ? 'active' : 'inactive';
    }

    public function accountStatusLabel(): string
    {
        return match ($this->accountStatusKey()) {
            'pending' => 'Pending',
            'inactive' => 'Nonaktif',
            default => 'Aktif',
        };
    }

    public function accountStatusIcon(): string
    {
        return match ($this->accountStatusKey()) {
            'pending' => 'fa-hourglass-half',
            'inactive' => 'fa-circle-xmark',
            default => 'fa-circle-check',
        };
    }

    public function scopePendingApproval(Builder $query): Builder
    {
        return $query
            ->where('role', UserRole::User->value)
            ->where(function (Builder $builder): void {
                $builder->whereNull('access_profile')
                    ->orWhereNull('organization_id');
            });
    }

    public function scopeApprovedUsers(Builder $query): Builder
    {
        return $query
            ->where('role', UserRole::User->value)
            ->whereNotNull('access_profile')
            ->whereNotNull('organization_id');
    }

    public function organizationNames(): Collection
    {
        $organizations = $this->relationLoaded('organizations')
            ? $this->organizations->pluck('organization_name')
            : $this->organizations()->pluck('organization_name');

        if ($organizations->isEmpty() && filled($this->organisasi)) {
            $organizations = collect([OrganizationNameNormalizer::display($this->organisasi)]);
        }

        return $organizations
            ->filter()
            ->values();
    }

    public function additionalOrganizationNames(): Collection
    {
        $primaryKey = OrganizationNameNormalizer::key($this->organisasi);

        return $this->organizationNames()
            ->reject(fn (?string $organization): bool => OrganizationNameNormalizer::key($organization) === $primaryKey)
            ->values();
    }

    /**
     * @return list<string>
     */
    public function organizationKeys(): array
    {
        return $this->organizationNames()
            ->map(fn (string $organization): ?string => OrganizationNameNormalizer::key($organization))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return list<int>
     */
    public function organizationIds(): array
    {
        $organizationIds = $this->relationLoaded('organizations')
            ? $this->organizations->pluck('organization_id')
            : $this->organizations()->pluck('mst_organization.organization_id');

        if ($organizationIds->isEmpty() && $this->rawOrganizationId()) {
            $organizationIds = collect([$this->rawOrganizationId()]);
        }

        return $organizationIds
            ->map(fn ($organizationId): int => (int) $organizationId)
            ->filter(fn (int $organizationId): bool => $organizationId > 0)
            ->unique()
            ->values()
            ->all();
    }

    public function organizationCount(): int
    {
        return $this->organizationNames()->count();
    }

    public function hasOrganizationAccess(?string $organization): bool
    {
        $organizationKey = OrganizationNameNormalizer::key($organization);

        return $organizationKey !== null && in_array($organizationKey, $this->organizationKeys(), true);
    }

    public function hasOrganizationId(?int $organizationId): bool
    {
        return $organizationId !== null && in_array((int) $organizationId, $this->organizationIds(), true);
    }

    public function displayOrganizationSummary(): string
    {
        $organizations = $this->organizationNames();

        if ($organizations->isEmpty()) {
            return '-';
        }

        $primary = $organizations->first();
        $additionalCount = $organizations->count() - 1;

        return $additionalCount > 0
            ? sprintf('%s +%d', $primary, $additionalCount)
            : $primary;
    }

    public function getOrganisasiAttribute($value): ?string
    {
        if ($this->relationLoaded('primaryOrganization')) {
            return $this->primaryOrganization?->organization_name ?? $value;
        }

        $organizationId = $this->rawOrganizationId();

        if ($organizationId) {
            return $this->primaryOrganization()->value('organization_name') ?? $value;
        }

        return $value;
    }
}
