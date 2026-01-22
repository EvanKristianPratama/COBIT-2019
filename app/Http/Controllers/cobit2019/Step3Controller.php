<?php
namespace App\Http\Controllers\cobit2019;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\DfStep2;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DfStep3;
use App\Models\TrsStep2;
use App\Models\TrsStep3;
use Illuminate\View\View;

class Step3Controller extends Controller
{
    private const DEFAULT_WEIGHTS = [1, 1, 1, 1, 1, 1];
    private const DF_RANGE = [5, 6, 7, 8, 9, 10]; // DF5 to DF10

    /**
     * Display Step 3 summary page
     */
    public function index(Request $request): View
    {
        $assessmentId = session('assessment_id');
        if (!$assessmentId) {
            return $this->handleAssessmentNotFound();
        }

        $assessment = $this->getAssessmentWithRelativeImportances();
        if (!$assessment) {
            return $this->handleAssessmentNotFound();
        }

        $userId = Auth::id();
        $savedWeights3 = $this->getSavedWeights($assessmentId);
        
        // Get Step 2 data from database (trs_step2)
        $step2Data = $this->getStep2DataFromDatabase($assessmentId, $userId);
        
        // Get Step 2 weights from database
        $step2Weights = $this->getStep2Weights($assessmentId, $userId);

        return view('cobit2019.step3.step3sumaryblade', [
            'assessment' => $assessment,
            'savedWeights3' => $savedWeights3,
            'step2Weights' => $step2Weights,
            'step2Totals' => $step2Data['totals'],
        ]);
    }

    /**
     * Store Step 3 data
     */
    public function store(Request $request)
    {
        $request->validate([
            'weights3' => 'required|json',
            'refinedScopes' => 'required|json',
        ]);

        $assessmentId = session('assessment_id') ?? $request->input('assessment_id');
        $userId = Auth::id();
        $weights3 = json_decode($request->input('weights3'), true);
        $refinedScopes = json_decode($request->input('refinedScopes'), true);

        DfStep3::updateOrCreate(
            ['assessment_id' => $assessmentId, 'user_id' => $userId],
            ['weights' => $weights3]
        );

        // Save to trs_step3 table (per objective)
        $this->saveTrsStep3($assessmentId, $userId, $weights3, $refinedScopes);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success', 
                'message' => 'Data Step 3 berhasil disimpan.'
            ]);
        }

        return redirect()->route('step3.index')->with('success', 'Data Step 3 berhasil disimpan.');
    }

    /**
     * Save data to trs_step3 table per objective
     */
    private function saveTrsStep3(
        int $assessmentId, 
        int $userId, 
        ?array $weights,
        ?array $refinedScopes
    ): void {
        $weights = is_array($weights) ? array_values($weights) : [1, 1, 1, 1, 1, 1];

        // Get step2 totals from database
        $step2Records = TrsStep2::where('assessment_id', $assessmentId)
            ->where('user_id', $userId)
            ->get()
            ->keyBy('objective_code');

        // Get assessment with relative importances from database for DF5-10
        $assessment = $this->getAssessmentWithRelativeImportances();

        // Process all 40 objectives
        for ($code = 1; $code <= 40; $code++) {
            // Get relative importance row for DF5-10 from database
            $relImpRow = [];
            for ($n = 5; $n <= 10; $n++) {
                $dfIndex = $n - 5;
                $rec = $assessment ? $assessment->{'df' . $n . 'RelativeImportances'}->first() : null;
                $col = "r_df{$n}_{$code}";
                $relImpRow[$dfIndex] = ($rec && isset($rec->$col)) ? $rec->$col : 0;
            }
            
            // Calculate total for step3 (sum of weighted rel_imp DF5-10)
            $totalStep3Objective = 0;
            for ($i = 0; $i < 6; $i++) {
                $totalStep3Objective += ($relImpRow[$i] ?? 0) * ($weights[$i] ?? 1);
            }

            // Get step2 total for this objective from trs_step2
            $step2Total = $step2Records->has($code) 
                ? (float) $step2Records->get($code)->total_objective 
                : 0;

            // Combined total = step2 + step3
            $totalCombined = $step2Total + $totalStep3Objective;

            TrsStep3::updateOrCreate(
                [
                    'assessment_id' => $assessmentId,
                    'user_id' => $userId,
                    'objective_code' => $code,
                ],
                [
                    'rel_imp_df5' => $relImpRow[0] ?? 0,
                    'rel_imp_df6' => $relImpRow[1] ?? 0,
                    'rel_imp_df7' => $relImpRow[2] ?? 0,
                    'rel_imp_df8' => $relImpRow[3] ?? 0,
                    'rel_imp_df9' => $relImpRow[4] ?? 0,
                    'rel_imp_df10' => $relImpRow[5] ?? 0,
                    'total_step3_objective' => $totalStep3Objective,
                    'total_combined' => $totalCombined,
                    'refined_scope_score' => $refinedScopes[$code - 1] ?? $totalCombined,
                ]
            );
        }
    }

    /**
     * Get assessment with relative importances for DF5-DF10
     */
    private function getAssessmentWithRelativeImportances(): ?Assessment
    {
        $assessmentId = session('assessment_id');
        if (!$assessmentId) {
            return null;
        }

        $relations = [];
        foreach (self::DF_RANGE as $n) {
            $relations["df{$n}RelativeImportances"] = fn($q) => $q->latest();
        }

        return Assessment::with($relations)
            ->where('assessment_id', $assessmentId)
            ->first();
    }

    /**
     * Get saved weights from database or return default
     */
    private function getSavedWeights(?int $assessmentId): array
    {
        if (!$assessmentId) {
            return self::DEFAULT_WEIGHTS;
        }

        $dfStep3 = DfStep3::where('assessment_id', $assessmentId)
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->first();

        return is_array($dfStep3->weights ?? null)
            ? $dfStep3->weights
            : self::DEFAULT_WEIGHTS;
    }

    /**
     * Get Step 2 weights from database
     */
    private function getStep2Weights(int $assessmentId, int $userId): array
    {
        $dfStep2 = DfStep2::where('assessment_id', $assessmentId)
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->first();

        return is_array($dfStep2->weights ?? null)
            ? $dfStep2->weights
            : [1, 1, 1, 1];
    }

    /**
     * Get Step 2 data from database (trs_step2)
     */
    private function getStep2DataFromDatabase(int $assessmentId, int $userId): array
    {
        $records = TrsStep2::where('assessment_id', $assessmentId)
            ->where('user_id', $userId)
            ->get()
            ->keyBy('objective_code');

        $totals = [];
        for ($code = 1; $code <= 40; $code++) {
            $totals[$code] = $records->has($code) 
                ? (float) $records->get($code)->total_objective 
                : 0;
        }

        return [
            'totals' => $totals,
            'records' => $records,
        ];
    }

    /**
     * Handle case when assessment is not found
     */
    private function handleAssessmentNotFound(): View
    {
        return view('cobit2019.step3.step3sumaryblade')
            ->with('error', 'Data Assessment tidak ditemukan.');
    }
}
