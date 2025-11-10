@extends('cobit2019.cobitTools')
@section('cobit-tools-content')
  @include('cobit2019.cobitPagination')
  <div class="container">

  {{-- Admin raw submissions (generic) --}}
  {{-- Admin raw submissions removed per request --}}
    <div class="row justify-content-center">
      <div class="col-md-10">
        <div class="card shadow border-0 rounded">
          <!-- Card Header -->
          <div class="card-header bg-primary text-white text-center py-3">
            <h4 class="mb-0">Design Factor 10 - Technology Adoption Strategy</h4>
          </div>
          <!-- Card Body -->
          <div class="card-body p-4">
            <form action="{{ route('df10.store') }}" method="POST" onsubmit="return validateForm()">
              @csrf
              <input type="hidden" name="df_id" value="{{ $id }}">

              <!-- Tabel Input DF10 -->
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
                      <td>First Mover</td>
                      <td>
                        <input type="number" name="input1df10" id="input1df10" class="form-control input-percentage"
                          required>
                        <small class="text-muted">Masukkan nilai dalam persen (contoh: 33 untuk 33%).</small>
                      </td>
                      <td class="text-center fw-bold text-success fs-5">
                        15%
                        <input type="hidden" name="baseline_1df10" value="33">
                      </td>
                    </tr>
                    <tr>
                      <td>Follower</td>
                      <td>
                        <input type="number" name="input2df10" id="input2df10" class="form-control input-percentage"
                          required>
                        <small class="text-muted">Masukkan nilai dalam persen (contoh: 33 untuk 33%).</small>
                      </td>
                      <td class="text-center fw-bold text-success fs-5">
                        70%
                        <input type="hidden" name="baseline_2df10" value="33">
                      </td>
                    </tr>
                    <tr>
                      <td>Slow Adopter</td>
                      <td>
                        <input type="number" name="input3df10" id="input3df10" class="form-control input-percentage"
                          required>
                        <small class="text-muted">Masukkan nilai dalam persen (contoh: 34 untuk 34%).</small>
                      </td>
                      <td class="text-center fw-bold text-success fs-5">
                        15%
                        <input type="hidden" name="baseline_3df10" value="34">
                      </td>
                    </tr>
                  </tbody>
                </table>
                      <!-- Submit Button -->
                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-primary btn-lg px-5">
                                    Save
                                </button>
                            </div>
              </div>

              <!-- Error Message -->
              <div id="error-message" class="alert alert-danger mt-3" role="alert" style="display: none;">
                The sum of all fields must not exceed 100%.
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
                            <th class="text-center text-primary">DF10 Score</th>
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
    // Inisialisasi Pie Chart untuk DF10
    const pieCtx = document.getElementById('pieChart').getContext('2d');
    const pieChart = new Chart(pieCtx, {
      type: 'pie',
      data: {
        labels: ['First Mover', 'Follower', 'Slow Adopter'],
        datasets: [{
          data: [33, 33, 34],
          backgroundColor: [
            'rgba(255, 99, 132, 0.6)',  // Red untuk First Mover
            'rgba(54, 162, 235, 0.6)',   // Blue untuk Follower
            'rgba(255, 206, 86, 0.6)'    // Yellow untuk Slow Adopter
          ],
          borderColor: [
            'rgba(255, 99, 132, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)'
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

    // Fungsi update Pie Chart saat input berubah
    function updatePieChart() {
      const in1 = parseFloat(document.getElementById('input1df10').value) || 33;
      const in2 = parseFloat(document.getElementById('input2df10').value) || 33;
      const in3 = parseFloat(document.getElementById('input3df10').value) || 34;
      pieChart.data.datasets[0].data = [in1, in2, in3];
      pieChart.data.datasets[0].backgroundColor = [
        in1 > 0 ? 'rgba(255, 99, 132, 0.6)' : 'rgba(169, 169, 169, 0.6)',
        in2 > 0 ? 'rgba(54, 162, 235, 0.6)' : 'rgba(169, 169, 169, 0.6)',
        in3 > 0 ? 'rgba(255, 206, 86, 0.6)' : 'rgba(169, 169, 169, 0.6)'
      ];
      pieChart.update();
    }

    // Validasi form: pastikan total nilai tidak melebihi 100%
    function validateForm() {
      const in1 = parseFloat(document.getElementById('input1df10').value) || 0;
      const in2 = parseFloat(document.getElementById('input2df10').value) || 0;
      const in3 = parseFloat(document.getElementById('input3df10').value) || 0;
      const total = in1 + in2 + in3;
      if (total > 100) {
        document.getElementById('error-message').style.display = 'block';
        return false;
      }
      document.getElementById('error-message').style.display = 'none';
      return true;
    }

    // -------------------- KONFIGURASI PERHITUNGAN DF10 --------------------
    // DF10_MAP: Matriks mapping (40x3)
    const DF10_MAP = [
      [3.5, 2.5, 1.5],
      [4.0, 2.5, 1.5],
      [1.5, 1.0, 1.0],
      [2.5, 2.0, 1.5],
      [1.5, 1.0, 1.0],
      [2.5, 1.5, 1.0],
      [4.0, 3.0, 1.5],
      [2.0, 1.0, 1.0],
      [4.0, 3.0, 1.0],
      [4.0, 2.5, 1.0],
      [1.0, 1.5, 1.0],
      [2.5, 1.0, 1.0],
      [3.0, 1.5, 1.0],
      [1.5, 1.5, 1.0],
      [2.5, 1.5, 1.0],
      [1.5, 1.5, 1.0],
      [2.0, 1.5, 1.0],
      [1.0, 1.0, 1.0],
      [2.5, 2.0, 1.0],
      [4.0, 3.0, 1.5],
      [3.5, 2.5, 1.0],
      [4.0, 2.5, 1.0],
      [1.5, 1.5, 1.0],
      [3.0, 2.0, 1.0],
      [2.5, 2.0, 1.0],
      [3.5, 2.5, 1.0],
      [1.5, 1.0, 1.0],
      [1.0, 1.0, 1.0],
      [1.5, 1.0, 1.0],
      [3.5, 2.5, 1.0],
      [1.0, 1.0, 1.0],
      [1.0, 1.0, 1.0],
      [1.5, 1.0, 1.0],
      [1.5, 1.0, 1.0],
      [1.5, 1.0, 1.0],
      [1.0, 1.0, 1.0],
      [3.0, 2.0, 1.0],
      [1.0, 1.0, 1.0],
      [1.0, 1.0, 1.0],
      [1.0, 1.0, 1.0]
    ];

    // DF10_SC_BASELINE: Baseline score untuk masing-masing baris (40x1)
    const DF10_SC_BASELINE = [
      [2.50],
      [2.58],
      [1.08],
      [2.00],
      [1.08],
      [1.58],
      [2.93],
      [1.15],
      [2.85],
      [2.50],
      [1.35],
      [1.23],
      [1.65],
      [1.43],
      [1.58],
      [1.43],
      [1.50],
      [1.00],
      [1.93],
      [2.93],
      [2.43],
      [2.50],
      [1.43],
      [2.00],
      [1.93],
      [2.43],
      [1.08],
      [1.00],
      [1.08],
      [2.43],
      [1.00],
      [1.00],
      [1.08],
      [1.08],
      [1.08],
      [1.00],
      [2.00],
      [1.00],
      [1.00],
      [1.00]
    ];

    // Fungsi pembulatan ke kelipatan tertentu
    function mround(value, multiple) {
      if (multiple === 0) return 0;
      return Math.round(value / multiple) * multiple;
    }

    // Fungsi untuk menghitung DF10_SCORE dan DF10_RELATIVE_IMP
    function updateRelativeImportance() {
      const in1 = parseFloat(document.getElementById('input1df10').value) || 15;
      const in2 = parseFloat(document.getElementById('input2df10').value) || 70;
      const in3 = parseFloat(document.getElementById('input3df10').value) || 15;
      const DF10_INPUT = [
        [in1 / 100],
        [in2 / 100],
        [in3 / 100]
      ];
      let DF10_SCORE = [];
      DF10_MAP.forEach((row, i) => {
        let score = 0;
        DF10_INPUT.forEach((input, j) => {
          score += row[j] * input[0];
        });
        DF10_SCORE.push(score);
      });
      let DF10_RELATIVE_IMP = [];
      DF10_SCORE.forEach((score, i) => {
        if (DF10_SC_BASELINE[i][0] !== 0) {
          let relativeValue = (100 * score) / DF10_SC_BASELINE[i][0];
          DF10_RELATIVE_IMP.push(mround(relativeValue, 5) - 100);
        } else {
          DF10_RELATIVE_IMP.push(0);
        }
      });
      return { score: DF10_SCORE, relative: DF10_RELATIVE_IMP };
    }

    // Fungsi untuk memperbarui tabel Relative Importance
    function updateRelativeTable(df10Scores, relativeImportanceValues) {
      const tableBody = document.querySelector('#results-table tbody');
      tableBody.innerHTML = '';
      relativeImportanceValues.forEach((relativeValue, index) => {
        const row = document.createElement('tr');
        const scoreClass = relativeValue > 0
          ? 'bg-primary-subtle text-dark'
          : relativeValue < 0
            ? 'bg-danger-subtle text-dark'
            : '';
        row.innerHTML = `
          <td class="text-center">${relativeImportanceLabels[index]}</td>
          <td class="text-center">${df10Scores[index].toFixed(2)}</td>
          <td class="text-center ${scoreClass}">${relativeValue}</td>
        `;
        tableBody.appendChild(row);
      });
    }

    // Label untuk Relative Importance (40 label)
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
          data: [],
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
    const relativeRadarChart = new Chart(relRadarCtx, {
      type: 'radar',
      data: {
        labels: [...relativeImportanceLabels].reverse(),
        datasets: [{
          label: 'Relative Importance',
          data: [],
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

    // Fungsi update untuk semua chart dan tabel
    function updateRelativeChartsAndTable() {
      updatePieChart();
      const result = updateRelativeImportance();
      updateRelativeTable(result.score, result.relative);

      // Update Bar Chart
      relativeBarChart.data.datasets[0].data = result.relative;
      relativeBarChart.data.datasets[0].backgroundColor = result.relative.map(value =>
        value > 0 ? 'rgba(54, 162, 235, 0.6)' :
          value < 0 ? 'rgba(255, 99, 132, 0.6)' :
            'rgba(201, 201, 201, 0.6)'
      );
      relativeBarChart.data.datasets[0].borderColor = result.relative.map(value =>
        value > 0 ? 'rgba(54, 162, 235, 1)' :
          value < 0 ? 'rgba(255, 99, 132, 1)' :
            'rgba(201, 201, 201, 1)'
      );
      relativeBarChart.update();

      // Update Radar Chart (data dibalik)
      const reversedData = [...result.relative].reverse();
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

    // Fungsi update untuk sinkronisasi semua komponen
    function updateAll() {
      updateRelativeChartsAndTable();
    }

    // Inisialisasi awal ketika DOM telah termuat
    document.addEventListener('DOMContentLoaded', function () {
      updateAll();
      // --- Restore server-provided latest inputs (if any) ---
      const serverHistoryInputs = @json($historyInputs ?? null);
      const serverHistoryScoreArray = @json($historyScoreArray ?? null);
      const serverHistoryRIArray = @json($historyRIArray ?? null);
      if (serverHistoryInputs) {
        document.getElementById('input1df10').value = serverHistoryInputs[0] ?? '';
        document.getElementById('input2df10').value = serverHistoryInputs[1] ?? '';
        document.getElementById('input3df10').value = serverHistoryInputs[2] ?? '';
        // trigger update to recompute charts/tables
        updateAll();
      }
      document.querySelectorAll('.input-percentage').forEach(input => {
        input.addEventListener('change', updateAll);
      });
    });

    // Tambahkan event listener untuk input DF10
    document.getElementById('input1df10').addEventListener('input', updateAll);
    document.getElementById('input2df10').addEventListener('input', updateAll);
    document.getElementById('input3df10').addEventListener('input', updateAll);
  </script>



@endsection