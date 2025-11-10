@extends('cobit2019.cobitTools')
@section('cobit-tools-content')
    @include('cobit2019.cobitPagination')
    
    <div class="container">

    {{-- Admin raw submissions removed per request --}}
        <!-- Card Utama -->
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow border-0 rounded">
                    <!-- Card Header -->
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h4 class="mb-0">Design Factor 5 — Importance of Threat Landscape</h4>
                    </div>
                    <!-- Card Body -->
                    <div class="card-body p-4">
                        <form action="{{ route('df5.store') }}" method="POST" onsubmit="return validateForm()">
                            @csrf
                            <input type="hidden" name="df_id" value="{{ $id }}">

                            <!-- Tabel Input DF5 -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-success">
                                        <tr>
                                            <th scope="col">Value</th>
                                            <th scope="col" class="text-center" style="width: 50%;">Importance (100%)</th>
                                            <th scope="col" class="text-center">Baseline</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>High</td>
                                            <td>
                                                <input type="number" name="input1df5" id="input1df5"
                                                    class="form-control input-percentage" required>
                                                <small class="text-muted">Masukkan nilai dalam persen (contoh: 33 untuk
                                                    33%).</small>
                                            </td>
                                            <td class="text-center fw-bold text-success fs-5">
                                                33%
                                                <input type="hidden" name="baseline_1df5" value="50">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Normal</td>
                                            <td>
                                                <input type="number" name="input2df5" id="input2df5"
                                                    class="form-control input-percentage" required>
                                                <small class="text-muted">Masukkan nilai dalam persen (contoh: 67 untuk
                                                    67%).</small>
                                            </td>
                                            <td class="text-center fw-bold text-success fs-5">
                                                67%
                                                <input type="hidden" name="baseline_2df5" value="50">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                 <!-- Submit Button -->
                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-primary btn-lg px-5">Save</button>
                            </div>
                            </div>

                            <!-- Pie Chart -->
                            <div class="chart-container mt-4" style="height: 300px;">
                                <canvas id="pieChart"></canvas>
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
                                                        <th class="text-center text-primary">DF5 Score</th>
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
                    <!-- end card-body -->
                </div>
            </div>
        </div>
    </div>

    <!-- Sertakan Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {

            // -------------------- PIE CHART --------------------
            const pieCtx = document.getElementById('pieChart').getContext('2d');
            const pieChart = new Chart(pieCtx, {
                type: 'pie',
                data: {
                    labels: ['High', 'Normal'],
                    datasets: [{
                        data: [50, 50], // Data default
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.6)',
                            'rgba(54, 162, 235, 0.6)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } }
                }
            });

            // Fungsi update untuk pie chart dan perhitungan DF5
            let isUpdating = false;
            function updateChartsAndTable() {
                // Ambil nilai input dari form
                const input1 = parseFloat(document.getElementById('input1df5').value) || 0;
                const input2 = parseFloat(document.getElementById('input2df5').value) || 0;

                // Update pie chart
                pieChart.data.datasets[0].data = [input1, input2];
                pieChart.update();

                // Hitung ulang perhitungan DF5
                const result = updateRelativeImportance();
                // Update tabel
                updateRelativeTable(result.score, result.relative);
                // Update Bar Chart dan Radar Chart
                updateRelativeCharts(result.relative);
            }

            // Event listener untuk sinkronisasi input pie chart
            document.getElementById('input1df5').addEventListener('input', function () {
                if (!isUpdating) {
                    isUpdating = true;
                    let val = parseFloat(this.value) || 0;
                    document.getElementById('input2df5').value = 100 - val;
                    updateChartsAndTable();
                    isUpdating = false;
                }
            });

            document.getElementById('input2df5').addEventListener('input', function () {
                if (!isUpdating) {
                    isUpdating = true;
                    let val = parseFloat(this.value) || 0;
                    document.getElementById('input1df5').value = 100 - val;
                    updateChartsAndTable();
                    isUpdating = false;
                }
            });

            // -------------------- KONFIGURASI PERHITUNGAN DF5 --------------------
            // DF5_MAP: Matriks mapping (40x2)
            const DF5_MAP = [
                [3.0, 1.0],
                [1.0, 1.0],
                [4.0, 1.0],
                [1.0, 1.0],
                [2.0, 1.0],
                [3.0, 1.0],
                [1.0, 1.0],
                [3.0, 1.0],
                [1.0, 1.0],
                [1.0, 1.0],
                [1.0, 1.0],
                [2.0, 1.0],
                [1.0, 1.0],
                [2.0, 1.0],
                [3.0, 1.0],
                [2.0, 1.0],
                [4.0, 1.0],
                [4.0, 1.0],
                [3.0, 1.0],
                [1.0, 1.0],
                [1.0, 1.0],
                [1.0, 1.0],
                [2.0, 1.0],
                [1.0, 1.0],
                [3.0, 1.0],
                [1.0, 1.0],
                [1.0, 1.0],
                [1.0, 1.0],
                [3.0, 1.0],
                [1.0, 1.0],
                [1.0, 1.0],
                [3.0, 1.0],
                [2.0, 1.0],
                [4.0, 1.0],
                [3.0, 1.0],
                [3.0, 1.0],
                [3.0, 1.0],
                [2.0, 1.0],
                [3.0, 1.0],
                [3.0, 1.0]
            ];
            // DF5_SC_BASELINE: Baseline score untuk masing-masing baris (40x1)
            const DF5_SC_BASELINE = [
                [1.66],
                [1.00],
                [1.99],
                [1.00],
                [1.33],
                [1.66],
                [1.00],
                [1.66],
                [1.00],
                [1.00],
                [1.00],
                [1.33],
                [1.00],
                [1.33],
                [1.66],
                [1.33],
                [1.99],
                [1.99],
                [1.66],
                [1.00],
                [1.00],
                [1.00],
                [1.33],
                [1.00],
                [1.66],
                [1.00],
                [1.00],
                [1.00],
                [1.66],
                [1.00],
                [1.00],
                [1.66],
                [1.33],
                [1.99],
                [1.66],
                [1.66],
                [1.66],
                [1.33],
                [1.66],
                [1.66]
            ];

            // ===============================================================
            // Fungsi pembulatan ke kelipatan tertentu
            // ===============================================================
            function mround(value, multiple) {
                if (multiple === 0) return 0;
                return Math.round(value / multiple) * multiple;
            }

            // ===============================================================
            // Fungsi untuk menghitung DF5_SCORE dan DF5_RELATIVE_IMP
            // ===============================================================
            function updateRelativeImportance() {
                // ---------------------------------------------------------------
                // 1. Ambil nilai input dari form dan konversi ke desimal
                // Misalnya: jika input adalah 33, maka 33 / 100 = 0.33
                // ---------------------------------------------------------------
                const in1 = parseFloat(document.getElementById('input1df5').value) || 33;
                const in2 = parseFloat(document.getElementById('input2df5').value) || 67;
                const DF5_INPUT = [
                    [in1 / 100],
                    [in2 / 100]
                ];

                // ---------------------------------------------------------------
                // 2. Hitung DF5_SCORE dengan melakukan perkalian matriks
                // DF5_MAP merupakan matriks (array dua dimensi) yang harus sudah didefinisikan secara global
                // Untuk setiap baris pada DF5_MAP, kalikan setiap elemen dengan input yang bersesuaian dan jumlahkan hasilnya
                // ---------------------------------------------------------------
                let DF5_SCORE = [];
                DF5_MAP.forEach((row, i) => {
                    let score = 0;
                    DF5_INPUT.forEach((input, j) => {
                        score += row[j] * input[0];
                    });
                    DF5_SCORE.push(score);
                });

                // ---------------------------------------------------------------
                // 3. Hitung DF5_RELATIVE_IMP
                // Untuk setiap skor pada DF5_SCORE, bandingkan dengan nilai baseline pada DF5_SC_BASELINE
                // Perhitungan: (100 * score / baseline) dibulatkan ke kelipatan 5, lalu dikurangi 100
                // Pastikan untuk menangani kasus pembagian dengan nol (baseline = 0)
                // ---------------------------------------------------------------
                let DF5_RELATIVE_IMP = [];
                DF5_SCORE.forEach((score, i) => {
                    if (DF5_SC_BASELINE[i][0] !== 0) {
                        let relativeValue = (100 * score) / DF5_SC_BASELINE[i][0];
                        DF5_RELATIVE_IMP.push(mround(relativeValue, 5) - 100);
                    } else {
                        DF5_RELATIVE_IMP.push(0);
                    }
                });

                // ---------------------------------------------------------------
                // 4. Kembalikan objek yang berisi DF5_SCORE dan DF5_RELATIVE_IMP
                // ---------------------------------------------------------------
                return { score: DF5_SCORE, relative: DF5_RELATIVE_IMP };
            }

            // Update tabel Relative Importance dengan memasukkan nomor indeks, nilai DF5_SCORE, dan Relative Importance
            function updateRelativeTable(df5Scores, relativeImportanceValues) {
                const tableBody = document.querySelector('#results-table tbody');
                tableBody.innerHTML = ''; // Bersihkan isi tabel sebelumnya

                relativeImportanceValues.forEach((relativeValue, index) => {
                    const row = document.createElement('tr');
                    // Terapkan kelas styling berdasarkan nilai (positif, negatif, atau nol)
                    const scoreClass = relativeValue > 0
                        ? 'bg-primary-subtle text-dark'
                        : relativeValue < 0
                            ? 'bg-danger-subtle text-dark'
                            : '';
                    row.innerHTML = `
          <td class="text-center">${index + 1}</td>
          <td class="text-center">${df5Scores[index].toFixed(2)}</td>
          <td class="text-center ${scoreClass}">${relativeValue}</td>
        `;
                    tableBody.appendChild(row);
                });
            }

            // -------------------- CHART RELATIVE IMPORTANCE --------------------
            // Label untuk relative importance (40 label)
            const relativeImportanceLabels = [
                'EDM01', 'EDM02', 'EDM03', 'EDM04', 'EDM05',
                'APO01', 'APO02', 'APO03', 'APO04', 'APO05',
                'APO06', 'APO07', 'APO08', 'APO09', 'APO10',
                'APO11', 'APO12', 'APO13', 'APO14',
                'BAI01', 'BAI02', 'BAI03', 'BAI04', 'BAI05', 'BAI06', 'BAI07', 'BAI08', 'BAI09', 'BAI10', 'BAI11',
                'DSS01', 'DSS02', 'DSS03', 'DSS04', 'DSS05', 'DSS06',
                'MEA01', 'MEA02', 'MEA03', 'MEA04'
            ];

            // Inisialisasi Bar Chart untuk Relative Importance
            const relBarCtx = document.getElementById('relativeImportanceChart').getContext('2d');
            const relativeBarChart = new Chart(relBarCtx, {
                type: 'bar',
                data: {
                    labels: relativeImportanceLabels,
                    datasets: [{
                        label: 'Relative Importance Score',
                        data: [], // akan diisi nanti
                        backgroundColor: [],
                        borderColor: [],
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            min: -100,
                            max: 100,
                            beginAtZero: true,
                            ticks: { stepSize: 20 },
                            grid: {
                                color: ctx => ctx.tick.value === 0 ? 'rgba(0,0,0,0.3)' : 'rgba(200,200,200,0.3)',
                                lineWidth: ctx => ctx.tick.value === 0 ? 2 : 1
                            }
                        },
                        y: { ticks: { autoSkip: false, maxTicksLimit: 40 } }
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

            // Inisialisasi Radar Chart untuk Relative Importance
            const relRadarCtx = document.getElementById('relativeImportanceRadarChart').getContext('2d');
            const relativeRadarChart = new Chart(relRadarCtx,
                {
                    type: 'radar',
                    data: {
                        labels: [...relativeImportanceLabels].reverse(),
                        datasets: [{
                            label: 'Relative Importance',
                            data: [], // akan diisi nanti
                            backgroundColor: 'rgba(235,54,54,0.2)',
                            borderColor: [],
                            borderWidth: 2,
                            tension: 0.4,
                            pointBackgroundColor: [],
                            pointBorderColor: '#fff',
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: []
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
                                angleLines: { color: 'rgba(200,200,200,0.3)' },
                                grid: { color: 'rgba(200,200,200,0.3)' }
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

            // Fungsi update untuk Bar & Radar Chart berdasarkan data relative importance
            function updateRelativeCharts(relImpData) {
                // Update Bar Chart
                relativeBarChart.data.datasets[0].data = relImpData;
                relativeBarChart.data.datasets[0].backgroundColor = relImpData.map(value =>
                    value > 0 ? 'rgba(54, 162, 235, 0.6)' :
                        value < 0 ? 'rgba(255, 99, 132, 0.6)' :
                            'rgba(201, 201, 201, 0.6)'
                );
                relativeBarChart.data.datasets[0].borderColor = relImpData.map(value =>
                    value > 0 ? 'rgba(54, 162, 235, 1)' :
                        value < 0 ? 'rgba(255, 99, 132, 1)' :
                            'rgba(201, 201, 201, 1)'
                );
                relativeBarChart.update();

                // Update Radar Chart (gunakan data dibalik)
                const reversedData = [...relImpData].reverse();
                relativeRadarChart.data.datasets[0].data = reversedData;
                relativeRadarChart.data.datasets[0].borderColor = reversedData.map(value =>
                    value < 0 ? 'rgba(255, 99, 132, 1)' : 'rgba(54, 162, 235, 1)'
                );
                relativeRadarChart.data.datasets[0].pointBackgroundColor = reversedData.map(value =>
                    value < 0 ? 'rgba(255, 99, 132, 1)' : 'rgba(54, 162, 235, 1)'
                );
                relativeRadarChart.data.datasets[0].pointHoverBorderColor = reversedData.map(value =>
                    value < 0 ? 'rgba(255, 99, 132, 1)' : 'rgba(54, 162, 235, 1)'
                );
                relativeRadarChart.update();
            }

            // Inisialisasi awal: hitung dan tampilkan nilai DF5 serta update semua chart dan tabel
            const initResult = updateRelativeImportance();
            updateRelativeTable(initResult.score, initResult.relative);
            updateRelativeCharts(initResult.relative);

            // --- Restore server-provided latest inputs (if any) ---
            const serverHistoryInputs = @json($historyInputs ?? null);
            const serverHistoryScoreArray = @json($historyScoreArray ?? null);
            const serverHistoryRIArray = @json($historyRIArray ?? null);
            if (serverHistoryInputs) {
                // apply values and trigger update
                document.getElementById('input1df5').value = serverHistoryInputs[0] ?? '';
                document.getElementById('input2df5').value = serverHistoryInputs[1] ?? '';
                updateChartsAndTable();
            }

            // Jika ada input lain yang berubah (misalnya kelas .input-percentage)
            document.querySelectorAll('.input-percentage').forEach(input => {
                input.addEventListener('change', updateChartsAndTable);
            });

        });
    </script>
@endsection