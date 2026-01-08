<?php

namespace App\Http\Controllers\AssessmentEval;

use App\Http\Controllers\Controller;
use App\Models\MstEval;
use App\Models\MstObjective;
use App\Models\TrsObjectiveScore;
use App\Models\MstEvidence;
use Barryvdh\DomPDF\Facade\Pdf;

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
        $evidenceTypes = MstEvidence::where('eval_id', $evalId)
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

        return compact('evaluation', 'objectives');
    }
}
