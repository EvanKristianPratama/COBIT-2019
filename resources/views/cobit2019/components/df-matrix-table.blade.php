@php
    $id = $id ?? 'df-matrix';
    $dfCode = strtoupper($dfCode ?? 'DF');
    $columns = array_values($columns ?? []);
    $matrix = $matrix ?? [];
    $rowLabels = $rowLabels ?? \App\Data\Cobit\Df10Data::getObjectiveLabels();
    $note = $note ?? 'Matriks ini digunakan untuk kalkulasi score dan relative importance.';

    $flat = [];
    foreach ($matrix as $row) {
        if (!is_array($row)) {
            continue;
        }
        foreach ($row as $value) {
            if (is_numeric($value)) {
                $flat[] = (float) $value;
            }
        }
    }

    $minValue = !empty($flat) ? min($flat) : 0.0;
    $maxValue = !empty($flat) ? max($flat) : 1.0;
    $range = ($maxValue - $minValue) > 0 ? ($maxValue - $minValue) : 1.0;
@endphp

@once
    <style>
        .df-matrix-card .nav-tabs .nav-link {
            font-weight: 600;
            color: #495057;
        }

        .df-matrix-card .nav-tabs .nav-link.active {
            color: #0d6efd;
        }

        .df-matrix-table {
            min-width: max-content;
            margin-bottom: 0;
        }

        .df-matrix-table th,
        .df-matrix-table td {
            border-color: #e5e7eb;
            white-space: nowrap;
            text-align: center;
            vertical-align: middle;
            line-height: 1.2;
            padding: 0.45rem 0.55rem;
        }

        .df-matrix-table .matrix-corner {
            background: #8a6a00;
            color: #fff;
            font-weight: 700;
            min-width: 140px;
        }

        .df-matrix-table .matrix-col-header {
            background: #111317;
            color: #fff;
            font-weight: 600;
            min-width: 95px;
        }

        .df-matrix-table .matrix-row-label {
            background: #0f1114;
            color: #fff;
            font-weight: 700;
            min-width: 110px;
        }

        .df-matrix-table .matrix-row-label.matrix-row-label-alt {
            background: #787476;
        }
    </style>
@endonce

<div class="card mt-4 df-matrix-card">
    <div class="card-header p-0 border-0 bg-white">
        <ul class="nav nav-tabs px-3 pt-3" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#{{ $id }}-matrix" type="button" role="tab">
                    Information Matrix
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#{{ $id }}-legend" type="button" role="tab">
                    Legend
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">
            <div class="tab-pane fade show active" id="{{ $id }}-matrix" role="tabpanel">
                <div class="table-responsive" style="max-height: 620px;">
                    <table class="table table-bordered table-sm df-matrix-table">
                        <thead>
                            <tr>
                                <th class="matrix-corner">{{ $dfCode }}</th>
                                @foreach ($columns as $column)
                                    <th class="matrix-col-header">{{ $column }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($matrix as $rowIndex => $row)
                                @php
                                    $rowLabel = $rowLabels[$rowIndex] ?? ('OBJ' . str_pad((string) ($rowIndex + 1), 2, '0', STR_PAD_LEFT));
                                    $isAltRowLabel = preg_match('/^(APO|DSS)/', $rowLabel) === 1;
                                    $rowLabelClass = $isAltRowLabel ? 'matrix-row-label matrix-row-label-alt' : 'matrix-row-label';
                                @endphp
                                <tr>
                                    <th class="{{ $rowLabelClass }}">{{ $rowLabel }}</th>
                                    @foreach ($columns as $colIndex => $unused)
                                        @php
                                            $value = (float) ($row[$colIndex] ?? 0);
                                            $normalized = max(0, min(1, ($value - $minValue) / $range));
                                            $alpha = 0.12 + (0.50 * $normalized);
                                            $textColor = $alpha >= 0.35 ? '#ffffff' : '#0f172a';
                                        @endphp
                                        <td style="background-color: rgba(37, 99, 165, {{ number_format($alpha, 3, '.', '') }}); color: {{ $textColor }}; font-weight: 600;">
                                            {{ number_format($value, 1, ',', '.') }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="{{ $id }}-legend" role="tabpanel">
                <div class="small text-muted">
                    {{ $note }}
                </div>
                <div class="d-flex align-items-center gap-3 mt-3">
                    <span class="badge text-bg-light border">Nilai rendah</span>
                    <div style="height:10px; width:180px; border-radius:9999px; background: linear-gradient(90deg, rgba(37,99,165,0.12) 0%, rgba(37,99,165,0.62) 100%);"></div>
                    <span class="badge text-bg-light border">Nilai tinggi</span>
                </div>
            </div>
        </div>
    </div>
</div>
