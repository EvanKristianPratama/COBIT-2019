@extends('cobit2019.cobitTools')
@section('cobit-tools-content')
    @include('cobit2019.cobitPagination')
    <div class="container">
        <!-- Card Utama -->
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow border-0 rounded">
                    <!-- Card Header -->
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h4 class="mb-0">Design Factor 1 - Importance of Each Enterprise Strategy Archetype</h4>
                    </div>

                    <!-- Card Body -->
                    <div class="card-body p-4">
                        @php
                            $historyValues = [];
                            if (!empty($historyInputs) && is_array($historyInputs)) {
                                $historyValues = [
                                    'strategy_archetype' => $historyInputs[0] ?? null,
                                    'current_performance' => $historyInputs[1] ?? null,
                                    'future_goals' => $historyInputs[2] ?? null,
                                    'alignment_with_it' => $historyInputs[3] ?? null,
                                ];
                            }
                        @endphp


                            {{-- Panel Admin: Distribusi Jawaban (Chart) --}}
                            @php
                                $isAdmin = in_array(strtolower(trim(Auth::user()->role ?? '')), ['admin','administrator','pic'], true);
                                $df1Fields = [
                                    'input1df1' => 'Growth/Acquisition',
                                    'input2df1' => 'Innovation/Differentiation',
                                    'input3df1' => 'Cost Leadership',
                                    'input4df1' => 'Client Service/Stability',
                                ];
                                $df1Freqs = [];
                                $df1Totals = [];
                                foreach ($df1Fields as $f => $label) {
                                    $df1Freqs[$f] = [1=>0,2=>0,3=>0,4=>0,5=>0];
                                    $df1Totals[$f] = 0;
                                }
                                if (!empty($allSubmissions) && $allSubmissions->isNotEmpty()) {
                                    foreach ($allSubmissions as $r) {
                                        foreach ($df1Fields as $f => $label) {
                                            $val = (int)($r->{$f} ?? 0);
                                            if ($val >= 1 && $val <= 5) {
                                                $df1Freqs[$f][$val]++;
                                                $df1Totals[$f]++;
                                            }
                                        }
                                    }
                                }
                            @endphp
                            @if($isAdmin)
                                <div class="mb-3">
                                    <div class="card shadow-sm border-0">
                                        <div class="card-header bg-primary text-white fw-bold text-center">
                                            Panel - Distribusi Jawaban Responden
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="accordion" id="adminStatsAccordion">
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="adminStatsHeading">
                                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#adminStatsCollapse" aria-expanded="true" aria-controls="adminStatsCollapse">
                                                            Statistik Distribusi
                                                        </button>
                                                    </h2>
                                                    <div id="adminStatsCollapse" class="accordion-collapse collapse show" aria-labelledby="adminStatsHeading" data-bs-parent="#adminStatsAccordion">
                                                        <div class="accordion-body p-2">
                                                            <div class="table-responsive">
                                                                <table class="table table-bordered table-striped table-sm align-middle mb-0">
                                                                    <thead class="table-light align-middle small">
                                                                        <tr>
                                                                            <th class="text-center align-middle" style="width:220px;">Value</th>
                                                                            <th class="text-center align-middle" style="width:80px;">Total Responden</th>
                                                                            <th class="text-center align-middle">Distribusi </th>
                                                                            <th class="text-center align-middle" style="width:160px;">Modus (Mayoritas)</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach($df1Fields as $f => $label)
                                                                            @php
                                                                                $freqs = $df1Freqs[$f] ?? [1=>0,2=>0,3=>0,4=>0,5=>0];
                                                                                $maxVal = max($freqs);
                                                                                $suggestions = collect($freqs)->filter(fn($v) => $v === $maxVal && $v > 0)->keys()->all();
                                                                            @endphp
                                                                            <tr class="small">
                                                                                <td class="fw-semibold align-middle text-center">{{ $label }}</td>
                                                                                <td class="text-center align-middle">{{ $df1Totals[$f] }}</td>
                                                                                <td class="align-middle">
                                                                                    <div style="height:90px;max-width:320px;">
                                                                                        <canvas id="df1_admin_{{ $f }}_chart" height="90" style="height:90px;"></canvas>
                                                                                    </div>
                                                                                </td>
                                                                                <td class="text-center align-middle">
                                                                                    @if(count($suggestions) > 0)
                                                                                        @foreach($suggestions as $s)
                                                                                            <span class="badge bg-primary me-1">{{ $s }}</span>
                                                                                        @endforeach
                                                                                    @else
                                                                                        <span class="text-muted">-</span>
                                                                                    @endif
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                    <!-- Accordion Detil Jawaban Per User -->
                                <div class="mb-3">
                                    <div class="accordion" id="df1UserAccordion">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="df1UserAccordionHeading">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#df1UserAccordionCollapse" aria-expanded="false" aria-controls="df1UserAccordionCollapse">
                                                    Lihat Detil Jawaban Per User
                                                </button>
                                            </h2>
                                            <div id="df1UserAccordionCollapse" class="accordion-collapse collapse" aria-labelledby="df1UserAccordionHeading" data-bs-parent="#df1UserAccordion">
                                                <div class="accordion-body p-2">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-sm align-middle mb-0">
                                                            <thead class="table bg-black small">
                                                                <tr>
                                                                    <th class="text-center align-middle ">No</th>
                                                                    <th class="align-middle">Nama</th>
                                                                    <th class="align-middle">Email</th>
                                                                    <th class="align-middle">Grup Fungsi</th>
                                                                    @foreach($df1Fields as $f => $label)
                                                                        <th class="text-center align-middle">{{ $label }}</th>
                                                                    @endforeach
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @forelse($allSubmissions ?? [] as $i => $row)
                                                                    <tr class="small">
                                                                        <td class="text-center align-middle">{{ $i+1 }}</td>
                                                                        <td class="align-middle">{{ $users[$row->id] ?? '-' }}</td>
                                                                        <td class="align-middle">{{ $email[$row->id] ?? '-' }}</td>
                                                                        <td class="align-middle">{{ $jabatan[$row->id] ?? '-' }}</td>
                                                                        @foreach($df1Fields as $f => $label)
                                                                            <td class="text-center align-middle">{{ $row->{$f} ?? '-' }}</td>
                                                                        @endforeach
                                                                    </tr>
                                                                @empty
                                                                    <tr><td colspan="{{ 3+count($df1Fields) }}" class="text-center text-muted">Belum ada data</td></tr>
                                                                @endforelse
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            
                                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                                <script>
                                document.addEventListener('DOMContentLoaded', function () {
                                    const freqs = @json($df1Freqs);
                                    Object.keys(freqs).forEach(function(f) {
                                        const ctx = document.getElementById('df1_admin_' + f + '_chart');
                                        if (!ctx) return;
                                        const data = [freqs[f][1], freqs[f][2], freqs[f][3], freqs[f][4], freqs[f][5]];
                                        new Chart(ctx, {
                                            type: 'bar',
                                            data: {
                                                labels: ['1','2','3','4','5'],
                                                datasets: [{
                                                    label: 'Jumlah',
                                                    data: data,
                                                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                                                    borderColor: 'rgba(54, 162, 235, 1)',
                                                    borderWidth: 1
                                                }]
                                            },
                                            options: {
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                plugins: { legend: { display: false } },
                                                scales: {
                                                    x: { grid: { display: false } },
                                                    y: { beginAtZero: true, precision: 0, stepSize: 1, max: 10 }
                                                }
                                            }
                                        });
                                    });
                                });
                                </script>
                            @endif

                            <form action="{{ route('df1.store') }}" method="POST" id="df1Form">
                            @csrf
                            <input type="hidden" name="df_id" value="{{ $id }}">

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <!-- Hint Untuk Suggestion -->
                                    <caption class="small mb-0 caption-top">
                                        <ul class="list-unstyled mb-0">
                                            <li><strong>Skala 5:</strong> Pilih 1 strategi perusahaan yang paling penting.
                                            </li>
                                            <li><strong>Skala 4:</strong> Pilih 1 strategi perusahaan yang kedua paling
                                                penting.</li>
                                            <li><strong>Skala 1–3:</strong> Pilih 2 strategi perusahaan yang tidak begitu
                                                penting.</li>
                                        </ul>
                                    </caption>
                                    <thead class="table-success">
                                        <tr>
                                            <th scope="col">Value</th>
                                            <th scope="col">Explanation</th>
                                            <th scope="col" class="align-middle">
                                                <div class="d-flex align-items-center">
                                                    <span>Importance</span>
                                                    <div class="d-flex align-items-center ms-auto">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" role="switch"
                                                                id="df1SuggestionSwitch">
                                                        </div>
                                                    </div>
                                                </div>
                                            </th>
                                            <th scope="col">Baseline</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $inputs = [
                                                'strategy_archetype' => ['Growth/Acquisition', 'The enterprise has a focus on growing (revenues).'],
                                                'current_performance' => ['Innovation/Differentiation', 'The enterprise has a focus on offering different and/or innovative products and services to their clients.'],
                                                'future_goals' => ['Cost Leadership', 'The enterprise has a focus on short-term cost minimization.'],
                                                'alignment_with_it' => ['Client Service/Stability', 'The enterprise has a focus on providing stable and client-oriented service.']
                                            ];
                                        @endphp

                                        @foreach($inputs as $name => $data)
                                            <tr>
                                                <td class="text-primary fw-bold">{{ $data[0] }}</td>
                                                <td>{{ $data[1] }}</td>
                                                <td>
                                                    <div class="d-flex justify-content-between">
                                                        @for ($i = 1; $i <= 5; $i++)
                                                            @php
                                                                $checkedVal = old($name, $historyValues[$name] ?? null);
                                                            @endphp
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio" name="{{ $name }}"
                                                                    id="{{ $name }}_{{ $i }}" value="{{ $i }}" required {{ ($checkedVal == $i) ? 'checked' : '' }}>
                                                                <label class="form-check-label small" for="{{ $name }}_{{ $i }}">
                                                                    {{ $i }}
                                                                </label>
                                                            </div>
                                                        @endfor
                                                    </div>
                                                </td>
                                                <td class="text-center fw-bold text-success fs-5">
                                                    3
                                                    <input type="hidden" name="baseline_{{ $name }}" value="3">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                <!-- Submit Button -->
                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-primary btn-lg px-5">
                                    Save
                                </button>
                            </div>

                            </div>
                             
                            <!-- Grafik Input Score -->
                            <div class="row mt-4">
                                <!-- Bar Chart Input Score -->
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100">
                                        <div class="card-header text-center">Bar Chart Input Score</div>
                                        <div class="card-body">
                                            <div class="w-100" style="height: 200px;">
                                                <canvas id="barChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Radar Chart Input Score -->
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100">
                                        <div class="card-header text-center">Radar Chart Input Score</div>
                                        <div class="card-body">
                                            <div class="w-100" style="height: 200px;">
                                                <canvas id="radarChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Layout: Relative Importance -->
                            <div class="row mt-4">
                                <!-- Relative Importance Radar Chart -->
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100">
                                        <div class="card-header text-center text-primary">
                                            Relative Importance (Radar Chart)
                                        </div>
                                        <div class="card-body">
                                            <div class="w-100" style="height: 400px;">
                                                <canvas id="relativeImportanceRadarChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Relative Importance Table -->
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100">
                                        <div class="card-header text-center text-primary">
                                            Relative Importance Table
                                        </div>
                                        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                            <table class="table table-bordered table-sm" id="results-table">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th class="text-center text-primary">Index</th>
                                                        <th class="text-center text-primary">DF1 Score</th>
                                                        <th class="text-center text-primary">Relative Importance</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Data akan diisi oleh JavaScript -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Relative Importance Bar Chart -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header text-center text-primary">
                                            Relative Importance (Bar Chart)
                                        </div>
                                        <div class="card-body">
                                            <div class="w-100" style="height: 700px;">
                                                <canvas id="relativeImportanceChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                           
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js Script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Render small distribution charts for each input's suggestion column
        // Chart shows counts for values 1..5 (even if some values = 0)
        document.addEventListener('DOMContentLoaded', function () {
            const frequencies = @json($aggregatedData['frequencies'] ?? []);
            const VALUE_LABELS = ['1','2','3','4','5'];
            Object.keys(frequencies).forEach(col => {
                const canvas = document.getElementById(`suggestion_${col}`);
                if (!canvas) return;
                const freqMap = frequencies[col] || {};
                // ensure labels 1..5 always present and ordered
                const labels = VALUE_LABELS.slice();
                const data = labels.map(l => parseInt(freqMap[l] ?? 0, 10));

                // choose color ramp
                const bg = labels.map((_, i) => `rgba(54, 162, 235, ${0.7 - i*0.06})`);
                const border = labels.map(_ => 'rgba(54, 162, 235, 1)');

                new Chart(canvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: bg,
                            borderColor: border,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { enabled: true, callbacks: {
                                label: ctx => `${ctx.label}: ${ctx.parsed.y} responden`
                            }}
                        },
                        scales: {
                            x: {
                                display: true,
                                grid: { display: false },
                                ticks: { font: { size: 11 } }
                            },
                            y: {
                                display: true,
                                beginAtZero: true,
                                ticks: { precision: 0, stepSize: 1 }
                            }
                        }
                    }
                });
            });
        });
        // end small suggestion charts (1..5)

        document.addEventListener('DOMContentLoaded', function () {
            // Inisialisasi Chart untuk Input Score
            const inputLabels = [
                'Growth/Acquisition',
                'Innovation/Differentiation',
                'Cost Leadership',
                'Client Service/Stability'
            ];
            // server-provided history inputs (may be null)
            const historyInputs = @json($historyInputs ?? null);
            // identifiers for local draft storage
            const ASSESSMENT_ID = @json(session('assessment_id') ?? null);
            const DF_ID = @json($id);

            // try to restore from local draft if no server history
            const draftKey = `df1:${ASSESSMENT_ID}:${DF_ID}`;
            let draftInputs = null;
            try {
                const raw = localStorage.getItem(draftKey);
                if (raw) draftInputs = JSON.parse(raw);
            } catch (e) { /* ignore parse errors */ }

            const initialInputData = Array.isArray(historyInputs) ? historyInputs : (Array.isArray(draftInputs) ? draftInputs : [0, 0, 0, 0]);

            // Bar Chart Input Score
            const barChart = new Chart(document.getElementById('barChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: inputLabels,
                    datasets: [{
                        label: 'Scores',
                        data: initialInputData,
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.5)',   // Biru untuk Growth/Acquisition
                            'rgba(255, 159, 64, 0.5)',   // Orange untuk Innovation/Differentiation
                            'rgba(201, 203, 207, 0.5)',  // Abu-abu untuk Cost Leadership
                            'rgba(255, 205, 86, 0.5)'    // Kuning untuk Client Service/Stability
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',     // Biru
                            'rgba(255, 159, 64, 1)',     // Orange
                            'rgba(201, 203, 207, 1)',    // Abu-abu
                            'rgba(255, 205, 86, 1)'      // Kuning
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    scales: {
                        x: { max: 5, display: false },
                        y: { ticks: { font: { size: 12 } } }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false }
                    }
                }
            });

            // Radar Chart Input Score
            const radarChartMain = new Chart(document.getElementById('radarChart').getContext('2d'), {
                type: 'radar',
                data: {
                    labels: inputLabels,
                    datasets: [{
                        label: 'Score Profile',
                        data: initialInputData,
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        pointBackgroundColor: 'rgba(255, 99, 132, 1)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgba(255, 99, 132, 1)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        r: {
                            beginAtZero: true,
                            max: 5,
                            ticks: { stepSize: 1, display: false },
                            pointLabels: { font: { size: 12 } }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false }
                    }
                }
            });

            // jika ada history inputs, set data awal pada chart input
            if (Array.isArray(initialInputData)) {
                barChart.data.datasets[0].data = initialInputData.slice();
                barChart.update();
                radarChartMain.data.datasets[0].data = initialInputData.slice();
                radarChartMain.update();
            }

            // Inisialisasi Chart untuk Relative Importance
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
            const initialRelativeData = @json($historyRIArray ?? null) || new Array(40).fill(0);
            const historyScoreData = @json($historyScoreArray ?? null);
            const historyRIData = @json($historyRIArray ?? null);

            // Radar Chart Relative Importance
            const relativeImportanceRadarChart = new Chart(
                document.getElementById('relativeImportanceRadarChart').getContext('2d'),
                {
                    type: 'radar',
                    data: {
                        labels: relativeImportanceLabels,
                        datasets: [{
                            label: 'Relative Importance',
                            data: initialRelativeData,
                            backgroundColor: 'rgba(235, 54, 54, 0.2)',
                            borderColor: initialRelativeData.map(val =>
                                val < 0 ? 'rgba(255, 99, 132, 1)' : 'rgba(54, 162, 235, 1)'
                            ),
                            borderWidth: 2,
                            pointBackgroundColor: initialRelativeData.map(val =>
                                val < 0 ? 'rgba(255, 99, 132, 1)' : 'rgba(54, 162, 235, 1)'
                            ),
                            pointBorderColor: '#fff',
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: initialRelativeData.map(val =>
                                val < 0 ? 'rgba(255, 99, 132, 1)' : 'rgba(54, 162, 235, 1)'
                            ),
                            borderJoinStyle: 'round',
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
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
                }
            );

            // Bar Chart Relative Importance
            const relativeImportanceChart = new Chart(
                document.getElementById('relativeImportanceChart').getContext('2d'),
                {
                    type: 'bar',
                    data: {
                        labels: relativeImportanceLabels,
                        datasets: [{
                            label: 'Relative Importance Score',
                            data: initialRelativeData,
                            backgroundColor: initialRelativeData.map(val =>
                                val > 0 ? 'rgba(54, 162, 235, 0.6)' :
                                    val < 0 ? 'rgba(255, 99, 132, 0.6)' :
                                        'rgba(201, 201, 201, 0.6)'
                            ),
                            borderColor: initialRelativeData.map(val =>
                                val > 0 ? 'rgba(54, 162, 235, 1)' :
                                    val < 0 ? 'rgba(255, 99, 132, 1)' :
                                        'rgba(201, 201, 201, 1)'
                            )
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
                }
            );

            // Data Konstan Perhitungan
            const DF1_BASELINE = [3, 3, 3, 3];
            const DF1_BASELINE_SCORE = [
                15, 24, 15, 22.5, 18, 12, 28.5, 24, 21, 33,
                22.5, 15, 21, 22.5, 21, 21, 18, 16.5, 12, 27,
                13.5, 13.5, 18, 25.5, 19.5, 18, 19.5, 12, 12, 27,
                13.5, 21, 18, 21, 16.5, 13.5, 12, 12, 12, 12
            ];
            const DF1map = [
                [1.0, 1.0, 1.5, 1.5],
                [1.5, 1.0, 2.0, 3.5],
                [1.0, 1.0, 1.0, 2.0],
                [1.5, 1.0, 4.0, 1.0],
                [1.5, 1.5, 1.0, 2.0],
                [1.0, 1.0, 1.0, 1.0],
                [3.5, 3.5, 1.5, 1.0],
                [4.0, 2.0, 1.0, 1.0],
                [1.0, 4.0, 1.0, 1.0],
                [3.5, 4.0, 2.5, 1.0],
                [1.5, 1.0, 4.0, 1.0],
                [2.0, 1.0, 1.0, 1.0],
                [1.0, 1.5, 1.0, 3.5],
                [1.0, 1.0, 1.5, 4.0],
                [1.0, 1.0, 3.5, 1.5],
                [1.0, 1.0, 1.0, 4.0],
                [1.0, 1.5, 1.0, 2.5],
                [1.0, 1.0, 1.0, 2.5],
                [1.0, 1.0, 1.0, 1.0],
                [4.0, 2.0, 1.5, 1.5],
                [1.0, 1.0, 1.5, 1.0],
                [1.0, 1.0, 1.5, 1.0],
                [1.0, 1.0, 1.0, 3.0],
                [4.0, 2.0, 1.0, 1.5],
                [2.0, 2.0, 1.0, 1.5],
                [1.5, 2.0, 1.0, 1.5],
                [1.0, 3.5, 1.0, 1.0],
                [1.0, 1.0, 1.0, 1.0],
                [1.0, 1.0, 1.0, 1.0],
                [3.5, 3.0, 1.5, 1.0],
                [1.0, 1.0, 1.0, 1.5],
                [1.0, 1.0, 1.0, 4.0],
                [1.0, 1.0, 1.0, 3.0],
                [1.0, 1.0, 1.0, 4.0],
                [1.0, 1.0, 1.0, 2.5],
                [1.0, 1.0, 1.0, 1.5],
                [1.0, 1.0, 1.0, 1.0],
                [1.0, 1.0, 1.0, 1.0],
                [1.0, 1.0, 1.0, 1.0],
                [1.0, 1.0, 1.0, 1.0]
            ];

            // ===============================================================
            // Fungsi Perhitungan dan Update Grafik untuk DF1
            // ===============================================================
            function calculateAndUpdate() {
                // ---------------------------------------------------------------
                // 1. Ambil nilai input dari assessment (misalnya, dari radio button)
                // ---------------------------------------------------------------
                const strategy = parseFloat(document.querySelector('input[name="strategy_archetype"]:checked')?.value || 3);
                const performance = parseFloat(document.querySelector('input[name="current_performance"]:checked')?.value || 3);
                const goals = parseFloat(document.querySelector('input[name="future_goals"]:checked')?.value || 3);
                const alignment = parseFloat(document.querySelector('input[name="alignment_with_it"]:checked')?.value || 3);

                // Gabungkan nilai input ke dalam satu array
                const DF1_INPUT = [strategy, performance, goals, alignment];

                // ---------------------------------------------------------------
                // 2. Hitung E12: Rata-rata dari semua nilai input assessment
                // ---------------------------------------------------------------
                const E12 = DF1_INPUT.reduce((sum, val) => sum + val, 0) / DF1_INPUT.length;

                // ---------------------------------------------------------------
                // 3. Hitung rata-rata baseline (misalnya, DF1_BASELINE sudah didefinisikan sebagai array)
                // ---------------------------------------------------------------
                const average_E7_E10 = DF1_BASELINE.reduce((sum, val) => sum + val, 0) / DF1_BASELINE.length;

                // ---------------------------------------------------------------
                // 4. Hitung E14: Perbandingan antara baseline rata-rata dengan input rata-rata
                // Jika E12 tidak nol, E14 = average_E7_E10 / E12, jika nol maka 0
                // ---------------------------------------------------------------
                const E14 = E12 !== 0 ? average_E7_E10 / E12 : 0;

                // ---------------------------------------------------------------
                // 5. Hitung DF1_SCORE: Perkalian matriks DF1map dengan input assessment
                // Misalnya, DF1map adalah matriks (array dua dimensi) yang telah didefinisikan secara global
                // ---------------------------------------------------------------
                const DF1_SCORE = DF1map.map(row =>
                    row.reduce((sum, value, index) => sum + value * DF1_INPUT[index], 0)
                );

                // ---------------------------------------------------------------
                // 6. Hitung DF1_RELATIVE_IMPORTANCE
                // Rumus: relative importance = (E14 * 100 * score / baselineScore) dibulatkan ke kelipatan 5, lalu dikurangi 100
                // Pastikan DF1_BASELINE_SCORE sudah didefinisikan sebagai array baseline score untuk tiap baris
                // ---------------------------------------------------------------
                const DF1_RELATIVE_IMPORTANCE = DF1_SCORE.map((score, index) => {
                    const baselineScore = DF1_BASELINE_SCORE[index];
                    return baselineScore !== 0 ? Math.round((E14 * 100 * score / baselineScore) / 5) * 5 - 100 : 0;
                });

                // ==================== UPDATE TABEL RELATIVE IMPORTANCE ====================
                const tbody = document.querySelector('#results-table tbody');
                tbody.innerHTML = Array.from({ length: DF1_SCORE.length }, (_, i) => {
                    const score = DF1_SCORE[i] ?? 0;
                    const relative = DF1_RELATIVE_IMPORTANCE[i] ?? 0;
                    const gmoLabels = relativeImportanceLabels[i] ?? 0;
                    const scoreClass = relative > 0
                        ? 'bg-primary-subtle text-dark'
                        : relative < 0
                            ? 'bg-danger-subtle text-dark'
                            : '';
                    return `<tr>
                            <td class="text-center">${gmoLabels}</td>
                            <td class="text-center">${score.toFixed(2)}</td>
                            <td class="text-center ${scoreClass}">${relative}</td>
                        </tr>`;
                }).join('');

                // Update grafik Relative Importance (Bar)
                relativeImportanceChart.data.datasets[0].data = DF1_RELATIVE_IMPORTANCE.slice();
                relativeImportanceChart.data.datasets[0].backgroundColor = DF1_RELATIVE_IMPORTANCE.map(val =>
                    val > 0 ? 'rgba(54, 162, 235, 0.6)' :
                        val < 0 ? 'rgba(255, 99, 132, 0.6)' :
                            'rgba(201, 201, 201, 0.6)'
                );
                relativeImportanceChart.data.datasets[0].borderColor = DF1_RELATIVE_IMPORTANCE.map(val =>
                    val > 0 ? 'rgba(54, 162, 235, 1)' :
                        val < 0 ? 'rgba(255, 99, 132, 1)' :
                            'rgba(201, 201, 201, 1)'
                );
                relativeImportanceChart.update();

                // Update grafik Relative Importance (Radar)
                relativeImportanceRadarChart.data.datasets[0].data = DF1_RELATIVE_IMPORTANCE.slice();
                relativeImportanceRadarChart.data.datasets[0].borderColor = DF1_RELATIVE_IMPORTANCE.map(val =>
                    val < 0 ? 'rgba(255, 99, 132, 1)' : 'rgba(54, 162, 235, 1)'
                );
                relativeImportanceRadarChart.data.datasets[0].pointBackgroundColor = DF1_RELATIVE_IMPORTANCE.map(val =>
                    val < 0 ? 'rgba(255, 99, 132, 1)' : 'rgba(54, 162, 235, 1)'
                );
                relativeImportanceRadarChart.update();
            }

            // ==================== EVENT LISTENER INPUT (RADIO BUTTON) ====================
            document.querySelectorAll('input[type="radio"]').forEach(radio => {
                radio.addEventListener('change', function () {
                    const newData = [
                        parseFloat(document.querySelector('input[name="strategy_archetype"]:checked')?.value || 0),
                        parseFloat(document.querySelector('input[name="current_performance"]:checked')?.value || 0),
                        parseFloat(document.querySelector('input[name="future_goals"]:checked')?.value || 0),
                        parseFloat(document.querySelector('input[name="alignment_with_it"]:checked')?.value || 0)
                    ];
                    barChart.data.datasets[0].data = newData;
                    barChart.update();
                    radarChartMain.data.datasets[0].data = newData;
                    radarChartMain.update();
                    calculateAndUpdate();

                    // save current inputs to local draft so navigating away keeps values
                    try {
                        const current = [
                            parseFloat(document.querySelector('input[name="strategy_archetype"]:checked')?.value || 0),
                            parseFloat(document.querySelector('input[name="current_performance"]:checked')?.value || 0),
                            parseFloat(document.querySelector('input[name="future_goals"]:checked')?.value || 0),
                            parseFloat(document.querySelector('input[name="alignment_with_it"]:checked')?.value || 0)
                        ];
                        localStorage.setItem(draftKey, JSON.stringify(current));
                    } catch (e) { /* ignore storage errors */ }
                });
            });

            // If server provided history, prefer it and clear any local draft so saved values take precedence
            try {
                if (Array.isArray(historyInputs) && historyInputs.length) {
                    try { localStorage.removeItem(draftKey); } catch (e) { /* ignore */ }
                }
            } catch (e) { /* ignore */ }

            // ==================== DF1 SUGGESTION SWITCH (UPDATE OPSI RADIO) ====================
            const radios = document.querySelectorAll('input[type="radio"]');
            const df1SuggestionSwitch = document.getElementById('df1SuggestionSwitch');
            const captionDf1Hint = document.querySelector('table caption');
            df1SuggestionSwitch.checked = true;

            function updateDisabledOptions() {
                if (df1SuggestionSwitch.checked) {
                    captionDf1Hint.style.display = '';
                    let counts = { "4": 0, "5": 0 };
                    radios.forEach(radio => {
                        if (radio.checked) {
                            if (radio.value === "4" || radio.value === "5") {
                                counts[radio.value]++;
                            }
                        }
                    });
                    radios.forEach(radio => {
                        if (radio.value === "4" || radio.value === "5") {
                            radio.disabled = counts[radio.value] >= 1 && !radio.checked;
                        } else {
                            const groupName = radio.name;
                            const selectedCount = document.querySelectorAll(`input[name="${groupName}"]:checked`).length;
                            radio.disabled = selectedCount >= 2 && !radio.checked;
                        }
                    });
                } else {
                    captionDf1Hint.style.display = 'none';
                    radios.forEach(radio => {
                        radio.disabled = false;
                    });
                }
            }
            radios.forEach(radio => radio.addEventListener("change", updateDisabledOptions));
            df1SuggestionSwitch.addEventListener("change", function () {
                radios.forEach(radio => {
                    radio.checked = false;
                    radio.disabled = false;
                });
                updateDisabledOptions();
            });
            updateDisabledOptions();

            // Panggil calculateAndUpdate() saat halaman selesai dimuat agar tabel dan grafik muncul dengan data default
            calculateAndUpdate();

            // Jika server menyediakan historyInputs, pastikan radios dan chart di-sync ke nilai terbaru
            try {
                if (Array.isArray(historyInputs) && historyInputs.length) {
                    const names = ['strategy_archetype','current_performance','future_goals','alignment_with_it'];
                    names.forEach((n, idx) => {
                        const val = historyInputs[idx];
                        const el = document.querySelector(`input[name="${n}"][value="${val}"]`);
                        if (el) el.checked = true;
                    });
                    // sync charts
                    if (barChart && radarChartMain) {
                        barChart.data.datasets[0].data = historyInputs.slice();
                        barChart.update();
                        radarChartMain.data.datasets[0].data = historyInputs.slice();
                        radarChartMain.update();
                    }
                    // recompute derived tables/relative importance
                    calculateAndUpdate();
                }
            } catch (e) { console.error(e); }
        });
    </script>
@endsection