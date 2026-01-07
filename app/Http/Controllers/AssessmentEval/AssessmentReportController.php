<?php

namespace App\Http\Controllers\AssessmentEval;

use App\Http\Controllers\Controller;
use App\Models\MstEval;
use App\Models\MstObjective;
use App\Models\TrsEvalDetail;
use App\Models\TrsObjectiveScore;
use App\Models\TrsScoping;
use App\Models\User;
use App\Services\EvaluationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

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

            if (! $evaluation) {
                return redirect()->route('assessment-eval.list')->withErrors(['error' => 'Assessment not found']);
            }

            $owner = User::find($evaluation->user_id);
            $currentUser = Auth::user();

            $isOwner = (string) $evaluation->user_id === (string) $currentUser->id;
            $sameOrg = ! empty($owner->organisasi) && ! empty($currentUser->organisasi) &&
                       strcasecmp(trim((string) $owner->organisasi), trim((string) $currentUser->organisasi)) === 0;

            if (! $isOwner && ! $sameOrg) {
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

            // Fetch I&T Target Maturity for this evaluation's year
            $year = $evaluation->tahun ?? $evaluation->year ?? $evaluation->assessment_year ?? null;
            $targetMaturity = null;
            if ($year) {
                $org = $currentUser->organisasi ?? null;
                $tmQuery = \App\Models\TargetMaturity::where('tahun', $year);
                if ($org) {
                    $tmQuery->where('organisasi', $org);
                } else {
                    $tmQuery->where('user_id', $currentUser->id);
                }
                $targetMaturity = $tmQuery->value('target_maturity');
            }

            return view('assessment-eval.report', compact(
                'objectives', 'evalId', 'evaluation',
                'isOwner', 'targetCapabilityMap',
                'allScopes', 'scopeMaturityData', 'targetMaturity'
            ));

        } catch (\Exception $e) {
            Log::error('Failed to load report', ['eval_id' => $evalId, 'error' => $e->getMessage()]);

            return redirect()->back()->withErrors(['error' => 'Failed to load report: '.$e->getMessage()]);
        }
    }

    public function index()
    {
        try {
            $data = $this->getReportData();
            if (isset($data['error'])) {
                return view('assessment-eval.report-all', [
                    'objectives' => [], 'assessments' => [],
                    'scopeMaturityData' => [], 'error' => $data['error'],
                ]);
            }

            return view('assessment-eval.report-all', $data);

        } catch (\Exception $e) {
            Log::error('Failed to load all-years report', ['user_id' => Auth::id(), 'error' => $e->getMessage()]);

            return redirect()->route('assessment-eval.list')->withErrors(['error' => 'Failed to load report: '.$e->getMessage()]);
        }
    }

    public function spiderweb()
    {
        try {
            $data = $this->getReportData();
            if (isset($data['error'])) {
                return view('assessment-eval.report-spiderweb', [
                    'objectives' => [], 'assessments' => [],
                    'scopeMaturityData' => [], 'error' => $data['error'],
                ]);
            }

            return view('assessment-eval.report-spiderweb', $data);

        } catch (\Exception $e) {
            Log::error('Failed to load spiderweb report', ['user_id' => Auth::id(), 'error' => $e->getMessage()]);

            return redirect()->route('assessment-eval.list')->withErrors(['error' => 'Failed to load report: '.$e->getMessage()]);
        }
    }

    private function getReportData()
    {
        $user = Auth::user();
        $org = $user->organisasi ?? null;

        $query = MstEval::with(['user', 'maturityScore'])->orderBy('created_at', 'desc');

        if ($org) {
            $query->where(function ($q) use ($user, $org) {
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
            return ['error' => 'No assessments found.'];
        }

        $objectives = $this->evaluationService->getSortedObjectives();
        $processedData = [];

        // Collect all years for fetching target maturities
        $allYears = $assessments->pluck('tahun')->filter()->unique()->values()->all();
        $targetMaturityMap = [];

        if (! empty($allYears)) {
            $targetMaturities = \App\Models\TargetMaturity::whereIn('tahun', $allYears)
                ->when($org, function ($q) use ($org) {
                    $q->where('organisasi', $org);
                }, function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->get();

            foreach ($targetMaturities as $tm) {
                $targetMaturityMap[$tm->tahun] = $tm->target_maturity;
            }
        }

        foreach ($assessments as $eval) {
            $scopes = TrsScoping::where('eval_id', $eval->eval_id)->get();
            if ($scopes->isEmpty()) {
                continue;
            }

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
                    'maturity_scores' => $maturityScores,
                    'target_maturity' => $targetMaturityMap[$year] ?? null,
                ];
            }
        }

        usort($processedData, function ($a, $b) {
            return ($a['year'] == $b['year'])
                ? strcmp($a['scope_name'], $b['scope_name'])
                : ($b['year'] <=> $a['year']); // Sort DESC by year
        });

        return compact('objectives', 'processedData');
    }
}
