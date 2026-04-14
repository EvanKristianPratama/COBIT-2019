<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Report Summary Detail PDF</title>
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
            margin-bottom: 8px;
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
            margin-top: 0px;
            page-break-inside: auto;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: top;
        }

        th {
            background-color: #0f2b5c;
            color: white;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .text-muted {
            color: #666;
            font-style: italic;
        }

        .page-break {
            page-break-after: always;
        }

        .objective-container {
            page-break-inside: avoid;
            margin-bottom: 20px;
        }

        .practice-header {
            background-color: #e8e8e8;
            font-weight: bold;
            font-size: 9pt;
        }
    </style>
</head>

<body>
    @foreach ($objectives as $objective)
        <div class="objective-container">
            {{-- 1. Header Bar --}}
            <div class="header">
                {{ $objective->objective_id }} - {{ $objective->objective }}
            </div>

            <table style="width: 100%; border: none; margin-bottom: 5px;">
                <tr style="border: none;">
                    {{-- 3. Left Column: Score Card --}}
                    <td style="width: 35%; border: none; padding: 0; padding-right: 10px; vertical-align: top;">
                        <table style="width: 100%; border-collapse: collapse; border: 1px solid #000;">
                            <thead>
                                {{-- Header Row --}}
                                <tr style="background-color: #9b59b6; color: #fff; height: 30px;">
                                    <th
                                        style="width: 25%; border: 1px solid #fff; font-size: 0.61rem; text-align: center; vertical-align: middle; background-color: #9b59b6; color: #fff; padding: 4px;">
                                        Capability Level
                                    </th>
                                    <th
                                        style="width: 25%; border: 1px solid #fff; font-size: 0.61rem; text-align: center; vertical-align: middle; background-color: #9b59b6; color: #fff; padding: 4px;">
                                        Max Level
                                    </th>
                                    <th
                                        style="width: 25%; border: 1px solid #fff; font-size: 0.61rem; text-align: center; vertical-align: middle; background-color: #9b59b6; color: #fff; padding: 4px;">
                                        Rating
                                    </th>
                                    <th
                                        style="width: 25%; border: 1px solid #fff; font-size: 0.61rem; text-align: center; vertical-align: middle; background-color: #9b59b6; color: #fff; padding: 4px;">
                                        Capability Target {{ $evaluation->tahun ?? '2025' }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td
                                        style="background-color: #fff; color: #000; text-align: center; vertical-align: middle; font-weight: bold; font-size: 10pt; border: 1px solid #000; padding: 5px;">
                                        {{ $objective->current_score }}
                                    </td>
                                    <td
                                        style="background-color: #fff; color: #000; text-align: center; vertical-align: middle; font-weight: bold; font-size: 10pt; border: 1px solid #000; padding: 5px;">
                                        {{ $objective->max_level }}
                                    </td>
                                    <td
                                        style="background-color: #fff; color: #000; text-align: center; vertical-align: middle; font-weight: bold; font-size: 10pt; border: 1px solid #000; padding: 5px;">
                                        {{ $objective->rating_string }}
                                    </td>
                                    <td
                                        style="background-color: #fff; color: #000; text-align: center; vertical-align: middle; font-weight: bold; font-size: 10pt; border: 1px solid #000; padding: 5px;">
                                        {{ $objective->target_level == 0 ? '-' : $objective->target_level }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>

                    {{-- 4. Right Column: Details --}}
                    <td style="width: 65%; border: none; padding: 0; vertical-align: top;">
                        {{-- Deskripsi --}}
                        <table
                            style="width: 100%; border-collapse: collapse; border: 1px solid #dee2e6; margin-bottom: 5px;">
                            <tr>
                                <td
                                    style="background-color: #0f2b5c; width: 70px; color: white; text-align: center; vertical-align: middle; padding: 5px;">
                                    <div style="font-weight: bold; font-size: 0.55rem;">Description</div>
                                </td>
                                <td style="background-color: white; padding: 5px; vertical-align: middle;">
                                    <p style="margin: 0; color: #000; font-size: 0.65rem; text-align: justify;">
                                        {{ $objective->objective_description ?? 'No description available.' }}
                                    </p>
                                </td>
                            </tr>
                        </table>

                        {{-- Tujuan --}}
                        <table style="width: 100%; border-collapse: collapse; border: 1px solid #dee2e6;">
                            <tr>
                                <td
                                    style="background-color: #0f2b5c; width: 70px; color: white; text-align: center; vertical-align: middle; padding: 5px;">
                                    <div style="font-weight: bold; font-size: 0.55rem;">Purpose</div>
                                </td>
                                <td style="background-color: white; padding: 5px; vertical-align: middle;">
                                    <p style="margin: 0; color: #000; font-size: 0.65rem; text-align: justify;">
                                        {{ $objective->objective_purpose ?? 'No description available.' }}
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            {{-- Evidence Table Per Practice --}}
            <div style="margin-top: 5px;">
                <table class="table" style="border: 1px solid #000;">
                    <thead>
                        <tr>
                            <th
                                style="width: 10%; background-color: #0f2b5c; color: white; padding: 5px; font-size: 10pt;">
                                Practice ID
                            </th>
                            <th
                                style="width: 45%; background-color: #0f2b5c; color: white; padding: 5px; font-size: 10pt;">
                                Kebijakan Pedoman / Prosedur
                            </th>
                            <th
                                style="width: 45%; background-color: #0f2b5c; color: white; padding: 5px; font-size: 10pt;">
                                Evidences / Bukti Pelaksanaan
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($objective->practices as $practice)
                            <tr>
                                <td
                                    style="text-align: center; vertical-align: middle; font-weight: bold; font-size: 9pt;">
                                    {{ str_replace('"', '', $practice->practice_id) }}
                                </td>
                                @if (
                                    (!isset($practice->policy_list) || count($practice->policy_list) == 0) &&
                                        (!isset($practice->execution_list) || count($practice->execution_list) == 0))
                                    <td colspan="2"
                                        style="text-align: center; vertical-align: middle; font-style: italic; color: #6c757d; font-size: 9pt;">
                                        Belum ada Kebijakan & Bukti Pelaksanaan
                                    </td>
                                @else
                                    <td
                                        style="vertical-align: {{ isset($practice->policy_list) && count($practice->policy_list) > 0 ? 'top' : 'middle' }};">
                                        @if (isset($practice->policy_list) && count($practice->policy_list) > 0)
                                            <div style="font-size: 9pt;">
                                                @foreach ($practice->policy_list as $line)
                                                    <div style="margin-bottom: 1px;">• {{ $line }}</div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div
                                                style="color: #6c757d; font-size: 9pt; font-style: italic; text-align: center;">
                                                Belum ada Kebijakan / Prosedur
                                            </div>
                                        @endif
                                    </td>
                                    <td
                                        style="vertical-align: {{ isset($practice->execution_list) && count($practice->execution_list) > 0 ? 'top' : 'middle' }};">
                                        @if (isset($practice->execution_list) && count($practice->execution_list) > 0)
                                            <div style="font-size: 9pt;">
                                                @foreach ($practice->execution_list as $line)
                                                    <div style="margin-bottom: 1px;">• {{ $line }}</div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div
                                                style="color: #6c757d; font-size: 9pt; font-style: italic; text-align: center;">
                                                Belum ada Evidence / Bukti Pelaksanaan
                                            </div>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Kesimpulan Section --}}
            <div style="margin-top: 5px; border: 1px solid #dee2e6;">
                <div style="background-color: #0f2b5c; color: white; padding: 5px; font-weight: bold; font-size: 9pt;">
                    Kesimpulan
                </div>
                <div style="padding: 10px; background-color: white;">
                    @php
                        $kesimpulan = is_array($objective->saved_note) ? $objective->saved_note['kesimpulan'] : '';
                        $kesimpulan = trim($kesimpulan);
                    @endphp
                    @if (empty($kesimpulan) || $kesimpulan === '-')
                        <p style="margin: 0; font-size: 10pt; color: #6c757d; font-style: italic;">Belum ada kesimpulan
                        </p>
                    @else
                        <p style="margin: 0; font-size: 10pt; color: #000;">
                            {!! nl2br(e($kesimpulan)) !!}
                        </p>
                    @endif
                </div>
            </div>

            {{-- Rekomendasi Section --}}
            <div style="margin-top: 5px; border: 1px solid #dee2e6;">
                <div style="background-color: #0f2b5c; color: white; padding: 5px; font-weight: bold; font-size: 9pt;">
                    Rekomendasi
                </div>
                <div style="padding: 10px; background-color: white;">
                    @php
                        $rekomendasi = is_array($objective->saved_note) ? $objective->saved_note['rekomendasi'] : '';
                        $rekomendasi = trim($rekomendasi);
                    @endphp
                    @if (empty($rekomendasi) || $rekomendasi === '-')
                        <p style="margin: 0; font-size: 10pt; color: #6c757d; font-style: italic;">Belum ada rekomendasi
                        </p>
                    @else
                        <p style="margin: 0; font-size: 10pt; color: #000;">
                            {!! nl2br(e($rekomendasi)) !!}
                        </p>
                    @endif
                </div>
            </div>

            {{-- Roadmap Target Capability Section --}}
            <div style="margin-top: 5px; border: 1px solid #dee2e6;">
                <div style="background-color: #0f2b5c; color: white; padding: 5px; font-weight: bold; font-size: 9pt;">
                    Roadmap Target Capability
                </div>
                <div style="padding: 5px; background-color: white;">
                    @if (isset($roadmap) && isset($roadmap['objectives']) && $roadmap['objectives']->isNotEmpty())
                        <table style="width: 100%; border-collapse: collapse; border: 1px solid #000;">
                            <thead>
                                <tr>
                                    <th rowspan="2"
                                        style="background-color: #0f2b5c; color: white; border: 1px solid #000; font-size: 8pt; vertical-align: middle; width: 60px;">
                                        Objective ID</th>
                                    <th rowspan="2"
                                        style="background-color: #0f2b5c; color: white; border: 1px solid #000; font-size: 8pt; vertical-align: middle; width: 150px;">
                                        Objective Name</th>
                                    <th colspan="2"
                                        style="background-color: #0f2b5c; color: white; border: 1px solid #000; font-size: 8pt; vertical-align: middle;">
                                        Hasil Assessment {{ $evaluation->tahun ?? '2025' }}
                                    </th>
                                    @foreach ($roadmap['years'] as $year)
                                        <th colspan="2"
                                            style="background-color: #0f2b5c; color: white; border: 1px solid #000; font-size: 8pt; vertical-align: middle;">
                                            {{ $year }}
                                        </th>
                                    @endforeach
                                </tr>
                                <tr>
                                    <th
                                        style="background-color: #0f2b5c; color: white; border: 1px solid #000; font-size: 7pt; width: 35px;">
                                        Level</th>
                                    <th
                                        style="background-color: #0f2b5c; color: white; border: 1px solid #000; font-size: 7pt; width: 35px;">
                                        Rating</th>
                                    @foreach ($roadmap['years'] as $year)
                                        <th
                                            style="background-color: #0f2b5c; color: white; border: 1px solid #000; font-size: 7pt; width: 35px;">
                                            Level</th>
                                        <th
                                            style="background-color: #0f2b5c; color: white; border: 1px solid #000; font-size: 7pt; width: 35px;">
                                            Rating</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($roadmap['objectives'] as $obj)
                                    <tr>
                                        <td
                                            style="border: 1px solid #000; font-size: 8pt; text-align: center; font-weight: bold;">
                                            {{ str_replace('"', '', $obj->objective_id) }}</td>
                                        <td style="border: 1px solid #000; font-size: 8pt; text-align: left;">
                                            {{ str_replace('"', '', $obj->objective) }}
                                        </td>
                                        {{-- Current Year Assessment Results from Scorecard --}}
                                        @php
                                            // Find matching objective from $objectives to get scorecard data
                                            $scorecardObj = $objectives->firstWhere('objective_id', $obj->objective_id);
                                        @endphp
                                        <td style="border: 1px solid #000; font-size: 8pt; text-align: center;">
                                            {{ $scorecardObj->current_score ?? '-' }}
                                        </td>
                                        <td style="border: 1px solid #000; font-size: 8pt; text-align: center;">
                                            {{ $scorecardObj->rating_string ?? '-' }}
                                        </td>
                                        @foreach ($roadmap['years'] as $year)
                                            <td style="border: 1px solid #000; font-size: 8pt; text-align: center;">
                                                {{ data_get($obj->roadmap_values, "$year.level") ?? '-' }}
                                            </td>
                                            <td style="border: 1px solid #000; font-size: 8pt; text-align: center;">
                                                {{ data_get($obj->roadmap_values, "$year.rating") ?? '-' }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p style="margin: 0; font-size: 10pt; color: #6c757d; font-style: italic; text-align: center;">
                            Belum ada data roadmap
                        </p>
                    @endif
                </div>
            </div>

        </div>

        @if (!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>

</html>
