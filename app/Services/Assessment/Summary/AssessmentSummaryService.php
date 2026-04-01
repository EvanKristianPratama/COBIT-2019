<?php

namespace App\Services\Assessment\Summary;

use App\Models\MstEval;
use App\Models\MstObjective;
use App\Models\TrsActivityeval;
use App\Models\TrsObjectiveScore;
use App\Models\TrsRoadmap;
use App\Models\TrsSummaryActivity;
use App\Models\TrsSummaryReport;
use App\Services\EvaluationService;

class AssessmentSummaryService
{
    public function __construct(
        private readonly EvaluationService $evaluationService
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getSummary(MstEval $evaluation, ?string $objectiveId = null): array
    {
        $evalId = $evaluation->eval_id;

        $objectivesQuery = MstObjective::with([
            'practices.activities.evaluations' => function ($query) use ($evalId) {
                $query->where('eval_id', $evalId);
            },
        ])->orderBy('objective_id');

        if ($objectiveId) {
            $objectivesQuery->where('objective_id', $objectiveId);
        }

        $objectives = $objectivesQuery->get();

        $scoresQuery = TrsObjectiveScore::where('eval_id', $evalId);
        if ($objectiveId) {
            $scoresQuery->where('objective_id', $objectiveId);
        }
        $objectiveScores = $scoresQuery->pluck('level', 'objective_id')->toArray();

        $savedNotesQuery = TrsSummaryReport::where('eval_id', $evalId)
            ->when($objectiveId, fn ($query) => $query->where('objective_id', $objectiveId))
            ->get();

        $savedNotes = [];
        foreach ($savedNotesQuery as $note) {
            $savedNotes[$note->objective_id] = [
                'kesimpulan' => $note->kesimpulan ?? '',
                'rekomendasi' => $note->rekomendasi ?? '',
                'roadmap_rekomendasi' => $note->roadmap_rekomendasi ?? null,
            ];
        }

        $maxLevels = [
            'EDM01' => 4, 'EDM02' => 5, 'EDM03' => 4, 'EDM04' => 4, 'EDM05' => 4,
            'APO01' => 5, 'APO02' => 4, 'APO03' => 5, 'APO04' => 4, 'APO05' => 5,
            'APO06' => 5, 'APO07' => 4, 'APO08' => 5, 'APO09' => 4, 'APO10' => 5,
            'APO11' => 5, 'APO12' => 5, 'APO13' => 5, 'APO14' => 5,
            'BAI01' => 5, 'BAI02' => 4, 'BAI03' => 4, 'BAI04' => 5, 'BAI05' => 5,
            'BAI06' => 4, 'BAI07' => 5, 'BAI08' => 5, 'BAI09' => 5, 'BAI10' => 5, 'BAI11' => 4,
            'DSS01' => 5, 'DSS02' => 5, 'DSS03' => 5, 'DSS04' => 5, 'DSS05' => 4, 'DSS06' => 5,
            'MEA01' => 5, 'MEA02' => 5, 'MEA03' => 5, 'MEA04' => 4,
        ];

        if ($objectiveId && isset($maxLevels[$objectiveId])) {
            $maxLevels = [$objectiveId => $maxLevels[$objectiveId]];
        } elseif ($objectiveId) {
            $maxLevels = [];
        }

        $mappedEvidence = TrsSummaryActivity::with('evidence')
            ->whereHas('activityEval', fn ($query) => $query->where('eval_id', $evalId))
            ->get()
            ->groupBy('activityeval_id');

        $targetCapabilityMap = $this->evaluationService->fetchTargetCapabilities($evaluation);
        $ratingMap = ['N' => 0.0, 'P' => 1.0 / 3.0, 'L' => 2.0 / 3.0, 'F' => 1.0];

        $objectives->map(function ($objective) use ($objectiveScores, $maxLevels, $mappedEvidence, $ratingMap, $targetCapabilityMap, $savedNotes, $evalId) {
            $currentLevel = $objectiveScores[$objective->objective_id] ?? 0;
            $objective->current_score = $currentLevel;
            $objective->max_level = $maxLevels[$objective->objective_id] ?? 0;
            $objective->saved_note = $savedNotes[$objective->objective_id] ?? [
                'kesimpulan' => '',
                'rekomendasi' => '',
                'roadmap_rekomendasi' => null,
            ];
            $objective->rating_string = $this->calculateRatingString($objective, $currentLevel, $ratingMap, $evalId);
            $objective->target_level = $targetCapabilityMap[$objective->objective_id] ?? 0;

            $objectiveEvidenceKeys = [];
            $objectivePolicyList = [];
            $objectiveExecutionList = [];

            foreach ($objective->practices as $practice) {
                $practicePolicyList = [];
                $practiceExecutionList = [];
                $practice->hasAnyActivity = $practice->activities->isNotEmpty();

                foreach ($practice->activities as $activity) {
                    $activityPolicyList = [];
                    $activityExecutionList = [];
                    $evalData = $activity->evaluations->first();

                    if ($evalData) {
                        $activityMappedEvidence = $mappedEvidence[$evalData->id] ?? collect();

                        foreach ($activityMappedEvidence as $mappedItem) {
                            $evidenceName = $mappedItem->evidence_name;
                            if (! $evidenceName) {
                                continue;
                            }

                            $isPolicy = $this->isPolicyEvidenceType($mappedItem->evidence_type);

                            if ($isPolicy) {
                                $activityPolicyList[] = $evidenceName;
                                $practicePolicyList[] = $evidenceName;
                            } else {
                                $activityExecutionList[] = $evidenceName;
                                $practiceExecutionList[] = $evidenceName;
                            }

                            $normalizedName = strtolower(trim($evidenceName));
                            if (in_array($normalizedName, $objectiveEvidenceKeys, true)) {
                                continue;
                            }

                            $objectiveEvidenceKeys[] = $normalizedName;
                            if ($isPolicy) {
                                $objectivePolicyList[] = $evidenceName;
                            } else {
                                $objectiveExecutionList[] = $evidenceName;
                            }
                        }
                    }

                    $activity->policy_list = $activityPolicyList;
                    $activity->execution_list = $activityExecutionList;
                    $activity->has_evidence = $activityPolicyList !== [] || $activityExecutionList !== [];
                    $activity->unsetRelation('evaluations');
                }

                $practice->policy_list = array_values(array_unique($practicePolicyList));
                $practice->execution_list = array_values(array_unique($practiceExecutionList));
                $practice->has_evidence = $practice->policy_list !== [] || $practice->execution_list !== [];
            }

            $objective->policy_list = $objectivePolicyList;
            $objective->execution_list = $objectiveExecutionList;
            $objective->has_evidence = $objectivePolicyList !== [] || $objectiveExecutionList !== [];
            $objective->filled_evidence_count = $objective->has_evidence ? 1 : 0;

            return $objective;
        });

        return compact('evaluation', 'objectives', 'targetCapabilityMap');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function saveNote(MstEval $evaluation, array $validated): TrsSummaryReport
    {
        $roadmapRekomendasi = null;
        $roadmapInput = $validated['roadmap_rekomendasi'] ?? null;

        if (is_array($roadmapInput)) {
            $roadmapRekomendasi = $roadmapInput;
        } elseif (is_string($roadmapInput) && $roadmapInput !== '') {
            $decoded = json_decode($roadmapInput, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $roadmapRekomendasi = $decoded;
            }
        }

        return TrsSummaryReport::updateOrCreate(
            [
                'eval_id' => $evaluation->eval_id,
                'objective_id' => $validated['objective_id'],
            ],
            [
                'kesimpulan' => $validated['kesimpulan'] ?? null,
                'rekomendasi' => $validated['rekomendasi'] ?? null,
                'roadmap_rekomendasi' => $roadmapRekomendasi,
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function getNotesPageData(MstEval $evaluation): array
    {
        $reports = TrsSummaryReport::where('eval_id', $evaluation->eval_id)
            ->orderByRaw("
                CASE
                    WHEN objective_id LIKE 'EDM%' THEN 1
                    WHEN objective_id LIKE 'APO%' THEN 2
                    WHEN objective_id LIKE 'BAI%' THEN 3
                    WHEN objective_id LIKE 'DSS%' THEN 4
                    WHEN objective_id LIKE 'MEA%' THEN 5
                    ELSE 6
                END,
                objective_id
            ")
            ->get();

        return [
            'reports' => $reports,
            'objectives' => MstObjective::pluck('objective', 'objective_id'),
            'evalId' => $evaluation->eval_id,
            'evaluation' => $evaluation,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getRoadmapTargetCapability(?string $objectiveId = null): array
    {
        $objectivesQuery = MstObjective::orderByRaw("FIELD(SUBSTRING(objective_id, 1, 3), 'EDM', 'APO', 'BAI', 'DSS', 'MEA')")
            ->orderBy('objective_id');

        if ($objectiveId) {
            $objectivesQuery->where('objective_id', $objectiveId);
        }

        $objectives = $objectivesQuery->get();

        $roadmapsQuery = TrsRoadmap::query();
        if ($objectiveId) {
            $roadmapsQuery->where('objective_id', $objectiveId);
        }
        $roadmaps = $roadmapsQuery->get();

        $mappedRoadmaps = [];
        $availableYears = [];
        foreach ($roadmaps as $roadmap) {
            $mappedRoadmaps[$roadmap->objective_id][$roadmap->year] = [
                'level' => $roadmap->level,
                'rating' => $roadmap->rating,
            ];
            $availableYears[] = (int) $roadmap->year;
        }

        $years = array_values(array_unique($availableYears));
        sort($years);

        foreach ($objectives as $objective) {
            $objective->roadmap_values = $mappedRoadmaps[$objective->objective_id] ?? [];
        }

        return compact('objectives', 'years');
    }

    private function calculateRatingString($objective, int $finalLevel, array $ratingMap, int $evalId): string
    {
        if ($finalLevel === 0) {
            return '0N';
        }

        $activitiesByLevel = [2 => [], 3 => [], 4 => [], 5 => []];
        foreach ($objective->practices as $practice) {
            foreach ($practice->activities as $activity) {
                $level = (int) ($activity->capability_lvl ?? $activity->capability_level ?? 0);
                if ($level >= 2 && $level <= 5) {
                    $activitiesByLevel[$level][] = $activity;
                }
            }
        }

        $levelToCheck = max(2, $finalLevel);
        $activities = $activitiesByLevel[$levelToCheck] ?? [];
        if ($activities === []) {
            return $finalLevel.'F';
        }

        $totalScore = 0.0;
        foreach ($activities as $activity) {
            $activityEval = TrsActivityeval::where('eval_id', $evalId)
                ->where('activity_id', $activity->activity_id)
                ->first();

            $rating = $activityEval?->level_achieved ?? 'N';
            $totalScore += $ratingMap[$rating] ?? 0.0;
        }

        $averageScore = $totalScore / count($activities);

        if ($averageScore > 0.85) {
            return $finalLevel.'F';
        }
        if ($averageScore > 0.50) {
            return $finalLevel.'L';
        }
        if ($averageScore > 0.15) {
            return $finalLevel.'P';
        }

        return $finalLevel.'N';
    }

    private function isPolicyEvidenceType(?string $type): bool
    {
        return $type !== null
            && (stripos($type, 'Design') !== false || stripos($type, 'Procedure') !== false);
    }
}
