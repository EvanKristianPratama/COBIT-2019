<?php
namespace App\Http\Controllers\cobit2019;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Assessment;
use Illuminate\Support\Facades\Auth;
use App\Models\DfStep2;
use App\Models\TrsStep2;

class Step2Controller extends Controller
{
    private const DEFAULT_WEIGHTS = [1, 1, 1, 1];
    private const DF_RANGE = [1, 2, 3, 4]; // DF1 to DF4

    /**
     * Display Step 2 summary page
     */
    public function index(Request $request)
    {
        $assessmentId = $this->getAssessmentId($request);
        if (!$assessmentId) {
            return redirect()->back()->with('error', 'Assessment ID tidak ditemukan.');
        }

        $assessment = $this->getAssessmentWithRelativeImportances($assessmentId);
        if (!$assessment) {
            return redirect()->back()->with('error', 'Data Assessment tidak ditemukan.');
        }

        $savedWeights = $this->getSavedWeights($assessmentId);
        $userIds = collect([auth()->id()]);

        return view('cobit2019.step2.step2sumaryblade', compact('assessment', 'userIds', 'savedWeights'));
    }

    /**
     * Store Step 2 data
     */
    public function storeStep2(Request $request)
    {
        $request->validate([
            'weights' => 'required|json',
            'totals' => 'nullable|json',
        ]);

        $assessmentId = session('assessment_id') ?? $request->input('assessment_id');
        $userId = Auth::id();
        $weights = json_decode($request->input('weights'), true);
        $totals = $request->has('totals') ? json_decode($request->input('totals'), true) : null;
        $relImps = $request->has('relative_importances') ? json_decode($request->input('relative_importances'), true) : null;
        $initialScopes = $request->has('initial_scope_scores') ? json_decode($request->input('initial_scope_scores'), true) : null;

        DfStep2::updateOrCreate(
            ['assessment_id' => $assessmentId, 'user_id' => $userId],
            ['weights' => $weights]
        );

        // Save to trs_step2 table (per objective)
        $this->saveTrsStep2($assessmentId, $userId, $weights, $relImps, $totals, $initialScopes);

        // Store step2 weights, totals and relative importances into session so Step 3 can read them
        if (is_array($weights)) {
            session(['step2.weights' => array_values($weights)]);
        }
        if (is_array($totals)) {
            session(['step2.totals' => $totals]);
        }
        if (is_array($relImps)) {
            session(['step2.relative_importances' => $relImps]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success', 
                'message' => 'Data Step 2 berhasil disimpan otomatis.'
            ]);
        }

        return redirect()->route('step2.index')->with('success', 'Data Step 2 berhasil disimpan.');
    }

    /**
     * Save data to trs_step2 table per objective
     */
    private function saveTrsStep2(
        int $assessmentId, 
        int $userId, 
        ?array $weights, 
        ?array $relImps, 
        ?array $totals,
        ?array $initialScopes
    ): void {
        $weights = is_array($weights) ? array_values($weights) : [1, 1, 1, 1];

        // Get assessment with relative importances from database
        $assessment = $this->getAssessmentWithRelativeImportances($assessmentId);
        if (!$assessment) {
            return;
        }

        // Process all 40 objectives
        for ($code = 1; $code <= 40; $code++) {
            // Get relative importances from database
            $relImpRow = [];
            for ($n = 1; $n <= 4; $n++) {
                $rec = $assessment->{'df' . $n . 'RelativeImportances'}->first();
                $col = "r_df{$n}_{$code}";
                $relImpRow[] = ($rec && isset($rec->$col)) ? $rec->$col : 0;
            }
            
            // Calculate total for this objective (sum of weighted rel_imp)
            $totalObjective = 0;
            for ($i = 0; $i < 4; $i++) {
                $totalObjective += ($relImpRow[$i] ?? 0) * ($weights[$i] ?? 1);
            }

            TrsStep2::updateOrCreate(
                [
                    'assessment_id' => $assessmentId,
                    'user_id' => $userId,
                    'objective_code' => $code,
                ],
                [
                    'rel_imp_df1' => $relImpRow[0] ?? 0,
                    'rel_imp_df2' => $relImpRow[1] ?? 0,
                    'rel_imp_df3' => $relImpRow[2] ?? 0,
                    'rel_imp_df4' => $relImpRow[3] ?? 0,
                    'total_objective' => $totalObjective,
                    'initial_scope_score' => $initialScopes[$code - 1] ?? $totalObjective,
                ]
            );
        }
    }

    /**
     * Get assessment ID from session or guest fallback
     */
    private function getAssessmentId(Request $request): ?int
    {
        // Guest users always use assessment ID 1
        if ($request->user()->role === 'guest') {
            return 1;
        }

        return session('assessment_id');
    }

    /**
     * Get assessment with all relative importances for DF1-DF4
     */
    private function getAssessmentWithRelativeImportances(int $assessmentId): ?Assessment
    {
        $relations = [];
        foreach (self::DF_RANGE as $n) {
            $relations["df{$n}RelativeImportances"] = fn($query) => $query->latest();
        }

        return Assessment::with($relations)
            ->where('assessment_id', $assessmentId)
            ->first();
    }

    /**
     * Get saved weights from database or return default
     */
    private function getSavedWeights(int $assessmentId): array
    {
        $dfStep2 = DfStep2::where('assessment_id', $assessmentId)
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        return is_array($dfStep2->weights ?? null) 
            ? $dfStep2->weights 
            : self::DEFAULT_WEIGHTS;
    }
}