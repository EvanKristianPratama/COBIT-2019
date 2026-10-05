<?php

return [
    /*
    |--------------------------------------------------------------------------
    | COBIT 4.1 Process Master Data
    |--------------------------------------------------------------------------
    |
    | Struktur data autentik COBIT 4.1 sesuai standar resmi ISACA:
    | 1. Process Description (Information Criteria, Control Statement Flow, IT Gov Focus, IT Resources)
    | 2. Control Objectives (PO1.1 s.d. PO1.6, dst.)
    | 3. Management Guidelines (Inputs & Outputs, RACI Chart, Goals and Metrics)
    | 4. Maturity Model (Level 0 s.d. Level 5)
    |
    */

    'domains' => [
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
    ],

    'processes' => [
        // =========================================================================
        // PO1 - Define a Strategic IT Plan
        // =========================================================================
        'PO1' => [
            'code' => 'PO1',
            'domain_code' => 'PO',
            'domain_name' => 'Plan and Organise',
            'title' => 'Define a Strategic IT Plan',
            'description' => "IT strategic planning is required to manage and direct all IT resources in line with the business strategy and priorities. The IT function and business stakeholders are responsible for ensuring that optimal value is realised from project and service portfolios. The strategic plan improves key stakeholders' understanding of IT opportunities and limitations, assesses current performance, identifies capacity and human resource requirements, and clarifies the level of investment required. The business strategy and priorities are to be reflected in portfolios and executed by the IT tactical plan(s), which specifies concise objectives, action plans and tasks that are understood and accepted by both business and IT.",

            // Page 1: Information Criteria
            'information_criteria' => [
                'effectiveness' => 'P',
                'efficiency' => 'S',
                'confidentiality' => '',
                'integrity' => '',
                'availability' => '',
                'compliance' => '',
                'reliability' => '',
            ],

            // Page 1: Control Statement Cascading Flow
            'control_statement' => [
                'control_over' => 'Define a strategic IT plan',
                'satisfies_requirement' => 'sustaining or extending the business strategy and governance requirements whilst being transparent about benefits, costs and risks',
                'focusing_on' => 'incorporating IT and business management in the translation of business requirements into service offerings, and the development of strategies to deliver these services in a transparent and effective manner',
                'achieved_by' => [
                    'Engaging with business and senior management in aligning IT strategic planning with current and future business needs',
                    'Understanding current IT capabilities',
                    'Providing for a prioritisation scheme for the business objectives that quantifies the business requirements',
                ],
                'measured_by' => [
                    'Percent of IT objectives in the IT strategic plan that support the strategic business plan',
                    'Percent of IT projects in the IT project portfolio that can be directly traced back to the IT tactical plans',
                    'Delay between updates of IT strategic plan and updates of IT tactical plans',
                ],
            ],

            // Page 1: IT Governance Focus Areas (Pentagon)
            'it_governance_focus' => [
                'strategic_alignment' => 'Primary',
                'value_delivery' => 'Secondary',
                'risk_management' => '',
                'resource_management' => '',
                'performance_measurement' => '',
            ],

            // Page 1: IT Resources Impacted
            'it_resources' => [
                'applications' => true,
                'information' => true,
                'infrastructure' => true,
                'people' => true,
            ],

            // Page 2: Control Objectives
            'control_objectives' => [
                [
                    'code' => 'PO1.1',
                    'title' => 'IT Value Management',
                    'desc' => 'Work with the business to ensure that the enterprise portfolio of IT-enabled investments contains programmes that have solid business cases. Recognise that there are mandatory, sustaining and discretionary investments that differ in complexity and degree of freedom in allocating funds. IT processes should provide effective and efficient delivery of the IT components of programmes and early warning of any deviations from plan, including cost, schedule or functionality, that might impact the expected outcomes of the programmes. IT services should be executed against equitable and enforceable service level agreements (SLAs). Accountability for achieving the benefits and controlling the costs should be clearly assigned and monitored. Establish fair, transparent, repeatable and comparable evaluation of business cases, including financial worth, the risk of not delivering a capability and the risk of not realising the expected benefits.',
                ],
                [
                    'code' => 'PO1.2',
                    'title' => 'Business-IT Alignment',
                    'desc' => 'Establish processes of bi-directional education and reciprocal involvement in strategic planning to achieve business and IT alignment and integration. Mediate between business and IT imperatives so priorities can be mutually agreed.',
                ],
                [
                    'code' => 'PO1.3',
                    'title' => 'Assessment of Current Capability and Performance',
                    'desc' => "Assess the current capability and performance of solution and service delivery to establish a baseline against which future requirements can be compared. Define performance in terms of IT's contribution to business objectives, functionality, stability, complexity, costs, strengths and weaknesses.",
                ],
                [
                    'code' => 'PO1.4',
                    'title' => 'IT Strategic Plan',
                    'desc' => "Create a strategic plan that defines, in co-operation with relevant stakeholders, how IT goals will contribute to the enterprise's strategic objectives and related costs and risks. It should include how IT will support IT-enabled investment programmes, IT services and IT assets. It should define how the objectives will be met, the measurements to be used and the procedures to obtain formal sign-off from the stakeholders. The IT strategic plan should cover investment/operational budget, funding sources, sourcing strategy, acquisition strategy, and legal and regulatory requirements. The strategic plan should be sufficiently detailed to allow for the definition of tactical IT plans.",
                ],
                [
                    'code' => 'PO1.5',
                    'title' => 'IT Tactical Plans',
                    'desc' => 'Create a portfolio of tactical IT plans that are derived from the IT strategic plan. The tactical plans should address IT-enabled programme investments, IT services and IT assets. The tactical plans should describe required IT initiatives, resource requirements, and how the use of resources and achievement of benefits will be monitored and managed. The tactical plans should be sufficiently detailed to allow the definition of project plans. Actively manage the set of tactical IT plans and initiatives through analysis of project and service portfolios.',
                ],
                [
                    'code' => 'PO1.6',
                    'title' => 'IT Portfolio Management',
                    'desc' => 'Actively manage with the business the portfolio of IT-enabled investment programmes required to achieve specific strategic business objectives by identifying, defining, evaluating, prioritising, selecting, initiating, managing and controlling programmes. This should include clarifying desired business outcomes, ensuring that programme objectives support achievement of the outcomes, understanding the full scope of effort required to achieve the outcomes, assigning clear accountability with supporting measures, defining projects within the programme, allocating resources and funding, delegating authority, and commissioning required projects at programme launch.',
                ],
            ],

            // Page 3: Management Guidelines
            'management_guidelines' => [
                'inputs' => [
                    ['from' => 'PO5', 'input' => 'Cost-benefits reports'],
                    ['from' => 'PO9', 'input' => 'Risk assessment'],
                    ['from' => 'PO10', 'input' => 'Updated IT project portfolio'],
                    ['from' => 'DS1', 'input' => 'New/updated service requirements; updated IT service portfolio'],
                    ['from' => '*', 'input' => 'Business strategy and priorities'],
                    ['from' => '*', 'input' => 'Programme portfolio'],
                    ['from' => 'ME1', 'input' => 'Performance input to IT planning'],
                    ['from' => 'ME4', 'input' => 'Report on IT governance status; enterprise strategic direction for IT'],
                ],
                'outputs' => [
                    ['output' => 'Strategic IT plan', 'to' => 'PO2...PO6, PO8, PO9, AI1, DS1'],
                    ['output' => 'Tactical IT plans', 'to' => 'PO2...PO6, PO9, AI1, DS1'],
                    ['output' => 'IT project portfolio', 'to' => 'PO5, PO6, PO10, AI6'],
                    ['output' => 'IT service portfolio', 'to' => 'PO5, PO6, PO9, DS1'],
                    ['output' => 'IT sourcing strategy', 'to' => 'DS2'],
                    ['output' => 'IT acquisition strategy', 'to' => 'AI5'],
                ],
                'raci' => [
                    'roles' => [
                        'CEO',
                        'CFO',
                        'Business Executive',
                        'CIO',
                        'Business Process Owner',
                        'Head Operations',
                        'Chief Architect',
                        'Head Development',
                        'Head IT Administration',
                        'PMO',
                        'Compliance, Audit, Risk and Security',
                    ],
                    'activities' => [
                        [
                            'activity' => 'Link business goals to IT goals.',
                            'raci' => ['C', 'I', 'A/R', 'R', 'C', '', '', '', '', '', ''],
                        ],
                        [
                            'activity' => 'Identify critical dependencies and current performance.',
                            'raci' => ['C', 'C', 'R', 'A/R', 'C', 'C', 'C', 'C', 'C', '', 'C'],
                        ],
                        [
                            'activity' => 'Build an IT strategic plan.',
                            'raci' => ['A', 'C', 'C', 'R', 'I', 'C', 'C', 'C', 'C', 'I', 'C'],
                        ],
                        [
                            'activity' => 'Build IT tactical plans.',
                            'raci' => ['C', 'I', '', 'A', 'C', 'C', 'C', 'C', 'C', 'R', 'I'],
                        ],
                        [
                            'activity' => 'Analyse programme portfolios and manage project and service portfolios.',
                            'raci' => ['C', 'I', 'I', 'A', 'R', 'R', 'C', 'R', 'C', 'C', 'I'],
                        ],
                    ],
                ],
                'goals_and_metrics' => [
                    'it_goals' => [
                        'Respond to business requirements in alignment with the business strategy.',
                        'Respond to governance requirements in line with board direction.',
                    ],
                    'it_metrics' => [
                        'Degree of approval of business owners of the IT strategic/tactical plans',
                        'Degree of compliance with business and governance requirements',
                        'Level of business satisfaction with the current state (number, scope, etc.) of the project and applications portfolio',
                    ],
                    'process_goals' => [
                        'Define how business requirements are translated in service offerings.',
                        'Define the strategy to deliver service offerings.',
                        'Contribute to the management of the portfolio of IT-enabled business investments.',
                        'Establish clarity regarding the business impact of risks on IT objectives and resources.',
                        'Provide transparency and understanding of IT costs, benefits, strategy, policies and service levels.',
                    ],
                    'process_metrics' => [
                        'Percent of IT objectives in the IT strategic plan that support the strategic business plan',
                        'Percent of IT initiatives in the IT tactical plan that support the tactical business plans',
                        'Percent of IT projects in the IT project portfolio that can be directly traced back to the IT tactical plans',
                    ],
                    'activities_goals' => [
                        'Engaging with business and senior management in aligning IT strategic planning with current and future business needs',
                        'Understanding current IT capabilities',
                        'Providing for a prioritisation scheme for the business objectives that quantifies the business requirements',
                        'Translating IT strategic planning into tactical plans',
                    ],
                    'activities_metrics' => [
                        'Delay between updates of business strategic/tactical plans and updates of IT strategic/tactical plans',
                        'Percent of strategic/tactical IT plans meetings where business representatives have actively participated',
                        'Delay between updates of IT strategic plan and updates of IT tactical plans',
                        'Percent of tactical IT plans complying with the predefined structure/contents of those plans',
                        'Percent of IT initiatives/projects championed by business owners',
                    ],
                ],
            ],

            // Page 4: Maturity Model
            'maturity_model' => [
                'intro' => 'Management of the process of Define a strategic IT plan that satisfies the business requirement for IT of sustaining or extending the business strategy and governance requirements whilst being transparent about benefits, costs and risks is:',
                'levels' => [
                    0 => [
                        'name' => 'Non-existent',
                        'desc' => 'IT strategic planning is not performed. There is no management awareness that IT strategic planning is needed to support business goals.',
                    ],
                    1 => [
                        'name' => 'Initial/Ad Hoc',
                        'desc' => 'The need for IT strategic planning is known by IT management. IT planning is performed on an as-needed basis in response to a specific business requirement. IT strategic planning is occasionally discussed at IT management meetings. The alignment of business requirements, applications and technology takes place reactively rather than by an organisationwide strategy. The strategic risk position is identified informally on a project-by-project basis.',
                    ],
                    2 => [
                        'name' => 'Repeatable but Intuitive',
                        'desc' => 'IT strategic planning is shared with business management on an as-needed basis. Updating of the IT plans occurs in response to requests by management. Strategic decisions are driven on a project-by-project basis without consistency with an overall organisation strategy. The risks and user benefits of major strategic decisions are recognised in an intuitive way.',
                    ],
                    3 => [
                        'name' => 'Defined',
                        'desc' => 'A policy defines when and how to perform IT strategic planning. IT strategic planning follows a structured approach that is documented and known to all staff. The IT planning process is reasonably sound and ensures that appropriate planning is likely to be performed. However, discretion is given to individual managers with respect to implementation of the process, and there are no procedures to examine the process. The overall IT strategy includes a consistent definition of risks that the organisation is willing to take as an innovator or follower. The IT financial, technical and human resources strategies increasingly influence the acquisition of new products and technologies. IT strategic planning is discussed at business management meetings.',
                    ],
                    4 => [
                        'name' => 'Managed and Measurable',
                        'desc' => 'IT strategic planning is standard practice and exceptions would be noticed by management. IT strategic planning is a defined management function with senior-level responsibilities. Management is able to monitor the IT strategic planning process, make informed decisions based on it and measure its effectiveness. Both short-range and long-range IT planning occurs and is cascaded down into the organisation, with updates done as needed. The IT strategy and organisationwide strategy are increasingly becoming more co-ordinated by addressing business processes and value-added capabilities and leveraging the use of applications and technologies through business process re-engineering. There is a well-defined process for determining the usage of internal and external resources required in system development and operations.',
                    ],
                    5 => [
                        'name' => 'Optimised',
                        'desc' => 'IT strategic planning is a documented, living process; is continuously considered in business goal setting; and results in discernible business value through investments in IT. Risk and value-added considerations are continuously updated in the IT strategic planning process. Realistic long-range IT plans are developed and constantly updated to reflect changing technology and business-related developments. Benchmarking against well-understood and reliable industry norms takes place and is integrated with the strategy formulation process. The strategic plan includes how new technology developments can drive the creation of new business capabilities and improve the competitive advantage of the organisation.',
                    ],
                ],
            ],
        ],

        // =========================================================================
        // PO2 - Define the Information Architecture
        // =========================================================================
        'PO2' => [
            'code' => 'PO2',
            'domain_code' => 'PO',
            'domain_name' => 'Plan and Organise',
            'title' => 'Define the Information Architecture',
            'description' => "The information systems function creates and regularly updates a business information model and defines the appropriate systems to optimise the use of this information. This encompasses the development of a corporate data dictionary with the organisation's data syntax rules, data classification scheme and security levels. This process improves the quality of management decision making by making sure that reliable and secure information is provided, and it enables rationalising information systems resources to appropriately match business strategies. This IT process is also needed to increase accountability for the integrity and security of data and to enhance the effectiveness and control of sharing information across applications and entities.",
            'information_criteria' => [
                'effectiveness' => 'P',
                'efficiency' => '',
                'confidentiality' => '',
                'integrity' => 'P',
                'availability' => 'S',
                'compliance' => '',
                'reliability' => '',
            ],
            'control_statement' => [
                'control_over' => 'Define the information architecture',
                'satisfies_requirement' => 'optimising the use of information and ensuring that reliable and secure information is provided to the business',
                'focusing_on' => 'establishing an enterprise data model, a data dictionary and data security rules',
                'achieved_by' => [
                    'Establishing an enterprise data classification scheme',
                    'Maintaining an enterprise information model and data dictionary',
                    'Assigning ownership and accountability for data',
                ],
                'measured_by' => [
                    'Percent of applications that share data models',
                    'Number of business applications with redundant data structures',
                    'Frequency of data dictionary updates and reviews',
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
                'infrastructure' => false,
                'people' => true,
            ],
            'control_objectives' => [
                [
                    'code' => 'PO2.1',
                    'title' => 'Enterprise Information Architecture Model',
                    'desc' => 'Establish and maintain an enterprise information architecture model to create an integrated corporate data dictionary and ensure consistency and integration across all business systems.',
                ],
                [
                    'code' => 'PO2.2',
                    'title' => 'Enterprise Data Dictionary and Data Syntax Rules',
                    'desc' => 'Maintain the enterprise data dictionary and define corporate data syntax rules to ensure uniform interpretation and understanding of data across the enterprise.',
                ],
                [
                    'code' => 'PO2.3',
                    'title' => 'Data Classification Scheme',
                    'desc' => 'Establish a data classification scheme that applies throughout the enterprise, based on the criticality and sensitivity (e.g., public, confidential, secret) of the data.',
                ],
            ],
            'management_guidelines' => [
                'inputs' => [
                    ['from' => 'PO1', 'input' => 'Strategic IT plan'],
                    ['from' => 'DS11', 'input' => 'Data dictionary updates'],
                ],
                'outputs' => [
                    ['output' => 'Enterprise information architecture model', 'to' => 'AI1, AI2, DS11'],
                    ['output' => 'Data classification scheme', 'to' => 'DS5, DS11'],
                ],
                'raci' => [
                    'roles' => ['CEO', 'CFO', 'Business Executive', 'CIO', 'Business Process Owner', 'Head Operations', 'Chief Architect', 'Head Development', 'Head IT Administration', 'PMO', 'Compliance, Audit, Risk and Security'],
                    'activities' => [
                        ['activity' => 'Develop and update enterprise data architecture.', 'raci' => ['', '', 'C', 'A', 'C', '', 'R', 'C', '', '', 'C']],
                        ['activity' => 'Define data classification and security levels.', 'raci' => ['', '', 'C', 'A', 'R', '', 'C', '', '', '', 'C']],
                    ],
                ],
                'goals_and_metrics' => [
                    'it_goals' => ['Deliver reliable, secure, consistent and accessible information.'],
                    'it_metrics' => ['Percent of business decisions supported by validated information.'],
                    'process_goals' => ['Establish and maintain a corporate data architecture.'],
                    'process_metrics' => ['Coverage of corporate data dictionary across enterprise databases.'],
                    'activities_goals' => ['Maintain accurate corporate data models.'],
                    'activities_metrics' => ['Number of data inconsistencies reported across applications.'],
                ],
            ],
            'maturity_model' => [
                'intro' => 'Management of the process of Define the Information Architecture is:',
                'levels' => [
                    0 => ['name' => 'Non-existent', 'desc' => 'There is no awareness of the need for an enterprise information architecture.'],
                    1 => ['name' => 'Initial/Ad Hoc', 'desc' => 'Information architecture is defined in an ad hoc, application-by-application manner.'],
                    2 => ['name' => 'Repeatable but Intuitive', 'desc' => 'Similar data structures are used across similar projects, but no central standard exists.'],
                    3 => ['name' => 'Defined', 'desc' => 'An enterprise information architecture model and data dictionary are formally defined and maintained.'],
                    4 => ['name' => 'Managed and Measurable', 'desc' => 'The enterprise information architecture is systematically updated, monitored and enforced.'],
                    5 => ['name' => 'Optimised', 'desc' => 'The information architecture is dynamically tuned and continuously optimised with business process changes.'],
                ],
            ],
        ],
    ],
];
