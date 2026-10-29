<?php

namespace App\Services\Assessment\Eval;

use App\Models\MstEval;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EvaluationReadService
{
    /**
     * Load evaluation data for the assessment form
     */
    public function loadEvaluation($evalId)
    {
        $evaluation = MstEval::with([
            'activityEvaluations.activity.practice.objective',
        ])->findOrFail($evalId);

        $formattedData = [
            'eval_id' => $evaluation->eval_id,
            'user_id' => $evaluation->user_id,
            'created_at' => $evaluation->created_at,
            'updated_at' => $evaluation->updated_at,
            'activity_evaluations' => [],
        ];

        foreach ($evaluation->activityEvaluations as $activityEval) {
            $activity = $activityEval->activity;
            $practice = $activity->practice;
            $objective = $practice ? $practice->objective : null;

            $formattedData['activity_evaluations'][$activityEval->activity_id] = [
                'activity_id' => $activityEval->activity_id,
                'level_achieved' => $activityEval->level_achieved,
                'evidence' => $activityEval->evidence,
                'notes' => $activityEval->notes,
                'capability_lvl' => $activity->capability_lvl ?? null,
                'objective_id' => $objective ? $objective->objective_id : null,
            ];
        }

        return $formattedData;
    }

    /**
     * Get an evaluation by ID
     */
    public function getEvaluationById($evalId)
    {
        try {
            // coba cari by eval_id (custom) lalu fallback ke primary key
            $evaluation = MstEval::where('eval_id', $evalId)->first();
            if (! $evaluation && is_numeric($evalId)) {
                $evaluation = MstEval::find($evalId);
            }

            return $evaluation;
        } catch (\Exception $e) {
            Log::error('Failed to get evaluation by ID', [
                'eval_id' => $evalId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get all evaluations for a user
     */
    public function getUserEvaluations($userId = null)
    {
        $userId = $userId ?? Auth::id();

        return MstEval::with('activityEvaluations')
            ->where('user_id', $userId)
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    /**
     * Get basic evaluation statistics
     */
    public function getEvaluationStats($evalId)
    {
        $evaluation = MstEval::with('activityEvaluations')->findOrFail($evalId);

        $totalActivities = $evaluation->activityEvaluations->count();
        $achievementCounts = $evaluation->activityEvaluations
            ->groupBy('level_achieved')
            ->map->count();

        return [
            'total_activities' => $totalActivities,
            'achievement_distribution' => [
                'N' => $achievementCounts['N'] ?? 0,
                'P' => $achievementCounts['P'] ?? 0,
                'L' => $achievementCounts['L'] ?? 0,
                'F' => $achievementCounts['F'] ?? 0,
            ],
        ];
    }
}
