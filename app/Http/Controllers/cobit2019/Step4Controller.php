<?php

namespace App\Http\Controllers\cobit2019;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\DfStep2;
use App\Models\DfStep3;
use App\Models\TrsStep2;
use App\Models\TrsStep3;
use App\Models\TrsStep4;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Step4Controller extends Controller
{
    private const DEFAULT_WEIGHTS_STEP2 = [1, 1, 1, 1];

    private const DEFAULT_WEIGHTS_STEP3 = [1, 1, 1, 1, 1, 1];

    /**
     * Tampilkan halaman Step 4, dengan data dari trs_step2 dan trs_step3
     */
    public function index(Request $request)
    {
        $assessmentId = session('assessment_id');
        if (! $assessmentId) {
            return redirect()->back()->with('error', 'Assessment ID tidak ditemukan.');
        }

        $userId = Auth::id();

        // Get weights from database
        $weights = $this->getWeights($assessmentId, $userId);

        // Get step data from trs_step2 and trs_step3 tables
        $stepData = $this->getStepData($assessmentId, $userId);

        $step4Db = $this->getStep4Db($assessmentId, $userId);

        return view('design_factor.step4.step4sumaryblade', [
            'step2' => [
                'weights' => $weights['step2'],
                'data' => $stepData['step2'],
            ],
            'step3' => [
                'weights' => $weights['step3'],
                'data' => $stepData['step3'],
            ],
            'AllRelImps' => $stepData['allRelImps'],
            'combinedTotals' => $stepData['combinedTotals'],
            'refinedScopes' => $stepData['refinedScopes'],
            'initialScopes' => $stepData['initialScopes'],
            'step4Adjust' => $step4Db['adjustment'],
            'step4ReasonAdj' => $step4Db['reason_adjust'],
            'step4ReasonTgt' => $step4Db['reason_target'],
            'step4Selected' => $step4Db['selected'],
            'step4Agreed' => $step4Db['agreed_level'],
            'assessment' => $this->getAssessment($assessmentId),
        ]);
    }

    /**
     * Simpan data Step 4 (adjustment + alasan)
     */
    public function store(Request $request)
    {
        $assessmentId = session('assessment_id');
        $userId = Auth::id();

        $request->validate([
            'adjustment' => 'sometimes|array', 'adjustment.*' => 'nullable|integer|between:-100,100',
            'reason_adjust' => 'sometimes|array', 'reason_adjust.*' => 'nullable|string|max:5000',
            'reason_target' => 'sometimes|array', 'reason_target.*' => 'nullable|string|max:5000',
            'agreed_level' => 'sometimes|array', 'agreed_level.*' => 'nullable|integer|between:0,5',
            'suggested_level' => 'sometimes|array', 'suggested_level.*' => 'nullable|integer|between:0,5',
            'concluded_priority' => 'sometimes|array', 'concluded_priority.*' => 'nullable|numeric',
            'selected' => 'sometimes|array', 'selected.*' => 'nullable|in:0,1',
        ]);
        DB::transaction(function () use ($request, $assessmentId, $userId): void {
            $this->saveStep4($request, $assessmentId, $userId);
        });

        return redirect()->route('step4.index')
            ->with('success', 'Hasil Step 4 berhasil disimpan ke database.');
    }

    /**
     * Get weights from database with fallback to defaults
     */
    private function getWeights(int $assessmentId, int $userId): array
    {
        $dfStep2 = DfStep2::where('assessment_id', $assessmentId)
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->first();

        $dfStep3 = DfStep3::where('assessment_id', $assessmentId)
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->first();

        return [
            'step2' => is_array($dfStep2->weights ?? null)
                ? $dfStep2->weights
                : self::DEFAULT_WEIGHTS_STEP2,
            'step3' => is_array($dfStep3->weights ?? null)
                ? $dfStep3->weights
                : self::DEFAULT_WEIGHTS_STEP3,
        ];
    }

    /**
     * Get step data from trs_step2 and trs_step3 tables
     * Returns consistent data structure for frontend
     */
    private function getStepData(int $assessmentId, int $userId): array
    {
        // Get all records from trs_step2 dan trs_step3
        $step2Records = TrsStep2::where('assessment_id', $assessmentId)
            ->where('user_id', $userId)
            ->orderBy('objective_code')
            ->get()
            ->keyBy('objective_code');

        $step3Records = TrsStep3::where('assessment_id', $assessmentId)
            ->where('user_id', $userId)
            ->orderBy('objective_code')
            ->get()
            ->keyBy('objective_code');

        $allRelImps = [];
        $combinedTotals = [];
        $refinedScopes = [];
        $initialScopes = [];
        $step2Data = [];
        $step3Data = [];

        // Build data for all 40 objectives
        for ($code = 1; $code <= 40; $code++) {
            $s2 = $step2Records->get($code);
            $s3 = $step3Records->get($code);

            // AllRelImps: DF1-4 from step2, DF5-10 from step3
            $allRelImps[$code] = [
                (float) ($s2->rel_imp_df1 ?? 0),
                (float) ($s2->rel_imp_df2 ?? 0),
                (float) ($s2->rel_imp_df3 ?? 0),
                (float) ($s2->rel_imp_df4 ?? 0),
                (float) ($s3->rel_imp_df5 ?? 0),
                (float) ($s3->rel_imp_df6 ?? 0),
                (float) ($s3->rel_imp_df7 ?? 0),
                (float) ($s3->rel_imp_df8 ?? 0),
                (float) ($s3->rel_imp_df9 ?? 0),
                (float) ($s3->rel_imp_df10 ?? 0),
            ];

            // Totals and scores
            $combinedTotals[$code] = (float) ($s3->total_combined ?? 0);
            $refinedScopes[$code] = (float) ($s3->refined_scope_score ?? 0);
            $initialScopes[$code] = (float) ($s2->initial_scope_score ?? 0);

            // Raw data for detailed access if needed
            $step2Data[$code] = $s2 ? $s2->toArray() : null;
            $step3Data[$code] = $s3 ? $s3->toArray() : null;
        }

        return [
            'step2' => $step2Data,
            'step3' => $step3Data,
            'allRelImps' => $allRelImps,
            'combinedTotals' => $combinedTotals,
            'refinedScopes' => $refinedScopes,
            'initialScopes' => $initialScopes,
        ];
    }

    /**
     * Load Step 4 data from database
     */
    private function getStep4Db(int $assessmentId, int $userId): array
    {
        $records = TrsStep4::where('assessment_id', $assessmentId)
            ->where('user_id', $userId)
            ->get()
            ->keyBy('objective_code');

        $data = [
            'adjustment' => [],
            'reason_adjust' => [],
            'reason_target' => [],
            'selected' => [],
            'agreed_level' => [],
        ];

        foreach ($records as $code => $record) {
            $data['adjustment'][$code] = (int) ($record->adjustment ?? 0);
            $data['reason_adjust'][$code] = $record->reason_adjust ?? '';
            $data['reason_target'][$code] = $record->reason_target ?? '';
            $data['selected'][$code] = (int) ($record->is_selected ?? 0);
            $data['agreed_level'][$code] = (int) ($record->agreed_level ?? 0);
        }

        return $data;
    }

    /**
     * Get assessment model
     */
    private function getAssessment(int $assessmentId): ?Assessment
    {
        return Assessment::where('assessment_id', $assessmentId)->first();
    }

    /**
     * Save Step 4 rows for all objectives
     */
    private function saveStep4(Request $request, int $assessmentId, int $userId): void
    {
        $adjustments = (array) $request->input('adjustment', []);
        $reasonAdjust = (array) $request->input('reason_adjust', []);
        $reasonTarget = (array) $request->input('reason_target', []);
        $concluded = (array) $request->input('concluded_priority', []);
        $suggested = (array) $request->input('suggested_level', []);
        $agreed = (array) $request->input('agreed_level', []);
        $selected = (array) $request->input('selected', []);

        $objectiveMap = $this->getObjectiveIdMap();

        for ($code = 1; $code <= 40; $code++) {
            $objectiveId = $objectiveMap[$code] ?? null;
            if (! $objectiveId) {
                continue;
            }

            $suggestedLevel = isset($suggested[$code]) ? (int) $suggested[$code] : 1;
            $agreedLevel = isset($agreed[$code]) ? (int) $agreed[$code] : $suggestedLevel;

            TrsStep4::updateOrCreate(
                [
                    'assessment_id' => $assessmentId,
                    'user_id' => $userId,
                    'objective_code' => $code,
                ],
                [
                    'objective_id' => $objectiveId,
                    'adjustment' => (int) ($adjustments[$code] ?? 0),
                    'reason_adjust' => $reasonAdjust[$code] ?? null,
                    'concluded_priority' => (float) ($concluded[$code] ?? 0),
                    'suggested_level' => $suggestedLevel,
                    'agreed_level' => $agreedLevel,
                    'reason_target' => $reasonTarget[$code] ?? null,
                    'is_selected' => (int) ($selected[$code] ?? 0) === 1 ? 1 : 0,
                ]
            );
        }
    }

    /**
     * Map objective code (1..40) to objective id (EDM01..)
     */
    private function getObjectiveIdMap(): array
    {
        return [
            1 => 'EDM01', 2 => 'EDM02', 3 => 'EDM03', 4 => 'EDM04', 5 => 'EDM05',
            6 => 'APO01', 7 => 'APO02', 8 => 'APO03', 9 => 'APO04', 10 => 'APO05',
            11 => 'APO06', 12 => 'APO07', 13 => 'APO08', 14 => 'APO09', 15 => 'APO10',
            16 => 'APO11', 17 => 'APO12', 18 => 'APO13', 19 => 'APO14',
            20 => 'BAI01', 21 => 'BAI02', 22 => 'BAI03', 23 => 'BAI04', 24 => 'BAI05',
            25 => 'BAI06', 26 => 'BAI07', 27 => 'BAI08', 28 => 'BAI09', 29 => 'BAI10',
            30 => 'BAI11',
            31 => 'DSS01', 32 => 'DSS02', 33 => 'DSS03', 34 => 'DSS04', 35 => 'DSS05', 36 => 'DSS06',
            37 => 'MEA01', 38 => 'MEA02', 39 => 'MEA03', 40 => 'MEA04',
        ];
    }
}
