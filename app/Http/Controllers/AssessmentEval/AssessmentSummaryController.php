<?php

namespace App\Http\Controllers\AssessmentEval;

use App\Http\Controllers\Controller;
use App\Models\MstEval;
use App\Models\MstEvidence;
use App\Models\MstObjective;
use App\Models\TrsActivityeval;
use App\Models\TrsObjectiveScore;
use App\Models\TrsSummaryReport;
use App\Services\EvaluationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // Pre-fetch Saved Notes
        $savedNotes = TrsSummaryReport::where('eval_id', $evalId)
            ->when($objectiveId, fn ($q) => $q->where('objective_id', $objectiveId))
            ->pluck('notes', 'objective_id')
            ->toArray();

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

        // 6. Fetch Evidence Types for classification AND ID map
        $allEvidence = MstEvidence::where('eval_id', $evalId)->get();
        // Map: lowercase(filename) -> type
        $evidenceTypes = $allEvidence->mapWithKeys(fn ($item) => [strtolower(trim($item->judul_dokumen)) => $item->tipe])->toArray();

        // 7. Fetch Target Capabilities
        $evaluationService = app(EvaluationService::class);
        $targetCapabilityMap = $evaluationService->fetchTargetCapabilities($evaluation);

        // 8. Rating Map for calculation
        $ratingMap = ['N' => 0.0, 'P' => 1.0 / 3.0, 'L' => 2.0 / 3.0, 'F' => 1.0];

        // Suntik data score dan max level ke dalam masing-masing object
        $objectives->map(function ($obj) use ($objectiveScores, $maxLevels, $evidenceTypes, $ratingMap, $targetCapabilityMap, $savedNotes) {
            $currentLevel = $objectiveScores[$obj->objective_id] ?? 0;
            $obj->current_score = $currentLevel;
            $obj->max_level = $maxLevels[$obj->objective_id] ?? 0;
            $obj->saved_note = $savedNotes[$obj->objective_id] ?? '';

            // Calculate Rating String (e.g., 4F)
            $obj->rating_string = $this->calculateRatingString($obj, $currentLevel, $ratingMap);

            // Inject Target
            $obj->target_level = $targetCapabilityMap[$obj->objective_id] ?? 0;

            $filledEvidenceCount = 0;

            foreach ($obj->practices as $practice) {
                // Variabel untuk menyimpan history evidence di practice ini (agar tidak duplikat)
                $daftarEvidenceUnikPractice = [];

                foreach ($practice->activities as $activity) {
                    // Ambil item pertama dari relasi hasMany (karena 1 activity hanya punya 1 nilai per eval_id ini)
                    $evalData = $activity->evaluations->first();

                    // Logic Deduplikasi Evidence dalam satu Practice
                    if ($evalData && ! empty($evalData->evidence)) {
                        $barisEvidenceMentah = explode("\n", $evalData->evidence);
                        $policyList = [];
                        $executionList = [];

                        foreach ($barisEvidenceMentah as $namaDokumen) {
                            $namaDokumenNormalisasi = strtolower(trim($namaDokumen));
                            if ($namaDokumenNormalisasi === '') {
                                continue;
                            }

                            if (! in_array($namaDokumenNormalisasi, $daftarEvidenceUnikPractice)) {
                                $daftarEvidenceUnikPractice[] = $namaDokumenNormalisasi;

                                // Lookup Tipe
                                $tipe = $evidenceTypes[$namaDokumenNormalisasi] ?? null;

                                // Filter Logic: Kebijakan vs Pelaksanaan
                                // Updated to use 'Design' based on latest requirements
                                if ($tipe && stripos($tipe, 'Design') !== false) {
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

                // Filter logic dipindah ke Controller: Hanya simpan activity yang punya evidence Unik (Normalized/Deduplicated)
                $filteredActivities = $practice->activities->filter(function ($act) {
                    if (empty($act->assessment)) {
                        return false;
                    }
                    // Cek apakah list hasil deduplikasi ada isinya
                    $hasPolicy = ! empty($act->assessment->policy_list) && count($act->assessment->policy_list) > 0;
                    $hasExecution = ! empty($act->assessment->execution_list) && count($act->assessment->execution_list) > 0;

                    return $hasPolicy || $hasExecution;
                })->values();

                $practice->setRelation('activities', $filteredActivities);

                // Set count properties for explicit access in View
                $practice->filled_evidence_count = $filteredActivities->count();
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
            'notes' => 'nullable|string',
        ]);

        $objectiveId = $request->input('objective_id');
        $notes = $request->input('notes');

        // 1. Save or Update Summary Report
        $summaryReport = TrsSummaryReport::updateOrCreate(
            ['eval_id' => $evalId, 'objective_id' => $objectiveId],
            ['notes' => $notes]
        );

        return redirect()->back()->with('success', 'Catatan berhasil disimpan.');
    }

    private function calculateRatingString($obj, $finalLevel, $ratingMap)
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
            $r = $a->assessment->level_achieved ?? 'N';
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
}
