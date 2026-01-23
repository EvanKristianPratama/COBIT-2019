@props([
    'title' => 'Statistik Distribusi Jawaban',
    'chartIdPrefix' => 'admin_stats',
    'fields' => [],
    'frequencies' => [],
    'totals' => [],
    'submissions' => [],
    'users' => [],
    'contacts' => [],
    'roles' => [],
])

<div class="card shadow-sm border-0 rounded-3 mb-4">
    <div class="card-header text-white fw-bold text-center py-3" style="background: linear-gradient(135deg, #081a3d, #0f2b5c, #1a3d6b);">
        {{ $title }}
    </div>
    <div class="card-body p-4">
        <div class="accordion" id="{{ $chartIdPrefix }}Accordion">
            
            {{-- Section 1: Distribution Statistics --}}
            <div class="accordion-item border-0 shadow-sm mb-3 rounded-3 overflow-hidden">
                <h2 class="accordion-header" id="{{ $chartIdPrefix }}StatsHeading">
                    <button class="accordion-button fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $chartIdPrefix }}StatsCollapse" aria-expanded="true">
                        <i class="bi bi-bar-chart-line me-2 text-primary"></i> Statistik Distribusi
                    </button>
                </h2>
                <div id="{{ $chartIdPrefix }}StatsCollapse" class="accordion-collapse collapse show" data-bs-parent="#{{ $chartIdPrefix }}Accordion">
                    <div class="accordion-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light small text-uppercase text-muted">
                                    <tr>
                                        <th class="ps-4" style="min-width: 200px;">Indikator</th>
                                        <th class="text-center" style="width: 100px;">Total</th>
                                        <th class="text-center" style="min-width: 250px;">Distribusi Jawaban</th>
                                        <th class="text-center" style="width: 150px;">Modus</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($fields as $f => $label)
                                        @php
                                            $freqs = $frequencies[$f] ?? [1=>0,2=>0,3=>0,4=>0,5=>0];
                                            $maxVal = max($freqs);
                                            $suggestions = collect($freqs)->filter(fn($v) => $v === $maxVal && $v > 0)->keys()->all();
                                        @endphp
                                        <tr>
                                            <td class="ps-4 fw-medium text-dark">{{ $label }}</td>
                                            <td class="text-center fw-bold text-secondary">{{ $totals[$f] ?? 0 }}</td>
                                            <td class="py-3">
                                                <div style="height: 60px; width: 100%;">
                                                    <canvas id="{{ $chartIdPrefix }}_{{ $f }}_chart"></canvas>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                @if(count($suggestions) > 0)
                                                    @foreach($suggestions as $s)
                                                        <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">{{ $s }}</span>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted small">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 2: User Details --}}
            <div class="accordion-item border-0 shadow-sm rounded-3 overflow-hidden">
                <h2 class="accordion-header" id="{{ $chartIdPrefix }}UserHeading">
                    <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $chartIdPrefix }}UserCollapse" aria-expanded="false">
                        <i class="bi bi-people me-2 text-primary"></i> Detail Jawaban Per User
                    </button>
                </h2>
                <div id="{{ $chartIdPrefix }}UserCollapse" class="accordion-collapse collapse" data-bs-parent="#{{ $chartIdPrefix }}Accordion">
                    <div class="accordion-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm align-middle mb-0">
                                <thead class="bg-light small text-uppercase text-muted">
                                    <tr>
                                        <th class="text-center ps-3">No</th>
                                        <th>Nama</th>
                                        <th>Kontak / Email</th>
                                        <th>Role / Jabatan</th>
                                        @foreach($fields as $f => $label)
                                            <th class="text-center">{{ $label }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($submissions as $i => $row)
                                        <tr>
                                            <td class="text-center ps-3 text-muted">{{ $i + 1 }}</td>
                                            <td class="fw-medium">{{ $users[$row->id] ?? 'Unknown' }}</td>
                                            <td class="text-muted small">{{ $contacts[$row->id] ?? '-' }}</td>
                                            <td><span class="badge bg-light text-dark border">{{ $roles[$row->id] ?? '-' }}</span></td>
                                            @foreach($fields as $f => $label)
                                                <td class="text-center fw-bold text-primary">{{ $row->{$f} ?? '-' }}</td>
                                            @endforeach
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ 4 + count($fields) }}" class="text-center py-4 text-muted">
                                                <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                                Belum ada data responden.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const freqs = @json($frequencies);
        const prefix = @json($chartIdPrefix);
        
        Object.keys(freqs).forEach(function(f) {
            const canvas = document.getElementById(prefix + '_' + f + '_chart');
            if (!canvas) return;
            
            const data = [freqs[f][1], freqs[f][2], freqs[f][3], freqs[f][4], freqs[f][5]];
            
            new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: ['1','2','3','4','5'],
                    datasets: [{
                        data: data,
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(54, 162, 235, 0.3)',
                            'rgba(54, 162, 235, 0.4)',
                            'rgba(54, 162, 235, 0.5)',
                            'rgba(54, 162, 235, 0.6)',
                        ],
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        borderRadius: 4,
                        barPercentage: 0.6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y', // Horizontal bars are better for small spaces
                    plugins: { 
                        legend: { display: false },
                        tooltip: { 
                            enabled: true,
                            callbacks: {
                                label: (ctx) => `Jumlah: ${ctx.raw}`
                            }
                        }
                    },
                    scales: {
                        x: { 
                            display: false,
                            grid: { display: false }
                        },
                        y: { 
                            display: true,
                            grid: { display: false, drawBorder: false },
                            ticks: { font: { size: 10 } }
                        }
                    },
                    layout: {
                        padding: { left: 0, right: 10, top: 0, bottom: 0 }
                    }
                }
            });
        });
    });
</script>
