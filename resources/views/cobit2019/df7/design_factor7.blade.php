@extends('cobit2019.cobitTools')
@section('cobit-tools-content')
  @include('cobit2019.cobitPagination')
  <div class="container">

  {{-- Admin raw submissions (generic) --}}
  {{-- Admin raw submissions removed per request --}}
    <!-- Card Utama -->
    <div class="row justify-content-center">
      <div class="col-md-10">
        <div class="card shadow border-0 rounded">
          <!-- Card Header -->
          <div class="card-header bg-primary text-white text-center py-3">
            <h4 class="mb-0">Design Factor 7 - Importance of Role of IT</h4>
          </div>
          <!-- Card Body -->
          <div class="card-body p-4">
            <form id="df7Form" action="{{ route('df7.store') }}" method="POST">
              @csrf
              <input type="hidden" name="df_id" value="{{ $id }}">
              <!-- ====================================================
                       Tabel Input DF7
                  ==================================================== -->
              <div class="table-responsive">
                <table class="table table-bordered table-hover">
                  <thead class="table-success">
                    <tr>
                      <th scope="col">Value</th>
                      <th scope="col" class="text-center" style="width: 30%;">Importance (1–5)</th>
                      <th scope="col" class="text-center">Baseline</th>
                    </tr>
                  </thead>
                  <tbody>
                    @php
                      $df7Items = ['Support', 'Factory', 'Turnaround', 'Strategic'];
                    @endphp
                    @foreach($df7Items as $index => $item)
                      <tr>
                        <!-- Kolom Value -->
                        <td class="text-primary fw-bold">{{ $item }}</td>
                        <!-- Kolom Input Score -->
                        <td class="text-center">
                          <div class="d-flex justify-content-center">
                            @for ($i = 1; $i <= 5; $i++)
                              <div class="form-check form-check-inline">
                                <input class="form-check-input input-score" type="radio" name="input{{ $index + 1 }}df7"
                                  id="input{{ $index + 1 }}df7_{{ $i }}" value="{{ $i }}" required>
                                <label class="form-check-label small" for="input{{ $index + 1 }}df7_{{ $i }}">
                                  {{ $i }}
                                </label>
                              </div>
                            @endfor
                          </div>
                        </td>
                        <!-- Kolom Baseline -->
                        <td class="text-center fw-bold text-success fs-5">
                          3
                          <input type="hidden" name="baseline_{{ $index + 1 }}df7" value="3">
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

              <!-- ====================================================
                       Grafik Input Score (Bar Chart)
                  ==================================================== -->
              <div class="row mt-4">
                <div class="col-12 mb-3">
                  <div class="card h-100">
                    <div class="card-header text-center">Bar Chart Input Score</div>
                    <div class="card-body" style="height: 200px;">
                      <canvas id="barChart"></canvas>
                    </div>
                  </div>
                </div>
              </div>

              <!-- ====================================================
                       Layout Relative Importance:
                       - Radar Chart, Table, & Bar Chart
                  ==================================================== -->
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
                            <th class="text-center text-primary">GMO</th>
                            <th class="text-center text-primary">DF7 Score</th>
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

  <!-- ====================================================
           SCRIPT: CHARTS & CALCULATIONS FOR DF7
      ==================================================== -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {

      /* -----------------------------------------------
         Bar Chart for Input Score (DF7)
      ----------------------------------------------- */
      const barCtx = document.getElementById('barChart').getContext('2d');
      const initialData = {
        labels: ['Support', 'Factory', 'Turnaround', 'Strategic'],
        datasets: [{
          label: 'Scores',
          data: [0, 0, 0, 0],
          backgroundColor: 'rgba(54, 162, 235, 0.5)',
          borderColor: 'rgba(54, 162, 235, 1)',
          borderWidth: 1
        }]
      };
      const barConfig = {
        type: 'bar',
        data: initialData,
        options: {
          responsive: true,
          maintainAspectRatio: false,
          indexAxis: 'y',
          scales: {
            x: { max: 5, display: false },
            y: { ticks: { font: { size: 12 } } }
          },
          plugins: { legend: { display: false }, tooltip: { enabled: false } }
        }
      };
      const barChart = new Chart(barCtx, barConfig);

      // Update Bar Chart on input change
      function updateCharts() {
        const scores = [
          parseFloat(document.querySelector('input[name="input1df7"]:checked')?.value || 0),
          parseFloat(document.querySelector('input[name="input2df7"]:checked')?.value || 0),
          parseFloat(document.querySelector('input[name="input3df7"]:checked')?.value || 0),
          parseFloat(document.querySelector('input[name="input4df7"]:checked')?.value || 0)
        ];
        barChart.data.datasets[0].data = scores;
        barChart.update();
      }
      document.querySelectorAll('.input-score').forEach(input => {
        input.addEventListener('change', updateCharts);
      });

      /* -----------------------------------------------
         DF7 Relative Importance Calculations
         (Converted from PHP to JavaScript)
      ----------------------------------------------- */
      function mround(value, multiple) {
        if (multiple === 0) return 0;
        return Math.round(value / multiple) * multiple;
      }

      function updateDF7RelativeImportance() {
        // Ambil nilai input (default 3 jika belum dipilih)
        const df7Input = [
          parseFloat(document.querySelector('input[name="input1df7"]:checked')?.value || 3),
          parseFloat(document.querySelector('input[name="input2df7"]:checked')?.value || 3),
          parseFloat(document.querySelector('input[name="input3df7"]:checked')?.value || 3),
          parseFloat(document.querySelector('input[name="input4df7"]:checked')?.value || 3)
        ];
        // Hitung rata-rata input
        const df7InpAvg = df7Input.reduce((sum, val) => sum + val, 0) / df7Input.length;
        // Baseline untuk setiap input adalah 3
        const df7Baseline = [3, 3, 3, 3];
        const df7BaselineAvg = df7Baseline.reduce((sum, val) => sum + val, 0) / df7Baseline.length;
        // Rasio baseline terhadap rata-rata input
        const df7InBsAvg = df7BaselineAvg / df7InpAvg;

        // DF7_MAP: 40x4 matrix (diambil dari PHP)
        const DF7_MAP = [
          [1.0, 2.0, 1.5, 4.0],
          [1.0, 1.0, 2.5, 3.0],
          [1.0, 3.0, 1.0, 3.0],
          [1.0, 1.0, 1.0, 2.0],
          [1.0, 1.0, 1.0, 2.0],
          [1.0, 1.5, 1.5, 2.5],
          [1.0, 1.0, 3.0, 3.0],
          [1.0, 1.0, 2.0, 2.0],
          [0.5, 1.0, 3.5, 4.0],
          [1.0, 1.0, 2.5, 3.0],
          [1.0, 1.0, 1.0, 2.0],
          [1.0, 1.0, 1.0, 1.5],
          [1.0, 1.0, 2.0, 2.5],
          [1.0, 2.0, 1.5, 2.0],
          [1.0, 2.5, 1.5, 2.0],
          [1.0, 1.5, 1.5, 2.0],
          [1.0, 2.5, 1.0, 3.0],
          [1.0, 2.0, 1.5, 3.0],
          [1.0, 1.5, 1.5, 2.5],
          [1.0, 1.0, 2.0, 2.5],
          [1.0, 1.0, 3.0, 3.0],
          [1.0, 1.0, 3.0, 3.0],
          [1.0, 2.5, 1.5, 2.0],
          [1.0, 1.0, 1.0, 2.0],
          [1.0, 2.5, 1.0, 2.0],
          [1.0, 1.0, 2.0, 2.0],
          [1.0, 1.0, 1.0, 2.0],
          [1.0, 1.0, 1.0, 2.0],
          [1.0, 1.5, 1.0, 2.0],
          [1.0, 1.0, 2.0, 2.0],
          [1.0, 3.5, 1.0, 3.0],
          [1.0, 3.0, 1.5, 3.0],
          [1.0, 3.0, 1.5, 3.5],
          [1.0, 3.0, 1.5, 3.5],
          [1.5, 2.5, 1.5, 3.5],
          [1.0, 1.0, 1.0, 2.5],
          [1.0, 1.0, 1.0, 2.0],
          [1.0, 1.0, 1.0, 2.0],
          [1.0, 1.0, 1.0, 1.5],
          [1.0, 1.0, 1.0, 2.0]
        ];

        // DF7_SC_BASELINE: Array of 40 baseline scores
        const DF7_SC_BASELINE = [
          25.5, 22.5, 24.0, 15.0, 15.0, 19.5, 24.0, 18.0, 27.0, 22.5,
          15.0, 13.5, 19.5, 19.5, 21.0, 18.0, 22.5, 22.5, 19.5, 19.5,
          24.0, 24.0, 21.0, 15.0, 19.5, 18.0, 15.0, 15.0, 16.5, 18.0,
          25.5, 25.5, 27.0, 27.0, 27.0, 16.5, 15.0, 15.0, 13.5, 15.0
        ];

        // Compute DF7_SCORE for each row in DF7_MAP
        const DF7_SCORE = DF7_MAP.map(row => {
          return row.reduce((sum, coeff, j) => sum + coeff * df7Input[j], 0);
        });

        // Compute DF7_RELATIVE_IMP for each row using the ratio and baseline score
        const DF7_RELATIVE_IMP = DF7_SCORE.map((score, i) => {
          if (DF7_SC_BASELINE[i] !== 0) {
            const calc = (df7InBsAvg * 100 * score) / DF7_SC_BASELINE[i];
            return mround(calc, 5) - 100;
          } else {
            return 0;
          }
        });

        return { score: DF7_SCORE, relative: DF7_RELATIVE_IMP };
      }

      /* -----------------------------------------------
         Update DF7 Relative Table
      ----------------------------------------------- */
      function updateDF7RelativeTable(df7Scores, relativeImportanceValues) {
        const tableBody = document.querySelector('#results-table tbody');
        tableBody.innerHTML = '';
        relativeImportanceValues.forEach((relativeValue, index) => {
          const row = document.createElement('tr');
          const scoreClass = relativeValue > 0 ? 'bg-primary-subtle text-dark'
            : relativeValue < 0 ? 'bg-danger-subtle text-dark' : '';
          row.innerHTML = `
              <td class="text-center">${relativeImportanceLabels[index]}</td>
              <td class="text-center">${df7Scores[index].toFixed(2)}</td>
              <td class="text-center ${scoreClass}">${relativeValue}</td>
            `;
          tableBody.appendChild(row);
        });
      }

      /* -----------------------------------------------
         Update Relative Charts (Bar & Radar) for DF7
      ----------------------------------------------- */
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

        // Update Radar Chart (data reversed)
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

      /* -----------------------------------------------
         Update All Charts and Table (DF7)
      ----------------------------------------------- */
      function updateAllChartsAndTable() {
        updateCharts();
        const result = updateDF7RelativeImportance();
        updateDF7RelativeTable(result.score, result.relative);
        updateRelativeCharts(result.relative);
      }

      /* -----------------------------------------------
         Create Relative Importance Bar Chart (DF7)
      ----------------------------------------------- */
      const relativeImportanceLabels = [
        'EDM01', 'EDM02', 'EDM03', 'EDM04', 'EDM05',
        'APO01', 'APO02', 'APO03', 'APO04', 'APO05',
        'APO06', 'APO07', 'APO08', 'APO09', 'APO10',
        'APO11', 'APO12', 'APO13', 'APO14',
        'BAI01', 'BAI02', 'BAI03', 'BAI04', 'BAI05', 'BAI06', 'BAI07', 'BAI08', 'BAI09', 'BAI10', 'BAI11',
        'DSS01', 'DSS02', 'DSS03', 'DSS04', 'DSS05', 'DSS06',
        'MEA01', 'MEA02', 'MEA03', 'MEA04'
      ];
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

      /* -----------------------------------------------
         Create Relative Importance Radar Chart (DF7)
      ----------------------------------------------- */
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

      // -----------------------------------------------
      // Initialize DF7 Relative Importance Section
      // -----------------------------------------------
      const initResult = updateDF7RelativeImportance();
      updateDF7RelativeTable(initResult.score, initResult.relative);
      updateRelativeCharts(initResult.relative);

      document.querySelectorAll('.input-score').forEach(input => {
        input.addEventListener('change', updateAllChartsAndTable);
      });

      // --- Restore server-provided latest radio selections (if any) ---
      const serverHistoryInputs = @json($historyInputs ?? null);
      const serverHistoryScoreArray = @json($historyScoreArray ?? null);
      const serverHistoryRIArray = @json($historyRIArray ?? null);
      if (serverHistoryInputs) {
        // serverHistoryInputs expected to be an array of 4 values (1..5)
        serverHistoryInputs.forEach((val, idx) => {
          if (val !== null && val !== undefined) {
            const selector = `input[name="input${idx + 1}df7"][value="${val}"]`;
            const el = document.querySelector(selector);
            if (el) el.checked = true;
          }
        });
        // trigger charts + table recompute
        updateAllChartsAndTable();
      }
    });
  </script>
@endsection