<?php

/*
|--------------------------------------------------------------------------
| COBIT 4.1 - Business Goals, IT Goals and their mappings
|--------------------------------------------------------------------------
|
| Source: COBIT 4.1 (ITGI), "Linking Business Goals to IT Goals" and
| "Linking IT Goals to IT Processes".
|
| criteria string = 7 chars, order:
|   Effectiveness, Efficiency, Confidentiality, Integrity,
|   Availability, Compliance, Reliability
| P = primary, S = secondary, . = none
|
*/

return [
    'criteria_order' => [
        'effectiveness',
        'efficiency',
        'confidentiality',
        'integrity',
        'availability',
        'compliance',
        'reliability',
    ],

    'business_goals' => [
        // code => [perspective, title, it_goals]
        1  => ['Financial', 'Provide a good return on investment of IT-enabled business investments.', [24]],
        2  => ['Financial', 'Manage IT-related business risk.', [2, 14, 17, 18, 19, 20, 21, 22]],
        3  => ['Financial', 'Improve corporate governance and transparency.', [2, 18]],
        4  => ['Customer', 'Improve customer orientation and service.', [3, 23]],
        5  => ['Customer', 'Offer competitive products and services.', [5, 24]],
        6  => ['Customer', 'Establish service continuity and availability.', [10, 16, 22, 23]],
        7  => ['Customer', 'Create agility in responding to changing business requirements.', [1, 5, 25]],
        8  => ['Customer', 'Achieve cost optimisation of service delivery.', [7, 8, 10, 24]],
        9  => ['Customer', 'Obtain reliable and useful information for strategic decision making.', [2, 4, 12, 20, 26]],
        10 => ['Internal', 'Improve and maintain business process functionality.', [6, 7, 11]],
        11 => ['Internal', 'Lower process costs.', [7, 8, 13, 15, 24]],
        12 => ['Internal', 'Provide compliance with external laws, regulations and contracts.', [2, 19, 20, 21, 22, 26, 27]],
        13 => ['Internal', 'Provide compliance with internal policies.', [2, 13]],
        14 => ['Internal', 'Manage business change.', [1, 5, 6, 11, 28]],
        15 => ['Internal', 'Improve and maintain operational and staff productivity.', [7, 8, 11, 13]],
        16 => ['Learning and Growth', 'Manage product and business innovation.', [5, 25, 28]],
        17 => ['Learning and Growth', 'Acquire and maintain skilled and motivated people.', [9]],
    ],

    'it_goals' => [
        // code => [title, criteria, processes]
        1  => ['Respond to business requirements in alignment with the business strategy.', 'PP.SS..', ['PO1', 'PO2', 'PO4', 'PO10', 'AI1', 'AI6', 'AI7', 'DS1', 'DS3', 'ME1']],
        2  => ['Respond to governance requirements in line with board direction.', 'PP.....', ['PO1', 'PO4', 'PO10', 'ME1', 'ME4']],
        3  => ['Ensure satisfaction of end users with service offerings and service levels.', 'PP.SS..', ['PO8', 'AI4', 'DS1', 'DS2', 'DS7', 'DS8', 'DS10', 'DS13']],
        4  => ['Optimise the use of information.', '.S.P..S', ['PO2', 'DS11']],
        5  => ['Create IT agility.', 'PP.S...', ['PO2', 'PO4', 'PO7', 'AI3']],
        6  => ['Define how business functional and control requirements are translated in effective and efficient automated solutions.', 'PP...S.', ['AI1', 'AI2', 'AI6']],
        7  => ['Acquire and maintain integrated and standardised application systems.', 'PP...S.', ['PO3', 'AI2', 'AI5']],
        8  => ['Acquire and maintain an integrated and standardised IT infrastructure.', 'SP.....', ['AI3', 'AI5']],
        9  => ['Acquire and maintain IT skills that respond to the IT strategy.', 'PP.....', ['PO7', 'AI5']],
        10 => ['Ensure mutual satisfaction of third-party relationships.', 'PPSSSSS', ['DS2']],
        11 => ['Ensure seamless integration of applications into business processes.', 'PP.SS..', ['PO2', 'AI4', 'AI7']],
        12 => ['Ensure transparency and understanding of IT cost, benefits, strategy, policies and service levels.', 'PP...SS', ['PO5', 'PO6', 'DS1', 'DS2', 'DS6', 'ME1', 'ME4']],
        13 => ['Ensure proper use and performance of the applications and technology solutions.', 'PS.....', ['PO6', 'AI4', 'AI7', 'DS7', 'DS8']],
        14 => ['Account for and protect all IT assets.', 'SSPPPSS', ['PO9', 'DS5', 'DS9', 'DS12', 'ME2']],
        15 => ['Optimise the IT infrastructure, resources and capabilities.', 'SP.....', ['PO3', 'AI3', 'DS3', 'DS7', 'DS9']],
        16 => ['Reduce solution and service delivery defects and rework.', 'PP.SS..', ['PO8', 'AI4', 'AI6', 'AI7', 'DS10']],
        17 => ['Protect the achievement of IT objectives.', 'PPSSSSS', ['PO9', 'DS10', 'ME2']],
        18 => ['Establish clarity of business impact of risks to IT objectives and resources.', 'SSPPPSS', ['PO9']],
        19 => ['Ensure that critical and confidential information is withheld from those who should not have access to it.', '..PPSSS', ['PO6', 'DS5', 'DS11', 'DS12']],
        20 => ['Ensure that automated business transactions and information exchanges can be trusted.', 'P..PSS.', ['PO6', 'AI7', 'DS5']],
        21 => ['Ensure that IT services and infrastructure can properly resist and recover from failures due to error, deliberate attack or disaster.', 'PS.SP..', ['PO6', 'AI7', 'DS4', 'DS5', 'DS12', 'DS13', 'ME2']],
        22 => ['Ensure minimum business impact in the event of an IT service disruption or change.', 'PS.SP..', ['PO6', 'AI6', 'DS4', 'DS12']],
        23 => ['Make sure that IT services are available as required.', 'PP..P..', ['DS3', 'DS4', 'DS8', 'DS13']],
        24 => ["Improve IT's cost-efficiency and its contribution to business profitability.", 'SP....S', ['PO5', 'DS6']],
        25 => ['Deliver projects on time and on budget, meeting quality standards.', 'PP.S..S', ['PO8', 'PO10']],
        26 => ['Maintain the integrity of information and processing infrastructure.', 'PP.PP.S', ['AI6', 'DS5']],
        27 => ['Ensure IT compliance with laws, regulations and contracts.', '..SS.PS', ['DS11', 'ME2', 'ME3', 'ME4']],
        28 => ['Ensure that IT demonstrates cost-efficient service quality, continuous improvement and readiness for future change.', 'PP....P', ['PO5', 'DS6', 'ME1', 'ME4']],
    ],
];
