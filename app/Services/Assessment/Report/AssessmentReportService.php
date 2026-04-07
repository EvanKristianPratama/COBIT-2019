<?php

namespace App\Services\Assessment\Report;

use App\Models\MstEval;
use App\Models\TargetMaturity;
use App\Models\TrsEvalDetail;
use App\Models\TrsScoping;
use App\Models\User;
use App\Services\Assessment\Access\AssessmentAccessService;
use App\Services\EvaluationService;

class AssessmentReportService
{
    public function __construct(
        private readonly EvaluationService $evaluationService,
        private readonly AssessmentAccessService $assessmentAccessService
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function buildSingleAssessmentReport(MstEval $evaluation): array
    {
        $evalId = $evaluation->eval_id;
        $allScopes = TrsScoping::where('eval_id', $evalId)->get();
        $objectives = $this->evaluationService->getSortedObjectives();
        $targetCapabilityMap = $this->evaluationService->fetchTargetCapabilities($evaluation);

        $loadedData = $this->evaluationService->loadEvaluation($evalId);
        $activityData = $loadedData['activity_evaluations'] ?? [];

        $scopeMaturityData = [];
        foreach ($allScopes as $scope) {
            $scopeDomains = TrsEvalDetail::where('scoping_id', $scope->id)->pluck('domain_id')->toArray();

            $scopeMaturityData[$scope->id] = [];
            foreach ($objectives as $objective) {
                $isInScope = in_array($objective->objective_id, $scopeDomains, true);
                $scopeMaturityData[$scope->id][$objective->objective_id] = $isInScope
                    ? $this->evaluationService->calculateObjectiveMaturity($objective, $activityData)
                    : null;
            }
        }

        $owner = $evaluation->relationLoaded('user') ? $evaluation->user : $evaluation->user()->first();
        $year = $evaluation->tahun ?? $evaluation->year ?? $evaluation->assessment_year ?? null;
        $targetMaturity = null;

        if ($year) {
            $tmQuery = TargetMaturity::where('tahun', $year);
            $organizationId = $evaluation->organization_id;

            if ($organizationId) {
                $tmQuery->where('organization_id', $organizationId);
            } elseif ($owner) {
                $tmQuery->where('user_id', $owner->id);
            }

            $targetMaturity = $tmQuery->value('target_maturity');
        }

        return compact(
            'objectives',
            'evalId',
            'evaluation',
            'targetCapabilityMap',
            'allScopes',
            'scopeMaturityData',
            'targetMaturity'
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function buildOverviewReport(User $user): array
    {
        $assessments = $this->assessmentAccessService
            ->queryAccessible($user)
            ->with(['user', 'organization', 'maturityScore'])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($assessments->isEmpty()) {
            return ['error' => 'No assessments found.'];
        }

        $objectives = $this->evaluationService->getSortedObjectives();
        $processedData = [];

        $allYears = $assessments->pluck('tahun')->filter()->unique()->values()->all();
        $ownerIds = $assessments->pluck('user_id')->filter()->unique()->values()->all();
        $organizationIds = $assessments->pluck('organization_id')->filter()->unique()->values()->all();

        $targetMaturityByOrganization = [];
        $targetMaturityByUser = [];

        if (! empty($allYears)) {
            $targetMaturities = TargetMaturity::whereIn('tahun', $allYears)
                ->where(function ($builder) use ($ownerIds, $organizationIds) {
                    if ($organizationIds !== []) {
                        $builder->whereIn('organization_id', $organizationIds);
                    }

                    if ($ownerIds !== []) {
                        $method = $organizationIds !== [] ? 'orWhereIn' : 'whereIn';
                        $builder->{$method}('user_id', $ownerIds);
                    }
                })
                ->get();

            foreach ($targetMaturities as $targetMaturity) {
                if ($targetMaturity->organization_id !== null) {
                    $targetMaturityByOrganization[$targetMaturity->organization_id.'|'.$targetMaturity->tahun] = $targetMaturity->target_maturity;
                    continue;
                }

                if ($targetMaturity->user_id !== null) {
                    $targetMaturityByUser[$targetMaturity->user_id.'|'.$targetMaturity->tahun] = $targetMaturity->target_maturity;
                }
            }
        }

        foreach ($assessments as $evaluation) {
            $scopes = TrsScoping::where('eval_id', $evaluation->eval_id)->get();
            if ($scopes->isEmpty()) {
                continue;
            }

            $loadedData = $this->evaluationService->loadEvaluation($evaluation->eval_id);
            $activityData = $loadedData['activity_evaluations'] ?? [];
            $year = $evaluation->tahun ?? $evaluation->year ?? $evaluation->assessment_year ?? $evaluation->created_at->format('Y');
            $targetMaturity = $evaluation->organization_id !== null
                ? ($targetMaturityByOrganization[$evaluation->organization_id.'|'.$year] ?? null)
                : ($targetMaturityByUser[$evaluation->user_id.'|'.$year] ?? null);

            foreach ($scopes as $scope) {
                $scopeDomains = TrsEvalDetail::where('scoping_id', $scope->id)->pluck('domain_id')->toArray();
                $maturityScores = [];

                foreach ($objectives as $objective) {
                    $maturityScores[$objective->objective_id] = in_array($objective->objective_id, $scopeDomains, true)
                        ? $this->evaluationService->calculateObjectiveMaturity($objective, $activityData)
                        : null;
                }

                $processedData[] = [
                    'assessment_id' => $evaluation->eval_id,
                    'year' => $year,
                    'scope_id' => $scope->id,
                    'scope_name' => $scope->nama_scope,
                    'user_name' => $evaluation->user->name ?? 'Unknown',
                    'maturity_scores' => $maturityScores,
                    'target_maturity' => $targetMaturity,
                ];
            }
        }

        usort($processedData, function ($left, $right) {
            return ($left['year'] == $right['year'])
                ? strcmp($left['scope_name'], $right['scope_name'])
                : ($right['year'] <=> $left['year']);
        });

        return compact('objectives', 'processedData');
    }
}
