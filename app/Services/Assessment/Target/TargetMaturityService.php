<?php

namespace App\Services\Assessment\Target;

use App\Models\TargetMaturity;
use App\Models\User;
use App\Services\Organization\OrganizationRegistryService;

class TargetMaturityService
{
    public function __construct(
        private readonly OrganizationRegistryService $organizationRegistryService
    ) {
    }

    public function getTargetsForUser(int $userId, ?int $organizationId = null)
    {
        return TargetMaturity::where('user_id', $userId)
            ->when($organizationId, fn ($query) => $query->where('organization_id', $organizationId))
            ->with('organization')
            ->orderBy('tahun', 'desc')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function store(User $user, array $validated): TargetMaturity
    {
        $organizationId = (int) $validated['organization_id'];
        $organizationName = $this->organizationRegistryService->resolveName($organizationId, $user->organisasi);

        return TargetMaturity::updateOrCreate(
            [
                'user_id' => $user->id,
                'tahun' => $validated['tahun'],
                'organization_id' => $organizationId,
            ],
            [
                'organisasi' => $organizationName ?? 'Unknown',
                'target_maturity' => $validated['target_maturity'],
            ]
        );
    }

    public function deleteForUser(int $targetId, int $userId): void
    {
        $target = TargetMaturity::where('user_id', $userId)->findOrFail($targetId);
        $target->delete();
    }
}
