<?php

return [
    'domain_order' => ['EDM', 'APO', 'BAI', 'DSS', 'MEA'],
    'domains' => [
        'APO' => [
            'title' => 'Align, Plan and Organise',
            'processes' => [
                'APO01' => [
                    'title' => 'Manage the IT Management Framework',
                    'practices' => [
                        ['code' => 'APO01.01', 'title' => 'Define the organisational structure.'],
                        ['code' => 'APO01.02', 'title' => 'Establish roles and responsibilities.'],
                        ['code' => 'APO01.03', 'title' => 'Maintain the enablers of the management system.'],
                        ['code' => 'APO01.04', 'title' => 'Communicate management objectives and direction.'],
                        ['code' => 'APO01.05', 'title' => 'Optimise the placement of the IT function.'],
                        ['code' => 'APO01.06', 'title' => 'Define information (data) and system ownership.'],
                        ['code' => 'APO01.07', 'title' => 'Manage continual improvement of processes.'],
                        ['code' => 'APO01.08', 'title' => 'Maintain compliance with policies and procedures.'],
                    ],
                ],
                'APO02' => [
                    'title' => 'Manage Strategy',
                    'practices' => [
                        ['code' => 'APO02.01', 'title' => 'Understand enterprise direction.'],
                        ['code' => 'APO02.02', 'title' => 'Assess the current environment, capabilities and performance.'],
                        ['code' => 'APO02.03', 'title' => 'Define the target IT capabilities.'],
                        ['code' => 'APO02.04', 'title' => 'Conduct a gap analysis.'],
                        ['code' => 'APO02.05', 'title' => 'Define the strategic plan and road map.'],
                        ['code' => 'APO02.06', 'title' => 'Communicate the IT strategy and direction.'],
                    ],
                ],
                'APO03' => [
                    'title' => 'Manage Enterprise Architecture',
                    'practices' => [
                        ['code' => 'APO03.01', 'title' => 'Develop the enterprise architecture vision.'],
                        ['code' => 'APO03.02', 'title' => 'Define reference architecture.'],
                        ['code' => 'APO03.03', 'title' => 'Select opportunities and solutions.'],
                        ['code' => 'APO03.04', 'title' => 'Define architecture implementation.'],
                        ['code' => 'APO03.05', 'title' => 'Provide enterprise architecture services.'],
                    ],
                ],
                'APO04' => [
                    'title' => 'Manage Innovation',
                    'practices' => [
                        ['code' => 'APO04.01', 'title' => 'Create an environment conducive to innovation.'],
                        ['code' => 'APO04.02', 'title' => 'Maintain an understanding of the enterprise environment.'],
                        ['code' => 'APO04.03', 'title' => 'Monitor and scan the technology environment.'],
                        ['code' => 'APO04.04', 'title' => 'Assess the potential of emerging technologies and innovation ideas.'],
                        ['code' => 'APO04.05', 'title' => 'Recommend appropriate further initiatives.'],
                        ['code' => 'APO04.06', 'title' => 'Monitor the implementation and use of innovation.'],
                    ],
                ],
                'APO05' => [
                    'title' => 'Manage Portfolio',
                    'practices' => [
                        ['code' => 'APO05.01', 'title' => 'Establish the target investment mix.'],
                        ['code' => 'APO05.02', 'title' => 'Determine the availability and sources of funds.'],
                        ['code' => 'APO05.03', 'title' => 'Evaluate and select programmes to fund.'],
                        ['code' => 'APO05.04', 'title' => 'Monitor, optimise and report on investment portfolio performance.'],
                        ['code' => 'APO05.05', 'title' => 'Maintain portfolios.'],
                        ['code' => 'APO05.06', 'title' => 'Manage benefits achievement.'],
                    ],
                ],
                'APO06' => [
                    'title' => 'Manage Budget and Costs',
                    'practices' => [
                        ['code' => 'APO06.01', 'title' => 'Manage finance and accounting.'],
                        ['code' => 'APO06.02', 'title' => 'Prioritise resource allocation.'],
                        ['code' => 'APO06.03', 'title' => 'Create and maintain budgets.'],
                        ['code' => 'APO06.04', 'title' => 'Model and allocate costs.'],
                        ['code' => 'APO06.05', 'title' => 'Manage costs.'],
                    ],
                ],
                'APO07' => [
                    'title' => 'Manage Human Resources',
                    'practices' => [
                        ['code' => 'APO07.01', 'title' => 'Maintain adequate and appropriate staffing.'],
                        ['code' => 'APO07.02', 'title' => 'Identify key IT personnel.'],
                        ['code' => 'APO07.03', 'title' => 'Maintain the skills and competencies of personnel.'],
                        ['code' => 'APO07.04', 'title' => 'Evaluate employee job performance.'],
                        ['code' => 'APO07.05', 'title' => 'Plan and track the usage of IT and business human resources.'],
                        ['code' => 'APO07.06', 'title' => 'Manage contract staff.'],
                    ],
                ],
                'APO08' => [
                    'title' => 'Manage Relationships',
                    'practices' => [
                        ['code' => 'APO08.01', 'title' => 'Understand business expectations.'],
                        ['code' => 'APO08.02', 'title' => 'Identify opportunities, risk and constraints for IT to enhance the business.'],
                        ['code' => 'APO08.03', 'title' => 'Manage the business relationship.'],
                        ['code' => 'APO08.04', 'title' => 'Co-ordinate and communicate.'],
                        ['code' => 'APO08.05', 'title' => 'Provide input to the continual improvement of services.'],
                    ],
                ],
                'APO09' => [
                    'title' => 'Manage Service Agreements',
                    'practices' => [
                        ['code' => 'APO09.01', 'title' => 'Identify IT services.'],
                        ['code' => 'APO09.02', 'title' => 'Catalogue IT-enabled services.'],
                        ['code' => 'APO09.03', 'title' => 'Define and prepare service agreements.'],
                        ['code' => 'APO09.04', 'title' => 'Monitor and report service levels.'],
                        ['code' => 'APO09.05', 'title' => 'Review service agreements and contracts.'],
                        ['code' => 'APO09.06', 'title' => 'Code shown in the supplied source; verify against COBIT 5.', 'review_note' => 'APO09.06 is shown in the supplied mapping image but is not listed as a COBIT 5 APO09 practice.'],
                    ],
                ],
                'APO10' => [
                    'title' => 'Manage Suppliers',
                    'practices' => [
                        ['code' => 'APO10.01', 'title' => 'Identify and evaluate supplier relationships and contracts.'],
                        ['code' => 'APO10.02', 'title' => 'Select suppliers.'],
                        ['code' => 'APO10.03', 'title' => 'Manage supplier relationships and contracts.'],
                        ['code' => 'APO10.04', 'title' => 'Manage supplier risk.'],
                        ['code' => 'APO10.05', 'title' => 'Monitor supplier performance and compliance.'],
                    ],
                ],
                'APO11' => [
                    'title' => 'Manage Quality',
                    'practices' => [
                        ['code' => 'APO11.01', 'title' => 'Establish a quality management system (QMS).'],
                        ['code' => 'APO11.02', 'title' => 'Define and manage quality standards, practices and procedures.'],
                        ['code' => 'APO11.03', 'title' => 'Focus quality management on customers.'],
                        ['code' => 'APO11.04', 'title' => 'Perform quality monitoring, control and reviews.'],
                        ['code' => 'APO11.05', 'title' => 'Integrate quality management into solutions for development and service delivery.'],
                        ['code' => 'APO11.06', 'title' => 'Maintain continuous improvement.'],
                    ],
                ],
                'APO12' => [
                    'title' => 'Manage Risk',
                    'practices' => [
                        ['code' => 'APO12.01', 'title' => 'Collect data.'],
                        ['code' => 'APO12.02', 'title' => 'Analyse risk.'],
                        ['code' => 'APO12.03', 'title' => 'Maintain a risk profile.'],
                        ['code' => 'APO12.04', 'title' => 'Articulate risk.'],
                        ['code' => 'APO12.05', 'title' => 'Define a risk management action portfolio.'],
                        ['code' => 'APO12.06', 'title' => 'Respond to risk.'],
                    ],
                ],
                'APO13' => [
                    'title' => 'Manage Security',
                    'practices' => [
                        ['code' => 'APO13.01', 'title' => 'Establish and maintain an ISMS.'],
                        ['code' => 'APO13.02', 'title' => 'Define and manage an information security risk treatment plan.'],
                        ['code' => 'APO13.03', 'title' => 'Monitor and review the ISMS.'],
                    ],
                ],
            ],
        ],
        'BAI' => [
            'title' => 'Build, Acquire and Implement',
            'processes' => [
                'BAI01' => [
                    'title' => 'Manage Programmes and Projects',
                    'practices' => [
                        ['code' => 'BAI01.01', 'title' => 'Maintain a standard approach for programme and project management.'],
                        ['code' => 'BAI01.02', 'title' => 'Initiate a programme.'],
                        ['code' => 'BAI01.03', 'title' => 'Manage stakeholder engagement.'],
                        ['code' => 'BAI01.04', 'title' => 'Develop and maintain the programme plan.'],
                        ['code' => 'BAI01.05', 'title' => 'Launch and execute the programme.'],
                        ['code' => 'BAI01.06', 'title' => 'Monitor, control and report on the programme outcomes.'],
                        ['code' => 'BAI01.07', 'title' => 'Start up and initiate projects within a programme.'],
                        ['code' => 'BAI01.08', 'title' => 'Plan projects.'],
                        ['code' => 'BAI01.09', 'title' => 'Manage programme and project quality.'],
                        ['code' => 'BAI01.10', 'title' => 'Manage programme and project risk.'],
                        ['code' => 'BAI01.11', 'title' => 'Monitor and control projects.'],
                        ['code' => 'BAI01.12', 'title' => 'Manage project resources and work packages.'],
                        ['code' => 'BAI01.13', 'title' => 'Close a project or iteration.'],
                        ['code' => 'BAI01.14', 'title' => 'Close a programme.'],
                    ],
                ],
                'BAI02' => [
                    'title' => 'Manage Requirements Definition',
                    'practices' => [
                        ['code' => 'BAI02.01', 'title' => 'Define and maintain business functional and technical requirements.'],
                        ['code' => 'BAI02.02', 'title' => 'Perform a feasibility study and formulate alternative solutions.'],
                        ['code' => 'BAI02.03', 'title' => 'Manage requirements risk.'],
                        ['code' => 'BAI02.04', 'title' => 'Obtain approval of requirements and solutions.'],
                    ],
                ],
                'BAI03' => [
                    'title' => 'Manage Solutions Identification and Build',
                    'practices' => [
                        ['code' => 'BAI03.01', 'title' => 'Design high-level solutions.'],
                        ['code' => 'BAI03.02', 'title' => 'Design detailed solution components.'],
                        ['code' => 'BAI03.03', 'title' => 'Develop solution components.'],
                        ['code' => 'BAI03.04', 'title' => 'Procure solution components.'],
                        ['code' => 'BAI03.05', 'title' => 'Build solutions.'],
                        ['code' => 'BAI03.06', 'title' => 'Perform quality assurance.'],
                        ['code' => 'BAI03.07', 'title' => 'Prepare for solution testing.'],
                        ['code' => 'BAI03.08', 'title' => 'Execute solution testing.'],
                        ['code' => 'BAI03.09', 'title' => 'Manage changes to requirements.'],
                        ['code' => 'BAI03.10', 'title' => 'Maintain solutions.'],
                        ['code' => 'BAI03.11', 'title' => 'Define IT services and maintain the service portfolio.'],
                    ],
                ],
                'BAI04' => [
                    'title' => 'Manage Availability and Capacity',
                    'practices' => [
                        ['code' => 'BAI04.01', 'title' => 'Assess current availability, performance and capacity and create a baseline.'],
                        ['code' => 'BAI04.02', 'title' => 'Assess business impact.'],
                        ['code' => 'BAI04.03', 'title' => 'Plan for new or changed service requirements.'],
                        ['code' => 'BAI04.04', 'title' => 'Monitor and review availability and capacity.'],
                        ['code' => 'BAI04.05', 'title' => 'Investigate and address availability, performance and capacity issues.'],
                    ],
                ],
                'BAI05' => [
                    'title' => 'Manage Organisational Change Enablement',
                    'practices' => [
                        ['code' => 'BAI05.01', 'title' => 'Establish the desire to change.'],
                        ['code' => 'BAI05.02', 'title' => 'Form an effective implementation team.'],
                        ['code' => 'BAI05.03', 'title' => 'Communicate the desired vision.'],
                        ['code' => 'BAI05.04', 'title' => 'Empower role players and identify short-term wins.'],
                        ['code' => 'BAI05.05', 'title' => 'Enable operation and use.'],
                        ['code' => 'BAI05.06', 'title' => 'Embed new approaches.'],
                        ['code' => 'BAI05.07', 'title' => 'Sustain changes.'],
                    ],
                ],
                'BAI06' => [
                    'title' => 'Manage Changes',
                    'practices' => [
                        ['code' => 'BAI06.01', 'title' => 'Evaluate, prioritise and authorise change requests.'],
                        ['code' => 'BAI06.02', 'title' => 'Manage emergency changes.'],
                        ['code' => 'BAI06.03', 'title' => 'Track and report change status.'],
                        ['code' => 'BAI06.04', 'title' => 'Close and document the changes.'],
                    ],
                ],
                'BAI07' => [
                    'title' => 'Manage Change Acceptance and Transitioning',
                    'practices' => [
                        ['code' => 'BAI07.01', 'title' => 'Establish an implementation plan.'],
                        ['code' => 'BAI07.02', 'title' => 'Plan business process, system and data conversion.'],
                        ['code' => 'BAI07.03', 'title' => 'Plan acceptance tests.'],
                        ['code' => 'BAI07.04', 'title' => 'Establish a test environment.'],
                        ['code' => 'BAI07.05', 'title' => 'Perform acceptance tests.'],
                        ['code' => 'BAI07.06', 'title' => 'Promote to production and manage releases.'],
                        ['code' => 'BAI07.07', 'title' => 'Provide early production support.'],
                        ['code' => 'BAI07.08', 'title' => 'Perform a post-implementation review.'],
                    ],
                ],
                'BAI08' => [
                    'title' => 'Manage Knowledge',
                    'practices' => [
                        ['code' => 'BAI08.01', 'title' => 'Nurture and facilitate a knowledge-sharing culture.'],
                        ['code' => 'BAI08.02', 'title' => 'Identify and classify sources of information.'],
                        ['code' => 'BAI08.03', 'title' => 'Organise and contextualise information into knowledge.'],
                        ['code' => 'BAI08.04', 'title' => 'Use and share knowledge.'],
                        ['code' => 'BAI08.05', 'title' => 'Evaluate and retire information.'],
                    ],
                ],
                'BAI09' => [
                    'title' => 'Manage Assets',
                    'practices' => [
                        ['code' => 'BAI09.01', 'title' => 'Identify and record current assets.'],
                        ['code' => 'BAI09.02', 'title' => 'Manage critical assets.'],
                        ['code' => 'BAI09.03', 'title' => 'Manage the asset life cycle.'],
                        ['code' => 'BAI09.04', 'title' => 'Optimise asset costs.'],
                        ['code' => 'BAI09.05', 'title' => 'Manage licences.'],
                    ],
                ],
                'BAI10' => [
                    'title' => 'Manage Configuration',
                    'practices' => [
                        ['code' => 'BAI10.01', 'title' => 'Establish and maintain a configuration model.'],
                        ['code' => 'BAI10.02', 'title' => 'Establish and maintain a configuration repository and baseline.'],
                        ['code' => 'BAI10.03', 'title' => 'Maintain and control configuration items.'],
                        ['code' => 'BAI10.04', 'title' => 'Produce status and configuration reports.'],
                        ['code' => 'BAI10.05', 'title' => 'Verify and review integrity of the configuration repository.'],
                    ],
                ],
            ],
        ],
        'DSS' => [
            'title' => 'Deliver, Service and Support',
            'processes' => [
                'DSS01' => [
                    'title' => 'Manage Operations',
                    'practices' => [
                        ['code' => 'DSS01.01', 'title' => 'Perform operational procedures.'],
                        ['code' => 'DSS01.02', 'title' => 'Manage outsourced IT services.'],
                        ['code' => 'DSS01.03', 'title' => 'Monitor IT infrastructure.'],
                        ['code' => 'DSS01.04', 'title' => 'Manage the environment.'],
                        ['code' => 'DSS01.05', 'title' => 'Manage facilities.'],
                    ],
                ],
                'DSS02' => [
                    'title' => 'Manage Service Requests and Incidents',
                    'practices' => [
                        ['code' => 'DSS02.01', 'title' => 'Define incident and service request classification schemes.'],
                        ['code' => 'DSS02.02', 'title' => 'Record, classify and prioritise requests and incidents.'],
                        ['code' => 'DSS02.03', 'title' => 'Verify, approve and fulfil service requests.'],
                        ['code' => 'DSS02.04', 'title' => 'Investigate, diagnose and allocate incidents.'],
                        ['code' => 'DSS02.05', 'title' => 'Resolve and recover from incidents.'],
                        ['code' => 'DSS02.06', 'title' => 'Close service requests and incidents.'],
                        ['code' => 'DSS02.07', 'title' => 'Track status and produce reports.'],
                    ],
                ],
                'DSS03' => [
                    'title' => 'Manage Problems',
                    'practices' => [
                        ['code' => 'DSS03.01', 'title' => 'Identify and classify problems.'],
                        ['code' => 'DSS03.02', 'title' => 'Investigate and diagnose problems.'],
                        ['code' => 'DSS03.03', 'title' => 'Raise known errors.'],
                        ['code' => 'DSS03.04', 'title' => 'Resolve and close problems.'],
                        ['code' => 'DSS03.05', 'title' => 'Perform proactive problem management.'],
                    ],
                ],
                'DSS04' => [
                    'title' => 'Manage Continuity',
                    'practices' => [
                        ['code' => 'DSS04.01', 'title' => 'Define the business continuity policy, objectives and scope.'],
                        ['code' => 'DSS04.02', 'title' => 'Maintain a continuity strategy.'],
                        ['code' => 'DSS04.03', 'title' => 'Develop and implement a business continuity response.'],
                        ['code' => 'DSS04.04', 'title' => 'Exercise, test and review the BCP.'],
                        ['code' => 'DSS04.05', 'title' => 'Review, maintain and improve the continuity plan.'],
                        ['code' => 'DSS04.06', 'title' => 'Conduct continuity plan training.'],
                        ['code' => 'DSS04.07', 'title' => 'Manage backup arrangements.'],
                        ['code' => 'DSS04.08', 'title' => 'Conduct post-resumption review.'],
                        ['code' => 'DSS04.09', 'title' => 'Code shown in the supplied source; verify against COBIT 5.', 'review_note' => 'DSS04.09 is shown in the supplied mapping image but is not listed as a COBIT 5 DSS04 practice.'],
                    ],
                ],
                'DSS05' => [
                    'title' => 'Manage Security Services',
                    'practices' => [
                        ['code' => 'DSS05.01', 'title' => 'Protect against malware.'],
                        ['code' => 'DSS05.02', 'title' => 'Manage network and connectivity security.'],
                        ['code' => 'DSS05.03', 'title' => 'Manage endpoint security.'],
                        ['code' => 'DSS05.04', 'title' => 'Manage user identity and logical access.'],
                        ['code' => 'DSS05.05', 'title' => 'Manage physical access to IT assets.'],
                        ['code' => 'DSS05.06', 'title' => 'Manage sensitive documents and output devices.'],
                        ['code' => 'DSS05.07', 'title' => 'Monitor the infrastructure for security-related events.'],
                        ['code' => 'DSS05.08', 'title' => 'Code shown in the supplied source; verify against COBIT 5.', 'review_note' => 'DSS05.08 is shown in the supplied mapping image but is not listed as a COBIT 5 DSS05 practice.'],
                    ],
                ],
                'DSS06' => [
                    'title' => 'Manage Business Process Controls',
                    'practices' => [
                        ['code' => 'DSS06.01', 'title' => 'Align control activities embedded in business processes with enterprise objectives.'],
                        ['code' => 'DSS06.02', 'title' => 'Control the processing of information.'],
                        ['code' => 'DSS06.03', 'title' => 'Manage roles, responsibilities, access privileges and levels of authority.'],
                        ['code' => 'DSS06.04', 'title' => 'Manage errors and exceptions.'],
                        ['code' => 'DSS06.05', 'title' => 'Ensure traceability of information events.'],
                        ['code' => 'DSS06.06', 'title' => 'Secure information assets.'],
                    ],
                ],
            ],
        ],
        'EDM' => [
            'title' => 'Evaluate, Direct and Monitor',
            'processes' => [
                'EDM01' => [
                    'title' => 'Ensure Governance Framework Setting and Maintenance',
                    'practices' => [
                        ['code' => 'EDM01.01', 'title' => 'Evaluate the governance system.'],
                        ['code' => 'EDM01.02', 'title' => 'Direct the governance system.'],
                        ['code' => 'EDM01.03', 'title' => 'Monitor the governance system.'],
                    ],
                ],
                'EDM02' => [
                    'title' => 'Ensure Benefits Delivery',
                    'practices' => [
                        ['code' => 'EDM02.01', 'title' => 'Evaluate value optimisation.'],
                        ['code' => 'EDM02.02', 'title' => 'Direct value optimisation.'],
                        ['code' => 'EDM02.03', 'title' => 'Monitor value optimisation.'],
                    ],
                ],
                'EDM03' => [
                    'title' => 'Ensure Risk Optimisation',
                    'practices' => [
                        ['code' => 'EDM03.01', 'title' => 'Evaluate risk management.'],
                        ['code' => 'EDM03.02', 'title' => 'Direct risk management.'],
                        ['code' => 'EDM03.03', 'title' => 'Monitor risk management.'],
                    ],
                ],
                'EDM04' => [
                    'title' => 'Ensure Resource Optimisation',
                    'practices' => [
                        ['code' => 'EDM04.01', 'title' => 'Evaluate resource management.'],
                        ['code' => 'EDM04.02', 'title' => 'Direct resource management.'],
                        ['code' => 'EDM04.03', 'title' => 'Monitor resource management.'],
                    ],
                ],
                'EDM05' => [
                    'title' => 'Ensure Stakeholder Transparency',
                    'practices' => [
                        ['code' => 'EDM05.01', 'title' => 'Evaluate stakeholder reporting requirements.'],
                        ['code' => 'EDM05.02', 'title' => 'Direct stakeholder communication and reporting.'],
                        ['code' => 'EDM05.03', 'title' => 'Monitor stakeholder communication.'],
                    ],
                ],
            ],
        ],
        'MEA' => [
            'title' => 'Monitor, Evaluate and Assess',
            'processes' => [
                'MEA01' => [
                    'title' => 'Monitor, Evaluate and Assess Performance and Conformance',
                    'practices' => [
                        ['code' => 'MEA01.01', 'title' => 'Establish a monitoring approach.'],
                        ['code' => 'MEA01.02', 'title' => 'Set performance and conformance targets.'],
                        ['code' => 'MEA01.03', 'title' => 'Collect and process performance and conformance data.'],
                        ['code' => 'MEA01.04', 'title' => 'Analyse and report performance.'],
                        ['code' => 'MEA01.05', 'title' => 'Ensure the implementation of corrective actions.'],
                    ],
                ],
                'MEA02' => [
                    'title' => 'Monitor, Evaluate and Assess the System of Internal Control',
                    'practices' => [
                        ['code' => 'MEA02.01', 'title' => 'Monitor internal controls.'],
                        ['code' => 'MEA02.02', 'title' => 'Review business process controls effectiveness.'],
                        ['code' => 'MEA02.03', 'title' => 'Perform control self-assessments.'],
                        ['code' => 'MEA02.04', 'title' => 'Identify and report control deficiencies.'],
                        ['code' => 'MEA02.05', 'title' => 'Ensure that assurance providers are independent and qualified.'],
                        ['code' => 'MEA02.06', 'title' => 'Plan assurance initiatives.'],
                        ['code' => 'MEA02.07', 'title' => 'Scope assurance initiatives.'],
                        ['code' => 'MEA02.08', 'title' => 'Execute assurance initiatives.'],
                    ],
                ],
                'MEA03' => [
                    'title' => 'Monitor, Evaluate and Assess Compliance with External Requirements',
                    'practices' => [
                        ['code' => 'MEA03.01', 'title' => 'Identify external compliance requirements.'],
                        ['code' => 'MEA03.02', 'title' => 'Optimise response to external requirements.'],
                        ['code' => 'MEA03.03', 'title' => 'Confirm external compliance.'],
                        ['code' => 'MEA03.04', 'title' => 'Obtain assurance of external compliance.'],
                    ],
                ],
            ],
        ],
    ],
    'unverified_practices' => [
        [
            'code' => 'DSS08.02',
            'title' => 'Code shown in the supplied source; verify against COBIT 5.',
            'domain_code' => 'DSS',
            'domain_title' => 'Deliver, Service and Support',
            'gamo_code' => 'DSS08',
            'gamo_title' => 'Code shown in the supplied source; GAMO not verified.',
            'review_note' => 'DSS08.02 is shown in the supplied mapping image but DSS08 is not listed among COBIT 5 DSS processes.',
        ],
    ],
];
