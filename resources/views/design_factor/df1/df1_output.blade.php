@extends('design_factor.cobitTools')
@section('cobit-tools-content')
@include('design_factor.cobitPagination')
<div class="container">
    <!-- Card Utama: Output Data Design Factor 1 -->
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow border-0 rounded">
                <!-- Card Header -->
                <div class="card-header bg-primary text-white text-center py-3">
                    <h4 class="mb-0">Design Factor 1 Output</h4>
                </div>

                <!-- Card Body: Data Verifikasi -->
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>DF ID</th>
                                    <th>Growth/Acquisition</th>
                                    <th>Innovation/Differentiation</th>
                                    <th>Cost Leadership </th>
                                    <th>Client Service/Stability</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ $designFactor->df_id }}</td>
                                    <td>{{ $designFactor->input1df1 }}</td>
                                    <td>{{ $designFactor->input2df1 }}</td>
                                    <td>{{ $designFactor->input3df1 }}</td>
                                    <td>{{ $designFactor->input4df1 }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Card Body: Row untuk Chart -->
                <div class="card-body">
                    <!-- Relative Importance Scores Table -->
                    <div class="row">
                        <div class="col-lg-6 mb-4">
                            <div class="card">
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
                                                    $score = $designFactorRelativeImportance->{'r_df1_' . ($index + 1)};
                                                    // Jika score > 0 (positif): gunakan bg-primary-subtle, jika score < 0: gunakan bg-danger-subtle
                                                    $scoreClass = $score > 0 ? 'bg-primary-subtle' : ($score < 0 ? 'bg-danger-subtle' : '');
                                                @endphp
                                                <tr>
                                                    <td class="text-center">{{ $index + 1 }}</td>
                                                    <td class="text-center">{{ $label }}</td>
                                                    <td class="text-center">{{ $designFactorScore->{'s_df1_' . ($index + 1)} }}</td>
                                                    <td class="text-center {{ $scoreClass }}">{{ $score }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Relative Importance Bar Chart -->
                        <div class="col-lg-6 mb-4">
                            <div class="card">
                                <div class="card-header text-center text-primary">
                                    Relative Importance (Bar Chart)
                                </div>
                                <div class="card-body">
                                    <div class="w-100" style="height: 600px;">
                                        <canvas id="relativeImportanceChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Radar Chart: Full-width -->
                    <div class="row">
                        <div class="col-lg-12 mb-4">
                            <div class="card">
                                <div class="card-header text-center">
                                    Radar Chart
                                </div>
                                <div class="card-body">
                                    <div class="w-100" style="height: 500px;">
                                        <canvas id="relativeImportanceRadarChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Card Body -->
            </div> <!-- End Card Utama -->
        </div>
    </div>
</div>

<!-- Script Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Data untuk Bar Chart Relative Importance DF1
    const relativeImportanceValues = [
        {{ $designFactorRelativeImportance->r_df1_1 }},
        {{ $designFactorRelativeImportance->r_df1_2 }},
        {{ $designFactorRelativeImportance->r_df1_3 }},
        {{ $designFactorRelativeImportance->r_df1_4 }},
        {{ $designFactorRelativeImportance->r_df1_5 }},
        {{ $designFactorRelativeImportance->r_df1_6 }},
        {{ $designFactorRelativeImportance->r_df1_7 }},
        {{ $designFactorRelativeImportance->r_df1_8 }},
        {{ $designFactorRelativeImportance->r_df1_9 }},
        {{ $designFactorRelativeImportance->r_df1_10 }},
        {{ $designFactorRelativeImportance->r_df1_11 }},
        {{ $designFactorRelativeImportance->r_df1_12 }},
        {{ $designFactorRelativeImportance->r_df1_13 }},
        {{ $designFactorRelativeImportance->r_df1_14 }},
        {{ $designFactorRelativeImportance->r_df1_15 }},
        {{ $designFactorRelativeImportance->r_df1_16 }},
        {{ $designFactorRelativeImportance->r_df1_17 }},
        {{ $designFactorRelativeImportance->r_df1_18 }},
        {{ $designFactorRelativeImportance->r_df1_19 }},
        {{ $designFactorRelativeImportance->r_df1_20 }},
        {{ $designFactorRelativeImportance->r_df1_21 }},
        {{ $designFactorRelativeImportance->r_df1_22 }},
        {{ $designFactorRelativeImportance->r_df1_23 }},
        {{ $designFactorRelativeImportance->r_df1_24 }},
        {{ $designFactorRelativeImportance->r_df1_25 }},
        {{ $designFactorRelativeImportance->r_df1_26 }},
        {{ $designFactorRelativeImportance->r_df1_27 }},
        {{ $designFactorRelativeImportance->r_df1_28 }},
        {{ $designFactorRelativeImportance->r_df1_29 }},
        {{ $designFactorRelativeImportance->r_df1_30 }},
        {{ $designFactorRelativeImportance->r_df1_31 }},
        {{ $designFactorRelativeImportance->r_df1_32 }},
        {{ $designFactorRelativeImportance->r_df1_33 }},
        {{ $designFactorRelativeImportance->r_df1_34 }},
        {{ $designFactorRelativeImportance->r_df1_35 }},
        {{ $designFactorRelativeImportance->r_df1_36 }},
        {{ $designFactorRelativeImportance->r_df1_37 }},
        {{ $designFactorRelativeImportance->r_df1_38 }},
        {{ $designFactorRelativeImportance->r_df1_39 }},
        {{ $designFactorRelativeImportance->r_df1_40 }}
    ];

    const relativeImportanceLabels = [
        'EDM01', 'EDM02', 'EDM03', 'EDM04', 'EDM05',
        'APO01', 'APO02', 'APO03', 'APO04', 'APO05',
        'APO06', 'APO07', 'APO08', 'APO09', 'APO10',
        'APO11', 'APO12', 'APO13', 'APO14', 'BAI01',
        'BAI02', 'BAI03', 'BAI04', 'BAI05', 'BAI06',
        'BAI07', 'BAI08', 'BAI09', 'BAI10', 'BAI11',
        'DSS01', 'DSS02', 'DSS03', 'DSS04', 'DSS05',
        'DSS06', 'MEA01', 'MEA02', 'MEA03', 'MEA04'
    ];

    // Inisialisasi Bar Chart (menggunakan data urutan asli)
    const ctxRelativeImportance = document.getElementById('relativeImportanceChart').getContext('2d');
    const relativeImportanceChart = new Chart(ctxRelativeImportance, {
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
                    ticks: { stepSize: 10 },
                    grid: {
                        color: ctx =>
                            ctx.tick.value === 0 ? 'rgba(0, 0, 0, 0.3)' : 'rgba(200, 200, 200, 0.3)',
                        lineWidth: ctx =>
                            ctx.tick.value === 0 ? 2 : 1
                    }
                },
                y: {
                    ticks: { maxTicksLimit: 40, autoSkip: false }
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
