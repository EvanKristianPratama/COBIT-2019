<?php

namespace App\Services\Assessment\List;

use App\Models\MstEval;
use App\Models\TargetCapability;
use App\Models\TargetMaturity;
use App\Models\TrsActivityeval;
use App\Models\TrsScoping;
use App\Models\User;
use App\Services\Assessment\Access\AssessmentAccessService;
use Illuminate\Support\Facades\DB;

class AssessmentListService
{
    public function __construct(
        private readonly AssessmentAccessService $assessmentAccessService
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getIndexData(User $user): array
    {
        $organizationOptions = $user->organizations()
            ->select('mst_organization.organization_id', 'organization_name')
            ->orderByPivot('is_primary', 'desc')
            ->orderBy('organization_name')
            ->get();

        $myQuery = $this->assessmentAccessService
            ->queryAccessible($user)
            ->with(['user', 'organization', 'maturityScore'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        $assignedQuery = $this->assessmentAccessService
            ->queryAccessible($user)
            ->with(['user', 'organization', 'maturityScore'])
            ->where('user_id', '!=', $user->id)
            ->orderBy('created_at', 'desc');

        $myAssessments = $myQuery->paginate(10, ['*'], 'my_page');
        $assignedAssessments = $assignedQuery->paginate(10, ['*'], 'assigned_page');

        $allEvals = collect($myAssessments->items());
        if ($assignedAssessments->total() > 0) {
            $allEvals = $allEvals->merge($assignedAssessments->items());
        }

        if ($allEvals->isNotEmpty()) {
            $evalIds = $allEvals->pluck('eval_id')->unique()->values()->all();

            $scopeCounts = TrsScoping::whereIn('eval_id', $evalIds)
                ->select('eval_id', DB::raw('count(*) as scope_count'))
                ->groupBy('eval_id')
                ->pluck('scope_count', 'eval_id')
                ->toArray();

            $lastActivityDates = TrsActivityeval::whereIn('eval_id', $evalIds)
                ->select('eval_id', DB::raw('MAX(updated_at) as last_activity_at'))
                ->groupBy('eval_id')
                ->pluck('last_activity_at', 'eval_id');

            foreach ($allEvals as $evaluation) {
                $evaluation->scope_count = $scopeCounts[$evaluation->eval_id] ?? 0;
                $evaluation->last_saved_at = $lastActivityDates[$evaluation->eval_id] ?? $evaluation->created_at;
                $evaluation->avg_target_capability = $this->resolveTargetCapabilityAverage($evaluation);
                $evaluation->target_maturity = $this->resolveTargetMaturity($evaluation);
            }
        }

        $totalAssessments = $myAssessments->total() + $assignedAssessments->total();

        $statsQuery = $this->assessmentAccessService->queryAccessible($user);

        $finishedAssessments = (clone $statsQuery)->where('status', 'finished')->count();

        return [
            'myAssessments' => $myAssessments,
            'assignedAssessments' => $assignedAssessments,
            'totalAssessments' => $totalAssessments,
            'finishedAssessments' => $finishedAssessments,
            'draftAssessments' => max(0, $totalAssessments - $finishedAssessments),
            'organizationOptions' => $organizationOptions,
            'selectedOrganizationId' => $user->organization_id ?: $organizationOptions->first()?->organization_id,
        ];
    }

    private function resolveTargetCapabilityAverage(MstEval $evaluation): float
    {
        $target = TargetCapability::query()
            ->where('tahun', $evaluation->tahun)
            ->when(
                filled($evaluation->organization_id),
                fn ($query) => $query->where('organization_id', $evaluation->organization_id),
                fn ($query) => $query->where('user_id', $evaluation->user_id)
            )
            ->latest('target_id')
            ->first();

        if (! $target) {
            return 0;
        }

        $domainCols = [
            'EDM01', 'EDM02', 'EDM03', 'EDM04', 'EDM05',
            'APO01', 'APO02', 'APO03', 'APO04', 'APO05', 'APO06', 'APO07', 'APO08', 'APO09', 'APO10', 'APO11', 'APO12', 'APO13', 'APO14',
            'BAI01', 'BAI02', 'BAI03', 'BAI04', 'BAI05', 'BAI06', 'BAI07', 'BAI08', 'BAI09', 'BAI10', 'BAI11',
            'DSS01', 'DSS02', 'DSS03', 'DSS04', 'DSS05', 'DSS06',
            'MEA01', 'MEA02', 'MEA03', 'MEA04',
        ];

        $sum = 0;
        $count = 0;
        foreach ($domainCols as $col) {
            $value = $target->{$col};
            if ($value !== null && is_numeric($value)) {
                $sum += $value;
                $count++;
            }
        }

        return $count > 0 ? round($sum / $count, 2) : 0;
    }

    private function resolveTargetMaturity(MstEval $evaluation): ?float
    {
        $target = TargetMaturity::query()
            ->where('tahun', $evaluation->tahun)
            ->when(
                filled($evaluation->organization_id),
                fn ($query) => $query->where('organization_id', $evaluation->organization_id),
                fn ($query) => $query->where('user_id', $evaluation->user_id)
            )
            ->latest('id')
            ->first();

        return $target?->target_maturity;
    }
}
