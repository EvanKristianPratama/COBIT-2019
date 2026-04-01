<?php

namespace App\Services\Assessment\Access;

use App\Models\MstEval;
use App\Models\TrsScoping;
use App\Models\User;

class AssessmentAccessService
{
    public function canView(User $user, MstEval $evaluation): bool
    {
        if ($this->canManage($user, $evaluation)) {
            return true;
        }

        $owner = $evaluation->relationLoaded('user')
            ? $evaluation->user
            : $evaluation->user()->first();

        return $owner instanceof User && $this->sharesOrganization($owner, $user);
    }

    public function canManage(User $user, MstEval $evaluation): bool
    {
        return (string) $evaluation->user_id === (string) $user->id;
    }

    public function findManagedScope(int $scopeId, User $user): ?TrsScoping
    {
        $scope = TrsScoping::with('evaluation.user')->find($scopeId);

        if (! $scope || ! $scope->evaluation) {
            return null;
        }

        return $this->canManage($user, $scope->evaluation) ? $scope : null;
    }

    private function sharesOrganization(User $owner, User $viewer): bool
    {
        $ownerOrg = trim((string) $owner->organisasi);
        $viewerOrg = trim((string) $viewer->organisasi);

        return $ownerOrg !== ''
            && $viewerOrg !== ''
            && strcasecmp($ownerOrg, $viewerOrg) === 0;
    }
}
