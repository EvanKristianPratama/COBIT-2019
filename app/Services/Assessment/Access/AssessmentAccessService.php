<?php

namespace App\Services\Assessment\Access;

use App\Models\AccessAssignment;
use App\Models\MstEval;
use App\Models\TrsScoping;
use App\Models\User;
use App\Services\Auth\AccessProfilePermissionService;
use App\Support\Authorization\PermissionCatalog;
use Illuminate\Database\Eloquent\Builder;

class AssessmentAccessService
{
    public function __construct(
        private readonly AccessProfilePermissionService $accessProfilePermissionService
    ) {
    }

    public function queryAccessible(User $user): Builder
    {
        $query = MstEval::query()->with('organization');

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

    public function canView(User $user, MstEval $evaluation): bool
    {
        if (! $user->can('assessments.view')) {
            return false;
        }

        if ($this->canManage($user, $evaluation) || $user->isAdmin()) {
            return true;
        }

        return $this->assignmentFor($user, $evaluation) !== null
            || $this->belongsToUserOrganizations($user, $evaluation);
    }

    public function canManage(User $user, MstEval $evaluation): bool
    {
        if (! $user->can('assessments.input')) {
            return false;
        }

        if ($user->isAdmin() || (string) $evaluation->user_id === (string) $user->id) {
            return true;
        }

        $assignment = $this->assignmentFor($user, $evaluation);

        if ($assignment && $this->accessProfilePermissionService->profileHasPermission($assignment->access_profile, PermissionCatalog::AssessmentsInput)) {
            return true;
        }

        return $this->belongsToUserOrganizations($user, $evaluation);
    }

    public function findManagedScope(int $scopeId, User $user): ?TrsScoping
    {
        $scope = TrsScoping::with('evaluation.user')->find($scopeId);

        if (! $scope || ! $scope->evaluation) {
            return null;
        }

        return $this->canManage($user, $scope->evaluation) ? $scope : null;
    }

    public function assign(User $user, MstEval $evaluation, ?User $assignedBy = null): AccessAssignment
    {
        $profile = $user->accessProfileEnum() ?? \App\Enums\UserAccessProfile::Viewer;

        return $evaluation->accessAssignments()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'access_profile' => $profile->value,
                'assigned_by' => $assignedBy?->id,
            ]
        );
    }

    private function assignmentFor(User $user, MstEval $evaluation): ?AccessAssignment
    {
        return $evaluation->accessAssignments()
            ->where('user_id', $user->id)
            ->first();
    }

    private function applyOrganizationScope(Builder $query, User $user): void
    {
        $organizationIds = $user->organizationIds();

        if ($organizationIds === []) {
            return;
        }

        $query->orWhereIn('organization_id', $organizationIds);
    }

    private function belongsToUserOrganizations(User $user, MstEval $evaluation): bool
    {
        if ($user->hasOrganizationId((int) $evaluation->organization_id)) {
            return true;
        }

        $organizationName = $evaluation->relationLoaded('organization')
            ? $evaluation->organization?->organization_name
            : $evaluation->organization()->value('organization_name');

        return $user->hasOrganizationAccess($organizationName);
    }
}
