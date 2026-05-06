<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssessmentEval\SaveSummaryNoteRequest;
use App\Models\MstEval;
use App\Services\Assessment\Access\AssessmentAccessService;
use App\Services\Assessment\Summary\AssessmentSummaryService;
use App\Services\EvaluationService;
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
            return redirect()->route('assessment.index')->withErrors(['error' => 'Access denied']);
        }

        $data = $this->assessmentSummaryService->getSummary($evaluation, $objectiveId);
        $roadmap = $this->assessmentSummaryService->getRoadmapTargetCapability($objectiveId);

        return view('assessment.report.summary', array_merge($data, compact('roadmap')));
    }

    public function saveNote(SaveSummaryNoteRequest $request, $evalId)
    {
        $evaluation = $this->resolveEvaluation($evalId);
        if (! $this->assessmentAccessService->canManage(Auth::user(), $evaluation)) {
            return redirect()->route('assessment.index')->withErrors(['error' => 'Access denied']);
        }

        $this->assessmentSummaryService->saveNote($evaluation, $request->validated());

        return redirect()->back()->with('success', 'Catatan berhasil disimpan.');
    }

    public function getNote($evalId)
    {
        $evaluation = $this->resolveEvaluation($evalId);
        if (! $this->assessmentAccessService->canView(Auth::user(), $evaluation)) {
            return redirect()->route('assessment.index')->withErrors(['error' => 'Access denied']);
        }

        return view('assessment.report.note', $this->assessmentSummaryService->getNotesPageData($evaluation));
    }

    public function summaryPdf(\Illuminate\Http\Request $request, $evalId, $objectiveId = null)
    {
        $evaluation = $this->resolveEvaluation($evalId);
        if (! $this->assessmentAccessService->canView(Auth::user(), $evaluation)) {
            return redirect()->route('assessment.index')->withErrors(['error' => 'Access denied']);
        }

        $this->preparePdfRuntime();

        $data = $this->assessmentSummaryService->getSummary($evaluation, $objectiveId);
        $roadmap = $this->assessmentSummaryService->getRoadmapTargetCapability($objectiveId);
        
        $includeRoadmap = $request->input('include_roadmap', '1') == '1';
        $data = array_merge($data, compact('roadmap', 'includeRoadmap'));

        $pdf = Pdf::loadView('assessment.report.export.summary-pdf', $data);
        $pdf->setPaper('a4', 'landscape');

        $filename = 'Summary-Report-'.$evaluation->eval_id.($objectiveId ? '-'.$objectiveId : '').'.pdf';

        return $pdf->stream($filename);
    }

    public function summaryJson(\Illuminate\Http\Request $request, $evalId, $objectiveId = null)
    {
        $evaluation = $this->resolveEvaluation($evalId);
        if (! $this->assessmentAccessService->canView(Auth::user(), $evaluation)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $data = $this->assessmentSummaryService->getSummary($evaluation, $objectiveId);
        $roadmap = $this->assessmentSummaryService->getRoadmapTargetCapability($objectiveId);
        
        $includeRoadmap = $request->input('include_roadmap', '1') == '1';
        $data = array_merge($data, compact('roadmap', 'includeRoadmap'));

        return response()->json($data);
    }

    public function summaryDetailPdf(\Illuminate\Http\Request $request, $evalId, $objectiveId = null)
    {
        $evaluation = $this->resolveEvaluation($evalId);
        if (! $this->assessmentAccessService->canView(Auth::user(), $evaluation)) {
            return redirect()->route('assessment.index')->withErrors(['error' => 'Access denied']);
        }

        $this->preparePdfRuntime();

        $data = $this->assessmentSummaryService->getSummary($evaluation, $objectiveId);
        $roadmap = $this->assessmentSummaryService->getRoadmapTargetCapability($objectiveId);
        
        $includeRoadmap = $request->input('include_roadmap', '1') == '1';
        $data = array_merge($data, compact('roadmap', 'includeRoadmap'));

        $pdf = Pdf::loadView('assessment.report.export.summary-detail-pdf', $data);
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

    private function preparePdfRuntime(): void
    {
        ini_set('memory_limit', '512M');
        @set_time_limit(120);
    }
}
