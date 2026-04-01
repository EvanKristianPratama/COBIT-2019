<?php

namespace App\Services\Assessment\Report;

use App\Models\MstEval;
use App\Models\TargetMaturity;
use App\Models\TrsEvalDetail;
use App\Models\TrsScoping;
use App\Models\User;
use App\Services\EvaluationService;

class AssessmentReportService
{
    public function __construct(
        private readonly EvaluationService $evaluationService
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
            $org = $owner?->organisasi ?? null;

            if ($org) {
                $tmQuery->where('organisasi', $org);
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
        $org = $user->organisasi ?? null;

        $query = MstEval::with(['user', 'maturityScore'])->orderBy('created_at', 'desc');

        if ($org) {
            $query->where(function ($builder) use ($user, $org) {
                $builder->where('user_id', $user->id)
                    ->orWhereHas('user', function ($subQuery) use ($org) {
                        $subQuery->where('organisasi', $org);
                    });
            });
        } else {
            $query->where('user_id', $user->id);
        }

        $assessments = $query->get();
        if ($assessments->isEmpty()) {
            return ['error' => 'No assessments found.'];
        }

        $objectives = $this->evaluationService->getSortedObjectives();
        $processedData = [];

        $allYears = $assessments->pluck('tahun')->filter()->unique()->values()->all();
        $targetMaturityMap = [];

        if (! empty($allYears)) {
            $targetMaturities = TargetMaturity::whereIn('tahun', $allYears)
                ->when($org, function ($builder) use ($org) {
                    $builder->where('organisasi', $org);
                }, function ($builder) use ($user) {
                    $builder->where('user_id', $user->id);
                })
                ->get();

            foreach ($targetMaturities as $targetMaturity) {
                $targetMaturityMap[$targetMaturity->tahun] = $targetMaturity->target_maturity;
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
                    'target_maturity' => $targetMaturityMap[$year] ?? null,
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
