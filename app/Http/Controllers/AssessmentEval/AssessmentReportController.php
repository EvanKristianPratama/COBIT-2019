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

    public function summary($evalId, $objectiveId = null)
    {
        // 1. Eval ID (and object for context)
        $evaluation = MstEval::findOrFail($evalId);

        // 2 & 3. All Objectives & Practices
        $objectivesQuery = MstObjective::with([
            'practices.activities.evaluations' => function ($query) use ($evalId) {
                $query->where('eval_id', $evalId); // <--- Filter Kuncinya
            },
        ])->orderBy('objective_id');

        if ($objectiveId) {
            $objectivesQuery->where('objective_id', $objectiveId);
        }

        $objectives = $objectivesQuery->get();

        // 4. Achieved Level per GAMO
        $scoresQuery = TrsObjectiveScore::where('eval_id', $evalId);

        if ($objectiveId) {
            $scoresQuery->where('objective_id', $objectiveId);
        }

        $objectiveScores = $scoresQuery->pluck('level', 'objective_id')->toArray();

        // 5. Max Capability Level (Hardcoded)
        $maxLevels = [
            'EDM01' => 4, 'EDM02' => 5, 'EDM03' => 4, 'EDM04' => 4, 'EDM05' => 4,
            'APO01' => 5, 'APO02' => 4, 'APO03' => 5, 'APO04' => 4, 'APO05' => 5,
            'APO06' => 5, 'APO07' => 4, 'APO08' => 5, 'APO09' => 4, 'APO10' => 5,
            'APO11' => 5, 'APO12' => 5, 'APO13' => 5, 'APO14' => 5,
            'BAI01' => 5, 'BAI02' => 4, 'BAI03' => 4, 'BAI04' => 5, 'BAI05' => 5,
            'BAI06' => 4, 'BAI07' => 5, 'BAI08' => 5, 'BAI09' => 5, 'BAI10' => 5, 'BAI11' => 4,
            'DSS01' => 5, 'DSS02' => 5, 'DSS03' => 5, 'DSS04' => 5, 'DSS05' => 4, 'DSS06' => 5,
            'MEA01' => 5, 'MEA02' => 5, 'MEA03' => 5, 'MEA04' => 4,
        ];

        if ($objectiveId && isset($maxLevels[$objectiveId])) {
            $maxLevels = [$objectiveId => $maxLevels[$objectiveId]];
        } elseif ($objectiveId) {
            $maxLevels = [];
        }

        // 6. Fetch Evidence Types for classification
        $evidenceTypes = \App\Models\MstEvidence::where('eval_id', $evalId)
            ->get()
            ->mapWithKeys(fn ($item) => [strtolower(trim($item->judul_dokumen)) => $item->tipe])
            ->toArray();

        // Suntik data score dan max level ke dalam masing-masing object
        $objectives->map(function ($obj) use ($objectiveScores, $maxLevels, $evidenceTypes) {
            $obj->current_score = $objectiveScores[$obj->objective_id] ?? 0;
            $obj->max_level = $maxLevels[$obj->objective_id] ?? 0;

            $filledEvidenceCount = 0;

            foreach ($obj->practices as $practice) {
                // Variabel untuk menyimpan history evidence di practice ini (agar tidak duplikat)
                $daftarEvidenceUnikPractice = [];

                foreach ($practice->activities as $activity) {
                    // Ambil item pertama dari relasi hasMany (karena 1 activity hanya punya 1 nilai per eval_id ini)
                    $evalData = $activity->evaluations->first();

                    // Logic Deduplikasi Evidence dalam satu Practice
                    if ($evalData && !empty($evalData->evidence)) {
                        $barisEvidenceMentah = explode("\n", $evalData->evidence);
                        $policyList = [];
                        $executionList = [];

                        foreach ($barisEvidenceMentah as $namaDokumen) {
                            $namaDokumenNormalisasi = strtolower(trim($namaDokumen));
                            if ($namaDokumenNormalisasi === '') {
                                continue;
                            }

                            if (!in_array($namaDokumenNormalisasi, $daftarEvidenceUnikPractice)) {
                                $daftarEvidenceUnikPractice[] = $namaDokumenNormalisasi;

                                // Lookup Tipe
                                $tipe = $evidenceTypes[$namaDokumenNormalisasi] ?? null;

                                // Filter Logic: Politik vs Pelaksanaan
                                if ($tipe && stripos($tipe, 'Dokumen Kebijakan') !== false) {
                                    $policyList[] = trim($namaDokumen);
                                } else {
                                    $executionList[] = trim($namaDokumen);
                                }
                            }
                        }

                        // Inject hasil filter langsung ke objek assessment siap pakai di View
                        $evalData->policy_list = $policyList;
                        $evalData->execution_list = $executionList;
                    }

                    // Suntikkan sebagai 'assessment' agar View & JSON langsung dapat datanya
                    $activity->assessment = $evalData;

                    // Hitung jika evidence tidak kosong
                    if ($evalData && ! empty($evalData->evidence)) {
                        $filledEvidenceCount++;
                    }

                    // Hapus relasi asli agar JSON bersih
                    $activity->unsetRelation('evaluations');
                }

                // Filter logic dipindah ke Controller: Hanya simpan activity yang punya evidence
                $filteredActivities = $practice->activities->filter(function ($act) {
                    return ! empty($act->assessment) && ! empty($act->assessment->evidence);
                })->values();

                $practice->setRelation('activities', $filteredActivities);

                // Set count properties for explicit access in View
                $practice->filled_evidence_count = $filteredActivities->count();
            }

            $obj->filled_evidence_count = $filledEvidenceCount;

            return $obj;
        });

        // return response()->json([
        //     'evaluation' => $evaluation,
        //     'objectives' => $objectives,
        // ]);

        return view('assessment-eval.report-summary', compact('evaluation', 'objectives'));
    }
}
