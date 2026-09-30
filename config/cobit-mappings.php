<?php

return [
    /*
    |--------------------------------------------------------------------------
    | COBIT Framework Mappings
    |--------------------------------------------------------------------------
    |
    | Mapping dari Baseline Objective ID COBIT 2019 ke Nama Proses Framework Lain.
    | Digunakan untuk bulk-clone objective beserta relasinya.
    |
    */

    'cobit5' => [
        // EDM
        'EDM01' => 'Ensure Governance Framework Setting and Maintenance',
        'EDM02' => 'Ensure Benefits Delivery',
        'EDM03' => 'Ensure Risk Optimization',
        'EDM04' => 'Ensure Resource Optimization',
        'EDM05' => 'Ensure Stakeholder Transparency',
        // APO
        'APO01' => 'Manage the IT Management Framework',
        'APO02' => 'Manage Strategy',
        'APO03' => 'Manage Enterprise Architecture',
        'APO04' => 'Manage Innovation',
        'APO05' => 'Manage Portfolio',
        'APO06' => 'Manage Budgets and Costs',
        'APO07' => 'Manage Human Resources',
        'APO08' => 'Manage Relationships',
        'APO09' => 'Manage Service Agreements',
        'APO10' => 'Manage Suppliers',
        'APO11' => 'Manage Quality',
        'APO12' => 'Manage Risk',
        'APO13' => 'Manage Security',
        // BAI
        'BAI01' => 'Manage Programmes and Projects',
        'BAI02' => 'Manage Requirements Definition',
        'BAI03' => 'Manage Solutions Identification and Build',
        'BAI04' => 'Manage Availability and Capacity',
        'BAI05' => 'Manage Organisational Change Enablement',
        'BAI06' => 'Manage Changes',
        'BAI07' => 'Manage Change Acceptance and Transition',
        'BAI08' => 'Manage Knowledge',
        'BAI09' => 'Manage Assets',
        'BAI10' => 'Manage Configuration',
        // DSS
        'DSS01' => 'Manage Operations',
        'DSS02' => 'Manage Service Requests and Incidents',
        'DSS03' => 'Manage Problems',
        'DSS04' => 'Manage Continuity',
        'DSS05' => 'Manage Security Services',
        'DSS06' => 'Manage Business Process Controls',
        // MEA
        'MEA01' => 'Monitor, Evaluate and Assess Performance and Conformance',
        'MEA02' => 'Monitor, Evaluate and Assess the System of Internal Control',
        'MEA03' => 'Monitor, Evaluate and Assess Compliance with External Requirements',
    ],

    'cobit4' => [
        // Plan and Organize (PO)
        'EDM01' => 'PO1 Define a Strategic IT Plan',
        'APO02' => 'PO2 Define the Information Architecture',
        'APO04' => 'PO3 Determine Technological Direction',
        'APO01' => 'PO4 Define the IT Processes, Organisation and Relationships',
        'APO06' => 'PO5 Manage the IT Investment',
        'APO07' => 'PO7 Manage IT Human Resources',
        'APO11' => 'PO8 Manage Quality',
        'APO12' => 'PO9 Assess and Manage IT Risks',
        'BAI01' => 'PO10 Manage Projects',
        // Acquire and Implement (AI)
        'BAI02' => 'AI1 Identify Automated Solutions',
        'BAI03' => 'AI2 Acquire and Maintain Application Software',
        'BAI04' => 'AI3 Acquire and Maintain Technology Infrastructure',
        'BAI05' => 'AI4 Enable Operation and Use',
        'APO10' => 'AI5 Procure IT Resources',
        'BAI06' => 'AI6 Manage Changes',
        'BAI07' => 'AI7 Install and Accredit Solutions and Changes',
        // Deliver and Support (DS)
        'APO09' => 'DS1 Define and Manage Service Levels',
        'DSS04' => 'DS4 Ensure Continuous Service',
        'DSS05' => 'DS5 Ensure Systems Security',
        'DSS02' => 'DS8 Manage Service Desk and Incidents',
        'BAI10' => 'DS9 Manage the Configuration',
        'DSS03' => 'DS10 Manage Problems',
        'DSS06' => 'DS11 Manage Data',
        'DSS01' => 'DS13 Manage Operations',
        // Monitor and Evaluate (ME)
        'MEA01' => 'ME1 Monitor and Evaluate IT Performance',
        'MEA02' => 'ME2 Monitor and Evaluate Internal Control',
        'MEA03' => 'ME3 Ensure Regulatory Compliance',
        'EDM05' => 'ME4 Provide IT Governance',
    ],
];

