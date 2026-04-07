<?php

namespace App\Services\Assessment\Eval;

use App\Models\MstEval;
use App\Models\MstEvidence;
use App\Models\MstObjective;
use App\Models\TrsEvalDetail;
use App\Models\TrsScoping;
use App\Services\Assessment\Scope\AssessmentScopeService;
use App\Services\EvaluationService;
use Illuminate\Support\Facades\DB;

class AssessmentManagementService
{
    public function __construct(
        private readonly EvaluationService $evaluationService,
        private readonly AssessmentScopeService $scopeService
    ) {
    }

    public function createAssessment(int $userId, array $attributes): MstEval
    {
        return DB::transaction(function () use ($userId, $attributes) {
            $evaluation = $this->evaluationService->createNewEvaluation(
                $userId,
                isset($attributes['organization_id']) ? (int) $attributes['organization_id'] : null
            );
            $evaluation->tahun = $attributes['tahun'] ?? date('Y');
            $evaluation->save();

            $this->scopeService->createInitialScope(
                $evaluation,
                $attributes['selected_gamos'] ?? [],
                $attributes['nama_scope'] ?? null
            );

            return $evaluation->fresh();
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function buildShowData(MstEval $evaluation, ?int $activeScopeId = null): array
    {
        $allScopes = TrsScoping::where('eval_id', $evaluation->eval_id)->get();
        $activeScope = $activeScopeId ? $allScopes->firstWhere('id', $activeScopeId) : $allScopes->first();

        $selectedDomains = $activeScope
            ? TrsEvalDetail::where('scoping_id', $activeScope->id)->pluck('domain_id')->unique()->toArray()
            : TrsEvalDetail::whereIn('scoping_id', $allScopes->pluck('id'))->pluck('domain_id')->unique()->toArray();

        $objectivesQuery = MstObjective::with(['practices.activities']);
        if ($selectedDomains !== []) {
            $objectivesQuery->where(function ($query) use ($selectedDomains) {
                foreach ($selectedDomains as $domain) {
                    $query->orWhere('objective_id', 'like', trim((string) $domain).'%');
                }
            });
        }

        return [
            'objectives' => $objectivesQuery->get(),
            'allObjectives' => MstObjective::all(),
            'evalId' => $evaluation->eval_id,
            'evaluation' => $evaluation,
            'evidences' => MstEvidence::where('eval_id', $evaluation->eval_id)->orderBy('created_at', 'desc')->get(),
            'targetCapabilityMap' => $this->evaluationService->fetchTargetCapabilities($evaluation),
            'allScopes' => $allScopes,
            'activeScope' => $activeScope,
            'scopeDetails' => $this->scopeService->buildScopeDetails($allScopes),
            'selectedDomains' => $selectedDomains,
        ];
    }

    public function saveAssessment(MstEval $evaluation, array $payload): MstEval
    {
        $data = $this->evaluationService->convertAssessmentData($payload);
        $data['user_id'] = $evaluation->user_id;
        $data['organization_id'] = $evaluation->organization_id;
        $data['eval_id'] = $evaluation->eval_id;

        return $this->evaluationService->saveEvaluation($data);
    }

    /**
     * @return array<string, mixed>
     */
    public function loadAssessment(MstEval $evaluation): array
    {
        $data = $this->evaluationService->loadEvaluation($evaluation->eval_id);

        $notes = [];
        $evidence = [];
        foreach ($data['activity_evaluations'] as $activityId => $activityData) {
            $notes[$activityId] = $activityData['notes'];
            $evidence[$activityId] = $activityData['evidence'];
        }

        return [
            'eval_id' => $data['eval_id'],
            'assessmentData' => [],
            'notes' => $notes,
            'evidence' => $evidence,
            'activityData' => $data['activity_evaluations'],
        ];
    }

    public function finish(MstEval $evaluation): void
    {
        $evaluation->status = 'finished';
        $evaluation->save();
    }

    public function unlock(MstEval $evaluation): void
    {
        $evaluation->status = 'in_progress';
        $evaluation->save();
    }

    public function delete(MstEval $evaluation): void
    {
        $this->evaluationService->deleteEvaluation($evaluation->eval_id);
    }

    public function getMaturityScore(?MstEval $evaluation): float
    {
        if (! $evaluation) {
            return 0.0;
        }

        return round($this->evaluationService->calculateMaturityScore($evaluation->eval_id), 2);
    }
}
