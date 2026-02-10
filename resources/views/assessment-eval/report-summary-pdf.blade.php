<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
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

            {{-- Management Practices List --}}
            <div style="margin-top: 5px; margin-bottom: 5px;">
                <div
                    style="background-color: #0f2b5c; color: white; text-align: center; padding: 5px; font-weight: bold; font-size: 10pt;">
                    Management Practices List
                </div>
                <div style="border: 1px solid #dee2e6; padding: 5px; background-color: white;">
                    @php
                        $practices = $objective->practices;
                        $chunks = $practices->chunk(ceil($practices->count() / 3));
                    @endphp
                    <table style="width: 100%; border: none;">
                        <tr style="border: none;">
                            @foreach ($chunks as $chunk)
                                <td style="width: 33%; vertical-align: top; border: none; padding: 0 5px;">
                                    @foreach ($chunk as $practice)
                                        <div style="margin-bottom: 3px;">
                                            <span
                                                style="font-size: 0.75rem; margin-right: 3px;">{{ str_replace('"', '', $practice->practice_id) }}</span>
                                            <span
                                                style="color: black; font-size: 0.75rem;">{{ str_replace('"', '', $practice->practice_name) }}</span>
                                        </div>
                                    @endforeach
                                </td>
                            @endforeach
                            @for ($i = $chunks->count(); $i < 3; $i++)
                                <td style="width: 33%; border: none;"></td>
                            @endfor
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Detailed Table Section --}}
            <div style="margin-top: 5px;">
                <table class="table" style="border: 1px solid #000;">
                    <thead>
                        <tr>
                            <th
                                style="width: 50%; background-color: #0f2b5c; color: white; padding: 5px; font-size: 10pt;">
                                Kebijakan Pedoman /
                                Prosedur</th>
                            <th
                                style="width: 50%; background-color: #0f2b5c; color: white; padding: 5px; font-size: 10pt;">
                                Evidences / Bukti
                                Pelaksanaan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($objective->has_evidence)
                            <tr>
                                <td
                                    style="vertical-align: {{ isset($objective->policy_list) && count($objective->policy_list) > 0 ? 'top' : 'middle' }};">
                                    @if (isset($objective->policy_list) && count($objective->policy_list) > 0)
                                        <div style="font-size: 9pt;">
                                            @foreach ($objective->policy_list as $line)
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
                                    style="vertical-align: {{ isset($objective->execution_list) && count($objective->execution_list) > 0 ? 'top' : 'middle' }};">
                                    @if (isset($objective->execution_list) && count($objective->execution_list) > 0)
                                        <div style="font-size: 9pt;">
                                            @foreach ($objective->execution_list as $line)
                                                <div style="margin-bottom: 1px;">• {{ $line }}</div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div
                                            style="color: #6c757d; font-size: 9pt; font-style: italic; text-align: center;">
                                            Belum ada Evidences / Bukti Pelaksanaan
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @else
                            <tr>
                                <td colspan="2"
                                    style="text-align: center; font-style: italic; color: #6c757d; font-size: 9pt;">
                                    Belum ada Kebijakan & Bukti Pelaksanaan
                                </td>
                            </tr>
                        @endif
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
                                        style="background-color: #0f2b5c; color: white; border: 1px solid #000; font-size: 7pt; vertical-align: middle; width: 60px;">
                                        Objective ID</th>
                                    <th rowspan="2"
                                        style="background-color: #0f2b5c; color: white; border: 1px solid #000; font-size: 7pt; vertical-align: middle; width: 150px;">
                                        Objective Name</th>
                                    <th colspan="2"
                                        style="background-color: #0f2b5c; color: white; border: 1px solid #000; font-size: 7pt; vertical-align: middle;">
                                        Hasil Assessment {{ $evaluation->tahun ?? '2025' }}
                                    </th>
                                    @foreach ($roadmap['years'] as $year)
                                        <th colspan="2"
                                            style="background-color: #0f2b5c; color: white; border: 1px solid #000; font-size: 7pt; vertical-align: middle;">
                                            {{ $year }}
                                        </th>
                                    @endforeach
                                </tr>
                                <tr>
                                    <th
                                        style="background-color: #0f2b5c; color: white; border: 1px solid #000; font-size: 6pt; width: 35px;">
                                        Level</th>
                                    <th
                                        style="background-color: #0f2b5c; color: white; border: 1px solid #000; font-size: 6pt; width: 35px;">
                                        Rating</th>
                                    @foreach ($roadmap['years'] as $year)
                                        <th
                                            style="background-color: #0f2b5c; color: white; border: 1px solid #000; font-size: 6pt; width: 35px;">
                                            Level</th>
                                        <th
                                            style="background-color: #0f2b5c; color: white; border: 1px solid #000; font-size: 6pt; width: 35px;">
                                            Rating</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($roadmap['objectives'] as $obj)
                                    <tr>
                                        <td
                                            style="border: 1px solid #000; font-size: 7pt; text-align: center; font-weight: bold;">
                                            {{ str_replace('"', '', $obj->objective_id) }}</td>
                                        <td style="border: 1px solid #000; font-size: 7pt; text-align: left;">
                                            {{ str_replace('"', '', $obj->objective) }}
                                        </td>
                                        {{-- Current Year Assessment Results from Scorecard --}}
                                        @php
                                            // Find matching objective from $objectives to get scorecard data
                                            $scorecardObj = $objectives->firstWhere('objective_id', $obj->objective_id);
                                        @endphp
                                        <td style="border: 1px solid #000; font-size: 7pt; text-align: center;">
                                            {{ $scorecardObj->current_score ?? '-' }}
                                        </td>
                                        <td style="border: 1px solid #000; font-size: 7pt; text-align: center;">
                                            {{ $scorecardObj->rating_string ?? '-' }}
                                        </td>
                                        @foreach ($roadmap['years'] as $year)
                                            <td style="border: 1px solid #000; font-size: 7pt; text-align: center;">
                                                {{ data_get($obj->roadmap_values, "$year.level") ?? '-' }}
                                            </td>
                                            <td style="border: 1px solid #000; font-size: 7pt; text-align: center;">
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
