{{-- resources/views/cobit2019/step4/step4sumaryblade.blade.php --}}
@extends('cobit2019.cobitTools')
@section('cobit-tools-content')
  @include('cobit2019.cobitPagination')

  @php
    $cobitCodes = [
      '', 'EDM01','EDM02','EDM03','EDM04','EDM05',
      'APO01','APO02','APO03','APO04','APO05','APO06','APO07','APO08','APO09','APO10','APO11','APO12','APO13','APO14',
      'BAI01','BAI02','BAI03','BAI04','BAI05','BAI06','BAI07','BAI08','BAI09','BAI10','BAI11',
      'DSS01','DSS02','DSS03','DSS04','DSS05','DSS06',
      'MEA01','MEA02','MEA03','MEA04'
    ];
    $weights2 = $step2['weights'] ?? [1, 1, 1, 1];
    $weights3 = $step3['weights'] ?? [1, 1, 1, 1, 1, 1];
    $allRelImps = $AllRelImps ?? [];
    $combinedTotals = $combinedTotals ?? [];
    $refinedScopes = $refinedScopes ?? [];
    $initialScopes = $initialScopes ?? [];
  @endphp

  <form action="{{ route('step4.store') }}" method="POST" id="step4Form">
    @csrf

    <div class="container-fluid px-4 py-3">
      
      {{-- Header Section --}}
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h4 class="text-primary fw-bold mb-1">
            <i class="bi bi-diagram-3 me-2"></i>COBIT 2019 Design Factor Analysis
          </h4>
          <p class="text-muted mb-0 small">Summary of Steps 2, 3 & 4 - Governance System Scope</p>
        </div>
        <button type="submit" class="btn btn-primary btn-lg shadow-sm">
          <i class="bi bi-save me-2"></i>Save Progress
        </button>
      </div>

      <div class="row g-4">

        {{-- STEP 2 CARD --}}
        <div class="col-lg-6">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header text-white py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="mb-0 fw-bold"><i class="bi bi-1-circle me-2"></i>Step 2: Initial Scope</h6>
                  <small class="opacity-75">Determine the Initial Scope of the Governance System</small>
                </div>
                <button type="button" class="btn btn-sm btn-light btn-sort" data-table="step2Table" data-col="initial">
                  <i class="bi bi-sort-down"></i> Sort
                </button>
              </div>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                <table class="table table-hover table-sm mb-0 align-middle" id="step2Table">
                  <thead class="sticky-top bg-light">
                    <tr>
                      <th class="text-center text-muted small fw-semibold" style="width:80px;">Code</th>
                      <th class="text-center text-muted small fw-semibold" title="Enterprise Strategy">DF1</th>
                      <th class="text-center text-muted small fw-semibold" title="Enterprise Goals">DF2</th>
                      <th class="text-center text-muted small fw-semibold" title="Risk Profile">DF3</th>
                      <th class="text-center text-muted small fw-semibold" title="IT-Related Issues">DF4</th>
                      <th class="text-center text-muted small fw-semibold bg-info bg-opacity-10" style="width:70px;">Total</th>
                      <th class="text-center text-muted small fw-semibold" style="width:140px;">Initial Scope</th>
                    </tr>
                    <tr class="bg-warning bg-opacity-25">
                      <td class="text-center small fw-bold text-warning">Weight</td>
                      @for ($i = 0; $i < 4; $i++)
                        <td class="text-center p-1">
                          <input type="number" name="weight2[{{ $i + 1 }}]" value="{{ $weights2[$i] ?? 1 }}"
                            class="form-control form-control-sm text-center border-0 bg-transparent weight2-input fw-bold" 
                            style="width:45px; margin:0 auto;" data-index="{{ $i }}">
                        </td>
                      @endfor
                      <td class="text-center">—</td>
                      <td></td>
                    </tr>
                  </thead>
                  <tbody>
                    @for ($code = 1; $code <= 40; $code++)
                      @php
                        $relImps = $allRelImps[$code] ?? [0,0,0,0,0,0,0,0,0,0];
                        $step2Data = $step2['data'][$code] ?? null;
                        $total2 = $step2Data['total_objective'] ?? 0;
                        $initialScore = $initialScopes[$code] ?? 0;
                      @endphp
                      <tr class="objective-row" data-code="{{ $code }}" data-initial-score="{{ $initialScore }}">
                        <td class="text-center fw-semibold text-primary">{{ $cobitCodes[$code] ?? '' }}</td>
                        @for ($n = 0; $n < 4; $n++)
                          @php $val = $relImps[$n] ?? 0; @endphp
                          <td class="text-center small value2-cell {{ $val < 0 ? 'text-danger' : ($val > 0 ? 'text-success' : 'text-muted') }}" data-value="{{ $val }}">
                            {{ $val != 0 ? number_format($val, 0) : '–' }}
                          </td>
                        @endfor
                        <td class="text-center fw-bold bg-info bg-opacity-10 total2-cell" data-total="{{ $total2 }}">
                          {{ number_format($total2, 0) }}
                        </td>
                        <td class="text-center initial-scope-cell2" data-score="{{ $initialScore }}"></td>
                      </tr>
                    @endfor
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        {{-- STEP 3 CARD --}}
        <div class="col-lg-6">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header text-white py-3" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="mb-0 fw-bold"><i class="bi bi-2-circle me-2"></i>Step 3: Refined Scope</h6>
                  <small class="opacity-75">Refine the Scope of the Governance System</small>
                </div>
                <button type="button" class="btn btn-sm btn-light btn-sort" data-table="step3Table" data-col="refined">
                  <i class="bi bi-sort-down"></i> Sort
                </button>
              </div>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                <table class="table table-hover table-sm mb-0 align-middle" id="step3Table">
                  <thead class="sticky-top bg-light">
                    <tr>
                      <th class="text-center text-muted small fw-semibold" style="width:80px;">Code</th>
                      <th class="text-center text-muted small fw-semibold" title="Threat Landscape">DF5</th>
                      <th class="text-center text-muted small fw-semibold" title="Compliance Requirements">DF6</th>
                      <th class="text-center text-muted small fw-semibold" title="Role of IT">DF7</th>
                      <th class="text-center text-muted small fw-semibold" title="Sourcing Model">DF8</th>
                      <th class="text-center text-muted small fw-semibold" title="IT Implementation">DF9</th>
                      <th class="text-center text-muted small fw-semibold" title="Technology Adoption">DF10</th>
                      <th class="text-center text-muted small fw-semibold bg-info bg-opacity-10" style="width:70px;">Total</th>
                      <th class="text-center text-muted small fw-semibold" style="width:140px;">Refined Scope</th>
                    </tr>
                    <tr class="bg-warning bg-opacity-25">
                      <td class="text-center small fw-bold text-warning">Weight</td>
                      @for ($i = 0; $i < 6; $i++)
                        <td class="text-center p-1">
                          <input type="number" name="weight3[{{ $i + 1 }}]" value="{{ $weights3[$i] ?? 1 }}"
                            class="form-control form-control-sm text-center border-0 bg-transparent weight3-input fw-bold" 
                            style="width:45px; margin:0 auto;" data-index="{{ $i }}">
                        </td>
                      @endfor
                      <td class="text-center">—</td>
                      <td></td>
                    </tr>
                  </thead>
                  <tbody>
                    @for ($code = 1; $code <= 40; $code++)
                      @php
                        $relImps = $allRelImps[$code] ?? [0,0,0,0,0,0,0,0,0,0];
                        $step3Data = $step3['data'][$code] ?? null;
                        $totalCombined = $combinedTotals[$code] ?? 0;
                        $refinedScore = $refinedScopes[$code] ?? 0;
                      @endphp
                      <tr class="objective-row" data-code="{{ $code }}" data-refined-score="{{ $refinedScore }}">
                        <td class="text-center fw-semibold text-primary">{{ $cobitCodes[$code] ?? '' }}</td>
                        @for ($n = 4; $n < 10; $n++)
                          @php $val = $relImps[$n] ?? 0; @endphp
                          <td class="text-center small value3-cell {{ $val < 0 ? 'text-danger' : ($val > 0 ? 'text-success' : 'text-muted') }}" data-value="{{ $val }}">
                            {{ $val != 0 ? number_format($val, 0) : '–' }}
                          </td>
                        @endfor
                        <td class="text-center fw-bold bg-info bg-opacity-10 total3-cell" data-total="{{ $totalCombined }}">
                          {{ number_format($totalCombined, 0) }}
                        </td>
                        <td class="text-center refined-scope-cell3" data-score="{{ $refinedScore }}"></td>
                      </tr>
                    @endfor
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

      </div>

      {{-- STEP 4 SECTION --}}
      <div class="card border-0 shadow-sm mt-4">
        <div class="card-header text-white py-3" style="background: linear-gradient(135deg, #667eea 0%, #f093fb 100%);">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h6 class="mb-0 fw-bold"><i class="bi bi-3-circle me-2"></i>Step 4: Concluded Scope</h6>
              <small class="opacity-75">Conclude the Scope of the Governance System</small>
            </div>
            <div class="btn-group">
              <button type="button" class="btn btn-sm btn-light btn-sort" data-table="step4Table" data-col="concluded">
                <i class="bi bi-sort-down"></i> Sort by Priority
              </button>
              <button type="button" class="btn btn-sm btn-outline-light" id="resetSort">
                <i class="bi bi-arrow-counterclockwise"></i> Reset
              </button>
            </div>
          </div>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-sm mb-0 align-middle" id="step4Table">
              <thead class="bg-light sticky-top">
                <tr>
                  <th class="text-center text-muted small fw-semibold" style="width:80px;">Code</th>
                  <th class="text-center text-muted small fw-semibold" style="width:100px;">Adjustment</th>
                  <th class="text-muted small fw-semibold" style="min-width:180px;">Reason (Adjustment)</th>
                  <th class="text-center small fw-semibold text-white" style="width:150px; background:#4B0082;">
                    Concluded Priority
                  </th>
                  <th class="text-center small fw-semibold text-white" style="width:120px; background:#4B0082;">
                    Suggested Level
                  </th>
                  <th class="text-center text-muted small fw-semibold" style="width:120px;">Agreed Level</th>
                  <th class="text-muted small fw-semibold" style="min-width:180px;">Reason (Target)</th>
                </tr>
              </thead>
              <tbody>
                @for ($code = 1; $code <= 40; $code++)
                  @php $refinedScore = $refinedScopes[$code] ?? 0; @endphp
                  <tr class="objective-row" data-code="{{ $code }}" data-refined="{{ $refinedScore }}">
                    <td class="text-center fw-semibold text-primary">{{ $cobitCodes[$code] ?? '' }}</td>
                    <td class="text-center p-1">
                      <input type="number" name="adjustment[{{ $code }}]"
                        class="form-control form-control-sm text-center adjust-input mx-auto"
                        style="width:70px;" min="-100" max="100" step="1"
                        value="{{ old("adjustment.$code", $step4Adjust[$code] ?? 0) }}"
                        data-refined="{{ $refinedScore }}">
                    </td>
                    <td class="p-1">
                      <input type="text" name="reason_adjust[{{ $code }}]" 
                        class="form-control form-control-sm border-0 bg-light"
                        placeholder="Reason..."
                        value="{{ old("reason_adjust.$code", $step4ReasonAdj[$code] ?? '') }}">
                    </td>
                    <td class="text-center concluded-scope-cell" data-refined="{{ $refinedScore }}"></td>
                    <td class="text-center suggested-cell"></td>
                    <td class="text-center agreed-cell"></td>
                    <td class="p-1">
                      <input type="text" name="reason_target[{{ $code }}]" 
                        class="form-control form-control-sm border-0 bg-light"
                        placeholder="Reason..."
                        value="{{ old("reason_target.$code", $step4ReasonTgt[$code] ?? '') }}">
                    </td>
                  </tr>
                @endfor
              </tbody>
            </table>
          </div>
        </div>
      </div>

      {{-- RADAR CHART --}}
      <div class="card border-0 shadow-sm mt-4">
        <div class="card-header text-white py-3" style="background: linear-gradient(135deg, #ff6b6b 0%, #feca57 100%);">
          <h6 class="mb-0 fw-bold"><i class="bi bi-pie-chart me-2"></i>Agreed Target Capability Radar</h6>
        </div>
        <div class="card-body">
          <div style="max-width:550px; margin:auto;">
            <canvas id="step4Chart"></canvas>
          </div>
        </div>
      </div>

    </div>
  </form>

  </div></div></div></div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // ─── HELPERS ───
      const roundTo5 = x => Math.round(x / 5) * 5;
      const fmt = x => new Intl.NumberFormat('en-US', { maximumFractionDigits: 0 }).format(x);

      function createVBar(pct, compact = false) {
        const w = compact ? 120 : 140;
        const h = compact ? 18 : 20;
        const container = document.createElement('div');
        container.style.cssText = `position:relative;height:${h}px;width:${w}px;background:#f1f3f4;border-radius:4px;margin:0 auto;overflow:hidden;`;
        
        const center = document.createElement('div');
        center.style.cssText = 'position:absolute;left:50%;top:0;bottom:0;width:1px;background:#dee2e6;';
        container.appendChild(center);

        const barWidth = Math.min(Math.abs(pct) / 2, 50);
        const bar = document.createElement('div');
        const isPos = pct >= 0;
        bar.style.cssText = `position:absolute;${isPos?'left:50%':'right:50%'};top:2px;bottom:2px;width:${barWidth}%;background:${isPos?'#28a745':'#dc3545'};border-radius:2px;transition:width 0.3s;`;
        container.appendChild(bar);

        const lbl = document.createElement('span');
        lbl.style.cssText = 'position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;color:#212529;';
        lbl.textContent = fmt(pct);
        container.appendChild(lbl);
        return container;
      }

      function createLevelBar(level, color) {
        const wrapper = document.createElement('div');
        wrapper.style.cssText = 'position:relative;height:20px;width:90px;background:#e9ecef;border-radius:4px;overflow:hidden;margin:0 auto;';
        const bar = document.createElement('div');
        bar.style.cssText = `position:absolute;left:0;top:2px;bottom:2px;width:${level*20}%;background:${color};border-radius:2px;transition:width 0.3s;`;
        wrapper.appendChild(bar);
        const lbl = document.createElement('span');
        lbl.style.cssText = 'position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;color:#212529;';
        lbl.textContent = level;
        wrapper.appendChild(lbl);
        return wrapper;
      }

      const getSuggestedLevel = pct => pct >= 75 ? 4 : pct >= 50 ? 3 : pct >= 25 ? 2 : 1;

      // ─── RENDER FUNCTIONS ───
      function renderStep2() {
        const rows = document.querySelectorAll('#step2Table tbody tr.objective-row');
        const totals = Array.from(rows).map(r => parseFloat(r.querySelector('.total2-cell').dataset.total) || 0);
        const maxAbs = Math.max(...totals.map(Math.abs), 1);
        rows.forEach((row, i) => {
          let pct = maxAbs ? Math.trunc((totals[i] / maxAbs) * 100) : 0;
          pct = totals[i] >= 0 ? roundTo5(pct) : -roundTo5(Math.abs(pct));
          const cell = row.querySelector('.initial-scope-cell2');
          cell.innerHTML = '';
          cell.appendChild(createVBar(pct, true));
          cell.dataset.scope = pct;
        });
      }

      function renderStep3() {
        const rows = document.querySelectorAll('#step3Table tbody tr.objective-row');
        const totals = Array.from(rows).map(r => parseFloat(r.querySelector('.total3-cell').dataset.total) || 0);
        const maxAbs = Math.max(...totals.map(Math.abs), 1);
        rows.forEach((row, i) => {
          let pct = maxAbs ? Math.trunc((totals[i] / maxAbs) * 100) : 0;
          pct = totals[i] >= 0 ? roundTo5(pct) : -roundTo5(Math.abs(pct));
          const cell = row.querySelector('.refined-scope-cell3');
          cell.innerHTML = '';
          cell.appendChild(createVBar(pct, true));
          cell.dataset.scope = pct;
        });
      }

      function renderStep4() {
        const rows4 = document.querySelectorAll('#step4Table tbody tr.objective-row');
        const rows3 = document.querySelectorAll('#step3Table tbody tr.objective-row');
        rows4.forEach((row, i) => {
          const refinedPct = parseFloat(rows3[i]?.querySelector('.refined-scope-cell3')?.dataset.scope || 0);
          const adj = parseFloat(row.querySelector('.adjust-input').value) || 0;
          const concluded = roundTo5(refinedPct + adj);
          
          const cc = row.querySelector('.concluded-scope-cell');
          cc.innerHTML = '';
          cc.appendChild(createVBar(concluded));
          cc.dataset.scope = concluded;

          const lvl = getSuggestedLevel(concluded);
          const sc = row.querySelector('.suggested-cell');
          sc.innerHTML = '';
          sc.appendChild(createLevelBar(lvl, '#007bff'));
          
          const ac = row.querySelector('.agreed-cell');
          ac.innerHTML = '';
          ac.appendChild(createLevelBar(lvl, '#6f42c1'));
        });
        updateChart();
      }

      // ─── SORTING ───
      const sortState = { step2Table: 'none', step3Table: 'none', step4Table: 'none' };
      
      function sortTable(tableId, colType) {
        const table = document.getElementById(tableId);
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr.objective-row'));
        
        // Toggle sort direction
        sortState[tableId] = sortState[tableId] === 'desc' ? 'asc' : 'desc';
        const dir = sortState[tableId] === 'desc' ? -1 : 1;

        rows.sort((a, b) => {
          let valA, valB;
          if (colType === 'initial') {
            valA = parseFloat(a.querySelector('.initial-scope-cell2')?.dataset.scope || 0);
            valB = parseFloat(b.querySelector('.initial-scope-cell2')?.dataset.scope || 0);
          } else if (colType === 'refined') {
            valA = parseFloat(a.querySelector('.refined-scope-cell3')?.dataset.scope || 0);
            valB = parseFloat(b.querySelector('.refined-scope-cell3')?.dataset.scope || 0);
          } else {
            valA = parseFloat(a.querySelector('.concluded-scope-cell')?.dataset.scope || 0);
            valB = parseFloat(b.querySelector('.concluded-scope-cell')?.dataset.scope || 0);
          }
          return (valB - valA) * dir;
        });

        rows.forEach(row => tbody.appendChild(row));
        
        // Update button icon
        const btn = document.querySelector(`[data-table="${tableId}"] i`);
        if (btn) btn.className = sortState[tableId] === 'desc' ? 'bi bi-sort-down' : 'bi bi-sort-up';
      }

      function resetSort() {
        ['step2Table', 'step3Table', 'step4Table'].forEach(tableId => {
          const table = document.getElementById(tableId);
          const tbody = table.querySelector('tbody');
          const rows = Array.from(tbody.querySelectorAll('tr.objective-row'));
          rows.sort((a, b) => parseInt(a.dataset.code) - parseInt(b.dataset.code));
          rows.forEach(row => tbody.appendChild(row));
          sortState[tableId] = 'none';
        });
        document.querySelectorAll('.btn-sort i').forEach(i => i.className = 'bi bi-sort-down');
      }

      // ─── CHART ───
      let chart;
      function updateChart() {
        const rows = Array.from(document.querySelectorAll('#step4Table tbody tr.objective-row'));
        const labels = rows.map(r => r.querySelector('td').textContent.trim());
        const data = rows.map(r => {
          const ac = r.querySelector('.agreed-cell span');
          return ac ? parseInt(ac.textContent) || 0 : 0;
        });
        const maxCap = [4,5,4,4,4,5,4,5,4,5,5,4,5,4,5,5,5,5,5,5,4,4,5,5,4,5,5,5,5,4,5,5,5,5,4,5,5,5,5,4];

        if (chart) {
          chart.data.labels = labels;
          chart.data.datasets[0].data = data;
          chart.update();
        } else {
          chart = new Chart(document.getElementById('step4Chart').getContext('2d'), {
            type: 'radar',
            data: {
              labels,
              datasets: [
                { label: 'Agreed Level', data, fill: false, borderColor: '#007bff', borderWidth: 2, pointRadius: 0 },
                { label: 'Maximum', data: maxCap, fill: false, borderColor: '#ffc107', borderWidth: 2, pointRadius: 0 }
              ]
            },
            options: {
              maintainAspectRatio: true,
              scales: { r: { suggestedMin: 0, suggestedMax: 5, ticks: { stepSize: 1 } } },
              plugins: { legend: { display: true }, tooltip: { enabled: false } }
            }
          });
        }
      }

      // ─── INIT ───
      renderStep2();
      renderStep3();
      renderStep4();

      document.querySelectorAll('.adjust-input').forEach(i => i.addEventListener('input', renderStep4));
      document.querySelectorAll('.btn-sort').forEach(btn => {
        btn.addEventListener('click', () => sortTable(btn.dataset.table, btn.dataset.col));
      });
      document.getElementById('resetSort')?.addEventListener('click', resetSort);
    });
  </script>

  <style>
    .sticky-top { position: sticky; top: 0; z-index: 10; }
    #step2Table, #step3Table, #step4Table { font-size: 13px; }
    #step2Table th, #step3Table th, #step4Table th { border-bottom: 2px solid #dee2e6; }
    #step2Table td, #step3Table td, #step4Table td { border-color: #f1f3f4; }
    .objective-row:hover { background-color: #f8f9fa !important; }
    .form-control:focus { box-shadow: 0 0 0 2px rgba(102,126,234,0.25); }
    .btn-sort { font-size: 12px; padding: 4px 10px; }
  </style>
@endsection