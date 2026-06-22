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

    // Anda bisa tambahkan mapping lain di sini (misal: 'cobit4', 'itil', dll)
    // Format: 'Baseline_ID_2019' => 'Nama_Custom'
];
