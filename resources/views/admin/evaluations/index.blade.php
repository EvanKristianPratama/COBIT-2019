@extends('layouts.admin')

@section('admin_title', 'Manage Assessment')

@section('admin_content')
<style>
    .evaluation-admin-page {
        --eval-border: #d7dfeb;
        --eval-bg: #ffffff;
        --eval-muted: #64748b;
        --eval-text: #0f172a;
        --eval-primary: var(--cobit-primary);
        --eval-primary-soft: rgba(15, 43, 92, 0.08);
        --eval-secondary: var(--cobit-secondary);
        --eval-secondary-soft: rgba(26, 61, 107, 0.1);
        --eval-accent: var(--cobit-accent);
        --eval-accent-soft: rgba(15, 106, 217, 0.1);
        --eval-danger: #8a3c2d;
        --eval-danger-soft: #f8ece8;
    }

    .evaluation-admin-page .panel-card,
    .evaluation-admin-page .stats-card,
    .evaluation-admin-page .filter-card {
        border: 1px solid var(--eval-border);
        border-radius: 22px;
        background: var(--eval-bg);
        box-shadow: 0 16px 34px rgba(15, 23, 42, 0.05);
    }

    .evaluation-admin-page .stats-card {
        padding: 1.1rem 1.2rem;
        height: 100%;
    }

    .evaluation-admin-page .stats-label {
        font-size: 0.74rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        font-weight: 800;
        color: var(--eval-muted);
    }

    .evaluation-admin-page .stats-value {
        margin-top: 0.55rem;
        font-size: 1.9rem;
        line-height: 1;
        font-weight: 800;
        color: var(--eval-text);
    }

    .evaluation-admin-page .stats-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    .evaluation-admin-page .icon-primary { background: var(--eval-primary-soft); color: var(--eval-primary); }
    .evaluation-admin-page .icon-secondary { background: var(--eval-secondary-soft); color: var(--eval-secondary); }
    .evaluation-admin-page .icon-accent { background: var(--eval-accent-soft); color: var(--eval-accent); }
    .evaluation-admin-page .icon-danger { background: var(--eval-danger-soft); color: var(--eval-danger); }

    .evaluation-admin-page .filter-card {
        padding: 1rem 1.1rem;
        margin-bottom: 1rem;
    }

    .evaluation-admin-page .panel-card .card-header {
        background: transparent;
        border-bottom: 1px solid var(--eval-border);
        padding: 1rem 1.2rem;
    }

    .evaluation-admin-page .panel-card .card-body {
        padding: 0;
    }

    .evaluation-admin-page .table-enterprise {
        margin-bottom: 0;
    }

    .evaluation-admin-page .table-enterprise thead th {
        background: #f8fafc;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-size: 0.72rem;
        font-weight: 800;
        border-bottom: 1px solid var(--eval-border);
        padding: 0.92rem 1rem;
        white-space: nowrap;
    }

    .evaluation-admin-page .table-enterprise tbody td {
        border-color: #edf2f7;
        padding: 0.95rem 1rem;
        vertical-align: top;
    }

    .evaluation-admin-page .table-enterprise tbody tr:hover {
        background: rgba(15, 106, 217, 0.04);
    }

    .evaluation-admin-page .eval-id {
        font-size: 1rem;
        font-weight: 800;
        color: var(--eval-text);
    }

    .evaluation-admin-page .eval-meta {
        font-size: 0.84rem;
        color: var(--eval-muted);
    }

    .evaluation-admin-page .chip {
        display: inline-flex;
        align-items: center;
        padding: 0.38rem 0.74rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 700;
        border: 1px solid transparent;
    }

    .evaluation-admin-page .chip-primary {
        background: var(--eval-primary-soft);
        color: var(--eval-primary);
        border-color: rgba(15, 43, 92, 0.12);
    }

    .evaluation-admin-page .chip-neutral {
        background: #f8fafc;
        color: #334155;
        border-color: #dbe4ee;
    }

    .evaluation-admin-page .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.42rem 0.78rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 800;
    }

    .evaluation-admin-page .status-finished {
        background: var(--eval-secondary-soft);
        color: var(--eval-secondary);
    }

    .evaluation-admin-page .status-draft {
        background: var(--eval-accent-soft);
        color: var(--eval-accent);
    }

    .evaluation-admin-page .metric-value {
        font-size: 1rem;
        font-weight: 800;
        color: var(--eval-text);
    }

    .evaluation-admin-page .empty-state {
        padding: 3rem 1.5rem;
        text-align: center;
        color: var(--eval-muted);
    }

    .evaluation-admin-page .empty-state i {
        font-size: 2.8rem;
        margin-bottom: 0.85rem;
    }
</style>

<div class="container-fluid px-0 evaluation-admin-page">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-circle-exclamation me-2"></i>{{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stats-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stats-label">Total Assessment</div>
                        <div class="stats-value">{{ number_format($stats['total']) }}</div>
                    </div>
                    <span class="stats-icon icon-primary"><i class="fas fa-clipboard-check"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stats-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stats-label">Finished</div>
                        <div class="stats-value">{{ number_format($stats['finished']) }}</div>
                    </div>
                    <span class="stats-icon icon-secondary"><i class="fas fa-flag-checkered"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stats-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stats-label">Draft</div>
                        <div class="stats-value">{{ number_format($stats['draft']) }}</div>
                    </div>
                    <span class="stats-icon icon-accent"><i class="fas fa-pen-ruler"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stats-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stats-label">Owner / Org</div>
                        <div class="stats-value">{{ number_format($stats['owners']) }} / {{ number_format($stats['organizations']) }}</div>
                    </div>
                    <span class="stats-icon icon-danger"><i class="fas fa-users-gear"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="filter-card">
        <form action="{{ route('admin.assessments.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-xl-4">
                <label for="eval_search" class="form-label">Cari</label>
                <input id="eval_search" type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Eval ID, owner, email, organisasi, tahun">
            </div>
            <div class="col-md-4 col-xl-2">
                <label for="eval_year" class="form-label">Tahun</label>
                <select id="eval_year" name="year" class="form-select">
                    <option value="">Semua</option>
                    @foreach($yearOptions as $yearOption)
                        <option value="{{ $yearOption }}" {{ (string) request('year') === (string) $yearOption ? 'selected' : '' }}>
                            {{ $yearOption }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 col-xl-3">
                <label for="eval_organization" class="form-label">Organisasi</label>
                <select id="eval_organization" name="organization_id" class="form-select">
                    <option value="">Semua</option>
                    @foreach($organizationOptions as $organizationOption)
                        <option value="{{ $organizationOption->organization_id }}" {{ (string) request('organization_id') === (string) $organizationOption->organization_id ? 'selected' : '' }}>
                            {{ $organizationOption->organization_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 col-xl-2">
                <label for="eval_status" class="form-label">Status</label>
                <select id="eval_status" name="status" class="form-select">
                    <option value="">Semua</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="finished" {{ request('status') === 'finished' ? 'selected' : '' }}>Finished</option>
                </select>
            </div>
            <div class="col-xl-1 d-grid">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
            <div class="col-12 d-flex flex-wrap gap-2">
                <a href="{{ route('admin.assessments.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-rotate-left me-2"></i>Reset
                </a>
                <a href="{{ route('admin.design-assessments.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-shield-alt me-2"></i>Manage Assessment Code
                </a>
            </div>
        </form>
    </div>

    <div class="card panel-card border-0">
        <div class="card-header d-flex flex-column flex-lg-row justify-content-between gap-2 align-items-lg-center">
            <div class="fw-bold">Assessment Evaluation Directory</div>
            <span class="chip chip-neutral">{{ $evaluations->total() }} data</span>
        </div>
        <div class="card-body">
            @if($evaluations->count() === 0)
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <div class="fw-semibold mb-1">Belum ada assessment evaluation</div>
                    <div>Data `mst_eval` akan muncul di sini setelah assessment dibuat.</div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-enterprise align-middle">
                        <thead>
                            <tr>
                                <th>Assessment</th>
                                <th>Owner</th>
                                <th>Organisasi</th>
                                <th>Status</th>
                                <th>Scope</th>
                                <th>Score</th>
                                <th>Target</th>
                                <th>Assigned</th>
                                <th>Last Update</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($evaluations as $evaluation)
                                @php
                                    $isFinished = ($evaluation->status ?? '') === 'finished';
                                    $score = (float) ($evaluation->maturityScore->score ?? 0);
                                    $lastSavedAt = $evaluation->last_saved_at ? \Carbon\Carbon::parse($evaluation->last_saved_at) : null;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="eval-id">Eval #{{ $evaluation->eval_id }}</div>
                                        <div class="eval-meta">Tahun {{ $evaluation->tahun ?? '-' }}</div>
                                        <div class="eval-meta">{{ optional($evaluation->created_at)->format('d M Y H:i') }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $evaluation->user?->name ?? 'System' }}</div>
                                        <div class="eval-meta">{{ $evaluation->user?->email ?? '-' }}</div>
                                    </td>
                                    <td>
                                        <span class="chip chip-primary">{{ $evaluation->organization?->organization_name ?? $evaluation->user?->organisasi ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <span class="status-badge {{ $isFinished ? 'status-finished' : 'status-draft' }}">
                                            <i class="fas {{ $isFinished ? 'fa-circle-check' : 'fa-pen-to-square' }}"></i>
                                            {{ $isFinished ? 'Finished' : 'Draft' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="metric-value">{{ number_format((int) ($evaluation->scope_count ?? 0)) }}</div>
                                        <div class="eval-meta">scope aktif</div>
                                    </td>
                                    <td>
                                        <div class="metric-value">{{ number_format($score, 2) }}</div>
                                        <div class="eval-meta">maturity score</div>
                                    </td>
                                    <td>
                                        <div class="metric-value">{{ number_format((float) ($evaluation->avg_target_capability ?? 0), 2) }}</div>
                                        <div class="eval-meta">capability</div>
                                        <div class="eval-meta">maturity {{ $evaluation->target_maturity !== null ? number_format((float) $evaluation->target_maturity, 2) : '-' }}</div>
                                    </td>
                                    <td>
                                        <div class="metric-value">{{ number_format((int) $evaluation->access_assignments_count) }}</div>
                                        <div class="eval-meta">direct assignment</div>
                                    </td>
                                    <td>
                                        <div>{{ $lastSavedAt?->diffForHumans() ?? '-' }}</div>
                                        <div class="eval-meta">{{ $lastSavedAt?->format('d M Y H:i') ?? '-' }}</div>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2 flex-wrap">
                                            <a href="{{ route('assessment.show', $evaluation->encrypted_id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye me-1"></i>Detail
                                            </a>
                                            <a href="{{ route('assessment.report', $evaluation->encrypted_id) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-file-alt me-1"></i>Report
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-3 py-3 border-top">
                    {{ $evaluations->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
