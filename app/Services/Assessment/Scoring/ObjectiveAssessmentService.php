<?php

namespace App\Services\Assessment\Scoring;

class ObjectiveAssessmentService
{
    public function __construct(
        private readonly AssessmentRatingService $ratingService
    ) {}

    /**
     * Helper: Calculate maturity for a single objective
     */
    public function calculateObjectiveMaturity($obj, $activityData)
    {
        return $this->calculateObjectiveAssessmentMetrics($obj, $activityData)['final_level'];
    }

    /**
     * @return array{
     *     final_level:int,
     *     final_score:float,
     *     progress_level:int,
     *     progress_score:float,
     *     rating_letter:string,
     *     rating_string:string,
     *     display_value:float,
     *     display_value_label:string,
     *     level_scores:array<int, array{score:float, letter:string}>
     * }
     */
    public function calculateObjectiveAssessmentMetrics($obj, $activityData): array
    {
        $activitiesByLevel = [2 => [], 3 => [], 4 => [], 5 => []];
        $allLevelsFound = [];

        foreach ($obj->practices as $p) {
            if ($p->activities) {
                foreach ($p->activities as $a) {
                    $lvl = (int) ($a->capability_lvl ?? $a->capability_level ?? 0);
                    if ($lvl >= 2 && $lvl <= 5) {
                        $activitiesByLevel[$lvl][] = $a;
                        $allLevelsFound[] = $lvl;
                    }
                }
            }
        }

        if (empty($allLevelsFound)) {
            return [
                'final_level' => 0,
                'final_score' => 0.0,
                'progress_level' => 0,
                'progress_score' => 0.0,
                'rating_letter' => 'N',
                'rating_string' => '0N',
                'display_value' => 0.0,
                'display_value_label' => '0.00',
                'level_scores' => [],
            ];
        }

        $minLevel = min($allLevelsFound);

        $getScore = function ($lvl) use ($activitiesByLevel, $activityData) {
            $acts = $activitiesByLevel[$lvl] ?? [];
            if (empty($acts)) {
                return 0.0;
            }
            $vals = 0;
            foreach ($acts as $a) {
                $vals += $this->ratingService->getRatingNumericValue($this->ratingService->extractAchievedRating($activityData, $a->activity_id));
            }

            return $vals / count($acts);
        };

        $levelScores = [];
        foreach (range($minLevel, 5) as $level) {
            $score = $getScore($level);
            $levelScores[$level] = [
                'score' => $score,
                'letter' => $this->ratingService->getScoreLetter($score),
            ];
        }

        $progressLevel = 0;
        $progressScore = 0.0;
        for ($level = 5; $level >= $minLevel; $level--) {
            $score = $levelScores[$level]['score'] ?? 0.0;
            if ($this->ratingService->isAchievedScore($score)) {
                $progressLevel = $level;
                $progressScore = $score;
                break;
            }
        }

        $getCapabilityScore = function (int $level) use ($levelScores, $minLevel): float {
            if ($level < $minLevel) {
                return 1.0;
            }

            return $levelScores[$level]['score'] ?? 0.0;
        };

        $score2 = $getCapabilityScore(2);
        $score3 = $getCapabilityScore(3);
        $score4 = $getCapabilityScore(4);
        $score5 = $getCapabilityScore(5);

        $finalLevel = 0;
        if ($score2 <= AssessmentRatingService::SCORE_THRESHOLD_PARTIAL) {
            $finalLevel = 0;
        } elseif ($score2 <= AssessmentRatingService::SCORE_THRESHOLD_LARGELY) {
            $finalLevel = 1;
        } elseif ($score2 <= AssessmentRatingService::SCORE_THRESHOLD_FULLY) {
            $finalLevel = 2;
        } else {
            if ($score3 <= AssessmentRatingService::SCORE_THRESHOLD_LARGELY) {
                $finalLevel = 2;
            } elseif ($score3 <= AssessmentRatingService::SCORE_THRESHOLD_FULLY) {
                $finalLevel = 3;
            } else {
                if ($score4 <= AssessmentRatingService::SCORE_THRESHOLD_LARGELY) {
                    $finalLevel = 3;
                } elseif ($score4 <= AssessmentRatingService::SCORE_THRESHOLD_FULLY) {
                    $finalLevel = 4;
                } else {
                    $finalLevel = $score5 <= AssessmentRatingService::SCORE_THRESHOLD_LARGELY ? 4 : 5;
                }
            }
        }

        if ($minLevel > 2 && ($levelScores[$minLevel]['score'] ?? 0.0) <= AssessmentRatingService::SCORE_THRESHOLD_PARTIAL) {
            $finalLevel = 0;
        }

        $ratingSourceLevel = $finalLevel > 0 ? max(2, $finalLevel) : null;
        if ($ratingSourceLevel !== null && ! isset($levelScores[$ratingSourceLevel])) {
            $ratingSourceLevel = $minLevel;
        }

        $finalScore = $ratingSourceLevel !== null ? ($levelScores[$ratingSourceLevel]['score'] ?? 0.0) : 0.0;
        $ratingLetter = $progressLevel > 0 ? $this->ratingService->getScoreLetter($progressScore) : 'N';
        $displayValue = $progressLevel > 0 ? round(($progressLevel - 1) + $progressScore, 2) : 0.0;

        return [
            'final_level' => $finalLevel,
            'final_score' => $finalScore,
            'progress_level' => $progressLevel,
            'progress_score' => $progressScore,
            'rating_letter' => $ratingLetter,
            'rating_string' => $progressLevel > 0 ? $progressLevel.$ratingLetter : '0N',
            'display_value' => $displayValue,
            'display_value_label' => number_format($displayValue, 2, '.', ''),
            'level_scores' => $levelScores,
        ];
    }
}
