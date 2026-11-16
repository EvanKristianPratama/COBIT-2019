<?php

namespace App\Http\Controllers\cobit2019;

use App\Http\Controllers\Controller;
use App\Services\Cobit\MasterEvidenceQueryService;
use App\Services\Cobit\MasterEvidenceTrackingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MasterEvidenceQueryController extends Controller
{
    public function tracking(Request $request, MasterEvidenceTrackingService $tracking)
    {
        $filters = $request->validate([
            'focus_area' => ['nullable', 'integer', Rule::exists('mst_focusarea', 'id')->where('version', '2019')],
            'objective_id' => ['nullable', 'string', 'max:255'],
            'q' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        return response()->json($tracking->track(array_filter($filters, fn ($value) => $value !== null)));
    }

    public function __invoke(Request $request, MasterEvidenceQueryService $catalog)
    {
        $filters = $request->validate([
            'version' => ['nullable', Rule::in(MasterEvidenceQueryService::VERSIONS)],
            'focus_area' => ['nullable', 'integer', 'min:1'],
            'objective_id' => ['nullable', 'string', 'max:255'],
            'q' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', Rule::in([25, 50, 100])],
        ]);

        return response()->json($catalog->search(array_filter($filters, fn ($value) => $value !== null)));
    }
}
