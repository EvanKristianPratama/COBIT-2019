<?php

namespace App\Http\Controllers\cobit2019;

use App\Http\Controllers\cobit2019\Concerns\ExtractsInfoflows;
use App\Http\Controllers\Controller;
use App\Models\MstAligngoals;
use App\Models\MstEntergoals;
use App\Models\MstObjective;
use Illuminate\Http\Request;

class MstObjectiveController extends Controller
{
    use ExtractsInfoflows;

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

        $focusAreaId = request()->query('focus_area');
        if (! $focusAreaId) {
            $matchingObj = MstObjective::where('objective_id', $objectiveId)->first();
            if ($matchingObj) {
                $focusAreaId = $matchingObj->focus_area_id;
            } else {
                $normalizedCode = preg_replace('/^([A-Za-z]+)0*(\d+)/', '$1$2', strtoupper($objectiveId));
                $c4Proc = \App\Models\Cobit4\Cobit4Process::where('code', $normalizedCode)
                    ->orWhere('code', strtoupper($objectiveId))
                    ->orWhere('objective_id', $objectiveId)
                    ->first();
                $focusAreaId = $c4Proc ? $c4Proc->focus_area_id : 1;
            }
        }

        // Load objective yang sesuai dengan focus_area
        $objective = MstObjective::with($relations)
            ->where('objective_id', $objectiveId)
            ->where('focus_area_id', $focusAreaId)
            ->first();

        // Fallback resolution for COBIT 4 process codes (e.g. PO1, PO01, PO01.M27)
        if (! $objective) {
            $normalizedCode = preg_replace('/^([A-Za-z]+)0*(\d+)/', '$1$2', strtoupper($objectiveId));
            $c4Proc = \App\Models\Cobit4\Cobit4Process::where('focus_area_id', $focusAreaId)
                ->where(function($q) use ($objectiveId, $normalizedCode) {
                    $q->where('code', $normalizedCode)
                      ->orWhere('code', strtoupper($objectiveId))
                      ->orWhere('objective_id', $objectiveId);
                })->first();

            if ($c4Proc) {
                if ($c4Proc->objective_id) {
                    $objective = MstObjective::with($relations)
                        ->where('objective_id', $c4Proc->objective_id)
                        ->where('focus_area_id', $focusAreaId)
                        ->first();
                }

                if (! $objective) {
                    $resolvedObjId = $c4Proc->objective_id ?: "{$c4Proc->code}.M{$focusAreaId}";
                    $objective = MstObjective::firstOrCreate(
                        [
                            'objective_id' => $resolvedObjId,
                            'focus_area_id' => $focusAreaId,
                        ],
                        [
                            'objective' => $c4Proc->title,
                            'description' => $c4Proc->description,
                        ]
                    );
                    $c4Proc->update(['objective_id' => $resolvedObjId]);
                    $objective->load($relations);
                }
            } else {
                $objective = MstObjective::with($relations)
                    ->where('focus_area_id', $focusAreaId)
                    ->where(function($q) use ($objectiveId, $normalizedCode) {
                        $q->where('objective_id', 'like', "{$normalizedCode}%")
                          ->orWhere('objective_id', 'like', strtoupper($objectiveId) . '%');
                    })
                    ->first();
            }
        }

        if (! $objective) {
            abort(404, "Objective [{$objectiveId}] tidak ditemukan.");
        }

        $allObjectives = MstObjective::select('objective_id', 'objective')
            ->where('focus_area_id', $focusAreaId)
            ->get();

        // load all focus areas / models for model switcher
        $allFocusAreas = \App\Models\MstFocusArea::orderBy('version', 'desc')->orderBy('id')->get();

        // Check if viewing COBIT 4.1 framework
        $curFa = \App\Models\MstFocusArea::find($focusAreaId);
        $isCobit4 = ($curFa && $curFa->version === '4.1')
            || ($focusAreaId == 27)
            || ($objective->focusArea && $objective->focusArea->version === '4.1');

        if ($isCobit4) {
            $cobit4Svc = app(\App\Services\Cobit4\Cobit4Service::class);
            $processCode = $cobit4Svc->resolveProcessCode($objective->objective_id);
            $cobit4Data = $cobit4Svc->getProcessData($processCode, $objective, (int)$focusAreaId);
            $domains = $cobit4Svc->getDomains();
            $allCobit4Processes = $cobit4Svc->getAllProcesses((int)$focusAreaId);
            $workProductService = app(\App\Services\Cobit4\Cobit4WorkProductService::class);
            $workProducts = $workProductService->forProcess($processCode, (int) $focusAreaId);
            $workProductsReady = $workProductService->available();
            $workProductsRevision = $workProductService->revision($workProducts);

            return view('cobit_component.cobit4_1.show', compact(
                'objective',
                'allObjectives',
                'allCobit4Processes',
                'focusAreaId',
                'allFocusAreas',
                'curFa',
                'cobit4Data',
                'workProducts',
                'workProductsReady',
                'workProductsRevision',
                'domains',
                'processCode'
            ));
        }

        // allow an optional ?component=... query param so the show view can preselect a component
        if ((string) $curFa?->version === '5') {
            return app(\App\Http\Controllers\Cobit5\ComponentController::class)->show(request(), $objective, app(\App\Services\Cobit5\DocumentService::class));
        }

        $component = request()->query('component', '');

        // load master goal lists for MASTER view
        $masterEnterGoals = MstEntergoals::with('entergoalsmetr')->orderBy('entergoals_id')->get();
        $masterAlignGoals = MstAligngoals::with('aligngoalsmetr')->orderBy('aligngoals_id')->get();
        // load master roles
        $masterRoles = \App\Models\MstRoles::orderBy('role_id')->get();
        // load master practices
        $masterPractices = \App\Models\MstPractice::orderBy('practice_id')->get();
        $masterEvidenceFilters = app(\App\Services\Cobit\MasterEvidenceQueryService::class)->filters();

        return view('cobit_component.cobit2019.show', compact('objective', 'allObjectives', 'component', 'masterEnterGoals', 'masterAlignGoals', 'masterRoles', 'masterPractices', 'masterEvidenceFilters', 'focusAreaId', 'allFocusAreas'));
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

        return view('cobit_component.cobit2019.show', [
            'component' => $component,
            'items' => $items,
            'masterEnterGoals' => $masterEnterGoals,
            'masterAlignGoals' => $masterAlignGoals,
            'masterRoles' => $masterRoles,
        ]);
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
