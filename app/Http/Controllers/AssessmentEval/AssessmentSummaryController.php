<?php

namespace App\Http\Controllers\AssessmentEval;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssessmentEval\SaveSummaryNoteRequest;
use App\Models\MstEval;
use App\Services\Assessment\Access\AssessmentAccessService;
use App\Services\Assessment\Summary\AssessmentSummaryService;
use App\Services\EvaluationService;
use Artisan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class AssessmentSummaryController extends Controller
{
    public function __construct(
        private readonly AssessmentSummaryService $assessmentSummaryService,
        private readonly AssessmentAccessService $assessmentAccessService,
        private readonly EvaluationService $evaluationService
    ) {
    }

    public function summary($evalId, $objectiveId = null)
    {
        $evaluation = $this->resolveEvaluation($evalId);
        if (! $this->assessmentAccessService->canView(Auth::user(), $evaluation)) {
            return redirect()->route('assessment-eval.list')->withErrors(['error' => 'Access denied']);
        }

        $data = $this->assessmentSummaryService->getSummary($evaluation, $objectiveId);
        $roadmap = $this->assessmentSummaryService->getRoadmapTargetCapability($objectiveId);

        return view('assessment-eval.report-summary', array_merge($data, compact('roadmap')));
    }

    public function saveNote(SaveSummaryNoteRequest $request, $evalId)
    {
        $evaluation = $this->resolveEvaluation($evalId);
        if (! $this->assessmentAccessService->canManage(Auth::user(), $evaluation)) {
            return redirect()->route('assessment-eval.list')->withErrors(['error' => 'Access denied']);
        }

        $this->assessmentSummaryService->saveNote($evaluation, $request->validated());

        return redirect()->back()->with('success', 'Catatan berhasil disimpan.');
    }

    public function getNote($evalId)
    {
        $evaluation = $this->resolveEvaluation($evalId);
        if (! $this->assessmentAccessService->canView(Auth::user(), $evaluation)) {
            return redirect()->route('assessment-eval.list')->withErrors(['error' => 'Access denied']);
        }

        return view('assessment-eval.summary', $this->assessmentSummaryService->getNotesPageData($evaluation));
    }

    public function summaryPdf($evalId, $objectiveId = null)
    {
        $evaluation = $this->resolveEvaluation($evalId);
        if (! $this->assessmentAccessService->canView(Auth::user(), $evaluation)) {
            return redirect()->route('assessment-eval.list')->withErrors(['error' => 'Access denied']);
        }

        $data = $this->assessmentSummaryService->getSummary($evaluation, $objectiveId);
        $roadmap = $this->assessmentSummaryService->getRoadmapTargetCapability($objectiveId);
        $data = array_merge($data, compact('roadmap'));

        $pdf = Pdf::loadView('assessment-eval.report-summary-pdf', $data);
        $pdf->setPaper('a4', 'landscape');

        $filename = 'Summary-Report-'.$evaluation->eval_id.($objectiveId ? '-'.$objectiveId : '').'.pdf';

        return $pdf->stream($filename);
    }

    public function summaryDetailPdf($evalId, $objectiveId = null)
    {
        $evaluation = $this->resolveEvaluation($evalId);
        if (! $this->assessmentAccessService->canView(Auth::user(), $evaluation)) {
            return redirect()->route('assessment-eval.list')->withErrors(['error' => 'Access denied']);
        }

        $data = $this->assessmentSummaryService->getSummary($evaluation, $objectiveId);
        $roadmap = $this->assessmentSummaryService->getRoadmapTargetCapability($objectiveId);
        $data = array_merge($data, compact('roadmap'));

        $pdf = Pdf::loadView('assessment-eval.report-summary-detail-pdf', $data);
        $pdf->setPaper('a4', 'landscape');

        $filename = 'Summary-Detail-Report-'.$evaluation->eval_id.($objectiveId ? '-'.$objectiveId : '').'.pdf';

        return $pdf->stream($filename);
    }

    private function resolveEvaluation($evalId): MstEval
    {
        $evaluation = $this->evaluationService->getEvaluationById($evalId);

        abort_if(! $evaluation, 404);

        return $evaluation;
    }

    public function cleanupEvidence($secret_key)  // ❌ Hapus parameter $evalId di sini
    {
        $configuredSecret = (string) config('services.assessment_eval.cleanup_secret');

        if ($configuredSecret === '' || ! hash_equals($configuredSecret, (string) $secret_key)) {
            abort(403, 'Unauthorized');
        }

        // Cek parameter dari query string
        $dryRun = request()->has('dry-run');
        $evalId = request()->get('eval');  // ✅ Ambil dari query string, bukan parameter route

        // Prepare options
        $options = [];
        if ($dryRun) {
            $options['--dry-run'] = true;
        }
        if ($evalId) {
            $options['--eval'] = $evalId;
        }

        // Jalankan command
        Artisan::call('evidence:cleanup', $options);

        // Tampilkan hasil
        $output = Artisan::output();

        // Return dengan format yang mudah dibaca
        return response("<pre style='background:#1e1e1e;color:#00ff00;padding:20px;font-family:monospace;'>$output</pre>");
    }
}
