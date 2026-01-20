<?php

namespace App\Services;

use App\Models\MstEval;
use App\Models\TrsActivityeval;
use App\Models\TrsObjectiveScore;
use App\Models\TrsMaturityScore;
use App\Models\TrsSummaryReport;
use App\Models\TrsSummaryActivity;
use App\Models\MstActivities;
use App\Models\MstEvidence;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EvaluationService
{
    /**
     * Save evaluation data from the assessment form
     */
    public function saveEvaluation($data)
    {
        try {
            DB::beginTransaction();

            $evaluation = MstEval::updateOrCreate(
                [
                    'eval_id' => $data['eval_id'] ?? null
                ],
                [
                    'user_id' => $data['user_id'] ?? Auth::id()
                ]
            );

            if (isset($data['activity_evaluations'])) {
                // Get all activity IDs from the request
                $incomingActivityIds = collect($data['activity_evaluations'])->pluck('activity_id')->toArray();
                
                // Pre-fetch Map: Activity ID -> Objective ID
                // Optimizes query to avoid N+1 inside loop
                $activityObjectiveMap = MstActivities::with('practice')
                    ->whereIn('activity_id', $incomingActivityIds)
                    ->get()
                    ->mapWithKeys(function ($act) {
                        return [$act->activity_id => $act->practice->objective_id ?? null];
                    });

 
                $evidenceMap = MstEvidence::where('eval_id', $evaluation->eval_id)
                    ->get()
                    ->mapWithKeys(function ($item) {
                        return [strtolower(trim($item->judul_dokumen)) => $item->id];
                    });

                // Delete activities that are not in the incoming data (completely removed from form)
                TrsActivityeval::withTrashed()
                    ->where('eval_id', $evaluation->eval_id)
                    ->whereNotIn('activity_id', $incomingActivityIds)
                    ->forceDelete();
                
                foreach ($data['activity_evaluations'] as $activityData) {
                    // Always save or update the activity, even if rated as 'N'
                    // This preserves evidence and notes when levels are changed
                    $activityEval = TrsActivityeval::updateOrCreate(
                        [
                            'eval_id' => $evaluation->eval_id,
                            'activity_id' => $activityData['activity_id']
                        ],
                        [
                            'level_achieved' => $activityData['level_achieved'],
                            'evidence' => $activityData['evidence'] ?? null,
                            'notes' => $activityData['notes'] ?? null
                        ]
                    );

                    // --- NORMALIZATION LOGIC START ---
                    
                    // 1. Resolve Objective ID for this activity
                    $objectiveId = $activityObjectiveMap[$activityData['activity_id']] ?? null;

                    if ($objectiveId) {
                        // 2. Ensure Summary Report exists (parent container)
                        $summaryReport = TrsSummaryReport::firstOrCreate(
                            [
                                'eval_id' => $evaluation->eval_id,
                                'objective_id' => $objectiveId
                            ]
                        );

                        // 3. Sync Evidence Mapping
                        // First, clear existing mappings for this specific activity evaluation to avoid duplicates
                        TrsSummaryActivity::where('activityeval_id', $activityEval->id)->delete();

                        if (!empty($activityData['evidence'])) {
                            // Priority 1: Use explicitly provided evidence names (Array from Frontend)
                            if (!empty($activityData['evidence_names']) && is_array($activityData['evidence_names'])) {
                                $files = $activityData['evidence_names'];
                            } 
                            // Priority 2: Fallback to parsing the evidence string (Legacy/Backup)
                            else {
                                $files = preg_split('/\r\n|\r|\n/', $activityData['evidence']);
                            }
                            
                            foreach ($files as $file) {
                                $filename = trim($file);
                                if (empty($filename)) continue;

                                // Lookup Evidence ID (Normalized)
                                $normalizedName = strtolower($filename);
                                $evidenceId = $evidenceMap[$normalizedName] ?? null;

                                // Link evidence, allowing NULL evidence_id for unknown documents if requested
                                TrsSummaryActivity::create([
                                    'summary_id' => $summaryReport->id,
                                    'activityeval_id' => $activityEval->id,
                                    'evidence_id' => $evidenceId
                                ]);
                            }
                        }
                    }
                    // --- NORMALIZATION LOGIC END ---
                }
            }

            // Calculate and save maturity scores to database
            $this->updateCalculatedScores($evaluation->eval_id);

            DB::commit();
            return $evaluation;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Load evaluation data for the assessment form
     */
    public function loadEvaluation($evalId)
    {
        $evaluation = MstEval::with([
            'activityEvaluations.activity.practice.objective'
        ])->findOrFail($evalId);

        $formattedData = [
            'eval_id' => $evaluation->eval_id,
            'user_id' => $evaluation->user_id,
            'created_at' => $evaluation->created_at,
            'updated_at' => $evaluation->updated_at,
            'activity_evaluations' => []
        ];

        foreach ($evaluation->activityEvaluations as $activityEval) {
            $activity = $activityEval->activity;
            $practice = $activity->practice;
            $objective = $practice ? $practice->objective : null;
            
            $formattedData['activity_evaluations'][$activityEval->activity_id] = [
                'activity_id' => $activityEval->activity_id,
                'level_achieved' => $activityEval->level_achieved,
                'evidence' => $activityEval->evidence,
                'notes' => $activityEval->notes,
                'capability_lvl' => $activity->capability_lvl ?? null,
                'objective_id' => $objective ? $objective->objective_id : null
            ];
        }

        return $formattedData;
    }

    /**
     * Convert frontend assessment data to database format
     */
    public function convertAssessmentData($assessmentData)
    {
        $activityEvaluations = [];
        $processedActivityIds = [];

        if (isset($assessmentData['assessmentData'])) {
            $levelScores = $assessmentData['assessmentData'];
            $notes = $assessmentData['notes'] ?? [];
            $evidence = $assessmentData['evidence'] ?? [];
            $evidenceNames = $assessmentData['evidenceNames'] ?? [];
            
            foreach ($levelScores as $objectiveId => $levels) {
                foreach ($levels as $level => $levelData) {
                    if (isset($levelData['activities'])) {
                        foreach ($levelData['activities'] as $activityId => $score) {
                            $levelAchieved = $this->scoreToLetter($score);
                            
                            $activityEvaluations[] = [
                                'activity_id' => $activityId,
                                'level_achieved' => $levelAchieved,
                                'evidence' => $evidence[$activityId] ?? null,
                                'evidence_names' => $evidenceNames[$activityId] ?? null,
                                'notes' => $notes[$activityId] ?? null
                            ];
                            $processedActivityIds[$activityId] = true;
                        }
                    }
                }
            }

            // check for activities with notes/evidence but no rating
            $otherIds = array_unique(array_merge(array_keys($notes), array_keys($evidence)));
            foreach ($otherIds as $actId) {
                if (!isset($processedActivityIds[$actId])) {
                    $activityEvaluations[] = [
                        'activity_id' => $actId,
                        'level_achieved' => null, // Allow null for unrated activities
                        'evidence' => $evidence[$actId] ?? null,
                        'evidence_names' => $evidenceNames[$actId] ?? null,
                        'notes' => $notes[$actId] ?? null
                    ];
                }
            }
        } else {
            foreach ($assessmentData as $level => $levelData) {
                if (isset($levelData['activities'])) {
                    foreach ($levelData['activities'] as $activityId => $score) {
                        $levelAchieved = $this->scoreToLetter($score);
                        
                        // Include all activities, even those rated as 'N'
                        // This preserves evidence and notes when levels are changed
                        $activityEvaluations[] = [
                            'activity_id' => $activityId,
                            'level_achieved' => $levelAchieved,
                            'evidence' => $levelData['evidence'][$activityId] ?? null,
                            'notes' => $levelData['notes'][$activityId] ?? null
                        ];
                    }
                }
            }
        }

        return [
            'activity_evaluations' => $activityEvaluations
        ];
    }

    /**
     * Convert score to letter grade
     */
    private function scoreToLetter($score)
    {
        if ($score > 0.85) return 'F';
        if ($score > 0.50) return 'L';
        if ($score > 0.15) return 'P';
        return 'N';
    }

    /**
     * Convert letter grade to score (for loading data)
     */
    public function letterToScore($letter)
    {
        $scoreMap = [
            'N' => 0.00,
            'P' => 1/3,
            'L' => 2/3,
            'F' => 1.00
        ];
        
        return $scoreMap[$letter] ?? 0.00;
    }

    /**
     * Create a new evaluation for a user
     */
    public function createNewEvaluation($userId)
    {
        try {
            DB::beginTransaction();
            
            $evaluation = MstEval::create([
                'user_id' => $userId
            ]);
            
            DB::commit();

            // refresh model to ensure any DB-generated fields are loaded
            try {
                $evaluation->refresh();
            } catch (\Exception $e) {
                // ignore refresh failures, return created model anyway
            }

            return $evaluation;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to create new evaluation", [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Get an evaluation by ID
     */
    public function getEvaluationById($evalId)
    {
        try {
            // coba cari by eval_id (custom) lalu fallback ke primary key
            $evaluation = MstEval::where('eval_id', $evalId)->first();
            if (!$evaluation && is_numeric($evalId)) {
                $evaluation = MstEval::find($evalId);
            }
            return $evaluation;
        } catch (\Exception $e) {
            Log::error("Failed to get evaluation by ID", [
                'eval_id' => $evalId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get all evaluations for a user
     */
    public function getUserEvaluations($userId = null)
    {
        $userId = $userId ?? Auth::id();
        
        return MstEval::with('activityEvaluations')
            ->where('user_id', $userId)
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    /**
     * Delete an evaluation
     */
    public function deleteEvaluation($evalId)
    {
        try {
            DB::beginTransaction();
            
            $evaluation = MstEval::findOrFail($evalId);
            
            TrsActivityeval::where('eval_id', $evalId)->delete();
            
            $evaluation->delete();
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get basic evaluation statistics
     */
    public function getEvaluationStats($evalId)
    {
        $evaluation = MstEval::with('activityEvaluations')->findOrFail($evalId);
        
        $totalActivities = $evaluation->activityEvaluations->count();
        $achievementCounts = $evaluation->activityEvaluations
            ->groupBy('level_achieved')
            ->map->count();
            
        return [
            'total_activities' => $totalActivities,
            'achievement_distribution' => [
                'N' => $achievementCounts['N'] ?? 0,
                'P' => $achievementCounts['P'] ?? 0,
                'L' => $achievementCounts['L'] ?? 0,
                'F' => $achievementCounts['F'] ?? 0,
            ]
        ];
    }
    /**
     * Calculate the overall IT Maturity Score for an evaluation
     * Logic exactly matches the AssessmentEvalController::report method
     */
    public function calculateMaturityScore($evalId)
    {
        $evaluation = MstEval::with([
            'activityEvaluations.activity.practices.objective'
        ])->find($evalId);

        if (!$evaluation) {
            return 0;
        }

        $formattedData = $this->loadEvaluation($evalId);
        $activityData = $formattedData['activity_evaluations'] ?? [];

        // Determine selected domains/objectives based on filled activities
        $relevantActivityIds = array_keys($activityData);
        if (empty($relevantActivityIds)) {
            return 0;
        }

        // Fetch objectives that are relevant to this evaluation (based on activities)
        // This ensures we don't average in 0s for objectives that weren't selected
        $objectives = \App\Models\MstObjective::whereHas('practices.activities', function($q) use ($relevantActivityIds) {
            $q->whereIn('activity_id', $relevantActivityIds);
        })->with(['practices.activities'])->get();

        if ($objectives->isEmpty()) {
            return 0;
        }
        
        $ratingMap = ['N' => 0.0, 'P' => 1.0/3.0, 'L' => 2.0/3.0, 'F' => 1.0];
        $calculatedLevels = [];

        foreach ($objectives as $obj) {
            $activitiesByLevel = [2 => [], 3 => [], 4 => [], 5 => []];
            $allLevelsFound = [];

            foreach ($obj->practices as $p) {
                if ($p->activities) {
                    foreach ($p->activities as $a) {
                        $lvl = (int)$a->capability_lvl;
                        if ($lvl >= 2 && $lvl <= 5) {
                            $activitiesByLevel[$lvl][] = $a;
                            $allLevelsFound[] = $lvl;
                        }
                    }
                }
            }

            if (empty($allLevelsFound)) {
                $calculatedLevels[] = 0;
                continue;
            }
            
            $minLevel = min($allLevelsFound);

            $getScore = function($lvl) use ($minLevel, $activitiesByLevel, $activityData, $ratingMap) {
                if ($lvl < $minLevel) return 1.0;
                
                $acts = $activitiesByLevel[$lvl] ?? [];
                if (empty($acts)) return 0.0;

                $vals = 0;
                foreach ($acts as $a) {
                    $r = $activityData[$a->activity_id]['level_achieved'] ?? 'N';
                    $vals += ($ratingMap[$r] ?? 0.0);
                }
                return $vals / count($acts);
            };

            $score2 = $getScore(2);
            $score3 = $getScore(3);
            $score4 = $getScore(4);
            $score5 = $getScore(5);

            $finalLevel = 0;
            if ($score2 <= 0.15) $finalLevel = 0;
            elseif ($score2 <= 0.50) $finalLevel = 1;
            elseif ($score2 <= 0.85) $finalLevel = 2;
            else {
                if ($score3 <= 0.50) $finalLevel = 2;
                elseif ($score3 <= 0.85) $finalLevel = 3;
                else {
                    if ($score4 <= 0.50) $finalLevel = 3;
                    elseif ($score4 <= 0.85) $finalLevel = 4;
                    else {
                        if ($score5 <= 0.50) $finalLevel = 4;
                        else $finalLevel = 5;
                    }
                }
            }

            if ($minLevel > 2) {
                $startScore = $getScore($minLevel);
                if ($startScore <= 0.15) $finalLevel = 0;
            }

            $calculatedLevels[] = $finalLevel;
        }

        if (empty($calculatedLevels)) {
            return 0;
        }

        // Return average of all objective scores
        return array_sum($calculatedLevels) / count($calculatedLevels);
    }

    /**
     * Calculate and update score tables (TrsObjectiveScore & TrsMaturityScore)
     */
    public function updateCalculatedScores($evalId)
    {
        $evaluation = MstEval::find($evalId);
        if (!$evaluation) return;

        // 1. Calculate Score (reuse existing logic)
        // We need to slightly modify calculateMaturityScore or extract the core logic
        // so we can get both per-objective scores AND the final score.
        // Let's copy the logic here for clarity or refactor.
        // For safety and speed now, let's implement the core logic here to save both.

        $activityData = TrsActivityeval::where('eval_id', $evalId)->get()->keyBy('activity_id');
        $relevantActivityIds = $activityData->keys()->toArray();

        // Clear existing scores for this evaluation to ensure clean state
        TrsObjectiveScore::where('eval_id', $evalId)->delete();
        TrsMaturityScore::where('eval_id', $evalId)->delete();

        // Use Scope (Selected Domains) to determine relevant objectives, EXACTLY like the report page
        // If we only look at "activities with data", we might miss 0-scored objectives that are part of the scope
        // causing the average to be higher (smaller divisor).
        
        $selectedDomains = \App\Models\TrsEvalDetail::where('eval_id', $evalId)->pluck('domain_id')->unique()->toArray();

        if (!empty($selectedDomains)) {
            $objectives = \App\Models\MstObjective::with(['practices.activities'])
                ->where(function ($q) use ($selectedDomains) {
                    foreach ($selectedDomains as $domain) {
                        $domain = trim((string)$domain);
                        if ($domain !== '') {
                            $q->orWhere('objective_id', 'like', $domain . '%');
                        }
                    }
                })->get();
        } else {
            // Fallback: If no scope defined, should we verify all? 
            // Or fallback to activities? Report falls back to ALL.
            // Let's stick to ALL to match Report logic 1:1.
            $objectives = \App\Models\MstObjective::with(['practices.activities'])->get();
        }

        if ($objectives->isEmpty()) {
            TrsMaturityScore::create(['eval_id' => $evalId, 'score' => 0]);
            return;
        }

        $ratingMap = ['N' => 0.0, 'P' => 1.0/3.0, 'L' => 2.0/3.0, 'F' => 1.0];
        $calculatedLevels = [];

        foreach ($objectives as $obj) {
            $activitiesByLevel = [2 => [], 3 => [], 4 => [], 5 => []];
            $allLevelsFound = [];

            foreach ($obj->practices as $p) {
                if ($p->activities) {
                    foreach ($p->activities as $a) {
                        $lvl = (int)$a->capability_lvl;
                        if ($lvl >= 2 && $lvl <= 5) {
                            $activitiesByLevel[$lvl][] = $a;
                            $allLevelsFound[] = $lvl;
                        }
                    }
                }
            }

            if (empty($allLevelsFound)) {
                $calculatedLevels[] = 0;
                // Save objective score 0
                TrsObjectiveScore::create([
                    'eval_id' => $evalId,
                    'objective_id' => $obj->objective_id,
                    'level' => 0
                ]);
                continue;
            }
            
            $minLevel = min($allLevelsFound);

            $getScore = function($lvl) use ($minLevel, $activitiesByLevel, $activityData, $ratingMap) {
                if ($lvl < $minLevel) return 1.0;
                
                $acts = $activitiesByLevel[$lvl] ?? [];
                if (empty($acts)) return 0.0;

                $vals = 0;
                foreach ($acts as $a) {
                    // We need to look up in the collection we fetched
                    $actRecord = $activityData[$a->activity_id] ?? null;
                    $r = $actRecord ? $actRecord->level_achieved : 'N';
                    $vals += ($ratingMap[$r] ?? 0.0);
                }
                return $vals / count($acts);
            };

            $score2 = $getScore(2);
            $score3 = $getScore(3);
            $score4 = $getScore(4);
            $score5 = $getScore(5);

            $finalLevel = 0;
            if ($score2 <= 0.15) $finalLevel = 0;
            elseif ($score2 <= 0.50) $finalLevel = 1;
            elseif ($score2 <= 0.85) $finalLevel = 2;
            else {
                if ($score3 <= 0.50) $finalLevel = 2;
                elseif ($score3 <= 0.85) $finalLevel = 3;
                else {
                    if ($score4 <= 0.50) $finalLevel = 3;
                    elseif ($score4 <= 0.85) $finalLevel = 4;
                    else {
                        if ($score5 <= 0.50) $finalLevel = 4;
                        else $finalLevel = 5;
                    }
                }
            }

            if ($minLevel > 2) {
                $startScore = $getScore($minLevel);
                if ($startScore <= 0.15) $finalLevel = 0;
            }

            $calculatedLevels[] = $finalLevel;

            // Save objective score
            TrsObjectiveScore::create([
                'eval_id' => $evalId,
                'objective_id' => $obj->objective_id,
                'level' => $finalLevel
            ]);
        }

        $avgScore = empty($calculatedLevels) ? 0 : (array_sum($calculatedLevels) / count($calculatedLevels));

        // Save overall maturity score
        TrsMaturityScore::create([
            'eval_id' => $evalId,
            'score' => $avgScore
        ]);
    }


    /**
     * Helper: Get and sort objectives
     */
    public function getSortedObjectives()
    {
        $objectives = \App\Models\MstObjective::with(['practices.activities'])->get();
        $domainOrder = ['EDM' => 1, 'APO' => 2, 'BAI' => 3, 'DSS' => 4, 'MEA' => 5];
        
        return $objectives->sortBy(function($obj) use ($domainOrder) {
            $prefix = preg_replace('/[0-9]+/', '', $obj->objective_id);
            if (empty($prefix)) $prefix = substr($obj->objective_id, 0, 3);
            $rank = $domainOrder[$prefix] ?? 99;
            return sprintf('%02d_%s', $rank, $obj->objective_id);
        })->values();
    }

    /**
     * Helper: Fetch Target Capabilities as map
     */
    public function fetchTargetCapabilities($evaluation)
    {
        $targetCapabilityMap = [];
        $assessmentYear = $evaluation->tahun ?? $evaluation->year ?? $evaluation->assessment_year ?? null;
        
        if ($assessmentYear) {
            $targetCapability = \App\Models\TargetCapability::where('user_id', $evaluation->user_id)
                ->where('tahun', (int) $assessmentYear)
                ->latest('updated_at')
                ->first();

            if ($targetCapability) {
                // List of all 40 GAMOs
                $fields = [
                    'EDM01','EDM02','EDM03','EDM04','EDM05',
                    'APO01','APO02','APO03','APO04','APO05','APO06','APO07','APO08','APO09','APO10','APO11','APO12','APO13','APO14',
                    'BAI01','BAI02','BAI03','BAI04','BAI05','BAI06','BAI07','BAI08','BAI09','BAI10','BAI11',
                    'DSS01','DSS02','DSS03','DSS04','DSS05','DSS06',
                    'MEA01','MEA02','MEA03','MEA04',
                ];
                foreach ($fields as $field) {
                    $targetCapabilityMap[$field] = $targetCapability->$field !== null ? (int) $targetCapability->$field : null;
                }
            }
        }
        return $targetCapabilityMap;
    }

    /**
     * Helper: Calculate maturity for a single objective
     */
    public function calculateObjectiveMaturity($obj, $activityData)
    {
        $ratingMap = ['N' => 0.0, 'P' => 1.0/3.0, 'L' => 2.0/3.0, 'F' => 1.0];
        $activitiesByLevel = [2 => [], 3 => [], 4 => [], 5 => []];
        $allLevelsFound = [];

        foreach ($obj->practices as $p) {
            if ($p->activities) {
                foreach ($p->activities as $a) {
                    $lvl = (int)$a->capability_lvl;
                    if ($lvl >= 2 && $lvl <= 5) {
                        $activitiesByLevel[$lvl][] = $a;
                        $allLevelsFound[] = $lvl;
                    }
                }
            }
        }

        if (empty($allLevelsFound)) return 0;
        
        $minLevel = min($allLevelsFound);

        $getScore = function($lvl) use ($minLevel, $activitiesByLevel, $activityData, $ratingMap) {
            if ($lvl < $minLevel) return 1.0;
            $acts = $activitiesByLevel[$lvl] ?? [];
            if (empty($acts)) return 0.0;
            $vals = 0;
            foreach ($acts as $a) {
                // Check if activityData is array or object/model
                if (is_array($activityData)) {
                    // Check if it's nested structure [activity_id => [level_achieved => ...]]
                    if (isset($activityData[$a->activity_id]['level_achieved'])) {
                        $r = $activityData[$a->activity_id]['level_achieved'];
                    } else {
                        // Or maybe direct key access if flat? Assuming nested based on common usage
                        $r = 'N'; 
                    }
                } else {
                    // Assuming collection or object
                     $r = isset($activityData[$a->activity_id]) ? $activityData[$a->activity_id]['level_achieved'] : 'N';
                }
                
                $vals += ($ratingMap[$r] ?? 0.0);
            }
            return $vals / count($acts);
        };

        $score2 = $getScore(2);
        $score3 = $getScore(3);
        $score4 = $getScore(4);
        $score5 = $getScore(5);

        $finalLevel = 0;
        if ($score2 <= 0.15) {
            $finalLevel = 0;
        } elseif ($score2 <= 0.50) {
            $finalLevel = 1;
        } elseif ($score2 <= 0.85) {
            $finalLevel = 2;
        } else {
            if ($score3 <= 0.50) {
                $finalLevel = 2;
            } elseif ($score3 <= 0.85) {
                $finalLevel = 3;
            } else {
                if ($score4 <= 0.50) {
                    $finalLevel = 3;
                } elseif ($score4 <= 0.85) {
                    $finalLevel = 4;
                } else {
                    $finalLevel = ($score5 <= 0.50) ? 4 : 5;
                }
            }
        }

        if ($minLevel > 2) {
            $startScore = $getScore($minLevel);
            if ($startScore <= 0.15) $finalLevel = 0;
        }

        return $finalLevel;
    }
}
