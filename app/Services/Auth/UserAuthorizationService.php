<?php

namespace App\Services\Auth;

use App\Enums\UserAccessProfile;
use App\Enums\UserRole;
use App\Models\User;
use App\Support\Authorization\PermissionCatalog;
use Spatie\Permission\PermissionRegistrar;

class UserAuthorizationService
{
    public function __construct(
        private readonly AccessProfilePermissionService $accessProfilePermissionService
    ) {
    }

    public function normalizeRole(?string $role): UserRole
    {
        return UserRole::tryFrom((string) $role) ?? UserRole::User;
    }

    public function normalizeAccessProfile(?string $role, ?string $accessProfile): ?UserAccessProfile
    {
        if ($this->normalizeRole($role) === UserRole::Admin) {
            return null;
        }

        return UserAccessProfile::tryFrom((string) $accessProfile) ?? UserAccessProfile::Viewer;
    }

    public function sync(User $user): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $role = $user->assignedRoleEnum();

        if (! $role) {
            $this->clearAssignments($user);
            return;
        }

        $accessProfile = $role === UserRole::Admin
            ? null
            : UserAccessProfile::tryFrom((string) $user->rawAccessProfile());

        if ($role !== UserRole::Admin && ! $accessProfile) {
            $this->clearAssignments($user);
            return;
        }

        $user->forceFill([
            'role' => $role->value,
            'access_profile' => $accessProfile?->value,
        ])->saveQuietly();

        $user->syncRoles([$role->value]);

        if ($role === UserRole::Admin) {
            $user->syncPermissions([]);
            return;
        }

        $user->syncPermissions($this->accessProfilePermissionService->permissionsForProfile($accessProfile));
    }

    public function syncUsersForAccessProfile(UserAccessProfile $accessProfile): void
    {
        User::query()
            ->where('role', UserRole::User->value)
            ->where('access_profile', $accessProfile->value)
            ->get()
            ->each(function (User $user): void {
                $this->sync($user);
            });
    }

    /**
     * @return list<string>
     */
    public function adminPermissions(): array
    {
        return PermissionCatalog::all();
    }

    private function clearAssignments(User $user): void
    {
        $user->syncRoles([]);
        $user->syncPermissions([]);
    }
}
