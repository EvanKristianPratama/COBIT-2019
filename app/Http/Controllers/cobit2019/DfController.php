<?php

declare(strict_types=1);

namespace App\Http\Controllers\cobit2019;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Cobit\Df1Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * DF1 Controller - Enterprise Strategy
 */
class DfController extends Controller
{
    public function __construct(
        private readonly Df1Service $service
    ) {}

    public function showDesignFactorForm(int $id): View
    {
        $assessmentId = session('assessment_id');
        $history = $assessmentId
            ? $this->service->loadHistory($assessmentId)
            : ['inputs' => null, 'scores' => null, 'relativeImportance' => null];

        $userIds = session('respondent_ids', []);
        $users = $this->loadUsers($userIds);

        return view('cobit2019.df1.design_factor', [
            'id' => $id,
            'history' => null, 
            'historyInputs' => $history['inputs'],
            'historyScoreArray' => $history['scores'],
            'historyRIArray' => $history['relativeImportance'],
            'allSubmissions' => collect(),
            'users' => $users,
            'email' => [],
            'jabatan' => [],
            'userIds' => $userIds,
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'df_id' => 'required|integer',
            'strategy_archetype' => 'required|integer',
            'current_performance' => 'required|integer',
            'future_goals' => 'required|integer',
            'alignment_with_it' => 'required|integer',
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
                    'message' => 'Data berhasil disimpan!',
                    'historyInputs' => $result['inputs'],
                    'historyScoreArray' => $result['scores'],
                    'historyRIArray' => $result['relativeImportance'],
                ]);
            }

            return redirect()->route('df1.output', ['id' => $validated['df_id']])->with('success', 'Data berhasil disimpan!');
        } catch (\Exception $e) {
            return $this->errorResponse($request, $e->getMessage());
        }
    }

    public function showOutput(int $id): View
    {
        $assessmentId = session('assessment_id');
        $history = $assessmentId ? $this->service->loadHistory($assessmentId) : ['inputs' => null, 'scores' => null, 'relativeImportance' => null];

        $designFactor = $history['inputs'] ? (object) [
            'df_id' => $id,
            'input1df1' => $history['inputs'][0] ?? 0,
            'input2df1' => $history['inputs'][1] ?? 0,
            'input3df1' => $history['inputs'][2] ?? 0,
            'input4df1' => $history['inputs'][3] ?? 0,
        ] : null;

        $designFactorScore = null;
        if ($history['scores']) {
            $s = (object) [];
            foreach ($history['scores'] as $idx => $val) {
                $s->{'s_df1_' . ($idx + 1)} = $val;
            }
            $designFactorScore = $s;
        }

        $designFactorRelativeImportance = null;
        if ($history['relativeImportance']) {
            $ri = (object) [];
            foreach ($history['relativeImportance'] as $idx => $val) {
                $ri->{'r_df1_' . ($idx + 1)} = $val;
            }
            $designFactorRelativeImportance = $ri;
        }

        if (!$designFactor || !$designFactorScore || !$designFactorRelativeImportance) {
            return redirect()->route('home')->with('error', 'Data tidak ditemukan.');
        }

        return view('cobit2019.df1.df1_output', compact('designFactor', 'designFactorScore', 'designFactorRelativeImportance'));
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
