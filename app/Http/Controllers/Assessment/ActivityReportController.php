<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use App\Services\Assessment\Access\AssessmentAccessService;
use App\Services\Assessment\Report\ActivityReportService;
use App\Services\EvaluationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ActivityReportController extends Controller
{
    public function __construct(
        protected EvaluationService $evaluationService,
        protected AssessmentAccessService $assessmentAccessService,
        protected ActivityReportService $activityReportService
    ) {
    }

    /**
     * Show activity report for a specific objective
     */
    public function show($evalId, $objectiveId, Request $request)
    {
        $filterLevel = $request->query('level');
        $data = $this->prepareActivityData($evalId, $objectiveId, $filterLevel);

        if ($data instanceof RedirectResponse) {
            return $data;
        }

        return view('assessment.report.activity', $data);
    }

    /**
     * Download PDF report for a specific objective
     */
    public function downloadPdf($evalId, $objectiveId, Request $request)
    {
        $filterLevel = $request->query('level');
        $data = $this->prepareActivityData($evalId, $objectiveId, $filterLevel);

        if ($data instanceof RedirectResponse) {
            return $data;
        }

        $pdf = Pdf::loadView('assessment.report.activity-pdf', $data);
        $pdf->setPaper('a4', 'landscape');

        $filename = 'Activity-Report-'.$evalId.'-'.$objectiveId.'.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Prepare activity data for both view and PDF
     */
    private function prepareActivityData($evalId, $objectiveId, $filterLevel = null)
    {
        try {
            $evaluation = $this->evaluationService->getEvaluationById($evalId);

            if (! $evaluation) {
                return redirect()->route('assessment.index')->withErrors(['error' => 'Assessment not found']);
            }

            $currentUser = Auth::user();
            if (! $this->assessmentAccessService->canView($currentUser, $evaluation)) {
                return redirect()->route('assessment.index')->withErrors(['error' => 'Access denied']);
            }

            return $this->activityReportService->build($evaluation, $objectiveId, $filterLevel);
        } catch (\Exception $e) {
            Log::error('Failed to load activity report', [
                'eval_id' => $evalId,
                'objective_id' => $objectiveId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->withErrors(['error' => 'Failed to load activity report: '.$e->getMessage()]);
        }
    }
}
