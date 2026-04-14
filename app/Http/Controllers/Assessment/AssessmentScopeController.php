<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssessmentEval\DeleteScopeRequest;
use App\Http\Requests\AssessmentEval\UpdateScopeRequest;
use App\Services\Assessment\Access\AssessmentAccessService;
use App\Services\Assessment\Scope\AssessmentScopeService;
use App\Services\EvaluationService;
use App\Models\MstObjective;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AssessmentScopeController extends Controller
{
    public function __construct(
        protected EvaluationService $evaluationService,
        protected AssessmentScopeService $assessmentScopeService,
        protected AssessmentAccessService $assessmentAccessService
    ) {
    }

    public function update(UpdateScopeRequest $request, $evalId)
    {
        try {
            $evaluation = $this->evaluationService->getEvaluationById($evalId);

            if (! $evaluation || ! $this->assessmentAccessService->canManage(Auth::user(), $evaluation)) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            if ($evaluation->status === 'finished') {
                return response()->json(['success' => false, 'message' => 'Assessment is finished'], 403);
            }

            $validated = $request->validated();
            $this->assessmentScopeService->syncScope(
                $evaluation,
                $validated['scopes'],
                $validated['nama_scope'],
                $validated['scope_id'] ?? null,
                (bool) ($validated['is_new'] ?? false)
            );
            
            return response()->json(['success' => true, 'message' => 'Scope updated successfully']);

        } catch (\Exception $e) {
            Log::error("Failed to update scope", ['eval_id' => $evalId, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to update scope: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(DeleteScopeRequest $request)
    {
        try {
            $scope = $this->assessmentAccessService->findManagedScope(
                (int) $request->validated('scope_id'),
                Auth::user()
            );

            if ($scope) {
                $this->assessmentScopeService->deleteScope($scope);

                return response()->json(['success' => true]);
            }
            
            return response()->json(['success' => false, 'message' => 'Scope not found or access denied']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getObjectives() {
        return response()->json(MstObjective::select('objective_id', 'objective')->get());
    }
}
