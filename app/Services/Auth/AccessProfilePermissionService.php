<?php

namespace App\Services\Auth;

use App\Enums\UserAccessProfile;
use App\Models\MstAccessProfile;
use App\Support\Authorization\PermissionCatalog;
use Illuminate\Support\Collection;

class AccessProfilePermissionService
{
    /**
     * @var array<string, list<string>>|null
     */
    private ?array $permissionMap = null;

    public function profiles(): Collection
    {
        $profiles = MstAccessProfile::query()
            ->with(['permissions' => fn ($query) => $query->orderBy('permission_name')])
            ->orderBy('access_profile_id')
            ->get();

        $profilesByKey = $profiles->keyBy('access_profile_key');

        return collect(UserAccessProfile::cases())->map(function (UserAccessProfile $profile) use ($profilesByKey): MstAccessProfile {
            if ($existingProfile = $profilesByKey->get($profile->value)) {
                return $existingProfile;
            }

            $model = new MstAccessProfile([
                'access_profile_key' => $profile->value,
                'access_profile_label' => $profile->label(),
                'is_system' => true,
            ]);

            $model->setRelation('permissions', collect());

            return $model;
        });
    }

    /**
     * @return list<string>
     */
    public function permissionsForProfile(UserAccessProfile|string|null $profile): array
    {
        $profileKey = $this->normalizeProfileKey($profile);

        if ($profileKey === null) {
            return [];
        }

        $map = $this->permissionMap();

        if (array_key_exists($profileKey, $map)) {
            return $map[$profileKey];
        }

        return UserAccessProfile::tryFrom($profileKey)?->defaultPermissions() ?? [];
    }

    public function profileHasPermission(UserAccessProfile|string|null $profile, string $permission): bool
    {
        return in_array($permission, $this->permissionsForProfile($profile), true);
    }

    /**
     * @return array<string, list<string>>
     */
    public function permissionMap(): array
    {
        if ($this->permissionMap !== null) {
            return $this->permissionMap;
        }

        $profiles = $this->profiles();
        $map = [];

        foreach ($profiles as $profile) {
            $key = (string) $profile->access_profile_key;

            $map[$key] = $profile->permissions
                ->pluck('permission_name')
                ->filter(fn (?string $permission): bool => in_array((string) $permission, PermissionCatalog::profileAssignable(), true))
                ->unique()
                ->values()
                ->all();

            if ($map[$key] === [] && ! $profile->exists) {
                $map[$key] = $profile->enum()?->defaultPermissions() ?? [];
            }
        }

        $this->permissionMap = $map;

        return $this->permissionMap;
    }

    /**
     * @param list<string> $permissions
     */
    public function replacePermissions(MstAccessProfile $profile, array $permissions): void
    {
        $normalizedPermissions = collect($permissions)
            ->filter(fn (?string $permission): bool => in_array((string) $permission, PermissionCatalog::profileAssignable(), true))
            ->unique()
            ->values();

        $profile->permissions()->delete();

        if ($normalizedPermissions->isNotEmpty()) {
            $profile->permissions()->createMany(
                $normalizedPermissions
                    ->map(fn (string $permission): array => ['permission_name' => $permission])
                    ->all()
            );
        }

        $this->permissionMap[(string) $profile->access_profile_key] = $normalizedPermissions->all();
    }

    private function normalizeProfileKey(UserAccessProfile|string|null $profile): ?string
    {
        if ($profile instanceof UserAccessProfile) {
            return $profile->value;
        }

        $profile = trim((string) $profile);

        return $profile !== '' ? $profile : null;
    }
}
