<?php

namespace App\Services\Auth;

use App\Models\MstOrganization;
use App\Models\User;
use App\Services\Organization\OrganizationRegistryService;
use App\Support\Organization\OrganizationNameNormalizer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UserOrganizationService
{
    public function __construct(
        private readonly OrganizationRegistryService $organizationRegistryService
    ) {
    }

    /**
     * @param  list<int|string>|null  $organizationIds
     */
    public function syncFromIds(User $user, ?array $organizationIds = null, ?User $assignedBy = null, ?int $preferredPrimaryOrganizationId = null): void
    {
        $organizationIds = collect($organizationIds ?? [])
            ->filter(fn ($value): bool => $value !== null && $value !== '')
            ->map(fn ($value): int => (int) $value)
            ->unique()
            ->values();

        if ($organizationIds->isEmpty()) {
            $this->clear($user);
            return;
        }

        $organizations = MstOrganization::query()
            ->whereIn('organization_id', $organizationIds->all())
            ->get();

        $primaryOrganizationId = $this->resolvePrimaryOrganizationId($organizations, $preferredPrimaryOrganizationId);

        $this->syncResolvedOrganizations($user, $organizations, $primaryOrganizationId, $assignedBy);
    }

    public function syncFromNames(User $user, ?string $primaryOrganization, ?string $additionalOrganizations = null, ?User $assignedBy = null): void
    {
        $organizations = OrganizationNameNormalizer::unique(array_merge(
            [OrganizationNameNormalizer::display($primaryOrganization)],
            OrganizationNameNormalizer::split($additionalOrganizations)
        ));

        if ($organizations === []) {
            $this->clear($user);
            return;
        }

        $resolvedOrganizations = $this->organizationRegistryService->syncByNames($organizations);
        $primaryOrganizationId = $this->organizationRegistryService
            ->findByName($primaryOrganization)
            ?->organization_id;

        $this->syncResolvedOrganizations($user, $resolvedOrganizations, $primaryOrganizationId, $assignedBy);
    }

    private function clear(User $user): void
    {
        $user->forceFill([
            'organization_id' => null,
            'organisasi' => null,
        ])->saveQuietly();

        $user->organizationMappings()->delete();
        $user->unsetRelation('organizationMappings');
        $user->unsetRelation('organizations');
        $user->unsetRelation('primaryOrganization');
    }

    private function syncResolvedOrganizations(User $user, Collection $organizations, ?int $primaryOrganizationId, ?User $assignedBy = null): void
    {
        if ($organizations->isEmpty()) {
            $this->clear($user);
            return;
        }

        $primaryOrganization = $organizations->firstWhere('organization_id', $primaryOrganizationId) ?? $organizations->first();
        $assignedById = $assignedBy?->id;
        $now = now();

        $user->forceFill([
            'organization_id' => $primaryOrganization?->organization_id,
            'organisasi' => $primaryOrganization?->organization_name,
        ])->saveQuietly();

        $rows = $organizations
            ->map(fn (MstOrganization $organization): array => [
                'user_id' => $user->id,
                'organization_id' => $organization->organization_id,
                'is_primary' => $organization->organization_id === $primaryOrganization?->organization_id,
                'assigned_by' => $assignedById,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->values()
            ->all();

        DB::table('trs_userorganization')->upsert(
            $rows,
            ['user_id', 'organization_id'],
            ['is_primary', 'assigned_by', 'updated_at']
        );

        $user->organizationMappings()
            ->whereNotIn('organization_id', array_column($rows, 'organization_id'))
            ->delete();

        $user->unsetRelation('organizationMappings');
        $user->unsetRelation('organizations');
        $user->unsetRelation('primaryOrganization');
    }

    private function resolvePrimaryOrganizationId(Collection $organizations, ?int $preferredPrimaryOrganizationId = null): ?int
    {
        if ($organizations->isEmpty()) {
            return null;
        }

        if ($preferredPrimaryOrganizationId && $organizations->contains('organization_id', $preferredPrimaryOrganizationId)) {
            return $preferredPrimaryOrganizationId;
        }

        return $organizations->first()?->organization_id;
    }
}
