<?php

namespace App\Http\Controllers\AssessmentEval;

use App\Http\Controllers\Controller;
use App\Services\EvaluationService;
use App\Models\MstEval;
use App\Models\MstObjective;
use App\Models\MstEvidence;
use App\Models\TrsActivityeval;
use App\Models\TrsScoping;
use App\Models\TrsEvalDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
    public function show($evalId, $objectiveId)
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

            // Get the objective
            $objective = MstObjective::with(['practices.activities'])
                ->where('objective_id', $objectiveId)
                ->first();

            if (!$objective) {
                return redirect()->back()->withErrors(['error' => 'Objective not found']);
            }

            // Load all evidences for this evaluation (for display lookup)
            $evalEvidences = MstEvidence::where('eval_id', $evalId)->get()->keyBy('id');

            // Get all activities for this objective with their evaluations
            $activityData = [];
            
            foreach ($objective->practices as $practice) {
                foreach ($practice->activities as $activity) {
                    // Get evaluation for this activity
                    $activityEval = TrsActivityeval::where('eval_id', $evalId)
                        ->where('activity_id', $activity->activity_id)
                        ->first();
                    
                    // Only include if has any response (level_achieved not null)
                    if ($activityEval && $activityEval->level_achieved) {
                        // Parse evidence - could be JSON array of IDs/names or comma-separated string
                        $evidenceDisplay = [];
                        $rawEvidence = $activityEval->evidence;
                        
                        if ($rawEvidence) {
                            // Try JSON decode first
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
                                // Not JSON, treat as plain text or comma-separated
                                $evidenceDisplay[] = $rawEvidence;
                            }
                        }

                        $activityData[] = [
                            'practice_id' => $practice->practice_id,
                            'practice_name' => $practice->practice_name,
                            'activity_id' => $activity->activity_id,
                            'activity_description' => $activity->description ?? $activity->activity ?? '',
                            'capability_level' => $activity->capability_lvl ?? $activity->capability_level ?? '',
                            'answer' => $activityEval->level_achieved,
                            'evidence' => $evidenceDisplay, // Now a clean array of strings
                            'notes' => $activityEval->notes
                        ];
                    }
                }
            }

            // Sort by practice_id then activity_id
            usort($activityData, function($a, $b) {
                $pCompare = strcmp($a['practice_id'], $b['practice_id']);
                return $pCompare !== 0 ? $pCompare : strcmp($a['activity_id'], $b['activity_id']);
            });

            return view('assessment-eval.report-activity', compact(
                'evaluation', 'evalId', 'objective', 'activityData'
            ));

        } catch (\Exception $e) {
            Log::error("Failed to load activity report", [
                'eval_id' => $evalId, 
                'objective_id' => $objectiveId,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->withErrors(['error' => 'Failed to load activity report: ' . $e->getMessage()]);
        }
    }
}
