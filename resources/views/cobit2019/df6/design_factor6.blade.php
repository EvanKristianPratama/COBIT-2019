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
            <h4 class="mb-0">Design Factor 6 - Importance of Compliance Requirements</h4>
          </div>
          <!-- Card Body -->
          <div class="card-body p-4">
            <form action="{{ route('df6.store') }}" method="POST" onsubmit="return validateForm()">
              @csrf
              <input type="hidden" name="df_id" value="{{ $id }}">

              <!-- Tabel Input DF6 -->
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
                        <input type="number" name="input1df6" id="input1df6" class="form-control input-percentage"
                          required>
                        <small class="text-muted">Masukkan nilai dalam persen (contoh: 33 untuk 33%).</small>
                      </td>
                      <td class="text-center fw-bold text-success fs-5">
                        0%
                        <input type="hidden" name="baseline_1df6" value="33">
                      </td>
                    </tr>
                    <tr>
                      <td>Normal</td>
                      <td>
                        <input type="number" name="input2df6" id="input2df6" class="form-control input-percentage"
                          required>
                        <small class="text-muted">Masukkan nilai dalam persen (contoh: 33 untuk 33%).</small>
                      </td>
                      <td class="text-center fw-bold text-success fs-5">
                        100%
                        <input type="hidden" name="baseline_2df6" value="33">
                      </td>
                    </tr>
                    <tr>
                      <td>Low</td>
                      <td>
                        <input type="number" name="input3df6" id="input3df6" class="form-control input-percentage"
                          required>
                        <small class="text-muted">Masukkan nilai dalam persen (contoh: 34 untuk 34%).</small>
                      </td>
                      <td class="text-center fw-bold text-success fs-5">
                        0%
                        <input type="hidden" name="baseline_3df6" value="34">
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
                            <th class="text-center text-primary">GMO</th>
                            <th class="text-center text-primary">DF6 Score</th>
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
    // Inisialisasi Pie Chart dengan 3 bagian: High, Normal, dan Low
    const pieCtx = document.getElementById('pieChart').getContext('2d');
    const pieChart = new Chart(pieCtx, {
      type: 'pie',
      data: {
        labels: ['High', 'Normal', 'Low'],
        datasets: [{
          // Nilai awal untuk masing-masing bagian
          data: [33, 33, 33],
          backgroundColor: [
            'rgba(255, 99, 132, 0.6)', // Merah untuk High
            'rgba(54, 162, 235, 0.6)',  // Biru untuk Normal
            'rgba(255, 247, 0, 0.6)'    // Kuning untuk Low
          ],
          borderColor: [
            'rgba(255, 99, 132, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 247, 0, 1)'
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

    // Fungsi untuk memperbarui Pie Chart saat input berubah
    function updatePieChart() {
      // Ambil nilai input dari masing-masing field
      const input1 = parseFloat(document.getElementById('input1df6').value) || 0;
      const input2 = parseFloat(document.getElementById('input2df6').value) || 0;
      const input3 = parseFloat(document.getElementById('input3df6').value) || 0;

      // Perbarui data pada chart
      pieChart.data.datasets[0].data = [input1, input2, input3];

      // Perbarui warna background berdasarkan nilai input:
      // Jika nilai > 0, gunakan warna aslinya; jika tidak, gunakan warna abu-abu.
      pieChart.data.datasets[0].backgroundColor = [
        input1 > 0 ? 'rgba(255, 99, 132, 0.6)' : 'rgba(169, 169, 169, 0.6)',
        input2 > 0 ? 'rgba(54, 162, 235, 0.6)' : 'rgba(169, 169, 169, 0.6)',
        input3 > 0 ? 'rgba(255, 247, 0, 0.6)' : 'rgba(169, 169, 169, 0.6)'
      ];

      // Perbarui tampilan chart
      pieChart.update();
    }

    // Tambahkan event listener untuk masing-masing input agar chart terupdate secara real-time
    document.getElementById('input1df6').addEventListener('input', updatePieChart);
    document.getElementById('input2df6').addEventListener('input', updatePieChart);
    document.getElementById('input3df6').addEventListener('input', updatePieChart);

    // Fungsi validasi form sebelum submit
    function validateForm() {
      const input1 = parseFloat(document.getElementById('input1df6').value) || 0;
      const input2 = parseFloat(document.getElementById('input2df6').value) || 0;
      const input3 = parseFloat(document.getElementById('input3df6').value) || 0;

      // Total nilai dari ketiga input
      const total = input1 + input2 + input3;

      // Jika total melebihi 100, tampilkan pesan error dan batalkan submit
      if (total > 100) {
        document.getElementById('error-message').style.display = 'block';
        document.getElementById('error-message').textContent = 'Total nilai tidak boleh melebihi 100%.';
        return false;
      }

      // Jika total kurang dari 100, tampilkan pesan error dan batalkan submit
      if (total < 100) {
        document.getElementById('error-message').style.display = 'block';
        document.getElementById('error-message').textContent = 'Total nilai harus tepat 100%.';
        return false;
      }

      // Jika valid, sembunyikan pesan error dan izinkan submit
      document.getElementById('error-message').style.display = 'none';
      return true;
    }


    document.addEventListener('DOMContentLoaded', function () {
      // ==================== INISIALISASI CHART INPUT SCORE (DF6) ====================
      const inputLabels = ['High', 'Normal', 'Low'];

      // ===================================================================
      // DF6_MAP
      // ===================================================================
      const DF6_MAP = [
        [3.0, 2.0, 1.0],
        [1.0, 1.0, 1.0],
        [4.0, 2.0, 1.0],
        [1.0, 1.0, 1.0],
        [1.5, 1.0, 1.0],
        [2.0, 1.5, 1.0],
        [1.0, 1.0, 1.0],
        [1.0, 1.0, 1.0],
        [1.0, 1.0, 1.0],
        [1.0, 1.0, 1.0],
        [1.0, 1.0, 1.0],
        [1.0, 1.0, 1.0],
        [1.0, 1.0, 1.0],
        [1.0, 1.0, 1.0],
        [1.5, 1.0, 1.0],
        [1.0, 1.0, 1.0],
        [4.0, 2.0, 1.0],
        [1.5, 1.0, 1.0],
        [2.0, 1.5, 1.0],
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
        [1.5, 1.0, 1.0],
        [2.0, 1.0, 1.0],
        [1.0, 1.0, 1.0],
        [1.0, 1.0, 1.0],
        [1.0, 1.0, 1.0],
        [4.0, 2.0, 1.0],
        [3.5, 2.0, 1.0]
      ];

      // ===================================================================
      // DF6_BASELINE
      // ===================================================================
      const DF6_BASELINE = [
        [0],
        [100],
        [0]
      ];

      // ===================================================================
      // DF6_SC_BASELINE
      // ===================================================================
      const DF6_SC_BASELINE = [
        [2.00],
        [1.00],
        [2.00],
        [1.00],
        [1.00],
        [1.50],
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
        [2.00],
        [1.00],
        [1.50],
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
        [2.00],
        [2.00]
      ];

      // ===============================================================
      // Fungsi pembulatan ke kelipatan tertentu
      // ===============================================================
      function mround(value, multiple) {
        if (multiple === 0) return 0;
        return Math.round(value / multiple) * multiple;
      }

      // ===============================================================
      // Fungsi untuk menghitung DF6_SCORE dan DF6_RELATIVE_IMP
      // ===============================================================
      function updateDF6RelativeImportance() {
        // ---------------------------------------------------------------
        // 1. Ambil nilai input dari form dan konversi ke desimal.
        // ---------------------------------------------------------------
        const in1 = parseFloat(document.getElementById('input1df6').value) || 0;
        const in2 = parseFloat(document.getElementById('input2df6').value) || 100;
        const in3 = parseFloat(document.getElementById('input3df6').value) || 0;
        const DF6_INPUT = [
          [in1 / 100],
          [in2 / 100],
          [in3 / 100]
        ];

        // ---------------------------------------------------------------
        // 2. Hitung DF6_SCORE dengan perkalian matriks.
        // Untuk setiap baris pada DF6_MAP, kalikan setiap elemen dengan input yang bersesuaian
        // dan jumlahkan hasilnya.
        // ---------------------------------------------------------------
        let DF6_SCORE = [];
        DF6_MAP.forEach((row, i) => {
          let score = 0;
          DF6_INPUT.forEach((input, j) => {
            score += row[j] * input[0];
          });
          DF6_SCORE.push(score);
        });

        // ---------------------------------------------------------------
        // 3. Hitung DF6_RELATIVE_IMP.
        // Untuk setiap skor pada DF6_SCORE, bandingkan dengan nilai baseline pada DF6_SC_BASELINE.
        // Rumus: (100 * score / baseline) dibulatkan ke kelipatan 5, lalu dikurangi 100.
        // Tangani kasus pembagian dengan nol (baseline = 0).
        // ---------------------------------------------------------------
        let DF6_RELATIVE_IMP = [];
        DF6_SCORE.forEach((score, i) => {
          if (DF6_SC_BASELINE[i][0] !== 0) {
            let relativeValue = (100 * score) / DF6_SC_BASELINE[i][0];
            DF6_RELATIVE_IMP.push(mround(relativeValue, 5) - 100);
          } else {
            DF6_RELATIVE_IMP.push(0);
          }
        });

        // ---------------------------------------------------------------
        // 4. Kembalikan objek yang berisi DF6_SCORE dan DF6_RELATIVE_IMP.
        // ---------------------------------------------------------------
        return { score: DF6_SCORE, relative: DF6_RELATIVE_IMP };
      }


      function updateDF6RelativeTable(df6Scores, relativeImportanceValues) {
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
          <td class="text-center">${df6Scores[index].toFixed(2)}</td>
          <td class="text-center ${scoreClass}">${relativeValue}</td>
        `;
          tableBody.appendChild(row);
        });
      }

      // Cabut Saja Bisa Dipakai
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

      // Cabut Saja Bisa Dipakai

      function updateChartsAndTable() {
        updatePieChart();
        const result = updateDF6RelativeImportance();
        updateDF6RelativeTable(result.score, result.relative);
        updateRelativeCharts(result.relative);
      }

      const initResult = updateDF6RelativeImportance();
      updateDF6RelativeTable(initResult.score, initResult.relative);
      updateRelativeCharts(initResult.relative);

      document.querySelectorAll('.input-percentage').forEach(input => {
        input.addEventListener('change', updateChartsAndTable);
        input.addEventListener('input', validateTotal);
      });

      // --- Restore server-provided latest inputs (if any) ---
      const serverHistoryInputs = @json($historyInputs ?? null);
      const serverHistoryScoreArray = @json($historyScoreArray ?? null);
      const serverHistoryRIArray = @json($historyRIArray ?? null);
      if (serverHistoryInputs) {
        document.getElementById('input1df6').value = serverHistoryInputs[0] ?? '';
        document.getElementById('input2df6').value = serverHistoryInputs[1] ?? '';
        document.getElementById('input3df6').value = serverHistoryInputs[2] ?? '';
        // validate & update visuals
        validateTotal();
        updateChartsAndTable();
      }

      // Fungsi untuk validasi real-time total input
      function validateTotal() {
        const input1 = parseFloat(document.getElementById('input1df6').value) || 0;
        const input2 = parseFloat(document.getElementById('input2df6').value) || 0;
        const input3 = parseFloat(document.getElementById('input3df6').value) || 0;
        const total = input1 + input2 + input3;

        if (total > 100) {
          document.getElementById('error-message').style.display = 'block';
          document.getElementById('error-message').textContent = 'Total nilai tidak boleh melebihi 100%.';
        } else if (total < 100) {
          document.getElementById('error-message').style.display = 'block';
          document.getElementById('error-message').textContent = 'Total nilai harus tepat 100%.';
        } else {
          document.getElementById('error-message').style.display = 'none';
        }
      }
    });
  </script>

@endsection