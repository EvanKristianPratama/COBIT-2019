@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
@php
    $evaluationsCollection = $evaluations ?? collect();
    $totalAssessments = $evaluationsCollection->count();
    $totalRatableActivities = \App\Models\MstActivities::count();
    $totalRatedActivities = $evaluationsCollection->sum(function($evaluation) {
        return ($evaluation->activityEvaluations ?? collect())->whereIn('level_achieved', ['F','L','P'])->count();
    });
@endphp
<div class="container mx-auto p-6" id="page-top">
    {{-- Main Hero Card --}}
    <div class="card shadow-sm mb-4 hero-card">
        <div class="card-header hero-header py-4">
            <div>
                <div class="hero-title">COBIT 2019 Assessments</div>
                <p class="hero-subtitle mb-0">Ringkasan portofolio asesmen yang siap dilanjutkan.</p>
            </div>
            <span class="hero-pill">
                <i class="fas fa-layer-group me-2"></i>List View
            </span>
        </div>
        <div class="card-body hero-body">
            <div class="hero-quick d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div class="hero-stat-card mb-0">
                    <span class="stat-label">Total Assessment</span>
                    <span class="stat-value">{{ number_format($totalAssessments) }}</span>
                    <span class="stat-subtext">Portofolio aktif</span>
                </div>
                <form action="{{ route('assessment-eval.create') }}" method="POST" class="d-inline-flex">
                    @csrf
                    <button type="submit" class="btn btn-light hero-action-btn">
                        <i class="fas fa-plus me-2"></i>Assessment Baru
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Error Messages --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong> {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Assessments List --}}
    @if($totalAssessments > 0)
        <div class="row g-4 assessment-grid" id="assessments-grid">
            @foreach($evaluationsCollection as $evaluation)
                @php
                    $activityEvaluations = $evaluation->activityEvaluations ?? collect();
                    $achievementCounts = $activityEvaluations->groupBy('level_achieved')->map->count();
                    $ratedCounts = [
                        'F' => $achievementCounts['F'] ?? 0,
                        'L' => $achievementCounts['L'] ?? 0,
                        'P' => $achievementCounts['P'] ?? 0,
                    ];
                    $totalRated = array_sum($ratedCounts);
                    $noneCount = max(0, $totalRatableActivities - $totalRated);
                    $percentages = $totalRatableActivities > 0
                        ? [
                            'F' => round(($ratedCounts['F'] / $totalRatableActivities) * 100, 1),
                            'L' => round(($ratedCounts['L'] / $totalRatableActivities) * 100, 1),
                            'P' => round(($ratedCounts['P'] / $totalRatableActivities) * 100, 1),
                            'N' => round(($noneCount / $totalRatableActivities) * 100, 1),
                        ]
                        : ['F' => 0, 'L' => 0, 'P' => 0, 'N' => 0];
                    $completion = $totalRatableActivities > 0 ? round(($totalRated / $totalRatableActivities) * 100, 1) : 0;
                    if ($completion >= 90) {
                        $statusLabel = 'Siap Review';
                        $statusClass = 'chip-success';
                    } elseif ($completion >= 60) {
                        $statusLabel = 'On Track';
                        $statusClass = 'chip-info';
                    } elseif ($completion > 0) {
                        $statusLabel = 'Sedang Dikerjakan';
                        $statusClass = 'chip-warning';
                    } else {
                        $statusLabel = 'Belum Mulai';
                        $statusClass = 'chip-muted';
                    }
                @endphp
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card h-100 assessment-card shadow-sm border-0">
                        <div class="card-header assessment-card-header">
                            <div>
                                <div class="assessment-code">Assessment {{ $evaluation->eval_id ?? '—' }}</div>
                                <div class="assessment-meta">
                                    Dibuat {{ optional($evaluation->created_at)->format('d M Y') ?? '—' }}
                                </div>
                            </div>
                            <span class="status-chip {{ $statusClass }}">{{ $statusLabel }}</span>
                        </div>
                        <div class="card-body">
                            <div class="assessment-progress-block">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="progress-label">Progress Capability</span>
                                    <span class="progress-value">{{ number_format($completion, 1) }}%</span>
                                </div>
                                <div class="progress assessment-progress">
                                    @if($percentages['F'] > 0)
                                        <div class="progress-bar bg-success" style="width: {{ $percentages['F'] }}%" title="Fully"></div>
                                    @endif
                                    @if($percentages['L'] > 0)
                                        <div class="progress-bar bg-info" style="width: {{ $percentages['L'] }}%" title="Largely"></div>
                                    @endif
                                    @if($percentages['P'] > 0)
                                        <div class="progress-bar bg-warning" style="width: {{ $percentages['P'] }}%" title="Partial"></div>
                                    @endif
                                    @if($percentages['N'] > 0)
                                        <div class="progress-bar bg-danger" style="width: {{ $percentages['N'] }}%" title="None"></div>
                                    @endif
                                </div>
                                <div class="rating-breakdown">
                                    <span class="rating-pill pill-success">F <strong>{{ $ratedCounts['F'] }}</strong></span>
                                    <span class="rating-pill pill-info">L <strong>{{ $ratedCounts['L'] }}</strong></span>
                                    <span class="rating-pill pill-warning">P <strong>{{ $ratedCounts['P'] }}</strong></span>
                                    <span class="rating-pill pill-danger">N <strong>{{ $noneCount }}</strong></span>
                                </div>
                            </div>
                            <ul class="assessment-timestamps list-unstyled mt-3 mb-0">
                                <li>
                                    <i class="fas fa-clock me-2 text-muted"></i>
                                    Terakhir diperbarui {{ optional($evaluation->updated_at)->diffForHumans() ?? '—' }}
                                </li>
                            </ul>
                        </div>
                        <div class="card-footer assessment-card-footer">
                            <a href="{{ route('assessment-eval.show', $evaluation->eval_id) }}" class="btn btn-primary btn-sm assessment-view-btn" title="Lihat Assessment #{{ $evaluation->eval_id }}">
                                <i class="fas fa-eye me-1"></i>Lihat Detail
                            </a>
                            <button class="btn btn-outline-danger btn-sm delete-assessment" 
                                    data-eval-id="{{ $evaluation->eval_id }}"
                                    data-db-id="{{ $evaluation->id }}"
                                    title="Hapus Assessment #{{ $evaluation->eval_id }}">
                                <i class="fas fa-trash me-1"></i>Hapus
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        {{-- Empty State --}}
        <div class="text-center py-5">
            <div class="card empty-state-card">
                <div class="card-body">
                    <i class="fas fa-clipboard-list text-muted mb-3" style="font-size: 4rem;"></i>
                    <h5 class="text-muted mb-3">Belum ada assessment</h5>
                    <p class="text-muted mb-4">
                        Mulai assessment pertama untuk membuka ringkasan domain seperti pada halaman detail.
                    </p>
                    <form action="{{ route('assessment-eval.create') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-lg hero-action-btn">
                            <i class="fas fa-plus me-2"></i>Buat Assessment Pertama
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Debug removed for cleaner UI --}}
</div>

<div class="sticky-action-group">
    <form action="{{ route('assessment-eval.create') }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="sticky-action-btn btn btn-primary" title="Assessment Baru">
            <i class="fas fa-plus me-2"></i>Assessment Baru
        </button>
    </form>
    <a href="{{ url('/') }}" class="sticky-action-btn btn btn-light" title="Beranda">
        <i class="fas fa-home me-2"></i>Home
    </a>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-assessment');
    console.log('Found delete buttons:', deleteButtons.length);

    deleteButtons.forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            const evalId = this.getAttribute('data-eval-id');
            const dbId = this.getAttribute('data-db-id');
            console.log('Delete button clicked for eval ID:', evalId, 'DB ID:', dbId);
            
            if (!evalId && !dbId) {
                alert('Unable to determine assessment id to delete.');
                return;
            }

            if (confirm(`Are you sure you want to delete Assessment #${evalId ?? dbId}? This action cannot be undone.`)) {
                try {
                    // Prefer DB ID when it's numeric (common for primary keys),
                    // otherwise fallback to evalId. This makes the frontend flexible.
                    let idToUse = null;
                    if (dbId && !isNaN(dbId)) {
                        idToUse = dbId;
                    } else {
                        idToUse = evalId;
                    }

                    console.log(`/assessment-eval/${encodeURIComponent(idToUse)}`);
                    const response = await fetch(`/assessment-eval/${encodeURIComponent(idToUse)}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({}) // some servers expect body; safe to send empty object
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        showNotification('Assessment deleted successfully!', 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1200);
                    } else {
                        // show server message if any, fallback to generic
                        showNotification(result.message || 'Failed to delete assessment', 'error');
                    }
                } catch (error) {
                    console.error('Delete error:', error);
                    showNotification('Failed to delete assessment', 'error');
                }
            }
        });
    });

    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }
});
</script>

<style>
.hero-card {
    border: none;
    border-radius: 1rem;
    box-shadow: 0 25px 60px rgba(9, 18, 56, 0.18);
    overflow: hidden;
}

.hero-header {
    background: linear-gradient(135deg, #081a3d, #0f2b5c);
    color: #fff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    border: none;
}

.hero-title {
    font-size: 1.6rem;
    font-weight: 700;
    letter-spacing: 0.03em;
}

.hero-subtitle {
    color: rgba(255,255,255,0.8);
    letter-spacing: 0.02em;
}

.hero-pill {
    border-radius: 999px;
    padding: 0.4rem 1.3rem;
    background: rgba(255,255,255,0.15);
    font-weight: 600;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

.hero-body {
    padding: 1.75rem;
    background: #fff;
}

.hero-copy {
    font-size: 1rem;
    color: #4b5677;
    margin-bottom: 1.25rem;
}

.hero-stat-card {
    background: #f6f8ff;
    border-radius: 0.9rem;
    padding: 1rem 1.2rem;
    border: 1px solid #e3e8ff;
    height: 100%;
}

.stat-label {
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #6b7392;
}

.stat-value {
    display: block;
    font-size: 1.6rem;
    font-weight: 700;
    color: #0f2b5c;
    margin: 0.15rem 0;
}

.stat-subtext {
    font-size: 0.9rem;
    color: #7b84a5;
}

.hero-action-btn {
    border-radius: 999px;
    padding: 0.55rem 1.5rem;
    font-weight: 600;
    box-shadow: 0 12px 30px rgba(15,106,217,0.2);
}

.hero-actions-secondary {
    font-size: 0.95rem;
    color: #5c6280;
}

.hero-link {
    font-weight: 600;
    color: #0f6ad9;
    text-decoration: none;
}

.assessment-grid .card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.assessment-grid .card:hover {
    transform: translateY(-3px);
    box-shadow: 0 18px 40px rgba(15,43,92,0.12);
}

.assessment-card-header {
    background: #f8f9ff;
    border-bottom: 1px solid rgba(15,43,92,0.08);
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.75rem;
}

.assessment-code {
    font-weight: 700;
    color: #0f2b5c;
}

.assessment-meta {
    font-size: 0.85rem;
    color: #7a809b;
}

.assessment-meta .divider {
    margin: 0 0.35rem;
}

.status-chip {
    border-radius: 999px;
    padding: 0.35rem 1rem;
    font-weight: 600;
    font-size: 0.85rem;
    letter-spacing: 0.03em;
}

.chip-success { background: #d1f2e2; color: #0f5132; }
.chip-info { background: #cff4fc; color: #055160; }
.chip-warning { background: #fff3cd; color: #7a5d07; }
.chip-muted { background: #f1f3f9; color: #5f6783; }

.assessment-progress-block {
    border: 1px solid #e4e8fb;
    border-radius: 0.9rem;
    padding: 1rem 1.25rem;
    background: #fff;
}

.assessment-progress {
    height: 8px;
    border-radius: 6px;
    overflow: hidden;
}

.progress-label {
    font-weight: 600;
    color: #4a5070;
}

.progress-value {
    font-weight: 700;
    color: #0f2b5c;
}

.rating-breakdown {
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem;
    margin-top: 0.85rem;
    font-size: 0.85rem;
}

.rating-pill {
    border-radius: 999px;
    padding: 0.25rem 0.85rem;
    font-weight: 600;
    border: 1px solid transparent;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

.rating-pill strong {
    font-size: 0.9rem;
}

.pill-success { background: #d1f2e2; color: #0f5132; }
.pill-info { background: #cff4fc; color: #055160; }
.pill-warning { background: #fff3cd; color: #7a5d07; }
.pill-danger { background: #fee2e2; color: #7a1a1a; }

.assessment-timestamps li {
    margin-bottom: 0.4rem;
    color: #5c6280;
}

.assessment-card-footer {
    background: #f8f9ff;
    border-top: 1px solid rgba(15,43,92,0.08);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.75rem;
}

.assessment-view-btn {
    border-radius: 999px;
    padding: 0.45rem 1.25rem;
    font-weight: 600;
}

.delete-assessment {
    border-radius: 999px;
    font-weight: 600;
}

.sticky-action-group {
    position: fixed;
    right: 25px;
    bottom: 25px;
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    z-index: 1050;
}

.sticky-action-btn {
    border-radius: 999px;
    padding: 0.65rem 1.4rem;
    font-weight: 600;
    box-shadow: 0 12px 32px rgba(15,106,217,0.2);
}

.sticky-action-btn.btn-light {
    background: #fff;
    color: #0f2b5c;
    border: 1px solid rgba(15,43,92,0.15);
}

.sticky-action-btn.btn-primary {
    background: linear-gradient(120deg, #0f6ad9, #0c4fb5);
    border: none;
}

.empty-state-card {
    border-radius: 1rem;
    border: none;
    box-shadow: 0 24px 55px rgba(15,43,92,0.12);
}

.empty-state-card .card-body {
    padding: 3rem 2.5rem;
}

@media (max-width: 576px) {
    .hero-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .assessment-card-footer {
        flex-direction: column;
        align-items: stretch;
    }

    .assessment-card-footer .btn {
        width: 100%;
    }
}
</style>
@endsection
