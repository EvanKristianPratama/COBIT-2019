@extends('layouts.admin')

@section('admin_title', 'Manage Assessment Code')

@section('admin_content')
<style>
    .assessment-dashboard-page {
        --assessment-primary: var(--cobit-primary);
        --assessment-primary-soft: rgba(15, 43, 92, 0.08);
        --assessment-secondary: var(--cobit-secondary);
        --assessment-secondary-soft: rgba(26, 61, 107, 0.1);
        --assessment-success: var(--cobit-secondary);
        --assessment-success-soft: rgba(26, 61, 107, 0.1);
        --assessment-info: var(--cobit-accent);
        --assessment-info-soft: rgba(15, 106, 217, 0.1);
        --assessment-warning: var(--cobit-accent);
        --assessment-warning-soft: rgba(15, 106, 217, 0.1);
        --assessment-border: #d7dfeb;
        --assessment-muted: #64748b;
    }

    .assessment-dashboard-page .card {
        border: 1px solid var(--assessment-border) !important;
        border-radius: 22px;
        box-shadow: 0 16px 34px rgba(15, 23, 42, 0.05);
    }

    .assessment-dashboard-page .page-header h1,
    .assessment-dashboard-page .page-header .h2 {
        color: #0f172a;
    }

    .assessment-dashboard-page .text-muted {
        color: var(--assessment-muted) !important;
    }

    .assessment-dashboard-page .card-header.bg-primary {
        background: linear-gradient(135deg, var(--cobit-primary) 0%, var(--cobit-secondary) 100%) !important;
    }

    .assessment-dashboard-page .card-header.bg-secondary {
        background: linear-gradient(135deg, var(--cobit-secondary) 0%, var(--cobit-accent) 100%) !important;
    }

    .assessment-dashboard-page .btn-primary {
        background: var(--cobit-primary);
        border-color: var(--cobit-primary);
    }

    .assessment-dashboard-page .btn-primary:hover,
    .assessment-dashboard-page .btn-primary:focus {
        background: var(--cobit-secondary);
        border-color: var(--cobit-secondary);
    }

    .assessment-dashboard-page .btn-secondary {
        background: var(--cobit-secondary);
        border-color: var(--cobit-secondary);
    }

    .assessment-dashboard-page .btn-secondary:hover,
    .assessment-dashboard-page .btn-secondary:focus {
        background: var(--cobit-accent);
        border-color: var(--cobit-accent);
    }

    .assessment-dashboard-page .btn-outline-secondary {
        color: var(--cobit-secondary);
        border-color: #c7d2de;
    }

    .assessment-dashboard-page .btn-outline-secondary:hover {
        background: #f8fafc;
        color: var(--cobit-secondary);
        border-color: #b8c7d6;
    }

    .assessment-dashboard-page .bg-primary.bg-opacity-10 {
        background: var(--assessment-primary-soft) !important;
    }

    .assessment-dashboard-page .bg-success.bg-opacity-10 {
        background: var(--assessment-success-soft) !important;
    }

    .assessment-dashboard-page .bg-info.bg-opacity-10 {
        background: var(--assessment-info-soft) !important;
    }

    .assessment-dashboard-page .bg-warning.bg-opacity-10 {
        background: var(--assessment-warning-soft) !important;
    }

    .assessment-dashboard-page .text-primary {
        color: var(--assessment-primary) !important;
    }

    .assessment-dashboard-page .text-success {
        color: var(--assessment-success) !important;
    }

    .assessment-dashboard-page .text-info {
        color: var(--assessment-info) !important;
    }

    .assessment-dashboard-page .text-warning {
        color: var(--assessment-warning) !important;
    }

    .assessment-dashboard-page .badge.bg-primary.bg-opacity-10 {
        background: var(--assessment-primary-soft) !important;
        color: var(--assessment-primary) !important;
    }

    .assessment-dashboard-page .badge.bg-info.bg-opacity-10 {
        background: var(--assessment-info-soft) !important;
        color: var(--assessment-info) !important;
    }
</style>

<div class="container-fluid px-0 assessment-dashboard-page">
    {{-- Alert Messages --}}
    <div class="alert-container mb-4">
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    {{-- Page Header --}}
    <div class="page-header mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div class="mb-3 mb-md-0">
                <h1 class="h2 fw-bold mb-2">Kelola Kode Design Factor Assessment</h1>
            </div>
            <a href="{{ route('admin.design-assessments.requests') }}" class="btn btn-primary d-flex align-items-center">
                <i class="fas fa-list me-2"></i> Cek Daftar Request
            </a>
        </div>
    </div>

    {{-- Forms Grid --}}
    <div class="row g-4 mb-4">
        {{-- Create Form --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="m-0 fw-medium">Buat Kode Baru</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.design-assessments.store') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label for="kode_assessment" class="form-label small text-muted mb-1">Kode Assessment</label>
                                <input type="text" id="kode_assessment" name="kode_assessment"
                                    class="form-control @error('kode_assessment') is-invalid @enderror"
                                    value="{{ old('kode_assessment') }}" required>
                                @error('kode_assessment')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-5">
                                <label for="organization_id" class="form-label small text-muted mb-1">Organisasi</label>
                                <select id="organization_id" name="organization_id"
                                    class="form-select @error('organization_id') is-invalid @enderror" required>
                                    <option value="">Pilih organisasi</option>
                                    @foreach($organizationCatalog as $organizationOption)
                                        <option value="{{ $organizationOption->organization_id }}" {{ (string) old('organization_id') === (string) $organizationOption->organization_id ? 'selected' : '' }}>
                                            {{ $organizationOption->organization_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('organization_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-save me-2"></i>Simpan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Filter Form --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-secondary text-white py-3">
                    <h6 class="m-0 fw-medium">Filter Data</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.design-assessments.index') }}" method="GET">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small text-muted mb-1">ID</label>
                                <input type="text" name="id" class="form-control" 
                                    placeholder="Cari ID" value="{{ request('id') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted mb-1">Kode</label>
                                <input type="text" name="kode_assessment" class="form-control" 
                                    placeholder="Cari Kode" value="{{ request('kode_assessment') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted mb-1">Organisasi</label>
                                <select name="organization_id" class="form-select">
                                    <option value="">Semua organisasi</option>
                                    @foreach($organizationCatalog as $organizationOption)
                                        <option value="{{ $organizationOption->organization_id }}" {{ (string) request('organization_id') === (string) $organizationOption->organization_id ? 'selected' : '' }}>
                                            {{ $organizationOption->organization_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 mt-2">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-secondary flex-grow-1">
                                        <i class="fas fa-filter me-2"></i>Terapkan Filter
                                    </button>
                                    <a href="{{ route('admin.design-assessments.index') }}" class="btn btn-outline-secondary flex-grow-1">
                                        <i class="fas fa-times me-2"></i>Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Summary --}}
    <div class="stats-grid mb-4">
        <div class="row g-4">
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                                <i class="fas fa-database fa-xl text-primary"></i>
                            </div>
                            <div class="ms-3">
                                <h3 class="mb-0">{{ $assessments->count() }}</h3>
                                <p class="text-muted small mb-0">Total Assessment</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                                <i class="fas fa-calendar-plus fa-xl text-success"></i>
                            </div>
                            <div class="ms-3">
                                <h3 class="mb-0">{{ $assessments->where('created_at', '>=', now()->subMonth())->count() }}</h3>
                                <p class="text-muted small mb-0">Bulan Ini</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-info bg-opacity-10 p-3 rounded-circle">
                                <i class="fas fa-clock fa-xl text-info"></i>
                            </div>
                            <div class="ms-3">
                                <h3 class="mb-0">{{ $assessments->where('created_at', '>=', now()->subWeek())->count() }}</h3>
                                <p class="text-muted small mb-0">Minggu Ini</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-warning bg-opacity-10 p-3 rounded-circle">
                                <i class="fas fa-sync fa-xl text-warning"></i>
                            </div>
                            <div class="ms-3">
                                <h3 class="mb-0">{{ $assessments->unique(fn ($assessment) => $assessment->organization_id ?: strtolower((string) $assessment->instansi))->count() }}</h3>
                                <p class="text-muted small mb-0">Organisasi Unik</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-secondary text-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-medium">Daftar Kode Assessment</h6>
                <span class="badge bg-light text-dark">{{ $assessments->count() }} Data</span>
            </div>
        </div>
        <div class="card-body p-0">
            @if($assessments->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-database fa-3x text-muted mb-3"></i>
                    <p class="h5 text-muted mb-3">Belum ada data assessment</p>
                    <a href="#" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Buat Kode Baru
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="py-3 ps-4">ID</th>
                                <th class="py-3">Kode</th>
                                <th class="py-3">Organisasi</th>
                                <th class="py-3 text-center">Assigned Users</th>
                                <th class="py-3">Dibuat Pada</th>
                                <th class="py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assessments as $a)
                                <tr>
                                    <td class="ps-4 fw-medium">{{ $a->assessment_id }}</td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                                            {{ $a->kode_assessment }}
                                        </span>
                                    </td>
                                    <td class="text-truncate" style="max-width: 200px" title="{{ $a->instansi }}">
                                        {{ $a->instansi }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info bg-opacity-10 text-info px-3 py-2">
                                            {{ $a->access_assignments_count ?? 0 }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span>{{ $a->created_at->format('d M Y') }}</span>
                                            <small class="text-muted">{{ $a->created_at->format('H:i') }}</small>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.design-assessments.show', $a->assessment_id) }}" 
                                           class="btn btn-sm btn-outline-secondary px-3">
                                            <i class="fas fa-shield-alt me-1"></i>Manage
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        {{-- Table Footer --}}
        <div class="card-footer bg-light py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Total: {{ $assessments->count() }} Data Assessment
                </div>
                <div class="text-muted small">
                    Diperbarui: {{ now()->format('d M Y H:i') }}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .alert-container .alert {
        border-left: 4px solid transparent;
        border-radius: 0.5rem;
    }
    .alert-danger {
        border-left-color: #dc3545 !important;
    }
    .alert-success {
        border-left-color: #198754 !important;
    }
    .card {
        border-radius: 0.75rem;
        overflow: hidden;
    }
    .card-header {
        border-radius: 0 !important;
    }
    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        color: #6c757d;
        background-color: #f8f9fa;
    }
    .table tbody tr {
        transition: background-color 0.2s;
        border-bottom: 1px solid #eff2f7;
    }
    .table tbody tr:last-child {
        border-bottom: none;
    }
    .table tbody tr:hover {
        background-color: #f8f9fa;
    }
    .stats-grid .card-body {
        padding: 1.25rem;
    }
    .page-header {
        padding: 0.5rem 0;
        margin-bottom: 1.5rem;
    }
    .table-responsive {
        max-height: 500px;
        overflow-y: auto;
    }
    .table thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: #f8f9fa;
    }
</style>
@endsection
