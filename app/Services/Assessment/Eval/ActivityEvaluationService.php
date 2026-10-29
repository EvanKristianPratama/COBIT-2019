<?php

namespace App\Services\Assessment\Eval;

use App\Models\MstActivities;
use App\Models\MstEval;
use App\Models\MstEvidence;
use App\Models\TrsActivityeval;
use App\Models\TrsSummaryActivity;
use App\Models\TrsSummaryReport;

class ActivityEvaluationService
{
    public function sync(MstEval $evaluation, array $activityEvaluations): void
    {
        // Get all activity IDs from the request
        $incomingActivityIds = collect($activityEvaluations)->pluck('activity_id')->toArray();

        // Pre-fetch Map: Activity ID -> Objective ID
        // Optimizes query to avoid N+1 inside loop
        $activityObjectiveMap = MstActivities::with('practice')
            ->whereIn('activity_id', $incomingActivityIds)
            ->get()
            ->mapWithKeys(function ($act) {
                return [$act->activity_id => $act->practice->objective_id ?? null];
            });

        $evidenceMap = MstEvidence::where('eval_id', $evaluation->eval_id)
            ->get()
            ->mapWithKeys(function ($item) {
                return [strtolower(trim($item->judul_dokumen)) => $item->id];
            });

        // Delete activities that are not in the incoming data (completely removed from form)
        $this->purgeActivityEvaluations(
            TrsActivityeval::withTrashed()
                ->where('eval_id', $evaluation->eval_id)
                ->whereNotIn('activity_id', $incomingActivityIds)
        );

        foreach ($activityEvaluations as $activityData) {
            // Always save or update the activity, even if rated as 'N'
            // This preserves evidence and notes when levels are changed
            $activityEval = TrsActivityeval::updateOrCreate(
                [
                    'eval_id' => $evaluation->eval_id,
                    'activity_id' => $activityData['activity_id'],
                ],
                [
                    'level_achieved' => $activityData['level_achieved'],
                    'evidence' => $activityData['evidence'] ?? null,
                    'notes' => $activityData['notes'] ?? null,
                ]
            );

            // 1. Resolve Objective ID for this activity
            $objectiveId = $activityObjectiveMap[$activityData['activity_id']] ?? null;

            if ($objectiveId) {
                // 2. Ensure Summary Report exists (parent container)
                $summaryReport = TrsSummaryReport::firstOrCreate(
                    [
                        'eval_id' => $evaluation->eval_id,
                        'objective_id' => $objectiveId,
                    ]
                );

                // 3. Sync Evidence Mapping
                // First, clear existing mappings for this specific activity evaluation to avoid duplicates
                TrsSummaryActivity::where('activityeval_id', $activityEval->id)->delete();

                if (! empty($activityData['evidence'])) {
                    // Priority 1: Use explicitly provided evidence names (Array from Frontend)
                    if (! empty($activityData['evidence_names']) && is_array($activityData['evidence_names'])) {
                        $files = $activityData['evidence_names'];
                    }
                    // Priority 2: Fallback to parsing the evidence string (Legacy/Backup)
                    else {
                        $files = preg_split('/\r\n|\r|\n/', $activityData['evidence']);
                    }

                    foreach ($files as $file) {
                        $filename = trim($file);
                        if (empty($filename)) {
                            continue;
                        }

                        // Lookup Evidence ID (Normalized)
                        $normalizedName = strtolower($filename);
                        $evidenceId = $evidenceMap[$normalizedName] ?? null;

                        // Link evidence, save miss_evidence when evidence not found
                        TrsSummaryActivity::create([
                            'summary_id' => $summaryReport->id,
                            'activityeval_id' => $activityEval->id,
                            'evidence_id' => $evidenceId,
                            'miss_evidence' => $evidenceId === null ? $filename : null,
                        ]);
                    }
                }
            }
        }
    }

    public function purgeActivityEvaluations($query): void
    {
        $activityEvalIds = (clone $query)->pluck('id');

        if ($activityEvalIds->isEmpty()) {
            return;
        }

        TrsSummaryActivity::whereIn('activityeval_id', $activityEvalIds)->delete();
        $query->forceDelete();
    }
}
