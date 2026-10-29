<?php

namespace App\Services\Assessment\Eval;

use App\Services\Assessment\Scoring\AssessmentRatingService;

class AssessmentDataConverter
{
    public function __construct(
        private readonly AssessmentRatingService $ratingService
    ) {}

    /**
     * Convert frontend assessment data to database format
     */
    public function convertAssessmentData($assessmentData)
    {
        $activityEvaluations = [];
        $processedActivityIds = [];

        if (isset($assessmentData['assessmentData'])) {
            $levelScores = $assessmentData['assessmentData'];
            $notes = $assessmentData['notes'] ?? [];
            $evidence = $assessmentData['evidence'] ?? [];
            $evidenceNames = $assessmentData['evidenceNames'] ?? [];

            foreach ($levelScores as $objectiveId => $levels) {
                foreach ($levels as $level => $levelData) {
                    if (isset($levelData['activities'])) {
                        foreach ($levelData['activities'] as $activityId => $score) {
                            $levelAchieved = $this->ratingService->scoreToLetter($score);

                            $activityEvaluations[] = [
                                'activity_id' => $activityId,
                                'level_achieved' => $levelAchieved,
                                'evidence' => $evidence[$activityId] ?? null,
                                'evidence_names' => $evidenceNames[$activityId] ?? null,
                                'notes' => $notes[$activityId] ?? null,
                            ];
                            $processedActivityIds[$activityId] = true;
                        }
                    }
                }
            }

            // check for activities with notes/evidence but no rating
            $otherIds = array_unique(array_merge(array_keys($notes), array_keys($evidence)));
            foreach ($otherIds as $actId) {
                if (! isset($processedActivityIds[$actId])) {
                    $activityEvaluations[] = [
                        'activity_id' => $actId,
                        'level_achieved' => null, // Allow null for unrated activities
                        'evidence' => $evidence[$actId] ?? null,
                        'evidence_names' => $evidenceNames[$actId] ?? null,
                        'notes' => $notes[$actId] ?? null,
                    ];
                }
            }
        } else {
            foreach ($assessmentData as $level => $levelData) {
                if (isset($levelData['activities'])) {
                    foreach ($levelData['activities'] as $activityId => $score) {
                        $levelAchieved = $this->ratingService->scoreToLetter($score);

                        // Include all activities, even those rated as 'N'
                        // This preserves evidence and notes when levels are changed
                        $activityEvaluations[] = [
                            'activity_id' => $activityId,
                            'level_achieved' => $levelAchieved,
                            'evidence' => $levelData['evidence'][$activityId] ?? null,
                            'notes' => $levelData['notes'][$activityId] ?? null,
                        ];
                    }
                }
            }
        }

        return [
            'activity_evaluations' => $activityEvaluations,
        ];
    }
}
