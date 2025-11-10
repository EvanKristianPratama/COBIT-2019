@extends('cobit2019.cobitTools')
@section('cobit-tools-content')
@include('cobit2019.cobitPagination')
<div class="container">
    <!-- Card Utama: Output Data Design Factor 3 -->
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card shadow border-0 rounded">
                <!-- Card Header -->
                <div class="card-header bg-primary text-white text-center py-3">
                    <h4 class="mb-0">Design Factor 3 Output</h4>
                </div>

                <!-- Card Body: Data Verifikasi (menampilkan input-data DF3A) -->
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>DF ID</th>
                                    <th>Input 1</th>
                                    <th>Input 2</th>
                                    <th>Input 3</th>
                                    <th>Input 4</th>
                                    <th>Input 5</th>
                                    <th>Input 6</th>
                                    <th>Input 7</th>
                                    <th>Input 8</th>
                                    <th>Input 9</th>
                                    <th>Input 10</th>
                                    <th>Input 11</th>
                                    <th>Input 12</th>
                                    <th>Input 13</th>
                                    <th>Input 14</th>
                                    <th>Input 15</th>
                                    <th>Input 16</th>
                                    <th>Input 17</th>
                                    <th>Input 18</th>
                                    <th>Input 19</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ $designFactor3a->df_id }}</td>
                                    <td>{{ $designFactor3a->input1df3 }}</td>
                                    <td>{{ $designFactor3a->input2df3 }}</td>
                                    <td>{{ $designFactor3a->input3df3 }}</td>
                                    <td>{{ $designFactor3a->input4df3 }}</td>
                                    <td>{{ $designFactor3a->input5df3 }}</td>
                                    <td>{{ $designFactor3a->input6df3 }}</td>
                                    <td>{{ $designFactor3a->input7df3 }}</td>
                                    <td>{{ $designFactor3a->input8df3 }}</td>
                                    <td>{{ $designFactor3a->input9df3 }}</td>
                                    <td>{{ $designFactor3a->input10df3 }}</td>
                                    <td>{{ $designFactor3a->input11df3 }}</td>
                                    <td>{{ $designFactor3a->input12df3 }}</td>
                                    <td>{{ $designFactor3a->input13df3 }}</td>
                                    <td>{{ $designFactor3a->input14df3 }}</td>
                                    <td>{{ $designFactor3a->input15df3 }}</td>
                                    <td>{{ $designFactor3a->input16df3 }}</td>
                                    <td>{{ $designFactor3a->input17df3 }}</td>
                                    <td>{{ $designFactor3a->input18df3 }}</td>
                                    <td>{{ $designFactor3a->input19df3 }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Card Body: Relative Importance Scores dan Chart -->
                <div class="card-body">
                    <div class="row">
                        <!-- Bagian Tabel Relative Importance Scores -->
                        <div class="col-lg-6 mb-4">
                            <div class="card">
                                <!-- Header tabel dengan judul -->
                                <div class="card-header text-center text-primary">
                                    Relative Importance Scores
                                </div>
                                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                                    <table class="table table-bordered table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center text-primary">Index</th>
                                                <th class="text-center text-primary">Label</th>
                                                <th class="text-center text-primary">Score</th>
                                                <th class="text-center text-primary">Relative Importance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                // Array label untuk tabel relative importance (40 label)
                                                $labels = [
                                                    'EDM01', 'EDM02', 'EDM03', 'EDM04', 'EDM05',
                                                    'APO01', 'APO02', 'APO03', 'APO04', 'APO05',
                                                    'APO06', 'APO07', 'APO08', 'APO09', 'APO10',
                                                    'APO11', 'APO12', 'APO13', 'APO14',
                                                    'BAI01', 'BAI02', 'BAI03', 'BAI04', 'BAI05', 'BAI06', 'BAI07', 'BAI08', 'BAI09', 'BAI10', 'BAI11',
                                                    'DSS01', 'DSS02', 'DSS03', 'DSS04', 'DSS05', 'DSS06',
                                                    'MEA01', 'MEA02', 'MEA03', 'MEA04'
                                                ];
                                            @endphp

                                            @foreach($labels as $index => $label)
                                                @php
                                                    // Mengambil nilai score dan relative importance dari data DF3
                                                    $score = $designFactor3a->{'s_df3_' . ($index + 1)};
                                                    $relative = $designFactorRelativeImportance->{'r_df3_' . ($index + 1)};
                                                    // Menentukan kelas CSS berdasarkan nilai relative importance:
                                                    // - Positif: bg-primary-subtle
                                                    // - Negatif: bg-danger-subtle
                                                    // - Nol: tanpa kelas tambahan
                                                    $scoreClass = $relative > 0 ? 'bg-primary-subtle' : ($relative < 0 ? 'bg-danger-subtle' : '');
                                                @endphp
                                                <tr>
                                                    <td class="text-center">{{ $index + 1 }}</td>
                                                    <td class="text-center">{{ $label }}</td>
                                                    <td class="text-center">{{ $score }}</td>
                                                    <td class="text-center {{ $scoreClass }}">{{ $relative }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Bagian Chart: Relative Importance Bar Chart -->
                        <div class="col-lg-6 mb-4">
                            <div class="card">
                                <!-- Header chart -->
                                <div class="card-header text-center text-primary">
                                    Relative Importance (Bar Chart)
                                </div>
                                <div class="card-body">
                                    <!-- Canvas untuk Chart.js Bar Chart -->
                                    <div class="w-100" style="height: 600px;">
                                        <canvas id="relativeImportanceBarChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Baris untuk Relative Importance Radar Chart (Full-width) -->
                    <div class="row">
                        <div class="col-lg-12 mb-4">
                            <div class="card">
                                <!-- Header chart -->
                                <div class="card-header text-center text-primary">
                                    Relative Importance (Radar Chart)
                                </div>
                                <div class="card-body">
                                    <!-- Canvas untuk Chart.js Radar Chart -->
                                    <div class="w-100" style="height: 600px;">
                                        <canvas id="relativeImportanceRadarChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Card Body -->
            </div>
        </div>
    </div>
</div>

<!-- Inisialisasi Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Data Relative Importance DF3, diambil dari controller
    const relativeImportanceValues = [
        {{ $designFactorRelativeImportance->r_df3_1 }},
        {{ $designFactorRelativeImportance->r_df3_2 }},
        {{ $designFactorRelativeImportance->r_df3_3 }},
        {{ $designFactorRelativeImportance->r_df3_4 }},
        {{ $designFactorRelativeImportance->r_df3_5 }},
        {{ $designFactorRelativeImportance->r_df3_6 }},
        {{ $designFactorRelativeImportance->r_df3_7 }},
        {{ $designFactorRelativeImportance->r_df3_8 }},
        {{ $designFactorRelativeImportance->r_df3_9 }},
        {{ $designFactorRelativeImportance->r_df3_10 }},
        {{ $designFactorRelativeImportance->r_df3_11 }},
        {{ $designFactorRelativeImportance->r_df3_12 }},
        {{ $designFactorRelativeImportance->r_df3_13 }},
        {{ $designFactorRelativeImportance->r_df3_14 }},
        {{ $designFactorRelativeImportance->r_df3_15 }},
        {{ $designFactorRelativeImportance->r_df3_16 }},
        {{ $designFactorRelativeImportance->r_df3_17 }},
        {{ $designFactorRelativeImportance->r_df3_18 }},
        {{ $designFactorRelativeImportance->r_df3_19 }},
        {{ $designFactorRelativeImportance->r_df3_20 }},
        {{ $designFactorRelativeImportance->r_df3_21 }},
        {{ $designFactorRelativeImportance->r_df3_22 }},
        {{ $designFactorRelativeImportance->r_df3_23 }},
        {{ $designFactorRelativeImportance->r_df3_24 }},
        {{ $designFactorRelativeImportance->r_df3_25 }},
        {{ $designFactorRelativeImportance->r_df3_26 }},
        {{ $designFactorRelativeImportance->r_df3_27 }},
        {{ $designFactorRelativeImportance->r_df3_28 }},
        {{ $designFactorRelativeImportance->r_df3_29 }},
        {{ $designFactorRelativeImportance->r_df3_30 }},
        {{ $designFactorRelativeImportance->r_df3_31 }},
        {{ $designFactorRelativeImportance->r_df3_32 }},
        {{ $designFactorRelativeImportance->r_df3_33 }},
        {{ $designFactorRelativeImportance->r_df3_34 }},
        {{ $designFactorRelativeImportance->r_df3_35 }},
        {{ $designFactorRelativeImportance->r_df3_36 }},
        {{ $designFactorRelativeImportance->r_df3_37 }},
        {{ $designFactorRelativeImportance->r_df3_38 }},
        {{ $designFactorRelativeImportance->r_df3_39 }},
        {{ $designFactorRelativeImportance->r_df3_40 }}
    ];

    // Label-label untuk Relative Importance (sesuai dengan urutan nilai)
    const relativeImportanceLabels = [
        'EDM01', 'EDM02', 'EDM03', 'EDM04', 'EDM05',
        'APO01', 'APO02', 'APO03', 'APO04', 'APO05',
        'APO06', 'APO07', 'APO08', 'APO09', 'APO10',
        'APO11', 'APO12', 'APO13', 'APO14',
        'BAI01', 'BAI02', 'BAI03', 'BAI04', 'BAI05', 'BAI06', 'BAI07', 'BAI08', 'BAI09', 'BAI10', 'BAI11',
        'DSS01', 'DSS02', 'DSS03', 'DSS04', 'DSS05', 'DSS06',
        'MEA01', 'MEA02', 'MEA03', 'MEA04'
    ];

    /* ------------------ Bar Chart Initialization ------------------ */
    const ctxBar = document.getElementById('relativeImportanceBarChart').getContext('2d');
    const barChart = new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: relativeImportanceLabels,
            datasets: [{
                label: 'Relative Importance Score',
                data: relativeImportanceValues,
                backgroundColor: relativeImportanceValues.map(value =>
                    value > 0 ? 'rgba(54, 162, 235, 0.6)' :
                    value < 0 ? 'rgba(255, 99, 132, 0.6)' :
                    'rgba(201, 201, 201, 0.6)'
                ),
                borderColor: relativeImportanceValues.map(value =>
                    value > 0 ? 'rgba(54, 162, 235, 1)' :
                    value < 0 ? 'rgba(255, 99, 132, 1)' :
                    'rgba(201, 201, 201, 1)'
                ),
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    max: 100,
                    min: -100,
                    beginAtZero: true,
                    ticks: { stepSize: 20 },
                    grid: {
                        color: ctx => ctx.tick.value === 0 ? 'rgba(0, 0, 0, 0.3)' : 'rgba(200, 200, 200, 0.3)',
                        lineWidth: ctx => ctx.tick.value === 0 ? 2 : 1
                    }
                },
                y: {
                    ticks: { autoSkip: false, maxTicksLimit: 40 }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => {
                            let label = ctx.dataset.label || '';
                            if (label) label += ': ';
                            label += ctx.raw >= 0 ? '+' + ctx.raw : ctx.raw;
                            return label;
                        }
                    }
                }
            }
        }
    });

// Membalik urutan label dan data khusus untuk Radar Chart,
    // sehingga urutannya akan mulai dari MEA04 ke EDM01 searah jarum jam.
    const reversedLabels = [...relativeImportanceLabels].reverse();
    const reversedValues = [...relativeImportanceValues].reverse();

    // Inisialisasi Radar Chart
    const ctxRadar = document.getElementById('relativeImportanceRadarChart').getContext('2d');
    const radarChart = new Chart(ctxRadar, {
        type: 'radar',
        data: {
            labels: reversedLabels,
            datasets: [{
                label: 'Relative Importance',
                data: reversedValues,
                backgroundColor: 'rgba(235, 54, 54, 0.2)',
                borderColor: reversedValues.map(value =>
                    value < 0 ? 'rgba(255, 99, 132, 1)' : 'rgba(54, 162, 235, 1)'
                ),
                borderWidth: 2,
                pointBackgroundColor: reversedValues.map(value =>
                    value < 0 ? 'rgba(255, 99, 132, 1)' : 'rgba(54, 162, 235, 1)'
                ),
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: reversedValues.map(value =>
                    value < 0 ? 'rgba(255, 99, 132, 1)' : 'rgba(54, 162, 235, 1)'
                ),
                tension: 0.4
            }]
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                r: {
                    suggestedMin: -100,
                    suggestedMax: 100,
                    ticks: { stepSize: 25 },
                    pointLabels: { font: { size: 10 } },
                    angleLines: { color: 'rgba(200, 200, 200, 0.3)' },
                    grid: { color: 'rgba(200, 200, 200, 0.3)' }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => {
                            let lbl = ctx.dataset.label || '';
                            if (lbl) lbl += ': ';
                            lbl += ctx.raw >= 0 ? '+' + ctx.raw : ctx.raw;
                            return lbl;
                        }
                    }
                }
            },
            elements: { line: { tension: 0.4 } }
        }
    });
});
</script>
@endsection
