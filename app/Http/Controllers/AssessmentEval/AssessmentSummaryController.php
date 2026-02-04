<?php

namespace App\Http\Controllers\AssessmentEval;

use App\Http\Controllers\Controller;
use App\Models\MstEval;
use App\Models\MstObjective;
use App\Models\TrsObjectiveScore;
use App\Models\TrsSummaryActivity;
use App\Models\TrsSummaryReport;
use App\Services\EvaluationService;
use Artisan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class AssessmentSummaryController extends Controller
{
    public function summary($evalId, $objectiveId = null)
    {
        $data = $this->getSummary($evalId, $objectiveId);

        // return response()->json($data);
        return view('assessment-eval.report-summary', $data);
    }

    public function summaryPdf($evalId, $objectiveId = null)
    {
        $data = $this->getSummary($evalId, $objectiveId);

        $pdf = PDF::loadView('assessment-eval.report-summary-pdf', $data);
        $pdf->setPaper('a4', 'landscape');

        $filename = 'Summary-Report-'.$evalId.($objectiveId ? '-'.$objectiveId : '').'.pdf';

        return $pdf->stream($filename);
    }

    public function summaryDetailPdf($evalId, $objectiveId = null)
    {
        $data = $this->getSummary($evalId, $objectiveId);

        $pdf = PDF::loadView('assessment-eval.report-summary-detail-pdf', $data);
        $pdf->setPaper('a4', 'landscape');

        $filename = 'Summary-Detail-Report-'.$evalId.($objectiveId ? '-'.$objectiveId : '').'.pdf';

        return $pdf->stream($filename);
    }

    private function getSummary($evalId, $objectiveId = null)
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

        // Pre-fetch Saved Notes (kesimpulan & rekomendasi & roadmap_rekomendasi)
        $savedNotesQuery = TrsSummaryReport::where('eval_id', $evalId)
            ->when($objectiveId, fn ($q) => $q->where('objective_id', $objectiveId))
            ->get();

        $savedNotes = [];
        foreach ($savedNotesQuery as $note) {
            $savedNotes[$note->objective_id] = [
                'kesimpulan' => $note->kesimpulan ?? '',
                'rekomendasi' => $note->rekomendasi ?? '',
                'roadmap_rekomendasi' => $note->roadmap_rekomendasi ?? null,
            ];
        }

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

        // 6. Pre-fetch Mapped Evidence from trs_summaryactivity (grouped by activityeval_id)
        $mappedEvidence = TrsSummaryActivity::with('evidence')
            ->whereHas('activityEval', fn ($q) => $q->where('eval_id', $evalId))
            ->get()
            ->groupBy('activityeval_id');

        // 7. Fetch Target Capabilities
        $evaluationService = app(EvaluationService::class);
        $targetCapabilityMap = $evaluationService->fetchTargetCapabilities($evaluation);

        // 8. Rating Map for calculation
        $ratingMap = ['N' => 0.0, 'P' => 1.0 / 3.0, 'L' => 2.0 / 3.0, 'F' => 1.0];

        // Suntik data score dan max level ke dalam masing-masing object
        $objectives->map(function ($obj) use ($objectiveScores, $maxLevels, $mappedEvidence, $ratingMap, $targetCapabilityMap, $savedNotes, $evalId) {
            $currentLevel = $objectiveScores[$obj->objective_id] ?? 0;
            $obj->current_score = $currentLevel;
            $obj->max_level = $maxLevels[$obj->objective_id] ?? 0;
            $obj->saved_note = $savedNotes[$obj->objective_id] ?? ['kesimpulan' => '', 'rekomendasi' => ''];

            // Calculate Rating String (e.g., 4F)
            $obj->rating_string = $this->calculateRatingString($obj, $currentLevel, $ratingMap, $evalId);

            // Inject Target
            $obj->target_level = $targetCapabilityMap[$obj->objective_id] ?? 0;

            $filledEvidenceCount = 0;

            // Variabel untuk menyimpan evidence unik per GAMO/objective (deduplicated)
            $daftarEvidenceUnikGamo = [];
            $objectivePolicyList = [];
            $objectiveExecutionList = [];

            foreach ($obj->practices as $practice) {
                // Initialize practice-level evidence lists
                $practicePolicyList = [];
                $practiceExecutionList = [];
                
                foreach ($practice->activities as $activity) {
                    // Ambil item pertama dari relasi hasMany (karena 1 activity hanya punya 1 nilai per eval_id ini)
                    $evalData = $activity->evaluations->first();

                    // Logic: Fetch evidence from mapping table (trs_summaryactivity)
                    if ($evalData) {
                        // Get mapped evidence for this activity evaluation
                        $activityMappedEvidence = $mappedEvidence[$evalData->id] ?? collect();

                        foreach ($activityMappedEvidence as $mappedItem) {
                            // Get evidence name (from relation or miss_evidence fallback)
                            $evidenceName = $mappedItem->evidence_name;
                            if (empty($evidenceName)) {
                                continue;
                            }

                            // Get evidence type (from relation or 'Execution' default)
                            $tipe = $mappedItem->evidence_type;

                            // Add to practice-level lists (allow duplicates at practice level)
                            if ($tipe && (stripos($tipe, 'Design') !== false || stripos($tipe, 'Procedure') !== false)) {
                                $practicePolicyList[] = $evidenceName;
                            } else {
                                $practiceExecutionList[] = $evidenceName;
                            }

                            // Deduplicate within GAMO/objective (not practice) for aggregated view
                            $normalizedName = strtolower(trim($evidenceName));
                            if (in_array($normalizedName, $daftarEvidenceUnikGamo)) {
                                continue;
                            }
                            $daftarEvidenceUnikGamo[] = $normalizedName;

                            // Filter Logic: Kebijakan (Design/Procedure) vs Pelaksanaan (Execution/Report)
                            if ($tipe && (stripos($tipe, 'Design') !== false || stripos($tipe, 'Procedure') !== false)) {
                                $objectivePolicyList[] = $evidenceName;
                            } else {
                                $objectiveExecutionList[] = $evidenceName;
                            }
                        }
                    }

                    // Hapus relasi asli agar JSON bersih
                    $activity->unsetRelation('evaluations');
                }
                
                // Inject practice-level evidence (deduplicated within practice)
                $practice->policy_list = array_unique($practicePolicyList);
                $practice->execution_list = array_unique($practiceExecutionList);
                $practice->has_evidence = !empty($practicePolicyList) || !empty($practiceExecutionList);
            }

            // Inject evidence lists at OBJECTIVE/GAMO level
            $obj->policy_list = $objectivePolicyList;
            $obj->execution_list = $objectiveExecutionList;

            // Check if objective has any evidence
            $hasEvidence = ! empty($objectivePolicyList) || ! empty($objectiveExecutionList);
            $obj->has_evidence = $hasEvidence;

            if ($hasEvidence) {
                $filledEvidenceCount = 1; // Count as 1 if GAMO has evidence
            }

            $obj->filled_evidence_count = $filledEvidenceCount;

            return $obj;
        });

        return compact('evaluation', 'objectives', 'targetCapabilityMap');
    }

    public function saveNote(Request $request, $evalId)
    {
        $request->validate([
            'objective_id' => 'required|string',
            'kesimpulan' => 'nullable|string',
            'rekomendasi' => 'nullable|string',
            'roadmap_rekomendasi' => 'nullable|string', // Comes as JSON string from frontend
        ]);

        $objectiveId = $request->input('objective_id');
        $kesimpulan = $request->input('kesimpulan');
        $rekomendasi = $request->input('rekomendasi');
        
        // Decode roadmap_rekomendasi JSON string to array
        $roadmapRekomendasi = null;
        $roadmapInput = $request->input('roadmap_rekomendasi');
        if ($roadmapInput) {
            $decoded = json_decode($roadmapInput, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $roadmapRekomendasi = $decoded;
            }
        }

        // 1. Save or Update Summary Report
        $summaryReport = TrsSummaryReport::updateOrCreate(
            ['eval_id' => $evalId, 'objective_id' => $objectiveId],
            [
                'kesimpulan' => $kesimpulan,
                'rekomendasi' => $rekomendasi,
                'roadmap_rekomendasi' => $roadmapRekomendasi,
            ]
        );

        return redirect()->back()->with('success', 'Catatan berhasil disimpan.');
    }

    public function getNote(Request $request, $evalId)
    {
        // Define COBIT domain order: EDM → APO → BAI → DSS → MEA
        $reports = TrsSummaryReport::where('eval_id', $evalId)
            ->orderByRaw("
                CASE 
                    WHEN objective_id LIKE 'EDM%' THEN 1
                    WHEN objective_id LIKE 'APO%' THEN 2
                    WHEN objective_id LIKE 'BAI%' THEN 3
                    WHEN objective_id LIKE 'DSS%' THEN 4
                    WHEN objective_id LIKE 'MEA%' THEN 5
                    ELSE 6
                END,
                objective_id
            ")
            ->get();
            
        $objectives = MstObjective::pluck('objective', 'objective_id');
        $evaluation = MstEval::findOrFail($evalId);

        return view('assessment-eval.summary', compact('reports', 'objectives', 'evalId', 'evaluation'));
    }

    private function calculateRatingString($obj, $finalLevel, $ratingMap, $evalId)
    {
        if ($finalLevel == 0) {
            return '0N';
        }

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
        if (empty($acts)) {
            return $finalLevel.'F';
        }

        $totalScore = 0;
        foreach ($acts as $a) {
            // Query database directly to get evaluation data
            $activityEval = \App\Models\TrsActivityeval::where('eval_id', $evalId)
                ->where('activity_id', $a->activity_id)
                ->first();

            $r = $activityEval ? $activityEval->level_achieved : 'N';
            $totalScore += $ratingMap[$r] ?? 0;
        }
        $avgScore = $totalScore / count($acts);

        $letter = 'N';
        if ($avgScore > 0.85) {
            $letter = 'F';
        } elseif ($avgScore > 0.50) {
            $letter = 'L';
        } elseif ($avgScore > 0.15) {
            $letter = 'P';
        }

        return $finalLevel.$letter;
    }

    public function cleanupEvidence($secret_key)  // ❌ Hapus parameter $evalId di sini
    {
        // Validasi secret key
        if ($secret_key !== 'rahasia-12345-XyZ') {
            abort(403, 'Unauthorized');
        }

        // Cek parameter dari query string
        $dryRun = request()->has('dry-run');
        $evalId = request()->get('eval');  // ✅ Ambil dari query string, bukan parameter route

        // Prepare options
        $options = [];
        if ($dryRun) {
            $options['--dry-run'] = true;
        }
        if ($evalId) {
            $options['--eval'] = $evalId;
        }

        // Jalankan command
        Artisan::call('evidence:cleanup', $options);

        // Tampilkan hasil
        $output = Artisan::output();

        // Return dengan format yang mudah dibaca
        return response("<pre style='background:#1e1e1e;color:#00ff00;padding:20px;font-family:monospace;'>$output</pre>");
    }
}
