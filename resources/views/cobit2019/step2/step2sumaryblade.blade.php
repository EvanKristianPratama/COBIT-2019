@extends('cobit2019.cobitTools')

@section('cobit-tools-content')
  @include('cobit2019.cobitPagination')

  <!-- Bungkus seluruh tampilan Step 2 di dalam sebuah form -->
  <form action="{{ route('step2.store') }}" method="POST" id="step2Form">
    @csrf
    <!-- Hidden input untuk menyimpan data weight dan relative importances -->
    <input type="hidden" name="weights" id="weightsInput">
    <input type="hidden" name="relative_importances" id="relative_importancesInput">
    <input type="hidden" name="totals" id="totalsInput">

    <div class="container my-4">
      <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white py-3">
          <h5 class="mb-0">Step 2: Determine the initial scope of the Governance System</h5>
        </div>
        <div class="card-body">
          <!-- Assessment ID -->
          <div class="d-flex align-items-center mb-4">
            <div class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
              <i class="fas me-2"></i>
              Design Factor ID: <strong>{{ $assessment->assessment_id }}</strong>
            </div>
          </div>

          @php
            use Illuminate\Support\Str;
            // Kumpulkan semua kode Design Factor unik (DF1–DF4)
            $allCodes = collect();
            for ($n = 1; $n <= 4; $n++) {
              $ris = $assessment->{'df' . $n . 'RelativeImportances'}->first() ?? collect();
              foreach ($ris->toArray() as $col => $val) {
                if (Str::startsWith($col, "r_df{$n}_")) {
                  $allCodes->push(Str::after($col, "r_df{$n}_"));
                }
              }
            }
            $allCodes = $allCodes->unique()->sort()->values();
            // Daftar lengkap kode COBIT 2019 untuk label
            $cobitCodes = [
              '',
              'EDM01','EDM02','EDM03','EDM04','EDM05',
              'APO01','APO02','APO03','APO04','APO05','APO06','APO07','APO08','APO09','APO10','APO11','APO12','APO13','APO14',
              'BAI01','BAI02','BAI03','BAI04','BAI05','BAI06','BAI07','BAI08','BAI09','BAI10','BAI11',
              'DSS01','DSS02','DSS03','DSS04','DSS05','DSS06',
              'MEA01','MEA02','MEA03','MEA04'
            ];
            // Berat tiap dimensi (dapat diubah oleh user)
            $sessionWeights = session('step2.weights') ?? [1, 1, 1, 1];
            // Pakai savedWeights dari controller jika ada, fallback ke session
            $weights = $savedWeights ?? old('weight', $sessionWeights);
          @endphp

          <!-- Tabel Relative Importance Matrix -->
          <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
              <h6 class="mb-0 fw-bold text-primary">Relative Importance Matrix</h6>
              <!-- Tombol kecil Simpan Sementara -->
              <div class="mt-3">
                <button type="button" id="saveButton" class="btn btn-sm btn-secondary">Simpan Weight</button>
              </div>
            </div>

            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm mb-0" id="matrixTable">
                  <thead>
                    <tr>
                      <th class="text-center bg-secondary fw-bold text-white" style="width: 120px;">Design Factor</th>
                      <th class="text-center bg-primary text-white">Enterprise Strategy</th>
                      <th class="text-center bg-primary text-white">Enterprise Goals</th>
                      <th class="text-center bg-primary text-white">Risk Profile</th>
                      <th class="text-center bg-primary text-white">IT-Related Issues</th>
                      <th class="text-center bg-info text-white">Total</th>
                      <th class="text-center bg-secondary text-white" style="width: 200px;">
                        Initial Scope:<br>Governance/Management Objectives
                      </th>
                    </tr>
                    <tr class="bg-warning">
                      <th class="fw-bold bg-success text-center text-white">Weight</th>
                      @for ($i = 0; $i < 4; $i++)
                        <th class="text-center bg-success">
                          <input type="number" name="weight[{{ $i + 1 }}]" value="{{ $weights[$i] }}"
                            class="form-control form-control-sm text-center d-block mx-auto weight-input"
                            style="width: 60px;" data-index="{{ $i }}">
                        </th>
                      @endfor
                      <th class="text-center">-</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($allCodes as $code)
                      <tr>
                        <td class="fw-bold bg-primary-subtle">
                          {{ $cobitCodes[$code] ?? '' }}
                        </td>
                        @php
                          $total = 0;
                          $values = [];
                        @endphp
                        @for ($n = 1; $n <= 4; $n++)
                          @php
                            $rec = $assessment->{'df' . $n . 'RelativeImportances'}->firstWhere('id', auth()->id());
                            $col = "r_df{$n}_{$code}";
                            $val = ($rec && isset($rec->$col)) ? $rec->$col : 0;
                            $values[] = $val;
                            $total += $val;
                            $cls = $val < 0
                              ? 'bg-danger bg-opacity-10'
                              : ($val > 0 ? 'bg-success bg-opacity-10' : '');
                          @endphp
                          <td class="text-center {{ $cls }} fw-medium value-cell" data-value="{{ $val }}">
                            {{ number_format($val, 0) }}
                          </td>
                        @endfor
                        <td class="text-center bg-info bg-opacity-10 fw-bold total-cell">
                          {{ number_format($total, 0) }}
                        </td>
                        <td class="text-center fw-medium initial-scope-cell">0</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Chart Utama: Initial Scope vs. Weights -->
          <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
              <h6 class="mb-0 fw-bold text-primary">Chart: Initial Scope vs. Weights</h6>
            </div>
            <div class="card-body">
              <canvas id="initialScopeChart" style="height:300px;"></canvas>
            </div>
          </div>


        </div>
      </div>
    </div>
  </form>

  <!-- Styles tambahan -->
  <style>
    .table {
      margin-bottom: 0;
    }

    .table th {
      border-top: none;
      font-weight: 600;
      vertical-align: middle;
    }

    .table td {
      vertical-align: middle;
    }

    .table input[type="number"] {
      -moz-appearance: textfield;
    }

    .table input[type="number"]::-webkit-outer-spin-button,
    .table input[type="number"]::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }

    .table-hover tbody tr:hover {
      background-color: rgba(var(--bs-primary-rgb), 0.05);
    }
  </style>

  <!-- Chart.js via CDN -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const weightInputs = document.querySelectorAll('.weight-input');
      const rows = document.querySelectorAll('#matrixTable tbody tr');

      function roundToNearest(number, multiple) {
        return Math.round(number / multiple) * multiple;
      }

      function number_format(number, decimals) {
        return new Intl.NumberFormat('en-US', {
          minimumFractionDigits: decimals,
          maximumFractionDigits: decimals
        }).format(number);
      }

      function calculateTotal(row) {
        const cells = row.querySelectorAll('.value-cell');
        const weights = Array.from(weightInputs).map(input => parseFloat(input.value) || 0);
        let total = 0;
        cells.forEach((cell, index) => {
          const value = parseFloat(cell.dataset.value) || 0;
          total += value * weights[index];
        });
        row.querySelector('.total-cell').textContent = number_format(total, 0);
        return total;
      }

      function updateInitialScope(total, maxTotal, row) {
        let initialScope = 0;
        if (maxTotal !== 0) {
          const percentage = Math.trunc((total / maxTotal) * 100);
          initialScope = total >= 0
            ? roundToNearest(percentage, 5)
            : -roundToNearest(Math.abs(percentage), 5);
        }
        // Bersihkan cell dan buat container dengan width tetap
        const cell = row.querySelector('.initial-scope-cell');
        cell.innerHTML = '';

        const container = document.createElement('div');
        container.style.cssText = `
          position: relative; 
          height: 20px; 
          width: 180px; 
          background: #f8f9fa; 
          border: 1px solid #ddd;
          margin: 0 auto;
          overflow: hidden;
        `;

        // Garis tengah sebagai baseline
        const centerLine = document.createElement('div');
        centerLine.style.cssText = 'position: absolute; left: 50%; top: 0; bottom: 0; width: 1px; background: #aaa;';
        container.appendChild(centerLine);

        // Buat bar chart
        const bar = document.createElement('div');
        const barWidth = Math.abs(initialScope) / 2;

        if (initialScope >= 0) {
          bar.style.cssText = `
          position: absolute;
          left: 50%;
          top: 0;
          height: 100%;
          width: ${barWidth}%;
          background-color: rgba(40, 167, 69, 0.8);
          transition: all 1s ease;
          max-width: 50%;
          `;
        } else {
          bar.style.cssText = `
          position: absolute;
          right: 50%;
          top: 0;
          height: 100%;
          width: ${barWidth}%;
          background-color: rgba(220, 53, 69, 0.8);
          transition: all 1s ease;
          max-width: 50%;
          `;
        }
        container.appendChild(bar);

        // Label nilai di tengah bar
        const label = document.createElement('div');
        label.style.cssText = `
          position: absolute;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 0.8rem;
          font-weight: 500;
          color: #343a40;
          z-index: 1;
        `;
        label.textContent = number_format(initialScope, 0);
        container.appendChild(label);

        cell.appendChild(container);
        cell.setAttribute('data-scope', initialScope);
        return initialScope;
      }

      function calculateInitialScope() {
        // 1. Hitung total tiap baris
        const totals = Array.from(rows).map(row => calculateTotal(row));
        // 3. Tentukan nilai maksimum untuk normalisasi scope
        const maxTotal = Math.max(...totals);

        // 4. Hitung initialScope untuk tiap baris dan kumpulkan ke array
        const scopes = totals.map((total, index) =>
          updateInitialScope(total, maxTotal, rows[index])
        );
        renderInitialScopeChart(scopes);
        const weights = Array.from(weightInputs).map(input => parseFloat(input.value) || 0);
        const relativeImportances = Array.from(rows).map(row =>
          Array.from(row.querySelectorAll('.value-cell'))
            .map(cell => parseFloat(cell.dataset.value) || 0)
        );
        // 7. Simpan ke hidden inputs
        document.getElementById('totalsInput').value = JSON.stringify(totals);
        document.getElementById('weightsInput').value = JSON.stringify(weights);
        document.getElementById('relative_importancesInput').value = JSON.stringify(relativeImportances);
      }


      function renderInitialScopeChart(scopes) {
        const labels = Array.from(rows).map(row => row.querySelector('td').textContent.trim());
        const backgroundColors = scopes.map(scope => scope >= 0 ? '#28a745' : '#dc3545');
        const ctx = document.getElementById('initialScopeChart').getContext('2d');

        if (window.initialScopeChart instanceof Chart) {
          window.initialScopeChart.destroy();
        }
        window.initialScopeChart = new Chart(ctx, {
          type: 'bar',
          data: {
            labels: labels,
            datasets: [{
              label: 'Initial Scope',
              data: scopes,
              backgroundColor: backgroundColors
            }]
          },
          options: {
            indexAxis: 'y',
            scales: {
              x: {
                beginAtZero: true,
                grid: {
                  drawBorder: true,
                  drawOnChartArea: true,
                  drawTicks: false
                }
              },
              y: { ticks: { autoSkip: false, maxTicksLimit: 40 } }
            },
            plugins: {
              legend: { display: false },
              tooltip: {
                callbacks: {
                  label: function (context) {
                    return number_format(context.parsed.x, 0);
                  }
                }
              }
            }
          }
        });
      }

      // Event listener untuk input weight
      weightInputs.forEach(input => {
        input.addEventListener('input', function () {
          calculateInitialScope();
        });
      });

      // Hitung ulang saat halaman dimuat awal
      calculateInitialScope();

      // Tombol kecil Simpan Sementara: saat diklik, form disubmit
      document.getElementById('saveButton').addEventListener('click', function () {
        calculateInitialScope();
        document.getElementById('step2Form').submit();
      });
    });
  </script>
@endsection