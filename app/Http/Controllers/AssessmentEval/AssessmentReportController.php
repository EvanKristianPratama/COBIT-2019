<?php

namespace App\Http\Controllers\AssessmentEval;

use App\Http\Controllers\Controller;
use App\Services\EvaluationService;
use App\Models\MstEval;
use App\Models\TrsScoping;
use App\Models\TrsEvalDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AssessmentReportController extends Controller
{
    protected $evaluationService;

    public function __construct(EvaluationService $evaluationService)
    {
        $this->evaluationService = $evaluationService;
    }

    /**
     * Display the assessment report for all years/assessments.
     */
    public function index()
    {
        try {
            $user = Auth::user();
            $org = $user->organisasi ?? null;

            // 1. Fetch relevant assessments (My Assessments + Org Assessments)
            // Reusing logic similar to listAssessments to ensure consistency in access
            $query = MstEval::with(['user', 'maturityScore'])
                ->orderBy('created_at', 'desc');

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
                    'objectives' => [],
                    'assessments' => [],
                    'scopeMaturityData' => [],
                    'error' => 'No assessments found.'
                ]);
            }

            // 2. Fetch Helper Data
            $objectives = $this->evaluationService->getSortedObjectives();
            
            // 3. Process each assessment
            $processedData = []; // flattened list of scopes with maturity data

            foreach ($assessments as $eval) {
                // Fetch scopes for this specific assessment
                $scopes = TrsScoping::where('eval_id', $eval->eval_id)->get();
                
                if ($scopes->isEmpty()) {
                    continue; // Skip assessments without scopes (or maybe show default "Unscoped"?)
                }

                // Load assessment activity data for calculation
                $loadedData = $this->evaluationService->loadEvaluation($eval->eval_id);
                $activityData = $loadedData['activity_evaluations'] ?? [];
                
                // Determine year (legacy support)
                $year = $eval->tahun ?? $eval->year ?? $eval->assessment_year ?? $eval->created_at->format('Y');

                foreach ($scopes as $scope) {
                    // Get domains/objectives in this scope
                    $scopeDomains = TrsEvalDetail::where('scoping_id', $scope->id)
                        ->pluck('domain_id')
                        ->toArray();
                    
                    // Calculate maturity for each objective
                    $maturityScores = [];
                    foreach ($objectives as $obj) {
                        if (in_array($obj->objective_id, $scopeDomains)) {
                            $maturityScores[$obj->objective_id] = $this->evaluationService->calculateObjectiveMaturity($obj, $activityData);
                        } else {
                            $maturityScores[$obj->objective_id] = null; // Not in scope
                        }
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
            
            // Sort processed data by Year (desc), then Scope Name
            usort($processedData, function($a, $b) {
                if ($a['year'] == $b['year']) {
                    return strcmp($a['scope_name'], $b['scope_name']);
                }
                return $a['year'] <=> $b['year'];
            });

            return view('assessment-eval.report-all', compact('objectives', 'processedData'));

        } catch (\Exception $e) {
            Log::error("Failed to load all-years report", [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('assessment-eval.list')->withErrors(['error' => 'Failed to load report: ' . $e->getMessage()]);
        }
    }
}
