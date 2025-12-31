<?php

namespace App\Http\Controllers\AssessmentEval;

class AssessmentSummaryController extends Controller
{
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

        // Suntik data score dan max level ke dalam masing-masing object
        $objectives->map(function ($obj) use ($objectiveScores, $maxLevels) {
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
                        $listEvidenceFinalActivity = [];

                        foreach ($barisEvidenceMentah as $namaDokumen) {
                            $namaDokumenNormalisasi = strtolower(trim($namaDokumen));
                            if ($namaDokumenNormalisasi === '') {
                                continue;
                            }

                            if (! in_array($namaDokumenNormalisasi, $daftarEvidenceUnikPractice)) {
                                $daftarEvidenceUnikPractice[] = $namaDokumenNormalisasi;
                                $listEvidenceFinalActivity[] = trim($namaDokumen);
                            }
                        }

                        // Update evidence dengan list yang unik (bisa kosong jika seluruhnya sudah ada sebelumnya)
                        $evalData->evidence = implode("\n", $listEvidenceFinalActivity);
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
