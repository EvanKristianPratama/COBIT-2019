<?php

namespace App\Services\Organization;

use App\Models\MstOrganization;
use App\Support\Organization\OrganizationNameNormalizer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrganizationRegistryService
{
    public function findByName(?string $name): ?MstOrganization
    {
        $organizationKey = OrganizationNameNormalizer::key($name);

        if ($organizationKey === null) {
            return null;
        }

        return MstOrganization::query()
            ->where('organization_key', $organizationKey)
            ->first();
    }

    public function findOrCreateByName(?string $name): ?MstOrganization
    {
        return $this->syncByNames([$name])->first();
    }

    /**
     * @param  iterable<string|null>  $names
     * @return Collection<int, MstOrganization>
     */
    public function syncByNames(iterable $names): Collection
    {
        $organizations = OrganizationNameNormalizer::unique($names);

        if ($organizations === []) {
            return collect();
        }

        $now = now();

        DB::table('mst_organization')->upsert(
            collect($organizations)
                ->map(fn (string $organization): array => [
                    'organization_name' => $organization,
                    'organization_key' => OrganizationNameNormalizer::key($organization),
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
                ->all(),
            ['organization_key'],
            ['organization_name', 'is_active', 'updated_at']
        );

        return MstOrganization::query()
            ->whereIn(
                'organization_key',
                collect($organizations)
                    ->map(fn (string $organization): ?string => OrganizationNameNormalizer::key($organization))
                    ->filter()
                    ->all()
            )
            ->get();
    }

    public function resolveName(?int $organizationId, ?string $fallback = null): ?string
    {
        if ($organizationId === null) {
            return OrganizationNameNormalizer::display($fallback);
        }

        return MstOrganization::query()
            ->where('organization_id', $organizationId)
            ->value('organization_name')
            ?? OrganizationNameNormalizer::display($fallback);
    }
}
