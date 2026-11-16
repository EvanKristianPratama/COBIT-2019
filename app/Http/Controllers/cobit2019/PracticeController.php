<?php

namespace App\Http\Controllers\cobit2019;

use App\Http\Controllers\cobit2019\Concerns\ResolvesObjectiveFocusArea;
use App\Http\Controllers\Controller;
use App\Models\MstPractice;
use App\Models\MstPracticeMetr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PracticeController extends Controller
{
    use ResolvesObjectiveFocusArea;

    /**
     * Get all practices for dropdown selection in infoflow.
     * Returns practices grouped by their objective for better organization.
     */
    public function getPracticesList()
    {
        $preferredOrderSql = "CASE WHEN UPPER(o.objective_id) LIKE 'EDM%' THEN 0 WHEN UPPER(o.objective_id) LIKE 'APO%' THEN 1 WHEN UPPER(o.objective_id) LIKE 'BAI%' THEN 2 WHEN UPPER(o.objective_id) LIKE 'DSS%' THEN 3 WHEN UPPER(o.objective_id) LIKE 'MEA%' THEN 4 ELSE 5 END, o.objective_id, p.practice_id";

        $focusAreaId = request()->query('focus_area', request()->query('focus_area_id'));

        $query = DB::table('mst_practice as p')
            ->join('mst_objective as o', 'p.objective_id', '=', 'o.objective_id')
            ->select('p.practice_id', 'p.practice_name', 'p.practice_description', 'p.objective_id', 'o.objective')
            ->orderByRaw($preferredOrderSql);

        if ($focusAreaId !== null && $focusAreaId !== '') {
            $query->where('o.focus_area_id', (int) $focusAreaId);
        }

        $practices = $query->get();

        return response()->json([
            'success' => true,
            'practices' => $practices->map(function ($practice) {
                return [
                    'practice_id' => $practice->practice_id,
                    'practice_name' => $practice->practice_name,
                    'practice_description' => $practice->practice_description,
                    'objective_id' => $practice->objective_id,
                    'objective_name' => $practice->objective,
                    'label' => "{$practice->practice_id} - {$practice->practice_name}",
                ];
            }),
        ]);
    }

    public function createPractice(Request $request)
    {
        $data = $request->validate([
            'objective_id' => 'required|string|exists:mst_objective,objective_id',
            'practice_name' => 'required|string|max:255',
            'practice_description' => 'nullable|string',
        ]);

        $focusAreaId = $this->focusAreaIdForObjective($data['objective_id']);

        $practice = DB::transaction(function () use ($data, $focusAreaId) {
            $latest = DB::table('mst_practice')
                ->where('objective_id', $data['objective_id'])
                ->orderByRaw('LENGTH(practice_id) DESC')
                ->orderBy('practice_id', 'desc')
                ->lockForUpdate()
                ->first();

            if ($latest) {
                $parts = explode('.', $latest->practice_id);
                if (count($parts) > 1) {
                    $num = (int) array_pop($parts);
                    $nextNum = str_pad($num + 1, 2, '0', STR_PAD_LEFT);
                    $parts[] = $nextNum;
                    $nextId = implode('.', $parts);
                } else {
                    $nextId = $data['objective_id'] . '.01';
                }
            } else {
                $nextId = $data['objective_id'] . '.01';
            }

            return \App\Models\MstPractice::create([
                'practice_id' => $nextId,
                'objective_id' => $data['objective_id'],
                'focus_area_id' => $focusAreaId,
                'practice_name' => $data['practice_name'],
                'practice_description' => $data['practice_description'] ?? null,
            ]);
        });

        return response()->json($practice->fresh(), 201);
    }

    public function updatePractice(Request $request, $practiceId)
    {
        $data = $request->validate([
            'focus_area_id' => 'nullable|integer|exists:mst_focusarea,id',
            'practice_name' => 'nullable|string|max:255',
            'practice_description' => 'nullable|string',
        ]);

        $practice = MstPractice::findOrFail($practiceId);
        if (!empty($data['focus_area_id']) && (int) $practice->focus_area_id !== (int) $data['focus_area_id']) {
            abort(404);
        }

        $practice->update([
            'practice_name' => $data['practice_name'] ?? $practice->practice_name,
            'practice_description' => $data['practice_description'] ?? $practice->practice_description,
        ]);

        return response()->json($practice->fresh());
    }

    public function destroyPractice(Request $request, $practiceId)
    {
        try {
            DB::beginTransaction();

            $practice = MstPractice::findOrFail($practiceId);

            // Cascade delete related entities manually to ensure safety
            $practice->activities()->delete();
            $practice->practicemetr()->delete();
            $practice->infoflowinput()->delete();
            $practice->infoflowoutput()->delete();
            $practice->roles()->detach();
            $practice->guidances()->detach();

            $practice->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Practice deleted successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting practice: ' . $e->getMessage()
            ], 500);
        }
    }

    public function createPracticeMetric(Request $request, $practiceId)
    {
        try {
            $data = $request->validate([
                'description' => 'required|string',
            ]);

            $practice = MstPractice::where('practice_id', $practiceId)->firstOrFail();

            $metric = MstPracticeMetr::create([
                'practice_id' => $practice->practice_id,
                'description' => $data['description'],
            ]);

            return response()->json($metric->fresh(), 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error on server: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine()
            ], 500);
        }
    }

    public function updatePracticeMetric(Request $request, $metricId)
    {
        try {
            $data = $request->validate([
                'description' => 'required|string',
            ]);

            $metric = MstPracticeMetr::findOrFail($metricId);
            $metric->update([
                'description' => $data['description'],
            ]);

            return response()->json($metric->fresh());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating metric: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroyPracticeMetric($metricId)
    {
        try {
            $metric = MstPracticeMetr::findOrFail($metricId);
            $metric->delete();

            return response()->json([
                'success' => true,
                'message' => 'Example metric deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting example metric: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updatePracticeRole(Request $request, $practiceId, $roleId)
    {
        $data = $request->validate([
            'focus_area_id' => 'nullable|integer|exists:mst_focusarea,id',
            'r_a' => 'nullable|string|in:R,A,C,I,-',
        ]);

        $practice = MstPractice::findOrFail($practiceId);
        if (!empty($data['focus_area_id']) && (int) $practice->focus_area_id !== (int) $data['focus_area_id']) {
            abort(404);
        }

        $raci = strtoupper(trim((string) ($data['r_a'] ?? '-')));
        DB::table('trs_practroles')->updateOrInsert(
            [
                'practice_id' => $practiceId,
                'role_id' => $roleId,
            ],
            [
                'r_a' => $raci,
            ]
        );

        return response()->json([
            'success' => true,
            'practice_id' => $practiceId,
            'role_id' => $roleId,
            'r_a' => $raci,
        ]);
    }

    public function destroyObjectiveRole(Request $request, $objectiveId, $roleId)
    {
        $objective = \App\Models\MstObjective::findOrFail($objectiveId);
        $practiceIds = \App\Models\MstPractice::where('objective_id', $objectiveId)->pluck('practice_id');

        DB::table('trs_practroles')
            ->whereIn('practice_id', $practiceIds)
            ->where('role_id', $roleId)
            ->delete();

        return response()->json(['success' => true]);
    }

    public function updateMasterRole(Request $request, $roleId)
    {
        $data = $request->validate([
            'role' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $role = \App\Models\MstRoles::findOrFail($roleId);
        $role->update([
            'role' => $data['role'],
            'description' => $data['description'] ?? $role->description,
        ]);

        return response()->json($role->fresh());
    }

    public function createActivity(Request $request)
    {
        try {
            $data = $request->validate([
                'practice_id' => 'required|string|exists:mst_practice,practice_id',
                'description' => 'required|string',
                'capability_lvl' => 'nullable|string',
            ]);

            $activity = \App\Models\MstActivities::create([
                'practice_id' => $data['practice_id'],
                'description' => $data['description'],
                'capability_lvl' => $data['capability_lvl'] ?? null,
            ]);

            return response()->json($activity->fresh(), 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error on server: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine()
            ], 500);
        }
    }

    public function updateActivity(Request $request, $activityId)
    {
        $data = $request->validate([
            'focus_area_id' => 'nullable|integer|exists:mst_focusarea,id',
            'description' => 'required|string',
            'capability_lvl' => 'nullable|string',
        ]);

        $activity = \App\Models\MstActivities::findOrFail($activityId);
        
        $activity->update([
            'description' => $data['description'],
            'capability_lvl' => $data['capability_lvl'] ?? $activity->capability_lvl,
        ]);

        return response()->json($activity->fresh());
    }

    public function destroyActivity(Request $request, $activityId)
    {
        try {
            $activity = \App\Models\MstActivities::findOrFail($activityId);
            $activity->evaluations()->delete(); // cascade delete evaluations if any
            $activity->delete();

            return response()->json([
                'success' => true,
                'message' => 'Activity deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting activity: ' . $e->getMessage()
            ], 500);
        }
    }
}
