<?php

namespace App\Http\Controllers\cobit2019;

use App\Http\Controllers\cobit2019\Concerns\ExtractsInfoflows;
use App\Http\Controllers\Controller;
use App\Models\MstObjective;
use Illuminate\Http\Request;

class CobitApiController extends Controller
{
    use ExtractsInfoflows;

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
