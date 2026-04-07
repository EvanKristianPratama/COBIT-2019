<?php

namespace App\Http\Controllers\AssessmentEval;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssessmentEval\CreateAssessmentRequest;
use App\Http\Requests\AssessmentEval\SaveAssessmentRequest;
use App\Models\MstEval;
use App\Services\Assessment\Access\AssessmentAccessService;
use App\Services\Assessment\Eval\AssessmentManagementService;
use App\Services\EvaluationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AssessmentEvalController extends Controller
{
    public function __construct(
        protected EvaluationService $evaluationService,
        protected AssessmentManagementService $assessmentManagementService,
        protected AssessmentAccessService $assessmentAccessService
    ) {
    }

    public function createAssessment(CreateAssessmentRequest $request)
    {
        try {
            $evaluation = $this->assessmentManagementService->createAssessment(
                Auth::id(),
                $request->validated()
            );
            $this->assessmentAccessService->assign(Auth::user(), $evaluation, Auth::user());

            return redirect()->route('assessment-eval.show', ['evalId' => $evaluation->encrypted_id]);
        } catch (\Exception $e) {
            Log::error('Failed to create assessment', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->withErrors(['error' => 'Failed to create assessment: '.$e->getMessage()]);
        }
    }

    public function showAssessment($evalId)
    {
        try {
            $evaluation = $this->evaluationService->getEvaluationById($evalId);

            if (! $evaluation) {
                return redirect()->route('assessment-eval.list')->withErrors(['error' => 'Assessment not found']);
            }

            $currentUser = Auth::user();
            if (! $this->assessmentAccessService->canView($currentUser, $evaluation)) {
                return redirect()->route('assessment-eval.list')->withErrors(['error' => 'Assessment not found or access denied']);
            }

            $data = $this->assessmentManagementService->buildShowData(
                $evaluation,
                request()->integer('scope_id') ?: null
            );
            $data['canManageAssessment'] = $this->assessmentAccessService->canManage($currentUser, $evaluation);

            return view('assessment-eval.show', $data);
        } catch (\Exception $e) {
            Log::error('Failed to load assessment', [
                'eval_id' => $evalId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('assessment-eval.list')->withErrors(['error' => 'Failed to load assessment: '.$e->getMessage()]);
        }
    }

    public function index()
    {
        return redirect()->route('assessment-eval.list');
    }

    public function save(SaveAssessmentRequest $request, $evalId)
    {
        try {
            $evaluation = $this->evaluationService->getEvaluationById($evalId);

            if (! $evaluation || ! $this->assessmentAccessService->canManage(Auth::user(), $evaluation)) {
                return response()->json(['success' => false, 'message' => 'Assessment not found or access denied'], 404);
            }

            if ($evaluation->status === 'finished') {
                return response()->json(['success' => false, 'message' => 'Assessment is finished and cannot be modified.'], 403);
            }

            $this->assessmentManagementService->saveAssessment($evaluation, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Assessment saved successfully',
                'eval_id' => $evalId,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function load($evalId)
    {
        try {
            $evaluation = $this->evaluationService->getEvaluationById($evalId);

            if (! $evaluation) {
                return response()->json(['success' => false, 'message' => 'Assessment not found'], 404);
            }

            $currentUser = Auth::user();
            if (! $this->assessmentAccessService->canView($currentUser, $evaluation)) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $data = $this->assessmentManagementService->loadAssessment($evaluation);

            return response()->json([
                'success' => true,
                'data' => [
                    ...$data,
                    'canManageAssessment' => $this->assessmentAccessService->canManage($currentUser, $evaluation),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getUserEvaluations()
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $this->evaluationService->getUserEvaluations(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function delete($evalId)
    {
        try {
            $evaluation = $this->evaluationService->getEvaluationById($evalId);

            if (! $evaluation) {
                return redirect()->back()->withErrors(['error' => 'Assessment tidak ditemukan.']);
            }

            if (! $this->assessmentAccessService->canManage(Auth::user(), $evaluation)) {
                return redirect()->back()->withErrors(['error' => 'Anda tidak memiliki akses untuk menghapus assessment ini.']);
            }

            $this->assessmentManagementService->delete($evaluation);

            return redirect()->route('assessment-eval.list')->with('success', 'Assessment berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Gagal menghapus assessment: '.$e->getMessage()]);
        }
    }

    public function finish($evalId)
    {
        try {
            $evaluation = $this->evaluationService->getEvaluationById($evalId);

            if (! $evaluation) {
                return response()->json(['message' => 'Assessment not found'], 404);
            }
            if (! $this->assessmentAccessService->canManage(Auth::user(), $evaluation)) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $this->assessmentManagementService->finish($evaluation);

            return response()->json(['message' => 'Assessment finished successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to finish assessment', ['eval_id' => $evalId, 'error' => $e->getMessage()]);

            return response()->json(['message' => 'Failed to finish assessment'], 500);
        }
    }

    public function unlock($evalId)
    {
        try {
            $evaluation = $this->evaluationService->getEvaluationById($evalId);

            if (! $evaluation) {
                return response()->json(['message' => 'Assessment not found'], 404);
            }
            if (! $this->assessmentAccessService->canManage(Auth::user(), $evaluation)) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $this->assessmentManagementService->unlock($evaluation);

            return response()->json(['message' => 'Assessment unlocked successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to unlock assessment', ['eval_id' => $evalId, 'error' => $e->getMessage()]);

            return response()->json(['message' => 'Failed to unlock assessment'], 500);
        }
    }

    public function getMaturityScore($evalId)
    {
        try {
            $evaluation = $this->evaluationService->getEvaluationById($evalId);
            if (! $evaluation) {
                return response()->json(['success' => false, 'message' => 'Assessment not found'], 404);
            }
            if (! $this->assessmentAccessService->canView(Auth::user(), $evaluation)) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            return response()->json([
                'success' => true,
                'score' => $this->assessmentManagementService->getMaturityScore($evaluation),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
