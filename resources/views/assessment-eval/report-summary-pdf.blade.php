<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Report Summary PDF</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10pt;
        }
        .header {
            background-color: #0f2b5c;
            color: white;
            padding: 10px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .header-purple {
            background-color: #9b59b6;
            color: white;
            padding: 10px;
            text-align: center;
        }
        .score-card {
            border: 1px solid #000;
            margin-bottom: 20px;
            text-align: center;
        }
        .score-value {
            font-size: 24pt;
            font-weight: bold;
            padding: 20px 0;
        }
        .section-title {
            background-color: #0f2b5c;
            color: white;
            padding: 5px;
            font-weight: bold;
            text-align: center;
        }
        .practice-list {
            font-size: 9pt;
            border: 1px solid #ccc;
            padding: 5px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            page-break-inside: auto;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: top;
        }
        th {
            background-color: #0f2b5c;
            color: white;
            text-align: center;
        }
        .text-center { text-align: center; }
        .text-muted { color: #666; font-style: italic; }
        .page-break { page-break-after: always; }
        .objective-container { page-break-inside: avoid; margin-bottom: 20px; }
    </style>
</head>
<body>
    @foreach ($objectives as $objective)
        <div class="objective-container">
            {{-- Header --}}
            <div class="header">
                {{ $loop->iteration }}. {{ $objective->objective_id }} - {{ $objective->objective }}
            </div>

            <table style="width: 100%; border: none; margin-bottom: 10px;">
                <tr style="border: none;">
                    {{-- Left Column: Score --}}
                    <td style="width: 20%; border: none; padding-right: 15px;">
                        <div class="score-card">
                            <div class="header-purple">
                                <div style="font-size: 16pt;">{{ $objective->objective_id }}</div>
                                <div style="font-size: 8pt;">{{ $objective->objective }}</div>
                            </div>
                            <div class="score-value">
                                {{ $objective->current_score }} / {{ $objective->max_level }}
                            </div>
                        </div>
                    </td>

                    {{-- Right Column: Details --}}
                    <td style="width: 80%; border: none;">
                        <div style="border: 1px solid #ccc; margin-bottom: 10px; padding: 5px;">
                            <strong>Tujuan:</strong><br>
                            {{ $objective->objective_purpose ?? ($objective->objective_description ?? '-') }}
                        </div>
                        
                        <div class="section-title">Management Practice</div>
                        <div class="practice-list">
                            @foreach ($objective->practices as $practice)
                                <span style="margin-right: 10px;">
                                    <strong>{{ str_replace('"', '', $practice->practice_id) }}</strong> 
                                    {{ str_replace('"', '', $practice->practice_name) }}
                                </span><br>
                            @endforeach
                        </div>
                    </td>
                </tr>
            </table>

            {{-- Detailed Table --}}
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 10%;">Practice</th>
                        <th style="width: 30%;">Kebijakan / Prosedur (Design)</th>
                        <th style="width: 30%;">Bukti Pelaksanaan (Implementation)</th>
                        <th style="width: 30%;">Potensi Perbaikan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($objective->practices as $practice)
                        @if ($practice->filled_evidence_count > 0)
                            @foreach ($practice->activities as $index => $activity)
                                <tr>
                                    @if ($index === 0)
                                        <td rowspan="{{ $practice->filled_evidence_count }}" class="text-center" style="vertical-align: middle;">
                                            <strong>{{ str_replace('"', '', $practice->practice_id) }}</strong>
                                        </td>
                                    @endif
                                    
                                    <td>
                                        @if (isset($activity->assessment->policy_list) && count($activity->assessment->policy_list) > 0)
                                            @foreach ($activity->assessment->policy_list as $line)
                                                <div>- {{ $line }}</div>
                                            @endforeach
                                        @else
                                            <div class="text-muted text-center">-</div>
                                        @endif
                                    </td>
                                    
                                    <td>
                                        @if (isset($activity->assessment->execution_list) && count($activity->assessment->execution_list) > 0)
                                            @foreach ($activity->assessment->execution_list as $line)
                                                <div>- {{ $line }}</div>
                                            @endforeach
                                        @else
                                            <div class="text-muted text-center">-</div>
                                        @endif
                                    </td>
                                    
                                    <td><!-- Notes/Improvements field (empty for PDF usually unless data exists) --></td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td class="text-center">
                                    <strong>{{ str_replace('"', '', $practice->practice_id) }}</strong>
                                </td>
                                <td colspan="3" class="text-center text-muted">Belum ada Evidence</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if (!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>
