<?php

namespace App\Http\Controllers\Cobit5;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Cobit5\AssessmentScopeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssessmentScopeController extends Controller
{
    public function __construct(
        protected AssessmentScopeService $scopeService
    ) {}

    /**
     * Display the COBIT 5 Assessment Scope page with 5 tabs.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        $assignedOrgs = $user->assignedOrganizations();
        $activeOrgId = $user->activeOrganizationId($assignedOrgs);
        $activeOrgName = $assignedOrgs->firstWhere('organization_id', $activeOrgId)?->organization_name
            ?? $user->activeOrganizationName()
            ?? 'Organisasi';

        $scopeData = $this->scopeService->getScopeData($activeOrgId, (int) $user->id);
        $dimensions = $scopeData['dimensions'] ?? [];
        $selfDiagnostic = $scopeData['self_diagnostic'] ?? [];
        $itrgHierarchy = $this->scopeService->getItrgHierarchy();

        $totalEgWeight = 0;
        $totalItrgScore = 0;
        $totalItrgItems = 0;

        foreach ($dimensions as $dim) {
            $totalEgWeight += $dim['eg_count'] ?? 0;
            $totalItrgScore += $dim['total_score'] ?? 0;
            foreach ($dim['enterprise_goals'] ?? [] as $eg) {
                $totalItrgItems += count($eg['itrgs'] ?? []);
            }
        }

        $activeTab = $request->query('tab', 'eg-hierarchy');

        return view('design_factor.cobit5.scope', [
            'user' => $user,
            'activeOrgId' => $activeOrgId,
            'activeOrgName' => $activeOrgName,
            'dimensions' => $dimensions,
            'selfDiagnostic' => $selfDiagnostic,
            'itrgHierarchy' => $itrgHierarchy,
            'totalEgWeight' => $totalEgWeight,
            'totalItrgScore' => $totalItrgScore,
            'totalItrgItems' => $totalItrgItems,
            'activeTab' => $activeTab,
        ]);
    }

    /**
     * Save customized assessment scope and self-diagnostic.
     */
    public function save(Request $request): JsonResponse|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $assignedOrgs = $user->assignedOrganizations();
        $activeOrgId = $user->activeOrganizationId($assignedOrgs);

        $payload = $request->input('scope_data');

        if (is_string($payload)) {
            $payload = json_decode($payload, true);
        }

        // If payload is empty or not in scope_data, check root
        if (empty($payload)) {
            $payload = [
                'dimensions' => $request->input('dimensions', []),
                'self_diagnostic' => $request->input('self_diagnostic', []),
            ];
        }

        if (! is_array($payload) || empty($payload)) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Data tidak valid.'], 422);
            }

            return back()->with('error', 'Data tidak valid.');
        }

        $this->scopeService->saveScopeData($activeOrgId, (int) $user->id, $payload);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Assessment scope & Self-diagnostic COBIT 5 berhasil disimpan.',
            ]);
        }

        return back()->with('success', 'Assessment scope & Self-diagnostic COBIT 5 berhasil disimpan.');
    }

    /**
     * Reset assessment scope to default template.
     */
    public function reset(Request $request): JsonResponse|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $assignedOrgs = $user->assignedOrganizations();
        $activeOrgId = $user->activeOrganizationId($assignedOrgs);

        $this->scopeService->resetScopeData($activeOrgId);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Assessment scope COBIT 5 berhasil di-reset ke baseline default.',
            ]);
        }

        return back()->with('success', 'Assessment scope COBIT 5 berhasil di-reset ke baseline default.');
    }

    /**
     * Export the assessment scope table to CSV.
     */
    public function export(): StreamedResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $assignedOrgs = $user->assignedOrganizations();
        $activeOrgId = $user->activeOrganizationId($assignedOrgs);

        $scopeData = $this->scopeService->getScopeData($activeOrgId, (int) $user->id);
        $dimensions = $scopeData['dimensions'] ?? [];

        $fileName = 'COBIT5_Assessment_Scope_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($dimensions) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['Balanced Scorecard', 'Enterprise Goal', 'IT-related Goal (ITRG)', 'Level', 'Weight', 'Score']);

            foreach ($dimensions as $dim) {
                $dimName = $dim['dimension'] ?? '';
                fputcsv($handle, [$dimName, '', '', '', $dim['eg_count'] ?? '', $dim['total_score'] ?? '']);

                foreach ($dim['enterprise_goals'] ?? [] as $eg) {
                    $egText = ($eg['code'] ?? '') . '. ' . ($eg['title'] ?? '');
                    fputcsv($handle, ['', $egText, '', '', $eg['weight'] ?? 1, $eg['subtotal'] ?? 0]);

                    foreach ($eg['itrgs'] ?? [] as $itrg) {
                        $itrgText = ($itrg['code'] ?? '') . ' ' . ($itrg['title'] ?? '');
                        fputcsv($handle, ['', '', $itrgText, $itrg['level'] ?? '', '', $itrg['score'] ?? 1]);
                    }
                }
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}
