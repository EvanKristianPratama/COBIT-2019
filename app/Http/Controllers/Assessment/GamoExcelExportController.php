<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use App\Models\TrsActivityeval;
use App\Services\Assessment\Access\AssessmentAccessService;
use App\Services\Assessment\Eval\AssessmentManagementService;
use App\Services\Assessment\Eval\EvaluationReadService;
use App\Services\Assessment\Export\GamoExcelExport;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GamoExcelExportController extends Controller
{
    public function __invoke(Request $request, string $evalId, EvaluationReadService $reader, AssessmentAccessService $access, AssessmentManagementService $management, GamoExcelExport $export)
    {
        $evaluation = $reader->getEvaluationById($evalId);
        abort_unless($evaluation && $access->canView($request->user(), $evaluation), 404);

        $validated = $request->validate(['scope_id' => ['nullable', 'integer', 'min:1']]);
        $scopeId = $validated['scope_id'] ?? null;
        $data = $management->buildShowData($evaluation, $scopeId ? (int) $scopeId : null);
        abort_if($scopeId && (int) ($data['activeScope']->id ?? 0) !== (int) $scopeId, 404);
        if (! $data['activeScope'] || ! collect($data['selectedDomains'])->contains(fn ($domain) => trim((string) $domain) !== '')) {
            return $this->emptyScopeResponse($request, $evaluation->eval_id, $scopeId, 'Scope belum memiliki GAMO untuk diekspor.');
        }
        $domainOrder = ['EDM' => 1, 'APO' => 2, 'BAI' => 3, 'DSS' => 4, 'MEA' => 5];
        $objectives = $data['objectives']->sortBy(function ($objective) use ($domainOrder) {
            $domain = preg_replace('/\d+/', '', $objective->objective_id);
            return sprintf('%02d_%s', $domainOrder[$domain] ?? 99, $objective->objective_id);
        });
        if ($objectives->isEmpty()) {
            return $this->emptyScopeResponse($request, $evaluation->eval_id, $scopeId, 'Tidak ada GAMO dalam scope ini yang dapat diekspor.');
        }

        $activities = TrsActivityeval::where('eval_id', $evaluation->eval_id)->get()->keyBy('activity_id');
        $spreadsheet = $export->build($objectives, $activities);
        $filename = preg_replace('/[^A-Za-z0-9_-]/', '_', 'Assessment-'.$evaluation->eval_id.'-Scope-'.($data['activeScope']->id ?? 'all')).'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            try {
                (new Xlsx($spreadsheet))->save('php://output');
            } finally {
                $spreadsheet->disconnectWorksheets();
            }
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    private function emptyScopeResponse(Request $request, $evalId, $scopeId, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 422);
        }

        return redirect()->route('assessment.show', ['evalId' => $evalId, 'scope_id' => $scopeId])
            ->with('export_error', $message);
    }
}
