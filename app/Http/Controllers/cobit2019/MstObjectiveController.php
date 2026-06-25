<?php

namespace App\Http\Controllers\cobit2019;

use App\Http\Controllers\Controller;
use App\Models\MstAligngoals;
use App\Models\MstAligngoalsmetr;
use App\Models\MstEntergoalsmetr;
use App\Models\MstInfoflowInput;
use App\Models\MstInfoflowOutput;
use App\Models\MstKeyCulture;
use App\Models\MstEntergoals;
use App\Models\MstGuidance;
use App\Models\MstObjective;
use App\Models\MstPolicy;
use App\Models\MstPractice;
use App\Models\MstSIA;
use App\Models\MstSkill;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class MstObjectiveController extends Controller
{
    /**
     * Centralised relation sets so we don't repeat the same arrays in multiple methods.
     */
    protected array $commonRelations = [
        'domains',
        'focusArea',
        'entergoals',
        'entergoals.entergoalsmetr',
        'aligngoals',
        'aligngoals.aligngoalsmetr',
        'practices',
        'practices.practicemetr',
        'practices.activities',
        'practices.guidances',
        'practices.roles',
        'practices.infoflowinput',
        'practices.infoflowinput.connectedoutputs',
        'policies',
        'policies.guidances',
        'skill',
        'skill.guidances',
        'keyculture',
        'keyculture.guidances',
        's_i_a',
        'practices.infoflowoutput',
    ];

    /**
     * Additional relations that are only required when rendering the detailed show view.
     */
    protected array $showExtraRelations = [
        'guidance',
    ];

    /**
     * Display a listing of objectives (JSON).
     */
    public function index()
    {
        $preferredOrderSql = "CASE WHEN UPPER(objective_id) LIKE 'EDM%' THEN 0 WHEN UPPER(objective_id) LIKE 'APO%' THEN 1 WHEN UPPER(objective_id) LIKE 'BAI%' THEN 2 WHEN UPPER(objective_id) LIKE 'DSS%' THEN 3 WHEN UPPER(objective_id) LIKE 'MEA%' THEN 4 ELSE 5 END, objective_id";

        $query = MstObjective::with($this->commonRelations);

        $focusAreaId = request()->query('focus_area', request()->query('focus_area_id'));
        if ($focusAreaId !== null && $focusAreaId !== '') {
            $query->where('focus_area_id', (int) $focusAreaId);
        }

        $objectives = $query
            ->orderByRaw($preferredOrderSql)
            ->get();

        return response()->json($objectives);
    }

    /**
     * Display the specified objective (view).
     *
     * Keeping the method signature compatible with existing routes that pass an id.
     */
    public function show($objectiveId)
    {
        $relations = array_merge($this->commonRelations, $this->showExtraRelations);

        $focusAreaId = request()->query('focus_area', 1);

        // Load objective yang sesuai dengan focus_area
        $objective = MstObjective::with($relations)
            ->where('objective_id', $objectiveId)
            ->where('focus_area_id', $focusAreaId)
            ->firstOrFail();

        $allObjectives = MstObjective::select('objective_id', 'objective')
            ->where('focus_area_id', $focusAreaId)
            ->get();

        // allow an optional ?component=... query param so the show view can preselect a component
        $component = request()->query('component', '');

        // load master goal lists for MASTER view
        $masterEnterGoals = MstEntergoals::with('entergoalsmetr')->orderBy('entergoals_id')->get();
        $masterAlignGoals = MstAligngoals::with('aligngoalsmetr')->orderBy('aligngoals_id')->get();
        // load master roles
        $masterRoles = \App\Models\MstRoles::orderBy('role_id')->get();
        // load master practices
        $masterPractices = \App\Models\MstPractice::orderBy('practice_id')->get();

        return view('cobit_component.show', compact('objective', 'allObjectives', 'component', 'masterEnterGoals', 'masterAlignGoals', 'masterRoles', 'masterPractices', 'focusAreaId'));
    }

    /**
     * Store a newly created objective.
     */
    public function store(Request $request)
    {
        $data = $this->validateCreate($request);

        $objective = MstObjective::create($data);

        return response()->json($objective, 201);
    }

    /**
     * Update the specified objective.
     */
    public function update(Request $request, $objectiveId)
    {
        $objective = MstObjective::where('objective_id', $objectiveId)->firstOrFail();

        $data = $this->validateUpdate($request);

        $objective->update($data);

        return response()->json($objective);
    }

    /**
     * Remove the specified objective.
     */
    public function destroy($objectiveId)
    {
        $objective = MstObjective::where('objective_id', $objectiveId)->firstOrFail();
        $objective->delete();

        return response()->json(null, 204);
    }

    /**
     * Show aggregated data for a given component across all objectives.
     * e.g. /cobit2019/objectives/component/skills
     */
    public function byComponent($component)
    {
        $valid = [
            'overview', 'goals', 'domains', 'practices', 'infoflows', 'organizational',
            'policies', 'skills', 'culture', 'services',
        ];

        if (! in_array($component, $valid, true)) {
            abort(404, 'Invalid component');
        }

        // eager load relations once using the centralised list and apply preferred ordering
        $preferredOrderSql = "CASE WHEN UPPER(objective_id) LIKE 'EDM%' THEN 0 WHEN UPPER(objective_id) LIKE 'APO%' THEN 1 WHEN UPPER(objective_id) LIKE 'BAI%' THEN 2 WHEN UPPER(objective_id) LIKE 'DSS%' THEN 3 WHEN UPPER(objective_id) LIKE 'MEA%' THEN 4 ELSE 5 END, objective_id";

        $objectives = MstObjective::with($this->commonRelations)
            ->orderByRaw($preferredOrderSql)
            ->get();

        $items = $objectives->map(function ($o) use ($component) {
            $payload = [
                'objective_id' => $o->objective_id,
                'objective' => $o->objective,
            ];

            switch ($component) {
                case 'overview':
                    $payload['description'] = $o->objective_description ?? '';
                    $payload['purpose'] = $o->objective_purpose ?? '';
                    $payload['domains'] = $o->domains ?? [];
                    break;

                case 'goals':
                    // include both enterprise and alignment goals for this objective
                    $payload['entergoals'] = $o->entergoals ?? [];
                    $payload['aligngoals'] = $o->aligngoals ?? [];
                    break;

                case 'domains':
                    $payload['domains'] = $o->domains ?? [];
                    break;

                case 'practices':
                case 'organizational':
                    $payload['practices'] = $o->practices ?? [];
                    break;

                case 'infoflows':
                    $payload['infoflows'] = $this->extractInfoflows($o);
                    break;

                case 'policies':
                    $payload['policies'] = $o->policies ?? [];
                    break;

                case 'skills':
                    $payload['skills'] = $o->skill ?? [];
                    break;

                case 'culture':
                    $payload['culture'] = $o->keyculture ?? [];
                    break;

                case 'services':
                    $payload['s_i_a'] = $o->s_i_a ?? [];
                    break;
            }

            return $payload;
        });

        $masterEnterGoals = \App\Models\MstEntergoals::with('entergoalsmetr')->orderBy('entergoals_id')->get();
        $masterAlignGoals = \App\Models\MstAligngoals::with('aligngoalsmetr')->orderBy('aligngoals_id')->get();
        $masterRoles = \App\Models\MstRoles::orderBy('role_id')->get();

        return view('cobit_component.show', [
            'component' => $component,
            'items' => $items,
            'masterEnterGoals' => $masterEnterGoals,
            'masterAlignGoals' => $masterAlignGoals,
            'masterRoles' => $masterRoles,
        ]);
    }

    /**
     * Render the interactive GAMO Information and RACI Flow analysis.
     */
    public function gamoAnalysis()
    {
        return view('cobit_component.gamoanalisis');
    }

    /**
     * Extract infoflows from an objective's practices in a consistent, null-safe way.
     */
    protected function extractInfoflows(MstObjective $objective)
    {
        $practices = $objective->practices ?? collect([]);

        return $practices->flatMap(function ($p) {
            $inputs = Arr::get($p, 'infoflowinput', $p->infoflowinput ?? []);

            return collect($inputs)->map(function ($inp) use ($p) {
                return [
                    'practice_id' => $p->practice_id ?? null,
                    'input' => $inp,
                    'connectedoutputs' => Arr::get($inp, 'connectedoutputs', $inp['connectedoutputs'] ?? []),
                ];
            });
        })->values();
    }

    /**
     * Update an information flow input row.
     */
    public function updateInfoflowInput(Request $request, $inputId)
    {
        $input = MstInfoflowInput::findOrFail($inputId);

        $data = $request->validate([
            'from' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $input->update($data);

        return response()->json($input->fresh());
    }

    /**
     * Create an information flow input row.
     */
    public function createInfoflowInput(Request $request)
    {
        $data = $request->validate([
            'practice_id' => 'required|string|exists:mst_practice,practice_id',
            'from' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $input = DB::transaction(function () use ($data) {
            $nextInputId = $this->nextInfoflowInputId();

            return MstInfoflowInput::create([
                'input_id' => $nextInputId,
                'practice_id' => $data['practice_id'],
                'from' => $data['from'] ?? null,
                'description' => $data['description'] ?? null,
            ]);
        });

        return response()->json($input->fresh(), 201);
    }

    /**
     * Update an information flow output row.
     */
    public function updateInfoflowOutput(Request $request, $outputId)
    {
        $output = MstInfoflowOutput::findOrFail($outputId);

        $data = $request->validate([
            'to' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $output->update($data);

        return response()->json($output->fresh());
    }

    /**
     * Create an information flow output row and optionally connect to input.
     */
    public function createInfoflowOutput(Request $request)
    {
        $data = $request->validate([
            'practice_id' => 'required|string|exists:mst_practice,practice_id',
            'input_id' => 'nullable|integer|exists:mst_infoflowinput,input_id',
            'to' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $output = DB::transaction(function () use ($data) {
            $nextOutputId = $this->nextInfoflowOutputId();

            $output = MstInfoflowOutput::create([
                'output_id' => $nextOutputId,
                'practice_id' => $data['practice_id'],
                'to' => $data['to'] ?? null,
                'description' => $data['description'] ?? null,
            ]);

            if (!empty($data['input_id'])) {
                $input = MstInfoflowInput::findOrFail($data['input_id']);
                $input->connectedoutputs()->syncWithoutDetaching([$output->output_id]);
            }

            return $output;
        });

        return response()->json($output->fresh(), 201);
    }

    protected function nextInfoflowInputId(): int
    {
        $latest = DB::table('mst_infoflowinput')
            ->select('input_id')
            ->orderByDesc('input_id')
            ->lockForUpdate()
            ->first();

        return ((int) ($latest->input_id ?? 0)) + 1;
    }

    protected function nextInfoflowOutputId(): int
    {
        $latest = DB::table('mst_infoflowoutput')
            ->select('output_id')
            ->orderByDesc('output_id')
            ->lockForUpdate()
            ->first();

        return ((int) ($latest->output_id ?? 0)) + 1;
    }

    public function createPolicy(Request $request)
    {
        $data = $request->validate([
            'objective_id' => 'required|string|exists:mst_objective,objective_id',
            'policy' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $focusAreaId = $this->focusAreaIdForObjective($data['objective_id']);

        $policy = DB::transaction(function () use ($data, $focusAreaId) {
            return MstPolicy::create([
                'policy_id' => $this->nextPolicyId(),
                'objective_id' => $data['objective_id'],
                'focus_area_id' => $focusAreaId,
                'policy' => $data['policy'] ?? null,
                'description' => $data['description'] ?? null,
            ]);
        });

        return response()->json($policy->fresh(), 201);
    }

    public function updatePolicy(Request $request, $policyId)
    {
        $data = $request->validate([
            'focus_area_id' => 'nullable|integer|exists:mst_focusarea,id',
            'policy' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $policy = MstPolicy::findOrFail($policyId);
        if (!empty($data['focus_area_id']) && (int) $policy->focus_area_id !== (int) $data['focus_area_id']) {
            abort(404);
        }

        $policy->update([
            'policy' => $data['policy'] ?? $policy->policy,
            'description' => $data['description'] ?? $policy->description,
        ]);

        return response()->json($policy->fresh());
    }

    public function createSkill(Request $request)
    {
        $data = $request->validate([
            'objective_id' => 'required|string|exists:mst_objective,objective_id',
            'skill' => 'nullable|string|max:255',
        ]);

        $data['focus_area_id'] = $this->focusAreaIdForObjective($data['objective_id']);
        $skill = MstSkill::create($data);

        return response()->json($skill->fresh(), 201);
    }

    public function updateSkill(Request $request, $skillId)
    {
        $data = $request->validate([
            'focus_area_id' => 'nullable|integer|exists:mst_focusarea,id',
            'skill' => 'nullable|string|max:255',
        ]);

        $skill = MstSkill::findOrFail($skillId);
        if (!empty($data['focus_area_id']) && (int) $skill->focus_area_id !== (int) $data['focus_area_id']) {
            abort(404);
        }

        $skill->update([
            'skill' => $data['skill'] ?? $skill->skill,
        ]);

        return response()->json($skill->fresh());
    }

    public function createKeyCulture(Request $request)
    {
        $data = $request->validate([
            'objective_id' => 'required|string|exists:mst_objective,objective_id',
            'element' => 'nullable|string',
        ]);

        $focusAreaId = $this->focusAreaIdForObjective($data['objective_id']);

        $keyCulture = DB::transaction(function () use ($data, $focusAreaId) {
            return MstKeyCulture::create([
                'keyculture_id' => $this->nextKeyCultureId(),
                'objective_id' => $data['objective_id'],
                'focus_area_id' => $focusAreaId,
                'element' => $data['element'] ?? null,
            ]);
        });

        return response()->json($keyCulture->fresh(), 201);
    }

    public function updateKeyCulture(Request $request, $keyCultureId)
    {
        $data = $request->validate([
            'focus_area_id' => 'nullable|integer|exists:mst_focusarea,id',
            'element' => 'nullable|string',
        ]);

        $keyCulture = MstKeyCulture::findOrFail($keyCultureId);
        if (!empty($data['focus_area_id']) && (int) $keyCulture->focus_area_id !== (int) $data['focus_area_id']) {
            abort(404);
        }

        $keyCulture->update([
            'element' => $data['element'] ?? $keyCulture->element,
        ]);

        return response()->json($keyCulture->fresh());
    }

    public function createSia(Request $request)
    {
        $data = $request->validate([
            'objective_id' => 'required|string|exists:mst_objective,objective_id',
            'description' => 'nullable|string',
        ]);

        $focusAreaId = $this->focusAreaIdForObjective($data['objective_id']);

        $sia = DB::transaction(function () use ($data, $focusAreaId) {
            return MstSIA::create([
                'sia_id' => $this->nextSiaId(),
                'objective_id' => $data['objective_id'],
                'focus_area_id' => $focusAreaId,
                'description' => $data['description'] ?? null,
            ]);
        });

        return response()->json($sia->fresh(), 201);
    }

    public function updateSia(Request $request, $siaId)
    {
        $data = $request->validate([
            'focus_area_id' => 'nullable|integer|exists:mst_focusarea,id',
            'description' => 'nullable|string',
        ]);

        $sia = MstSIA::findOrFail($siaId);
        if (!empty($data['focus_area_id']) && (int) $sia->focus_area_id !== (int) $data['focus_area_id']) {
            abort(404);
        }

        $sia->update([
            'description' => $data['description'] ?? $sia->description,
        ]);

        return response()->json($sia->fresh());
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

    public function createActivity(Request $request)
    {
        $data = $request->validate([
            'practice_id' => 'required|string|exists:mst_practice,practice_id',
            'description' => 'required|string',
            'capability_lvl' => 'nullable|string',
        ]);

        $activity = DB::transaction(function () use ($data) {
            $latest = DB::table('mst_activities')
                ->where('practice_id', $data['practice_id'])
                ->orderByRaw('LENGTH(activity_id) DESC')
                ->orderBy('activity_id', 'desc')
                ->lockForUpdate()
                ->first();

            if ($latest) {
                $parts = explode('.', $latest->activity_id);
                if (count($parts) > 2) {
                    $num = (int) array_pop($parts);
                    $nextNum = $num + 1;
                    $parts[] = $nextNum;
                    $nextId = implode('.', $parts);
                } else {
                    $nextId = $data['practice_id'] . '.1';
                }
            } else {
                $nextId = $data['practice_id'] . '.1';
            }

            return \App\Models\MstActivities::create([
                'activity_id' => $nextId,
                'practice_id' => $data['practice_id'],
                'description' => $data['description'],
                'capability_lvl' => $data['capability_lvl'] ?? null,
            ]);
        });

        return response()->json($activity->fresh(), 201);
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

    public function createPolicyGuidance(Request $request, $policyId)
    {
        $policy = MstPolicy::findOrFail($policyId);

        $data = $request->validate([
            'guidance' => 'nullable|string',
            'reference' => 'nullable|string',
        ]);

        $guidance = DB::transaction(function () use ($policy, $data) {
            $guidance = MstGuidance::create([
                'guidance_id' => $this->nextGuidanceId(),
                'guidance' => $data['guidance'] ?? null,
                'reference' => $data['reference'] ?? null,
            ]);

            $policy->guidances()->syncWithoutDetaching([$guidance->guidance_id]);

            return $guidance;
        });

        return response()->json($guidance->fresh(), 201);
    }

    public function createSkillGuidance(Request $request, $skillId)
    {
        $skill = MstSkill::findOrFail($skillId);

        $data = $request->validate([
            'guidance' => 'nullable|string',
            'reference' => 'nullable|string',
        ]);

        $guidance = DB::transaction(function () use ($skill, $data) {
            $guidance = MstGuidance::create([
                'guidance_id' => $this->nextGuidanceId(),
                'guidance' => $data['guidance'] ?? null,
                'reference' => $data['reference'] ?? null,
            ]);

            $skill->guidances()->syncWithoutDetaching([$guidance->guidance_id]);

            return $guidance;
        });

        return response()->json($guidance->fresh(), 201);
    }

    public function createKeyCultureGuidance(Request $request, $keyCultureId)
    {
        $keyCulture = MstKeyCulture::findOrFail($keyCultureId);

        $data = $request->validate([
            'guidance' => 'nullable|string',
            'reference' => 'nullable|string',
        ]);

        $guidance = DB::transaction(function () use ($keyCulture, $data) {
            $guidance = MstGuidance::create([
                'guidance_id' => $this->nextGuidanceId(),
                'guidance' => $data['guidance'] ?? null,
                'reference' => $data['reference'] ?? null,
            ]);

            $keyCulture->guidances()->syncWithoutDetaching([$guidance->guidance_id]);

            return $guidance;
        });

        return response()->json($guidance->fresh(), 201);
    }

    public function updateGuidance(Request $request, $guidanceId)
    {
        $guidance = MstGuidance::findOrFail($guidanceId);

        $data = $request->validate([
            'guidance' => 'nullable|string',
            'reference' => 'nullable|string',
        ]);

        $guidance->update($data);

        return response()->json($guidance->fresh());
    }

    protected function nextPolicyId(): int
    {
        $latest = DB::table('mst_policy')
            ->select('policy_id')
            ->orderByDesc('policy_id')
            ->lockForUpdate()
            ->first();

        return ((int) ($latest->policy_id ?? 0)) + 1;
    }

    protected function nextKeyCultureId(): int
    {
        $latest = DB::table('mst_keyculture')
            ->select('keyculture_id')
            ->orderByDesc('keyculture_id')
            ->lockForUpdate()
            ->first();

        return ((int) ($latest->keyculture_id ?? 0)) + 1;
    }

    protected function nextSiaId(): int
    {
        $latest = DB::table('mst_SIA')
            ->select('sia_id')
            ->orderByDesc('sia_id')
            ->lockForUpdate()
            ->first();

        return ((int) ($latest->sia_id ?? 0)) + 1;
    }

    protected function nextGuidanceId(): int
    {
        $latest = DB::table('mst_guidance')
            ->select('guidance_id')
            ->orderByDesc('guidance_id')
            ->lockForUpdate()
            ->first();

        return ((int) ($latest->guidance_id ?? 0)) + 1;
    }

    /**
     * Resolve the focus area for a given objective id.
     */
    protected function focusAreaIdForObjective(string $objectiveId): int
    {
        return (int) (MstObjective::where('objective_id', $objectiveId)->value('focus_area_id') ?? 1);
    }

    /**
     * Validation rules for creating an objective.
     */
    protected function validateCreate(Request $request): array
    {
        return $request->validate([
            'objective_id' => 'required|string|unique:mst_objective',
            'objective' => 'required|string',
            'objective_description' => 'nullable|string',
            'objective_purpose' => 'nullable|string',
        ]);
    }

    /**
     * Validation rules for updating an objective.
     */
    protected function validateUpdate(Request $request): array
    {
        return $request->validate([
            'objective' => 'sometimes|required|string',
            'objective_description' => 'sometimes|nullable|string',
            'objective_purpose' => 'sometimes|nullable|string',
        ]);
    }

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

    /**
     * Get the COBIT RACI matrix of roles and practices.
     * Accessible by other apps.
     */
    public function getRolesMatrix(Request $request)
    {
        $objectiveId = $request->query('objective_id');

        // Query practices with their objective and roles
        $query = \App\Models\MstPractice::with(['objective', 'roles']);

        if ($objectiveId) {
            $query->where('objective_id', $objectiveId);
        }

        $preferredOrderSql = "CASE WHEN UPPER(objective_id) LIKE 'EDM%' THEN 0 WHEN UPPER(objective_id) LIKE 'APO%' THEN 1 WHEN UPPER(objective_id) LIKE 'BAI%' THEN 2 WHEN UPPER(objective_id) LIKE 'DSS%' THEN 3 WHEN UPPER(objective_id) LIKE 'MEA%' THEN 4 ELSE 5 END, objective_id, practice_id";
        $practices = $query->orderByRaw($preferredOrderSql)->get();

        // Fetch all master roles in correct order
        $roles = \App\Models\MstRoles::orderBy('role_id')->get();

        // Transform the data for easy ingestion by external apps
        $matrix = $practices->map(function ($practice) {
            // Map roles of this practice into an associative array role_id => r_a
            $mappedRoles = [];
            foreach ($practice->roles as $role) {
                $mappedRoles[$role->role_id] = $role->pivot->r_a;
            }

            return [
                'practice_id' => $practice->practice_id,
                'practice_name' => $practice->practice_name,
                'practice_description' => $practice->practice_description,
                'objective_id' => $practice->objective_id,
                'objective_name' => $practice->objective?->objective,
                'role_assignments' => $mappedRoles,
            ];
        });

        return response()->json([
            'success' => true,
            'roles' => $roles->map(function ($role) {
                return [
                    'role_id' => $role->role_id,
                    'role_name' => $role->role,
                    'description' => $role->description,
                ];
            }),
            'matrix' => $matrix,
        ], 200, [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Content-Type, X-Requested-With, Authorization',
            'Access-Control-Allow-Methods' => 'GET, OPTIONS'
        ]);
    }

    /**
     * Get the COBIT GAMO Information Flow analysis data.
     * Accessible by other apps (public API, no auth).
     *
     * Returns per-practice information flows: inputs, connected outputs, and RACI roles.
     * Supports optional filtering via query parameters:
     *   - objective_id: filter by a specific objective (e.g. ?objective_id=EDM01)
     *   - domain:       filter by domain prefix (e.g. ?domain=APO)
     *
     * Response structure:
     * {
     *   "success": true,
     *   "objectives": [
     *     {
     *       "objective_id": "EDM01",
     *       "objective": "Ensured Governance Framework Setting and Maintenance",
     *       "objective_description": "...",
     *       "practices": [
     *         {
     *           "practice_id": "EDM01.01",
     *           "practice_name": "...",
     *           "practice_description": "...",
     *           "inputs": [ { "input_id", "from", "description" } ],
     *           "outputs": [ { "output_id", "to", "description" } ],
     *           "role_assignments": { role_id: "R"|"A"|"C"|"I" }
     *         }
     *       ]
     *     }
     *   ],
     *   "roles": [ { "role_id", "role_name", "description" } ]
     * }
     */
    public function getGamoInfoflow(Request $request)
    {
        $objectiveId = $request->query('objective_id');
        $domain = $request->query('domain');

        $preferredOrderSql = "CASE WHEN UPPER(objective_id) LIKE 'EDM%' THEN 0 WHEN UPPER(objective_id) LIKE 'APO%' THEN 1 WHEN UPPER(objective_id) LIKE 'BAI%' THEN 2 WHEN UPPER(objective_id) LIKE 'DSS%' THEN 3 WHEN UPPER(objective_id) LIKE 'MEA%' THEN 4 ELSE 5 END, objective_id";

        $query = MstObjective::with([
            'practices.infoflowinput.connectedoutputs',
            'practices.infoflowoutput',
            'practices.roles',
        ]);

        if ($objectiveId) {
            $query->where('objective_id', $objectiveId);
        } elseif ($domain) {
            $query->where('objective_id', 'LIKE', strtoupper($domain) . '%');
        }

        $objectives = $query->orderByRaw($preferredOrderSql)->get();

        // Fetch all master roles for reference
        $roles = \App\Models\MstRoles::orderBy('role_id')->get();

        // Transform objectives into structured API response
        $result = $objectives->map(function ($obj) {
            $practices = ($obj->practices ?? collect([]))->sortBy('practice_id')->values();

            return [
                'objective_id' => $obj->objective_id,
                'objective' => $obj->objective,
                'objective_description' => $obj->objective_description,
                'practices' => $practices->map(function ($practice) {
                    // Collect inputs
                    $inputs = ($practice->infoflowinput ?? collect([]))->map(function ($inp) {
                        return [
                            'input_id' => $inp->input_id,
                            'from' => $inp->from,
                            'description' => $inp->description,
                        ];
                    })->values();

                    // Collect outputs: merge connected outputs (via pivot) + direct practice outputs
                    $outputsMap = [];
                    foreach ($practice->infoflowinput ?? [] as $inp) {
                        foreach ($inp->connectedoutputs ?? [] as $out) {
                            $outputsMap[$out->output_id] = [
                                'output_id' => $out->output_id,
                                'to' => $out->to,
                                'description' => $out->description,
                            ];
                        }
                    }
                    foreach ($practice->infoflowoutput ?? [] as $out) {
                        if (! isset($outputsMap[$out->output_id])) {
                            $outputsMap[$out->output_id] = [
                                'output_id' => $out->output_id,
                                'to' => $out->to,
                                'description' => $out->description,
                            ];
                        }
                    }

                    // Collect RACI role assignments
                    $roleAssignments = [];
                    foreach ($practice->roles ?? [] as $role) {
                        $raci = strtoupper($role->pivot->r_a ?? '');
                        if ($raci && $raci !== '-') {
                            $roleAssignments[$role->role_id] = [
                                'role_name' => $role->role,
                                'raci' => $raci,
                            ];
                        }
                    }

                    return [
                        'practice_id' => $practice->practice_id,
                        'practice_name' => $practice->practice_name,
                        'practice_description' => $practice->practice_description,
                        'inputs' => $inputs,
                        'outputs' => array_values($outputsMap),
                        'role_assignments' => $roleAssignments,
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'objectives' => $result,
            'roles' => $roles->map(function ($role) {
                return [
                    'role_id' => $role->role_id,
                    'role_name' => $role->role,
                    'description' => $role->description,
                ];
            }),
        ], 200, [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Content-Type, X-Requested-With, Authorization',
            'Access-Control-Allow-Methods' => 'GET, OPTIONS',
        ]);
    }

    public function deleteInfoflowInput($inputId)
    {
        $input = MstInfoflowInput::findOrFail($inputId);
        DB::transaction(function () use ($input) {
            $input->connectedoutputs()->detach();
            $input->delete();
        });
        return response()->json(['success' => true]);
    }

    public function deleteInfoflowOutput($outputId)
    {
        $output = MstInfoflowOutput::findOrFail($outputId);
        DB::transaction(function () use ($output) {
            DB::table('trs_infoflowio')->where('output_id', $output->output_id)->delete();
            $output->delete();
        });
        return response()->json(['success' => true]);
    }

    /**
     * Get API response headers for CORS.
     */
    protected function apiResponseHeaders(): array
    {
        return [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Content-Type, X-Requested-With, Authorization',
            'Access-Control-Allow-Methods' => 'GET, OPTIONS',
        ];
    }

    /**
     * Get a list of all available COBIT components metadata.
     */
    public function getComponentsList(Request $request)
    {
        $components = [
            [
                'name' => 'overview',
                'description' => 'Expose overview details (description, purpose, domain mapping) for COBIT objectives.',
                'endpoint' => '/api/cobit/components/overview'
            ],
            [
                'name' => 'goals',
                'description' => 'Expose enterprise and alignment goals mapping for COBIT objectives.',
                'endpoint' => '/api/cobit/components/goals'
            ],
            [
                'name' => 'domains',
                'description' => 'Expose domains for COBIT objectives.',
                'endpoint' => '/api/cobit/components/domains'
            ],
            [
                'name' => 'practices',
                'description' => 'Expose practices, metrics, activities, and references.',
                'endpoint' => '/api/cobit/components/practices'
            ],
            [
                'name' => 'organizational',
                'description' => 'Expose organizational structures and roles matrix.',
                'endpoint' => '/api/cobit/components/organizational'
            ],
            [
                'name' => 'infoflows',
                'description' => 'Expose input and output information flows.',
                'endpoint' => '/api/cobit/components/infoflows'
            ],
            [
                'name' => 'policies',
                'description' => 'Expose policies and frameworks.',
                'endpoint' => '/api/cobit/components/policies'
            ],
            [
                'name' => 'skills',
                'description' => 'Expose skills and competencies.',
                'endpoint' => '/api/cobit/components/skills'
            ],
            [
                'name' => 'culture',
                'description' => 'Expose culture and behavioral elements.',
                'endpoint' => '/api/cobit/components/culture'
            ],
            [
                'name' => 'services',
                'description' => 'Expose services, infrastructure, and application (SIA) details.',
                'endpoint' => '/api/cobit/components/services'
            ]
        ];

        return response()->json([
            'success' => true,
            'components' => $components
        ], 200, $this->apiResponseHeaders());
    }

    /**
     * Standardized API endpoint to retrieve COBIT component details.
     */
    public function getComponentApi(Request $request, $component = null)
    {
        $component = strtolower($component ?? '');

        // Normalize aliases
        if ($component === 'processes') {
            $component = 'practices';
        } elseif ($component === 'information-flows') {
            $component = 'infoflows';
        }

        $valid = [
            'overview', 'goals', 'domains', 'practices', 'infoflows', 'organizational',
            'policies', 'skills', 'culture', 'services',
        ];

        if (!in_array($component, $valid, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid component. Valid components are: ' . implode(', ', $valid)
            ], 404, $this->apiResponseHeaders());
        }

        $objectiveId = $request->query('objective_id');
        $domain = $request->query('domain');

        $relations = $this->getRelationsForComponent($component);
        $query = MstObjective::with($relations);

        if ($objectiveId) {
            $query->where('objective_id', $objectiveId);
        } elseif ($domain) {
            $query->where('objective_id', 'LIKE', strtoupper($domain) . '%');
        }

        $preferredOrderSql = "CASE WHEN UPPER(objective_id) LIKE 'EDM%' THEN 0 WHEN UPPER(objective_id) LIKE 'APO%' THEN 1 WHEN UPPER(objective_id) LIKE 'BAI%' THEN 2 WHEN UPPER(objective_id) LIKE 'DSS%' THEN 3 WHEN UPPER(objective_id) LIKE 'MEA%' THEN 4 ELSE 5 END, objective_id";
        $objectives = $query->orderByRaw($preferredOrderSql)->get();

        $data = $objectives->map(function ($o) use ($component) {
            $payload = [
                'objective_id' => $o->objective_id,
                'objective' => $o->objective,
            ];

            switch ($component) {
                case 'overview':
                    $payload['description'] = $o->objective_description ?? '';
                    $payload['purpose'] = $o->objective_purpose ?? '';
                    $payload['domains'] = $o->domains->map(function ($d) {
                        return [
                            'area' => $d->area,
                            'domain' => $d->pivot->domain ?? null,
                        ];
                    });
                    break;

                case 'goals':
                    $payload['entergoals'] = $o->entergoals->map(function ($eg) {
                        return [
                            'entergoals_id' => $eg->entergoals_id,
                            'description' => $eg->description,
                            'metrics' => $eg->entergoalsmetr->map(function ($m) {
                                return [
                                    'entergoalsmetr_id' => $m->entergoalsmetr_id,
                                    'description' => $m->description,
                                ];
                            }),
                        ];
                    });
                    $payload['aligngoals'] = $o->aligngoals->map(function ($ag) {
                        return [
                            'aligngoals_id' => $ag->aligngoals_id,
                            'description' => $ag->description,
                            'metrics' => $ag->aligngoalsmetr->map(function ($m) {
                                return [
                                    'aligngoalsmetr_id' => $m->aligngoalsmetr_id,
                                    'description' => $m->description,
                                ];
                            }),
                        ];
                    });
                    break;

                case 'domains':
                    $payload['domains'] = $o->domains->map(function ($d) {
                        return [
                            'area' => $d->area,
                            'domain' => $d->pivot->domain ?? null,
                        ];
                    });
                    break;

                case 'practices':
                    $payload['practices'] = $o->practices->map(function ($p) {
                        return [
                            'practice_id' => $p->practice_id,
                            'practice_name' => $p->practice_name,
                            'practice_description' => $p->practice_description,
                            'metrics' => $p->practicemetr->map(function ($m) {
                                return [
                                    'id' => $m->id,
                                    'description' => $m->description,
                                ];
                            }),
                            'activities' => $p->activities->map(function ($a) {
                                return [
                                    'activity_id' => $a->activity_id,
                                    'capability_lvl' => $a->capability_lvl,
                                    'description' => $a->description,
                                ];
                            }),
                            'guidances' => $p->guidances->map(function ($g) {
                                return [
                                    'guidance_id' => $g->guidance_id,
                                    'guidance' => $g->guidance,
                                    'reference' => $g->reference,
                                ];
                            }),
                            'roles' => $p->roles->map(function ($r) {
                                return [
                                    'role_id' => $r->role_id,
                                    'role_name' => $r->role,
                                    'description' => $r->description,
                                    'raci' => strtoupper($r->pivot->r_a ?? ''),
                                ];
                            }),
                        ];
                    });
                    break;

                case 'organizational':
                    $payload['practices'] = $o->practices->map(function ($p) {
                        $roleAssignments = [];
                        foreach ($p->roles ?? [] as $role) {
                            $raci = strtoupper($role->pivot->r_a ?? '');
                            if ($raci && $raci !== '-') {
                                $roleAssignments[$role->role_id] = [
                                    'role_name' => $role->role,
                                    'raci' => $raci,
                                ];
                            }
                        }
                        return [
                            'practice_id' => $p->practice_id,
                            'practice_name' => $p->practice_name,
                            'practice_description' => $p->practice_description,
                            'role_assignments' => $roleAssignments,
                        ];
                    });
                    break;

                case 'infoflows':
                    $payload['infoflows'] = $this->extractInfoflows($o);
                    break;

                case 'policies':
                    $payload['policies'] = $o->policies->map(function ($pol) {
                        return [
                            'policy_id' => $pol->policy_id,
                            'policy' => $pol->policy,
                            'description' => $pol->description,
                            'guidances' => $pol->guidances->map(function ($g) {
                                return [
                                    'guidance_id' => $g->guidance_id,
                                    'guidance' => $g->guidance,
                                    'reference' => $g->reference,
                                ];
                            }),
                        ];
                    });
                    break;

                case 'skills':
                    $payload['skills'] = $o->skill->map(function ($sk) {
                        return [
                            'skill_id' => $sk->skill_id,
                            'skill' => $sk->skill,
                            'guidances' => $sk->guidances->map(function ($g) {
                                return [
                                    'guidance_id' => $g->guidance_id,
                                    'guidance' => $g->guidance,
                                    'reference' => $g->reference,
                                ];
                            }),
                        ];
                    });
                    break;

                case 'culture':
                    $payload['culture'] = $o->keyculture->map(function ($cul) {
                        return [
                            'keyculture_id' => $cul->keyculture_id,
                            'element' => $cul->element,
                            'guidances' => $cul->guidances->map(function ($g) {
                                return [
                                    'guidance_id' => $g->guidance_id,
                                    'guidance' => $g->guidance,
                                    'reference' => $g->reference,
                                ];
                            }),
                        ];
                    });
                    break;

                case 'services':
                    $payload['s_i_a'] = $o->s_i_a->map(function ($sia) {
                        return [
                            'sia_id' => $sia->sia_id,
                            'description' => $sia->description,
                        ];
                    });
                    break;
            }

            return $payload;
        });

        return response()->json([
            'success' => true,
            'component' => $component,
            'data' => $data
        ], 200, $this->apiResponseHeaders());
    }

    /**
     * Get specific eager load relations for a given component.
     */
    protected function getRelationsForComponent(string $component): array
    {
        switch ($component) {
            case 'overview':
                return ['domains'];
            case 'goals':
                return ['entergoals.entergoalsmetr', 'aligngoals.aligngoalsmetr'];
            case 'domains':
                return ['domains'];
            case 'practices':
                return [
                    'practices.practicemetr',
                    'practices.activities',
                    'practices.guidances',
                    'practices.roles',
                ];
            case 'organizational':
                return ['practices.roles'];
            case 'infoflows':
                return [
                    'practices.infoflowinput.connectedoutputs',
                    'practices.infoflowoutput',
                ];
            case 'policies':
                return ['policies.guidances'];
            case 'skills':
                return ['skill.guidances'];
            case 'culture':
                return ['keyculture.guidances'];
            case 'services':
                return ['s_i_a'];
            default:
                return [];
        }
    }

    // Wrap API methods for clear routing aliases
    public function getOverviewApi(Request $request) { return $this->getComponentApi($request, 'overview'); }
    public function getGoalsApi(Request $request) { return $this->getComponentApi($request, 'goals'); }
    public function getDomainsApi(Request $request) { return $this->getComponentApi($request, 'domains'); }
    public function getPracticesApi(Request $request) { return $this->getComponentApi($request, 'practices'); }
    public function getOrganizationalApi(Request $request) { return $this->getComponentApi($request, 'organizational'); }
    public function getInfoflowsApi(Request $request) { return $this->getComponentApi($request, 'infoflows'); }
    public function getPoliciesApi(Request $request) { return $this->getComponentApi($request, 'policies'); }
    public function getSkillsApi(Request $request) { return $this->getComponentApi($request, 'skills'); }
    public function getCultureApi(Request $request) { return $this->getComponentApi($request, 'culture'); }
    public function getServicesApi(Request $request) { return $this->getComponentApi($request, 'services'); }
}
