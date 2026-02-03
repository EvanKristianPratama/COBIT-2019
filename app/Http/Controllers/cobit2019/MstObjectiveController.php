<?php

namespace App\Http\Controllers\cobit2019;

use App\Http\Controllers\Controller;
use App\Models\MstAligngoals;
use App\Models\MstEntergoals;
use App\Models\MstObjective;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class MstObjectiveController extends Controller
{
    /**
     * Centralised relation sets so we don't repeat the same arrays in multiple methods.
     */
    protected array $commonRelations = [
        'domains',
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
    ];

    /**
     * Additional relations that are only required when rendering the detailed show view.
     */
    protected array $showExtraRelations = [
        'practices.infoflowoutput',
        'guidance',
    ];

    /**
     * Display the main COBIT Dictionary wrapper (Vue/Inertia).
     */
    public function index()
    {
        // load master goal lists for MASTER view
        $masterEnterGoals = MstEntergoals::with('entergoalsmetr')->orderBy('entergoals_id')->get();
        $masterAlignGoals = MstAligngoals::with('aligngoalsmetr')->orderBy('aligngoals_id')->get();
        // load master roles
        $masterRoles = \App\Models\MstRoles::orderBy('role_id')->get();

        return \Inertia\Inertia::render('CobitComponents/Index', [
            'masterEnterGoals' => $masterEnterGoals,
            'masterAlignGoals' => $masterAlignGoals,
            'masterRoles' => $masterRoles,
            'objectives' => \App\Models\MstObjective::select('objective_id', 'objective', 'objective_description')
                ->orderByRaw("CASE WHEN UPPER(objective_id) LIKE 'EDM%' THEN 0 WHEN UPPER(objective_id) LIKE 'APO%' THEN 1 WHEN UPPER(objective_id) LIKE 'BAI%' THEN 2 WHEN UPPER(objective_id) LIKE 'DSS%' THEN 3 WHEN UPPER(objective_id) LIKE 'MEA%' THEN 4 ELSE 5 END, objective_id")
                ->get()
        ]);
    }

    /**
     * Display a listing of objectives (JSON API).
     */
    public function apiIndex()
    {
        $preferredOrderSql = "CASE WHEN UPPER(objective_id) LIKE 'EDM%' THEN 0 WHEN UPPER(objective_id) LIKE 'APO%' THEN 1 WHEN UPPER(objective_id) LIKE 'BAI%' THEN 2 WHEN UPPER(objective_id) LIKE 'DSS%' THEN 3 WHEN UPPER(objective_id) LIKE 'MEA%' THEN 4 ELSE 5 END, objective_id";

        $objectives = MstObjective::with($this->commonRelations)
            ->orderByRaw($preferredOrderSql)
            ->get();

        return response()->json($objectives);
    }

    /**
     * Display the specified objective (view).
     *
     * Keeping the method signature compatible with existing routes that pass an id.
     */
    public function show($id)
    {
        $relations = array_merge($this->commonRelations, $this->showExtraRelations);
        $objective = MstObjective::with($relations)->findOrFail($id);
        $masterRoles = \App\Models\MstRoles::orderBy('role_id')->get();

        return \Inertia\Inertia::render('CobitComponents/Partials/ViewByGamo/GamoDetail', [
            'objective' => $objective,
            'masterRoles' => $masterRoles,
        ]);
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
    public function update(Request $request, $id)
    {
        $objective = MstObjective::findOrFail($id);

        $data = $this->validateUpdate($request);

        $objective->update($data);

        return response()->json($objective);
    }

    /**
     * Remove the specified objective.
     */
    public function destroy($id)
    {
        $objective = MstObjective::findOrFail($id);
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
                    $payload['practices'] = $o->practices ?? [];
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

        // Create Master Data loader helper to avoid duplication (inline here for now or refactor later)
        $masterEnterGoals = MstEntergoals::with('entergoalsmetr')->orderBy('entergoals_id')->get();
        $masterAlignGoals = MstAligngoals::with('aligngoalsmetr')->orderBy('aligngoals_id')->get();
        $masterRoles = \App\Models\MstRoles::orderBy('role_id')->get();

        return \Inertia\Inertia::render('CobitComponents/Index', [
            'initialTab' => 'component',
            'selectedComponent' => $component,
            'componentData' => $items,
             // We also need basic objectives list for the GAMO tab if user switches
            'objectives' => MstObjective::select('objective_id', 'objective', 'objective_description')->orderByRaw($preferredOrderSql)->get(),
            'masterEnterGoals' => $masterEnterGoals,
            'masterAlignGoals' => $masterAlignGoals,
            'masterRoles' => $masterRoles,
        ]);
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
}
