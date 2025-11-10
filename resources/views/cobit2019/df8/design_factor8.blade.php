@extends('cobit2019.cobitTools')
@section('cobit-tools-content')
@include('cobit2019.cobitPagination')
<div class="container">

  {{-- Admin raw submissions (generic) --}}
  {{-- Admin raw submissions removed per request --}}
  <div class="row justify-content-center">
    <div class="col-md-10">
      <div class="card shadow border-0 rounded">
        <!-- Header Card -->
        <div class="card-header bg-primary text-white text-center py-3">
          <h4 class="mb-0">Design Factor 8 - Sourcing Model for IT</h4>
        </div>
        <div class="card-body p-4">
          <form action="{{ route('df8.store') }}" method="POST" onsubmit="return validateForm()">
            @csrf
            <input type="hidden" name="df_id" value="{{ $id }}">

            <!-- Tabel Input DF8 -->
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
                    <td>Outsourcing</td>
                    <td>
                      <input type="number" name="input1df8" id="input1df8" class="form-control input-percentage" required>
                      <small class="text-muted">Masukkan nilai dalam persen (contoh: 33 untuk 33%).</small>
                    </td>
                    <td class="text-center fw-bold text-success fs-5">
                      33%
                      <input type="hidden" name="baseline_1df8" value="33">
                    </td>
                  </tr>
                  <tr>
                    <td>Cloud</td>
                    <td>
                      <input type="number" name="input2df8" id="input2df8" class="form-control input-percentage" required>
                      <small class="text-muted">Masukkan nilai dalam persen (contoh: 33 untuk 33%).</small>
                    </td>
                    <td class="text-center fw-bold text-success fs-5">
                      33%
                      <input type="hidden" name="baseline_2df8" value="33">
                    </td>
                  </tr>
                  <tr>
                    <td>Insourced</td>
                    <td>
                      <input type="number" name="input3df8" id="input3df8" class="form-control input-percentage" required>
                      <small class="text-muted">Masukkan nilai dalam persen (contoh: 34 untuk 34%).</small>
                    </td>
                    <td class="text-center fw-bold text-success fs-5">
                      34%
                      <input type="hidden" name="baseline_3df8" value="34">
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

            <!-- Relative Importance Section -->
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
                          <th class="text-center text-primary">DF8 Score</th>
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

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  // --------------------------- PIE CHART ---------------------------
  // Inisialisasi Pie Chart untuk DF8 dengan default data [33, 33, 34]
  const ctx = document.getElementById('pieChart').getContext('2d');
  const pieChart = new Chart(ctx, {
    type: 'pie',
    data: {
      labels: ['Outsourcing', 'Cloud', 'Insourced'],
      datasets: [{
        data: [33, 33, 34],
        backgroundColor: [
          'rgba(255, 99, 132, 0.6)', // Warna untuk Outsourcing
          'rgba(54, 162, 235, 0.6)',  // Warna untuk Cloud
          'rgba(255, 206, 86, 0.6)'   // Warna untuk Insourced
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
      plugins: {
        legend: { display: false }
      }
    }
  });

  // Fungsi untuk memperbarui data Pie Chart saat input berubah
  function updatePieChart() {
    const input1 = parseFloat(document.getElementById('input1df8').value) || 0;
    const input2 = parseFloat(document.getElementById('input2df8').value) || 0;
    const input3 = parseFloat(document.getElementById('input3df8').value) || 0;
    pieChart.data.datasets[0].data = [input1, input2, input3];
    // Update warna background secara dinamis
    pieChart.data.datasets[0].backgroundColor = [
      input1 > 0 ? 'rgba(255, 99, 132, 0.6)' : 'rgba(169, 169, 169, 0.6)',
      input2 > 0 ? 'rgba(54, 162, 235, 0.6)' : 'rgba(169, 169, 169, 0.6)',
      input3 > 0 ? 'rgba(255, 206, 86, 0.6)' : 'rgba(169, 169, 169, 0.6)'
    ];
    pieChart.update();
  }

  // Tambahkan event listener pada setiap input untuk update chart secara real-time
  document.getElementById('input1df8').addEventListener('input', updatePieChart);
  document.getElementById('input2df8').addEventListener('input', updatePieChart);
  document.getElementById('input3df8').addEventListener('input', updatePieChart);

  // --------------------------- VALIDASI FORM ---------------------------
  // Fungsi validasi untuk memastikan total input tidak melebihi 100%
  function validateForm() {
    const input1 = parseFloat(document.getElementById('input1df8').value) || 0;
    const input2 = parseFloat(document.getElementById('input2df8').value) || 0;
    const input3 = parseFloat(document.getElementById('input3df8').value) || 0;
    const total = input1 + input2 + input3;
    if(total > 100) {
      document.getElementById('error-message').style.display = 'block';
      return false;
    }
    document.getElementById('error-message').style.display = 'none';
    return true;
  }

  // --------------------------- PERHITUNGAN DF8 SCORE & RELATIVE IMPORTANCE ---------------------------
  // Fungsi pembulatan ke kelipatan tertentu (mirip dengan fungsi mround di PHP)
  function mround(value, multiple) {
    if (multiple === 0) return 0;
    return Math.round(value / multiple) * multiple;
  }

  // Fungsi utama untuk menghitung DF8_SCORE dan DF8_RELATIVE_IMP
  function updateDF8RelativeImportance() {
    // Ambil nilai input dari form dan konversi ke desimal (misal: 33 menjadi 0.33)
    const in1 = parseFloat(document.getElementById('input1df8').value) || 33;
    const in2 = parseFloat(document.getElementById('input2df8').value) || 33;
    const in3 = parseFloat(document.getElementById('input3df8').value) || 34;
    // DF8_INPUT berupa array 1 dimensi (dalam bentuk desimal)
    const DF8_INPUT = [
      in1 / 100,
      in2 / 100,
      in3 / 100
    ];

    // ===================================================================
    // DF8_MAP: Array 2D dengan 40 baris dan 3 kolom untuk perhitungan DF8
    // ===================================================================
    const DF8_MAP = [
      [1.0, 1.0, 1.0],
      [1.0, 1.0, 1.0],
      [1.0, 2.0, 1.0],
      [1.0, 1.0, 1.0],
      [1.0, 1.0, 1.0],
      [1.0, 1.0, 1.0],
      [1.0, 1.0, 1.0],
      [1.0, 1.0, 1.0],
      [1.0, 1.0, 1.0],
      [1.0, 1.0, 1.0],
      [1.0, 1.0, 1.0],
      [1.0, 1.0, 1.0],
      [1.0, 1.0, 1.0],
      [4.0, 4.0, 1.0],
      [4.0, 4.0, 1.0],
      [1.0, 1.0, 1.0],
      [2.0, 2.0, 1.0],
      [1.0, 1.0, 1.0],
      [1.0, 1.0, 1.0],
      [1.0, 1.0, 1.0],
      [1.0, 1.0, 1.0],
      [1.0, 1.0, 1.0],
      [1.0, 1.0, 1.0],
      [1.0, 1.0, 1.0],
      [1.0, 1.0, 1.0],
      [1.0, 1.0, 1.0],
      [1.0, 1.0, 1.0],
      [1.0, 1.0, 1.0],
      [1.0, 1.0, 1.0],
      [1.0, 1.0, 1.0],
      [1.0, 1.0, 1.0],
      [1.0, 1.0, 1.0],
      [1.0, 1.0, 1.0],
      [1.0, 1.0, 1.0],
      [1.0, 1.0, 1.0],
      [1.0, 1.0, 1.0],
      [3.0, 3.0, 1.0],
      [1.0, 1.0, 1.0],
      [1.0, 1.0, 1.0],
      [1.0, 1.0, 1.0]
    ];

    // ===================================================================
    // DF8_BASELINE: Baseline input (tidak langsung digunakan di perhitungan skor)
    // ===================================================================
    const DF8_BASELINE = [
      [33], // 33%
      [33], // 33%
      [34]  // 34%
    ];

    // ===================================================================
    // DF8_SC_BASELINE: Array 2D yang berisi nilai baseline untuk skor (40 baris)
    // ===================================================================
    const DF8_SC_BASELINE = [
      [1.00],
      [1.00],
      [1.33],
      [1.00],
      [1.00],
      [1.00],
      [1.00],
      [1.00],
      [1.00],
      [1.00],
      [1.00],
      [1.00],
      [1.00],
      [2.98],
      [2.98],
      [1.00],
      [1.66],
      [1.00],
      [1.00],
      [1.00],
      [1.00],
      [1.00],
      [1.00],
      [1.00],
      [1.00],
      [1.00],
      [1.00],
      [1.00],
      [1.00],
      [1.00],
      [1.00],
      [1.00],
      [1.00],
      [1.00],
      [1.00],
      [1.00],
      [2.32],
      [1.00],
      [1.00],
      [1.00]
    ];

    // ===================================================================
    // DF8: Menghitung DF8_SCORE dengan mengalikan tiap elemen pada DF8_MAP dengan DF8_INPUT
    // ===================================================================
    const DF8_SCORE = [];
    for (let i = 0; i < DF8_MAP.length; i++) {
      let score = 0;
      for (let j = 0; j < DF8_INPUT.length; j++) {
        score += DF8_MAP[i][j] * DF8_INPUT[j];
      }
      DF8_SCORE.push(score);
    }

    // ===================================================================
    // Menghitung DF8_RELATIVE_IMP berdasarkan DF8_SCORE dan DF8_SC_BASELINE
    // Rumus: (100 * score / baseline), dibulatkan ke kelipatan 5, lalu dikurangi 100
    // ===================================================================
    const DF8_RELATIVE_IMP = [];
    for (let i = 0; i < DF8_SCORE.length; i++) {
      if (DF8_SC_BASELINE[i][0] !== 0) {
        let relativeValue = (100 * DF8_SCORE[i]) / DF8_SC_BASELINE[i][0];
        DF8_RELATIVE_IMP.push(mround(relativeValue, 5) - 100);
      } else {
        DF8_RELATIVE_IMP.push(0);
      }
    }

    // -------------------------------------------------------------------
    // Tampilkan hasil perhitungan ke konsol (atau update ke elemen HTML jika diperlukan)
    // -------------------------------------------------------------------
    return { score: DF8_SCORE, relative: DF8_RELATIVE_IMP };
  }

  // --------------------------- UPDATE TABEL RELATIVE IMPORTANCE ---------------------------
  // Memperbarui tabel dengan hasil perhitungan DF8_SCORE dan DF8_RELATIVE_IMP
  function updateDF8RelativeTable(scores, relativeValues) {
    const tableBody = document.querySelector('#results-table tbody');
    tableBody.innerHTML = '';
    relativeValues.forEach((value, index) => {
      const scoreClass = value > 0 ? 'bg-primary-subtle text-dark'
                        : value < 0 ? 'bg-danger-subtle text-dark' : '';
      const row = document.createElement('tr');
      row.innerHTML = `
        <td class="text-center">${relativeImportanceLabels[index]}</td>
        <td class="text-center">${scores[index].toFixed(2)}</td>
        <td class="text-center ${scoreClass}">${value}</td>
      `;
      tableBody.appendChild(row);
    });
  }

  // --------------------------- INISIALISASI CHARTS UNTUK RELATIVE IMPORTANCE ---------------------------
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
  const relativeRadarChart = new Chart(relRadarCtx, {
    type: 'radar',
    data: {
      labels: [...relativeImportanceLabels].reverse(),
      datasets: [{
        label: 'Relative Importance',
        data: [], // akan diisi nanti
        backgroundColor: 'rgba(235,54,54,0.2)',
        borderColor: [],
        tension: 0.4,
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

  // Fungsi untuk mengupdate semua chart dan tabel secara bersamaan
  function updateChartsAndTable() {
    updatePieChart();
    const result = updateDF8RelativeImportance();
    updateDF8RelativeTable(result.score, result.relative);
    updateRelativeCharts(result.relative);
  }

  // Inisialisasi perhitungan dan update tabel/chart saat halaman dimuat
  const initResult = updateDF8RelativeImportance();
  updateDF8RelativeTable(initResult.score, initResult.relative);
  updateRelativeCharts(initResult.relative);

  // Tambahkan event listener pada setiap input
  document.querySelectorAll('.input-percentage').forEach(input => {
    input.addEventListener('change', updateChartsAndTable);
  });

  // --- Restore server-provided latest inputs (if any) ---
  const serverHistoryInputs = @json($historyInputs ?? null);
  const serverHistoryScoreArray = @json($historyScoreArray ?? null);
  const serverHistoryRIArray = @json($historyRIArray ?? null);
  if (serverHistoryInputs) {
    document.getElementById('input1df8').value = serverHistoryInputs[0] ?? '';
    document.getElementById('input2df8').value = serverHistoryInputs[1] ?? '';
    document.getElementById('input3df8').value = serverHistoryInputs[2] ?? '';
    updateChartsAndTable();
  }
</script>

@endsection
