<?php

namespace App\Services\Cobit;

use App\Enums\UserAccessProfile;
use App\Models\AccessAssignment;
use App\Models\Assessment;
use App\Models\User;
use App\Services\Auth\AccessProfilePermissionService;
use App\Support\Authorization\PermissionCatalog;
use Illuminate\Database\Eloquent\Builder;

class CobitAssessmentAccessService
{
    public function __construct(
        private readonly AccessProfilePermissionService $accessProfilePermissionService
    ) {
    }

    public function queryAccessible(User $user): Builder
    {
        $query = Assessment::query()->with('organization');

        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($user) {
            $builder->where('user_id', $user->id)
                ->orWhereHas('accessAssignments', function (Builder $assignmentQuery) use ($user) {
                    $assignmentQuery->where('user_id', $user->id);
                });

            $this->applyOrganizationScope($builder, $user);
        });
    }

    public function canView(User $user, Assessment $assessment): bool
    {
        if (! $user->can('design-factors.view')) {
            return false;
        }

        if ($user->isAdmin() || $this->isOwner($user, $assessment)) {
            return true;
        }

        return $this->assignmentFor($user, $assessment) !== null
            || $this->belongsToUserOrganizations($user, $assessment);
    }

    public function canInput(User $user, Assessment $assessment): bool
    {
        if (! $user->can('design-factors.input')) {
            return false;
        }

        if ($user->isAdmin() || $this->isOwner($user, $assessment)) {
            return true;
        }

        $assignment = $this->assignmentFor($user, $assessment);

        if ($assignment && $this->accessProfilePermissionService->profileHasPermission($assignment->access_profile, PermissionCatalog::DesignFactorsInput)) {
            return true;
        }

        return $this->belongsToUserOrganizations($user, $assessment);
    }

    public function assign(User $user, Assessment $assessment, ?User $assignedBy = null): AccessAssignment
    {
        $profile = $user->accessProfileEnum() ?? UserAccessProfile::Viewer;

        return $assessment->accessAssignments()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'access_profile' => $profile->value,
                'assigned_by' => $assignedBy?->id,
            ]
        );
    }

    public function assignmentFor(User $user, Assessment $assessment): ?AccessAssignment
    {
        return $assessment->accessAssignments()
            ->where('user_id', $user->id)
            ->first();
    }

    public function revoke(Assessment $assessment, AccessAssignment $assignment): void
    {
        if ((int) $assignment->assignable_id !== (int) $assessment->assessment_id) {
            return;
        }

        if ($assignment->assignable_type !== $assessment->getMorphClass()) {
            return;
        }

        $assignment->delete();
    }

    private function isOwner(User $user, Assessment $assessment): bool
    {
        return (string) $assessment->user_id === (string) $user->id;
    }

    private function applyOrganizationScope(Builder $query, User $user): void
    {
        $organizationIds = $user->organizationIds();

        if ($organizationIds === []) {
            return;
        }

        $query->orWhereIn('organization_id', $organizationIds);
    }

    private function belongsToUserOrganizations(User $user, Assessment $assessment): bool
    {
        if ($user->hasOrganizationId((int) $assessment->organization_id)) {
            return true;
        }

        return $user->hasOrganizationAccess($assessment->instansi);
    }
}
