<?php

namespace App\Services\Assessment\Scoring;

use App\Models\MstEval;
use App\Services\Assessment\Eval\EvaluationReadService;

class MaturityScoreService
{
    public function __construct(
        private readonly EvaluationReadService $evaluationReader
    ) {}

    /**
     * Calculate the overall IT Maturity Score for an evaluation
     * Logic exactly matches the AssessmentEvalController::report method
     */
    public function calculateMaturityScore($evalId)
    {
        $evaluation = MstEval::with([
            'activityEvaluations.activity.practice.objective',
        ])->find($evalId);

        if (! $evaluation) {
            return 0;
        }

        $formattedData = $this->evaluationReader->loadEvaluation($evalId);
        $activityData = $formattedData['activity_evaluations'] ?? [];

        // Determine selected domains/objectives based on filled activities
        $relevantActivityIds = array_keys($activityData);
        if (empty($relevantActivityIds)) {
            return 0;
        }

        $eval = \App\Models\MstEval::find($evalId);
        $focusAreaId = $eval?->focus_area_id ?: 1;

        // Fetch objectives that are relevant to this evaluation (based on activities)
        // This ensures we don't average in 0s for objectives that weren't selected
        $objectives = \App\Models\MstObjective::where('focus_area_id', $focusAreaId)
            ->whereHas('practices.activities', function ($q) use ($relevantActivityIds) {
                $q->whereIn('activity_id', $relevantActivityIds);
            })->with(['practices.activities'])->get();

        if ($objectives->isEmpty()) {
            return 0;
        }

        $ratingMap = ['N' => 0.0, 'P' => 1.0 / 3.0, 'L' => 2.0 / 3.0, 'F' => 1.0];
        $calculatedLevels = [];

        foreach ($objectives as $obj) {
            $activitiesByLevel = [2 => [], 3 => [], 4 => [], 5 => []];
            $allLevelsFound = [];

            foreach ($obj->practices as $p) {
                if ($p->activities) {
                    foreach ($p->activities as $a) {
                        $lvl = (int) $a->capability_lvl;
                        if ($lvl >= 2 && $lvl <= 5) {
                            $activitiesByLevel[$lvl][] = $a;
                            $allLevelsFound[] = $lvl;
                        }
                    }
                }
            }

            if (empty($allLevelsFound)) {
                $calculatedLevels[] = 0;

                continue;
            }

            $minLevel = min($allLevelsFound);

            $getScore = function ($lvl) use ($minLevel, $activitiesByLevel, $activityData, $ratingMap) {
                if ($lvl < $minLevel) {
                    return 1.0;
                }

                $acts = $activitiesByLevel[$lvl] ?? [];
                if (empty($acts)) {
                    return 0.0;
                }

                $vals = 0;
                foreach ($acts as $a) {
                    $r = $activityData[$a->activity_id]['level_achieved'] ?? 'N';
                    $vals += ($ratingMap[$r] ?? 0.0);
                }

                return $vals / count($acts);
            };

            $score2 = $getScore(2);
            $score3 = $getScore(3);
            $score4 = $getScore(4);
            $score5 = $getScore(5);

            $finalLevel = 0;
            if ($score2 <= 0.15) {
                $finalLevel = 0;
            } elseif ($score2 <= 0.50) {
                $finalLevel = 1;
            } elseif ($score2 <= 0.85) {
                $finalLevel = 2;
            } else {
                if ($score3 <= 0.50) {
                    $finalLevel = 2;
                } elseif ($score3 <= 0.85) {
                    $finalLevel = 3;
                } else {
                    if ($score4 <= 0.50) {
                        $finalLevel = 3;
                    } elseif ($score4 <= 0.85) {
                        $finalLevel = 4;
                    } else {
                        if ($score5 <= 0.50) {
                            $finalLevel = 4;
                        } else {
                            $finalLevel = 5;
                        }
                    }
                }
            }

            if ($minLevel > 2) {
                $startScore = $getScore($minLevel);
                if ($startScore <= 0.15) {
                    $finalLevel = 0;
                }
            }

            $calculatedLevels[] = $finalLevel;
        }

        if (empty($calculatedLevels)) {
            return 0;
        }

        // Return average of all objective scores
        return array_sum($calculatedLevels) / count($calculatedLevels);
    }
}
