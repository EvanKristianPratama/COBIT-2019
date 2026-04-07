<?php

namespace App\Http\Controllers\AssessmentEval;

use App\Http\Controllers\Controller;
use App\Services\Assessment\Access\AssessmentAccessService;
use App\Services\Assessment\Report\AssessmentReportService;
use App\Services\EvaluationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AssessmentReportController extends Controller
{
    public function __construct(
        protected EvaluationService $evaluationService,
        protected AssessmentAccessService $assessmentAccessService,
        protected AssessmentReportService $assessmentReportService
    ) {
    }

    public function show($evalId)
    {
        try {
            $evaluation = $this->evaluationService->getEvaluationById($evalId);

            if (! $evaluation) {
                return redirect()->route('assessment-eval.list')->withErrors(['error' => 'Assessment not found']);
            }

            $currentUser = Auth::user();
            if (! $this->assessmentAccessService->canView($currentUser, $evaluation)) {
                return redirect()->route('assessment-eval.list')->withErrors(['error' => 'Access denied']);
            }

            return view('assessment-eval.report', array_merge(
                $this->assessmentReportService->buildSingleAssessmentReport($evaluation),
                ['canManageAssessment' => $this->assessmentAccessService->canManage($currentUser, $evaluation)]
            ));

        } catch (\Exception $e) {
            Log::error('Failed to load report', ['eval_id' => $evalId, 'error' => $e->getMessage()]);

            return redirect()->back()->withErrors(['error' => 'Failed to load report: '.$e->getMessage()]);
        }
    }

    public function index()
    {
        try {
            $data = $this->assessmentReportService->buildOverviewReport(Auth::user());
            if (isset($data['error'])) {
                return view('assessment-eval.report-all', [
                    'objectives' => [], 'assessments' => [],
                    'scopeMaturityData' => [], 'error' => $data['error'],
                ]);
            }

            return view('assessment-eval.report-all', $data);

        } catch (\Exception $e) {
            Log::error('Failed to load all-years report', ['user_id' => Auth::id(), 'error' => $e->getMessage()]);

            return redirect()->route('assessment-eval.list')->withErrors(['error' => 'Failed to load report: '.$e->getMessage()]);
        }
    }

    public function spiderweb()
    {
        try {
            $data = $this->assessmentReportService->buildOverviewReport(Auth::user());
            if (isset($data['error'])) {
                return view('assessment-eval.report-spiderweb', [
                    'objectives' => [], 'assessments' => [],
                    'scopeMaturityData' => [], 'error' => $data['error'],
                ]);
            }

            return view('assessment-eval.report-spiderweb', $data);

        } catch (\Exception $e) {
            Log::error('Failed to load spiderweb report', ['user_id' => Auth::id(), 'error' => $e->getMessage()]);

            return redirect()->route('assessment-eval.list')->withErrors(['error' => 'Failed to load report: '.$e->getMessage()]);
        }
    }

    public function exportPdf(\Illuminate\Http\Request $request)
    {
        try {
            $scopeIds = $request->input('scope_ids', []);

            // Allow comma separated string if coming from simple form submit
            if (is_string($scopeIds)) {
                $scopeIds = explode(',', $scopeIds);
            }

            $scopeIds = array_filter($scopeIds, function ($id) {
                return is_numeric($id);
            });

            if (empty($scopeIds)) {
                return redirect()->back()->withErrors(['error' => 'No scopes selected for export.']);
            }

            // Reuse existing data fetching logic
            $reportData = $this->assessmentReportService->buildOverviewReport(Auth::user());

            if (isset($reportData['error'])) {
                return redirect()->back()->withErrors(['error' => $reportData['error']]);
            }

            // Filter data based on selected scopes
            $allScopedData = $reportData['processedData'];
            $selectedData = array_filter($allScopedData, function ($item) use ($scopeIds) {
                return in_array($item['scope_id'], $scopeIds);
            });

            // Re-index array
            $selectedData = array_values($selectedData);

            // BUMN Target Override Logic (matches JS config)
            $targetBumnOverride = 3.00;

            // Augment data with effective targets for PDF view simplicity
            foreach ($selectedData as &$data) {
                $scopeName = (string) ($data['scope_name'] ?? '');
                $isBumn = stripos($scopeName, 'bumn') !== false;
                $data['effective_target'] = $isBumn ? $targetBumnOverride : ($data['target_maturity'] ?? 0);
            }
            unset($data);

            $pdf = Pdf::loadView('assessment-eval.report-all-pdf', [
                'objectives' => $reportData['objectives'],
                'selectedData' => $selectedData,
                'showMaxLevel' => $request->input('show_max_level') == '1',
            ]);

            // Set paper size to landscape for better table view
            $pdf->setPaper('a4', 'landscape');

            return $pdf->download('All_Assessments_Report_'.date('Y-m-d_H-i').'.pdf');

        } catch (\Exception $e) {
            Log::error('Failed to export PDF', ['user_id' => Auth::id(), 'error' => $e->getMessage()]);

            return redirect()->back()->withErrors(['error' => 'Failed to export PDF: '.$e->getMessage()]);
        }
    }
}
