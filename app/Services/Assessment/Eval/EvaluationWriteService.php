<?php

namespace App\Services\Assessment\Eval;

use App\Models\MstEval;
use App\Models\MstEvidence;
use App\Models\TrsActivityeval;
use App\Models\TrsEvalDetail;
use App\Models\TrsMaturityScore;
use App\Models\TrsObjectiveScore;
use App\Models\TrsScoping;
use App\Models\TrsSummaryReport;
use App\Models\User;
use App\Services\Assessment\Scoring\CalculatedScoreService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EvaluationWriteService
{
    public function __construct(
        private readonly ActivityEvaluationService $activityEvaluations,
        private readonly CalculatedScoreService $calculatedScores
    ) {}

    /**
     * Create a new evaluation for a user
     */
    public function createNewEvaluation($userId, ?int $organizationId = null)
    {
        try {
            DB::beginTransaction();

            $evaluation = MstEval::create([
                'focus_area_id' => 1,
                'user_id' => $userId,
                'organization_id' => $organizationId ?? (User::find($userId)?->activeOrganizationId() ?? User::where('id', $userId)->value('organization_id')),
            ]);

            DB::commit();

            // refresh model to ensure any DB-generated fields are loaded
            try {
                $evaluation->refresh();
            } catch (\Exception $e) {
                // ignore refresh failures, return created model anyway
            }

            return $evaluation;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create new evaluation', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Save evaluation data from the assessment form
     */
    public function saveEvaluation($data)
    {
        try {
            DB::beginTransaction();

            $existingEvaluation = isset($data['eval_id'])
                ? MstEval::query()->find($data['eval_id'])
                : null;

            $userId = $data['user_id']
                ?? $existingEvaluation?->user_id
                ?? Auth::id();

            $organizationId = $data['organization_id']
                ?? $existingEvaluation?->organization_id
                ?? User::where('id', $userId)->value('organization_id');

            $evaluation = MstEval::updateOrCreate(
                [
                    'eval_id' => $data['eval_id'] ?? null,
                ],
                [
                    'user_id' => $userId,
                    'organization_id' => $organizationId,
                ]
            );

            if (isset($data['activity_evaluations'])) {
                $this->activityEvaluations->sync($evaluation, $data['activity_evaluations']);
            }

            // Calculate and save maturity scores to database
            $this->calculatedScores->updateCalculatedScores($evaluation->eval_id);

            DB::commit();

            return $evaluation;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete an evaluation
     */
    public function deleteEvaluation($evalId)
    {
        try {
            DB::beginTransaction();

            $evaluation = MstEval::findOrFail($evalId);

            $this->activityEvaluations->purgeActivityEvaluations(
                TrsActivityeval::withTrashed()->where('eval_id', $evalId)
            );

            TrsSummaryReport::where('eval_id', $evalId)->delete();
            TrsObjectiveScore::where('eval_id', $evalId)->delete();
            TrsMaturityScore::where('eval_id', $evalId)->delete();
            TrsEvalDetail::where('eval_id', $evalId)->delete();
            TrsScoping::withTrashed()->where('eval_id', $evalId)->forceDelete();
            MstEvidence::withTrashed()->where('eval_id', $evalId)->forceDelete();

            $evaluation->forceDelete();

            DB::commit();

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
