<?php

namespace App\Http\Controllers\AssessmentEval;

use App\Http\Controllers\Controller;
use App\Services\EvaluationService;
use App\Models\MstObjective;
use App\Models\MstEval;
use App\Models\MstEvidence;
use App\Models\TrsEvalDetail;
use App\Models\TargetCapability;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AssessmentEvalController extends Controller
{
    protected $evaluationService;

    public function __construct(EvaluationService $evaluationService)
    {
        $this->evaluationService = $evaluationService;
    }

    /**
     * Display the list of assessments for the current user
     */
    /**
     * Display the list of assessments for the current user
     */
    public function listAssessments()
    {
        try {
            $user = Auth::user();
            $org = $user->organisasi ?? null;

            // 1. My Assessments Query
            $myQuery = MstEval::with(['user', 'maturityScore'])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc');

            // 2. Other Assessments Query
            $otherQuery = null;
            if ($org) {
                $otherQuery = MstEval::with(['user', 'maturityScore'])
                    ->where('user_id', '!=', $user->id)
                    ->whereHas('user', function ($q) use ($org) {
                        $q->where('organisasi', $org);
                    })
                    ->orderBy('created_at', 'desc');
            }

            // Paginate independently
            $myAssessments = $myQuery->paginate(10, ['*'], 'my_page');
            $otherAssessments = $otherQuery 
                ? $otherQuery->paginate(10, ['*'], 'other_page') 
                : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);

            // Collect all items from current pages to fetch metadata efficiently
            $allEvals = collect($myAssessments->items());
            if ($otherQuery) {
                $allEvals = $allEvals->merge($otherAssessments->items());
            }

            if ($allEvals->isNotEmpty()) {
                $evalIds = $allEvals->pluck('eval_id')->unique()->values()->all();

                // Fetch Achievement Counts
                $achRows = \App\Models\TrsActivityeval::whereIn('eval_id', $evalIds)
                    ->select('eval_id', 'level_achieved', DB::raw('count(*) as cnt'))
                    ->groupBy('eval_id', 'level_achieved')
                    ->get();
                
                $countsMap = [];
                foreach ($achRows as $r) {
                    $countsMap[$r->eval_id][$r->level_achieved] = (int) $r->cnt;
                }

                // Fetch Selected Domains
                $rows = TrsEvalDetail::whereIn('eval_id', $evalIds)->get();
                $selectedDomainsMap = [];
                foreach ($rows as $row) {
                    $selectedDomainsMap[$row->eval_id][] = $row->domain_id;
                }

                // Fetch Last Activity Dates
                $lastActivityDates = \App\Models\TrsActivityeval::whereIn('eval_id', $evalIds)
                    ->select('eval_id', DB::raw('MAX(updated_at) as last_activity_at'))
                    ->groupBy('eval_id')
                    ->pluck('last_activity_at', 'eval_id');

                // Calculate filled GAMO counts
                $filledGamoCounts = DB::table('trs_activityeval')
                    ->join('mst_activities', 'trs_activityeval.activity_id', '=', 'mst_activities.activity_id')
                    ->join('mst_practice', 'mst_activities.practice_id', '=', 'mst_practice.practice_id')
                    ->whereIn('trs_activityeval.eval_id', $evalIds)
                    ->select('trs_activityeval.eval_id', DB::raw('count(distinct mst_practice.objective_id) as filled_count'))
                    ->groupBy('trs_activityeval.eval_id')
                    ->pluck('filled_count', 'eval_id')
                    ->toArray();

                // Calculate activity counts per domain (System Constants)
                $domainActivityCounts = DB::table('mst_activities')
                    ->join('mst_practice', 'mst_activities.practice_id', '=', 'mst_practice.practice_id')
                    ->select('mst_practice.objective_id', DB::raw('count(*) as cnt'))
                    ->groupBy('mst_practice.objective_id')
                    ->pluck('cnt', 'objective_id')
                    ->toArray();
                
                $totalSystemActivities = array_sum($domainActivityCounts);

                // Fetch Target Capabilities for relevant years
                $years = $allEvals->pluck('tahun')->filter()->unique()->values()->all();
                $targetAverages = [];
                if (!empty($years)) {
                    $targetCaps = \App\Models\TargetCapability::whereIn('tahun', $years)
                        ->when($org, function($q) use ($org) {
                            $q->where('organisasi', $org);
                        }, function($q) use ($user) {
                            $q->where('user_id', $user->id);
                        })
                        ->get();

                    $domainCols = [
                        'EDM01','EDM02','EDM03','EDM04','EDM05',
                        'APO01','APO02','APO03','APO04','APO05','APO06','APO07','APO08','APO09','APO10','APO11','APO12','APO13','APO14',
                        'BAI01','BAI02','BAI03','BAI04','BAI05','BAI06','BAI07','BAI08','BAI09','BAI10','BAI11',
                        'DSS01','DSS02','DSS03','DSS04','DSS05','DSS06',
                        'MEA01','MEA02','MEA03','MEA04',
                    ];

                    foreach ($targetCaps as $tc) {
                        $sum = 0;
                        $count = 0;
                        foreach ($domainCols as $col) {
                            $val = $tc->$col;
                            // Consider average of all set targets (assuming 0 is valid but usually targets are 1-5, null/0 might be unset)
                            // If user sets 0 as target, it might drag down average. Assuming valid inputs are > 0 for now based on context.
                            if (!is_null($val) && is_numeric($val)) {
                                $sum += $val;
                                $count++;
                            }
                        }
                        $targetAverages[$tc->tahun] = $count > 0 ? round($sum / $count, 2) : 0;
                    }
                }

                // Transform/Hydrate items
                foreach ($allEvals as $evaluation) {
                    // Achievement counts
                    $evaluation->achievement_counts = $countsMap[$evaluation->eval_id] ?? [];

                    // Selected Domains details
                    $selectedDomains = $selectedDomainsMap[$evaluation->eval_id] ?? [];
                    $evaluation->selected_gamo_count = count($selectedDomains) > 0 ? count($selectedDomains) : 40;
                    $evaluation->filled_gamo_count = $filledGamoCounts[$evaluation->eval_id] ?? 0;
                    
                    $totalRatable = 0;
                    if (empty($selectedDomains)) {
                        $totalRatable = $totalSystemActivities;
                    } else {
                        foreach ($selectedDomains as $domain) {
                            $totalRatable += $domainActivityCounts[$domain] ?? 0;
                        }
                    }
                    $evaluation->total_ratable_activities = $totalRatable > 0 ? $totalRatable : 1;

                    // Last Updated
                    $evaluation->last_saved_at = $lastActivityDates[$evaluation->eval_id] ?? $evaluation->created_at;

                    // Average Target Capability
                    $evaluation->avg_target_capability = $targetAverages[$evaluation->tahun ?? ''] ?? 0;
                }
            }

            // Calculate total for hero card (Totals available in Paginators)
            $totalAssessments = $myAssessments->total();
            if ($otherQuery) {
                $totalAssessments += $otherAssessments->total();
            }

            // Calculate Global Stats for Hero Card (Finished vs Draft)
            // We need to replicate the scope logic to get accurate counts
            $statsQuery = MstEval::query();
            if ($org) {
                 $statsQuery->whereHas('user', function ($q) use ($org) {
                     $q->where('organisasi', $org);
                 });
            } else {
                 $statsQuery->where('user_id', $user->id);
            }
            // Clone query for finished count
            $finishedAssessments = (clone $statsQuery)->where('status', 'finished')->count();
            $draftAssessments = $totalAssessments - $finishedAssessments;
            if ($draftAssessments < 0) $draftAssessments = 0;

            return view('assessment-eval.list', compact('myAssessments', 'otherAssessments', 'totalAssessments', 'finishedAssessments', 'draftAssessments'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to load assessments: ' . $e->getMessage()]);
        }
    }

    /**
     * Create a new assessment for the current user
     */
    public function createAssessment(Request $request)
    {
        try {
            // 1. Buat evaluasi dasar via service
            $evaluation = $this->evaluationService->createNewEvaluation(Auth::id());
            
            // 2. Update Tahun Assessment (Tambahan Baru)
            // Ambil dari request, jika kosong default ke tahun sekarang
            // Ambil dari request, jika kosong default ke tahun sekarang
            $tahun = $request->input('tahun', date('Y'));
            $evaluation->tahun = $tahun;
            $evaluation->save();

            $verifyEvaluation = $this->evaluationService->getEvaluationById($evaluation->eval_id);
            
            if (!$verifyEvaluation || (string)$verifyEvaluation->user_id !== (string)Auth::id()) {
                Log::error("Created evaluation verification failed", [
                    'eval_id' => $evaluation->eval_id,
                    'user_id' => Auth::id(),
                    'user_id_type' => gettype(Auth::id()),
                    'evaluation_user_id' => $verifyEvaluation ? $verifyEvaluation->user_id : null,
                    'evaluation_user_id_type' => $verifyEvaluation ? gettype($verifyEvaluation->user_id) : null,
                    'verification_result' => $verifyEvaluation ? 'found but wrong user' : 'not found'
                ]);
                throw new \Exception('Failed to verify created assessment');
            }
            
            // store selected GAMO/domain mappings if provided (do in a transaction)
            try {
                $selected = $request->input('selected_gamos', []);
                if ($selected && !is_array($selected)) {
                    $selected = array_map('trim', explode(',', (string)$selected));
                }

                $scopeName = $request->input('nama_scope') ?: 'Default';

                if (!empty($selected)) {
                    DB::transaction(function () use ($evaluation, $selected, $scopeName) {
                        // Create or get the scoping record (parent)
                        $scoping = \App\Models\TrsScoping::firstOrCreate(
                            [
                                'eval_id' => $evaluation->eval_id,
                                'nama_scope' => $scopeName
                            ]
                        );

                        // Remove existing details for this scoping to avoid duplicates
                        TrsEvalDetail::where('scoping_id', $scoping->id)->delete();

                        $inserts = [];
                        foreach ($selected as $domainId) {
                            $domainId = trim((string)$domainId);
                            if ($domainId !== '') {
                                $inserts[] = [
                                    'eval_id' => $evaluation->eval_id,
                                    'scoping_id' => $scoping->id,
                                    'domain_id' => $domainId,
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ];
                            }
                        }

                        if (!empty($inserts)) {
                            // bulk insert for efficiency
                            TrsEvalDetail::insert($inserts);
                        }
                    });
                }
            } catch (\Exception $e) {
                // log but allow creation to succeed — mapping is optional
                Log::warning('Failed to save selected GAMO mapping for eval', [
                    'eval_id' => $evaluation->eval_id,
                    'error' => $e->getMessage()
                ]);
            }

            return redirect()->route('assessment-eval.show', ['evalId' => $evaluation->encrypted_id]);
        } catch (\Exception $e) {
            Log::error("Failed to create assessment", [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withErrors(['error' => 'Failed to create assessment: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the assessment evaluation page for a specific evaluation
     */
    public function showAssessment($evalId)
    {
        try {
            $evaluation = $this->evaluationService->getEvaluationById($evalId);
            
            if (!$evaluation) {
                Log::warning("Assessment not found in database when showing", [
                    'requested_eval_id' => $evalId,
                    'requesting_user_id' => Auth::id(),
                    'user_evaluations_count' => count($this->evaluationService->getUserEvaluations() ?? [])
                ]);
                return redirect()->route('assessment-eval.list')->withErrors(['error' => 'Assessment not found']);
            }
            
            // Allow viewing if requester is owner OR belongs to the same organization as owner
            $owner = User::find($evaluation->user_id);
            $currentUser = Auth::user();

            $canView = false;
            $isOwner = false;
            if ($owner && $currentUser) {
                if ((string)$evaluation->user_id === (string)$currentUser->id) {
                    $canView = true; // owner
                    $isOwner = true;
                } elseif (!empty($owner->organisasi) && !empty($currentUser->organisasi) && strcasecmp(trim((string)$owner->organisasi), trim((string)$currentUser->organisasi)) === 0) {
                    // treat organisasi as equal if same after trimming and case-insensitive compare
                    $canView = true; // same organization -> view-only
                }
            }

            if (!$canView) {
                // Log detailed context to help debug ownership/organization mismatches
                $requestingUserOrg = $currentUser->organisasi ?? null;
                $ownerOrg = $owner->organisasi ?? null;
                Log::warning('Assessment access denied (showAssessment)', [
                    'eval_id' => $evalId,
                    'requesting_user_id' => Auth::id(),
                    'requesting_user_org' => $requestingUserOrg,
                    'owner_user_id' => $evaluation->user_id,
                    'owner_org' => $ownerOrg,
                    'is_owner_flag' => $isOwner
                ]);

                // Provide a friendly error to the UI; logs contain the debug info.
                return redirect()->route('assessment-eval.list')->withErrors(['error' => 'Assessment not found or access denied']);
            }
            // Fetch all scopes for this evaluation
            $allScopes = \App\Models\TrsScoping::where('eval_id', $evalId)->get();
            
            // Determine active scope (from request or default to first)
            $activeScopeId = request()->input('scope_id');
            if ($activeScopeId) {
                $activeScope = $allScopes->firstWhere('id', $activeScopeId);
            } else {
                $activeScope = $allScopes->first();
            }

            // Fetch selected domains based on active scope
            if ($activeScope) {
                $selectedDomains = TrsEvalDetail::where('scoping_id', $activeScope->id)
                    ->pluck('domain_id')->unique()->toArray();
            } else {
                // Fallback: fetch all domains for this eval (backward compatibility)
                $selectedDomains = TrsEvalDetail::where('eval_id', $evalId)
                    ->pluck('domain_id')->unique()->toArray();
            }

            if (!empty($selectedDomains)) {
                $objectives = MstObjective::with(['practices.activities'])
                    ->where(function ($q) use ($selectedDomains) {
                        foreach ($selectedDomains as $domain) {
                            $domain = trim((string)$domain);
                            if ($domain !== '') {
                                // match objective_id prefix (e.g. EDM -> EDM01, EDM02...)
                                $q->orWhere('objective_id', 'like', $domain . '%');
                            }
                        }
                    })->get();
            } else {
                $objectives = MstObjective::with(['practices.activities'])->get();
            }

            // Fetch evidences for this evaluation
            $evidences = MstEvidence::where('eval_id', $evalId)->orderBy('created_at', 'desc')->get();

            // Fetch Target Capability for matching assessment year (if available)
            $targetCapabilityMap = [];
            $assessmentYear = $evaluation->tahun ?? $evaluation->year ?? $evaluation->assessment_year ?? null;
            if ($assessmentYear) {
                $targetCapability = TargetCapability::where('user_id', $evaluation->user_id)
                    ->where('tahun', (int) $assessmentYear)
                    ->latest('updated_at')
                    ->first();

                if ($targetCapability) {
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

            // Pass all objectives for scope editing modal
            $allObjectives = MstObjective::all();

            // Build scope details map for displaying in table
            $scopeDetails = [];
            foreach ($allScopes as $scope) {
                $scopeDetails[$scope->id] = [
                    'name' => $scope->nama_scope,
                    'domains' => TrsEvalDetail::where('scoping_id', $scope->id)
                        ->pluck('domain_id')->toArray()
                ];
            }

            // Pass isOwner and scope data to the view
            return view('assessment-eval.show', compact(
                'objectives', 
                'allObjectives', 
                'evalId', 
                'evaluation', 
                'isOwner', 
                'evidences', 
                'targetCapabilityMap',
                'allScopes',
                'activeScope',
                'scopeDetails'
            ));
        } catch (\Exception $e) {
            Log::error("Failed to load assessment", [
                'eval_id' => $evalId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('assessment-eval.list')->withErrors(['error' => 'Failed to load assessment: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the scope (selected objectives) for an assessment
     */
    public function updateScope(Request $request, $evalId)
    {
        try {
            $evaluation = $this->evaluationService->getEvaluationById($evalId);

            if (!$evaluation || (string)$evaluation->user_id !== (string)Auth::id()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            if ($evaluation->status === 'finished') {
                return response()->json(['success' => false, 'message' => 'Assessment is finished'], 403);
            }

            $selectedScopes = $request->input('scopes', []); // Expecting array of objective IDs like ['EDM01', 'APO02']
            $isNewScope = $request->input('is_new', false);
            $scopeName = $request->input('nama_scope');

            DB::transaction(function () use ($evalId, $selectedScopes, $isNewScope, $scopeName, $request) {
                if ($isNewScope && $scopeName) {
                    // Create new scope
                    $scoping = \App\Models\TrsScoping::create([
                        'eval_id' => $evalId,
                        'nama_scope' => $scopeName
                    ]);

                    // Add domains to new scope
                    $inserts = [];
                    foreach ($selectedScopes as $scope) {
                        $scope = trim((string)$scope);
                        if ($scope !== '') {
                            $inserts[] = [
                                'eval_id' => $evalId,
                                'scoping_id' => $scoping->id,
                                'domain_id' => $scope,
                                'created_at' => now(),
                                'updated_at' => now()
                            ];
                        }
                    }

                    if (!empty($inserts)) {
                        TrsEvalDetail::insert($inserts);
                    }
                } else {
                    // Editing existing scope - check if scope_id provided
                    $scopeId = $request->input('scope_id');
                    
                    if ($scopeId) {
                        // Update specific scope
                        $scopeToUpdate = \App\Models\TrsScoping::where('id', $scopeId)
                            ->where('eval_id', $evalId)
                            ->first();

                        // Update scope name if provided
                        if ($scopeToUpdate && $scopeName) {
                            $scopeToUpdate->update(['nama_scope' => $scopeName]);
                        }
                    } else {
                        // Fallback: update first scope (backward compatibility)
                        $scopeToUpdate = \App\Models\TrsScoping::where('eval_id', $evalId)->first();
                    }
                    
                    if ($scopeToUpdate) {
                        // Remove existing details for this scope
                        TrsEvalDetail::where('scoping_id', $scopeToUpdate->id)->delete();

                        // Insert new domains
                        $inserts = [];
                        foreach ($selectedScopes as $scope) {
                            $scope = trim((string)$scope);
                            if ($scope !== '') {
                                $inserts[] = [
                                    'eval_id' => $evalId,
                                    'scoping_id' => $scopeToUpdate->id,
                                    'domain_id' => $scope,
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ];
                            }
                        }

                        if (!empty($inserts)) {
                            TrsEvalDetail::insert($inserts);
                        }
                    }
                }
            });
            
            // NOTE: We do NOT delete assessment data (TrsActivityeval) for removed scopes.
            // This is safer. Orphaned data just won't show up in the view because the view filters based on selected scope.

            return response()->json([
                'success' => true,
                'message' => 'Scope updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to update scope", [
                'eval_id' => $evalId,
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to update scope: ' . $e->getMessage()], 500);
        }
    }

    public function deleteScope(Request $request)
    {
        try {
            $scopeId = $request->input('scope_id');
            $scope = \App\Models\TrsScoping::find($scopeId);

            if ($scope) {
                $scope->delete(); // Soft delete
                return response()->json(['success' => true]);
            }
            
            return response()->json(['success' => false, 'message' => 'Scope not found']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get all objectives for the modal (optional helper if needed via AJAX, but passing to view is easier)
     */
    public function getObjectives() {
        return response()->json(MstObjective::select('objective_id', 'objective')->get());
    }

    /**
     * Display the assessment evaluation page (legacy support)
     */
    public function index()
    {
        return redirect()->route('assessment-eval.list');
    }

    /**
     * Save assessment data for a specific evaluation
     */
    public function save(Request $request, $evalId)
    {
        try {
            $evaluation = $this->evaluationService->getEvaluationById($evalId);
            
            if (!$evaluation || (string)$evaluation->user_id !== (string)Auth::id()) {
                Log::warning('Save access denied', [
                    'auth_id' => Auth::id(),
                    'eval_id' => $evalId,
                    'evaluation_user_id' => $evaluation ? $evaluation->user_id : null
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Assessment not found or access denied'
                ], 404);
            }

            if ($evaluation->status === 'finished') {
                return response()->json([
                    'success' => false,
                    'message' => 'Assessment is finished and cannot be modified. Please unlock it first.'
                ], 403);
            }

            $data = $this->evaluationService->convertAssessmentData($request->all());
            $data['user_id'] = Auth::id();
            $data['eval_id'] = $evalId;

            $this->evaluationService->saveEvaluation($data);

            return response()->json([
                'success' => true,
                'message' => 'Assessment saved successfully',
                'eval_id' => $evalId
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save assessment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Load assessment data for a specific evaluation
     */
    public function load($evalId)
    {
        try {
            $evaluation = $this->evaluationService->getEvaluationById($evalId);
            
            // Separate checks for better debugging
            if (!$evaluation) {
                Log::warning('Load requested for missing assessment', ['requested_eval_id' => $evalId, 'auth_id' => Auth::id()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Assessment not found'
                ], 404);
            }
            
            // Allow load if requester is owner OR belongs to the same organization as owner
            $owner = User::find($evaluation->user_id);
            $currentUser = Auth::user();

            $isOwner = false;
            $canLoad = false;
            if ($currentUser && $owner) {
                if ((string)$evaluation->user_id === (string)$currentUser->id) {
                    $isOwner = true;
                    $canLoad = true;
                } elseif (!empty($owner->organisasi) && !empty($currentUser->organisasi) && strcasecmp(trim((string)$owner->organisasi), trim((string)$currentUser->organisasi)) === 0) {
                    // normalize organisasi comparison (trim + case-insensitive)
                    $canLoad = true; // same organization -> view-only
                }
            }

            if (!$canLoad) {
                Log::warning('Load access denied', [
                    'auth_id' => Auth::id(),
                    'requested_eval_id' => $evalId,
                    'owner_user_id' => $evaluation->user_id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied - you are not allowed to view this assessment'
                ], 403);
            }

            $data = $this->evaluationService->loadEvaluation($evalId);
            
            $assessmentData = [];
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
                    'assessmentData' => $assessmentData,
                    'notes' => $notes,
                    'evidence' => $evidence,
                    'activityData' => $data['activity_evaluations'],
                    'isOwner' => $isOwner
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load assessment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's evaluations list
     */
    public function getUserEvaluations()
    {
        try {
            $evaluations = $this->evaluationService->getUserEvaluations();
            
            return response()->json([
                'success' => true,
                'data' => $evaluations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get evaluations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an evaluation
     */
    public function delete($evalId)
    {
        try {
            $evaluation = MstEval::where('eval_id', $evalId)
                ->where('user_id', Auth::id())
                ->firstOrFail();
            
            // Soft delete (handled by Trait)
            $evaluation->delete();
            
            return redirect()->route('assessment-eval.list')->with('success', 'Assessment berhasil dihapus (soft delete).');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Gagal menghapus assessment: ' . $e->getMessage()]);
        }
    }

    /**
     * Finish the assessment
     */
    public function finish($evalId)
    {
        try {
            $evaluation = $this->evaluationService->getEvaluationById($evalId);
            
            if (!$evaluation) {
                return response()->json(['message' => 'Assessment not found'], 404);
            }
            
            if ((string)$evaluation->user_id !== (string)Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $evaluation->status = 'finished';
            $evaluation->save();

            return response()->json(['message' => 'Assessment finished successfully']);
        } catch (\Exception $e) {
            Log::error("Failed to finish assessment", [
                'eval_id' => $evalId,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Failed to finish assessment: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Unlock the assessment
     */
    public function unlock($evalId)
    {
        try {
            $evaluation = $this->evaluationService->getEvaluationById($evalId);
            
            if (!$evaluation) {
                return response()->json(['message' => 'Assessment not found'], 404);
            }
            
            if ((string)$evaluation->user_id !== (string)Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $evaluation->status = 'in_progress';
            $evaluation->save();

            return response()->json(['message' => 'Assessment unlocked successfully']);
        } catch (\Exception $e) {
            Log::error("Failed to unlock assessment", [
                'eval_id' => $evalId,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Failed to unlock assessment: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the evidence list for an assessment
     */
    public function evidenceIndex($evalId)
    {
        try {
            $evaluation = $this->evaluationService->getEvaluationById($evalId);
            
            if (!$evaluation) {
                return redirect()->route('assessment-eval.list')->withErrors(['error' => 'Assessment not found']);
            }

            // Check authorization (similar to showAssessment)
            $owner = User::find($evaluation->user_id);
            $currentUser = Auth::user();
            $canView = false;
            $isOwner = false;

            if ($owner && $currentUser) {
                if ((string)$evaluation->user_id === (string)$currentUser->id) {
                    $canView = true;
                    $isOwner = true;
                } elseif (!empty($owner->organisasi) && !empty($currentUser->organisasi) && strcasecmp(trim((string)$owner->organisasi), trim((string)$currentUser->organisasi)) === 0) {
                    $canView = true;
                }
            }

            if (!$canView) {
                return redirect()->route('assessment-eval.list')->withErrors(['error' => 'Access denied']);
            }

            $evidences = MstEvidence::where('eval_id', $evalId)->orderBy('created_at', 'desc')->get();

            return view('assessment-eval.evidence', compact('evaluation', 'evidences', 'evalId', 'isOwner'));

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to load evidence: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the report page for an assessment
     */
    public function report($evalId)
    {
        try {
            $evaluation = $this->evaluationService->getEvaluationById($evalId);
            
            if (!$evaluation) {
                return redirect()->route('assessment-eval.list')->withErrors(['error' => 'Assessment not found']);
            }

            // Check authorization
            $owner = User::find($evaluation->user_id);
            $currentUser = Auth::user();
            $canView = false;
            $isOwner = false;

            if ($owner && $currentUser) {
                if ((string)$evaluation->user_id === (string)$currentUser->id) {
                    $canView = true;
                    $isOwner = true;
                } elseif (!empty($owner->organisasi) && !empty($currentUser->organisasi) && strcasecmp(trim((string)$owner->organisasi), trim((string)$currentUser->organisasi)) === 0) {
                    $canView = true;
                }
            }

            if (!$canView) {
                return redirect()->route('assessment-eval.list')->withErrors(['error' => 'Access denied']);
            }

            // Fetch ALL scopes for this evaluation
            $allScopes = \App\Models\TrsScoping::where('eval_id', $evalId)->get();

            // Get ALL 40 objectives (not just selected ones)
            $objectives = MstObjective::with(['practices.activities'])->get();

            // Sort objectives by EDM, APO, BAI, DSS, MEA
            $domainOrder = ['EDM' => 1, 'APO' => 2, 'BAI' => 3, 'DSS' => 4, 'MEA' => 5];
            $objectives = $objectives->sortBy(function($obj) use ($domainOrder) {
                $prefix = preg_replace('/[0-9]+/', '', $obj->objective_id);
                if (empty($prefix)) $prefix = substr($obj->objective_id, 0, 3);
                $rank = $domainOrder[$prefix] ?? 99;
                return sprintf('%02d_%s', $rank, $obj->objective_id);
            })->values();

            // Fetch Target Capability
            $targetCapabilityMap = [];
            $assessmentYear = $evaluation->tahun ?? $evaluation->year ?? $evaluation->assessment_year ?? null;
            if ($assessmentYear) {
                $targetCapability = TargetCapability::where('user_id', $evaluation->user_id)
                    ->where('tahun', (int) $assessmentYear)
                    ->latest('updated_at')
                    ->first();

                if ($targetCapability) {
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

            // Fetch Assessment Data
            $loadedData = $this->evaluationService->loadEvaluation($evalId);
            $activityData = $loadedData['activity_evaluations'] ?? [];
            $ratingMap = ['N' => 0.0, 'P' => 1.0/3.0, 'L' => 2.0/3.0, 'F' => 1.0];

            // Helper function to calculate maturity level for an objective
            $calculateMaturity = function($obj) use ($activityData, $ratingMap) {
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
            };

            // Calculate maturity data for EACH scope
            $scopeMaturityData = [];
            foreach ($allScopes as $scope) {
                // Get domains in this scope
                $scopeDomains = TrsEvalDetail::where('scoping_id', $scope->id)
                    ->pluck('domain_id')->toArray();
                
                // Calculate maturity only for objectives in this scope
                $scopeMaturityData[$scope->id] = [];
                foreach ($objectives as $obj) {
                    // Check if this objective is in the scope
                    $isInScope = in_array($obj->objective_id, $scopeDomains);
                    
                    if ($isInScope) {
                        $scopeMaturityData[$scope->id][$obj->objective_id] = $calculateMaturity($obj);
                    } else {
                        $scopeMaturityData[$scope->id][$obj->objective_id] = null; // Not in scope
                    }
                }
            }

            return view('assessment-eval.report', compact(
                'objectives', 
                'evalId', 
                'evaluation', 
                'isOwner', 
                'targetCapabilityMap', 
                'allScopes',
                'scopeMaturityData'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to load report: ' . $e->getMessage()]);
        }
    }

    public function getMaturityScore($evalId)
    {
        try {
            $score = $this->evaluationService->calculateMaturityScore($evalId);
            return response()->json([
                'success' => true,
                'score' => round($score, 2)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch evidences from previous assessments in the same organization (excluding current eval).
     */
    public function previousEvidences($evalId)
    {
        try {
            $evaluation = $this->evaluationService->getEvaluationById($evalId);
            if (!$evaluation) {
                return response()->json(['success' => false, 'message' => 'Assessment not found'], 404);
            }

            $currentUser = Auth::user();
            $owner = User::find($evaluation->user_id);

            if (!$currentUser || !$owner) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            // Authorization: allow owner or same organization
            $sameOrg = !empty($owner->organisasi) && !empty($currentUser->organisasi)
                && strcasecmp(trim((string)$owner->organisasi), trim((string)$currentUser->organisasi)) === 0;

            if ((string)$currentUser->id !== (string)$owner->id && !$sameOrg) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $orgName = trim((string)$owner->organisasi);
            $userIds = User::when($orgName !== '', function ($q) use ($orgName) {
                    $q->whereRaw('LOWER(TRIM(organisasi)) = ?', [strtolower($orgName)]);
                })
                ->orWhere('id', $owner->id)
                ->pluck('id');

            if ($evalIds->isEmpty()) {
                return response()->json(['success' => true, 'data' => []]);
            }

            $evalIds = MstEval::whereIn('user_id', $userIds)
                ->where('eval_id', '!=', $evalId)
                ->pluck('eval_id');

            if ($evalIds->isEmpty()) {
                return response()->json(['success' => true, 'data' => []]);
            }

            $evidences = MstEvidence::whereIn('eval_id', $evalIds)
                ->with(['evaluation' => function ($q) {
                    $q->select('eval_id', 'tahun');
                }])
                ->orderByDesc('created_at')
                ->limit(200)
                ->get([
                    'id',
                    'eval_id',
                    'judul_dokumen',
                    'no_dokumen',
                    'grup',
                    'tipe',
                    'tahun_terbit',
                    'tahun_kadaluarsa',
                    'pemilik_dokumen',
                    'pengesahan',
                    'klasifikasi',
                    'summary',
                    'notes',
                    'created_at'
                ]);

            $mapped = $evidences->map(function ($evidence) {
                $assessmentYear = optional($evidence->evaluation)->year
                    ?? optional($evidence->evaluation)->assessment_year
                    ?? optional($evidence->evaluation)->tahun
                    ?? null;

                return [
                    'id' => $evidence->id,
                    'eval_id' => $evidence->eval_id,
                    'judul_dokumen' => $evidence->judul_dokumen,
                    'no_dokumen' => $evidence->no_dokumen,
                    'grup' => $evidence->grup,
                    'tipe' => $evidence->tipe,
                    'tahun_terbit' => $evidence->tahun_terbit,
                    'tahun_kadaluarsa' => $evidence->tahun_kadaluarsa,
                    'pemilik_dokumen' => $evidence->pemilik_dokumen,
                    'pengesahan' => $evidence->pengesahan,
                    'klasifikasi' => $evidence->klasifikasi,
                    'summary' => $evidence->summary,
                    'notes' => $evidence->notes,
                    'created_at' => $evidence->created_at,
                    'assessment_year' => $assessmentYear,
                ];
            });

            return response()->json(['success' => true, 'data' => $mapped]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch previous evidences', [
                'eval_id' => $evalId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load previous evidences'
            ], 500);
        }
    }

    /**
     * Store evidence for an assessment
     */
    public function storeEvidence(Request $request, $evalId)
    {
        try {
            $evaluation = $this->evaluationService->getEvaluationById($evalId);
            
            if (!$evaluation || (string)$evaluation->user_id !== (string)Auth::id()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'judul_dokumen' => 'required|string|max:255',
                'no_dokumen' => 'nullable|string|max:100',
                'tahun_terbit' => 'nullable|integer',
                'tahun_kadaluarsa' => 'nullable|integer',
                'tipe' => 'nullable|string|max:100',
                'pengesahan' => 'nullable|string|max:255',
                'pemilik_dokumen' => 'nullable|string|max:255',
                'klasifikasi' => 'nullable|string|max:100',
                'grup' => 'nullable|string|max:100',
                'notes' => 'nullable|string',
                'summary' => 'nullable|string',
            ]);

            $evidence = MstEvidence::create([
                'eval_id' => $evalId,
                ...$validated
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Evidence added successfully',
                'data' => $evidence
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to store evidence", [
                'eval_id' => $evalId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to store evidence: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update evidence
     */
    public function updateEvidence(Request $request, $evidenceId)
    {
        try {
            $evidence = MstEvidence::findOrFail($evidenceId);
            $evaluation = $this->evaluationService->getEvaluationById($evidence->eval_id);

            if (!$evaluation || (string)$evaluation->user_id !== (string)Auth::id()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'judul_dokumen' => 'required|string|max:255',
                'no_dokumen' => 'nullable|string|max:100',
                'tahun_terbit' => 'nullable|integer',
                'tahun_kadaluarsa' => 'nullable|integer',
                'tipe' => 'nullable|string|max:100',
                'pengesahan' => 'nullable|string|max:255',
                'pemilik_dokumen' => 'nullable|string|max:255',
                'klasifikasi' => 'nullable|string|max:100',
                'grup' => 'nullable|string|max:100',
                'notes' => 'nullable|string',
                'summary' => 'nullable|string',
            ]);

            $evidence->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Evidence updated successfully',
                'data' => $evidence
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to update evidence", [
                'evidence_id' => $evidenceId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update evidence: ' . $e->getMessage()
            ], 500);
        }
    }


}
