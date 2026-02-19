@extends('layouts.app')

@section('content')
<div class="container">
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
                <h1 class="h2 fw-bold mb-2">Kelola Kode Assessment</h1>
                <p class="text-muted mb-0">Kelola kode assessment dan instansi terkait</p>
            </div>
            <a href="{{ url('admin/requests') }}" class="btn btn-primary d-flex align-items-center">
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
                    <form action="{{ route('admin.assessments.index') }}" method="POST">
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
                                <label for="instansi" class="form-label small text-muted mb-1">Instansi Default</label>
                                <input type="text" id="instansi" name="instansi"
                                    class="form-control @error('instansi') is-invalid @enderror"
                                    value="{{ old('instansi') }}" required>
                                @error('instansi')
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
                    <form action="{{ route('admin.assessments.index') }}" method="GET">
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
                                <label class="form-label small text-muted mb-1">Instansi</label>
                                <input type="text" name="instansi" class="form-control" 
                                    placeholder="Cari Instansi" value="{{ request('instansi') }}">
                            </div>
                            <div class="col-12 mt-2">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-secondary flex-grow-1">
                                        <i class="fas fa-filter me-2"></i>Terapkan Filter
                                    </button>
                                    <a href="{{ route('admin.assessments.index') }}" class="btn btn-outline-secondary flex-grow-1">
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
                                <h3 class="mb-0">{{ $assessments->unique('instansi')->count() }}</h3>
                                <p class="text-muted small mb-0">Instansi Unik</p>
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
                                <th class="py-3">Instansi</th>
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
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span>{{ $a->created_at->format('d M Y') }}</span>
                                            <small class="text-muted">{{ $a->created_at->format('H:i') }}</small>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.assessments.show', $a->assessment_id) }}" 
                                           class="btn btn-sm btn-outline-secondary px-3">
                                            <i class="fas fa-eye me-1"></i>Detail
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
