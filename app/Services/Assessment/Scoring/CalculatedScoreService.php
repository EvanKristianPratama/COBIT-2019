<?php

namespace App\Services\Assessment\Scoring;

use App\Models\MstEval;
use App\Models\TrsActivityeval;
use App\Models\TrsMaturityScore;
use App\Models\TrsObjectiveScore;

class CalculatedScoreService
{
    public function __construct(
        private readonly ObjectiveAssessmentService $objectiveAssessment
    ) {}

    /**
     * Calculate and update score tables (TrsObjectiveScore & TrsMaturityScore)
     */
    public function updateCalculatedScores($evalId)
    {
        $evaluation = MstEval::find($evalId);
        if (! $evaluation) {
            return;
        }

        $activityData = TrsActivityeval::where('eval_id', $evalId)->get()->keyBy('activity_id');

        // Clear existing scores for this evaluation to ensure clean state
        TrsObjectiveScore::where('eval_id', $evalId)->delete();
        TrsMaturityScore::where('eval_id', $evalId)->delete();

        // Use Scope (Selected Domains) to determine relevant objectives, EXACTLY like the report page
        // If we only look at "activities with data", we might miss 0-scored objectives that are part of the scope
        // causing the average to be higher (smaller divisor).

        $eval = \App\Models\MstEval::find($evalId);
        $focusAreaId = $eval?->focus_area_id ?: 1;

        $selectedDomains = \App\Models\TrsEvalDetail::where('eval_id', $evalId)
            ->whereHas('scoping')
            ->pluck('domain_id')
            ->unique()
            ->toArray();

        $objectivesQuery = \App\Models\MstObjective::where('focus_area_id', $focusAreaId)
            ->with(['practices.activities']);

        if (! empty($selectedDomains)) {
            $objectives = $objectivesQuery->where(function ($q) use ($selectedDomains) {
                foreach ($selectedDomains as $domain) {
                    $domain = trim((string) $domain);
                    if ($domain !== '') {
                        if (in_array(strtoupper($domain), ['EDM', 'APO', 'BAI', 'DSS', 'MEA'])) {
                            $q->orWhere('objective_id', 'like', $domain.'%');
                        } else {
                            $q->orWhere('objective_id', $domain);
                        }
                    }
                }
            })->get();
        } else {
            $objectives = $objectivesQuery->get();
        }

        if ($objectives->isEmpty()) {
            TrsMaturityScore::create(['eval_id' => $evalId, 'score' => 0]);

            return;
        }

        $calculatedLevels = [];

        foreach ($objectives as $obj) {
            $finalLevel = $this->objectiveAssessment->calculateObjectiveAssessmentMetrics($obj, $activityData)['final_level'];

            $calculatedLevels[] = $finalLevel;

            // Save objective score
            TrsObjectiveScore::create([
                'eval_id' => $evalId,
                'objective_id' => $obj->objective_id,
                'level' => $finalLevel,
            ]);
        }

        $avgScore = empty($calculatedLevels) ? 0 : (array_sum($calculatedLevels) / count($calculatedLevels));

        // Save overall maturity score
        TrsMaturityScore::create([
            'eval_id' => $evalId,
            'score' => $avgScore,
        ]);
    }
}
