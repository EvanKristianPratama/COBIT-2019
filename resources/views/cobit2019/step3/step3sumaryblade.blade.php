@extends('cobit2019.cobitTools')

@section('cobit-tools-content')
  @include('cobit2019.cobitPagination')
  <!-- Form untuk simpan sementara Step 3 -->
  <form action="{{ route('step3.store') }}" method="POST" id="step3Form">
    @csrf
    <input type="hidden" name="weights3" id="weights3Input">
    <input type="hidden" name="refinedScopes" id="refinedScopesInput">

    <!-- ALERT unsaved -->
    <div id="unsavedAlert3" class="alert alert-warning d-none">
      Nilai belum di save
    </div>


    @if (session('success'))
      <div class="alert alert-success">
        {{ session('success') }}
      </div>
    @elseif (session('error'))
      <div class="alert alert-danger">
        {{ session('error') }}

    @endif

      <div class="container my-4">
        <div class="card shadow-sm mb-4">
          <!-- Ubah header card menjadi mirip step 2 -->
          <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0">Step 3 Summary : Refine the scope of the Governance System</h5>
          </div>
          <div class="card-body">
            <div class="d-flex align-items-center mb-4">
              <div class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">

                <i class="fas me-2"></i>
                Design Factor ID: <strong>{{ $assessment->assessment_id }}</strong>
              </div>

            </div>

            @php
              use Illuminate\Support\Str;
              
              // Daftar lengkap 40 kode COBIT 2019 (1-indexed)
              $cobitCodes = [
                '',  // index 0 (tidak digunakan)
                'EDM01','EDM02','EDM03','EDM04','EDM05',
                'APO01','APO02','APO03','APO04','APO05','APO06','APO07','APO08','APO09','APO10','APO11','APO12','APO13','APO14',
                'BAI01','BAI02','BAI03','BAI04','BAI05','BAI06','BAI07','BAI08','BAI09','BAI10','BAI11',
                'DSS01','DSS02','DSS03','DSS04','DSS05','DSS06',
                'MEA01','MEA02','MEA03','MEA04'
              ];

              // Kumpulkan kode yang ada dari database (jika ada)
              $existingCodes = collect();
              for ($n = 5; $n <= 10; $n++) {
                $ris = $assessment->{'df' . $n . 'RelativeImportances'}->first();
                if ($ris) {
                  foreach ($ris->toArray() as $col => $val) {
                    if (Str::startsWith($col, "r_df{$n}_")) {
                      $existingCodes->push(Str::after($col, "r_df{$n}_"));
                    }
                  }
                }
              }

              // Gunakan semua 40 kode (index 1-40) sebagai default, agar tabel selalu muncul
              // Bahkan ketika belum ada data DF sama sekali
              $allCodes = collect(range(1, 40));

              // Berat masing-masing dimensi (bisa diisi user)
              $default3 = [1,1,1,1,1,1];
              $weightsSource3 = $savedWeights3 ?? session('step3.weights', $default3);
              $weights = old('weight', $weightsSource3);
            @endphp

            <div class="card shadow-sm mb-4">
              <!-- Ubah header tabel menjadi bg-primary dan text-white -->
              <div class="card-header text-primary py-3 bg-white">
                <h6 class="fw-bold mb-2">Relative Importance Matrix</h6>

                <button type="button" id="save3Button" class="btn btn-sm btn-secondary">
                  Simpan Weight
                </button>

              </div>
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-bordered table-hover table-sm mb-0" id="matrixTable">
                    <thead>
                      <tr>
                        <th class="text-center bg-secondary fw-bold text-white" style="width: 120px;">Design Factors</th>
                        <th class="text-center bg-primary text-white">Threat Landscape</th>
                        <th class="text-center bg-primary text-white">Compliance Req's</th>
                        <th class="text-center bg-primary text-white">Role of IT</th>
                        <th class="text-center bg-primary text-white">"Sourcing Model for IT"</th>
                        <th class="text-center bg-primary text-white">IT Implementation Methods</th>
                        <th class="text-center bg-primary text-white">Technology Adoption Strategy</th>
                        <th class="text-center bg-info text-white">Total</th>
                        <th class="text-center bg-secondary text-white" style="width: 200px;">
                          Refined Scope:<br>Governance/Management Objectives Score
                        </th>
                      </tr>
                      <tr class="bg-success">
                        <th class="fw-bold text-center text-white bg-warning">Weight</th>
                        @for ($i = 0; $i < 6; $i++)
                          <th class="text-center bg-success">
                            <input type="number" name="weight[{{ $i + 1 }}]" value="{{ $weights[$i] ?? 1 }}"
                              class="form-control form-control-sm text-center d-block mx-auto weight-input"
                              style="width: 60px;" data-index="{{ $i }}">
                          </th>
                        @endfor
                        <th class="text-center text-white">-</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($allCodes as $code)
                        <tr>
                          <td class="fw-bold bg-primary-subtle text-primary">
                            {{ $cobitCodes[$code] ?? '' }}
                          </td>
                          @php
                            $total = 0;
                            $values = [];
                          @endphp
                          @for ($n = 5; $n <= 10; $n++)
                            @php
                              // Get first record from relation (already scoped to current assessment)
                              $rec = $assessment->{'df' . $n . 'RelativeImportances'}->first();
                              $col = "r_df{$n}_{$code}";
                              // Default value: 0 if Relative Importance not yet set (KISS principle)
                              $val = ($rec && isset($rec->$col)) ? $rec->$col : 0;
                              $values[] = $val;
                              $cls = $val < 0 ? 'bg-danger bg-opacity-10' : ($val > 0 ? 'bg-success bg-opacity-10' : '');
                            @endphp
                            <td class="text-center {{ $cls }} fw-medium value-cell" data-value="{{ $val }}">
                              {{ number_format($val, 0) }}
                            </td>

                          @endfor
                          @php
                            // Tambahkan array values baris ini
                            $step3RelImps[] = $values;
                          @endphp
                          <td class="text-center bg-info bg-opacity-10 fw-bold total-cell">
                            {{ number_format($total, 0) }}
                          </td>
                          <td class="text-center fw-medium refined-scope-cell">0</td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <p>
              @php
                // Gunakan semua 40 kode COBIT (1-40) untuk aggregasi DF1-10
                // Sama seperti di tabel utama, untuk konsistensi
                $allCodesForAggregation = collect(range(1, 40));

                // Kumpulkan satu array per code, DF1–DF10
                $AllRelImps = [];
                foreach ($allCodesForAggregation as $code) {
                  $arr = [];
                  for ($n = 1; $n <= 10; $n++) {
                    // Get first record from relation (already scoped to current assessment)
                    $rec = $assessment->{'df' . $n . 'RelativeImportances'}->first();
                    $col = "r_df{$n}_{$code}";
                    // Default value: 0 if Relative Importance not yet set (KISS principle)
                    $arr[] = ($rec && isset($rec->$col)) ? $rec->$col : 0;
                  }
                  $AllRelImps[$code] = $arr;
                }
              @endphp
              <!-- Chart Section (sama style dengan step 2) -->
            <div class="card shadow-sm">
              <div class="card-header bg-primary text-white py-3">
                <h6 class="mb-0 fw-bold">Chart: Refined Scope vs. Weights</h6>
              </div>
              <div id="chart-container" style="height:300px;">
                <canvas id="refinedScopeChart"></canvas>
              </div>

            </div>
          </div>
        </div>
      </div>
  </form>
  <!-- Styles tambahan (sama seperti di step 2) -->
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

    /* Unsaved alert — fixed top-right, non-intrusive */
    #unsavedAlert3 {
      position: fixed;
      top: 1rem;
      right: 1rem;
      z-index: 1080;
      width: 260px;
      box-shadow: 0 6px 18px rgba(0,0,0,0.12);
      border-radius: .5rem;
      padding: .6rem 1rem;
      display: flex;
      align-items: center;
      gap: .5rem;
    }
    #unsavedAlert3 .small {
      margin: 0;
      font-size: .9rem;
    }
  </style>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    // 0) Ambil total Step 2 dari PHP (array indexed sesuai urutan `codes`)
    const step2Totals = @json($step2Totals); // e.g. [120, 85, 145, …]

    // 1) Ambil bobot Step 2 dari PHP
    const step2Weights = @json($step2Weights);          // e.g. [1,2,0,0]
    // 2) Ambil semua Relative Importances DF1–DF10 per code
    let allRelImps = @json($AllRelImps) || {};
    // 3) Urutan kode (sama dengan row di tabel) — if empty, build from table rows and fill zeros
    let codes = Object.keys(allRelImps || {});

    // helper to ensure each code has an array length 10 (DF1..DF10) filled with zeros if missing
    function ensureRelImpsDefault() {
      // if no codes from server, build from table rows
      if (!codes || codes.length === 0) {
        const rows = Array.from(document.querySelectorAll('#matrixTable tbody tr'));
        codes = rows.map(r => r.querySelector('td').textContent.trim());
      }
      codes.forEach(code => {
        if (!Array.isArray(allRelImps[code]) || allRelImps[code].length < 10) {
          allRelImps[code] = new Array(10).fill(0);
        }
      });
    }

    document.addEventListener('DOMContentLoaded', function () {
      const unsavedAlert3 = document.getElementById('unsavedAlert3');
      const save3Button = document.getElementById('save3Button');
      let isDirty3 = false;

      function markDirty3() {
        isDirty3 = true;
        if (unsavedAlert3) unsavedAlert3.classList.remove('d-none');
      }
      function clearDirty3() {
        isDirty3 = false;
        if (unsavedAlert3) unsavedAlert3.classList.add('d-none');
      }
      window.addEventListener('beforeunload', function (e) {
        if (!isDirty3) return;
        const msg = 'Anda memiliki perubahan yang belum disimpan. Keluar tanpa menyimpan?';
        e.returnValue = msg;
        return msg;
      });

       const weightInputs = document.querySelectorAll('.weight-input');
       const rows = document.querySelectorAll('#matrixTable tbody tr');

      // helper: format angka tanpa desimal
      function number_format(num) {
        return new Intl.NumberFormat('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(num);
      }
      // helper: bulat ke kelipatan terdekat
      function roundToNearest(val, mul) {
        return Math.round(val / mul) * mul;
      }

      // baca bobot Step 3 saja (input user)
      function getStep3Weights() {
        return Array.from(weightInputs)
          .map(i => parseFloat(i.value) || 0);
      }

      // menghitung kontribusi Step 3 = Σ (RI5–10 * weightStep3)
      function calcStep3Total(code, weights3) {
        const rels = allRelImps[code].slice(4, 10); // indeks 4–9 = DF5–DF10
        return rels.reduce((sum, rel, i) => sum + rel * weights3[i], 0);
      }

      // update kolom Refined Scope per baris dengan vbar
      function updateRefinedScope(total, maxTotal, row) {
        // hitung nilai refined scope (% dibulatkan 5 terdekat)
        let refined = 0;
        if (maxTotal !== 0) {
          const pct = Math.trunc((total / maxTotal) * 100);
          refined = total >= 0
            ? roundToNearest(pct, 5)
            : -roundToNearest(Math.abs(pct), 5);
        }

        // ambil sel
        const cell = row.querySelector('.refined-scope-cell');
        // kosongkan dulu
        cell.innerHTML = '';

        // container vbar
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

        // garis tengah baseline
        const centerLine = document.createElement('div');
        centerLine.style.cssText = `
      position: absolute;
      left: 50%;
      top: 0;
      bottom: 0;
      width: 1px;
      background: #aaa;
    `;
        container.appendChild(centerLine);

        // buat bar (kanan untuk positif, kiri untuk negatif)
        const bar = document.createElement('div');
        // barWidth: setengah skala (100 → 50%)
        const barWidth = Math.abs(refined) / 2;
        if (refined >= 0) {
          bar.style.cssText = `
        position: absolute;
        left: 50%;
        top: 0;
        height: 100%;
        width: ${barWidth}%;
        background-color: rgba(40, 167, 69, 0.8);
        transition: all 0.5s ease;
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
        transition: all 0.5s ease;
        max-width: 50%;
      `;
        }
        container.appendChild(bar);

        // label angka di tengah
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
        label.textContent = number_format(refined);
        container.appendChild(label);

        // masukkan ke cell
        cell.appendChild(container);
        // simpan nilai refined sebagai data‑attribute (opsional)
        cell.setAttribute('data-scope', refined);
        return refined;
      }


      // fungsi utama: hitung total = existing Step2 + kontribusi Step3
      function calculateRefinedScope() {
        const weights3 = getStep3Weights();

        // hitung total per code
        const totals = codes.map((code, idx) => {
          const tot2 = step2Totals[idx] || 0;               // Total dari Step 2
          const tot3 = calcStep3Total(code, weights3);      // Total Step 3
          return tot2 + tot3;
        });

        const maxTotal = Math.max(...totals) || 1;

        // render ke tabel
        const scopes = totals.map((tot, idx) => {
          const row = rows[idx];
          row.querySelector('.total-cell').textContent = number_format(tot);
          return updateRefinedScope(tot, maxTotal, row);
        });

        // render chart
        renderRefinedScopeChart(scopes);

        // simpan ke hidden input agar bisa dikirim saat submit
        const wInput = document.getElementById('weights3Input');
        const sInput = document.getElementById('refinedScopesInput');
        if (wInput) wInput.value = JSON.stringify(weights3);
        if (sInput) sInput.value = JSON.stringify(scopes);
      }

      function renderRefinedScopeChart(scopes) {
        const labels = Array.from(rows).map(row => row.querySelector('td').textContent.trim());
        const backgroundColors = scopes.map(scope => scope >= 0 ? '#28a745' : '#dc3545');
        const ctx = document.getElementById('refinedScopeChart').getContext('2d');

        if (window.refinedScopeChart instanceof Chart) {
          window.refinedScopeChart.destroy();
        }
        window.refinedScopeChart = new Chart(ctx, {
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

      // ensure defaults for allRelImps (if server returned empty)
      ensureRelImpsDefault();

      // bind dirty flag & Auto-save
      let saveInterval;
      
      // Indikator status save
      const statusIndicator = document.createElement('span');
      statusIndicator.className = 'ms-2 text-muted small';
      statusIndicator.style.transition = 'opacity 0.5s';
      const headerTitle = document.querySelector('.card-header h5');
      if(headerTitle) headerTitle.appendChild(statusIndicator);

      function showSavingStatus(status) {
        if (status === 'saving') {
          statusIndicator.textContent = 'Saving...';
          statusIndicator.className = 'ms-2 text-warning small';
          statusIndicator.style.opacity = '1';
        } else if (status === 'saved') {
          statusIndicator.textContent = 'All changes saved';
          statusIndicator.className = 'ms-2 text-success small';
          setTimeout(() => { statusIndicator.style.opacity = '0'; }, 3000);
        } else if (status === 'error') {
          statusIndicator.textContent = 'Error saving';
          statusIndicator.className = 'ms-2 text-danger small';
        }
      }

      function autoSave3() {
        calculateRefinedScope(); // update inputs
        // Clear dirty flag karena kita sedang saving otomatis
        clearDirty3();

        const form = document.getElementById('step3Form');
        const formData = new FormData(form);

        showSavingStatus('saving');

        fetch(form.action, {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            showSavingStatus('saved');
            console.log(data.message);
          }
        })
        .catch(error => {
          console.error(error);
          showSavingStatus('error');
          // Jika error, kembalikan flag dirty agar user aware
          markDirty3();
        });
      }

      weightInputs.forEach(i => i.addEventListener('input', function () {
        markDirty3();
        calculateRefinedScope();
        
        // Debounce auto-save
        clearTimeout(saveInterval);
        saveInterval = setTimeout(autoSave3, 1000);
      }));

      // initial calc
      calculateRefinedScope();

      // simpan sementara manual
      if (save3Button) {
        save3Button.addEventListener('click', () => {
          // calculate & set hidden inputs
          calculateRefinedScope();
          clearDirty3(); // manual save also clears dirty
          document.getElementById('step3Form').submit();
        });
      }

    });
   </script>
 
@endsection