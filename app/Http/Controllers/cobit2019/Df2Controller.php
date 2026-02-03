<?php

declare(strict_types=1);

namespace App\Http\Controllers\cobit2019;

use App\Data\Cobit\Df2Data;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Cobit\Df2Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * DF2 Controller - IT Management Goals
 */
class Df2Controller extends Controller
{
    public function __construct(
        private readonly Df2Service $service
    ) {}

    public function showDesignFactor2Form(int $id): View
    {
        $assessmentId = session('assessment_id');
        $history = $assessmentId
            ? $this->service->loadHistory($assessmentId, $id)
            : ['inputs' => null, 'scores' => null, 'relativeImportance' => null];

        $userIds = session('respondent_ids', []);
        $users = $this->loadUsers($userIds);

        return view('cobit2019.df2.design_factor2', [
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
        // Build validation rules for 13 inputs
        $rules = ['df_id' => 'required|integer'];
        for ($i = 1; $i <= Df2Data::INPUT_COUNT; $i++) {
            $rules["input{$i}df2"] = 'required|integer';
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

            return redirect()->route('df2.output', ['id' => $validated['df_id']])->with('success', 'Data berhasil disimpan!');
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

        $designFactor2 = null;
        if ($history['inputs']) {
            $obj = (object) ['df_id' => $id];
            foreach ($history['inputs'] as $idx => $val) {
                $obj->{"input" . ($idx + 1) . "df2"} = $val;
            }
            $designFactor2 = $obj;
        }

        $designFactorRelativeImportance = null;
        if ($history['relativeImportance']) {
            $ri = (object) [];
            foreach ($history['relativeImportance'] as $idx => $val) {
                $ri->{'r_df2_' . ($idx + 1)} = $val;
            }
            $designFactorRelativeImportance = $ri;
        }

        return view('cobit2019.df2.df2_output', compact('designFactor2', 'designFactorRelativeImportance'));
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
