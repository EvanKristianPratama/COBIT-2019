@extends('layouts.app')

@section('content')
<style>
    :root {
        --evo-primary: #0f2b5c;
        --evo-accent: #0f6ad9;
        --evo-edm: #0f2b5c;
        --evo-apo: #0284c7;
        --evo-bai: #d97706;
        --evo-dss: #059669;
        --evo-mea: #dc2626;
        --evo-surface: #ffffff;
        --evo-border: #e2e8f0;
    }

    .evo-page {
        background: #f8fafc;
        padding: 24px;
        border-radius: 16px;
    }

    /* Hero Banner */
    .evo-hero {
        background: linear-gradient(135deg, #081a3d 0%, #0f2b5c 50%, #1e3a8a 100%);
        border-radius: 1.2rem;
        padding: 2rem 2.2rem;
        color: #fff;
        margin-bottom: 1.8rem;
        box-shadow: 0 16px 36px rgba(15, 43, 92, 0.12);
        position: relative;
        overflow: hidden;
    }

    .evo-hero h1 {
        font-size: 1.85rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        margin: 0;
    }

    .evo-hero p {
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.95rem;
        margin: 0.4rem 0 0 0;
        max-width: 780px;
    }

    /* Stats Grid */
    .evo-stat-card {
        background: #fff;
        border: 1px solid var(--evo-border);
        border-radius: 1rem;
        padding: 1.25rem 1.4rem;
        box-shadow: 0 4px 15px rgba(15, 43, 92, 0.04);
        display: flex;
        align-items: center;
        gap: 1.1rem;
        height: 100%;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .evo-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 25px rgba(15, 43, 92, 0.08);
    }

    .evo-stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.45rem;
        flex-shrink: 0;
    }

    .evo-stat-val {
        font-size: 1.7rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
    }

    .evo-stat-label {
        font-size: 0.82rem;
        color: #64748b;
        font-weight: 600;
        margin-top: 0.2rem;
    }

    /* Timeline / Evolution overview card */
    .evo-flow-box {
        background: #ffffff;
        border: 1px solid var(--evo-border);
        border-radius: 1.1rem;
        padding: 1.5rem 1.8rem;
        margin-bottom: 1.8rem;
        box-shadow: 0 4px 16px rgba(15, 43, 92, 0.04);
    }

    .evo-flow-step {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        position: relative;
    }

    .evo-flow-arrow {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 1.5rem;
    }

    /* Filter & Search Toolbar */
    .evo-toolbar {
        background: #fff;
        border: 1px solid var(--evo-border);
        border-radius: 1rem;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 16px rgba(15, 43, 92, 0.04);
    }

    .domain-pill {
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #475569;
        font-size: 0.82rem;
        font-weight: 700;
        padding: 0.4rem 0.9rem;
        border-radius: 999px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        user-select: none;
    }

    .domain-pill:hover {
        background: #f1f5f9;
        color: #0f172a;
    }

    .domain-pill.active {
        background: var(--evo-primary);
        color: #fff;
        border-color: var(--evo-primary);
        box-shadow: 0 4px 12px rgba(15, 43, 92, 0.2);
    }

    .status-pill {
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        color: #64748b;
        font-size: 0.78rem;
        font-weight: 700;
        padding: 0.35rem 0.8rem;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        user-select: none;
    }

    .status-pill.active {
        background: #0f172a;
        color: #fff;
        border-color: #0f172a;
    }

    /* Analog Matrix Comparison Table */
    .evo-table-wrap {
        background: #fff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(15, 23, 42, 0.05);
    }

    .evo-table {
        margin-bottom: 0;
        width: 100%;
        border-collapse: collapse;
        font-family: inherit;
    }

    .evo-table th {
        background: #f1f5f9;
        color: #1e293b;
        font-weight: 700;
        font-size: 0.8rem;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        padding: 0.75rem 0.85rem;
        border: 1px solid #cbd5e1;
        border-bottom: 2px solid #94a3b8;
    }

    .evo-table td {
        padding: 0.75rem 0.85rem;
        border: 1px solid #e2e8f0;
        font-size: 0.85rem;
        vertical-align: top;
        background-color: #ffffff;
    }

    .evo-table tbody tr:nth-child(even) td {
        background-color: #f8fafc;
    }

    .evo-table tbody tr:hover td {
        background-color: #f1f5f9;
    }

    .evo-row-new td {
        background-color: #f0fdf4 !important;
    }

    .evo-row-restructured td {
        background-color: #fffbeb !important;
    }

    /* Badges */
    .gamo-code-badge {
        font-family: monospace;
        font-size: 0.88rem;
        font-weight: 800;
        padding: 0.35rem 0.65rem;
        border-radius: 8px;
        letter-spacing: 0.04em;
        display: inline-block;
    }

    .badge-edm { background: rgba(15, 43, 92, 0.12); color: #0f2b5c; border: 1px solid rgba(15, 43, 92, 0.25); }
    .badge-apo { background: rgba(2, 132, 199, 0.12); color: #0369a1; border: 1px solid rgba(2, 132, 199, 0.25); }
    .badge-bai { background: rgba(217, 119, 6, 0.12); color: #b45309; border: 1px solid rgba(217, 119, 6, 0.25); }
    .badge-dss { background: rgba(5, 150, 105, 0.12); color: #047857; border: 1px solid rgba(5, 150, 105, 0.25); }
    .badge-mea { background: rgba(220, 38, 38, 0.12); color: #b91c1c; border: 1px solid rgba(220, 38, 38, 0.25); }

    .tag-status-new {
        background: #d1fae5;
        color: #047857;
        font-weight: 800;
        font-size: 0.72rem;
        padding: 0.25rem 0.6rem;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        border: 1px solid rgba(16, 185, 129, 0.3);
    }

    .tag-status-restructured {
        background: #fef3c7;
        color: #b45309;
        font-weight: 800;
        font-size: 0.72rem;
        padding: 0.25rem 0.6rem;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        border: 1px solid rgba(245, 158, 11, 0.3);
    }

    .tag-status-evolved {
        background: #e2e8f0;
        color: #475569;
        font-weight: 700;
        font-size: 0.72rem;
        padding: 0.25rem 0.55rem;
        border-radius: 999px;
    }

    .lineage-chip {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.45rem 0.75rem;
        font-size: 0.82rem;
        line-height: 1.4;
    }

    /* Modal */
    .lineage-modal .modal-content {
        border: none;
        border-radius: 1.25rem;
        box-shadow: 0 25px 60px rgba(15, 23, 42, 0.25);
        overflow: hidden;
    }

    .lineage-modal .modal-header {
        background: linear-gradient(135deg, #081a3d, #0f2b5c);
        color: #fff;
        padding: 1.25rem 1.75rem;
    }

    .version-box {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.2rem;
        height: 100%;
        background: #fff;
    }
</style>

<div class="container-fluid py-4 evo-page">

    <!-- Hero Banner -->
    <div class="evo-hero d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-white text-primary fw-bold px-3 py-1 rounded-pill" style="font-size: 0.76rem;">
                    <i class="fas fa-sitemap me-1"></i> MATRIKS EVOLUSI FRAMEWORK
                </span>
                <span class="badge bg-warning text-dark fw-bold px-2 py-1 rounded-pill" style="font-size: 0.76rem;">
                    ISACA COBIT Standard
                </span>
            </div>
            <h1>COBIT GAMO Mapping Evolution</h1>
            <p>
                Pelacakan silsilah dan evolusi komprehensif <strong>Governance and Management Objectives (GAMO)</strong> dari 
                <strong>COBIT 4.1 (2007)</strong> &rarr; <strong>COBIT 5 (2012)</strong> &rarr; <strong>COBIT 2019 (2019)</strong>.
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('focus-areas.index') }}" class="btn btn-light fw-bold shadow-sm px-3">
                <i class="fas fa-cubes me-1 text-primary"></i> Pilih Model
            </a>
            <a href="{{ route('cobit_component.show', 'EDM01') }}" class="btn btn-outline-light fw-bold shadow-sm px-3">
                <i class="fas fa-book-open me-1"></i> Kamus Komponen
            </a>
            <a href="{{ route('cobit_component.gamoanalysis') }}" class="btn btn-outline-light fw-bold shadow-sm px-3">
                <i class="fas fa-project-diagram me-1"></i> Analisis Alur
            </a>
        </div>
    </div>

    <!-- Summary KPI Metric Cards -->
    <div class="row g-3 mb-4">
        <!-- 2019 Total -->
        <div class="col-sm-6 col-xl">
            <div class="evo-stat-card">
                <div class="evo-stat-icon bg-primary-subtle text-primary">
                    <i class="fas fa-cubes"></i>
                </div>
                <div>
                    <div class="evo-stat-val">{{ $stats['total_2019'] }}</div>
                    <div class="evo-stat-label">COBIT 2019 GAMO</div>
                </div>
            </div>
        </div>

        <!-- COBIT 5 Total -->
        <div class="col-sm-6 col-xl">
            <div class="evo-stat-card">
                <div class="evo-stat-icon bg-success-subtle text-success">
                    <i class="fas fa-sitemap"></i>
                </div>
                <div>
                    <div class="evo-stat-val">{{ $stats['total_5'] }}</div>
                    <div class="evo-stat-label">COBIT 5 Proses</div>
                </div>
            </div>
        </div>

        <!-- COBIT 4.1 Total -->
        <div class="col-sm-6 col-xl">
            <div class="evo-stat-card">
                <div class="evo-stat-icon bg-warning-subtle text-warning">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div>
                    <div class="evo-stat-val">{{ $stats['total_4'] }}</div>
                    <div class="evo-stat-label">COBIT 4.1 Proses</div>
                </div>
            </div>
        </div>

        <!-- New in 2019 -->
        <div class="col-sm-6 col-xl">
            <div class="evo-stat-card border-success">
                <div class="evo-stat-icon bg-emerald text-white" style="background:#10b981;">
                    <i class="fas fa-star"></i>
                </div>
                <div>
                    <div class="evo-stat-val text-success">{{ $stats['new_2019'] }}</div>
                    <div class="evo-stat-label">Baru di 2019 (Data, Projects, Assurance)</div>
                </div>
            </div>
        </div>

        <!-- Restructured -->
        <div class="col-sm-6 col-xl">
            <div class="evo-stat-card border-warning">
                <div class="evo-stat-icon bg-warning text-dark">
                    <i class="fas fa-code-branch"></i>
                </div>
                <div>
                    <div class="evo-stat-val text-warning">{{ $stats['restructured'] }}</div>
                    <div class="evo-stat-label">Pemisahan Program & Proyek</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Visual Architecture Evolution Banner -->
    <div class="evo-flow-box">
        <h5 class="fw-bold text-dark mb-3">
            <i class="fas fa-history text-primary me-2"></i>Garis Waktu Pergeseran Arsitektur Framework COBIT
        </h5>
        <div class="row g-3 align-items-stretch">
            <!-- COBIT 4.1 -->
            <div class="col-md-3">
                <div class="evo-flow-step h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="badge bg-warning text-dark fw-bold">COBIT 4.1 (2007)</span>
                        <span class="small fw-bold text-muted">34 Proses</span>
                    </div>
                    <h6 class="fw-bold mb-1">Control & Audit Standard</h6>
                    <p class="small text-muted mb-2">Fokus pada kontrol internal dan audit kepatuhan berbasis 4 domain utama:</p>
                    <div class="small fw-semibold text-secondary">
                        &bull; PO (11) &middot; AI (7) &middot; DS (13) &middot; ME (4)
                    </div>
                </div>
            </div>

            <!-- Arrow 1 -->
            <div class="col-md-1 d-none d-md-flex align-items-center justify-content-center">
                <div class="evo-flow-arrow"><i class="fas fa-arrow-right"></i></div>
            </div>

            <!-- COBIT 5 -->
            <div class="col-md-3">
                <div class="evo-flow-step h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="badge bg-success text-white fw-bold">COBIT 5 (2012)</span>
                        <span class="small fw-bold text-muted">37 Proses</span>
                    </div>
                    <h6 class="fw-bold mb-1">Enterprise IT Governance</h6>
                    <p class="small text-muted mb-2">Integrasi Val IT & Risk IT. Pemisahan eksplisit antara Tata Kelola (Governance) & Manajemen:</p>
                    <div class="small fw-semibold text-success">
                        &bull; EDM (5) &middot; APO (13) &middot; BAI (10) &middot; DSS (6) &middot; MEA (3)
                    </div>
                </div>
            </div>

            <!-- Arrow 2 -->
            <div class="col-md-1 d-none d-md-flex align-items-center justify-content-center">
                <div class="evo-flow-arrow"><i class="fas fa-arrow-right"></i></div>
            </div>

            <!-- COBIT 2019 -->
            <div class="col-md-4">
                <div class="evo-flow-step h-100 border-primary" style="background: #eff6ff;">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="badge bg-primary text-white fw-bold">COBIT 2019 (Current)</span>
                        <span class="small fw-bold text-primary">40 GAMO (+3 Baru)</span>
                    </div>
                    <h6 class="fw-bold text-primary mb-1">Tailored Modular Governance</h6>
                    <p class="small text-muted mb-2">Penambahan Tata Kelola Data (APO14), Manajemen Proyek Mandiri (BAI11), dan Penjaminan Independen (MEA04):</p>
                    <div class="small fw-bold text-dark">
                        &bull; EDM (5) &middot; APO (14) &middot; BAI (11) &middot; DSS (6) &middot; MEA (4)
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="evo-toolbar">
        <div class="row g-3 align-items-center justify-content-between">
            <!-- Search -->
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" id="evoSearchInput" class="form-control border-start-0 ps-0" placeholder="Cari kode (APO14), judul, atau kata kunci..." oninput="filterEvoRows()">
                </div>
            </div>

            <!-- Status Filter -->
            <div class="col-md-8 text-md-end">
                <div class="d-flex gap-2 flex-wrap justify-content-md-end align-items-center">
                    <span class="small text-muted fw-bold me-1">Status:</span>
                    <span class="status-pill active" onclick="setStatusFilter('all', this)">Semua (40)</span>
                    <span class="status-pill text-success" onclick="setStatusFilter('new', this)">
                        <i class="fas fa-star me-1"></i> Baru di 2019 (3)
                    </span>
                    <span class="status-pill text-warning" onclick="setStatusFilter('restructured', this)">
                        <i class="fas fa-code-branch me-1"></i> Restrukturisasi (1)
                    </span>
                    <span class="status-pill" onclick="setStatusFilter('evolved', this)">Evolusi COBIT 5 (36)</span>
                </div>
            </div>

            <!-- Domain Pills -->
            <div class="col-12 pt-2 border-top">
                <div class="d-flex gap-2 flex-wrap align-items-center">
                    <span class="small text-muted fw-bold me-1">Domain:</span>
                    <span class="domain-pill active" onclick="setDomainFilter('ALL', this)">
                        Semua Domain (40)
                    </span>
                    @foreach($domains as $domKey => $dom)
                        <span class="domain-pill" onclick="setDomainFilter('{{ $domKey }}', this)">
                            <span class="badge {{ 'badge-' . strtolower($domKey) }} px-2 py-1">{{ $domKey }}</span>
                            <span>{{ $dom['name'] }}</span>
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill">{{ $dom['count'] }}</span>
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Comparison Matrix Table -->
    <div class="evo-table-wrap">
        <div class="table-responsive">
            <table class="table evo-table align-middle" id="evoTable">
                <thead>
                    <tr>
                        <th style="width: 20%;">COBIT 4.1 (2007)</th>
                        <th style="width: 22%;">COBIT 5 (2012)</th>
                        <th style="width: 25%;">COBIT 2019 (GAMO)</th>
                        <th style="width: 7%; text-align: center;">Domain</th>
                        <th style="width: 20%;">Catatan Perubahan &amp; Evolusi</th>
                        <th style="width: 6%; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="evoTableBody">
                    @forelse($gamoList as $item)
                        @php
                            $rowClass = $item['status'] === 'new' ? 'evo-row-new' : ($item['status'] === 'restructured' ? 'evo-row-restructured' : '');
                            $domainBadge = 'badge-' . strtolower($item['domain']);
                            $c4Empty = ($item['cobit4_id'] === '-' || empty(trim($item['cobit4_id'], '- ')));
                            $c5Empty = ($item['cobit5_id'] === '-' || $item['cobit5_id'] === 'Belum ada' || empty(trim($item['cobit5_id'], '- ')));
                        @endphp
                        <tr class="evo-row {{ $rowClass }}"
                            data-domain="{{ $item['domain'] }}"
                            data-status="{{ $item['status'] }}"
                            data-code="{{ strtolower($item['id']) }}"
                            data-title="{{ strtolower($item['title_2019']) }}"
                            data-c5="{{ strtolower($item['cobit5_title']) }}"
                            data-c4="{{ strtolower($item['cobit4_title']) }}"
                            data-notes="{{ strtolower($item['notes']) }}">
                            <!-- 1. COBIT 4.1 (Kiri) -->
                            <td class="align-top">
                                @if($c4Empty)
                                    <span class="text-muted fw-light">-</span>
                                @else
                                    <div class="fw-bold font-monospace text-dark" style="font-size: 0.85rem;">{{ $item['cobit4_id'] }}</div>
                                    <div class="text-secondary small" style="font-size: 0.82rem; line-height: 1.35;">{{ $item['cobit4_title'] }}</div>
                                @endif
                            </td>

                            <!-- 2. COBIT 5 (Tengah) -->
                            <td class="align-top">
                                @if($c5Empty)
                                    <span class="text-muted fw-light">-</span>
                                @else
                                    <div class="fw-bold font-monospace text-dark" style="font-size: 0.85rem;">{{ $item['cobit5_id'] }}</div>
                                    <div class="text-secondary small" style="font-size: 0.82rem; line-height: 1.35;">{{ $item['cobit5_title'] }}</div>
                                @endif
                            </td>

                            <!-- 3. COBIT 2019 GAMO -->
                            <td class="align-top">
                                <div class="d-flex align-items-center gap-1 mb-1">
                                    <span class="fw-bold font-monospace text-dark" style="font-size: 0.88rem;">{{ $item['id'] }}</span>
                                    @if($item['status'] === 'new')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.68rem; padding: 2px 5px;">Baru</span>
                                    @elseif($item['status'] === 'restructured')
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size: 0.68rem; padding: 2px 5px;">Restruktur</span>
                                    @endif
                                </div>
                                <div class="fw-semibold text-primary" style="font-size: 0.85rem; line-height: 1.35;">{{ $item['title_2019'] }}</div>
                            </td>

                            <!-- 4. Domain (Di sebelah COBIT 2019 paling ujung) -->
                            <td class="align-top text-center">
                                <span class="badge {{ $domainBadge }} px-2 py-1 font-monospace fw-bold" style="font-size: 0.78rem;">
                                    {{ $item['domain'] }}
                                </span>
                            </td>

                            <!-- 5. Catatan Perubahan & Evolusi -->
                            <td class="align-top">
                                <div class="text-secondary small" style="font-size: 0.8rem; line-height: 1.45;">
                                    {{ $item['notes'] }}
                                </div>
                            </td>

                            <!-- 6. Aksi -->
                            <td class="align-top text-center">
                                <div class="d-flex flex-column gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 fw-semibold" style="font-size: 0.74rem;" onclick="showLineageModal({{ json_encode($item) }})" title="Lihat Silsilah Lengkap">
                                        Detail
                                    </button>
                                    <a href="{{ route('cobit_component.show', $item['id']) }}" class="btn btn-sm btn-light border py-0 px-2 text-secondary fw-semibold" style="font-size: 0.72rem;" title="Buka Kamus Komponen">
                                        Kamus
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                Data evolusi tidak ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div id="noResultsMsg" class="p-5 text-center text-muted" style="display: none;">
            <i class="fas fa-search fa-2x mb-3 text-secondary"></i>
            <h5>Tidak ada objektif GAMO yang cocok dengan filter</h5>
            <p class="small">Coba reset filter atau gunakan kata kunci pencarian yang lain.</p>
            <button class="btn btn-sm btn-outline-primary fw-bold mt-2" onclick="resetAllFilters()">Reset Filter</button>
        </div>
    </div>
</div>

<!-- LINEAGE & EVOLUTION DETAIL MODAL -->
<div class="modal fade lineage-modal" id="lineageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <span class="badge bg-white text-primary fw-bold px-2 py-1 mb-1" id="mDomainBadge">EDM</span>
                    <h5 class="modal-title fw-bold text-white mb-0" id="mModalTitle">EDM01 - Detail Evolusi GAMO</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Evolution Notes Callout -->
                <div class="alert alert-info border-info-subtle shadow-sm mb-4">
                    <h6 class="fw-bold mb-1 text-primary">
                        <i class="fas fa-lightbulb me-1"></i> Catatan Perubahan &amp; Evolusi ISACA
                    </h6>
                    <p class="mb-0 small" id="mNotes" style="line-height: 1.6;"></p>
                </div>

                <!-- 3 Versions Comparison Grid: COBIT 4.1 -> COBIT 5 -> COBIT 2019 -->
                <div class="row g-3 mb-3">
                    <!-- 1. COBIT 4.1 (2007) -->
                    <div class="col-md-4">
                        <div class="version-box border-warning" style="background:#fffdf5;">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-warning text-dark fw-bold">1. COBIT 4.1 (2007)</span>
                                <span class="small text-muted fw-bold">34 Proses</span>
                            </div>
                            <h6 class="fw-bold text-warning-emphasis mb-1" id="m4Id"></h6>
                            <div class="fw-bold text-dark mb-2" id="m4Title" style="font-size: 0.88rem;"></div>
                            <p class="small text-muted mb-0" style="line-height: 1.45;">
                                Standar audit dan pengendalian internal TI berorientasi kontrol proses internal.
                            </p>
                        </div>
                    </div>

                    <!-- 2. COBIT 5 (2012) -->
                    <div class="col-md-4">
                        <div class="version-box border-success" style="background:#f0fdf4;">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-success text-white fw-bold">2. COBIT 5 (2012)</span>
                                <span class="small text-muted fw-bold">37 Proses</span>
                            </div>
                            <h6 class="fw-bold text-success mb-1" id="m5Id"></h6>
                            <div class="fw-bold text-dark mb-2" id="m5Title" style="font-size: 0.88rem;"></div>
                            <p class="small text-muted mb-0" style="line-height: 1.45;">
                                Kerangka kerja tata kelola TI enterprise komprehensif mengintegrasikan Val IT &amp; Risk IT.
                            </p>
                        </div>
                    </div>

                    <!-- 3. COBIT 2019 (Current) -->
                    <div class="col-md-4">
                        <div class="version-box border-primary" style="background:#eff6ff;">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-primary text-white fw-bold">3. COBIT 2019 GAMO</span>
                                <span id="mStatusBadge"></span>
                            </div>
                            <h6 class="fw-bold text-primary mb-1" id="m2019Id"></h6>
                            <div class="fw-bold text-dark mb-2" id="m2019Title" style="font-size: 0.88rem;"></div>
                            <p class="small text-muted mb-0" id="m2019Desc" style="line-height: 1.45;"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                <a href="#" id="mKamusBtn" class="btn btn-primary fw-bold">
                    <i class="fas fa-book-open me-1"></i> Buka di Kamus Komponen
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    let activeDomain = 'ALL';
    let activeStatus = 'all';

    function setDomainFilter(dom, elem) {
        activeDomain = dom;
        document.querySelectorAll('.domain-pill').forEach(p => p.classList.remove('active'));
        if (elem) elem.classList.add('active');
        filterEvoRows();
    }

    function setStatusFilter(status, elem) {
        activeStatus = status;
        document.querySelectorAll('.status-pill').forEach(p => p.classList.remove('active'));
        if (elem) elem.classList.add('active');
        filterEvoRows();
    }

    function filterEvoRows() {
        const query = document.getElementById('evoSearchInput').value.trim().toLowerCase();
        const rows = document.querySelectorAll('.evo-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const rowDomain = row.getAttribute('data-domain');
            const rowStatus = row.getAttribute('data-status');
            const code = row.getAttribute('data-code') || '';
            const title = row.getAttribute('data-title') || '';
            const c5 = row.getAttribute('data-c5') || '';
            const c4 = row.getAttribute('data-c4') || '';
            const notes = row.getAttribute('data-notes') || '';

            const matchDomain = (activeDomain === 'ALL' || rowDomain === activeDomain);
            const matchStatus = (activeStatus === 'all' || rowStatus === activeStatus);
            const matchSearch = (!query || code.includes(query) || title.includes(query) || c5.includes(query) || c4.includes(query) || notes.includes(query));

            if (matchDomain && matchStatus && matchSearch) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const noMsg = document.getElementById('noResultsMsg');
        if (visibleCount === 0) {
            noMsg.style.display = 'block';
        } else {
            noMsg.style.display = 'none';
        }
    }

    function resetAllFilters() {
        document.getElementById('evoSearchInput').value = '';
        activeDomain = 'ALL';
        activeStatus = 'all';
        document.querySelectorAll('.domain-pill').forEach((p, idx) => {
            if (idx === 0) p.classList.add('active'); else p.classList.remove('active');
        });
        document.querySelectorAll('.status-pill').forEach((p, idx) => {
            if (idx === 0) p.classList.add('active'); else p.classList.remove('active');
        });
        filterEvoRows();
    }

    function showLineageModal(item) {
        document.getElementById('mDomainBadge').textContent = item.domain;
        document.getElementById('mModalTitle').textContent = `${item.id} - ${item.title_2019}`;
        document.getElementById('mNotes').textContent = item.notes;

        document.getElementById('m2019Id').textContent = item.id;
        document.getElementById('m2019Title').textContent = item.title_2019;
        document.getElementById('m2019Desc').textContent = item.desc_2019;

        const sBadge = document.getElementById('mStatusBadge');
        if (item.status === 'new') {
            sBadge.className = 'tag-status-new';
            sBadge.innerHTML = '<i class="fas fa-sparkles"></i> BARU DI 2019';
        } else if (item.status === 'restructured') {
            sBadge.className = 'tag-status-restructured';
            sBadge.innerHTML = '<i class="fas fa-cut"></i> RESTRUKTURISASI';
        } else {
            sBadge.className = 'tag-status-evolved';
            sBadge.innerHTML = '<i class="fas fa-check-circle text-success me-1"></i> Evolusi COBIT 5';
        }

        if (!item.cobit5_id || item.cobit5_id === '-' || item.cobit5_id === 'Belum ada') {
            document.getElementById('m5Id').textContent = '-';
            document.getElementById('m5Title').textContent = 'Proses ini belum tersedia di COBIT 5';
        } else {
            document.getElementById('m5Id').textContent = item.cobit5_id;
            document.getElementById('m5Title').textContent = item.cobit5_title;
        }

        if (!item.cobit4_id || item.cobit4_id === '-') {
            document.getElementById('m4Id').textContent = '-';
            document.getElementById('m4Title').textContent = 'Domain atau proses ini belum ada di COBIT 4.1';
        } else {
            document.getElementById('m4Id').textContent = item.cobit4_id;
            document.getElementById('m4Title').textContent = item.cobit4_title;
        }

        document.getElementById('mKamusBtn').href = `{{ url('/objectives') }}/${item.id}`;

        new bootstrap.Modal(document.getElementById('lineageModal')).show();
    }
</script>
@endsection
