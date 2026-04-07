<?php

namespace App\Services\Assessment\Report;

use App\Models\MstEval;
use App\Models\MstEvidence;
use App\Models\MstObjective;
use App\Models\TrsActivityeval;
use App\Models\TrsDomain;
use App\Services\EvaluationService;
use InvalidArgumentException;

class ActivityReportService
{
    public function __construct(
        private readonly EvaluationService $evaluationService
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(MstEval $evaluation, string $objectiveId, $filterLevel = null): array
    {
        $evalId = $evaluation->eval_id;
        $owner = $evaluation->relationLoaded('user') ? $evaluation->user : $evaluation->user()->first();
        $organization = $evaluation->relationLoaded('organization')
            ? $evaluation->organization?->organization_name
            : $evaluation->organization()->value('organization_name');

        if (! $organization) {
            $organization = $owner?->organisasi ?? 'N/A';
        }

        $objective = MstObjective::with(['practices.activities'])
            ->where('objective_id', $objectiveId)
            ->first();

        if (! $objective) {
            throw new InvalidArgumentException('Objective not found');
        }

        $areaObjective = TrsDomain::where('objective_id', $objectiveId)->first();

        $maxLevel = 0;
        foreach ($objective->practices as $practice) {
            foreach ($practice->activities as $activity) {
                $level = (int) ($activity->capability_lvl ?? $activity->capability_level ?? 0);
                if ($level > $maxLevel) {
                    $maxLevel = $level;
                }
            }
        }

        $evaluationActivityData = TrsActivityeval::where('eval_id', $evalId)
            ->get()
            ->keyBy('activity_id');
        $metrics = $this->evaluationService->calculateObjectiveAssessmentMetrics($objective, $evaluationActivityData);
        $currentLevel = $metrics['final_level'];

        $targetCapabilityMap = $this->evaluationService->fetchTargetCapabilities($evaluation);
        $targetLevel = $targetCapabilityMap[$objectiveId] ?? null;
        $ratingString = $metrics['rating_string'];
        $displayValue = $metrics['display_value_label'];
        $evalEvidences = MstEvidence::where('eval_id', $evalId)->get()->keyBy('id');

        $activityData = [];
        foreach ($objective->practices as $practice) {
            foreach ($practice->activities as $activity) {
                $capabilityLevel = $activity->capability_lvl ?? $activity->capability_level ?? '';
                if ($filterLevel && (string) $capabilityLevel !== (string) $filterLevel) {
                    continue;
                }

                $activityEval = $evaluationActivityData->get($activity->activity_id);

                if (! $activityEval || ! $activityEval->level_achieved) {
                    continue;
                }

                $evidenceDisplay = [];
                $rawEvidence = $activityEval->evidence;

                if ($rawEvidence) {
                    $decoded = json_decode($rawEvidence, true);
                    if (is_array($decoded)) {
                        foreach ($decoded as $evidenceItem) {
                            if (is_numeric($evidenceItem) && isset($evalEvidences[$evidenceItem])) {
                                $evidenceDisplay[] = $evalEvidences[$evidenceItem]->judul_dokumen ?? "Evidence #{$evidenceItem}";
                            } elseif (is_array($evidenceItem)) {
                                $evidenceDisplay[] = $evidenceItem['name'] ?? $evidenceItem['judul_dokumen'] ?? json_encode($evidenceItem);
                            } else {
                                $evidenceDisplay[] = (string) $evidenceItem;
                            }
                        }
                    } else {
                        $evidenceDisplay[] = $rawEvidence;
                    }
                }

                $activityData[] = [
                    'practice_id' => $practice->practice_id,
                    'practice_name' => $practice->practice_name,
                    'activity_id' => $activity->activity_id,
                    'activity_description' => $activity->description ?? $activity->activity ?? '',
                    'capability_level' => (string) $capabilityLevel,
                    'answer' => $activityEval->level_achieved,
                    'evidence' => $evidenceDisplay,
                    'notes' => $activityEval->notes,
                ];
            }
        }

        usort($activityData, function ($left, $right) {
            $levelLeft = (int) $left['capability_level'];
            $levelRight = (int) $right['capability_level'];

            if ($levelLeft !== $levelRight) {
                return $levelLeft <=> $levelRight;
            }

            $practiceComparison = strcmp($left['practice_id'], $right['practice_id']);
            if ($practiceComparison !== 0) {
                return $practiceComparison;
            }

            return strcmp($left['activity_id'], $right['activity_id']);
        });

        $levelRatings = [];
        $activitiesByLevel = collect($activityData)->groupBy('capability_level');

        foreach ($activitiesByLevel as $level => $activities) {
            $totalScore = 0;
            $count = 0;

            foreach ($activities as $activity) {
                $totalScore += $this->evaluationService->getRatingNumericValue($activity['answer']);
                $count++;
            }

            if ($count > 0) {
                $averageScore = $totalScore / $count;
                $letter = $this->evaluationService->getScoreLetter($averageScore);

                $levelRatings[$level] = [
                    'rating' => $level.$letter,
                    'score' => round($averageScore * 100, 1),
                    'count' => $count,
                ];
            } else {
                $levelRatings[$level] = [
                    'rating' => $level.'N',
                    'score' => 0,
                    'count' => 0,
                ];
            }
        }

        return compact(
            'evaluation',
            'evalId',
            'objective',
            'areaObjective',
            'activityData',
            'filterLevel',
            'currentLevel',
            'maxLevel',
            'organization',
            'targetLevel',
            'ratingString',
            'displayValue',
            'levelRatings'
        );
    }
}
