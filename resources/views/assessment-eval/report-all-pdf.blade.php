<!DOCTYPE html>
<html>
<head>
    <title>All Assessments Report</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
            padding: 0;
            font-size: 16px;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000; /* Analog style: solid black borders */
            padding: 4px 6px;
            vertical-align: middle;
        }
        th {
            background-color: #f0f0f0; /* Light gray header */
            font-weight: bold;
            text-align: center;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bg-gray { background-color: #f0f0f0; }
        
        .score-cell { font-weight: bold; text-align: center; }
        
        /* Footer-like totals */
        .footer-row td {
            font-weight: bold;
        }
        
        /* Color classes for scores (optional, sticking to clean/analog look primarily, but adding subtle visual cues) */
        .level-0 { background-color: #ffebee; color: #c62828; }
        .level-1 { background-color: #fff3e0; color: #ef6c00; }
        .level-2 { background-color: #fff8e1; color: #f57f17; }
        .level-3 { background-color: #e8f5e9; color: #2e7d32; }
        .level-4 { background-color: #e3f2fd; color: #1565c0; }
        .level-5 { background-color: #f3e5f5; color: #6a1b9a; }

        .gap-pos { color: #2e7d32; }
        .gap-neg { color: #c62828; }
    </style>
</head>
<body>

    <div class="header">
        <h2>All Assessments Report (Comparison)</h2>
        <p>Generated on: {{ date('d M Y H:i') }}</p>
    </div>

    @php
        // Static Max Levels Reference per Objective (Matches JS Config)
        $MAX_LEVELS_REF = [
            'EDM01' => 4, 'EDM02' => 5, 'EDM03' => 4, 'EDM04' => 4, 'EDM05' => 4,
            'APO01' => 5, 'APO02' => 4, 'APO03' => 5, 'APO04' => 4, 'APO05' => 5, 'APO06' => 5, 'APO07' => 4, 'APO08' => 5, 'APO09' => 4, 'APO10' => 5, 'APO11' => 5, 'APO12' => 5, 'APO13' => 5, 'APO14' => 5,
            'BAI01' => 5, 'BAI02' => 4, 'BAI03' => 4, 'BAI04' => 5, 'BAI05' => 5, 'BAI06' => 4, 'BAI07' => 5, 'BAI08' => 5, 'BAI09' => 5, 'BAI10' => 4, 'BAI11' => 5,
            'DSS01' => 5, 'DSS02' => 5, 'DSS03' => 5, 'DSS04' => 4, 'DSS05' => 5, 'DSS06' => 5,
            'MEA01' => 5, 'MEA02' => 5, 'MEA03' => 5, 'MEA04' => 4
        ];
    @endphp

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 60px;">GAMO</th>
                <th style="width: 150px;">Process Name</th>
                @foreach($selectedData as $data)
                    <th>
                        <div style="font-size: 11px;">{{ $data['year'] }}</div>
                        <div style="font-weight: normal; font-size: 9px;">{{ $data['scope_name'] }}</div>
                    </th>
                @endforeach
                @if($showMaxLevel)
                    <th style="width: 60px; background-color: #6c757d; color: white;">Max Level</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($objectives as $index => $obj)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center"><strong>{{ $obj->objective_id }}</strong></td>
                    <td>{{ $obj->objective }}</td>
                    
                    @foreach($selectedData as $data)
                        @php
                            $score = $data['maturity_scores'][$obj->objective_id] ?? null;
                            $colorClass = $score !== null ? 'level-' . floor($score) : '';
                        @endphp
                        
                        @if($score !== null)
                            <td class="score-cell {{ $colorClass }}">{{ $score }}</td>
                        @else
                            <td class="text-center text-muted">-</td>
                        @endif
                    @endforeach

                    @if($showMaxLevel)
                        <td class="text-center" style="background-color: #6c757d; color: white; font-weight: bold;">
                            {{ $MAX_LEVELS_REF[$obj->objective_id] ?? '-' }}
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            {{-- ROW 1: TOTAL GAMO --}}
            <tr class="footer-row bg-gray">
                <td colspan="3" class="text-right">Total GAMO Selected</td>
                @foreach($selectedData as $data)
                    @php
                        $count = count(array_filter($data['maturity_scores'], function($v) { return $v !== null; }));
                    @endphp
                    <td class="text-center">{{ $count }}</td>
                @endforeach
                @if($showMaxLevel)
                    <td class="text-center" style="background-color: #6c757d; color: white;">-</td>
                @endif
            </tr>

            {{-- ROW 2: AVG MATURITY --}}
            <tr class="footer-row">
                <td colspan="3" class="text-right">I&T Maturity Score</td>
                @foreach($selectedData as $data)
                    @php
                        $valid = array_filter($data['maturity_scores'], function($v) { return $v !== null; });
                        $avg = count($valid) ? array_sum($valid) / count($valid) : 0;
                    @endphp
                    <td class="text-center">{{ number_format($avg, 2) }}</td>
                @endforeach
                @if($showMaxLevel)
                    <td class="text-center" style="background-color: #6c757d; color: white;">-</td>
                @endif
            </tr>

            {{-- ROW 3: TARGET --}}
            <tr class="footer-row">
                <td colspan="3" class="text-right">I&T Target Maturity</td>
                @foreach($selectedData as $data)
                    <td class="text-center">{{ number_format($data['effective_target'], 2) }}</td>
                @endforeach
                @if($showMaxLevel)
                    <td class="text-center" style="background-color: #6c757d; color: white;">-</td>
                @endif
            </tr>

            {{-- ROW 4: GAP --}}
            <tr class="footer-row">
                <td colspan="3" class="text-right">Gap Analysis</td>
                @foreach($selectedData as $data)
                    @php
                        $valid = array_filter($data['maturity_scores'], function($v) { return $v !== null; });
                        $avg = count($valid) ? array_sum($valid) / count($valid) : 0;
                        $gap = $avg - $data['effective_target'];
                        $gapSign = $gap > 0 ? '+' : '';
                        $gapClass = $gap >= 0 ? 'gap-pos' : 'gap-neg';
                    @endphp
                    <td class="text-center {{ $gapClass }}">
                        {{ $gapSign }}{{ number_format($gap, 2) }}
                    </td>
                @endforeach
                @if($showMaxLevel)
                    <td class="text-center" style="background-color: #6c757d; color: white;">-</td>
                @endif
            </tr>
        </tfoot>
    </table>

</body>
</html>
