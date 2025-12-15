<?php
namespace App\Http\Controllers\cobit2019;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DfStep3;
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
        $assessment = $this->getAssessmentWithRelativeImportances();
        if (!$assessment) {
            return $this->handleAssessmentNotFound();
        }

        $assessmentId = session('assessment_id');
        $savedWeights3 = $this->getSavedWeights($assessmentId);
        
        // Get Step 2 data from session with defaults
        $step2Data = $this->getStep2Data();

        return $this->renderStep3View(
            $assessment,
            $step2Data['weights'],
            $step2Data['relImps'],
            $step2Data['totals'],
            $savedWeights3
        );
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

        DfStep3::updateOrCreate(
            ['assessment_id' => $assessmentId, 'user_id' => $userId],
            ['weights' => $weights3]
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success', 
                'message' => 'Data Step 3 berhasil disimpan otomatis.'
            ]);
        }

        return redirect()->route('step3.index')->with('success', 'Data Step 3 berhasil disimpan.');
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
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        return is_array($dfStep3->weights ?? null)
            ? $dfStep3->weights
            : self::DEFAULT_WEIGHTS;
    }

    /**
     * Get Step 2 data from session with defaults
     */
    private function getStep2Data(): array
    {
        return [
            'weights' => session('step2.weights', [0, 0, 0, 0]),
            'relImps' => session('step2.relative_importances', []),
            'totals' => session('step2.totals', [])
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

    /**
     * Render Step 3 view with all required data
     */
    private function renderStep3View(
        Assessment $assessment,
        array $step2Weights,
        array $step2RelImps,
        array $step2Totals,
        array $savedWeights3
    ): View {
        $userIds = collect([Auth::id()]);

        return view('cobit2019.step3.step3sumaryblade', [
            'assessment' => $assessment,
            'userIds' => $userIds,
            'step2Weights' => $step2Weights,
            'step2RelativeImportances' => $step2RelImps,
            'step2Totals' => $step2Totals,
            'savedWeights3' => $savedWeights3,
        ]);
    }
}
