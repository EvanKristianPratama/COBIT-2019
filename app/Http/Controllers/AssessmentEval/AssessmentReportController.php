<?php

namespace App\Http\Controllers\AssessmentEval;

use App\Http\Controllers\Controller;
use App\Services\EvaluationService;
use App\Models\MstEval;
use App\Models\TrsScoping;
use App\Models\TrsEvalDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use App\Models\User;
use Illuminate\Http\Request;

class AssessmentReportController extends Controller
{
    protected $evaluationService;

    public function __construct(EvaluationService $evaluationService)
    {
        $this->evaluationService = $evaluationService;
    }

    public function show($evalId)
    {
        try {
            $evaluation = $this->evaluationService->getEvaluationById($evalId);
            
            if (!$evaluation) {
                return redirect()->route('assessment-eval.list')->withErrors(['error' => 'Assessment not found']);
            }

            $owner = User::find($evaluation->user_id);
            $currentUser = Auth::user();

            $isOwner = (string)$evaluation->user_id === (string)$currentUser->id;
            $sameOrg = !empty($owner->organisasi) && !empty($currentUser->organisasi) && 
                       strcasecmp(trim((string)$owner->organisasi), trim((string)$currentUser->organisasi)) === 0;

            if (!$isOwner && !$sameOrg) {
                return redirect()->route('assessment-eval.list')->withErrors(['error' => 'Access denied']);
            }

            $allScopes = TrsScoping::where('eval_id', $evalId)->get();
            $objectives = $this->evaluationService->getSortedObjectives();
            $targetCapabilityMap = $this->evaluationService->fetchTargetCapabilities($evaluation);
            
            $loadedData = $this->evaluationService->loadEvaluation($evalId);
            $activityData = $loadedData['activity_evaluations'] ?? [];

            $scopeMaturityData = [];
            foreach ($allScopes as $scope) {
                $scopeDomains = TrsEvalDetail::where('scoping_id', $scope->id)->pluck('domain_id')->toArray();
                
                $scopeMaturityData[$scope->id] = [];
                foreach ($objectives as $obj) {
                    $isInScope = in_array($obj->objective_id, $scopeDomains);
                    $scopeMaturityData[$scope->id][$obj->objective_id] = $isInScope 
                        ? $this->evaluationService->calculateObjectiveMaturity($obj, $activityData) 
                        : null;
                }
            }

            return view('assessment-eval.report', compact(
                'objectives', 'evalId', 'evaluation', 
                'isOwner', 'targetCapabilityMap', 
                'allScopes', 'scopeMaturityData'
            ));

        } catch (\Exception $e) {
            Log::error("Failed to load report", ['eval_id' => $evalId, 'error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => 'Failed to load report: ' . $e->getMessage()]);
        }
    }

    public function index()
    {
        try {
            $user = Auth::user();
            $org = $user->organisasi ?? null;

            $query = MstEval::with(['user', 'maturityScore'])->orderBy('created_at', 'desc');

            if ($org) {
                $query->where(function($q) use ($user, $org) {
                    $q->where('user_id', $user->id)
                      ->orWhereHas('user', function ($subQ) use ($org) {
                          $subQ->where('organisasi', $org);
                      });
                });
            } else {
                $query->where('user_id', $user->id);
            }

            $assessments = $query->get();

            if ($assessments->isEmpty()) {
                return view('assessment-eval.report-all', [
                    'objectives' => [], 'assessments' => [],
                    'scopeMaturityData' => [], 'error' => 'No assessments found.'
                ]);
            }

            $objectives = $this->evaluationService->getSortedObjectives();
            $processedData = [];

            foreach ($assessments as $eval) {
                $scopes = TrsScoping::where('eval_id', $eval->eval_id)->get();
                if ($scopes->isEmpty()) continue;

                $loadedData = $this->evaluationService->loadEvaluation($eval->eval_id);
                $activityData = $loadedData['activity_evaluations'] ?? [];
                
                $year = $eval->tahun ?? $eval->year ?? $eval->assessment_year ?? $eval->created_at->format('Y');

                foreach ($scopes as $scope) {
                    $scopeDomains = TrsEvalDetail::where('scoping_id', $scope->id)->pluck('domain_id')->toArray();
                    
                    $maturityScores = [];
                    foreach ($objectives as $obj) {
                        $maturityScores[$obj->objective_id] = in_array($obj->objective_id, $scopeDomains)
                            ? $this->evaluationService->calculateObjectiveMaturity($obj, $activityData)
                            : null;
                    }

                    $processedData[] = [
                        'assessment_id' => $eval->eval_id,
                        'year' => $year,
                        'scope_id' => $scope->id,
                        'scope_name' => $scope->nama_scope,
                        'user_name' => $eval->user->name ?? 'Unknown',
                        'maturity_scores' => $maturityScores
                    ];
                }
            }
            
            usort($processedData, function($a, $b) {
                return ($a['year'] == $b['year']) 
                    ? strcmp($a['scope_name'], $b['scope_name']) 
                    : ($a['year'] <=> $b['year']);
            });

            return view('assessment-eval.report-all', compact('objectives', 'processedData'));

        } catch (\Exception $e) {
            Log::error("Failed to load all-years report", ['user_id' => Auth::id(), 'error' => $e->getMessage()]);
            return redirect()->route('assessment-eval.list')->withErrors(['error' => 'Failed to load report: ' . $e->getMessage()]);
        }
    }
}
