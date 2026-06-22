<?php

namespace App\Http\Controllers\cobit2019;

use App\Http\Controllers\Controller;
use App\Models\MstFocusArea;
use App\Models\MstEntergoals;
use App\Models\MstAligngoals;
use App\Models\MstObjective;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class FocusAreaController extends Controller
{
    protected const OBJECTIVE_ORDER_SQL = "CASE WHEN UPPER(objective_id) LIKE 'EDM%' THEN 0 WHEN UPPER(objective_id) LIKE 'APO%' THEN 1 WHEN UPPER(objective_id) LIKE 'BAI%' THEN 2 WHEN UPPER(objective_id) LIKE 'DSS%' THEN 3 WHEN UPPER(objective_id) LIKE 'MEA%' THEN 4 ELSE 5 END, objective_id";

    /**
     * Centralised relation sets for loading objective components.
     */
    protected array $objectiveRelations = [
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
        'practices.infoflowoutput',
        'focusArea',
    ];

    protected function objectiveOrderSql(): string
    {
        return self::OBJECTIVE_ORDER_SQL;
    }

    protected function nextNumericId(string $table, string $column): int
    {
        $value = DB::table($table)->max($column);

        return ((int) ($value ?? 0)) + 1;
    }

    protected function nextGuidanceId(): int
    {
        $value = DB::table('mst_guidance')->max('guidance_id');

        return ((int) ($value ?? 0)) + 1;
    }

    protected function modelSuffix(int $focusAreaId): string
    {
        return '.M' . $focusAreaId;
    }

    protected function uniqueObjectiveId(string $baseObjectiveId, int $focusAreaId): string
    {
        $base = strtoupper(trim($baseObjectiveId));
        $suffixSeed = $this->modelSuffix($focusAreaId);
        $candidate = Str::limit($base . $suffixSeed, 20, '');
        $suffix = 1;

        while (MstObjective::where('objective_id', $candidate)->exists()) {
            $tail = $suffixSeed . '.' . $suffix;
            $candidate = Str::limit($base, 20 - strlen($tail), '') . $tail;
            $suffix++;
        }

        return $candidate;
    }

    protected function uniquePracticeId(string $basePracticeId, int $focusAreaId): string
    {
        $base = strtoupper(trim($basePracticeId));
        $suffixSeed = $this->modelSuffix($focusAreaId);
        $candidate = Str::limit($base . $suffixSeed, 20, '');
        $suffix = 1;

        while (DB::table('mst_practice')->where('practice_id', $candidate)->exists()) {
            $tail = $suffixSeed . '.' . $suffix;
            $candidate = Str::limit($base, 20 - strlen($tail), '') . $tail;
            $suffix++;
        }

        return $candidate;
    }

    protected function uniqueEnterGoalId(string $baseGoalId, int $focusAreaId): string
    {
        $base = strtoupper(trim($baseGoalId));
        $suffixSeed = $this->modelSuffix($focusAreaId);
        $candidate = Str::limit($base . $suffixSeed, 20, '');
        $suffix = 1;

        while (MstEntergoals::where('entergoals_id', $candidate)->exists()) {
            $tail = $suffixSeed . '.' . $suffix;
            $candidate = Str::limit($base, 20 - strlen($tail), '') . $tail;
            $suffix++;
        }

        return $candidate;
    }

    protected function uniqueAlignGoalId(string $baseGoalId, int $focusAreaId): string
    {
        $base = strtoupper(trim($baseGoalId));
        $suffixSeed = $this->modelSuffix($focusAreaId);
        $candidate = Str::limit($base . $suffixSeed, 20, '');
        $suffix = 1;

        while (MstAligngoals::where('aligngoals_id', $candidate)->exists()) {
            $tail = $suffixSeed . '.' . $suffix;
            $candidate = Str::limit($base, 20 - strlen($tail), '') . $tail;
            $suffix++;
        }

        return $candidate;
    }

    protected function findExistingObjectiveClone(string $baseObjectiveId, int $focusAreaId): ?MstObjective
    {
        $base = strtoupper(trim($baseObjectiveId));
        $prefixes = [
            $base . $this->modelSuffix($focusAreaId),
            $base . '.FA' . $focusAreaId,
        ];

        foreach ($prefixes as $prefix) {
            $existing = MstObjective::where('focus_area_id', $focusAreaId)
                ->where('objective_id', 'like', $prefix . '%')
                ->orderByRaw('LENGTH(objective_id) ASC')
                ->first();

            if ($existing) {
                return $existing;
            }
        }

        return null;
    }

    protected function nextEnterGoalMetricId(): int
    {
        return $this->nextNumericId('mst_entergoalsmetr', 'entergoalsmetr_id');
    }

    protected function nextAlignGoalMetricId(): int
    {
        return $this->nextNumericId('mst_aligngoalsmetr', 'aligngoalsmetr_id');
    }

    protected function cloneObjectiveFromBaseline(MstObjective $baseline, MstFocusArea $focusArea, ?string $customId = null, ?string $customName = null): MstObjective
    {
        return DB::transaction(function () use ($baseline, $focusArea, $customId, $customName) {
            $newObjectiveId = $this->uniqueObjectiveId($customId ?: $baseline->objective_id, $focusArea->id);

            $objective = MstObjective::create([
                'objective_id' => $newObjectiveId,
                'focus_area_id' => $focusArea->id,
                'objective' => $customName ?: $baseline->objective,
                'objective_description' => $baseline->objective_description,
                'objective_purpose' => $baseline->objective_purpose,
            ]);

            $this->cloneObjectiveRelations($baseline, $objective, $focusArea->id);

            return $objective;
        });
    }

    protected function cloneObjectiveRelations(MstObjective $baseline, MstObjective $objective, int $focusAreaId): void
    {
        foreach ($baseline->domains as $domain) {
            DB::table('trs_domain')->insertOrIgnore([
                'focus_area_id' => $focusAreaId,
                'area' => $domain->area,
                'objective_id' => $objective->objective_id,
                'domain' => $domain->pivot->domain ?? null,
            ]);
        }

        $guidanceMap = [];
        $cloneGuidance = function ($guidance) use (&$guidanceMap): int {
            $sourceId = (int) $guidance->guidance_id;
            if (isset($guidanceMap[$sourceId])) {
                return $guidanceMap[$sourceId];
            }

            $newGuidanceId = $this->nextGuidanceId();
            DB::table('mst_guidance')->insert([
                'guidance_id' => $newGuidanceId,
                'guidance' => $guidance->guidance,
                'reference' => $guidance->reference,
            ]);
            $guidanceMap[$sourceId] = $newGuidanceId;

            return $newGuidanceId;
        };

        foreach ($baseline->entergoals as $enterGoal) {
            $newEnterGoalId = $this->uniqueEnterGoalId($enterGoal->entergoals_id, $focusAreaId);

            DB::table('mst_entergoals')->insert([
                'entergoals_id' => $newEnterGoalId,
                'description' => $enterGoal->description,
            ]);

            foreach ($enterGoal->entergoalsmetr as $metr) {
                DB::table('mst_entergoalsmetr')->insert([
                    'entergoalsmetr_id' => $this->nextEnterGoalMetricId(),
                    'entergoals_id' => $newEnterGoalId,
                    'description' => $metr->description,
                ]);
            }

            DB::table('trs_entergoals')->insertOrIgnore([
                'focus_area_id' => $focusAreaId,
                'objective_id' => $objective->objective_id,
                'entergoals_id' => $newEnterGoalId,
            ]);
        }

        foreach ($baseline->aligngoals as $alignGoal) {
            $newAlignGoalId = $this->uniqueAlignGoalId($alignGoal->aligngoals_id, $focusAreaId);

            DB::table('mst_aligngoals')->insert([
                'aligngoals_id' => $newAlignGoalId,
                'description' => $alignGoal->description,
            ]);

            foreach ($alignGoal->aligngoalsmetr as $metr) {
                DB::table('mst_aligngoalsmetr')->insert([
                    'aligngoalsmetr_id' => $this->nextAlignGoalMetricId(),
                    'aligngoals_id' => $newAlignGoalId,
                    'description' => $metr->description,
                ]);
            }

            DB::table('trs_aligngoals')->insertOrIgnore([
                'focus_area_id' => $focusAreaId,
                'objective_id' => $objective->objective_id,
                'aligngoals_id' => $newAlignGoalId,
            ]);
        }

        foreach ($baseline->guidance as $guidance) {
            DB::table('trs_objectiveguidance')->insertOrIgnore([
                'focus_area_id' => $focusAreaId,
                'objective_id' => $objective->objective_id,
                'guidance_id' => $cloneGuidance($guidance),
                'component' => $guidance->pivot->component ?? null,
            ]);
        }

        foreach ($baseline->policies as $policy) {
            $newPolicyId = $this->nextNumericId('mst_policy', 'policy_id');
            DB::table('mst_policy')->insert([
                'policy_id' => $newPolicyId,
                'focus_area_id' => $focusAreaId,
                'objective_id' => $objective->objective_id,
                'policy' => $policy->policy,
                'description' => $policy->description,
            ]);
        }

        foreach ($baseline->skill as $skill) {
            $newSkillId = $this->nextNumericId('mst_skill', 'skill_id');
            DB::table('mst_skill')->insert([
                'skill_id' => $newSkillId,
                'focus_area_id' => $focusAreaId,
                'objective_id' => $objective->objective_id,
                'skill' => $skill->skill,
            ]);

            foreach ($skill->guidances as $guidance) {
                DB::table('trs_skillguidance')->insertOrIgnore([
                    'skill_id' => $newSkillId,
                    'guidance_id' => $guidance->guidance_id,
                ]);
            }
        }

        foreach ($baseline->keyculture as $culture) {
            $newCultureId = $this->nextNumericId('mst_keyculture', 'keyculture_id');
            DB::table('mst_keyculture')->insert([
                'keyculture_id' => $newCultureId,
                'focus_area_id' => $focusAreaId,
                'objective_id' => $objective->objective_id,
                'element' => $culture->element,
            ]);

            foreach ($culture->guidances as $guidance) {
                DB::table('trs_keycultureguidance')->insertOrIgnore([
                    'keyculture_id' => $newCultureId,
                    'guidance_id' => $guidance->guidance_id,
                ]);
            }
        }

        foreach ($baseline->s_i_a as $sia) {
            $newSiaId = $this->nextNumericId('mst_SIA', 'sia_id');
            DB::table('mst_SIA')->insert([
                'sia_id' => $newSiaId,
                'focus_area_id' => $focusAreaId,
                'objective_id' => $objective->objective_id,
                'description' => $sia->description,
            ]);
        }

        foreach ($baseline->practices as $practice) {
            $newPracticeId = $this->uniquePracticeId($practice->practice_id, $focusAreaId);

            DB::table('mst_practice')->insert([
                'focus_area_id' => $focusAreaId,
                'practice_id' => $newPracticeId,
                'objective_id' => $objective->objective_id,
                'practice_name' => $practice->practice_name,
                'practice_description' => $practice->practice_description,
            ]);

            foreach ($practice->practicemetr as $metr) {
                DB::table('mst_practicemetr')->insert([
                    'practice_id' => $newPracticeId,
                    'description' => $metr->description,
                ]);
            }

            $newInputMap = [];
            foreach ($practice->infoflowinput as $input) {
                $newInputId = $this->nextNumericId('mst_infoflowinput', 'input_id');
                DB::table('mst_infoflowinput')->insert([
                    'input_id' => $newInputId,
                    'practice_id' => $newPracticeId,
                    'from' => $input->from,
                    'description' => $input->description,
                ]);
                $newInputMap[$input->input_id] = $newInputId;
            }

            $newOutputMap = [];
            foreach ($practice->infoflowoutput as $output) {
                $newOutputId = $this->nextNumericId('mst_infoflowoutput', 'output_id');
                DB::table('mst_infoflowoutput')->insert([
                    'output_id' => $newOutputId,
                    'practice_id' => $newPracticeId,
                    'to' => $output->to,
                    'description' => $output->description,
                ]);
                $newOutputMap[$output->output_id] = $newOutputId;
            }

            foreach ($practice->activities as $activity) {
                $newActivityId = $this->nextNumericId('mst_activities', 'activity_id');
                DB::table('mst_activities')->insert([
                    'activity_id' => $newActivityId,
                    'practice_id' => $newPracticeId,
                    'description' => $activity->description,
                    'capability_lvl' => $activity->capability_lvl,
                ]);
            }

            foreach ($practice->roles as $role) {
                DB::table('trs_practroles')->insertOrIgnore([
                    'practice_id' => $newPracticeId,
                    'role_id' => $role->role_id,
                    'r_a' => $role->pivot->r_a ?? null,
                ]);
            }

            foreach ($practice->guidances as $guidance) {
                DB::table('trs_practiceguidance')->insertOrIgnore([
                    'practice_id' => $newPracticeId,
                    'guidance_id' => $cloneGuidance($guidance),
                ]);
            }

            foreach ($practice->infoflowinput as $input) {
                $newInputId = $newInputMap[$input->input_id] ?? null;
                if (! $newInputId) {
                    continue;
                }
                foreach ($input->connectedoutputs as $output) {
                    if (! isset($newOutputMap[$output->output_id])) {
                        continue;
                    }
                    DB::table('trs_infoflowio')->insertOrIgnore([
                        'input_id' => $newInputId,
                        'output_id' => $newOutputMap[$output->output_id],
                    ]);
                }
            }
        }

        foreach ($baseline->roadmaps as $roadmap) {
            DB::table('trs_roadmap')->insertOrIgnore([
                'focus_area_id' => $focusAreaId,
                'objective_id' => $objective->objective_id,
                'year' => $roadmap->year,
                'level' => $roadmap->level,
                'rating' => $roadmap->rating,
            ]);
        }
    }

    /**
     * Display a listing of focus areas (page).
     */
    public function index()
    {
        $focusAreas = MstFocusArea::withCount('objectives')->get();
        $allObjectives = MstObjective::select('objective_id', 'objective')
            ->orderByRaw($this->objectiveOrderSql())
            ->get();

        return view('focus_area.index', compact('focusAreas', 'allObjectives'));
    }

    /**
     * Display the specified focus area with all objectives and components.
     */
    public function show($id)
    {
        $focusArea = MstFocusArea::findOrFail($id);

        // Load objectives that belong to this focus area with all their components
        $objectives = $focusArea->objectives()
            ->with($this->objectiveRelations)
            ->orderByRaw($this->objectiveOrderSql())
            ->get();

        $baselineObjectives = MstObjective::select('objective_id', 'objective', 'objective_description', 'objective_purpose')
            ->where('focus_area_id', 1)
            ->orderByRaw($this->objectiveOrderSql())
            ->get();

        $allFocusAreas = MstFocusArea::withCount('objectives')->get();

        // load master roles
        $masterRoles = \App\Models\MstRoles::orderBy('role_id')->get();

        return view('focus_area.show', compact('focusArea', 'objectives', 'baselineObjectives', 'allFocusAreas', 'masterRoles'));
    }

    /**
     * Store a newly created focus area.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:10|unique:mst_focusarea,code',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        $focusArea = MstFocusArea::create($data);

        return response()->json($focusArea, 201);
    }

    /**
     * Update the specified focus area.
     */
    public function update(Request $request, $id)
    {
        $focusArea = MstFocusArea::findOrFail($id);

        $data = $request->validate([
            'code' => 'sometimes|required|string|max:10|unique:mst_focusarea,code,' . $focusArea->id,
            'name' => 'sometimes|required|string|max:100',
            'description' => 'nullable|string',
        ]);

        $focusArea->update($data);

        return response()->json($focusArea);
    }

    /**
     * Remove the specified focus area.
     */
    public function destroy($id)
    {
        if ($id == 1) {
            return response()->json(['message' => 'COBIT Core Model tidak bisa dihapus.'], 403);
        }

        $focusArea = MstFocusArea::findOrFail($id);
        // Cascade delete will remove all objectives with this focus_area_id
        // and their child data (practices, policies, etc) via FK cascade
        $focusArea->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Store a new objective, or add baseline objectives to the focus area.
     */
    public function storeObjective(Request $request, $id)
    {
        $focusArea = MstFocusArea::findOrFail($id);

        $baselineId = trim((string) $request->input('baseline_objective_id', ''));
        $legacyBaselineIds = collect($request->input('baseline_objective_ids', []))
            ->filter()
            ->unique()
            ->values();

        if ($baselineId !== '' || $legacyBaselineIds->isNotEmpty()) {
            $selectedBaselineId = $baselineId !== ''
                ? $baselineId
                : (string) $legacyBaselineIds->first();

            $data = $request->validate([
                'baseline_objective_id' => 'nullable|string|exists:mst_objective,objective_id',
                'baseline_objective_ids' => 'nullable|array|min:1',
                'baseline_objective_ids.*' => 'string|exists:mst_objective,objective_id',
                'custom_objective_id' => 'nullable|string|max:50',
                'custom_objective_name' => 'nullable|string|max:255',
            ]);

            $customId = $request->input('custom_objective_id');
            $customName = $request->input('custom_objective_name');

            $baselines = MstObjective::with([
                'domains',
                'entergoals',
                'aligngoals',
                'guidance',
                'policies',
                'skill.guidances',
                'keyculture.guidances',
                's_i_a',
                'practices.practicemetr',
                'practices.activities',
                'practices.roles',
                'practices.guidances',
                'practices.infoflowinput.connectedoutputs',
                'practices.infoflowinput',
                'practices.infoflowoutput',
                'roadmaps',
            ])->where('objective_id', $selectedBaselineId)->get();

            $cloned = [];
            $reusedCount = 0;
            $createdCount = 0;
            foreach ($baselines as $baseline) {
                if (!$customId && $existingClone = $this->findExistingObjectiveClone($baseline->objective_id, $focusArea->id)) {
                    $cloned[] = $existingClone->objective_id;
                    $reusedCount++;
                    continue;
                }

                $clonedObjective = $this->cloneObjectiveFromBaseline($baseline, $focusArea, $customId, $customName);
                $cloned[] = $clonedObjective->objective_id;
                $createdCount++;
            }

            return response()->json([
                'success' => true,
                'mode' => 'baseline',
                'added' => $createdCount,
                'reused' => $reusedCount,
                'cloned_objective' => $cloned[0] ?? null,
                'cloned_objectives' => $cloned,
                'message' => $createdCount > 0 ? 'Objective berhasil ditambahkan.' : 'Objective sudah ada di model ini.',
            ], $createdCount > 0 ? 201 : 200);
        }

        $request->merge([
            'objective_id' => strtoupper(trim((string) $request->input('objective_id'))),
        ]);

        $data = $request->validate([
            'objective_id' => ['required', 'string', 'max:20', Rule::unique('mst_objective', 'objective_id')],
            'objective' => 'required|string|max:255',
            'objective_description' => 'nullable|string',
            'objective_purpose' => 'nullable|string',
        ]);

        $data['focus_area_id'] = $focusArea->id;
        $objective = MstObjective::create($data);

        return response()->json($objective, 201);
    }

    /**
     * Update an objective within this focus area.
     */
    /**
     * Generate COBIT 5 processes automatically for a Focus Area.
     */
    public function generateCobit5($id)
    {
        $focusArea = MstFocusArea::findOrFail($id);
        $mapping = config('cobit-mappings.cobit5', []);

        if (empty($mapping)) {
            return response()->json(['success' => false, 'message' => 'Mapping COBIT 5 tidak ditemukan di config.'], 404);
        }

        $result = $this->bulkCloneObjectives($focusArea, $mapping);

        return response()->json([
            'success' => true,
            'added' => $result['added'],
            'reused' => $result['reused'],
            'message' => "COBIT 5 template generated. {$result['added']} process created, {$result['reused']} skipped."
        ]);
    }

    /**
     * Generic function to bulk clone objectives based on a mapping array.
     * Mapping format: ['Baseline_ID' => 'Custom Name']
     */
    protected function bulkCloneObjectives(MstFocusArea $focusArea, array $mapping): array
    {
        $baselines = MstObjective::with([
            'domains',
            'entergoals',
            'aligngoals',
            'guidance',
            'policies',
            'skill.guidances',
            'keyculture.guidances',
            's_i_a',
            'practices.practicemetr',
            'practices.activities',
            'practices.roles',
            'practices.guidances',
            'practices.infoflowinput.connectedoutputs',
            'practices.infoflowinput',
            'practices.infoflowoutput',
            'roadmaps',
        ])->whereIn('objective_id', array_keys($mapping))->get()->keyBy('objective_id');

        $createdCount = 0;
        $reusedCount = 0;

        foreach ($mapping as $baselineId => $customName) {
            $baseline = $baselines->get($baselineId);
            if (!$baseline) {
                continue;
            }

            // check if custom already exists
            if ($existingClone = $this->findExistingObjectiveClone($baseline->objective_id, $focusArea->id)) {
                $reusedCount++;
                continue;
            }

            // Clone with custom ID/Name
            $this->cloneObjectiveFromBaseline($baseline, $focusArea, $baseline->objective_id, $customName);
            $createdCount++;
        }

        return [
            'added' => $createdCount,
            'reused' => $reusedCount,
        ];
    }

    public function updateObjective(Request $request, $id, $objectiveId)
    {
        $focusArea = MstFocusArea::findOrFail($id);

        $objective = MstObjective::where('objective_id', $objectiveId)
            ->where('focus_area_id', $focusArea->id)
            ->firstOrFail();

        $data = $request->validate([
            'objective' => 'sometimes|required|string|max:255',
            'objective_description' => 'nullable|string',
            'objective_purpose' => 'nullable|string',
        ]);

        $objective->update($data);

        return response()->json($objective);
    }

    /**
     * Delete an objective within this focus area (cascade deletes child data).
     */
    public function destroyObjective(Request $request, $id, $objectiveId)
    {
        $focusArea = MstFocusArea::findOrFail($id);

        $deleted = MstObjective::where('objective_id', $objectiveId)
            ->where('focus_area_id', $focusArea->id)
            ->delete();

        return response()->json(['success' => true, 'deleted' => $deleted]);
    }

    // =========================================================================
    // PUBLIC API ENDPOINTS
    // =========================================================================

    /**
     * Public API: List all focus areas.
     */
    public function apiList()
    {
        $focusAreas = MstFocusArea::with('objectives:objective_id,objective,focus_area_id')->get();

        $data = $focusAreas->map(function ($fa) {
            return [
                'id' => $fa->id,
                'code' => $fa->code,
                'name' => $fa->name,
                'description' => $fa->description,
                'objectives_count' => $fa->objectives->count(),
                'objectives' => $fa->objectives->map(function ($o) {
                    return [
                        'objective_id' => $o->objective_id,
                        'objective' => $o->objective,
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'focus_areas' => $data,
        ], 200, $this->apiResponseHeaders());
    }

    /**
     * Public API: Show a single focus area with full objective components.
     */
    public function apiShow($id)
    {
        $focusArea = MstFocusArea::findOrFail($id);

        $objectives = $focusArea->objectives()
            ->with($this->objectiveRelations)
            ->orderByRaw($this->objectiveOrderSql())
            ->get();

        $data = [
            'id' => $focusArea->id,
            'code' => $focusArea->code,
            'name' => $focusArea->name,
            'description' => $focusArea->description,
            'objectives' => $objectives->map(function ($o) {
                return [
                    'objective_id' => $o->objective_id,
                    'objective' => $o->objective,
                    'description' => $o->objective_description ?? '',
                    'purpose' => $o->objective_purpose ?? '',
                    'domains' => $o->domains->map(fn ($d) => [
                        'area' => $d->area,
                        'domain' => $d->pivot->domain ?? null,
                    ]),
                    'practices_count' => $o->practices->count(),
                    'policies_count' => $o->policies->count(),
                    'skills_count' => $o->skill->count(),
                    'culture_count' => $o->keyculture->count(),
                    'sia_count' => $o->s_i_a->count(),
                ];
            }),
        ];

        return response()->json([
            'success' => true,
            'focus_area' => $data,
        ], 200, $this->apiResponseHeaders());
    }

    /**
     * CORS response headers for public API.
     */
    protected function apiResponseHeaders(): array
    {
        return [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Content-Type, X-Requested-With, Authorization',
            'Access-Control-Allow-Methods' => 'GET, OPTIONS',
        ];
    }
}
