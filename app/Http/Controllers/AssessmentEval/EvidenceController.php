<?php

namespace App\Http\Controllers\AssessmentEval;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssessmentEval\PreviousEvidenceRequest;
use App\Http\Requests\AssessmentEval\UpsertEvidenceRequest;
use App\Services\Assessment\Access\AssessmentAccessService;
use App\Services\Assessment\Evidence\AssessmentEvidenceService;
use App\Services\EvaluationService;
use App\Models\MstEvidence;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EvidenceController extends Controller
{
    public function __construct(
        protected EvaluationService $evaluationService,
        protected AssessmentEvidenceService $assessmentEvidenceService,
        protected AssessmentAccessService $assessmentAccessService
    ) {
    }

    public function index($evalId)
    {
        try {
            $evaluation = $this->evaluationService->getEvaluationById($evalId);
            
            if (!$evaluation) {
                return redirect()->route('assessment-eval.list')->withErrors(['error' => 'Assessment not found']);
            }

            $currentUser = Auth::user();
            if (! $this->assessmentAccessService->canView($currentUser, $evaluation)) {
                return redirect()->route('assessment-eval.list')->withErrors(['error' => 'Access denied']);
            }

            $evidences = $this->assessmentEvidenceService->getEvidences($evaluation);
            $canManageAssessment = $this->assessmentAccessService->canManage($currentUser, $evaluation);

            return view('assessment-eval.evidence', compact('evaluation', 'evidences', 'evalId', 'canManageAssessment'));

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to load evidence: ' . $e->getMessage()]);
        }
    }

    public function previous(PreviousEvidenceRequest $request, $evalId)
    {
        try {
            $evaluation = $this->evaluationService->getEvaluationById($evalId);
            if (!$evaluation) return response()->json(['success' => false, 'message' => 'Assessment not found'], 404);

            $currentUser = Auth::user();
            if (!$this->assessmentAccessService->canView($currentUser, $evaluation)) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $previousEvidences = $this->assessmentEvidenceService->getPreviousEvidences(
                $evaluation,
                $request->validated()
            );

            return response()->json([
                'success' => true, 
                'data' => $previousEvidences['data'],
                'pagination' => $previousEvidences['pagination'],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch previous evidences', ['eval_id' => $evalId, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to load previous evidences'], 500);
        }
    }

    public function store(UpsertEvidenceRequest $request, $evalId)
    {
        try {
            $evaluation = $this->evaluationService->getEvaluationById($evalId);
            
            if (!$evaluation || ! $this->assessmentAccessService->canManage(Auth::user(), $evaluation)) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $evidence = $this->assessmentEvidenceService->store($evaluation, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Evidence added successfully',
                'data' => $evidence
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to store evidence", ['eval_id' => $evalId, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to store evidence: ' . $e->getMessage()], 500);
        }
    }

    public function update(UpsertEvidenceRequest $request, $evidenceId)
    {
        try {
            $evidence = MstEvidence::findOrFail($evidenceId);
            $evaluation = $this->evaluationService->getEvaluationById($evidence->eval_id);

            if (!$evaluation || ! $this->assessmentAccessService->canManage(Auth::user(), $evaluation)) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $evidence = $this->assessmentEvidenceService->update($evidence, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Evidence updated successfully',
                'data' => $evidence
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to update evidence", ['evidence_id' => $evidenceId, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to update evidence: ' . $e->getMessage()], 500);
        }
    }
}
