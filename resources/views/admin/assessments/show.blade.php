@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Main Card --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Detail Assessment {{ $assessment->assessment_id }}</h3>
                <div>
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-success dropdown-toggle px-3 py-2" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-edit me-2"></i>Fill Design Factors
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('df1.form', ['id' => 1, 'assessment' => $assessment->assessment_id]) }}">Design Factor 1</a></li>
                                <li><a class="dropdown-item" href="{{ route('df2.form', ['id' => 2, 'assessment' => $assessment->assessment_id]) }}">Design Factor 2</a></li>
                                <li><a class="dropdown-item" href="{{ route('df3.form', ['id' => 3, 'assessment' => $assessment->assessment_id]) }}">Design Factor 3</a></li>
                                <li><a class="dropdown-item" href="{{ route('df4.form', ['id' => 4, 'assessment' => $assessment->assessment_id]) }}">Design Factor 4</a></li>
                                <li><a class="dropdown-item" href="{{ route('df5.form', ['id' => 5, 'assessment' => $assessment->assessment_id]) }}">Design Factor 5</a></li>
                                <li><a class="dropdown-item" href="{{ route('df6.form', ['id' => 6, 'assessment' => $assessment->assessment_id]) }}">Design Factor 6</a></li>
                                <li><a class="dropdown-item" href="{{ route('df7.form', ['id' => 7, 'assessment' => $assessment->assessment_id]) }}">Design Factor 7</a></li>
                                <li><a class="dropdown-item" href="{{ route('df8.form', ['id' => 8, 'assessment' => $assessment->assessment_id]) }}">Design Factor 8</a></li>
                                <li><a class="dropdown-item" href="{{ route('df9.form', ['id' => 9, 'assessment' => $assessment->assessment_id]) }}">Design Factor 9</a></li>
                                <li><a class="dropdown-item" href="{{ route('df10.form', ['id' => 10, 'assessment' => $assessment->assessment_id]) }}">Design Factor 10</a></li>
                            </ul>
                        </div>
                    <a href="{{ route('admin.assessments.index') }}" class="btn btn-light px-3 py-2">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Kode:</strong> {{ $assessment->kode_assessment }}</p>
                    <p><strong>Instansi:</strong> {{ $assessment->instansi }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white py-3">
            <h6 class="m-0 font-weight-bold">Filter Data</h6>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-md-4">
                    <input type="number" id="filterUserId" class="form-control" placeholder="Masukkan User ID">
                </div>
                <div class="col-md-8">
                    <button class="btn btn-primary px-3 py-2 me-2" id="applyFilter">
                        <i class="fas fa-filter me-2"></i>Filter
                    </button>
                    <button class="btn btn-outline-secondary px-3 py-2" id="clearFilter">
                        <i class="fas fa-sync me-2"></i>Clear
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Toggle Buttons --}}
    <div class="btn-group mb-4 shadow-sm w-100" role="group">
        <button type="button" class="btn btn-outline-primary px-3 py-2" id="btn-df">Inputs</button>
        <button type="button" class="btn btn-outline-secondary px-3 py-2" id="btn-scores">Scores</button>
        <button type="button" class="btn btn-outline-success px-3 py-2" id="btn-relimp">RelImp</button>
        <button type="button" class="btn btn-outline-dark px-3 py-2" id="btn-all">All</button>
    </div>

    @php
        use Illuminate\Support\Str;
        // `userIds` is prepared in the controller (sanitized & admin-excluded). Ensure it's a Collection here.
        if (!isset($userIds) || !($userIds instanceof \Illuminate\Support\Collection)) {
            $userIds = collect();
        }
    @endphp

    {{-- SECTION: Design Factor Inputs --}}
    <div id="section-df">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0">Design Factor Inputs</h5>
            </div>
            <div class="card-body">
                @for($n=1; $n<=10; $n++)
                <div class="mb-5">
                    <h6 class="fw-bold mb-3">DF{{ $n }}</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover table-sm" data-df="{{ $n }}">
                            <thead>
                                <tr class="bg-primary text-white">
                                    <th style="width: 150px;">Responden</th>
                                    @foreach($userIds as $uid)
                                    <th class="user-col col-u-{{ $uid }} text-center" style="width: 120px;">
                                        <div class="fw-bold">{{ $uid }}</div>
                                        <small class="fw-normal">
                                            {{ explode(' ', $users[$uid] ?? 'Unknown')[0] }}
                                        </small>
                                    </th>
                                    @endforeach
                                    <th class="text-center bg-warning text-dark" style="width: 140px;">Statistik</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $dfRecords = $assessment->{'df'.$n};
                                    $inputCols = [];
                                    if ($first = $dfRecords->first()) {
                                        foreach ($first->getAttributes() as $key => $value) {
                                            if (Str::startsWith($key, 'input') && Str::endsWith($key, "df{$n}")) {
                                                $inputCols[] = $key;
                                            }
                                        }
                                    }
                                @endphp

                                @php
                                    // custom labels untuk DF1 (index dimulai 0). Ubah teks sesuai kebutuhan.
                                    $customDfLabels = [
                                        1 => [
                                            'Growth/Acquisition',
                                            'Innovation/Differentiation',
                                            'Cost Leadership',
                                            'Client Service/Stability',
                                            'Lainnya' // ubah atau hapus jika tidak diperlukan
                                        ]
                                    ];
                                @endphp

                                @foreach($inputCols as $i => $col)
                                <tr>
                                    @php
                                        // jika DF1 dan ada custom label untuk index ini, pakai itu.
                                        if ($n === 1 && isset($customDfLabels[1][$i])) {
                                            $label = $customDfLabels[1][$i];
                                        } else {
                                            // fallback ke label lama (menghapus 'df{n}' dari nama kolom)
                                            $label = str_replace("df{$n}", '', $col);
                                        }
                                    @endphp

                                    <td class="fw-bold">{{ $label }}</td>
                                    @foreach($userIds as $uid)
                                        @php $rec = $dfRecords->firstWhere('id', $uid); @endphp
                                        <td class="user-col col-u-{{ $uid }} text-center">{{ $rec->{$col} ?? '-' }}</td>
                                    @endforeach
                                    {{-- statistik grafis (chart) --}}
                                    <td class="suggestion text-center">
                                        <canvas id="chart-df{{ $n }}-col{{ $i }}" style="width:160px;height:80px;"></canvas>
                                    </td>
                                </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>
                @endfor
            </div>
        </div>
    </div>

    {{-- SECTION: Scores --}}
    <div id="section-scores" style="display:none;">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info text-white py-3">
                <h5 class="mb-0">Design Factor Scores</h5>
            </div>
            <div class="card-body">
                @for($n=1; $n<=10; $n++)
                <div class="mb-5">
                    <h6 class="fw-bold mb-3">DF{{ $n }}</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover table-sm">
                            <thead>
                                <tr class="bg-info text-white">
                                    <th class="bg-info text-white" style="width: 150px;">User</th>
                                    @foreach($userIds as $uid)
                                    <th class="user-col col-u-{{ $uid }} text-center bg-info text-white" style="width: 120px;">
                                        <div class="fw-bold">{{ $uid }}</div>
                                        <small class="fw-normal">
                                            {{ explode(' ', $users[$uid] ?? 'Unknown')[0] }}
                                        </small>
                                    </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @php $scores = $assessment->{'df'.$n.'Scores'}->first() ?? collect(); @endphp
                                @foreach($scores->toArray() as $col => $val)
                                    @if(str_starts_with($col, 's_df'.$n.'_'))
                                    <tr>
                                        <td class="fw-bold">{{ $col }}</td>
                                        @foreach($userIds as $uid)
                                            @php $rec = $assessment->{'df'.$n.'Scores'}->firstWhere('id',$uid); @endphp
                                            <td class="user-col col-u-{{ $uid }} text-center">{{ $rec ? number_format($rec->$col, 2) : '-' }}</td>
                                        @endforeach
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endfor
            </div>
        </div>
    </div>

    {{-- SECTION: Relative Importance --}}
    <div id="section-relimp" style="display:none;">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white py-3">
                <h5 class="mb-0">Relative Importance</h5>
            </div>
            <div class="card-body">
                @for($n=1; $n<=10; $n++)
                <div class="mb-5">
                    <h6 class="fw-bold mb-3">DF{{ $n }}</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover table-sm">
                            <thead>
                                <tr class="bg-success text-white">
                                    <th class="bg-success text-white" style="width: 150px;">User</th>
                                    @foreach($userIds as $uid)
                                    <th class="user-col col-u-{{ $uid }} text-center bg-success text-white" style="width: 120px;">
                                        <div class="fw-bold">{{ $uid }}</div>
                                        <small class="fw-normal">
                                            {{ explode(' ', $users[$uid] ?? 'Unknown')[0] }}
                                        </small>
                                    </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @php $ris = $assessment->{'df'.$n.'RelativeImportances'}->first() ?? collect(); @endphp
                                @foreach($ris->toArray() as $col => $val)
                                    @if(str_starts_with($col, 'r_df'.$n.'_'))
                                    <tr>
                                        <td class="fw-bold">{{ $col }}</td>
                                        @foreach($userIds as $uid)
                                            @php $rec = $assessment->{'df'.$n.'RelativeImportances'}->firstWhere('id',$uid); @endphp
                                            <td class="user-col col-u-{{ $uid }} text-center">{{ $rec ? number_format($rec->$col, 2) : '-' }}</td>
                                        @endforeach
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endfor
            </div>
        </div>
    </div>
</div>


<script>
  // Toggle sections
  const sections = {
    'btn-df': ['section-df'],
    'btn-scores': ['section-scores'],
    'btn-relimp': ['section-relimp'],
    'btn-all': ['section-df','section-scores','section-relimp']
  };
  Object.entries(sections).forEach(([btn, secs]) => {
    document.getElementById(btn).addEventListener('click', () => {
      document.querySelectorAll('[id^="section-"]').forEach(el => el.style.display = 'none');
      secs.forEach(id => document.getElementById(id).style.display = 'block');
    });
  });

  // Filter columns
  function filterColumns(userId) {
    document.querySelectorAll('.user-col').forEach(el => {
      el.style.display = (!userId || el.classList.contains(`col-u-${userId}`)) ? '' : 'none';
    });
  }
  document.getElementById('applyFilter').addEventListener('click', () => {
    filterColumns(document.getElementById('filterUserId').value.trim());
  });
  document.getElementById('clearFilter').addEventListener('click', () => {
    document.getElementById('filterUserId').value = '';
    filterColumns('');
  });

  // Hitung suggestion via JS
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('#section-df table[data-df]').forEach(tbl => {
      const df = parseInt(tbl.dataset.df, 10);
      const isMode = [1,2,3,4,7].includes(df);

      tbl.querySelectorAll('tbody tr').forEach(row => {
        // Ambil text tiap user-col, skip '-' dan non-numeric
        const cells = Array.from(row.querySelectorAll('td'))
          .slice(1, -1)
          .map(td => td.textContent.trim())
          .filter(txt => txt !== '-');

        if (isMode) {
          const freq = {};
          cells.forEach(v => {
            // hanya numeric atau string angka
            if (!isNaN(v)) {
              v = String(v);
              freq[v] = (freq[v]||0) + 1;
            }
          });
          const max = Math.max(0, ...Object.values(freq));
          const modes = Object.entries(freq)
            .filter(([,c]) => c === max)
            .map(([v]) => v)
            .join(', ');
          row.querySelector('.suggestion').textContent = modes || '–';

        } else {
          // Rata2 persen: hanya hitung yang numeric
          const nums = cells
            .map(v=>parseFloat(v))
            .filter(v=>!isNaN(v));
          const avg = nums.length
            ? nums.reduce((a,b)=>a+b,0)/nums.length
            : 0;
          row.querySelector('.suggestion').textContent = avg.toFixed(2) + '%';
        }
      });
    });
  });
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Render distribution charts (values 1..5) for each DF input row
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('#section-df table[data-df]').forEach(tbl => {
            tbl.querySelectorAll('tbody tr').forEach((row, rowIndex) => {
                try {
                    // collect user columns text (skip first label cell, last statistik cell)
                    const userCells = Array.from(row.querySelectorAll('td.user-col')).map(td => td.textContent.trim());
                    // prepare counts for values 1..5
                    const labels = ['1','2','3','4','5'];
                    const counts = labels.map(() => 0);
                    userCells.forEach(v => {
                        if (!v || v === '-') return;
                        const num = parseInt(v);
                        if (!isNaN(num) && num >= 1 && num <= 5) {
                            counts[num - 1] += 1;
                        }
                    });

                    // find canvas inside this row
                    const canvas = row.querySelector('canvas');
                    if (!canvas) return;

                    // ensure canvas has explicit pixel size so Chart can render reliably
                    canvas.width = 160;
                    canvas.height = 80;
                    canvas.style.width = '160px';
                    canvas.style.height = '80px';

                    const ctx = canvas.getContext('2d');
                    // clear any previous drawing
                    ctx.clearRect(0,0,canvas.width,canvas.height);

                    const total = counts.reduce((a,b)=>a+b,0);
                    if (total === 0) {
                        // draw a subtle placeholder text when no responses
                        ctx.fillStyle = '#6c757d';
                        ctx.font = '12px system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText('No responses', canvas.width/2, canvas.height/2);
                        return;
                    }

                    // create small bar chart
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: counts,
                                backgroundColor: labels.map((_,i) => `rgba(54,162,235,${0.75 - i*0.08})`),
                                borderColor: 'rgba(54,162,235,1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: false,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => `${ctx.label}: ${ctx.parsed.y} responden`
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    display: true,
                                    grid: { display: false },
                                    ticks: { font: { size: 11 } }
                                },
                                y: {
                                    display: true,
                                    beginAtZero: true,
                                    ticks: { precision: 0, stepSize: 1 }
                                }
                            }
                        }
                    });
                } catch (err) {
                    // debug output in console if chart fails
                    console.error('Chart render error for row', rowIndex, err);
                    const canvas = row.querySelector('canvas');
                    if (canvas) {
                        const ctx = canvas.getContext('2d');
                        ctx.clearRect(0,0,canvas.width,canvas.height);
                        ctx.fillStyle = '#dc3545';
                        ctx.font = '12px system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText('Chart error', canvas.width/2, canvas.height/2);
                    }
                }
            });
        });
    });
</script>
@endsection
