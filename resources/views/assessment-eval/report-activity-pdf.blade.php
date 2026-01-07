<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Activity Report PDF</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 9pt;
            margin: 0;
            padding: 0;
        }
        .header {
            background-color: #0f2b5c;
            color: white;
            padding: 12px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .header-title {
            font-size: 14pt;
            margin-bottom: 4px;
        }
        .header-subtitle {
            font-size: 10pt;
            opacity: 0.9;
        }
        .brief-info {
            border: 1px solid #ddd;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8f9fa;
        }
        .brief-info table {
            width: 100%;
            border: none;
        }
        .brief-info td {
            border: none;
            padding: 5px;
            vertical-align: top;
        }
        .info-label {
            font-size: 7pt;
            text-transform: uppercase;
            color: #666;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .info-value {
            font-size: 10pt;
            font-weight: bold;
            color: #000;
        }
        .maturity-box {
            text-align: center;
            background-color: #e9ecef;
            padding: 15px;
            border-right: 1px solid #ddd;
        }
        .maturity-value {
            font-size: 24pt;
            font-weight: bold;
            color: #0066cc;
        }
        table.activity-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.activity-table th,
        table.activity-table td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
            font-size: 8pt;
        }
        table.activity-table th {
            background-color: #0f2b5c;
            color: white;
            text-align: center;
            font-weight: bold;
            font-size: 7pt;
        }
        .text-center { text-align: center; }
        .text-muted { color: #666; font-style: italic; }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: bold;
        }
        .badge-success { background-color: #28a745; color: white; }
        .badge-warning { background-color: #ffc107; color: #000; }
        .badge-danger { background-color: #dc3545; color: white; }
        .evidence-list {
            margin: 0;
            padding-left: 15px;
            font-size: 7pt;
        }
        .evidence-list li {
            margin-bottom: 2px;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <div class="header-title">Activity Report</div>
        <div class="header-subtitle">{{ $objective->objective_id }} - {{ $objective->objective }}</div>
    </div>

    {{-- Brief Info Section --}}
    <div class="brief-info">
        <table>
            <tr>
                <td style="width: 25%;" class="maturity-box">
                    <div class="info-label">Capability Maturity</div>
                    <div class="maturity-value">{{ $currentLevel }}/{{ $maxLevel }}</div>
                    <div style="font-size: 7pt; color: #666; margin-top: 3px;">Level {{ $currentLevel }} of Max {{ $maxLevel }}</div>
                </td>
                <td style="width: 75%; padding-left: 15px;">
                    <table style="width: 100%; border: none;">
                        <tr>
                            <td style="width: 33%;">
                                <div class="info-label">Assessment ID</div>
                                <div class="info-value">#{{ $evalId }}</div>
                            </td>
                            <td style="width: 33%;">
                                <div class="info-label">Assessment Year</div>
                                <div class="info-value">{{ $evaluation->year ?? $evaluation->assessment_year ?? $evaluation->tahun ?? 'N/A' }}</div>
                            </td>
                            <td style="width: 34%;">
                                <div class="info-label">Organization</div>
                                <div class="info-value">{{ $organization }}</div>
                            </td>
                        </tr>
                    </table>
                    <hr style="margin: 8px 0; border: none; border-top: 1px solid #ddd;">
                    <div class="info-label">Target Objective</div>
                    <div style="font-size: 9pt; color: #333;">{{ $objective->objective_id }} - {{ $objective->objective }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Activity Table --}}
    <div style="margin-bottom: 10px; font-weight: bold; font-size: 10pt;">
        Filled Activities 
        <span style="background-color: #6c757d; color: white; padding: 2px 6px; border-radius: 3px; font-size: 8pt; margin-left: 5px;">
            {{ count($activityData) }} activities
        </span>
        @if($filterLevel)
            <span style="background-color: #0066cc; color: white; padding: 2px 6px; border-radius: 3px; font-size: 8pt; margin-left: 5px;">
                Filter: Level {{ $filterLevel }}
            </span>
        @endif
    </div>

    <table class="activity-table">
        <thead>
            <tr>
                <th style="width: 4%;">NO</th>
                <th style="width: 8%;">PRACTICE</th>
                <th style="width: 15%;">PRACTICE NAME</th>
                <th style="width: 25%;">ACTIVITY</th>
                <th style="width: 8%;">ANSWER</th>
                <th style="width: 20%;">EVIDENCE</th>
                <th style="width: 15%;">NOTES</th>
                <th style="width: 5%;">LEVEL</th>
            </tr>
        </thead>
        <tbody>
            @forelse($activityData as $index => $activity)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $activity['practice_id'] }}</td>
                    <td>{{ $activity['practice_name'] }}</td>
                    <td>{{ $activity['activity_description'] }}</td>
                    <td class="text-center">
                        @php
                            $ans = strtoupper($activity['answer']);
                            $label = $ans;
                            $style = 'background-color: #6c757d; color: white;'; // Default secondary
                            
                            if ($ans == 'FULLY' || $ans == 'F') {
                                $label = 'FULLY';
                                $style = 'background-color: #28a745; color: white;';
                            } elseif ($ans == 'LARGELY' || $ans == 'L') {
                                $label = 'LARGELY';
                                $style = 'background-color: #17a2b8; color: white;';
                            } elseif ($ans == 'PARTIALLY' || $ans == 'P') {
                                $label = 'PARTIALLY';
                                $style = 'background-color: #ffc107; color: #000;';
                            } elseif ($ans == 'NOT' || $ans == 'N') {
                                $label = 'NOT';
                                $style = 'background-color: #dc3545; color: white;';
                            }
                        @endphp
                        <span class="badge" style="{{ $style }}">{{ $label }}</span>
                    </td>
                    <td>
                        @if(!empty($activity['evidence']))
                            <ul class="evidence-list">
                                @foreach($activity['evidence'] as $evidence)
                                    <li>{{ $evidence }}</li>
                                @endforeach
                            </ul>
                        @else
                            <span class="text-muted text-center" style="display: block;">-</span>
                        @endif
                    </td>
                    <td>{{ $activity['notes'] ?? '-' }}</td>
                    <td class="text-center">{{ $activity['capability_level'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted" style="padding: 20px;">
                        No activities found
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
