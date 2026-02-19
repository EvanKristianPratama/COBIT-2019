<?php

declare(strict_types=1);

namespace App\Http\Controllers\cobit2019;

use App\Data\Cobit\Df10Data;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Cobit\Df10Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * DF10 Controller - Threat Landscape Assessment
 *
 * This controller is now THIN:
 * - Handles HTTP request/response
 * - Delegates business logic to Df10Service
 *
 * Follows SOLID principles:
 * - Single Responsibility: Only handles HTTP layer
 * - Dependency Inversion: Depends on service abstraction
 */
class Df10Controller extends Controller
{
    public function __construct(
        private readonly Df10Service $service
    ) {}

    /**
     * Display the DF10 form
     */
    public function showDesignFactor10Form(int $id): View
    {
        $assessmentId = session('assessment_id');

        // Load history from service
        $history = $assessmentId
            ? $this->service->loadHistory($assessmentId)
            : ['inputs' => null, 'scores' => null, 'relativeImportance' => null];

        // Prepare view data
        $userIds = session('respondent_ids', []);
        $users = $this->loadUsers($userIds);

        return view('cobit2019.df10.design_factor10', [
            'id' => $id,
            'historyInputs' => $history['inputs'],
            'historyScoreArray' => $history['scores'],
            'historyRIArray' => $history['relativeImportance'],
            'userIds' => $userIds,
            'users' => $users,
            'aggregatedData' => [],
            'suggestedValues' => [],
            'allSubmissions' => collect(),
        ]);
    }

    /**
     * Store DF10 submission
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        // Validate input
        $validated = $request->validate([
            'df_id' => 'required|integer',
            'input1df10' => 'required|integer|min:0|max:100',
            'input2df10' => 'required|integer|min:0|max:100',
            'input3df10' => 'required|integer|min:0|max:100',
        ]);

        // Check assessment ID
        $assessmentId = session('assessment_id');
        if (!$assessmentId) {
            return $this->errorResponse(
                $request,
                'Assessment ID tidak ditemukan, silahkan join assessment terlebih dahulu.'
            );
        }

        try {
            // Delegate to service
            $result = $this->service->store(
                (int) $validated['df_id'],
                (int) $assessmentId,
                $validated
            );

            // Return response
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'historyInputs' => $result['inputs'],
                    'historyScoreArray' => $result['scores'],
                    'historyRIArray' => $result['relativeImportance'],
                ]);
            }

            return redirect()
                ->route('df10.output', ['id' => $validated['df_id']])
                ->with('success', 'Data berhasil disimpan!');

        } catch (\Exception $e) {
            return $this->errorResponse($request, $e->getMessage());
        }
    }

    /**
     * Display DF10 output/results
     */
    public function showOutput(int $id): View
    {
        $assessmentId = session('assessment_id');
        $history = $assessmentId
            ? $this->service->loadHistory($assessmentId)
            : ['inputs' => null, 'scores' => null, 'relativeImportance' => null];

        $designFactor10Data = [
            'df_id' => $id,
            'input1df10' => (int) ($history['inputs'][0] ?? 0),
            'input2df10' => (int) ($history['inputs'][1] ?? 0),
            'input3df10' => (int) ($history['inputs'][2] ?? 0),
        ];
        for ($i = 1; $i <= Df10Data::OBJECTIVE_COUNT; $i++) {
            $designFactor10Data['s_df10_' . $i] = (float) ($history['scores'][$i - 1] ?? 0);
        }
        $designFactor10 = (object) $designFactor10Data;

        $designFactorRelativeImportanceData = [];
        for ($i = 1; $i <= Df10Data::OBJECTIVE_COUNT; $i++) {
            $designFactorRelativeImportanceData['r_df10_' . $i] = (float) ($history['relativeImportance'][$i - 1] ?? 0);
        }
        $designFactorRelativeImportance = (object) $designFactorRelativeImportanceData;

        return view('cobit2019.df10.df10_output', compact(
            'designFactor10',
            'designFactorRelativeImportance'
        ));
    }

    /**
     * Load users by IDs
     */
    private function loadUsers(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }

        try {
            return User::whereIn('id', $userIds)
                ->pluck('name', 'id')
                ->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Return error response (JSON or redirect)
     */
    private function errorResponse(Request $request, string $message): JsonResponse|RedirectResponse
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 500);
        }

        return redirect()->back()->with('error', $message);
    }
}
