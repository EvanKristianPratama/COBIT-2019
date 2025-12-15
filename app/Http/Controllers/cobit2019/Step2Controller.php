<?php
namespace App\Http\Controllers\cobit2019;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Assessment;
use Illuminate\Support\Facades\Auth;
use App\Models\DfStep2;

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
        ]);

        $assessmentId = session('assessment_id') ?? $request->input('assessment_id');
        $userId = Auth::id();
        $weights = json_decode($request->input('weights'), true);

        DfStep2::updateOrCreate(
            ['assessment_id' => $assessmentId, 'user_id' => $userId],
            ['weights' => $weights]
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success', 
                'message' => 'Data Step 2 berhasil disimpan otomatis.'
            ]);
        }

        return redirect()->route('step2.index')->with('success', 'Data Step 2 berhasil disimpan.');
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