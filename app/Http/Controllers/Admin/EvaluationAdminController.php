<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MstEval;
use App\Services\Assessment\List\AssessmentListService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EvaluationAdminController extends Controller
{
    public function __construct(
        private readonly AssessmentListService $assessmentListService
    ) {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $this->ensureAdmin();

        return view('admin.evaluations.index', $this->assessmentListService->getAdminIndexData(
            $request->only(['q', 'year', 'organization_id', 'status', 'sort_by', 'sort_direction'])
        ));
    }

    public function updateVisibility(Request $request): RedirectResponse
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'assessment_ids' => ['required', 'array', 'min:1', 'max:15'],
            'assessment_ids.*' => ['required', 'integer', 'distinct', 'exists:mst_eval,eval_id'],
            'visible_ids' => ['sometimes', 'array', 'max:15'],
            'visible_ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $ids = array_map('intval', $data['assessment_ids']);
        $visibleIds = array_map('intval', $data['visible_ids'] ?? []);
        if (array_diff($visibleIds, $ids) !== []) {
            throw ValidationException::withMessages(['visible_ids' => 'Pilihan assessment tidak valid.']);
        }

        DB::transaction(function () use ($ids, $visibleIds): void {
            MstEval::query()->whereIn('eval_id', $ids)->update(['show_on_user_page' => false]);
            if ($visibleIds !== []) {
                MstEval::query()->whereIn('eval_id', $visibleIds)->update(['show_on_user_page' => true]);
            }
        });

        return back()->with('success', 'Tampilan assessment berhasil disimpan.');
    }

    private function ensureAdmin(): void
    {
        if (! Auth::check() || ! Auth::user()->isAdmin()) {
            abort(403);
        }
    }
}
