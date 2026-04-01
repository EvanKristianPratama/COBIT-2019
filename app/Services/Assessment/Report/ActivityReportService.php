<?php

namespace App\Services\Assessment\Report;

use App\Models\MstEval;
use App\Models\MstEvidence;
use App\Models\MstObjective;
use App\Models\TrsActivityeval;
use App\Models\TrsDomain;
use App\Models\TrsObjectiveScore;
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
        $organization = $owner?->organisasi ?? 'N/A';

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

        $objectiveScore = TrsObjectiveScore::where('eval_id', $evalId)
            ->where('objective_id', $objectiveId)
            ->first();
        $currentLevel = $objectiveScore ? $objectiveScore->level : 0;

        $targetCapabilityMap = $this->evaluationService->fetchTargetCapabilities($evaluation);
        $targetLevel = $targetCapabilityMap[$objectiveId] ?? null;
        $ratingString = $this->calculateRatingString($objective, $currentLevel, $evalId);
        $evalEvidences = MstEvidence::where('eval_id', $evalId)->get()->keyBy('id');

        $activityData = [];
        foreach ($objective->practices as $practice) {
            foreach ($practice->activities as $activity) {
                $capabilityLevel = $activity->capability_lvl ?? $activity->capability_level ?? '';
                if ($filterLevel && (string) $capabilityLevel !== (string) $filterLevel) {
                    continue;
                }

                $activityEval = TrsActivityeval::where('eval_id', $evalId)
                    ->where('activity_id', $activity->activity_id)
                    ->first();

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

        $ratingMap = ['N' => 0.0, 'P' => 1.0 / 3.0, 'L' => 2.0 / 3.0, 'F' => 1.0];
        $levelRatings = [];
        $activitiesByLevel = collect($activityData)->groupBy('capability_level');

        foreach ($activitiesByLevel as $level => $activities) {
            $totalScore = 0;
            $count = 0;

            foreach ($activities as $activity) {
                $totalScore += $ratingMap[$activity['answer']] ?? 0;
                $count++;
            }

            if ($count > 0) {
                $averageScore = $totalScore / $count;
                $letter = 'N';

                if ($averageScore > 0.85) {
                    $letter = 'F';
                } elseif ($averageScore > 0.50) {
                    $letter = 'L';
                } elseif ($averageScore > 0.15) {
                    $letter = 'P';
                }

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
            'levelRatings'
        );
    }

    private function calculateRatingString($objective, $finalLevel, int $evalId): string
    {
        if ((int) $finalLevel === 0) {
            return '0N';
        }

        $ratingMap = ['N' => 0.0, 'P' => 1.0 / 3.0, 'L' => 2.0 / 3.0, 'F' => 1.0];
        $activitiesByLevel = [2 => [], 3 => [], 4 => [], 5 => []];

        foreach ($objective->practices as $practice) {
            foreach ($practice->activities as $activity) {
                $level = (int) ($activity->capability_lvl ?? $activity->capability_level ?? 0);
                if ($level >= 2 && $level <= 5) {
                    $activitiesByLevel[$level][] = $activity;
                }
            }
        }

        $levelToCheck = max(2, (int) $finalLevel);
        $activities = $activitiesByLevel[$levelToCheck] ?? [];
        if ($activities === []) {
            return $finalLevel.'F';
        }

        $totalScore = 0;
        $count = 0;
        foreach ($activities as $activity) {
            $activityEval = TrsActivityeval::where('eval_id', $evalId)
                ->where('activity_id', $activity->activity_id)
                ->first();

            $rating = $activityEval ? $activityEval->level_achieved : 'N';
            $totalScore += $ratingMap[$rating] ?? 0;
            $count++;
        }

        if ($count === 0) {
            return $finalLevel.'F';
        }

        $averageScore = $totalScore / $count;
        $letter = 'N';

        if ($averageScore > 0.85) {
            $letter = 'F';
        } elseif ($averageScore > 0.50) {
            $letter = 'L';
        } elseif ($averageScore > 0.15) {
            $letter = 'P';
        }

        return $finalLevel.$letter;
    }
}
