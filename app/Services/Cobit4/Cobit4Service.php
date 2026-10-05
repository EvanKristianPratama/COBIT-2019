<?php

namespace App\Services\Cobit4;

use App\Models\MstObjective;
use App\Models\MstFocusArea;
use Illuminate\Support\Facades\DB;

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
     * Get full authentic structured data for a process.
     */
    public function getProcessData(string $processCode, ?MstObjective $objective = null): array
    {
        $code = strtoupper(trim($processCode));

        // 1. Check if user has saved custom data in storage
        $overridePath = storage_path("app/cobit4/{$code}.json");
        if (file_exists($overridePath)) {
            $saved = json_decode(file_get_contents($overridePath), true);
            if (is_array($saved) && !empty($saved['code'])) {
                return $saved;
            }
        }

        // 2. Check if baseline authentic config exists
        $configData = config("cobit4-data.processes.{$code}");
        if ($configData) {
            return $configData;
        }

        // 3. Fallback generator for other COBIT 4.1 processes
        return $this->generateFallbackProcessData($code, $objective);
    }

    /**
     * Save custom COBIT 4.1 process data.
     */
    public function saveProcessData(string $processCode, array $data, ?string $objectiveId = null, int $focusAreaId = 27): bool
    {
        $code = strtoupper(trim($processCode));
        $dir = storage_path('app/cobit4');
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents("{$dir}/{$code}.json", json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        // Also update mst_objective table
        if ($objectiveId) {
            $obj = MstObjective::where('objective_id', $objectiveId)
                ->where('focus_area_id', $focusAreaId)
                ->first();
            if ($obj) {
                if (!empty($data['title'])) $obj->objective = $data['title'];
                if (!empty($data['description'])) $obj->objective_description = $data['description'];
                $obj->save();
            }
        }

        return true;
    }

    /**
     * Reset custom COBIT 4.1 process data back to ISACA baseline.
     */
    public function resetProcessData(string $processCode, ?string $objectiveId = null, int $focusAreaId = 27): bool
    {
        $code = strtoupper(trim($processCode));
        $file = storage_path("app/cobit4/{$code}.json");
        if (file_exists($file)) {
            unlink($file);
        }

        // Reset mst_objective to baseline if available
        $baseline = config("cobit4-data.processes.{$code}");
        if ($baseline && $objectiveId) {
            $obj = MstObjective::where('objective_id', $objectiveId)
                ->where('focus_area_id', $focusAreaId)
                ->first();
            if ($obj) {
                $obj->objective = $baseline['title'];
                $obj->objective_description = $baseline['description'];
                $obj->save();
            }
        }

        return true;
    }

    /**
     * Generate fallback authentic COBIT 4.1 structure when specific process data is not yet in config.
     */
    protected function generateFallbackProcessData(string $code, ?MstObjective $objective = null): array
    {
        $domainPrefix = preg_replace('/[0-9]/', '', $code);
        $domainMap = [
            'PO' => 'Plan and Organise',
            'AI' => 'Acquire and Implement',
            'DS' => 'Deliver and Support',
            'ME' => 'Monitor and Evaluate',
        ];
        $domainName = $domainMap[$domainPrefix] ?? 'Plan and Organise';

        $title = $objective ? $objective->objective : "Process {$code}";
        $desc = $objective && $objective->objective_description
            ? $objective->objective_description
            : "Management of the IT process of {$title} in accordance with COBIT 4.1 control and governance objectives.";

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

    /**
     * Ensure all 34 authentic COBIT 4.1 processes exist in Focus Area 27.
     */
    public function ensureCobit4Objectives(int $focusAreaId = 27): array
    {
        $focusArea = MstFocusArea::find($focusAreaId);
        if (!$focusArea) {
            return ['added' => 0, 'existing' => 0];
        }

        $masterList = $this->getMasterProcesses();
        $added = 0;
        $existing = 0;

        foreach ($masterList as $item) {
            // Check if objective already exists in focus area (by ID or prefix)
            $obj = MstObjective::where('focus_area_id', $focusAreaId)
                ->where(function ($q) use ($item) {
                    $q->where('objective_id', $item['id'])
                      ->orWhere('objective_id', $item['code'])
                      ->orWhere('objective_id', 'LIKE', $item['code'] . '.%');
                })->first();

            if ($obj) {
                $existing++;
                // Ensure title and description are clean and authentic
                if ($obj->objective !== $item['title']) {
                    $obj->objective = $item['title'];
                    $obj->objective_description = $item['desc'];
                    $obj->save();
                }
                continue;
            }

            MstObjective::create([
                'objective_id' => $item['id'],
                'focus_area_id' => $focusAreaId,
                'objective' => $item['title'],
                'objective_description' => $item['desc'],
                'objective_purpose' => 'COBIT 4.1 Control Objectives and Management Guidelines',
            ]);
            $added++;
        }

        return ['added' => $added, 'existing' => $existing];
    }
}
