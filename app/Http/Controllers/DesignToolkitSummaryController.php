<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment;
use App\Models\DfStep2;
use App\Models\DfStep3;
use App\Models\TrsStep2;
use App\Models\TrsStep3;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DesignToolkitSummaryController extends Controller
{
    private const STEP2_DF_RANGE = [1, 2, 3, 4];
    private const STEP3_DF_RANGE = [5, 6, 7, 8, 9, 10];
    private const DEFAULT_WEIGHTS_STEP2 = [1, 1, 1, 1];
    private const DEFAULT_WEIGHTS_STEP3 = [1, 1, 1, 1, 1, 1];

    /**
     * Display Step 2 (Initial Scope)
     */
    public function step2(Request $request)
    {
        $assessmentId = session('assessment_id');
        if (!$assessmentId) {
            return redirect()->route('design-toolkit.index')->with('error', 'Silakan pilih assessment terlebih dahulu.');
        }

        $assessment = $this->getAssessmentWithRelImps($assessmentId, self::STEP2_DF_RANGE);
        if (!$assessment) {
            return redirect()->route('design-toolkit.index')->with('error', 'Data assessment tidak ditemukan.');
        }

        $savedWeights = $this->getSavedWeights($assessmentId, 2);
        
        // Prepare matrix data for the frontend
        $matrix = $this->prepareMatrixData($assessment, self::STEP2_DF_RANGE);

        return Inertia::render('DesignToolkit/Step2/Index', [
            'assessment' => $assessment,
            'dfNumber' => 11,
            'savedWeights' => $savedWeights,
            'matrix' => $matrix,
            'objectiveLabels' => $this->getObjectiveLabels(),
            'routes' => $this->getRoutes(2),
        ]);
    }

    /**
     * Store Step 2 data
     */
    public function storeStep2(Request $request)
    {
        $request->validate([
            'weights' => 'required|array',
            'totals' => 'required|array',
            'initialScopeScores' => 'required|array',
        ]);

        $assessmentId = session('assessment_id');
        $userId = Auth::id();

        DfStep2::updateOrCreate(
            ['assessment_id' => $assessmentId, 'user_id' => $userId],
            ['weights' => $request->weights]
        );

        $this->saveTrsStep2($assessmentId, $userId, $request);

        return redirect()->back()->with('success', 'Data Step 2 berhasil disimpan.');
    }

    /**
     * Display Step 3 (Refined Scope)
     */
    public function step3(Request $request)
    {
        $assessmentId = session('assessment_id');
        if (!$assessmentId) {
            return redirect()->route('design-toolkit.index')->with('error', 'Silakan pilih assessment terlebih dahulu.');
        }

        $assessment = $this->getAssessmentWithRelImps($assessmentId, self::STEP3_DF_RANGE);
        if (!$assessment) {
            return redirect()->route('design-toolkit.index')->with('error', 'Data assessment tidak ditemukan.');
        }

        $userId = Auth::id();
        $savedWeights3 = $this->getSavedWeights($assessmentId, 3);
        $step2Weights = $this->getSavedWeights($assessmentId, 2);
        
        // Get Step 2 totals from database
        $step2Totals = TrsStep2::where('assessment_id', $assessmentId)
            ->where('user_id', $userId)
            ->pluck('total_objective', 'objective_code')
            ->toArray();

        $matrix = $this->prepareMatrixData($assessment, self::STEP3_DF_RANGE);

        return Inertia::render('DesignToolkit/Step3/Index', [
            'assessment' => $assessment,
            'dfNumber' => 12,
            'savedWeights3' => $savedWeights3,
            'step2Weights' => $step2Weights,
            'step2Totals' => $step2Totals,
            'matrix' => $matrix,
            'objectiveLabels' => $this->getObjectiveLabels(),
            'routes' => $this->getRoutes(3),
        ]);
    }

    /**
     * Store Step 3 data
     */
    public function storeStep3(Request $request)
    {
        $request->validate([
            'weights3' => 'required|array',
            'refinedScopeScores' => 'required|array',
        ]);

        $assessmentId = session('assessment_id');
        $userId = Auth::id();

        DfStep3::updateOrCreate(
            ['assessment_id' => $assessmentId, 'user_id' => $userId],
            ['weights' => $request->weights3]
        );

        $this->saveTrsStep3($assessmentId, $userId, $request);

        return redirect()->back()->with('success', 'Data Step 3 berhasil disimpan.');
    }

    // --- Helpers ---

    private function getAssessmentWithRelImps(int $assessmentId, array $dfRange): ?Assessment
    {
        $relations = [];
        foreach ($dfRange as $n) {
            $relations["df{$n}RelativeImportances"] = fn($query) => $query->latest();
        }

        return Assessment::with($relations)
            ->where('assessment_id', $assessmentId)
            ->first();
    }

    private function getSavedWeights(int $assessmentId, int $step): array
    {
        $model = $step === 2 ? DfStep2::class : DfStep3::class;
        $default = $step === 2 ? self::DEFAULT_WEIGHTS_STEP2 : self::DEFAULT_WEIGHTS_STEP3;

        $record = $model::where('assessment_id', $assessmentId)
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->first();

        return is_array($record->weights ?? null) ? $record->weights : $default;
    }

    private function prepareMatrixData(Assessment $assessment, array $dfRange): array
    {
        $matrix = [];
        for ($code = 1; $code <= 40; $code++) {
            $row = [];
            foreach ($dfRange as $n) {
                $rec = $assessment->{"df{$n}RelativeImportances"}->first();
                $col = "r_df{$n}_{$code}";
                $row["df{$n}"] = ($rec && isset($rec->$col)) ? (float) $rec->$col : 0.0;
            }
            $matrix[$code] = $row;
        }
        return $matrix;
    }

    private function saveTrsStep2(int $assessmentId, int $userId, Request $request): void
    {
        $totals = $request->totals;
        $initialScopeScores = $request->initialScopeScores;
        $weights = $request->weights;

        // Note: Re-fetching matrix data to ensure rel_imp values are saved correctly
        $assessment = $this->getAssessmentWithRelImps($assessmentId, self::STEP2_DF_RANGE);
        
        for ($code = 1; $code <= 40; $code++) {
            $relImps = [];
            foreach (self::STEP2_DF_RANGE as $n) {
                $rec = $assessment->{"df{$n}RelativeImportances"}->first();
                $col = "r_df{$n}_{$code}";
                $relImps[] = ($rec && isset($rec->$col)) ? $rec->$col : 0;
            }

            TrsStep2::updateOrCreate(
                [
                    'assessment_id' => $assessmentId,
                    'user_id' => $userId,
                    'objective_code' => $code,
                ],
                [
                    'rel_imp_df1' => $relImps[0] ?? 0,
                    'rel_imp_df2' => $relImps[1] ?? 0,
                    'rel_imp_df3' => $relImps[2] ?? 0,
                    'rel_imp_df4' => $relImps[3] ?? 0,
                    'total_objective' => $totals[$code] ?? 0,
                    'initial_scope_score' => $initialScopeScores[$code - 1] ?? 0,
                ]
            );
        }
    }

    private function saveTrsStep3(int $assessmentId, int $userId, Request $request): void
    {
        $weights3 = $request->weights3;
        $refinedScopeScores = $request->refinedScopeScores;

        $assessment = $this->getAssessmentWithRelImps($assessmentId, self::STEP3_DF_RANGE);
        $step2Records = TrsStep2::where('assessment_id', $assessmentId)
            ->where('user_id', $userId)
            ->get()
            ->keyBy('objective_code');

        for ($code = 1; $code <= 40; $code++) {
            $relImps = [];
            foreach (self::STEP3_DF_RANGE as $n) {
                $rec = $assessment->{"df{$n}RelativeImportances"}->first();
                $col = "r_df{$n}_{$code}";
                $relImps[] = ($rec && isset($rec->$col)) ? $rec->$col : 0;
            }

            // Calculate step 3 total
            $tot3 = 0;
            foreach ($relImps as $i => $val) {
                $tot3 += $val * ($weights3[$i] ?? 1);
            }

            $step2Total = $step2Records->has($code) ? (float) $step2Records->get($code)->total_objective : 0;
            $totalCombined = $step2Total + $tot3;

            TrsStep3::updateOrCreate(
                [
                    'assessment_id' => $assessmentId,
                    'user_id' => $userId,
                    'objective_code' => $code,
                ],
                [
                    'rel_imp_df5' => $relImps[0] ?? 0,
                    'rel_imp_df6' => $relImps[1] ?? 0,
                    'rel_imp_df7' => $relImps[2] ?? 0,
                    'rel_imp_df8' => $relImps[3] ?? 0,
                    'rel_imp_df9' => $relImps[4] ?? 0,
                    'rel_imp_df10' => $relImps[5] ?? 0,
                    'total_step3_objective' => $tot3,
                    'total_combined' => $totalCombined,
                    'refined_scope_score' => $refinedScopeScores[$code - 1] ?? 0,
                ]
            );
        }
    }

    private function getRoutes(int $step): array
    {
        $dfRoutes = [];
        for ($i = 1; $i <= 10; $i++) {
            $dfRoutes[$i] = route('design-toolkit.show', ['number' => $i]);
        }

        return [
            'dashboard' => route('dashboard'),
            'index' => route('design-toolkit.index'),
            'show' => $dfRoutes,
            'store' => $step === 2 ? route('design-toolkit.step2.store') : route('design-toolkit.step3.store'),
        ];
    }

    private function getObjectiveLabels(): array
    {
        return [
            'EDM01', 'EDM02', 'EDM03', 'EDM04', 'EDM05',
            'APO01', 'APO02', 'APO03', 'APO04', 'APO05',
            'APO06', 'APO07', 'APO08', 'APO09', 'APO10',
            'APO11', 'APO12', 'APO13', 'APO14',
            'BAI01', 'BAI02', 'BAI03', 'BAI04', 'BAI05',
            'BAI06', 'BAI07', 'BAI08', 'BAI09', 'BAI10', 'BAI11',
            'DSS01', 'DSS02', 'DSS03', 'DSS04', 'DSS05', 'DSS06',
            'MEA01', 'MEA02', 'MEA03', 'MEA04',
        ];
    }
}
