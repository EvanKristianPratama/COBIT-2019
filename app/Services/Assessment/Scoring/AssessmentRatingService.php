<?php

namespace App\Services\Assessment\Scoring;

class AssessmentRatingService
{
    public const SCORE_THRESHOLD_PARTIAL = 0.15;

    public const SCORE_THRESHOLD_LARGELY = 0.50;

    public const SCORE_THRESHOLD_FULLY = 0.85;

    /**
     * Convert score to letter grade
     */
    public function scoreToLetter($score)
    {
        if ($score > 0.85) {
            return 'F';
        }
        if ($score > 0.50) {
            return 'L';
        }
        if ($score > 0.15) {
            return 'P';
        }

        return 'N';
    }

    /**
     * Convert letter grade to score (for loading data)
     */
    public function letterToScore($letter)
    {
        $scoreMap = [
            'N' => 0.00,
            'P' => 1 / 3,
            'L' => 2 / 3,
            'F' => 1.00,
        ];

        return $scoreMap[$letter] ?? 0.00;
    }

    public function getScoreLetter(float $score): string
    {
        if ($score > self::SCORE_THRESHOLD_FULLY) {
            return 'F';
        }
        if ($score > self::SCORE_THRESHOLD_LARGELY) {
            return 'L';
        }
        if ($score > self::SCORE_THRESHOLD_PARTIAL) {
            return 'P';
        }

        return 'N';
    }

    public function isAchievedScore(float $score): bool
    {
        return $score > self::SCORE_THRESHOLD_PARTIAL;
    }

    public function getRatingNumericValue(?string $rating): float
    {
        return match ($rating) {
            'F' => 1.0,
            'L' => 2.0 / 3.0,
            'P' => 1.0 / 3.0,
            default => 0.0,
        };
    }

    public function extractAchievedRating($activityData, string $activityId): string
    {
        if ($activityData instanceof \Illuminate\Support\Collection) {
            $activity = $activityData->get($activityId);

            return (string) ($activity?->level_achieved ?? 'N');
        }

        if (is_array($activityData) && array_key_exists($activityId, $activityData)) {
            $activity = $activityData[$activityId];

            if (is_array($activity)) {
                return (string) ($activity['level_achieved'] ?? 'N');
            }

            if (is_object($activity)) {
                return (string) ($activity->level_achieved ?? 'N');
            }
        }

        if ($activityData instanceof \ArrayAccess && isset($activityData[$activityId])) {
            return (string) ($activityData[$activityId]['level_achieved'] ?? 'N');
        }

        return 'N';
    }
}
