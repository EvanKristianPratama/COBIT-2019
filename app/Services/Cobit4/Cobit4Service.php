<?php

namespace App\Services\Cobit4;

use App\Models\Cobit4\Cobit4Process;
use App\Models\Cobit4\Cobit4ControlObjective;
use App\Models\Cobit4\Cobit4Input;
use App\Models\Cobit4\Cobit4Output;
use App\Models\Cobit4\Cobit4RaciActivity;
use App\Models\Cobit4\Cobit4GoalsMetric;
use App\Models\Cobit4\Cobit4MaturityLevel;
use App\Models\MstObjective;
use App\Models\MstFocusArea;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Cobit4Service
{
    /**
     * Resolve standard COBIT 4.1 process code from objective ID.
     * E.g. 'PO01.M27' -> 'PO1', 'PO2.M27' -> 'PO2', 'AI05.M27' -> 'AI5', 'DS10' -> 'DS10'
     */
    public function resolveProcessCode(string $objectiveId): string
    {
        if (preg_match('/^([A-Za-z]+)0*(\d+)/', $objectiveId, $matches)) {
            return strtoupper($matches[1]) . intval($matches[2]);
        }
        return strtoupper($objectiveId);
    }

    /**
     * Get authentic COBIT 4.1 domains.
     */
    public function getDomains(): array
    {
        return config('cobit4-data.domains', [
            'PO' => [
                'code' => 'PO',
                'name' => 'Plan and Organise',
                'badge' => 'Plan and Organise',
                'color' => '#0a4b88',
                'desc' => 'Covers strategy and tactics, and concerns the identification of the way IT can best contribute to the achievement of the business objectives.',
            ],
            'AI' => [
                'code' => 'AI',
                'name' => 'Acquire and Implement',
                'badge' => 'Acquire and Implement',
                'color' => '#1d5a8a',
                'desc' => 'To realise the IT strategy, IT solutions need to be identified, developed or acquired, as well as implemented and integrated into the business process.',
            ],
            'DS' => [
                'code' => 'DS',
                'name' => 'Deliver and Support',
                'badge' => 'Deliver and Support',
                'color' => '#1f698e',
                'desc' => 'Concerned with the actual delivery of required services, including service delivery, management of security and continuity, service support for users, and management of data and the operational facilities.',
            ],
            'ME' => [
                'code' => 'ME',
                'name' => 'Monitor and Evaluate',
                'badge' => 'Monitor and Evaluate',
                'color' => '#144670',
                'desc' => 'All IT processes need to be regularly assessed over time for their quality and compliance with control requirements.',
            ],
        ]);
    }

    /**
     * Ensure all 34 authentic COBIT 4.1 processes exist in the database.
     */
    public function ensureCobit4Database(int $focusAreaId = 27): int
    {
        $focusArea = MstFocusArea::find($focusAreaId);
        if (!$focusArea) {
            return 0;
        }

        $masterList = $this->getMasterProcesses();
        $countAdded = 0;

        foreach ($masterList as $item) {
            $code = strtoupper(trim($item['code']));
            $existing = Cobit4Process::where('focus_area_id', $focusAreaId)
                ->where('code', $code)
                ->first();

            if (!$existing) {
                // Determine source data: check config baseline or fallback generator
                $configData = config("cobit4-data.processes.{$code}");
                $procData = $configData ?: $this->generateFallbackProcessData($code, null, $item['title'], $item['desc']);
                
                $this->createProcessFromData($procData, $item['id'], $focusAreaId);
                $countAdded++;
            }
        }

        return $countAdded;
    }

    /**
     * Create a Cobit4Process record with its relations from structured array.
     */
    public function createProcessFromData(array $data, ?string $objectiveId = null, int $focusAreaId = 27): Cobit4Process
    {
        return DB::transaction(function () use ($data, $objectiveId, $focusAreaId) {
            $code = strtoupper(trim($data['code'] ?? 'PO1'));
            $domainCode = strtoupper(trim($data['domain_code'] ?? substr($code, 0, 2)));
            $domainMap = [
                'PO' => 'Plan and Organise',
                'AI' => 'Acquire and Implement',
                'DS' => 'Deliver and Support',
                'ME' => 'Monitor and Evaluate',
            ];
            $domainName = $data['domain_name'] ?? ($domainMap[$domainCode] ?? 'Plan and Organise');

            $criteria = $data['information_criteria'] ?? [];
            $controlStmt = $data['control_statement'] ?? [];
            $govFocus = $data['it_governance_focus'] ?? [];
            $res = $data['it_resources'] ?? [];
            $maturityModel = $data['maturity_model'] ?? [];

            $resolvedObjId = $objectiveId ?: "{$code}.M{$focusAreaId}";

            // 1. Create or update Cobit4Process
            $process = Cobit4Process::updateOrCreate(
                [
                    'focus_area_id' => $focusAreaId,
                    'code' => $code,
                ],
                [
                    'objective_id' => $resolvedObjId,
                    'domain_code' => $domainCode,
                    'domain_name' => $domainName,
                    'title' => $data['title'] ?? "Process {$code}",
                    'description' => $data['description'] ?? null,
                    'criteria_effectiveness' => $criteria['effectiveness'] ?? null,
                    'criteria_efficiency' => $criteria['efficiency'] ?? null,
                    'criteria_confidentiality' => $criteria['confidentiality'] ?? null,
                    'criteria_integrity' => $criteria['integrity'] ?? null,
                    'criteria_availability' => $criteria['availability'] ?? null,
                    'criteria_compliance' => $criteria['compliance'] ?? null,
                    'criteria_reliability' => $criteria['reliability'] ?? null,
                    'control_over' => $controlStmt['control_over'] ?? null,
                    'satisfies_requirement' => $controlStmt['satisfies_requirement'] ?? null,
                    'focusing_on' => $controlStmt['focusing_on'] ?? null,
                    'achieved_by' => $controlStmt['achieved_by'] ?? [],
                    'measured_by' => $controlStmt['measured_by'] ?? [],
                    'gov_strategic_alignment' => $govFocus['strategic_alignment'] ?? null,
                    'gov_value_delivery' => $govFocus['value_delivery'] ?? null,
                    'gov_risk_management' => $govFocus['risk_management'] ?? null,
                    'gov_resource_management' => $govFocus['resource_management'] ?? null,
                    'gov_performance_measurement' => $govFocus['performance_measurement'] ?? null,
                    'res_applications' => !empty($res['applications']),
                    'res_information' => !empty($res['information']),
                    'res_infrastructure' => !empty($res['infrastructure']),
                    'res_people' => !empty($res['people']),
                    'maturity_intro' => $maturityModel['intro'] ?? null,
                ]
            );

            // 2. Control Objectives
            $process->controlObjectives()->delete();
            $coList = $data['control_objectives'] ?? [];
            foreach ($coList as $idx => $co) {
                Cobit4ControlObjective::create([
                    'process_id' => $process->id,
                    'code' => $co['code'] ?? "{$code}." . ($idx + 1),
                    'title' => $co['title'] ?? 'Control Objective',
                    'description' => $co['desc'] ?? ($co['description'] ?? ''),
                    'order_no' => $idx + 1,
                ]);
            }

            // 3. Inputs & Outputs
            $process->inputs()->delete();
            $process->outputs()->delete();
            $inputs = $data['management_guidelines']['inputs'] ?? [];
            foreach ($inputs as $idx => $inp) {
                Cobit4Input::create([
                    'process_id' => $process->id,
                    'from_source' => $inp['from'] ?? '*',
                    'input_description' => $inp['input'] ?? '',
                    'order_no' => $idx + 1,
                ]);
            }

            $outputs = $data['management_guidelines']['outputs'] ?? [];
            foreach ($outputs as $idx => $out) {
                Cobit4Output::create([
                    'process_id' => $process->id,
                    'output_description' => $out['output'] ?? '',
                    'to_target' => $out['to'] ?? '',
                    'order_no' => $idx + 1,
                ]);
            }

            // 4. RACI Activities
            $process->raciActivities()->delete();
            $activities = $data['management_guidelines']['raci']['activities'] ?? [];
            foreach ($activities as $idx => $act) {
                Cobit4RaciActivity::create([
                    'process_id' => $process->id,
                    'activity' => $act['activity'] ?? '',
                    'raci_matrix' => $act['raci'] ?? [],
                    'order_no' => $idx + 1,
                ]);
            }

            // 5. Goals & Metrics
            $process->goalsMetrics()->delete();
            $gm = $data['management_guidelines']['goals_and_metrics'] ?? [];
            $categories = ['it_goals', 'it_metrics', 'process_goals', 'process_metrics', 'activities_goals', 'activities_metrics'];
            foreach ($categories as $cat) {
                $items = $gm[$cat] ?? [];
                foreach ($items as $idx => $itemText) {
                    Cobit4GoalsMetric::create([
                        'process_id' => $process->id,
                        'category' => $cat,
                        'content' => $itemText,
                        'order_no' => $idx + 1,
                    ]);
                }
            }

            // 6. Maturity Levels
            $process->maturityLevels()->delete();
            $levels = $maturityModel['levels'] ?? [];
            foreach ($levels as $lvl => $levelData) {
                Cobit4MaturityLevel::create([
                    'process_id' => $process->id,
                    'level' => (int) $lvl,
                    'name' => $levelData['name'] ?? "Level {$lvl}",
                    'description' => $levelData['desc'] ?? ($levelData['description'] ?? ''),
                ]);
            }

            // 7. Sync mst_objective table
            MstObjective::updateOrCreate(
                [
                    'objective_id' => $resolvedObjId,
                    'focus_area_id' => $focusAreaId,
                ],
                [
                    'objective' => $process->title,
                    'objective_description' => $process->description,
                    'objective_purpose' => 'COBIT 4.1 Control Objectives and Management Guidelines',
                ]
            );

            return $process;
        });
    }

    /**
     * Get full authentic structured data for a process.
     */
    public function getProcessData(string $processCode, ?MstObjective $objective = null, int $focusAreaId = 27): array
    {
        $code = strtoupper(trim($processCode));

        // 1. Query MySQL database first
        $process = Cobit4Process::with([
            'controlObjectives',
            'inputs',
            'outputs',
            'raciActivities',
            'goalsMetrics',
            'maturityLevels',
        ])
        ->where('focus_area_id', $focusAreaId)
        ->where('code', $code)
        ->first();

        if ($process) {
            return $process->toStructuredArray();
        }

        // 2. If not seeded yet, seed all 34 and retrieve
        $this->ensureCobit4Database($focusAreaId);

        $process = Cobit4Process::with([
            'controlObjectives',
            'inputs',
            'outputs',
            'raciActivities',
            'goalsMetrics',
            'maturityLevels',
        ])
        ->where('focus_area_id', $focusAreaId)
        ->where('code', $code)
        ->first();

        if ($process) {
            return $process->toStructuredArray();
        }

        // 3. Fallback generator if process is custom or completely new
        return $this->generateFallbackProcessData($code, $objective);
    }

    /**
     * Save custom COBIT 4.1 process data directly to database.
     */
    public function saveProcessData(string $processCode, array $data, ?string $objectiveId = null, int $focusAreaId = 27): bool
    {
        $data['code'] = $processCode;
        $this->createProcessFromData($data, $objectiveId, $focusAreaId);
        return true;
    }

    /**
     * Create a new custom COBIT 4 GAMO/Process from user form.
     * Inputs: code (e.g. PO11), title (Nama GAMO), domain_code (PO/AI/DS/ME), description (Isi).
     */
    public function createCustomProcess(array $input, int $focusAreaId = 27): Cobit4Process
    {
        $code = strtoupper(trim($input['code'] ?? ''));
        $title = trim($input['title'] ?? '');
        $domainCode = strtoupper(trim($input['domain_code'] ?? 'PO'));
        $description = trim($input['description'] ?? '');

        $domainMap = [
            'PO' => 'Plan and Organise',
            'AI' => 'Acquire and Implement',
            'DS' => 'Deliver and Support',
            'ME' => 'Monitor and Evaluate',
        ];
        $domainName = $domainMap[$domainCode] ?? 'Plan and Organise';

        $resolvedObjId = "{$code}.M{$focusAreaId}";

        // Build scaffold structure
        $scaffoldData = $this->generateFallbackProcessData($code, null, $title, $description);
        $scaffoldData['domain_code'] = $domainCode;
        $scaffoldData['domain_name'] = $domainName;

        return $this->createProcessFromData($scaffoldData, $resolvedObjId, $focusAreaId);
    }

    /**
     * Delete a COBIT 4 process.
     */
    public function deleteProcess(string $processCode, int $focusAreaId = 27): bool
    {
        $code = strtoupper(trim($processCode));
        $proc = Cobit4Process::where('focus_area_id', $focusAreaId)->where('code', $code)->first();
        if ($proc) {
            $objId = $proc->objective_id;
            $proc->delete();
            if ($objId) {
                MstObjective::where('objective_id', $objId)->where('focus_area_id', $focusAreaId)->delete();
            }
            return true;
        }
        return false;
    }

    /**
     * Reset custom COBIT 4.1 process data back to ISACA baseline.
     */
    public function resetProcessData(string $processCode, ?string $objectiveId = null, int $focusAreaId = 27): bool
    {
        $code = strtoupper(trim($processCode));
        
        $configBaseline = config("cobit4-data.processes.{$code}");
        if ($configBaseline) {
            $this->createProcessFromData($configBaseline, $objectiveId, $focusAreaId);
        } else {
            // Re-generate default baseline
            $masterList = collect($this->getMasterProcesses())->keyBy('code');
            $masterItem = $masterList->get($code);
            $title = $masterItem ? $masterItem['title'] : "Process {$code}";
            $desc = $masterItem ? $masterItem['desc'] : null;
            $scaffold = $this->generateFallbackProcessData($code, null, $title, $desc);
            $this->createProcessFromData($scaffold, $objectiveId, $focusAreaId);
        }

        return true;
    }

    /**
     * Get all processes for a focus area (ordered by domain & code).
     */
    public function getAllProcesses(int $focusAreaId = 27)
    {
        $this->ensureCobit4Database($focusAreaId);

        $orderSql = "CASE 
            WHEN domain_code = 'PO' THEN 1 
            WHEN domain_code = 'AI' THEN 2 
            WHEN domain_code = 'DS' THEN 3 
            WHEN domain_code = 'ME' THEN 4 
            ELSE 5 END, CAST(REGEXP_SUBSTR(code, '[0-9]+') AS UNSIGNED), code";

        return Cobit4Process::with([
            'controlObjectives',
            'inputs',
            'outputs',
            'raciActivities',
            'goalsMetrics',
            'maturityLevels',
        ])
        ->where('focus_area_id', $focusAreaId)
        ->orderByRaw($orderSql)
        ->get();
    }

    /**
     * Get aggregated data for "View by Component" in COBIT 4.
     */
    public function getComponentData(string $component, int $focusAreaId = 27): array
    {
        $processes = $this->getAllProcesses($focusAreaId);

        return $processes->map(function ($proc) use ($component) {
            $base = [
                'id' => $proc->id,
                'code' => $proc->code,
                'domain_code' => $proc->domain_code,
                'domain_name' => $proc->domain_name,
                'title' => $proc->title,
                'objective_id' => $proc->objective_id,
            ];

            switch ($component) {
                case 'overview':
                    $base['description'] = $proc->description;
                    $base['criteria'] = [
                        'effectiveness' => $proc->criteria_effectiveness,
                        'efficiency' => $proc->criteria_efficiency,
                        'confidentiality' => $proc->criteria_confidentiality,
                        'integrity' => $proc->criteria_integrity,
                        'availability' => $proc->criteria_availability,
                        'compliance' => $proc->criteria_compliance,
                        'reliability' => $proc->criteria_reliability,
                    ];
                    $base['control_statement'] = [
                        'control_over' => $proc->control_over,
                        'satisfies_requirement' => $proc->satisfies_requirement,
                        'focusing_on' => $proc->focusing_on,
                        'achieved_by' => $proc->achieved_by ?? [],
                        'measured_by' => $proc->measured_by ?? [],
                    ];
                    $base['it_resources'] = [
                        'applications' => $proc->res_applications,
                        'information' => $proc->res_information,
                        'infrastructure' => $proc->res_infrastructure,
                        'people' => $proc->res_people,
                    ];
                    break;

                case 'control_objectives':
                    $base['control_objectives'] = $proc->controlObjectives->map(fn($co) => [
                        'id' => $co->id,
                        'code' => $co->code,
                        'title' => $co->title,
                        'description' => $co->description,
                    ])->toArray();
                    break;

                case 'infoflows':
                    $base['inputs'] = $proc->inputs->map(fn($i) => [
                        'id' => $i->id,
                        'from' => $i->from_source,
                        'input' => $i->input_description,
                    ])->toArray();
                    $base['outputs'] = $proc->outputs->map(fn($o) => [
                        'id' => $o->id,
                        'output' => $o->output_description,
                        'to' => $o->to_target,
                    ])->toArray();
                    break;

                case 'organizational':
                    $base['raci_activities'] = $proc->raciActivities->map(fn($ra) => [
                        'id' => $ra->id,
                        'activity' => $ra->activity,
                        'raci' => $ra->raci_matrix ?? [],
                    ])->toArray();
                    break;

                case 'goals_metrics':
                    $base['it_goals'] = $proc->goalsMetrics->where('category', 'it_goals')->pluck('content')->values()->toArray();
                    $base['it_metrics'] = $proc->goalsMetrics->where('category', 'it_metrics')->pluck('content')->values()->toArray();
                    $base['process_goals'] = $proc->goalsMetrics->where('category', 'process_goals')->pluck('content')->values()->toArray();
                    $base['process_metrics'] = $proc->goalsMetrics->where('category', 'process_metrics')->pluck('content')->values()->toArray();
                    $base['activities_goals'] = $proc->goalsMetrics->where('category', 'activities_goals')->pluck('content')->values()->toArray();
                    $base['activities_metrics'] = $proc->goalsMetrics->where('category', 'activities_metrics')->pluck('content')->values()->toArray();
                    break;

                case 'maturity':
                    $base['maturity_intro'] = $proc->maturity_intro;
                    $base['levels'] = $proc->maturityLevels->map(fn($m) => [
                        'level' => $m->level,
                        'name' => $m->name,
                        'description' => $m->description,
                    ])->toArray();
                    break;
            }

            return $base;
        })->toArray();
    }

    /**
     * Generate fallback authentic COBIT 4.1 structure when specific process data is not yet in config.
     */
    public function generateFallbackProcessData(string $code, ?MstObjective $objective = null, ?string $customTitle = null, ?string $customDesc = null): array
    {
        $domainPrefix = preg_replace('/[0-9]/', '', $code);
        $domainMap = [
            'PO' => 'Plan and Organise',
            'AI' => 'Acquire and Implement',
            'DS' => 'Deliver and Support',
            'ME' => 'Monitor and Evaluate',
        ];
        $domainName = $domainMap[$domainPrefix] ?? 'Plan and Organise';

        $title = $customTitle ?: ($objective ? $objective->objective : "Process {$code}");
        $desc = $customDesc ?: ($objective && $objective->objective_description
            ? $objective->objective_description
            : "Management of the IT process of {$title} in accordance with COBIT 4.1 control and governance objectives.");

        return [
            'code' => $code,
            'domain_code' => $domainPrefix,
            'domain_name' => $domainName,
            'title' => $title,
            'description' => $desc,
            'information_criteria' => [
                'effectiveness' => 'P',
                'efficiency' => 'S',
                'confidentiality' => '',
                'integrity' => 'P',
                'availability' => '',
                'compliance' => 'S',
                'reliability' => '',
            ],
            'control_statement' => [
                'control_over' => strtolower($title),
                'satisfies_requirement' => 'supporting business operational goals and strategic compliance while managing risks and resource utilisation',
                'focusing_on' => 'establishing standard operating procedures, roles, performance indicators and governance oversight',
                'achieved_by' => [
                    "Defining clear accountability and ownership for {$title}",
                    "Implementing standard operating baselines and repeatable procedures",
                    "Monitoring key performance indicators and reporting deviations promptly",
                ],
                'measured_by' => [
                    "Percent of stakeholders satisfied with process outcomes",
                    "Number of process deviations or control exceptions identified",
                    "Cycle time and cost efficiency of process execution",
                ],
            ],
            'it_governance_focus' => [
                'strategic_alignment' => 'Primary',
                'value_delivery' => 'Secondary',
                'risk_management' => '',
                'resource_management' => '',
                'performance_measurement' => '',
            ],
            'it_resources' => [
                'applications' => true,
                'information' => true,
                'infrastructure' => true,
                'people' => true,
            ],
            'control_objectives' => [
                [
                    'code' => "{$code}.1",
                    'title' => "Governance and Management of {$title}",
                    'desc' => "Establish an effective management framework for {$title} ensuring strategic alignment and accountability across relevant business and IT stakeholders.",
                ],
                [
                    'code' => "{$code}.2",
                    'title' => "Execution and Procedural Controls",
                    'desc' => "Define, document and enforce standard operating controls and repeatable practices for {$title}.",
                ],
                [
                    'code' => "{$code}.3",
                    'title' => "Monitoring and Performance Evaluation",
                    'desc' => "Regularly assess the efficiency, reliability and compliance of {$title} against predefined metrics and service levels.",
                ],
            ],
            'management_guidelines' => [
                'inputs' => [
                    ['from' => 'PO1', 'input' => 'Strategic and tactical IT plans'],
                    ['from' => '*', 'input' => 'Business strategy and operational requirements'],
                ],
                'outputs' => [
                    ['output' => "{$title} operational reports", 'to' => 'ME1, ME4'],
                    ['output' => 'Process improvement action items', 'to' => 'PO4'],
                ],
                'raci' => [
                    'roles' => [
                        'CEO', 'CFO', 'Business Executive', 'CIO', 'Business Process Owner',
                        'Head Operations', 'Chief Architect', 'Head Development',
                        'Head IT Administration', 'PMO', 'Compliance, Audit, Risk and Security'
                    ],
                    'activities' => [
                        [
                            'activity' => "Define strategic parameters and requirements for {$title}.",
                            'raci' => ['C', 'I', 'A', 'R', 'C', '', '', '', '', '', 'C'],
                        ],
                        [
                            'activity' => "Execute, operate and administer {$title}.",
                            'raci' => ['', '', 'C', 'A', 'R', 'R', 'C', 'C', 'C', '', ''],
                        ],
                        [
                            'activity' => "Monitor performance, compliance and risk for {$title}.",
                            'raci' => ['I', 'I', 'C', 'A', 'C', 'C', '', '', '', 'C', 'R'],
                        ],
                    ],
                ],
                'goals_and_metrics' => [
                    'it_goals' => [
                        "Deliver reliable and value-driven IT services in alignment with {$title}.",
                        "Ensure compliance with governance standards and business policies.",
                    ],
                    'it_metrics' => [
                        "Stakeholder satisfaction score with {$title} deliverables.",
                        "Percent of objectives met within budget and timeframe.",
                    ],
                    'process_goals' => [
                        "Establish repeatable and well-managed execution of {$title}.",
                        "Provide transparent reporting on process efficiency and risks.",
                    ],
                    'process_metrics' => [
                        "Coverage of standard controls across operational units.",
                        "Frequency of periodic reviews and updates.",
                    ],
                    'activities_goals' => [
                        "Ensure staff are trained and roles clearly understood.",
                        "Document all procedures and operational baselines.",
                    ],
                    'activities_metrics' => [
                        "Number of training sessions conducted.",
                        "Variance against schedule and SLA commitments.",
                    ],
                ],
            ],
            'maturity_model' => [
                'intro' => "Management of the process of {$title} is:",
                'levels' => [
                    0 => ['name' => 'Non-existent', 'desc' => "There is complete lack of any recognisable process for {$title}. The enterprise has not recognised that there is an issue to be addressed."],
                    1 => ['name' => 'Initial/Ad Hoc', 'desc' => "There is evidence that the enterprise has recognised that the issues exist and need to be addressed. There are, however, no standard processes; instead there are ad hoc approaches that tend to be applied on an individual or case-by-case basis."],
                    2 => ['name' => 'Repeatable but Intuitive', 'desc' => "Processes have developed to the stage where similar procedures are followed by different people undertaking the same task. There is no formal training or communication of standard procedures, and responsibility is left to the individual."],
                    3 => ['name' => 'Defined', 'desc' => "Procedures have been standardised and documented, and communicated through training. It is mandated that these processes should be followed; however, it is unlikely that deviations will be detected."],
                    4 => ['name' => 'Managed and Measurable', 'desc' => "Management monitors and measures compliance with procedures and takes action where processes appear not to be working effectively. Processes are under constant improvement and provide good practice."],
                    5 => ['name' => 'Optimised', 'desc' => "Processes have been refined to a level of good practice, based on the results of continuous improvement and maturity modelling with other enterprises. IT is used in an integrated way to automate the workflow."],
                ],
            ],
        ];
    }

    /**
     * Return list of all 34 authentic COBIT 4.1 processes.
     */
    public function getMasterProcesses(): array
    {
        return [
            // Plan and Organise (PO)
            ['id' => 'PO01.M27', 'code' => 'PO1', 'domain' => 'PO', 'title' => 'Define a Strategic IT Plan', 'desc' => "IT strategic planning is required to manage and direct all IT resources in line with the business strategy and priorities."],
            ['id' => 'PO02.M27', 'code' => 'PO2', 'domain' => 'PO', 'title' => 'Define the Information Architecture', 'desc' => "The information systems function creates and regularly updates a business information model and defines the appropriate systems to optimise the use of this information."],
            ['id' => 'PO03.M27', 'code' => 'PO3', 'domain' => 'PO', 'title' => 'Determine Technological Direction', 'desc' => "The information services function determines the technological direction to support the business."],
            ['id' => 'PO04.M27', 'code' => 'PO4', 'domain' => 'PO', 'title' => 'Define the IT Processes, Organisation and Relationships', 'desc' => "An IT organisation is established by considering requirements for staff, skills, functions, authorities, roles and responsibilities."],
            ['id' => 'PO05.M27', 'code' => 'PO5', 'domain' => 'PO', 'title' => 'Manage the IT Investment', 'desc' => "Establishing and maintaining a budgeting and accounting process to manage and monitor IT investment and spending."],
            ['id' => 'PO06.M27', 'code' => 'PO6', 'domain' => 'PO', 'title' => 'Communicate Management Aims and Direction', 'desc' => "Management develops an enterprise control environment and communicates management direction."],
            ['id' => 'PO07.M27', 'code' => 'PO7', 'domain' => 'PO', 'title' => 'Manage IT Human Resources', 'desc' => "Acquiring, maintaining and motivating a competent workforce for the creation and delivery of IT services."],
            ['id' => 'PO08.M27', 'code' => 'PO8', 'domain' => 'PO', 'title' => 'Manage Quality', 'desc' => "A quality management system (QMS) is established and maintained to ensure continuous quality improvement."],
            ['id' => 'PO09.M27', 'code' => 'PO9', 'domain' => 'PO', 'title' => 'Assess and Manage IT Risks', 'desc' => "A risk management framework is created and maintained to identify, evaluate and manage IT-related risks."],
            ['id' => 'PO10.M27', 'code' => 'PO10', 'domain' => 'PO', 'title' => 'Manage Projects', 'desc' => "Establishing a programme and project management framework for IT projects to deliver within scope, budget and time."],

            // Acquire and Implement (AI)
            ['id' => 'AI01.M27', 'code' => 'AI1', 'domain' => 'AI', 'title' => 'Identify Automated Solutions', 'desc' => "Analysing business requirements and specifying feasible automated solutions to meet business needs."],
            ['id' => 'AI02.M27', 'code' => 'AI2', 'domain' => 'AI', 'title' => 'Acquire and Maintain Application Software', 'desc' => "Applications are made available in line with business requirements through custom development or acquisition."],
            ['id' => 'AI03.M27', 'code' => 'AI3', 'domain' => 'AI', 'title' => 'Acquire and Maintain Technology Infrastructure', 'desc' => "Providing suitable hardware, system software and network infrastructure to support business applications."],
            ['id' => 'AI04.M27', 'code' => 'AI4', 'domain' => 'AI', 'title' => 'Enable Operation and Use', 'desc' => "Providing documentation, user manuals and training materials to enable proper operation and use of applications."],
            ['id' => 'AI05.M27', 'code' => 'AI5', 'domain' => 'AI', 'title' => 'Procure IT Resources', 'desc' => "Improving IT resource acquisition through proper vendor evaluation, contracting and procurement processes."],
            ['id' => 'AI06.M27', 'code' => 'AI6', 'domain' => 'AI', 'title' => 'Manage Changes', 'desc' => "All changes to information systems and infrastructure are assessed, approved and implemented in a controlled manner."],
            ['id' => 'AI07.M27', 'code' => 'AI7', 'domain' => 'AI', 'title' => 'Install and Accredit Solutions and Changes', 'desc' => "Testing and accreditation of new systems and changes prior to release into production operations."],

            // Deliver and Support (DS)
            ['id' => 'DS01.M27', 'code' => 'DS1', 'domain' => 'DS', 'title' => 'Define and Manage Service Levels', 'desc' => "Communication between IT and customers is maintained through formal service level agreements (SLAs)."],
            ['id' => 'DS02.M27', 'code' => 'DS2', 'domain' => 'DS', 'title' => 'Manage Third-party Services', 'desc' => "Establishing relationships with external service providers and monitoring vendor service delivery."],
            ['id' => 'DS03.M27', 'code' => 'DS3', 'domain' => 'DS', 'title' => 'Manage Performance and Capacity', 'desc' => "Ensuring IT capacity and resources are sufficient to satisfy current and future service requirements."],
            ['id' => 'DS04.M27', 'code' => 'DS4', 'domain' => 'DS', 'title' => 'Ensure Continuous Service', 'desc' => "Developing IT continuity plans to ensure resilience and recovery in the event of major disruptions."],
            ['id' => 'DS05.M27', 'code' => 'DS5', 'domain' => 'DS', 'title' => 'Ensure Systems Security', 'desc' => "Maintaining the integrity of information and protection of IT assets against unauthorised access."],
            ['id' => 'DS06.M27', 'code' => 'DS6', 'domain' => 'DS', 'title' => 'Identify and Allocate Costs', 'desc' => "A fair and equitable system of identifying and allocating IT costs to business users."],
            ['id' => 'DS07.M27', 'code' => 'DS7', 'domain' => 'DS', 'title' => 'Educate and Train Users', 'desc' => "Organising structured training and education for users to maximise effective and secure use of IT systems."],
            ['id' => 'DS08.M27', 'code' => 'DS8', 'domain' => 'DS', 'title' => 'Manage Service Desk and Incidents', 'desc' => "Timely and effective response to user queries, problems and incident resolution via service desk."],
            ['id' => 'DS09.M27', 'code' => 'DS9', 'domain' => 'DS', 'title' => 'Manage the Configuration', 'desc' => "Maintaining accurate records of configuration items (CIs) and relationships across IT infrastructure."],
            ['id' => 'DS10.M27', 'code' => 'DS10', 'domain' => 'DS', 'title' => 'Manage Problems', 'desc' => "Identifying root causes of incidents and preventing recurrence of IT service disruptions."],
            ['id' => 'DS11.M27', 'code' => 'DS11', 'domain' => 'DS', 'title' => 'Manage Data', 'desc' => "Ensuring that data remains complete, accurate and valid throughout its lifecycle and backup operations."],
            ['id' => 'DS12.M27', 'code' => 'DS12', 'domain' => 'DS', 'title' => 'Manage the Physical Environment', 'desc' => "Providing physical security and environmental protection for computer facilities against physical threats."],
            ['id' => 'DS13.M27', 'code' => 'DS13', 'domain' => 'DS', 'title' => 'Manage Operations', 'desc' => "Ensuring that daily operational tasks, job scheduling and system maintenance are carried out reliably."],

            // Monitor and Evaluate (ME)
            ['id' => 'ME01.M27', 'code' => 'ME1', 'domain' => 'ME', 'title' => 'Monitor and Evaluate IT Performance', 'desc' => "Establishing an IT performance monitoring framework and periodic reporting against goals."],
            ['id' => 'ME02.M27', 'code' => 'ME2', 'domain' => 'ME', 'title' => 'Monitor and Evaluate Internal Control', 'desc' => "Assessing the effectiveness of the internal control environment for IT operations and governance."],
            ['id' => 'ME03.M27', 'domain' => 'ME', 'code' => 'ME3', 'title' => 'Ensure Regulatory Compliance', 'desc' => "Ensuring compliance of IT processes with applicable laws, statutory, and contractual requirements."],
            ['id' => 'ME04.M27', 'code' => 'ME4', 'domain' => 'ME', 'title' => 'Provide IT Governance', 'desc' => "Establishing an effective governance framework aligned with COBIT principles and board oversight."],
        ];
    }
}
