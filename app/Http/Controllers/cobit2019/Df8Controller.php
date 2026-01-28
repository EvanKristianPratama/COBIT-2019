<?php

declare(strict_types=1);

namespace App\Http\Controllers\cobit2019;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Cobit\Df8Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * DF8 Controller - IT Implementation Methods
 */
class Df8Controller extends Controller
{
    public function __construct(
        private readonly Df8Service $service
    ) {}

    public function showDesignFactor8Form(int $id): View
    {
        $assessmentId = session('assessment_id');
        $history = $assessmentId
            ? $this->service->loadHistory($assessmentId)
            : ['inputs' => null, 'scores' => null, 'relativeImportance' => null];

        $userIds = session('respondent_ids', []);
        $users = $this->loadUsers($userIds);

        return view('cobit2019.df8.design_factor8', [
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

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'df_id' => 'required|integer',
            'input1df8' => 'required|integer|min:0|max:100',
            'input2df8' => 'required|integer|min:0|max:100',
            'input3df8' => 'required|integer|min:0|max:100',
        ]);

        $assessmentId = session('assessment_id');
        if (!$assessmentId) {
            return $this->errorResponse($request, 'Assessment ID tidak ditemukan, silahkan join assessment terlebih dahulu.');
        }

        try {
            $result = $this->service->store((int) $validated['df_id'], (int) $assessmentId, $validated);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'historyInputs' => $result['inputs'],
                    'historyScoreArray' => $result['scores'],
                    'historyRIArray' => $result['relativeImportance'],
                ]);
            }

            return redirect()->route('df8.output', ['id' => $validated['df_id']])->with('success', 'Data berhasil disimpan!');
        } catch (\Exception $e) {
            return $this->errorResponse($request, $e->getMessage());
        }
    }

    public function showOutput(int $id): View
    {
        $assessmentId = session('assessment_id');
        $history = $assessmentId ? $this->service->loadHistory($assessmentId) : ['inputs' => null, 'relativeImportance' => null];

        $designFactor8 = $history['inputs'] ? (object) [
            'input1df8' => $history['inputs'][0] ?? 0,
            'input2df8' => $history['inputs'][1] ?? 0,
            'input3df8' => $history['inputs'][2] ?? 0,
        ] : null;

        $designFactorRelativeImportance = null;
        if ($history['relativeImportance']) {
            $ri = (object) [];
            foreach ($history['relativeImportance'] as $idx => $val) {
                $ri->{'r_df8_' . ($idx + 1)} = $val;
            }
            $designFactorRelativeImportance = $ri;
        }

        return view('cobit2019.df8.df8_output', compact('designFactor8', 'designFactorRelativeImportance', 'id'));
    }

    private function loadUsers(array $userIds): array
    {
        if (empty($userIds)) return [];
        try {
            return User::whereIn('id', $userIds)->pluck('name', 'id')->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function errorResponse(Request $request, string $message): JsonResponse|RedirectResponse
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => false, 'message' => $message], 500);
        }
        return redirect()->back()->with('error', $message);
    }
}