<?php

declare(strict_types=1);

namespace App\Http\Controllers\cobit2019;

use App\Data\Cobit\Df3Data;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Cobit\Df3Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * DF3 Controller - Risk Profile
 */
class Df3Controller extends Controller
{
    public function __construct(
        private readonly Df3Service $service
    ) {}

    public function showDesignFactor3Form(int $id): View
    {
        $assessmentId = session('assessment_id');
        $history = $assessmentId
            ? $this->service->loadHistory($assessmentId, $id)
            : ['inputs' => null, 'scores' => null, 'relativeImportance' => null];

        $userIds = session('respondent_ids', []);
        $users = $this->loadUsers($userIds);

        return view('cobit2019.df3.design_factor3', [
            'id' => $id,
            'historyInputs' => $history['inputs'],
            'historyScoreArray' => $history['scores'],
            'historyRIArray' => $history['relativeImportance'],
            'userIds' => $userIds,
            'users' => $users,
            'allSubmissions' => collect(),
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        // Build validation rules for 19 inputs + impacts + likelihoods
        $rules = ['df_id' => 'required|integer'];
        for ($i = 1; $i <= Df3Data::INPUT_COUNT; $i++) {
            $rules["input{$i}df3"] = 'nullable|integer';
            $rules["impact{$i}"] = 'nullable|numeric';
            $rules["likelihood{$i}"] = 'nullable|numeric';
        }
        $validated = $request->validate($rules);

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

            return redirect()->route('df3.output', ['id' => $validated['df_id']])->with('success', 'Data berhasil disimpan!');
        } catch (\Exception $e) {
            return $this->errorResponse($request, $e->getMessage());
        }
    }

    public function showOutput(int $id): View
    {
        $assessmentId = session('assessment_id');
        $history = $assessmentId
            ? $this->service->loadHistory($assessmentId, $id)
            : ['inputs' => null, 'scores' => null, 'relativeImportance' => null];

        if ($history['inputs']) {
            $designFactor3a = (object) array_merge(['df_id' => $id], $history['inputs']);
            if (!empty($history['scores'])) {
                foreach ($history['scores'] as $idx => $val) {
                    $designFactor3a->{'s_df3_' . ($idx + 1)} = $val;
                }
            }
        } else {
            $designFactor3a = null;
        }

        $designFactorRelativeImportance = null;
        if ($history['relativeImportance']) {
            $ri = (object) [];
            foreach ($history['relativeImportance'] as $idx => $val) {
                $ri->{'r_df3_' . ($idx + 1)} = $val;
            }
            $designFactorRelativeImportance = $ri;
        }

        return view('cobit2019.df3.df3_output', compact('designFactor3a', 'designFactorRelativeImportance'));
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
