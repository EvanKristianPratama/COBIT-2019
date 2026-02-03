<?php

declare(strict_types=1);

namespace App\Http\Controllers\cobit2019;

use App\Data\Cobit\Df4Data;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Cobit\Df4Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * DF4 Controller - IT-Related Issues
 */
class Df4Controller extends Controller
{
    public function __construct(
        private readonly Df4Service $service
    ) {}

    public function showDesignFactor4Form(int $id): View
    {
        $assessmentId = session('assessment_id');
        $history = $assessmentId
            ? $this->service->loadHistory($assessmentId)
            : ['inputs' => null, 'scores' => null, 'relativeImportance' => null];

        $userIds = session('respondent_ids', []);
        $users = $this->loadUsers($userIds);

        return view('cobit2019.df4.design_factor4', [
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
        // Build validation rules for 20 inputs
        $rules = ['df_id' => 'required|integer'];
        for ($i = 1; $i <= Df4Data::INPUT_COUNT; $i++) {
            $rules["input{$i}df4"] = 'required|integer';
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

            return redirect()->route('df4.output', ['id' => $validated['df_id']])->with('success', 'Data berhasil disimpan!');
        } catch (\Exception $e) {
            return $this->errorResponse($request, $e->getMessage());
        }
    }

    public function showOutput(int $id): View
    {
        $assessmentId = session('assessment_id');
        $history = $assessmentId ? $this->service->loadHistory($assessmentId) : ['inputs' => null, 'relativeImportance' => null];

        $designFactor4 = null;
        if ($history['inputs']) {
            $obj = (object) ['df_id' => $id];
            foreach ($history['inputs'] as $key => $val) {
                $obj->{$key} = $val;
            }
            $designFactor4 = $obj;
        }

        $designFactorRelativeImportance = null;
        if ($history['relativeImportance']) {
            $ri = (object) [];
            foreach ($history['relativeImportance'] as $idx => $val) {
                $ri->{'r_df4_' . ($idx + 1)} = $val;
            }
            $designFactorRelativeImportance = $ri;
        }

        return view('cobit2019.df4.df4_output', compact('designFactor4', 'designFactorRelativeImportance'));
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
