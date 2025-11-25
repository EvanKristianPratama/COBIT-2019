{{-- resources/views/cobit2019/step4/step4‐matrices.blade.php --}}
@extends('cobit2019.cobitTools')
@section('cobit-tools-content')
  @include('cobit2019.cobitPagination')

  @php
    use Illuminate\Support\Str;

    //
    // 1) Siapkan daftar kode COBIT (1..40) sekali, untuk label baris di kedua tabel
    //
    $cobitCodes = [
      '',
      'EDM01',
      'EDM02',
      'EDM03',
      'EDM04',
      'EDM05',
      'APO01',
      'APO02',
      'APO03',
      'APO04',
      'APO05',
      'APO06',
      'APO07',
      'APO08',
      'APO09',
      'APO10',
      'APO11',
      'APO12',
      'APO13',
      'APO14',
      'BAI01',
      'BAI02',
      'BAI03',
      'BAI04',
      'BAI05',
      'BAI06',
      'BAI07',
      'BAI08',
      'BAI09',
      'BAI10',
      'BAI11',
      'DSS01',
      'DSS02',
      'DSS03',
      'DSS04',
      'DSS05',
      'DSS06',
      'MEA01',
      'MEA02',
      'MEA03',
      'MEA04'
    ];

    //
    // 2) Kumpulkan “allCodes2” = semua kode yang muncul di tabel df1..df4
    //
    $allCodes2 = collect();
    for ($n = 1; $n <= 4; $n++) {
      $firstRow = $assessment->{'df' . $n . 'RelativeImportances'}->first() ?? collect();
      foreach ($firstRow->toArray() as $col => $val) {
        if (Str::startsWith($col, "r_df{$n}_")) {
          $allCodes2->push(Str::after($col, "r_df{$n}_"));
        }
      }
    }
    $allCodes2 = $allCodes2->unique()->sort()->values();

    //
    // 3) Kumpulkan “allCodes3” = semua kode yang muncul di tabel df5..df10
    //
    $allCodes3 = collect();
    for ($n = 5; $n <= 10; $n++) {
      $firstRow = $assessment->{'df' . $n . 'RelativeImportances'}->first() ?? collect();
      foreach ($firstRow->toArray() as $col => $val) {
        if (Str::startsWith($col, "r_df{$n}_")) {
          $allCodes3->push(Str::after($col, "r_df{$n}_"));
        }
      }
    }
    $allCodes3 = $allCodes3->unique()->sort()->values();

    //
    // 4) Ambil bobot dari session
    //
    // controller mengirim: $step2['weights'] dan $step3['weights']
    $weights2 = $step2['weights'] ?? session('step2.weights', [0, 0, 0, 0]);
    $weights3 = $step3['weights'] ?? session('step3.weights', [0, 0, 0, 0, 0, 0]);

    //
    // 5) Siapkan array “Refined Scope” dari Step 3 untuk Step 4 nanti
    //
    $step3RefinedScopes = [];
    foreach ($allCodes3 as $idx => $code) {
      // Hitung kembali “Refined Scope” di server (meski JS juga menghitungnya)
      $total3 = 0;
      for ($n = 5; $n <= 10; $n++) {
        $rec = $assessment->{'df' . $n . 'RelativeImportances'}->firstWhere('id', auth()->id());
        $colKey = "r_df{$n}_{$code}";
        $v = ($rec && isset($rec->$colKey)) ? $rec->$colKey : 0;
        $total3 += $v * ($weights3[$n - 5] ?? 0);
      }
      // Simpan di array
      $step3RefinedScopes[] = $total3;
    }

    //
    // 6) Gabungkan kedua list kode untuk Step 4 (urutkan seperti allCodes2 + allCodes3)
    //
    $allCodes = $allCodes2->merge($allCodes3)
      ->unique()
      ->values();
  @endphp
  <form action="{{ route('step4.store') }}" method="POST" id="step4Form">
    @csrf

    <div class="container my-4">
      <div class="row gx-4">

        {{-- STEP 2 MATRIX (kiri) --}}
        <div class="col-md-6">
          <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white py-3">
              <h6 class="mb-0">Step 2: Determine the Initial Scope of the Governance System</h6>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm mb-0" id="step2Table">
                  <thead>
                    <tr class="bg-white">
                      <th class="text-center bg-secondary text-white" style="width: 120px;">Design Factor</th>
                      <th class="text-center bg-primary text-white">Enterprise Strategy</th>
                      <th class="text-center bg-primary text-white">Enterprise Goals</th>
                      <th class="text-center bg-primary text-white">Risk Profile</th>
                      <th class="text-center bg-primary text-white">IT-Related Issues</th>
                      <th class="text-center bg-info text-info">Total</th>
                      <th class="text-center bg-secondary text-white" style="width: 200px;">
                        Initial Scope:<br>Governance/Management Objectives Score
                      </th>
                    </tr>
                    <tr class="bg-warning">
                      <th class="fw-bold bg-warning text-center text-white">Weight</th>
                      @for ($i = 0; $i < 4; $i++)
                        <th class="text-center bg-success">
                          <input type="number" name="weight2[{{ $i + 1 }}]" value="{{ $weights2[$i] }}"
                            class="form-control form-control-sm text-center weight2-input" style="width: 60px;"
                            data-index="{{ $i }}">
                        </th>
                      @endfor
                      <th class="text-center bg-info text-white">—</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($allCodes2 as $idx => $code)
                      @php
                        $values2 = [];
                        $total2 = 0;
                        for ($n = 1; $n <= 4; $n++) {
                          $rec = $assessment->{'df' . $n . 'RelativeImportances'}->firstWhere('id', auth()->id());
                          $colKey = "r_df{$n}_{$code}";
                          $v = ($rec && isset($rec->$colKey)) ? $rec->$colKey : 0;
                          $values2[] = $v;
                          $total2 += ($v * ($weights2[$n - 1] ?? 0));
                        }
                      @endphp
                      <tr>
                        <td class="fw-bold bg-primary-subtle text-center">
                          {{ $cobitCodes[$code] ?? '' }}
                        </td>
                        @foreach ($values2 as $i => $val2)
                          @php
                            $cls = '';
                            if ($val2 < 0) {
                              $cls = 'bg-danger bg-opacity-10';
                            } elseif ($val2 > 0) {
                              $cls = 'bg-success bg-opacity-10';
                            }
                          @endphp
                          <td class="text-center {{ $cls }} fw-medium value2-cell" data-value="{{ $val2 }}">
                            {{ number_format($val2, 0) }}
                          </td>
                        @endforeach
                        <td class="text-center bg-info bg-opacity-10 fw-bold total2-cell">
                          {{ number_format($total2, 0) }}
                        </td>
                        <td class="text-center fw-medium initial-scope-cell2">0</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        {{-- STEP 3 MATRIX (kanan) --}}
        <div class="col-md-6">
          <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white py-3">
              <h6 class="mb-0">Step 3: Refine the Scope of the Governance System</h6>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm mb-0" id="step3Table">
                  <thead>
                    <tr class="bg-white">
                      <th class="text-center bg-secondary text-white" style="width: 140px;">Design Factor</th>
                      <th class="text-center bg-primary text-white">Threat Landscape</th>
                      <th class="text-center bg-primary text-white">Compliance Req’s</th>
                      <th class="text-center bg-primary text-white">Role of IT</th>
                      <th class="text-center bg-primary text-white">“Sourcing Model for IT”</th>
                      <th class="text-center bg-primary text-white">IT Implementation on Methods</th>
                      <th class="text-center bg-primary text-white">Technology Adoption Strategy</th>
                      <th class="text-center bg-info text-white">Total</th>
                      <th class="text-center bg-secondary text-white" style="width: 200px;">
                        Refined Scope:<br>Governance/Management Objectives Score
                      </th>
                    </tr>
                    <tr class="bg-success">
                      <th class="fw-bold bg-warning text-center text-white">Weight</th>
                      @for ($i = 0; $i < 6; $i++)
                        <th class="text-center bg-success">
                          <input type="number" name="weight3[{{ $i + 1 }}]" value="{{ $weights3[$i] }}"
                            class="form-control form-control-sm text-center weight3-input" style="width: 60px;"
                            data-index="{{ $i }}">
                        </th>
                      @endfor
                      <th class="text-center bg-info text-white">—</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($allCodes3 as $idx => $code)
                      @php
                        $values3 = [];
                        $total3 = 0;
                        for ($n = 5; $n <= 10; $n++) {
                          $rec = $assessment->{'df' . $n . 'RelativeImportances'}->firstWhere('id', auth()->id());
                          $colKey = "r_df{$n}_{$code}";
                          $v = ($rec && isset($rec->$colKey)) ? $rec->$colKey : 0;
                          $values3[] = $v;
                          $total3 += ($v * ($weights3[$n - 5] ?? 0));
                        }
                      @endphp
                      <tr>
                        <td class="fw-bold bg-primary-subtle text-center">
                          {{ $cobitCodes[$code] ?? '' }}
                        </td>
                        @foreach ($values3 as $i => $val3)
                          @php
                            $cls = '';
                            if ($val3 < 0) {
                              $cls = 'bg-danger bg-opacity-10';
                            } elseif ($val3 > 0) {
                              $cls = 'bg-success bg-opacity-10';
                            }
                          @endphp
                          <td class="text-center {{ $cls }} fw-medium value3-cell" data-value="{{ $val3 }}">
                            {{ number_format($val3, 0) }}
                          </td>
                        @endforeach
                        <td class="text-center bg-info bg-opacity-10 fw-bold total3-cell">
                          {{ number_format($total3, 0) }}
                        </td>
                        <td class="text-center fw-medium refined-scope-cell3" data-refined="{{ $total3 }}">0</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

      </div> {{-- /.row --}}

      {{-- STEP 4 ADJUSTMENT --}}
      <div class="row gx-4 mt-4">
        <div class="col-12">
          <div class="card shadow-sm mb-4">
            <div class="card-header" style="background-color:#4B0082;">
              <h6 class="mb-0 text-white">Step 4: Conclude the Scope of the Governance System</h6>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm mb-0" id="step4Table">
                  <thead class="bg-gray-200">
                    <tr>
                      <th class="text-center bg-secondary text-white" style="width: 120px;">Design Factor</th>
                      <th class="text-center bg-secondary text-white" style="width: 120px;">
                        Adjustment<br>(–100 s.d. +100)
                      </th>
                      <th class="text-center bg-secondary text-white">Reason (Adjustment)</th>
                      <th class="text-center bg-black text-white">
                        Concluded Scope:<br>Governance/Management Objectives Priority
                      </th>
                      <th class="text-center bg-black text-white">Suggested Target Capability Level</th>
                      <th class="text-center bg-secondary text-white">Agreed Target Capability Level</th>
                      <th class="text-center bg-secondary text-white">Reason (Target)</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($allCodes as $idx => $code)
                      @php $refinedValue = $step3RefinedScopes[$idx] ?? 0; @endphp
                      <tr>
                        <td class="fw-bold bg-primary-subtle text-center">
                          {{ $cobitCodes[$code] ?? $code }}
                        </td>
                        <td class="px-1 py-1">
                          <input type="number" name="adjustment[{{ $code }}]"
                            class="form-control form-control-sm text-center adjust-input"
                            style="width: 80px; margin: 0 auto;" min="-100" max="100" step="1"
                            value="{{ old("adjustment.$code", $step4Adjust[$code] ?? 0) }}"
                            data-refined="{{ $refinedValue }}" data-index="{{ $idx }}">
                        </td>
                        <td class="px-1 py-1">
                          <input type="text" name="reason_adjust[{{ $code }}]" class="form-control form-control-sm"
                            placeholder="Masukkan alasan…"
                            value="{{ old("reason_adjust.$code", $step4ReasonAdj[$code] ?? '') }}">
                        </td>
                        <td class="text-center py-1 concluded-scope-cell" data-refined="{{ $refinedValue }}">0</td>
                        <td class="px-1 py-1 suggested-cell" data-code="{{ $code }}">0</td>
                        <td class="px-1 py-1 agreed-cell" data-code="{{ $code }}">0</td>
                        <td class="px-1 py-1">
                          <input type="text" name="reason_target[{{ $code }}]" class="form-control form-control-sm"
                            placeholder="Masukkan alasan…"
                            value="{{ old("reason_target.$code", $step4ReasonTgt[$code] ?? '') }}">
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
                <div class="card-footer text-end">
                  <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-save"></i> Simpan Sementara
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div> {{-- /.row --}}

      <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white py-3">
          <h6 class="mb-0">Agreed Target Capability Radar</h6>
        </div>
        <div class="card-body" style="padding: 1rem;">
          <div class="mt-2" style="max-width:600px; margin:auto;">
            <canvas id="step4Chart"></canvas>
          </div>
        </div>
      </div>


    </div> {{-- /.container --}}
  </form>

  </div>
  </div>
  </div>
  </div>


  {{-- Chart.js + Logic untuk menghitung semua v-bar (Step 2, Step 3, Step 4) --}}
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      //
      // STEP 2: Hitung “Initial Scope” v-bar untuk setiap baris di #step2Table
      //
      (function () {
        const weightInputs2 = document.querySelectorAll('.weight2-input');
        const rows2 = document.querySelectorAll('#step2Table tbody tr');

        function roundToNearest5(x) {
          return Math.round(x / 5) * 5;
        }
        function formatInteger(x) {
          return new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
          }).format(x);
        }

        function updateInitialScopeForRow(row) {
          const cells = row.querySelectorAll('.value2-cell');
          const w = Array.from(weightInputs2).map(i => parseFloat(i.value) || 0);

          let tot = 0;
          cells.forEach((cell, idx) => {
            const v = parseFloat(cell.dataset.value) || 0;
            tot += v * (w[idx] || 0);
          });
          row.querySelector('.total2-cell').textContent = formatInteger(tot);
          return tot;
        }

        function renderAllInitialScopes() {
          const allTotals = Array.from(rows2).map(r => updateInitialScopeForRow(r));
          const maxT = Math.max(...allTotals.map(v => Math.abs(v)), 1);

          rows2.forEach((row, i) => {
            const tot = allTotals[i];
            let pct = 0;
            if (maxT !== 0) {
              pct = Math.trunc((tot / maxT) * 100);
              pct = tot >= 0 ? roundToNearest5(pct) : -roundToNearest5(Math.abs(pct));
            }
            const cell = row.querySelector('.initial-scope-cell2');
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
            const centerLine = document.createElement('div');
            centerLine.style.cssText = 'position: absolute; left:50%; top:0; bottom:0; width:1px; background:#aaa;';
            container.appendChild(centerLine);

            const barWidth = Math.min(Math.abs(pct) / 2, 50);
            const bar = document.createElement('div');
            if (pct >= 0) {
              bar.style.cssText = `
                  position: absolute;
                  left: 50%;
                  top: 0;
                  height: 100%;
                  width: ${barWidth}%;
                  background-color: rgba(40, 167, 69, 0.8);
                  transition: all 0.5s ease;
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
                `;
            }
            container.appendChild(bar);

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
            label.textContent = formatInteger(pct);
            container.appendChild(label);

            cell.appendChild(container);
            cell.setAttribute('data-scope', pct);
          });
        }

        // expose to global so other blocks can call when weights change
        window.renderAllInitialScopes = function () {
          const allTotals = Array.from(rows2).map(r => updateInitialScopeForRow(r));
          const maxT = Math.max(...allTotals.map(v => Math.abs(v)), 1);

          rows2.forEach((row, i) => {
            const tot = allTotals[i];
            let pct = 0;
            if (maxT !== 0) {
              pct = Math.trunc((tot / maxT) * 100);
              pct = tot >= 0 ? roundToNearest5(pct) : -roundToNearest5(Math.abs(pct));
            }
            const cell = row.querySelector('.initial-scope-cell2');
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
            const centerLine = document.createElement('div');
            centerLine.style.cssText = 'position: absolute; left:50%; top:0; bottom:0; width:1px; background:#aaa;';
            container.appendChild(centerLine);

            const barWidth = Math.min(Math.abs(pct) / 2, 50);
            const bar = document.createElement('div');
            if (pct >= 0) {
              bar.style.cssText = `
                  position: absolute;
                  left: 50%;
                  top: 0;
                  height: 100%;
                  width: ${barWidth}%;
                  background-color: rgba(40, 167, 69, 0.8);
                  transition: all 0.5s ease;
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
                `;
            }
            container.appendChild(bar);

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
            label.textContent = formatInteger(pct);
            container.appendChild(label);

            cell.appendChild(container);
            cell.setAttribute('data-scope', pct);
          });
        }

        // initial render and bind listeners to weight inputs so editing updates charts
        renderAllInitialScopes();
        weightInputs2.forEach(i => i.addEventListener('input', () => {
          // ensure numeric value
          if (i.value === '') i.value = 0;
          window.renderAllInitialScopes();
          if (typeof window.renderAllRefinedScopes === 'function') window.renderAllRefinedScopes();
          if (typeof window.updateStep4All === 'function') window.updateStep4All();
        }));
      })();


      /// STEP 3: Hitung “Refined Scope” v‑bar untuk setiap baris di #step3Table
      (function () {
        const weightInputs3 = document.querySelectorAll('.weight3-input');
        const rows2 = document.querySelectorAll('#step2Table tbody tr');
        const rows3 = document.querySelectorAll('#step3Table tbody tr');

        function roundToNearest5(x) {
          return Math.round(x / 5) * 5;
        }
        function formatInteger(x) {
          return new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
          }).format(x);
        }

        // 1) Hitung raw combined = (DF5..DF10 total) + (Initial Scope dari Step 2)
        function updateRawCombined(row, idx) {
          // a) tot3 = sum DF5..DF10 * bobot
          const cells = row.querySelectorAll('.value3-cell');
          const w = Array.from(weightInputs3).map(i => parseFloat(i.value) || 0);
          let tot3 = 0;
          cells.forEach((cell, i) => {
            tot3 += (parseFloat(cell.dataset.value) || 0) * (w[i] || 0);
          });

          // b) initPct = data-scope dari Step 2 baris sama idx
          const initCell = rows2[idx].querySelector('.initial-scope-cell2');
          const initPct = parseFloat(initCell.getAttribute('data-scope')) || 0;

          return tot3 + initPct;
        }

        // 2) Render semua refined scopes
        function renderAllRefinedScopes() {
          // a) kumpulkan semua rawCombined untuk normalisasi
          const rawCombined = Array.from(rows3).map((row, i) => updateRawCombined(row, i));
          const maxAbs = Math.max(...rawCombined.map(v => Math.abs(v)), 1);

          // b) per baris, hitung pct, tulis Total & gambar bar
          rows3.forEach((row, i) => {
            const combined = rawCombined[i];
            // tulis nilai total (combined) di kolom Total3
            row.querySelector('.total3-cell').textContent = formatInteger(combined);

            // hitung persen relatif
            let pct = 0;
            if (maxAbs !== 0) {
              pct = Math.trunc((combined / maxAbs) * 100);
              pct = combined >= 0 ? roundToNearest5(pct) : -roundToNearest5(Math.abs(pct));
            }

            // gambar v‑bar di kolom refined-scope
            const cell = row.querySelector('.refined-scope-cell3');
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
            const centerLine = document.createElement('div');
            centerLine.style.cssText = 'position: absolute; left:50%; top:0; bottom:0; width:1px; background:#aaa;';
            container.appendChild(centerLine);

            const barWidth = Math.min(Math.abs(pct) / 2, 50);
            const bar = document.createElement('div');
            if (pct >= 0) {
              bar.style.cssText = `
             position: absolute;
             left: 50%;
             top: 0;
             height: 100%;
             width: ${barWidth}%;
             background-color: rgba(40, 167, 69, 0.8);
             transition: all 0.5s ease;
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
           `;
            }
            container.appendChild(bar);

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
            label.textContent = formatInteger(pct);
            container.appendChild(label);

            cell.appendChild(container);
            cell.setAttribute('data-scope', pct);
          });
        }

        // expose to global so other blocks can call when weights change
        window.renderAllRefinedScopes = function () {
          // a) kumpulkan semua rawCombined untuk normalisasi
          const rawCombined = Array.from(rows3).map((row, i) => updateRawCombined(row, i));
          const maxAbs = Math.max(...rawCombined.map(v => Math.abs(v)), 1);

          // b) per baris, hitung pct, tulis Total & gambar bar
          rows3.forEach((row, i) => {
            const combined = rawCombined[i];
            // tulis nilai total (combined) di kolom Total3
            row.querySelector('.total3-cell').textContent = formatInteger(combined);

            // hitung persen relatif
            let pct = 0;
            if (maxAbs !== 0) {
              pct = Math.trunc((combined / maxAbs) * 100);
              pct = combined >= 0 ? roundToNearest5(pct) : -roundToNearest5(Math.abs(pct));
            }

            // gambar v‑bar di kolom refined-scope
            const cell = row.querySelector('.refined-scope-cell3');
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
            const centerLine = document.createElement('div');
            centerLine.style.cssText = 'position: absolute; left:50%; top:0; bottom:0; width:1px; background:#aaa;';
            container.appendChild(centerLine);

            const barWidth = Math.min(Math.abs(pct) / 2, 50);
            const bar = document.createElement('div');
            if (pct >= 0) {
              bar.style.cssText = `
             position: absolute;
             left: 50%;
             top: 0;
             height: 100%;
             width: ${barWidth}%;
             background-color: rgba(40, 167, 69, 0.8);
             transition: all 0.5s ease;
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
           `;
            }
            container.appendChild(bar);

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
            label.textContent = formatInteger(pct);
            container.appendChild(label);

            cell.appendChild(container);
            cell.setAttribute('data-scope', pct);
          });
        }

        // initial render and bind weight change listeners
        renderAllRefinedScopes();
        weightInputs3.forEach(i => i.addEventListener('input', () => {
          if (i.value === '') i.value = 0;
          window.renderAllRefinedScopes();
          if (typeof window.updateStep4All === 'function') window.updateStep4All();
        }));
      })();


      //
      // STEP 4: Hitung “Concluded Scope” v-bar dan “Suggested/Agreed Target” setiap kali Adjustment berubah
      //
      (function () {
        const adjustInputs = document.querySelectorAll('.adjust-input');
        const rows4 = document.querySelectorAll('#step4Table tbody tr');

        // ── Helper ──
        function roundToNearest5(x) {
          return Math.round(x / 5) * 5;
        }
        function formatInteger(x) {
          return new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
          }).format(x);
        }

        // ── HITUNG Concluded Scope (Refined Scope + Adjustment) ──
        function updateConcludedRaw(row, idx) {
          // 1) Ambil pct dari Step 3
          const refRow = document.querySelectorAll('#step3Table tbody tr')[idx];
          const refinedPct = parseFloat(refRow.querySelector('.refined-scope-cell3').dataset.scope) || 0;

          // 2) Ambil nilai adjustment dari Step 4
          const adjustment = parseFloat(row.querySelector('.adjust-input').value) || 0;

          // 3) Jumlahkan
          return refinedPct + adjustment;
        }


        // ── RENDER v-bar “Concluded Scope” DI SETIAP ROW ──
        function renderConcludedBars() {
          rows4.forEach((row, i) => {
            const pct = roundToNearest5(updateConcludedRaw(row, i));

            const cell = row.querySelector('.concluded-scope-cell');
            cell.innerHTML = '';

            // wadah bar
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
            // garis tengah
            const centerLine = document.createElement('div');
            centerLine.style.cssText = 'position: absolute; left:50%; top:0; bottom:0; width:1px; background:#aaa;';
            container.appendChild(centerLine);

            // buat bar
            const barWidth = Math.min(Math.abs(pct) / 2, 50);
            const bar = document.createElement('div');
            if (pct >= 0) {
              bar.style.cssText = `
          position: absolute;
          left: 50%;
          top: 0;
          height: 100%;
          width: ${barWidth}%;
          background-color: rgba(40, 167, 69, 0.8);
          transition: width 0.3s ease;
        `;
            } else {
              bar.style.cssText = `
          position: absolute;
          right: 50%;
          top: 0;
          height: 100%;
          width: ${barWidth}%;
          background-color: rgba(220, 53, 69, 0.8);
          transition: width 0.3s ease;
        `;
            }
            container.appendChild(bar);

            // label di tengah
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
            label.textContent = formatInteger(pct);
            container.appendChild(label);

            cell.appendChild(container);
            cell.setAttribute('data-scope', pct);
          });
        }

        // ── HITUNG Suggested Level BERDASARKAN nilai Concluded─
        function computeSuggestedLevel(concludedPct) {
          if (concludedPct >= 75) return 4;
          if (concludedPct >= 50) return 3;
          if (concludedPct >= 25) return 2;
          return 1;
        }

        // ── RENDER horizontal bar biru/ungu di cell Suggested atau Agreed ──
        function renderSmallBar(container, level, color) {
          container.innerHTML = '';

          const wrapper = document.createElement('div');
          wrapper.style.cssText = `
        position: relative;
        height: 20px;
        width: 100%;
        background: #e9ecef;
        border: 1px solid #ddd;
        overflow: hidden;
        border-radius: 3px;
      `;

          const bar = document.createElement('div');
          bar.style.cssText = `
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: ${level * 20}%;
        background-color: ${color};
        transition: width 0.3s ease;
      `;
          wrapper.appendChild(bar);

          const label = document.createElement('div');
          label.style.cssText = `
        position: absolute;
        top: 0;
        right: 4px;
        height: 100%;
        display: flex;
        align-items: center;
        font-size: 0.8rem;
        font-weight: 500;
        color: #343a40;
        z-index: 1;
      `;
          label.textContent = level;
          wrapper.appendChild(label);

          container.appendChild(wrapper);
        }

        // ── RENDER Suggested + Agreed berdasarkan nilai Concluded─
        function renderSuggestedAgreed() {
          rows4.forEach(row => {
            const concludedPct = parseFloat(row.querySelector('.concluded-scope-cell').getAttribute('data-scope')) || 0;
            const suggestedLevel = computeSuggestedLevel(concludedPct);

            const suggestedCell = row.querySelector('.suggested-cell');
            const agreedCell = row.querySelector('.agreed-cell');

            renderSmallBar(suggestedCell, suggestedLevel, 'rgba(0, 123, 255, 0.8)');
            renderSmallBar(agreedCell, suggestedLevel, 'rgba(108, 13, 171, 0.8)');
          });
        }

        // ── FUNGSI UTAMA: panggil keduanya (Concluded + Suggested/Agreed) ──
        function updateStep4All() {
          renderConcludedBars();
          renderSuggestedAgreed();
        }
        // expose updateStep4All so other blocks can call
        window.updateStep4All = updateStep4All;

        // Pas halaman siap, panggil sekali
        updateStep4All();

        // Setiap kali user mengubah Adjustment, panggil ulang
        adjustInputs.forEach(input => {
          input.addEventListener('input', () => {
            updateStep4All();
          });
        });
      })();

      // ── STEP 4: Chart.js Radar Chart ──
      // Ambil semua label dari tabel Step 4
      // const labels = Array.from(document.querySelectorAll('#step4Table tbody tr td:first-child'))
      //   .map(td => td.textContent.trim());
      // Ambil semua data agreed-cell dari tabel Step 4
      const rows = Array.from(document.querySelectorAll('#step4Table tbody tr'));
      const labels = rows.map(r => r.querySelector('td').textContent.trim());
      const dataAgreed = rows.map(r => parseFloat(r.querySelector('.agreed-cell').textContent) || 0);

      // Maximum Capability Data (sesuai urutan dari gambar)
      const dataMaximum = [
        4, 5, 4, 4, 4, 5, 4, 5, 4, 5, 5, 4, 5, 4, 5, 5, 5, 5, 5, 5,
        4, 4, 5, 5, 4, 5, 5, 5, 5, 4, 5, 5, 5, 5, 4, 5, 5, 5, 5, 4
      ];

      const ctx = document.getElementById('step4Chart').getContext('2d');
      const step4Chart = new Chart(ctx, {
        type: 'radar',
        data: {
          labels: labels,
          datasets: [
            {
              label: 'Agreed Target Capability Level',
              data: dataAgreed,
              fill: false,
              backgroundColor: 'transparent',
              borderColor: 'rgba(54, 162, 235, 1)',  // Biru
              borderWidth: 2,
              pointRadius: 0,
              pointHoverRadius: 0
            },
            {
              label: 'Maximum Capability',
              data: dataMaximum,
              fill: false,
              backgroundColor: 'transparent',
              borderColor: 'orange',
              borderWidth: 2,
              pointRadius: 0,
              pointHoverRadius: 0
            }
          ]
        },
        options: {
          maintainAspectRatio: true,
          scales: {
            r: {
              suggestedMin: 0,
              suggestedMax: 5,
              ticks: { stepSize: 1 }
            }
          },
          plugins: {
            legend: {
              display: true, // tampilkan legend sekarang karena ada dua garis
              labels: {
                color: '#333'
              }
            },
            tooltip: {
              enabled: false
            }
          },
          elements: {
            line: {
              tension: 0
            }
          }
        }
      });




    });


    // DOMContentLoaded


  </script>

  {{-- Tambahan styling agar tampilan tabel tidak terlalu mepet --}}
  <style>
    #step2Table th,
    #step2Table td,
    #step3Table th,
    #step3Table td,
    #step4Table th,
    #step4Table td {
      vertical-align: middle;
      text-align: center;
    }

    /* Pastikan kolom “Design Factor” berwarna berbeda dengan yang lain */
    #step2Table td:first-child,
    #step3Table td:first-child,
    #step4Table td:first-child {
      background-color: #f0f8ff;
    }
  </style>
@endsection