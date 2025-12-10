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
    public function listAssessments()
    {
        try {
            $user = Auth::user();
            $org = $user->organisasi ?? null;

            if ($org) {
                // load all evaluations created by users in the same organization
                // NOTE: do NOT eager-load full activityEvaluations for other users — expose only aggregated counts
                $evaluations = MstEval::with(['user'])
                    ->whereHas('user', function ($q) use ($org) {
                        $q->where('organisasi', $org);
                    })->get();

                // attach aggregated achievement counts per evaluation (F/L/P) without loading full rows
                $evalIds = $evaluations->pluck('eval_id')->filter()->unique()->values()->all();
                if (!empty($evalIds)) {
                    $rows = \App\Models\TrsActivityeval::whereIn('eval_id', $evalIds)
                        ->select('eval_id', 'level_achieved', DB::raw('count(*) as cnt'))
                        ->groupBy('eval_id', 'level_achieved')
                        ->get();

                    $countsMap = [];
                    foreach ($rows as $r) {
                        $eid = $r->eval_id;
                        $lvl = $r->level_achieved;
                        $countsMap[$eid][$lvl] = (int) $r->cnt;
                    }

                    foreach ($evaluations as $ev) {
                        $ev->achievement_counts = $countsMap[$ev->eval_id] ?? [];
                    }
                }
            } else {
                // fallback to current user's evaluations
                $evaluations = $this->evaluationService->getUserEvaluations();
            }

            $evalIds = $evaluations->pluck('eval_id')->filter()->unique()->values()->all();
            $selectedDomainsMap = [];
            $lastActivityDates = [];
            if (!empty($evalIds)) {
                $rows = TrsEvalDetail::whereIn('eval_id', $evalIds)->get();
                foreach ($rows as $row) {
                    $selectedDomainsMap[$row->eval_id][] = $row->domain_id;
                }

                // Fetch last activity update time
                $lastActivityDates = \App\Models\TrsActivityeval::whereIn('eval_id', $evalIds)
                    ->select('eval_id', DB::raw('MAX(updated_at) as last_activity_at'))
                    ->groupBy('eval_id')
                    ->pluck('last_activity_at', 'eval_id');
            }

            // Calculate filled GAMO counts (distinct objectives with at least one rated activity)
            $filledGamoCounts = [];
            if (!empty($evalIds)) {
                $filledGamoCounts = DB::table('trs_activityeval')
                    ->join('mst_activities', 'trs_activityeval.activity_id', '=', 'mst_activities.activity_id')
                    ->join('mst_practice', 'mst_activities.practice_id', '=', 'mst_practice.practice_id')
                    ->whereIn('trs_activityeval.eval_id', $evalIds)
                    ->select('trs_activityeval.eval_id', DB::raw('count(distinct mst_practice.objective_id) as filled_count'))
                    ->groupBy('trs_activityeval.eval_id')
                    ->pluck('filled_count', 'eval_id')
                    ->toArray();
            }

            // Calculate activity counts per domain to determine denominator for progress
            $domainActivityCounts = DB::table('mst_activities')
                ->join('mst_practice', 'mst_activities.practice_id', '=', 'mst_practice.practice_id')
                ->select('mst_practice.objective_id', DB::raw('count(*) as cnt'))
                ->groupBy('mst_practice.objective_id')
                ->pluck('cnt', 'objective_id')
                ->toArray();
            
            $totalSystemActivities = array_sum($domainActivityCounts);

            $evaluations->transform(function ($evaluation) use ($selectedDomainsMap, $domainActivityCounts, $totalSystemActivities, $lastActivityDates, $filledGamoCounts) {
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
                $evaluation->total_ratable_activities = $totalRatable > 0 ? $totalRatable : 1; // avoid division by zero

                // Use activity date if available, otherwise fallback to created_at
                $evaluation->last_saved_at = $lastActivityDates[$evaluation->eval_id] ?? $evaluation->created_at;

                return $evaluation;
            });

            return view('assessment-eval.list', compact('evaluations'));
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

                if (!empty($selected)) {
                    DB::transaction(function () use ($evaluation, $selected) {
                        // remove any existing mappings for this evaluation to avoid duplicates
                        TrsEvalDetail::where('eval_id', $evaluation->eval_id)->delete();

                        $inserts = [];
                        foreach ($selected as $domainId) {
                            $domainId = trim((string)$domainId);
                            if ($domainId !== '') {
                                $inserts[] = [
                                    'eval_id' => $evaluation->eval_id,
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

            return redirect()->route('assessment-eval.show', ['evalId' => $evaluation->eval_id]);
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
            $selectedDomains = TrsEvalDetail::where('eval_id', $evalId)->pluck('domain_id')->unique()->toArray();

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

            // Pass isOwner to the view. Non-owners may view the full content in read-only mode.
            return view('assessment-eval.show', compact('objectives', 'evalId', 'evaluation', 'isOwner', 'evidences', 'targetCapabilityMap'));
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
            $evaluation = $this->evaluationService->getEvaluationById($evalId);
            
            if (!$evaluation || (string)$evaluation->user_id !== (string)Auth::id()) {
                Log::warning('Delete access denied', [
                    'auth_id' => Auth::id(),
                    'eval_id' => $evalId,
                    'evaluation_user_id' => $evaluation ? $evaluation->user_id : null
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Assessment not found or access denied'
                ], 404);
            }
            
            $this->evaluationService->deleteEvaluation($evalId);
            
            return response()->json([
                'success' => true,
                'message' => 'Assessment deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete assessment: ' . $e->getMessage()
            ], 500);
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

            $selectedDomains = TrsEvalDetail::where('eval_id', $evalId)->pluck('domain_id')->unique()->toArray();

            if (!empty($selectedDomains)) {
                $objectives = MstObjective::with(['practices.activities'])
                    ->where(function ($q) use ($selectedDomains) {
                        foreach ($selectedDomains as $domain) {
                            $domain = trim((string)$domain);
                            if ($domain !== '') {
                                $q->orWhere('objective_id', 'like', $domain . '%');
                            }
                        }
                    })->get();
            } else {
                $objectives = MstObjective::with(['practices.activities'])->get();
            }

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

            return view('assessment-eval.report', compact('objectives', 'evalId', 'evaluation', 'isOwner', 'targetCapabilityMap'));

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to load report: ' . $e->getMessage()]);
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
