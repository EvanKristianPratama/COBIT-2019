<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class ReportingController extends Controller
{
    /**
     * Display the main reporting dashboard.
     */
    public function index()
    {
        // Dummy data for assessments this year
        $assessments = [
            [
                'id' => 1,
                'name' => 'IT Governance Assessment Q1',
                'date' => '2026-01-15',
                'status' => 'completed',
                'score' => 78,
                'target' => 85,
            ],
            [
                'id' => 2,
                'name' => 'Security Compliance Review',
                'date' => '2026-01-20',
                'status' => 'completed',
                'score' => 82,
                'target' => 80,
            ],
            [
                'id' => 3,
                'name' => 'Data Management Audit',
                'date' => '2026-02-01',
                'status' => 'in_progress',
                'score' => 65,
                'target' => 75,
            ],
        ];

        // Dummy capability levels for radar/bar chart
        $capabilityData = [
            ['domain' => 'EDM', 'current' => 2.5, 'target' => 4.0, 'fullName' => 'Evaluate, Direct and Monitor'],
            ['domain' => 'APO', 'current' => 3.0, 'target' => 4.0, 'fullName' => 'Align, Plan and Organize'],
            ['domain' => 'BAI', 'current' => 2.8, 'target' => 3.5, 'fullName' => 'Build, Acquire and Implement'],
            ['domain' => 'DSS', 'current' => 3.2, 'target' => 4.0, 'fullName' => 'Deliver, Service and Support'],
            ['domain' => 'MEA', 'current' => 2.0, 'target' => 3.5, 'fullName' => 'Monitor, Evaluate and Assess'],
        ];

        // Dummy summary statistics
        $summary = [
            'totalAssessments' => 12,
            'completedThisYear' => 8,
            'averageScore' => 75.5,
            'targetAchievement' => 68,
            'activeProjects' => 5,
            'pendingActions' => 23,
        ];

        // Dummy roadmap items
        $roadmap = [
            [
                'id' => 1,
                'title' => 'Enhance IT Risk Management',
                'domain' => 'APO12',
                'currentLevel' => 2,
                'targetLevel' => 4,
                'priority' => 'high',
                'timeline' => 'Q2 2026',
                'status' => 'in_progress',
                'progress' => 45,
            ],
            [
                'id' => 2,
                'title' => 'Implement Security Operations',
                'domain' => 'DSS05',
                'currentLevel' => 3,
                'targetLevel' => 4,
                'priority' => 'high',
                'timeline' => 'Q2 2026',
                'status' => 'in_progress',
                'progress' => 60,
            ],
            [
                'id' => 3,
                'title' => 'Improve Change Management',
                'domain' => 'BAI06',
                'currentLevel' => 2,
                'targetLevel' => 3,
                'priority' => 'medium',
                'timeline' => 'Q3 2026',
                'status' => 'planned',
                'progress' => 15,
            ],
            [
                'id' => 4,
                'title' => 'Establish IT Governance Framework',
                'domain' => 'EDM01',
                'currentLevel' => 1,
                'targetLevel' => 3,
                'priority' => 'high',
                'timeline' => 'Q4 2026',
                'status' => 'planned',
                'progress' => 10,
            ],
            [
                'id' => 5,
                'title' => 'Performance Monitoring Enhancement',
                'domain' => 'MEA01',
                'currentLevel' => 2,
                'targetLevel' => 4,
                'priority' => 'medium',
                'timeline' => 'Q1 2027',
                'status' => 'planned',
                'progress' => 0,
            ],
        ];

        // Monthly trend data for line chart
        $trendData = [
            ['month' => 'Aug', 'score' => 62],
            ['month' => 'Sep', 'score' => 65],
            ['month' => 'Oct', 'score' => 68],
            ['month' => 'Nov', 'score' => 72],
            ['month' => 'Dec', 'score' => 71],
            ['month' => 'Jan', 'score' => 75],
            ['month' => 'Feb', 'score' => 78],
        ];

        return Inertia::render('Reporting/Index', [
            'assessments' => $assessments,
            'capabilityData' => $capabilityData,
            'summary' => $summary,
            'roadmap' => $roadmap,
            'trendData' => $trendData,
            'currentYear' => date('Y'),
        ]);
    }

    /**
     * Show detailed capability report.
     */
    public function capability()
    {
        $domains = [
            [
                'code' => 'EDM',
                'name' => 'Evaluate, Direct and Monitor',
                'objectives' => [
                    ['code' => 'EDM01', 'name' => 'Ensured Governance Framework Setting and Maintenance', 'current' => 2, 'target' => 4],
                    ['code' => 'EDM02', 'name' => 'Ensured Benefits Delivery', 'current' => 3, 'target' => 4],
                    ['code' => 'EDM03', 'name' => 'Ensured Risk Optimization', 'current' => 2, 'target' => 3],
                    ['code' => 'EDM04', 'name' => 'Ensured Resource Optimization', 'current' => 3, 'target' => 4],
                    ['code' => 'EDM05', 'name' => 'Ensured Stakeholder Engagement', 'current' => 2, 'target' => 3],
                ],
            ],
            [
                'code' => 'APO',
                'name' => 'Align, Plan and Organize',
                'objectives' => [
                    ['code' => 'APO01', 'name' => 'Managed I&T Management Framework', 'current' => 3, 'target' => 4],
                    ['code' => 'APO02', 'name' => 'Managed Strategy', 'current' => 2, 'target' => 4],
                    ['code' => 'APO03', 'name' => 'Managed Enterprise Architecture', 'current' => 3, 'target' => 4],
                    ['code' => 'APO04', 'name' => 'Managed Innovation', 'current' => 2, 'target' => 3],
                    ['code' => 'APO05', 'name' => 'Managed Portfolio', 'current' => 3, 'target' => 4],
                ],
            ],
            [
                'code' => 'BAI',
                'name' => 'Build, Acquire and Implement',
                'objectives' => [
                    ['code' => 'BAI01', 'name' => 'Managed Programs', 'current' => 3, 'target' => 4],
                    ['code' => 'BAI02', 'name' => 'Managed Requirements Definition', 'current' => 2, 'target' => 3],
                    ['code' => 'BAI03', 'name' => 'Managed Solutions Identification and Build', 'current' => 3, 'target' => 4],
                    ['code' => 'BAI04', 'name' => 'Managed Availability and Capacity', 'current' => 2, 'target' => 3],
                    ['code' => 'BAI05', 'name' => 'Managed Organizational Change', 'current' => 2, 'target' => 3],
                ],
            ],
            [
                'code' => 'DSS',
                'name' => 'Deliver, Service and Support',
                'objectives' => [
                    ['code' => 'DSS01', 'name' => 'Managed Operations', 'current' => 3, 'target' => 4],
                    ['code' => 'DSS02', 'name' => 'Managed Service Requests and Incidents', 'current' => 4, 'target' => 4],
                    ['code' => 'DSS03', 'name' => 'Managed Problems', 'current' => 3, 'target' => 4],
                    ['code' => 'DSS04', 'name' => 'Managed Continuity', 'current' => 2, 'target' => 4],
                    ['code' => 'DSS05', 'name' => 'Managed Security Services', 'current' => 3, 'target' => 4],
                ],
            ],
            [
                'code' => 'MEA',
                'name' => 'Monitor, Evaluate and Assess',
                'objectives' => [
                    ['code' => 'MEA01', 'name' => 'Managed Performance and Conformance Monitoring', 'current' => 2, 'target' => 4],
                    ['code' => 'MEA02', 'name' => 'Managed System of Internal Control', 'current' => 2, 'target' => 3],
                    ['code' => 'MEA03', 'name' => 'Managed Compliance with External Requirements', 'current' => 2, 'target' => 3],
                    ['code' => 'MEA04', 'name' => 'Managed Assurance', 'current' => 1, 'target' => 3],
                ],
            ],
        ];

        return Inertia::render('Reporting/Capability', [
            'domains' => $domains,
        ]);
    }

}
