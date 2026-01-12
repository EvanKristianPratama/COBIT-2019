<?php

namespace App\Http\Controllers\AssessmentEval;

use App\Models\User;
use App\Models\MstEval;
use App\Models\TrsDomain;
use App\Models\TrsScoping;
use App\Models\MstEvidence;
use App\Models\MstObjective;
use Illuminate\Http\Request;
use App\Models\TrsEvalDetail;
use App\Models\TrsActivityeval;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\TrsObjectiveScore;
use App\Services\EvaluationService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

class ActivityReportController extends Controller
{
    protected $evaluationService;

    public function __construct(EvaluationService $evaluationService)
    {
        $this->evaluationService = $evaluationService;
    }

    /**
     * Show activity report for a specific objective
     */
    public function show($evalId, $objectiveId, Request $request)
    {
        $filterLevel = $request->query('level');
        $data = $this->prepareActivityData($evalId, $objectiveId, $filterLevel);
        
        if ($data instanceof RedirectResponse) {
            return $data;
        }
        
        return view('assessment-eval.report-activity', $data);
    }

    /**
     * Download PDF report for a specific objective
     */
    public function downloadPdf($evalId, $objectiveId, Request $request)
    {
        $filterLevel = $request->query('level');
        $data = $this->prepareActivityData($evalId, $objectiveId, $filterLevel);
        
        if ($data instanceof RedirectResponse) {
            return $data;
        }
        
        $pdf = Pdf::loadView('assessment-eval.report-activity-pdf', $data);
        $pdf->setPaper('a4', 'landscape');
        
        $filename = 'Activity-Report-' . $evalId . '-' . $objectiveId . '.pdf';
        
        return $pdf->stream($filename);
    }

    /**
     * Prepare activity data for both view and PDF
     */
    private function prepareActivityData($evalId, $objectiveId, $filterLevel = null)
    {
        try {
            $evaluation = $this->evaluationService->getEvaluationById($evalId);
            
            if (!$evaluation) {
                return redirect()->route('assessment-eval.list')->withErrors(['error' => 'Assessment not found']);
            }

            // Authorization check
            $owner = User::find($evaluation->user_id);
            $currentUser = Auth::user();

            $isOwner = (string)$evaluation->user_id === (string)$currentUser->id;
            $sameOrg = !empty($owner->organisasi) && !empty($currentUser->organisasi) && 
                       strcasecmp(trim((string)$owner->organisasi), trim((string)$currentUser->organisasi)) === 0;

            if (!$isOwner && !$sameOrg) {
                return redirect()->route('assessment-eval.list')->withErrors(['error' => 'Access denied']);
            }

            $organization = $owner->organisasi ?? 'N/A';

            // Get the objective
            $objective = MstObjective::with(['practices.activities'])
                ->where('objective_id', $objectiveId)
                ->first();

            if (!$objective) {
                return redirect()->back()->withErrors(['error' => 'Objective not found']);
            }

            // Get area objective
            $areaObjective = TrsDomain::where('objective_id', $objectiveId)->first();

            // Calculate max level for this objective
            $maxLevel = 0;
            foreach ($objective->practices as $practice) {
                foreach ($practice->activities as $activity) {
                    $lvl = (int)($activity->capability_lvl ?? $activity->capability_level ?? 0);
                    if ($lvl > $maxLevel) $maxLevel = $lvl;
                }
            }

            // Get current maturity level from score table
            $objectiveScore = TrsObjectiveScore::where('eval_id', $evalId)
                ->where('objective_id', $objectiveId)
                ->first();
            $currentLevel = $objectiveScore ? $objectiveScore->level : 0;

            // Fetch Target Capabilities
            $targetCapabilityMap = $this->evaluationService->fetchTargetCapabilities($evaluation);
            $targetLevel = $targetCapabilityMap[$objectiveId] ?? 0;

            // Rating Map for calculation
            $ratingMap = ['N' => 0.0, 'P' => 1.0/3.0, 'L' => 2.0/3.0, 'F' => 1.0];
            
            // Calculate Rating String (e.g., 4F)
            $ratingString = $this->calculateRatingString($objective, $currentLevel, $evalId);

            // Load all evidences for this evaluation (for display lookup)
            $evalEvidences = MstEvidence::where('eval_id', $evalId)->get()->keyBy('id');

            // Get all activities for this objective with their evaluations
            $activityData = [];
            
            foreach ($objective->practices as $practice) {
                foreach ($practice->activities as $activity) {
                    // Check if capability level filter applies
                    $capLevel = $activity->capability_lvl ?? $activity->capability_level ?? '';
                    if ($filterLevel && (string)$capLevel !== (string)$filterLevel) {
                        continue;
                    }

                    // Get evaluation for this activity
                    $activityEval = TrsActivityeval::where('eval_id', $evalId)
                        ->where('activity_id', $activity->activity_id)
                        ->first();
                    
                    // Only include if has any response (level_achieved not null)
                    if ($activityEval && $activityEval->level_achieved) {
                        // Parse evidence
                        $evidenceDisplay = [];
                        $rawEvidence = $activityEval->evidence;
                        
                        if ($rawEvidence) {
                            $decoded = json_decode($rawEvidence, true);
                            if (is_array($decoded)) {
                                foreach ($decoded as $evItem) {
                                    if (is_numeric($evItem) && isset($evalEvidences[$evItem])) {
                                        $evidenceDisplay[] = $evalEvidences[$evItem]->judul_dokumen ?? "Evidence #{$evItem}";
                                    } elseif (is_array($evItem)) {
                                        $evidenceDisplay[] = $evItem['name'] ?? $evItem['judul_dokumen'] ?? json_encode($evItem);
                                    } else {
                                        $evidenceDisplay[] = (string)$evItem;
                                    }
                                }
                            } else {
                                $evidenceDisplay[] = $rawEvidence;
                            }
                        }

                        $activityData[] = [
                            'practice_id' => $practice->practice_id,
                            'practice_name' => $practice->practice_name,
                            'activity_id' => $activity->activity_id,
                            'activity_description' => $activity->description ?? $activity->activity ?? '',
                            'capability_level' => (string)$capLevel,
                            'answer' => $activityEval->level_achieved,
                            'evidence' => $evidenceDisplay,
                            'notes' => $activityEval->notes
                        ];
                    }
                }
            }

            // Primary sort by capability_level (ASC), secondary by practice_id (ASC)
            usort($activityData, function($a, $b) {
                // Secondary sort: practice_id
                $lvlA = (int)$a['capability_level'];
                $lvlB = (int)$b['capability_level'];
                
                if ($lvlA !== $lvlB) {
                    return $lvlA <=> $lvlB;
                }
                
                $pCompare = strcmp($a['practice_id'], $b['practice_id']);
                if ($pCompare !== 0) return $pCompare;
                
                return strcmp($a['activity_id'], $b['activity_id']);
            });

            // Calculate rating per level for display
            $ratingMap = ['N' => 0.0, 'P' => 1.0/3.0, 'L' => 2.0/3.0, 'F' => 1.0];
            $levelRatings = [];
            
            // Group activities by level
            $activitiesByLevel = collect($activityData)->groupBy('capability_level');
            
            foreach ($activitiesByLevel as $level => $activities) {
                $totalScore = 0;
                $count = 0;
                
                foreach ($activities as $act) {
                    $answer = $act['answer'];
                    $totalScore += $ratingMap[$answer] ?? 0;
                    $count++;
                }
                
                if ($count > 0) {
                    $avgScore = $totalScore / $count;
                    
                    // Determine letter rating
                    $letter = 'N';
                    if ($avgScore > 0.85) $letter = 'F';
                    elseif ($avgScore > 0.50) $letter = 'L';
                    elseif ($avgScore > 0.15) $letter = 'P';
                    
                    $levelRatings[$level] = [
                        'rating' => $level . $letter,
                        'score' => round($avgScore * 100, 1),
                        'count' => $count
                    ];
                } else {
                    $levelRatings[$level] = [
                        'rating' => $level . 'N',
                        'score' => 0,
                        'count' => 0
                    ];
                }
            }

            return compact(
                'evaluation', 'evalId', 'objective', 'areaObjective', 'activityData', 
                'filterLevel', 'currentLevel', 'maxLevel', 'organization',
                'targetLevel', 'ratingString', 'levelRatings'
            );
        } catch (\Exception $e) {
            Log::error("Failed to load activity report", [
                'eval_id' => $evalId, 
                'objective_id' => $objectiveId,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->withErrors(['error' => 'Failed to load activity report: ' . $e->getMessage()]);
        }
    }

    private function calculateRatingString($obj, $finalLevel, $evalId)
    {
        if ($finalLevel == 0) return '0N';

        $ratingMap = ['N' => 0.0, 'P' => 1.0/3.0, 'L' => 2.0/3.0, 'F' => 1.0];
        $activitiesByLevel = [2 => [], 3 => [], 4 => [], 5 => []];
        foreach ($obj->practices as $p) {
            foreach ($p->activities as $a) {
                $lvl = (int) ($a->capability_lvl ?? $a->capability_level ?? 0);
                if ($lvl >= 2 && $lvl <= 5) {
                    $activitiesByLevel[$lvl][] = $a;
                }
            }
        }

        $lvlToCheck = max(2, $finalLevel);
        $acts = $activitiesByLevel[$lvlToCheck] ?? [];
        if (empty($acts)) return $finalLevel . 'F';

        $totalScore = 0;
        $count = 0;
        foreach ($acts as $a) {
            $activityEval = \App\Models\TrsActivityeval::where('eval_id', $evalId)
                ->where('activity_id', $a->activity_id)
                ->first();
            
            $r = $activityEval ? $activityEval->level_achieved : 'N';
            $totalScore += $ratingMap[$r] ?? 0;
            $count++;
        }
        
        if ($count == 0) return $finalLevel . 'F';
        
        $avgScore = $totalScore / $count;

        $letter = 'N';
        if ($avgScore > 0.85) $letter = 'F';
        elseif ($avgScore > 0.50) $letter = 'L';
        elseif ($avgScore > 0.15) $letter = 'P';

        return $finalLevel . $letter;
    }
}
