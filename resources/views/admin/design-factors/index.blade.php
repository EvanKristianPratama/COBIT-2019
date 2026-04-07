@extends('layouts.admin')

@section('admin_title', 'Manage Design Factor')

@section('admin_content')
<style>
    :root {
        --df-admin-border: #d7dfeb;
        --df-admin-bg: #ffffff;
        --df-admin-muted: #64748b;
        --df-admin-text: #0f172a;
        --df-admin-primary: var(--cobit-primary);
        --df-admin-primary-soft: rgba(15, 43, 92, 0.08);
        --df-admin-bronze: var(--cobit-secondary);
        --df-admin-bronze-soft: rgba(26, 61, 107, 0.1);
    }

    .df-admin-card, .df-admin-panel, .df-admin-link-card {
        border: 1px solid var(--df-admin-border);
        border-radius: 20px;
        background: var(--df-admin-bg);
        box-shadow: 0 16px 34px rgba(15, 23, 42, 0.05);
    }

    .df-admin-card { padding: 1.15rem 1.2rem; height: 100%; }
    .df-admin-label { font-size: 0.74rem; text-transform: uppercase; letter-spacing: 0.06em; font-weight: 800; color: var(--df-admin-muted); }
    .df-admin-value { margin-top: 0.5rem; font-size: 1.9rem; font-weight: 800; line-height: 1; }
    .df-admin-icon { width: 48px; height: 48px; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; background: var(--df-admin-primary-soft); color: var(--df-admin-primary); }
    .df-admin-icon-bronze { background: var(--df-admin-bronze-soft); color: var(--df-admin-bronze); }

    .df-admin-link-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.9rem;
        padding: 1rem 1.05rem;
        text-decoration: none;
        color: var(--df-admin-text);
        transition: all 0.2s ease;
        height: 100%;
    }

    .df-admin-link-card:hover { color: var(--df-admin-text); transform: translateY(-2px); box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08); }
    .df-admin-link-main { display: flex; align-items: center; gap: 0.85rem; min-width: 0; }
    .df-admin-link-icon { width: 48px; height: 48px; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; background: var(--df-admin-primary-soft); color: var(--df-admin-primary); flex-shrink: 0; }
    .df-admin-link-card.variant-primary .df-admin-link-icon { background: var(--df-admin-bronze-soft); color: var(--df-admin-bronze); }
    .df-admin-link-label { font-weight: 800; margin-bottom: 0.18rem; }
    .df-admin-panel .card-header { background: transparent; border-bottom: 1px solid var(--df-admin-border); padding: 1rem 1.2rem; }
    .df-admin-panel .card-body { padding: 0; }
    .df-admin-table { margin-bottom: 0; }
    .df-admin-table thead th { background: #f8fafc; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.72rem; font-weight: 800; border-bottom: 1px solid var(--df-admin-border); padding: 0.9rem 1rem; }
    .df-admin-table tbody td { border-color: #edf2f7; padding: 0.95rem 1rem; vertical-align: top; }
    .df-admin-chip { display: inline-flex; align-items: center; padding: 0.35rem 0.72rem; border-radius: 999px; background: #f8fafc; border: 1px solid #dbe4ee; color: #334155; font-size: 0.78rem; font-weight: 800; }
</style>

<div class="container-fluid px-0">
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3"><div class="df-admin-card"><div class="d-flex justify-content-between align-items-start"><div><div class="df-admin-label">Assessment</div><div class="df-admin-value">{{ $stats['assessments'] }}</div></div><span class="df-admin-icon"><i class="fas fa-clipboard-check"></i></span></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="df-admin-card"><div class="d-flex justify-content-between align-items-start"><div><div class="df-admin-label">Organisasi</div><div class="df-admin-value">{{ $stats['organizations'] }}</div></div><span class="df-admin-icon df-admin-icon-bronze"><i class="fas fa-building"></i></span></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="df-admin-card"><div class="d-flex justify-content-between align-items-start"><div><div class="df-admin-label">Target Capability</div><div class="df-admin-value">{{ $stats['target_capabilities'] }}</div></div><span class="df-admin-icon"><i class="fas fa-bullseye"></i></span></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="df-admin-card"><div class="d-flex justify-content-between align-items-start"><div><div class="df-admin-label">Target Maturity</div><div class="df-admin-value">{{ $stats['target_maturities'] }}</div></div><span class="df-admin-icon df-admin-icon-bronze"><i class="fas fa-chart-line"></i></span></div></div></div>
    </div>

    <div class="row g-3 mb-4">
        @foreach($workspaceLinks as $workspaceLink)
            <div class="col-lg-6 col-xl-3">
                <a href="{{ $workspaceLink['route'] }}" class="df-admin-link-card variant-{{ $workspaceLink['variant'] }}">
                    <div class="df-admin-link-main">
                        <span class="df-admin-link-icon"><i class="fas {{ $workspaceLink['icon'] }}"></i></span>
                        <span>
                            <span class="df-admin-link-label d-block">{{ $workspaceLink['label'] }}</span>
                        </span>
                    </div>
                    <span class="text-muted"><i class="fas fa-chevron-right"></i></span>
                </a>
            </div>
        @endforeach
    </div>

    <div class="card df-admin-panel border-0">
        <div class="card-header">
            <div class="fw-bold">Recent Design Factor Assessments</div>
        </div>
        <div class="card-body">
            @if($recentAssessments->isEmpty())
                <div class="text-center text-muted py-5">Belum ada assessment design factor.</div>
            @else
                <div class="table-responsive">
                    <table class="table df-admin-table align-middle">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Organisasi</th>
                                <th>Owner</th>
                                <th>Dibuat</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentAssessments as $recentAssessment)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $recentAssessment->kode_assessment }}</div>
                                        <div class="text-muted small">Assessment #{{ $recentAssessment->assessment_id }}</div>
                                    </td>
                                    <td><span class="df-admin-chip">{{ $recentAssessment->instansi }}</span></td>
                                    <td>{{ $recentAssessment->creator?->name ?? 'System' }}</td>
                                    <td>
                                        <div>{{ optional($recentAssessment->created_at)->format('d M Y') }}</div>
                                        <div class="text-muted small">{{ optional($recentAssessment->created_at)->format('H:i') }}</div>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2 flex-wrap">
                                            <a href="{{ route('admin.assessments.show', $recentAssessment->assessment_id) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-shield-alt me-1"></i>Manage
                                            </a>
                                            <form method="POST" action="{{ route('assessment.join.store') }}" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="kode_assessment" value="{{ $recentAssessment->kode_assessment }}">
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-arrow-up-right-from-square me-1"></i>Open
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
