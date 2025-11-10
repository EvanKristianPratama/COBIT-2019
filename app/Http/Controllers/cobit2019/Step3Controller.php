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
    /**
     * Menampilkan halaman summary untuk Design Factor 5–10
     */
    public function index(Request $request): View
    {
        $assessment = $this->getAssessmentWithRelativeImportances();
        if (!$assessment) {
            return $this->handleAssessmentNotFound();
        }

        // Ambil saved weights Step3 dari DB (df_step3) untuk current user
        $assessmentId = session('assessment_id');
        $dfStep3 = DfStep3::where('assessment_id', $assessmentId)
                    ->where('user_id', auth()->id())
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id','desc')
                    ->first();

        $defaultWeights3 = [1,1,1,1,1,1];
        $savedWeights3 = is_array($dfStep3->weights ?? null) ? $dfStep3->weights : $defaultWeights3;

        // Ambil data Step 2 dari session
        $step2Weights = session('step2.weights', [0, 0, 0, 0]);
        $step2RelImps = session('step2.relative_importances', []);
        $step2Totals = session('step2.totals', []);

        return $this->renderStep3View(
            $assessment,
            $step2Weights,
            $step2RelImps,
            $step2Totals,
            $savedWeights3
        );
    }

    /**
     * Ambil Assessment + RI untuk DF5–DF10
     */
    private function getAssessmentWithRelativeImportances(): ?Assessment
    {
        $assessmentId = session('assessment_id');
        if (!$assessmentId) {
            return null;
        }

        return Assessment::with([
            'df5RelativeImportances' => fn($q) => $q->latest(),
            'df6RelativeImportances' => fn($q) => $q->latest(),
            'df7RelativeImportances' => fn($q) => $q->latest(),
            'df8RelativeImportances' => fn($q) => $q->latest(),
            'df9RelativeImportances' => fn($q) => $q->latest(),
            'df10RelativeImportances' => fn($q) => $q->latest(),
        ])
            ->where('assessment_id', $assessmentId)
            ->first();
    }

    /**
     * Jika assessment tidak ditemukan
     */
    private function handleAssessmentNotFound(): View
    {
        return view('cobit2019.step3.step3sumaryblade')
            ->with('error', 'Data Assessment tidak ditemukan.');
    }
    /**
     * Simpan data Step 3 ke session
     *//**
     * Store Step 3 data ke session
     */
  public function store(Request $request)
{
    $request->validate([
        'weights3'      => 'required|json',
        'refinedScopes' => 'required|json',
    ]);

    $assessmentId = session('assessment_id') ?? $request->input('assessment_id');
    $userId = Auth::id();

    $weights3 = json_decode($request->input('weights3'), true);

    DfStep3::updateOrCreate(
        ['assessment_id' => $assessmentId, 'user_id' => $userId],
        [
            'weights' => $weights3,
        ]
    );

    return redirect()->route('step3.index')->with('success', 'Data Step 3 berhasil disimpan.');
}


    /**
     * Render view Step 3 dengan semua data
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
