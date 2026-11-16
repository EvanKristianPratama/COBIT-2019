<?php

namespace App\Http\Controllers\cobit2019;

use App\Http\Controllers\Controller;
use App\Models\MstAligngoals;
use App\Models\MstAligngoalsmetr;
use App\Models\MstEntergoals;
use App\Models\MstEntergoalsmetr;
use Illuminate\Http\Request;

class ObjectiveGoalController extends Controller
{
    public function updateEnterGoal(Request $request, $entergoalsId)
    {
        $data = $request->validate([
            'focus_area_id' => 'nullable|integer|exists:mst_focusarea,id',
            'description' => 'nullable|string',
        ]);

        $goal = MstEntergoals::findOrFail($entergoalsId);
        if (!empty($data['focus_area_id'])) {
            $belongsToRequestedFocusArea = $goal->objectives()
                ->where('mst_objective.focus_area_id', $data['focus_area_id'])
                ->exists();

            if (! $belongsToRequestedFocusArea) {
                abort(404);
            }
        }

        $goal->update([
            'description' => $data['description'] ?? $goal->description,
        ]);

        return response()->json($goal->fresh());
    }

    public function updateAlignGoal(Request $request, $aligngoalsId)
    {
        $data = $request->validate([
            'focus_area_id' => 'nullable|integer|exists:mst_focusarea,id',
            'description' => 'nullable|string',
        ]);

        $goal = MstAligngoals::findOrFail($aligngoalsId);
        if (!empty($data['focus_area_id'])) {
            $belongsToRequestedFocusArea = $goal->objectives()
                ->where('mst_objective.focus_area_id', $data['focus_area_id'])
                ->exists();

            if (! $belongsToRequestedFocusArea) {
                abort(404);
            }
        }

        $goal->update([
            'description' => $data['description'] ?? $goal->description,
        ]);

        return response()->json($goal->fresh());
    }

    public function updateEnterGoalMetric(Request $request, $metricId)
    {
        $data = $request->validate([
            'focus_area_id' => 'nullable|integer|exists:mst_focusarea,id',
            'description' => 'required|string',
        ]);

        $metric = MstEntergoalsmetr::with('entergoals')->findOrFail($metricId);
        if (!empty($data['focus_area_id'])) {
            $goal = $metric->entergoals;
            $belongsToRequestedFocusArea = $goal
                ? $goal->objectives()->where('mst_objective.focus_area_id', $data['focus_area_id'])->exists()
                : false;

            if (! $belongsToRequestedFocusArea) {
                abort(404);
            }
        }

        $metric->update([
            'description' => $data['description'],
        ]);

        return response()->json($metric->fresh());
    }

    public function updateAlignGoalMetric(Request $request, $metricId)
    {
        $data = $request->validate([
            'focus_area_id' => 'nullable|integer|exists:mst_focusarea,id',
            'description' => 'required|string',
        ]);

        $metric = MstAligngoalsmetr::with('aligngoals')->findOrFail($metricId);
        if (!empty($data['focus_area_id'])) {
            $goal = $metric->aligngoals;
            $belongsToRequestedFocusArea = $goal
                ? $goal->objectives()->where('mst_objective.focus_area_id', $data['focus_area_id'])->exists()
                : false;

            if (! $belongsToRequestedFocusArea) {
                abort(404);
            }
        }

        $metric->update([
            'description' => $data['description'],
        ]);

        return response()->json($metric->fresh());
    }
}
