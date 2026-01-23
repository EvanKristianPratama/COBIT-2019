@extends('cobit2019.cobitTools')
@section('cobit-tools-content')
  @include('cobit2019.cobitPagination')

  <form action="{{ route('step2.store') }}" method="POST" id="step2Form">
    @csrf
    <input type="hidden" name="weights" id="weightsInput">
    <input type="hidden" name="relative_importances" id="relative_importancesInput">
    <input type="hidden" name="totals" id="totalsInput">
    <input type="hidden" name="initial_scope_scores" id="initialScopeScoresInput">

    @php
      $cobitCodes = [
        '', 'EDM01','EDM02','EDM03','EDM04','EDM05',
        'APO01','APO02','APO03','APO04','APO05','APO06','APO07','APO08','APO09','APO10','APO11','APO12','APO13','APO14',
        'BAI01','BAI02','BAI03','BAI04','BAI05','BAI06','BAI07','BAI08','BAI09','BAI10','BAI11',
        'DSS01','DSS02','DSS03','DSS04','DSS05','DSS06',
        'MEA01','MEA02','MEA03','MEA04'
      ];
      $allCodes = collect(range(1, 40));
      $weights = $savedWeights ?? session('step2.weights', [1, 1, 1, 1]);
    @endphp

    <div class="container-fluid px-4 py-3">
      
      {{-- Header Section --}}
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h4 class="fw-bold mb-1" style="color: #667eea;">
            <i class="bi bi-1-circle-fill me-2"></i>Step 2: Initial Scope
          </h4>
          <p class="text-muted mb-0 small">Determine the Initial Scope of the Governance System</p>
        </div>
        <div class="d-flex align-items-center gap-3">
          <span id="saveStatus" class="small text-muted"></span>
          <button type="button" id="saveButton" class="btn btn-primary shadow-sm">
            <i class="bi bi-save me-2"></i>Save
          </button>
        </div>
      </div>

      {{-- Main Card --}}
      <div class="card border-0 shadow-sm">
        <div class="card-header text-white py-3" style="background: var(--cobit-gradient);">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h6 class="mb-0 fw-bold"><i class="bi bi-table me-2"></i>Relative Importance Matrix</h6>
              <small class="opacity-75">Design Factor ID: {{ $assessment->assessment_id }}</small>
            </div>
            <button type="button" class="btn btn-sm btn-light" id="sortBtn">
              <i class="bi bi-sort-down"></i> Sort by Score
            </button>
          </div>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive" style="max-height: 550px; overflow-y: auto;">
            <table class="table table-hover table-sm mb-0 align-middle" id="matrixTable">
              <thead class="sticky-top bg-light">
                <tr>
                  <th class="text-center text-muted small fw-semibold" style="width:80px;">GAMO</th>
                  <th class="text-center text-muted small fw-semibold" title="Enterprise Strategy">DF1</th>
                  <th class="text-center text-muted small fw-semibold" title="Enterprise Goals">DF2</th>
                  <th class="text-center text-muted small fw-semibold" title="Risk Profile">DF3</th>
                  <th class="text-center text-muted small fw-semibold" title="IT-Related Issues">DF4</th>
                  <th class="text-center text-muted small fw-semibold bg-info bg-opacity-10" style="width:70px;">Total</th>
                  <th class="text-center text-muted small fw-semibold" style="width:150px;">Initial Scope Score</th>
                </tr>
                <tr class="bg-warning bg-opacity-25">
                  <td class="text-center small fw-bold text-warning">Weight</td>
                  @for ($i = 0; $i < 4; $i++)
                    <td class="text-center p-1">
                      <input type="number" name="weight[{{ $i + 1 }}]" value="{{ $weights[$i] ?? 1 }}"
                        class="form-control form-control-sm text-center border-0 bg-transparent weight-input fw-bold" 
                        style="width:50px; margin:0 auto;" data-index="{{ $i }}">
                    </td>
                  @endfor
                  <td class="text-center">—</td>
                  <td></td>
                </tr>
              </thead>
              <tbody>
                @foreach ($allCodes as $code)
                  @php
                    $total = 0;
                    $values = [];
                    for ($n = 1; $n <= 4; $n++) {
                      $rec = $assessment->{'df' . $n . 'RelativeImportances'}->first();
                      $col = "r_df{$n}_{$code}";
                      $val = ($rec && isset($rec->$col)) ? $rec->$col : 0;
                      $values[] = $val;
                      $total += $val;
                    }
                  @endphp
                  <tr class="objective-row" data-code="{{ $code }}">
                    <td class="text-center fw-semibold text-primary">{{ $cobitCodes[$code] ?? '' }}</td>
                    @foreach ($values as $i => $val)
                      <td class="text-center small value-cell {{ $val < 0 ? 'text-danger' : ($val > 0 ? 'text-success' : 'text-muted') }}" data-value="{{ $val }}">
                        {{ $val != 0 ? number_format($val, 0) : '–' }}
                      </td>
                    @endforeach
                    <td class="text-center fw-bold bg-info bg-opacity-10 total-cell">{{ number_format($total, 0) }}</td>
                    <td class="text-center initial-scope-cell"></td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>

      {{-- Chart Section --}}
      <div class="card border-0 shadow-sm mt-4">
        <div class="card-header text-white py-3" style="background: linear-gradient(135deg, #ff6b6b 0%, #feca57 100%);">
          <h6 class="mb-0 fw-bold"><i class="bi bi-bar-chart me-2"></i>Initial Scope Distribution</h6>
        </div>
        <div class="card-body">
          <div style="height: 400px;">
            <canvas id="initialScopeChart"></canvas>
          </div>
        </div>
      </div>

    </div>
  </form>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const weightInputs = document.querySelectorAll('.weight-input');
      const rows = document.querySelectorAll('#matrixTable tbody tr.objective-row');
      let sortAsc = false;

      const roundTo5 = x => Math.round(x / 5) * 5;
      const fmt = x => new Intl.NumberFormat('en-US', { maximumFractionDigits: 0 }).format(x);

      function createVBar(pct) {
        const container = document.createElement('div');
        container.style.cssText = 'position:relative;height:18px;width:130px;background:#f1f3f4;border-radius:4px;margin:0 auto;overflow:hidden;';
        
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

      function calculateAll() {
        const weights = Array.from(weightInputs).map(i => parseFloat(i.value) || 0);
        const totals = [];
        const relImps = [];
        const codes = [];

        rows.forEach(row => {
          const cells = row.querySelectorAll('.value-cell');
          let total = 0;
          const rowVals = [];
          cells.forEach((cell, i) => {
            const v = parseFloat(cell.dataset.value) || 0;
            rowVals.push(v);
            total += v * weights[i];
          });
          relImps.push(rowVals);
          totals.push(total);
          codes.push(row.querySelector('td').textContent.trim());
          row.querySelector('.total-cell').textContent = fmt(total);
        });

        const maxT = Math.max(...totals.map(Math.abs), 1);
        const scopes = totals.map((t, i) => {
          let pct = maxT ? Math.trunc((t / maxT) * 100) : 0;
          pct = t >= 0 ? roundTo5(pct) : -roundTo5(Math.abs(pct));
          const cell = rows[i].querySelector('.initial-scope-cell');
          cell.innerHTML = '';
          cell.appendChild(createVBar(pct));
          cell.dataset.scope = pct;
          return pct;
        });

        // Save to hidden inputs
        const totalsByCode = {};
        codes.forEach((c, i) => totalsByCode[c] = totals[i]);
        document.getElementById('weightsInput').value = JSON.stringify(weights);
        document.getElementById('relative_importancesInput').value = JSON.stringify(relImps);
        document.getElementById('totalsInput').value = JSON.stringify(totalsByCode);
        document.getElementById('initialScopeScoresInput').value = JSON.stringify(scopes);

        renderChart(codes, scopes);
      }

      function renderChart(labels, scopes) {
        const ctx = document.getElementById('initialScopeChart').getContext('2d');
        if (window.scopeChart) window.scopeChart.destroy();
        window.scopeChart = new Chart(ctx, {
          type: 'bar',
          data: {
            labels,
            datasets: [{
              label: 'Initial Scope',
              data: scopes,
              backgroundColor: scopes.map(s => s >= 0 ? '#28a745' : '#dc3545')
            }]
          },
          options: {
            indexAxis: 'y',
            maintainAspectRatio: false,
            scales: {
              x: { beginAtZero: true },
              y: { ticks: { autoSkip: false, font: { size: 10 } } }
            },
            plugins: { legend: { display: false } }
          }
        });
      }

      function sortTable() {
        const tbody = document.querySelector('#matrixTable tbody');
        const rowsArr = Array.from(rows);
        sortAsc = !sortAsc;
        rowsArr.sort((a, b) => {
          const va = parseFloat(a.querySelector('.initial-scope-cell').dataset.scope) || 0;
          const vb = parseFloat(b.querySelector('.initial-scope-cell').dataset.scope) || 0;
          return sortAsc ? va - vb : vb - va;
        });
        rowsArr.forEach(r => tbody.appendChild(r));
        document.querySelector('#sortBtn i').className = sortAsc ? 'bi bi-sort-up' : 'bi bi-sort-down';
      }

      // Auto-save
      let saveTimer;
      function autoSave() {
        const form = document.getElementById('step2Form');
        document.getElementById('saveStatus').textContent = 'Saving...';
        fetch(form.action, {
          method: 'POST',
          body: new FormData(form),
          headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(() => {
          document.getElementById('saveStatus').textContent = 'Saved ✓';
          setTimeout(() => document.getElementById('saveStatus').textContent = '', 3000);
        })
        .catch(() => document.getElementById('saveStatus').textContent = 'Error!');
      }

      weightInputs.forEach(i => i.addEventListener('input', () => {
        calculateAll();
        clearTimeout(saveTimer);
        saveTimer = setTimeout(autoSave, 1000);
      }));

      document.getElementById('sortBtn').addEventListener('click', sortTable);
      document.getElementById('saveButton').addEventListener('click', () => {
        calculateAll();
        document.getElementById('step2Form').submit();
      });

      calculateAll();
    });
  </script>

  <style>
    .sticky-top { position: sticky; top: 0; z-index: 10; }
    #matrixTable { font-size: 13px; }
    #matrixTable th { border-bottom: 2px solid #dee2e6; }
    #matrixTable td { border-color: #f1f3f4; }
    .objective-row:hover { background-color: #f8f9fa !important; }
    .form-control:focus { box-shadow: 0 0 0 2px rgba(102,126,234,0.25); }
  </style>
@endsection