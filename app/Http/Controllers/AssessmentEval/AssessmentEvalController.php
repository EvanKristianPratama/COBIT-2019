<?php

namespace App\Http\Controllers\AssessmentEval;

use App\Http\Controllers\Controller;
use App\Models\MstEval;
use App\Models\MstEvidence;
use App\Models\MstObjective;
use App\Models\TargetCapability;
use App\Models\TrsEvalDetail;
use App\Models\User;
use App\Services\EvaluationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssessmentEvalController extends Controller
{
    protected $evaluationService;

    public function __construct(EvaluationService $evaluationService)
    {
        $this->evaluationService = $evaluationService;
    }

    public function createAssessment(Request $request)
    {
        try {
            $evaluation = $this->evaluationService->createNewEvaluation(Auth::id());

            $evaluation->tahun = $request->input('tahun', date('Y'));
            $evaluation->save();

            $verifyEvaluation = $this->evaluationService->getEvaluationById($evaluation->eval_id);

            if (! $verifyEvaluation || (string) $verifyEvaluation->user_id !== (string) Auth::id()) {
                Log::error('Created evaluation verification failed', [
                    'eval_id' => $evaluation->eval_id,
                    'user_id' => Auth::id(),
                    'verification_result' => $verifyEvaluation ? 'found but wrong user' : 'not found',
                ]);
                throw new \Exception('Failed to verify created assessment');
            }

            try {
                $selected = $request->input('selected_gamos', []);
                if ($selected && ! is_array($selected)) {
                    $selected = array_map('trim', explode(',', (string) $selected));
                }

                $scopeName = $request->input('nama_scope') ?: 'Default';

                if (! empty($selected)) {
                    DB::transaction(function () use ($evaluation, $selected, $scopeName) {
                        $scoping = \App\Models\TrsScoping::firstOrCreate([
                            'eval_id' => $evaluation->eval_id,
                            'nama_scope' => $scopeName,
                        ]);

                        TrsEvalDetail::where('scoping_id', $scoping->id)->delete();

                        $inserts = [];
                        foreach ($selected as $domainId) {
                            $domainId = trim((string) $domainId);
                            if ($domainId !== '') {
                                $inserts[] = [
                                    'eval_id' => $evaluation->eval_id,
                                    'scoping_id' => $scoping->id,
                                    'domain_id' => $domainId,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            }
                        }

                        if (! empty($inserts)) {
                            TrsEvalDetail::insert($inserts);
                        }
                    });
                }
            } catch (\Exception $e) {
                Log::warning('Failed to save selected GAMO mapping for eval', [
                    'eval_id' => $evaluation->eval_id,
                    'error' => $e->getMessage(),
                ]);
            }

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

            $owner = User::find($evaluation->user_id);
            $currentUser = Auth::user();

            $isOwner = (string) $evaluation->user_id === (string) $currentUser->id;
            $sameOrg = ! empty($owner->organisasi) && ! empty($currentUser->organisasi) &&
                       strcasecmp(trim((string) $owner->organisasi), trim((string) $currentUser->organisasi)) === 0;

            if (! $isOwner && ! $sameOrg) {
                return redirect()->route('assessment-eval.list')->withErrors(['error' => 'Assessment not found or access denied']);
            }

            $allScopes = \App\Models\TrsScoping::where('eval_id', $evalId)->get();
            $activeScopeId = request()->input('scope_id');
            $activeScope = $activeScopeId ? $allScopes->firstWhere('id', $activeScopeId) : $allScopes->first();

            $selectedDomains = $activeScope
                ? TrsEvalDetail::where('scoping_id', $activeScope->id)->pluck('domain_id')->unique()->toArray()
                : TrsEvalDetail::where('eval_id', $evalId)->pluck('domain_id')->unique()->toArray();

            $objectivesQuery = MstObjective::with(['practices.activities']);
            if (! empty($selectedDomains)) {
                $objectivesQuery->where(function ($q) use ($selectedDomains) {
                    foreach ($selectedDomains as $domain) {
                        $domain = trim((string) $domain);
                        if ($domain !== '') {
                            $q->orWhere('objective_id', 'like', $domain.'%');
                        }
                    }
                });
            }
            $objectives = $objectivesQuery->get();

            $evidences = MstEvidence::where('eval_id', $evalId)->orderBy('created_at', 'desc')->get();

            $targetCapabilityMap = [];
            $assessmentYear = $evaluation->tahun ?? $evaluation->year ?? $evaluation->assessment_year;

            if ($assessmentYear) {
                $targetCapability = TargetCapability::where('user_id', $evaluation->user_id)
                    ->where('tahun', (int) $assessmentYear)
                    ->latest('updated_at')
                    ->first();

                if ($targetCapability) {
                    $fields = [
                        'EDM01', 'EDM02', 'EDM03', 'EDM04', 'EDM05',
                        'APO01', 'APO02', 'APO03', 'APO04', 'APO05', 'APO06', 'APO07', 'APO08', 'APO09', 'APO10', 'APO11', 'APO12', 'APO13', 'APO14',
                        'BAI01', 'BAI02', 'BAI03', 'BAI04', 'BAI05', 'BAI06', 'BAI07', 'BAI08', 'BAI09', 'BAI10', 'BAI11',
                        'DSS01', 'DSS02', 'DSS03', 'DSS04', 'DSS05', 'DSS06',
                        'MEA01', 'MEA02', 'MEA03', 'MEA04',
                    ];
                    foreach ($fields as $field) {
                        $targetCapabilityMap[$field] = $targetCapability->$field !== null ? (int) $targetCapability->$field : null;
                    }
                }
            }

            $allObjectives = MstObjective::all();

            $scopeDetails = $allScopes->mapWithKeys(function ($scope) {
                return [$scope->id => [
                    'name' => $scope->nama_scope,
                    'domains' => TrsEvalDetail::where('scoping_id', $scope->id)->pluck('domain_id')->toArray(),
                ]];
            });

            return view('assessment-eval.show', compact(
                'objectives', 'allObjectives', 'evalId', 'evaluation',
                'isOwner', 'evidences', 'targetCapabilityMap',
                'allScopes', 'activeScope', 'scopeDetails'
            ));

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

    public function save(Request $request, $evalId)
    {
        try {
            $evaluation = $this->evaluationService->getEvaluationById($evalId);

            if (! $evaluation || (string) $evaluation->user_id !== (string) Auth::id()) {
                return response()->json(['success' => false, 'message' => 'Assessment not found or access denied'], 404);
            }

            if ($evaluation->status === 'finished') {
                return response()->json(['success' => false, 'message' => 'Assessment is finished and cannot be modified.'], 403);
            }

            $data = $this->evaluationService->convertAssessmentData($request->all());
            $data['user_id'] = Auth::id();
            $data['eval_id'] = $evalId;

            $this->evaluationService->saveEvaluation($data);

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

            $owner = User::find($evaluation->user_id);
            $currentUser = Auth::user();

            $isOwner = (string) $evaluation->user_id === (string) $currentUser->id;
            $sameOrg = ! empty($owner->organisasi) && ! empty($currentUser->organisasi) &&
                       strcasecmp(trim((string) $owner->organisasi), trim((string) $currentUser->organisasi)) === 0;

            if (! $isOwner && ! $sameOrg) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $data = $this->evaluationService->loadEvaluation($evalId);

            $notes = [];
            $evidence = [];
            foreach ($data['activity_evaluations'] as $activityId => $activityData) {
                $notes[$activityId] = $activityData['notes'];
                $evidence[$activityId] = $activityData['evidence'];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'eval_id' => $data['eval_id'],
                    'assessmentData' => [],
                    'notes' => $notes,
                    'evidence' => $evidence,
                    'activityData' => $data['activity_evaluations'],
                    'isOwner' => $isOwner,
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
            MstEval::where('eval_id', $evalId)
                ->where('user_id', Auth::id())
                ->firstOrFail()
                ->delete();

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
            if ((string) $evaluation->user_id !== (string) Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $evaluation->status = 'finished';
            $evaluation->save();

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
            if ((string) $evaluation->user_id !== (string) Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $evaluation->status = 'in_progress';
            $evaluation->save();

            return response()->json(['message' => 'Assessment unlocked successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to unlock assessment', ['eval_id' => $evalId, 'error' => $e->getMessage()]);

            return response()->json(['message' => 'Failed to unlock assessment'], 500);
        }
    }

    public function getMaturityScore($evalId)
    {
        try {
            return response()->json([
                'success' => true,
                'score' => round($this->evaluationService->calculateMaturityScore($evalId), 2),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
