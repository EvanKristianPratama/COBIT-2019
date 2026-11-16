<?php

namespace App\Http\Controllers\Cobit5;

use App\Http\Controllers\Controller;
use App\Models\MstObjective;
use App\Services\Cobit5\DocumentService;
use Illuminate\Http\Request;

class ComponentController extends Controller
{
    public function show(Request $request, MstObjective $objective, DocumentService $documents)
    {
        abort_unless((string) $objective->focusArea?->version === '5', 404);
        $focusAreaId = (int) $objective->focus_area_id;
        $allObjectives = MstObjective::where('focus_area_id', $focusAreaId)
            ->orderByRaw("CASE WHEN objective_id LIKE 'EDM%' THEN 0 WHEN objective_id LIKE 'APO%' THEN 1 WHEN objective_id LIKE 'BAI%' THEN 2 WHEN objective_id LIKE 'DSS%' THEN 3 ELSE 4 END")
            ->orderBy('objective_id')->get();
        $masterEvidenceFilters = app(\App\Services\Cobit\MasterEvidenceQueryService::class)->filters();
        $workProducts = app(\App\Services\Cobit5\WorkProductService::class)->forObjective($objective);
        if ($request->query('view') === 'data') {
            $masterWorkProducts = app(\App\Services\Cobit5\WorkProductService::class)->forModel($focusAreaId);
            $component = $request->query('component', '');
            $allFocusAreas = \App\Models\MstFocusArea::orderBy('version', 'desc')->orderBy('id')->get();
            $masterEnterGoals = \App\Models\MstEntergoals::with('entergoalsmetr')->orderBy('entergoals_id')->get();
            $masterAlignGoals = \App\Models\MstAligngoals::with('aligngoalsmetr')->orderBy('aligngoals_id')->get();
            $masterRoles = \App\Models\MstRoles::orderBy('role_id')->get();
            $masterPractices = \App\Models\MstPractice::orderBy('practice_id')->get();

            return view('cobit_component.cobit5.manage', compact('objective', 'focusAreaId', 'allObjectives', 'masterEvidenceFilters', 'workProducts', 'masterWorkProducts',
                'component', 'allFocusAreas', 'masterEnterGoals', 'masterAlignGoals', 'masterRoles', 'masterPractices'));
        }

        $document = $documents->build($objective);
        $position = $allObjectives->search(fn ($item) => $item->objective_id === $objective->objective_id);
        $previous = $position > 0 ? $allObjectives->get($position - 1) : null;
        $next = $position !== false ? $allObjectives->get($position + 1) : null;

        return view('cobit_component.cobit5.show', compact('objective', 'focusAreaId', 'allObjectives', 'masterEvidenceFilters', 'workProducts', 'document', 'previous', 'next'));
    }
}
