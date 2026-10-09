@extends('layouts.app')

@section('content')
<style>
    /* ==========================================================================
       COBIT 4.1 AUTHENTIC MANUAL & DATABASE EDITING STYLING
       ========================================================================== */

    :root {
        --c4-navy-dark: #072b4c;
        --c4-navy: #0a3d68;
        --c4-blue: #13558c;
        --c4-blue-light: #2c75b0;
        --c4-accent: #357bb9;
        --c4-primary-box: #1a4b80;
        --c4-secondary-box: #7da0c5;
        --c4-neutral-box: #d9e4f0;
        --c4-border: #9ab4cf;
        --c4-text-dark: #111827;
        --c4-text-muted: #374151;
        --c4-font-serif: 'Georgia', 'Times New Roman', serif;
        --c4-font-sans: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    .c4-wrapper {
        background-color: #f1f5f9;
        min-height: 100vh;
        padding: 1.5rem 0 3.5rem;
        font-family: var(--c4-font-sans);
        color: var(--c4-text-dark);
    }

    .c4-toolbar {
        background: #ffffff;
        border-radius: 12px;
        padding: 0.85rem 1.25rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        margin-bottom: 1.25rem;
        border: 1px solid #e2e8f0;
    }

    /* Floating / Sticky Input Mode Action Bar */
    .c4-input-mode-banner {
        background: linear-gradient(90deg, #fffbeb 0%, #fef3c7 100%);
        border: 2px solid #f59e0b;
        border-radius: 10px;
        padding: 0.75rem 1.25rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.15);
        display: none;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .c4-input-mode-banner.active {
        display: flex;
    }

    /* Tab Navigation for Book Pages */
    .c4-nav-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 0.75rem;
        margin-bottom: 1.5rem;
    }

    .c4-nav-btn {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        color: #334155;
        padding: 0.55rem 1.15rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.88rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        transition: all 0.15s ease-in-out;
    }

    .c4-nav-btn:hover {
        background: #f8fafc;
        border-color: var(--c4-blue);
        color: var(--c4-blue);
    }

    .c4-nav-btn.active {
        background: var(--c4-navy);
        border-color: var(--c4-navy);
        color: #ffffff;
        box-shadow: 0 2px 8px rgba(10, 61, 104, 0.25);
    }

    /* The Authentic White Book Page (Paper Layout) */
    .c4-book-page {
        background: #ffffff;
        border-radius: 4px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        border: 1px solid #d1d5db;
        padding: 0;
        margin-bottom: 2.5rem;
        overflow: hidden;
        position: relative;
    }

    /* Page Header Banner */
    .c4-header-banner {
        background: linear-gradient(90deg, #072b4c 0%, #0a3d68 65%, #13558c 100%);
        color: #ffffff;
        padding: 0.9rem 2rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 82px;
        border-bottom: 3px solid #041a2e;
        position: relative;
    }

    .c4-header-banner.banner-left {
        justify-content: flex-start;
        gap: 1.5rem;
    }

    .c4-header-banner.banner-right {
        justify-content: flex-end;
        gap: 1.5rem;
    }

    .c4-banner-code {
        font-family: var(--c4-font-sans);
        font-size: 3.2rem;
        font-weight: 900;
        line-height: 1;
        letter-spacing: -0.04em;
        color: #2b74b3;
        text-shadow: 0 1px 2px rgba(0,0,0,0.3);
    }

    .c4-banner-info {
        text-align: right;
    }

    .c4-header-banner.banner-left .c4-banner-info {
        text-align: left;
    }

    .c4-banner-domain {
        font-size: 1.35rem;
        font-weight: 800;
        letter-spacing: -0.01em;
        line-height: 1.15;
    }

    .c4-banner-title {
        font-size: 1.05rem;
        font-weight: 500;
        color: rgba(255, 255, 255, 0.92);
        margin-top: 2px;
    }

    /* Page Body Content */
    .c4-page-content {
        padding: 2rem 2.5rem 3rem;
    }

    .c4-serif-heading {
        font-family: var(--c4-font-serif);
        font-size: 1.55rem;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        font-weight: 600;
        text-align: center;
        color: #111827;
        margin: 0.25rem 0 1.25rem;
    }

    .c4-process-title {
        font-size: 1.18rem;
        font-weight: 800;
        color: #111827;
        margin-bottom: 0.85rem;
    }

    .c4-process-desc {
        font-size: 0.95rem;
        line-height: 1.6;
        color: #1f2937;
        text-align: justify;
        margin-bottom: 1.75rem;
    }

    /* Criteria Slanted Table */
    .c4-criteria-table-wrap {
        display: inline-block;
    }

    .c4-slanted-col-header {
        height: 85px;
        width: 32px;
        vertical-align: bottom;
        padding: 0 4px 6px 0;
        white-space: nowrap;
    }

    .c4-slanted-label {
        display: inline-block;
        transform: rotate(-50deg);
        transform-origin: bottom left;
        font-size: 0.78rem;
        font-weight: 700;
        color: #1e3a5f;
    }

    .c4-criteria-cell {
        width: 32px;
        height: 28px;
        border: 1px solid #7099c2;
        background: #f0f5fa;
        text-align: center;
        vertical-align: middle;
        font-weight: 900;
        font-size: 0.95rem;
        color: #072b4c;
        cursor: pointer;
        user-select: none;
        transition: background 0.15s, color 0.15s;
    }

    .c4-criteria-cell.p-active {
        background: #13467b;
        color: #ffffff;
    }

    .c4-criteria-cell.s-active {
        background: #8bb4de;
        color: #0a2540;
    }

    /* Domain Buttons Stack */
    .c4-domain-btn-stack {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        max-width: 175px;
        margin-left: auto;
    }

    .c4-domain-btn {
        background: linear-gradient(180deg, #1f5d94 0%, #0d3862 100%);
        color: #ffffff;
        font-weight: 700;
        font-size: 0.82rem;
        line-height: 1.25;
        text-align: center;
        padding: 0.5rem 0.65rem;
        border-radius: 8px;
        border: 2px solid #5a8ab8;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        cursor: pointer;
        transition: transform 0.1s, filter 0.15s;
    }

    .c4-domain-btn:hover {
        filter: brightness(1.12);
    }

    .c4-domain-btn.active {
        border-color: #ffd166;
        box-shadow: 0 0 0 3px rgba(255, 209, 102, 0.65);
        transform: scale(1.02);
    }

    /* Control Statement / Waterfall Section */
    .c4-waterfall-box {
        margin: 1.75rem 0 2rem;
        line-height: 1.6;
        font-size: 0.95rem;
    }

    .c4-waterfall-step {
        margin-bottom: 0.9rem;
    }

    .c4-waterfall-label {
        font-weight: 800;
        color: #111827;
        display: block;
        margin-bottom: 0.15rem;
    }

    .c4-waterfall-text {
        color: #1f2937;
        padding-left: 0.5rem;
    }

    .c4-waterfall-bullets {
        margin: 0.35rem 0 0.5rem;
        padding-left: 1.75rem;
        color: #1f2937;
    }

    .c4-waterfall-bullets li {
        margin-bottom: 0.25rem;
    }

    /* Bottom: IT Gov Focus (Pentagon) & IT Resources */
    .c4-bottom-grid {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-top: 2rem;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    /* Authentic IT Governance Pentagon Diagram Styling */
    .c4-pentagon-wrap {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        background: #ffffff;
        padding: 0.75rem 1rem 0.5rem;
        border-radius: 8px;
    }

    .c4-pentagon-svg {
        filter: drop-shadow(0px 8px 20px rgba(15, 23, 42, 0.28));
        overflow: visible;
        user-select: none;
    }

    .c4-seg-poly {
        stroke: #1c2833;
        stroke-width: 1.8;
        stroke-linejoin: round;
        transition: fill 0.2s ease, filter 0.15s ease;
        cursor: default;
    }

    .input-mode-on .c4-seg-poly {
        cursor: pointer;
    }

    .input-mode-on .c4-seg-poly:hover {
        filter: brightness(1.1);
    }

    .c4-seg-text {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        font-size: 8.5px;
        font-weight: 800;
        text-anchor: middle;
        pointer-events: none;
        letter-spacing: 0.03em;
        line-height: 1;
    }

    .c4-pentagon-legend {
        display: flex;
        gap: 1.8rem;
        justify-content: center;
        align-items: center;
        margin-top: 0.85rem;
        font-size: 0.84rem;
        font-weight: 700;
        color: #1e293b;
    }

    .c4-legend-box {
        width: 14px;
        height: 14px;
        display: inline-block;
        border: 1px solid #0f172a;
        border-radius: 2px;
    }

    .c4-resources-table-wrap {
        display: inline-block;
        margin-left: auto;
    }

    .c4-res-cell {
        width: 32px;
        height: 28px;
        border: 1px solid #7099c2;
        background: #f0f5fa;
        text-align: center;
        vertical-align: middle;
        font-weight: 900;
        font-size: 1rem;
        color: #072b4c;
        cursor: pointer;
        user-select: none;
    }

    /* Page 2: Control Objectives List */
    .c4-obj-item {
        margin-bottom: 1.5rem;
    }

    .c4-obj-item-title {
        font-weight: 800;
        font-size: 1rem;
        color: #111827;
        margin-bottom: 0.25rem;
    }

    .c4-obj-item-desc {
        font-size: 0.92rem;
        line-height: 1.55;
        color: #1f2937;
        text-align: justify;
    }

    /* Page 3: Inputs, Outputs, RACI, Goals & Metrics */
    .c4-io-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.85rem;
        margin-bottom: 1.75rem;
    }

    .c4-io-table th {
        background: #0a3d68;
        color: #ffffff;
        padding: 0.45rem 0.65rem;
        font-weight: 700;
        border: 1px solid #072b4c;
    }

    .c4-io-table td {
        padding: 0.4rem 0.65rem;
        border: 1px solid #cbd5e1;
        vertical-align: middle;
    }

    .c4-io-table tr:nth-child(even) td {
        background: #f8fafc;
    }

    /* RACI Chart Table */
    .c4-raci-table-wrap {
        overflow-x: auto;
        margin-bottom: 2rem;
    }

    .c4-raci-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.82rem;
    }

    .c4-raci-table th.raci-header-role {
        height: 130px;
        width: 36px;
        min-width: 36px;
        vertical-align: bottom;
        padding: 0 4px 8px 0;
        white-space: nowrap;
    }

    .c4-raci-table th.raci-header-role span {
        display: inline-block;
        transform: rotate(-60deg);
        transform-origin: bottom left;
        font-size: 0.78rem;
        font-weight: 700;
        color: #072b4c;
    }

    .c4-raci-activity-cell {
        padding: 0.45rem 0.75rem;
        font-weight: 500;
        color: #1e293b;
        border: 1px solid #cbd5e1;
        font-size: 0.84rem;
    }

    .c4-raci-val-cell {
        width: 36px;
        height: 32px;
        text-align: center;
        vertical-align: middle;
        font-weight: 800;
        font-size: 0.9rem;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        cursor: pointer;
        user-select: none;
    }

    .c4-raci-val-cell:hover { background: #f1f5f9; }
    .c4-raci-val-cell.has-r { background: #fee2e2; color: #991b1b; }
    .c4-raci-val-cell.has-a { background: #fef3c7; color: #92400e; }
    .c4-raci-val-cell.has-c { background: #e0f2fe; color: #075985; }
    .c4-raci-val-cell.has-i { background: #dcfce7; color: #166534; }
    .c4-raci-val-cell.has-ar { background: #fed7aa; color: #9a3412; }

    /* Goals & Metrics Boxes */
    .c4-gm-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.25rem;
        margin-top: 1rem;
    }

    .c4-gm-col {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .c4-gm-box {
        border-radius: 4px;
        padding: 0.85rem 1rem;
        font-size: 0.82rem;
        line-height: 1.45;
    }

    .c4-gm-box.goals-box {
        background: #0a3d68;
        color: #ffffff;
        min-height: 140px;
    }

    .c4-gm-box.metrics-box {
        background: #cbd8e6;
        color: #072b4c;
        min-height: 140px;
        border: 1px solid #94b2d1;
    }

    .c4-gm-box-header {
        font-weight: 800;
        font-size: 0.85rem;
        text-transform: uppercase;
        margin-bottom: 0.4rem;
        letter-spacing: 0.05em;
    }

    .c4-gm-box ul {
        margin: 0;
        padding-left: 1.15rem;
    }

    .c4-gm-box li {
        margin-bottom: 0.35rem;
    }

    /* Page 4: Maturity Model */
    .c4-maturity-intro {
        font-style: italic;
        color: #1f2937;
        margin-bottom: 1.75rem;
        font-size: 0.95rem;
    }

    .c4-maturity-level-item {
        margin-bottom: 1.4rem;
        font-size: 0.92rem;
        line-height: 1.55;
    }

    .c4-maturity-badge {
        font-weight: 800;
        color: #072b4c;
        display: inline-block;
        margin-bottom: 0.2rem;
    }

    /* Live Inline Edit Elements */
    .c4-inline-input {
        width: 100%;
        border: 1px solid #f59e0b;
        background: #fffbeb;
        padding: 0.35rem 0.65rem;
        border-radius: 6px;
        font-size: 0.88rem;
        color: #1e293b;
        outline: none;
    }
    .c4-inline-input:focus {
        border-color: #d97706;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.25);
    }
    .c4-edit-field { display: none; }
    .c4-view-field { display: block; }
    .input-mode-on .c4-edit-field { display: block !important; }
    .input-mode-on .c4-view-field { display: none !important; }

    .c4-bullet-edit-row {
        display: flex;
        gap: 0.5rem;
        align-items: center;
        margin-bottom: 0.4rem;
    }
    .btn-action-del {
        background: #fee2e2;
        border: 1px solid #fca5a5;
        color: #dc2626;
        padding: 0.25rem 0.5rem;
        border-radius: 5px;
        cursor: pointer;
    }
    .btn-action-del:hover { background: #fecaca; }
    .btn-action-add {
        background: #dbeafe;
        border: 1px dashed #3b82f6;
        color: #1d4ed8;
        padding: 0.35rem 0.75rem;
        border-radius: 6px;
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
    }
    .btn-action-add:hover { background: #bfdbfe; }
</style>

<div class="c4-wrapper" id="c4Container">
    <div class="container-fluid px-lg-5">

        <!-- ====================================================================
             TOP CONTROLS & MODEL SWITCHER
             ==================================================================== -->
        <div class="c4-toolbar d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-warning text-dark px-3 py-2 fw-bold fs-6">
                    <i class="fas fa-certificate me-1"></i> COBIT 4.1 Framework
                </span>
                <div>
                    <h5 class="mb-0 fw-bold text-dark">
                        <span class="text-primary">{{ $processCode }}</span> - 
                        <span id="displayHeaderTitle">{{ $cobit4Data['title'] }}</span>
                    </h5>

                </div>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <!-- BUTTON TAMBAH GAMO COBIT 4 -->
                <button type="button" class="btn btn-primary fw-bold btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCreateCobit4Gamo">
                    <i class="fas fa-plus me-1"></i> Tambah GAMO COBIT 4
                </button>

                <!-- BUTTON ACTION: EDIT -->
                <button type="button" class="btn btn-outline-warning fw-bold btn-sm shadow-sm" id="btnInputModeToggle" onclick="toggleCobit4InputMode()">
                    <i class="fas fa-edit me-1"></i> Edit
                </button>

                @php
                    $procList = $allCobit4Processes ?? [];
                    $curObjId = $objective->objective_id;
                    $allList = collect($procList)->values();
                    $currentIndex = $allList->search(fn($p) => $p->code === $processCode || $p->objective_id === $curObjId);
                    $prevProcess = ($currentIndex !== false && $currentIndex > 0) ? $allList[$currentIndex - 1] : null;
                    $nextProcess = ($currentIndex !== false && $currentIndex < count($allList) - 1) ? $allList[$currentIndex + 1] : null;
                @endphp

                <!-- PILIH OBJECTIVE SELECTOR (INTERAKTIF & PASTI WORK) -->
                <div class="d-flex align-items-center bg-light border border-primary-subtle rounded-3 p-1 shadow-sm gap-1">
                    @if($prevProcess)
                        <a href="{{ route('cobit_component.show', ['id' => $prevProcess->objective_id ?: $prevProcess->code, 'focus_area' => $focusAreaId]) }}" 
                           class="btn btn-sm btn-white border shadow-xs text-primary px-2 py-1" 
                           title="Sebelumnya: {{ $prevProcess->code }} - {{ $prevProcess->title }}">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    @else
                        <button class="btn btn-sm btn-white border text-muted px-2 py-1" disabled>
                            <i class="fas fa-chevron-left"></i>
                        </button>
                    @endif

                    <div class="d-flex align-items-center gap-1">
                        <label for="selectCobit4Objective" class="form-label mb-0 fw-bold small text-dark px-1 text-nowrap d-none d-sm-inline">
                            <i class="fas fa-list-check text-primary me-1"></i> Objective:
                        </label>
                        <select id="selectCobit4Objective" 
                                class="form-select form-select-sm fw-bold border-0 bg-transparent text-primary" 
                                style="min-width: 240px; max-width: 340px; cursor: pointer;" 
                                onchange="handleCobit4ObjectiveSelect(this.value)">
                            @php
                                $domainGroups = [
                                    'PO' => 'Plan & Organise (PO)',
                                    'AI' => 'Acquire & Implement (AI)',
                                    'DS' => 'Deliver & Support (DS)',
                                    'ME' => 'Monitor & Evaluate (ME)',
                                ];
                                $grouped = collect($procList)->groupBy('domain_code');
                            @endphp
                            @foreach($domainGroups as $dKey => $dName)
                                @if(isset($grouped[$dKey]) && count($grouped[$dKey]))
                                    <optgroup label="{{ $dName }}">
                                        @foreach($grouped[$dKey] as $p)
                                             @php
                                                $isSelected = ($p->code === $processCode || $p->objective_id === $curObjId);
                                                $pUrl = route('cobit_component.show', ['id' => $p->objective_id ?: $p->code, 'focus_area' => $focusAreaId]);
                                            @endphp
                                            <option value="{{ $pUrl }}" {{ $isSelected ? 'selected' : '' }}>
                                                {{ $p->code }} - {{ Str::limit($p->title, 32) }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    @if($nextProcess)
                        <a href="{{ route('cobit_component.show', ['id' => $nextProcess->objective_id ?: $nextProcess->code, 'focus_area' => $focusAreaId]) }}" 
                           class="btn btn-sm btn-white border shadow-xs text-primary px-2 py-1" 
                           title="Selanjutnya: {{ $nextProcess->code }} - {{ $nextProcess->title }}">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    @else
                        <button class="btn btn-sm btn-white border text-muted px-2 py-1" disabled>
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- ====================================================================
             ACTIVE INPUT MODE ACTION BANNER
             ==================================================================== -->
        <div class="c4-input-mode-banner" id="c4InputModeBanner">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-pen-fancy text-warning fs-4"></i>
                <div>
                    <strong class="text-dark">Mode Edit Aktif</strong>
                    <div class="small text-muted">
                        Anda dapat mengubah data proses, kriteria informasi, control objectives, alur input/output, dan RACI.
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-success btn-sm fw-bold shadow-sm px-3" onclick="saveCobit4Data()">
                    <i class="fas fa-save me-1"></i> Simpan
                </button>
                <button type="button" class="btn btn-danger btn-sm fw-bold shadow-sm px-3" onclick="deleteCurrentGamo()">
                    <i class="fas fa-trash me-1"></i> Hapus
                </button>
            </div>
        </div>

        <!-- ====================================================================
             TAB SWITCHER BUTTONS (4 BUKU HALAMAN COBIT 4.1)
             ==================================================================== -->
        <div class="c4-nav-pills">
            <button class="c4-nav-btn active" id="btnPage1" onclick="switchCobit4Page('page1', this)">
                <i class="fas fa-file-alt"></i> 1. Process Description
            </button>
            <button class="c4-nav-btn" id="btnPage2" onclick="switchCobit4Page('page2', this)">
                <i class="fas fa-bullseye"></i> 2. Control Objectives
            </button>
            <button class="c4-nav-btn" id="btnPage3" onclick="switchCobit4Page('page3', this)">
                <i class="fas fa-project-diagram"></i> 3. Management Guidelines
            </button>
            <button class="c4-nav-btn" id="btnPage4" onclick="switchCobit4Page('page4', this)">
                <i class="fas fa-chart-line"></i> 4. Maturity Model
            </button>
        </div>

        <!-- ====================================================================
             PAGE 1: PROCESS DESCRIPTION
             ==================================================================== -->
        <div class="c4-book-page c4-view-section" id="c4-section-page1">
            <div class="c4-header-banner banner-right">
                <div class="c4-banner-info">
                    <div class="c4-banner-domain" id="bannerDomainText">{{ $cobit4Data['domain_name'] }}</div>
                    <div class="c4-banner-title" id="bannerTitleText">{{ $cobit4Data['title'] }}</div>
                </div>
                <div class="c4-banner-code">{{ $processCode }}</div>
            </div>

            <div class="c4-page-content">
                <div class="c4-serif-heading">Process Description</div>

                <!-- Process Title -->
                <div class="c4-process-title">
                    <span class="c4-view-field" id="viewProcessTitle">{{ $processCode }} {{ $cobit4Data['title'] }}</span>
                    <div class="c4-edit-field mb-2">
                        <label class="form-label small fw-bold text-muted mb-1">Judul GAMO / Proses:</label>
                        <input type="text" class="c4-inline-input fw-bold fs-6" id="inputProcessTitle" 
                               value="{{ $cobit4Data['title'] }}" oninput="syncTitle(this.value)">
                    </div>
                </div>

                <!-- Process Summary Description -->
                <div class="c4-process-desc">
                    <span class="c4-view-field" id="viewProcessDesc">{{ $cobit4Data['description'] }}</span>
                    <div class="c4-edit-field mb-3">
                        <label class="form-label small fw-bold text-muted mb-1">Deskripsi Ringkasan Proses:</label>
                        <textarea class="c4-inline-input" id="inputProcessDesc" rows="4" 
                                  oninput="DATA.description = this.value">{{ $cobit4Data['description'] }}</textarea>
                    </div>
                </div>

                <!-- Row: Criteria Cube (Left) & Domain Buttons (Right) -->
                <div class="row align-items-start mb-4">
                    <div class="col-lg-8">
                        <div class="c4-criteria-table-wrap">
                            <table style="border-collapse: separate; border-spacing: 0;">
                                <tr>
                                    @php
                                        $criteriaKeys = ['effectiveness', 'efficiency', 'confidentiality', 'integrity', 'availability', 'compliance', 'reliability'];
                                        $criteriaLabels = ['Effectiveness', 'Efficiency', 'Confidentiality', 'Integrity', 'Availability', 'Compliance', 'Reliability'];
                                    @endphp
                                    @foreach($criteriaLabels as $clabel)
                                        <th class="c4-slanted-col-header">
                                            <span class="c4-slanted-label">{{ $clabel }}</span>
                                        </th>
                                    @endforeach
                                </tr>
                                <tr>
                                    @foreach($criteriaKeys as $ckey)
                                        @php
                                            $cval = strtoupper($cobit4Data['information_criteria'][$ckey] ?? '');
                                            $cClass = ($cval === 'P') ? 'p-active' : (($cval === 'S') ? 's-active' : '');
                                        @endphp
                                        <td class="c4-criteria-cell {{ $cClass }}" 
                                            id="criteriaCell_{{ $ckey }}" 
                                            onclick="cycleCriteria('{{ $ckey }}')"
                                            title="Klik untuk mengubah nilai P / S">
                                            <span id="criteriaText_{{ $ckey }}">{{ $cval }}</span>
                                        </td>
                                    @endforeach
                                </tr>
                            </table>
                            <div class="c4-edit-field mt-1 small text-warning-emphasis">
                                <i class="fas fa-info-circle me-1"></i> Klik pada kotak di atas untuk beralih: <strong>P</strong> (Primary) ➔ <strong>S</strong> (Secondary) ➔ Kosong.
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 text-end">
                        <div class="c4-domain-btn-stack">
                            <div class="c4-domain-btn {{ $cobit4Data['domain_code'] === 'PO' ? 'active' : '' }}" 
                                 id="domainBtn_PO" onclick="selectDomain('PO', 'Plan and Organise')">
                                Plan and<br>Organise
                            </div>
                            <div class="c4-domain-btn {{ $cobit4Data['domain_code'] === 'AI' ? 'active' : '' }}" 
                                 id="domainBtn_AI" onclick="selectDomain('AI', 'Acquire and Implement')">
                                Acquire and<br>Implement
                            </div>
                            <div class="c4-domain-btn {{ $cobit4Data['domain_code'] === 'DS' ? 'active' : '' }}" 
                                 id="domainBtn_DS" onclick="selectDomain('DS', 'Deliver and Support')">
                                Deliver and<br>Support
                            </div>
                            <div class="c4-domain-btn {{ $cobit4Data['domain_code'] === 'ME' ? 'active' : '' }}" 
                                 id="domainBtn_ME" onclick="selectDomain('ME', 'Monitor and Evaluate')">
                                Monitor and<br>Evaluate
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Waterfall Control Statement -->
                <div class="c4-waterfall-box">
                    <div class="c4-waterfall-step">
                        <span class="c4-waterfall-label">Control over the IT process of</span>
                        <div class="c4-waterfall-text">
                            <span class="c4-view-field" id="viewControlOver">{{ $cobit4Data['control_statement']['control_over'] ?? strtolower($cobit4Data['title']) }}</span>
                            <div class="c4-edit-field">
                                <input type="text" class="c4-inline-input" id="inputControlOver" 
                                       value="{{ $cobit4Data['control_statement']['control_over'] ?? strtolower($cobit4Data['title']) }}"
                                       oninput="DATA.control_statement.control_over = this.value">
                            </div>
                        </div>
                    </div>

                    <div class="c4-waterfall-step ps-3">
                        <span class="c4-waterfall-label">that satisfies the business requirement for IT of</span>
                        <div class="c4-waterfall-text">
                            <span class="c4-view-field" id="viewSatisfies">{{ $cobit4Data['control_statement']['satisfies_requirement'] ?? '' }}</span>
                            <div class="c4-edit-field">
                                <textarea class="c4-inline-input" id="inputSatisfies" rows="2" 
                                          oninput="DATA.control_statement.satisfies_requirement = this.value">{{ $cobit4Data['control_statement']['satisfies_requirement'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="c4-waterfall-step ps-5">
                        <span class="c4-waterfall-label">by focusing on</span>
                        <div class="c4-waterfall-text">
                            <span class="c4-view-field" id="viewFocusing">{{ $cobit4Data['control_statement']['focusing_on'] ?? '' }}</span>
                            <div class="c4-edit-field">
                                <textarea class="c4-inline-input" id="inputFocusing" rows="2" 
                                          oninput="DATA.control_statement.focusing_on = this.value">{{ $cobit4Data['control_statement']['focusing_on'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="c4-waterfall-step" style="padding-left: 5rem;">
                        <span class="c4-waterfall-label">is achieved by</span>
                        <ul class="c4-waterfall-bullets c4-view-field" id="viewAchievedList"></ul>
                        <div class="c4-edit-field" id="editAchievedContainer"></div>
                    </div>

                    <div class="c4-waterfall-step" style="padding-left: 7rem;">
                        <span class="c4-waterfall-label">and is measured by</span>
                        <ul class="c4-waterfall-bullets c4-view-field" id="viewMeasuredList"></ul>
                        <div class="c4-edit-field" id="editMeasuredContainer"></div>
                    </div>
                </div>

                <!-- Bottom: IT Governance Focus & IT Resources -->
                <div class="c4-bottom-grid">
                    <!-- IT Governance Pentagon Diagram (Authentic ISACA COBIT 4.1) -->
                    <div class="c4-pentagon-wrap">
                        <div class="small fw-bold text-muted mb-2 text-uppercase text-center" style="letter-spacing: 0.05em;">
                            IT Governance Focus:
                        </div>
                        <svg width="270" height="240" viewBox="35 15 230 215" id="pentagonSvg" class="c4-pentagon-svg">
                            <defs>
                                <!-- Shaded recessed inner pentagon gradient -->
                                <radialGradient id="c4InnerGovGrad" cx="50%" cy="48%" r="55%">
                                    <stop offset="0%" stop-color="#ffffff" />
                                    <stop offset="60%" stop-color="#cbd5e1" />
                                    <stop offset="100%" stop-color="#94a3b8" />
                                </radialGradient>
                            </defs>

                            <!-- 1. Strategic Alignment (Top-Left) -->
                            <polygon id="poly_strategic_alignment" class="c4-seg-poly"
                                     points="54.9,99.1 150.0,30.0 150.0,88.0 110.1,117.0"
                                     fill="#ffffff" onclick="cyclePentagon('strategic_alignment')" />
                            <g transform="rotate(-36, 115, 83)">
                                <text x="115" y="80" class="c4-seg-text c4-txt-strategic_alignment" fill="#0f2338">STRATEGIC</text>
                                <text x="115" y="90" class="c4-seg-text c4-txt-strategic_alignment" fill="#0f2338">ALIGNMENT</text>
                            </g>

                            <!-- 2. Value Delivery (Top-Right) -->
                            <polygon id="poly_value_delivery" class="c4-seg-poly"
                                     points="150.0,30.0 245.1,99.1 189.9,117.0 150.0,88.0"
                                     fill="#ffffff" onclick="cyclePentagon('value_delivery')" />
                            <g transform="rotate(36, 185, 83)">
                                <text x="185" y="80" class="c4-seg-text c4-txt-value_delivery" fill="#0f2338">VALUE</text>
                                <text x="185" y="90" class="c4-seg-text c4-txt-value_delivery" fill="#0f2338">DELIVERY</text>
                            </g>

                            <!-- 3. Risk Management (Right) -->
                            <polygon id="poly_risk_management" class="c4-seg-poly"
                                     points="245.1,99.1 208.8,210.9 174.7,164.0 189.9,117.0"
                                     fill="#ffffff" onclick="cyclePentagon('risk_management')" />
                            <g transform="rotate(72, 204, 147)">
                                <text x="204" y="144" class="c4-seg-text c4-txt-risk_management" fill="#0f2338">RISK</text>
                                <text x="204" y="154" class="c4-seg-text c4-txt-risk_management" fill="#0f2338">MANAGEMENT</text>
                            </g>

                            <!-- 4. Resource Management (Bottom) -->
                            <polygon id="poly_resource_management" class="c4-seg-poly"
                                     points="208.8,210.9 91.2,210.9 125.3,164.0 174.7,164.0"
                                     fill="#ffffff" onclick="cyclePentagon('resource_management')" />
                            <g>
                                <text x="150" y="185" class="c4-seg-text c4-txt-resource_management" fill="#0f2338">RESOURCE</text>
                                <text x="150" y="196" class="c4-seg-text c4-txt-resource_management" fill="#0f2338">MANAGEMENT</text>
                            </g>

                            <!-- 5. Performance Measurement (Left) -->
                            <polygon id="poly_performance_measurement" class="c4-seg-poly"
                                     points="91.2,210.9 54.9,99.1 110.1,117.0 125.3,164.0"
                                     fill="#ffffff" onclick="cyclePentagon('performance_measurement')" />
                            <g transform="rotate(-72, 96, 147)">
                                <text x="96" y="144" class="c4-seg-text c4-txt-performance_measurement" fill="#0f2338">PERFORMANCE</text>
                                <text x="96" y="154" class="c4-seg-text c4-txt-performance_measurement" fill="#0f2338">MEASUREMENT</text>
                            </g>

                            <!-- Center: Inner Pentagon (IT Governance) -->
                            <polygon points="150.0,88.0 189.9,117.0 174.7,164.0 125.3,164.0 110.1,117.0"
                                     fill="url(#c4InnerGovGrad)" stroke="#1c2833" stroke-width="1.8" stroke-linejoin="round" />
                            <text x="150" y="133" font-family="'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" font-size="8.8" font-weight="900" fill="#111827" text-anchor="middle" letter-spacing="-0.02em">IT GOVERNANCE</text>
                        </svg>

                        <!-- Authentic ISACA Legend -->
                        <div class="c4-pentagon-legend">
                            <div class="d-flex align-items-center gap-2">
                                <span class="c4-legend-box" style="background: #1b4b83;"></span>
                                <span>Primary</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="c4-legend-box" style="background: #a6bdd7;"></span>
                                <span>Secondary</span>
                            </div>
                        </div>
                    </div>

                    <!-- IT Resources Checklist Table -->
                    <div class="c4-resources-table-wrap">
                        <table style="border-collapse: separate; border-spacing: 0;">
                            <tr>
                                <td class="c4-res-cell" id="resCell_applications" onclick="toggleResource('applications')"><span id="resCheck_applications">✔</span></td>
                                <td class="c4-res-cell" id="resCell_information" onclick="toggleResource('information')"><span id="resCheck_information">✔</span></td>
                                <td class="c4-res-cell" id="resCell_infrastructure" onclick="toggleResource('infrastructure')"><span id="resCheck_infrastructure">✔</span></td>
                                <td class="c4-res-cell" id="resCell_people" onclick="toggleResource('people')"><span id="resCheck_people">✔</span></td>
                            </tr>
                            <tr>
                                @php
                                    $resLabels = ['Applications', 'Information', 'Infrastructure', 'People'];
                                @endphp
                                @foreach($resLabels as $rlabel)
                                    <th class="c4-slanted-col-header" style="height: 75px;">
                                        <span class="c4-slanted-label">{{ $rlabel }}</span>
                                    </th>
                                @endforeach
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ====================================================================
             PAGE 2: CONTROL OBJECTIVES
             ==================================================================== -->
        <div class="c4-book-page c4-view-section" id="c4-section-page2" style="display: none;">
            <div class="c4-header-banner banner-left">
                <div class="c4-banner-code">{{ $processCode }}</div>
                <div class="c4-banner-info">
                    <div class="c4-banner-domain" id="bannerDomainText2">{{ $cobit4Data['domain_name'] }}</div>
                    <div class="c4-banner-title" id="bannerTitleText2">{{ $cobit4Data['title'] }}</div>
                </div>
            </div>

            <div class="c4-page-content">
                <div class="c4-serif-heading">Control Objectives</div>
                <div class="c4-process-title" id="processTitlePage2">{{ $processCode }} {{ $cobit4Data['title'] }}</div>

                <div id="controlObjectivesContainer" class="mt-4"></div>

                <div class="c4-edit-field mt-3">
                    <button class="btn btn-outline-primary btn-sm fw-bold" onclick="addControlObjective()">
                        <i class="fas fa-plus me-1"></i> Tambah Control Objective Baru
                    </button>
                </div>
            </div>
        </div>

        <!-- ====================================================================
             PAGE 3: MANAGEMENT GUIDELINES
             ==================================================================== -->
        <div class="c4-book-page c4-view-section" id="c4-section-page3" style="display: none;">
            <div class="c4-header-banner banner-right">
                <div class="c4-banner-info">
                    <div class="c4-banner-domain" id="bannerDomainText3">{{ $cobit4Data['domain_name'] }}</div>
                    <div class="c4-banner-title" id="bannerTitleText3">{{ $cobit4Data['title'] }}</div>
                </div>
                <div class="c4-banner-code">{{ $processCode }}</div>
            </div>

            <div class="c4-page-content">
                <div class="c4-serif-heading">Management Guidelines</div>
                <div class="c4-process-title" id="processTitlePage3">{{ $processCode }} {{ $cobit4Data['title'] }}</div>

                <!-- Inputs & Outputs Row -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <table class="c4-io-table">
                            <thead>
                                <tr>
                                    <th style="width: 70px;">From</th>
                                    <th>Inputs</th>
                                    <th class="c4-edit-field" style="width: 40px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="inputsTableBody"></tbody>
                        </table>
                        <div class="c4-edit-field mb-2">
                            <button class="btn-action-add" onclick="addInputRow()"><i class="fas fa-plus me-1"></i> Tambah Input</button>
                        </div>
                        <div class="small text-muted">* Inputs from outside COBIT</div>
                    </div>

                    <div class="col-md-6">
                        <table class="c4-io-table">
                            <thead>
                                <tr>
                                    <th>Outputs</th>
                                    <th style="width: 140px;">To</th>
                                    <th class="c4-edit-field" style="width: 40px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="outputsTableBody"></tbody>
                        </table>
                        <div class="c4-edit-field mb-2">
                            <button class="btn-action-add" onclick="addOutputRow()"><i class="fas fa-plus me-1"></i> Tambah Output</button>
                        </div>
                    </div>
                </div>

                <!-- RACI Chart -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold fs-6">RACI Chart</span>
                        <span class="fw-bold text-muted small text-uppercase">Functions</span>
                    </div>

                    <div class="c4-raci-table-wrap">
                        <table class="c4-raci-table">
                            <thead>
                                <tr>
                                    <th style="vertical-align: bottom; padding-bottom: 8px; font-weight: 800;">Activities</th>
                                    @php
                                        $roles = [
                                            'CEO', 'CFO', 'Business Executive', 'CIO', 'Business Process Owner',
                                            'Head Operations', 'Chief Architect', 'Head Development',
                                            'Head IT Administration', 'PMO', 'Compliance, Audit, Risk and Security'
                                        ];
                                    @endphp
                                    @foreach($roles as $role)
                                        <th class="raci-header-role"><span>{{ $role }}</span></th>
                                    @endforeach
                                    <th class="c4-edit-field" style="width: 40px;"></th>
                                </tr>
                            </thead>
                            <tbody id="raciTableBody"></tbody>
                        </table>
                    </div>
                    <div class="small text-muted">A <strong>RACI chart</strong> identifies who is <strong>R</strong>esponsible, <strong>A</strong>ccountable, <strong>C</strong>onsulted and/or <strong>I</strong>nformed.</div>
                    <div class="c4-edit-field mt-2">
                        <button class="btn-action-add" onclick="addRaciActivity()"><i class="fas fa-plus me-1"></i> Tambah Aktivitas RACI</button>
                    </div>
                </div>

                <!-- Goals and Metrics Flow -->
                <div class="mt-4">
                    <span class="fw-bold fs-6">Goals and Metrics</span>
                    <div class="c4-gm-grid">
                        <div class="c4-gm-col">
                            <div class="c4-gm-box goals-box">
                                <div class="c4-gm-box-header text-center">IT</div>
                                <div id="itGoalsBox"></div>
                            </div>
                            <div class="c4-gm-box metrics-box">
                                <div id="itMetricsBox"></div>
                            </div>
                        </div>

                        <div class="c4-gm-col">
                            <div class="c4-gm-box goals-box">
                                <div class="c4-gm-box-header text-center">Process</div>
                                <div id="processGoalsBox"></div>
                            </div>
                            <div class="c4-gm-box metrics-box">
                                <div id="processMetricsBox"></div>
                            </div>
                        </div>

                        <div class="c4-gm-col">
                            <div class="c4-gm-box goals-box">
                                <div class="c4-gm-box-header text-center">Activities</div>
                                <div id="activitiesGoalsBox"></div>
                            </div>
                            <div class="c4-gm-box metrics-box">
                                <div id="activitiesMetricsBox"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ====================================================================
             PAGE 4: MATURITY MODEL
             ==================================================================== -->
        <div class="c4-book-page c4-view-section" id="c4-section-page4" style="display: none;">
            <div class="c4-header-banner banner-left">
                <div class="c4-banner-code">{{ $processCode }}</div>
                <div class="c4-banner-info">
                    <div class="c4-banner-domain" id="bannerDomainText4">{{ $cobit4Data['domain_name'] }}</div>
                    <div class="c4-banner-title" id="bannerTitleText4">{{ $cobit4Data['title'] }}</div>
                </div>
            </div>

            <div class="c4-page-content">
                <div class="c4-serif-heading">Maturity Model</div>
                <div class="c4-process-title" id="processTitlePage4">{{ $processCode }} {{ $cobit4Data['title'] }}</div>

                <div class="c4-maturity-intro">
                    <span class="c4-view-field" id="viewMaturityIntro">{{ $cobit4Data['maturity_model']['intro'] ?? "Management of the process of {$cobit4Data['title']} is:" }}</span>
                    <div class="c4-edit-field">
                        <label class="form-label small fw-bold text-muted mb-1">Pengantar Maturity Model:</label>
                        <input type="text" class="c4-inline-input" id="inputMaturityIntro" 
                               value="{{ $cobit4Data['maturity_model']['intro'] ?? "Management of the process of {$cobit4Data['title']} is:" }}"
                               oninput="DATA.maturity_model.intro = this.value">
                    </div>
                </div>

                <div id="maturityLevelsContainer"></div>
            </div>
        </div>

    </div>
</div>

<!-- ====================================================================
     MODAL TAMBAH GAMO COBIT 4.1
     ==================================================================== -->
<div class="modal fade" id="modalCreateCobit4Gamo" tabindex="-1" aria-labelledby="modalCreateCobit4GamoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold" id="modalCreateCobit4GamoLabel">
                    <i class="fas fa-plus-circle me-1"></i> Tambah GAMO Baru (COBIT 4.1)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formCreateCobit4Gamo" onsubmit="handleCreateCobit4Gamo(event)">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="newGamoCode" class="form-label fw-bold text-dark">
                            Kode Proses <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control font-monospace fw-bold" id="newGamoCode" placeholder="Contoh: PO11, AI8, DS14, ME5" required>
                        <div class="form-text">Gunakan kode awalan domain (PO, AI, DS, ME) dan nomor urut.</div>
                    </div>

                    <div class="mb-3">
                        <label for="newGamoTitle" class="form-label fw-bold text-dark">
                            Nama GAMO / Judul Proses <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="newGamoTitle" placeholder="Contoh: Manage Cloud & AI Integration" required>
                    </div>

                    <div class="mb-3">
                        <label for="newGamoDomain" class="form-label fw-bold text-dark">
                            Domain <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="newGamoDomain" required>
                            <option value="PO">PO - Plan and Organise</option>
                            <option value="AI">AI - Acquire and Implement</option>
                            <option value="DS">DS - Deliver and Support</option>
                            <option value="ME">ME - Monitor and Evaluate</option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label for="newGamoDesc" class="form-label fw-bold text-dark">
                            Isi / Deskripsi Ringkasan Proses
                        </label>
                        <textarea class="form-control" id="newGamoDesc" rows="4" placeholder="Jelaskan tujuan dan ruang lingkup proses ini..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4" id="btnSubmitNewGamo">
                        <i class="fas fa-save me-1"></i> Simpan GAMO
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const PROCESS_CODE = @json($processCode);
    const OBJECTIVE_ID = @json($objective->objective_id);
    const FOCUS_AREA_ID = @json((int)$focusAreaId);
    let DATA = @json($cobit4Data);

    let isInputMode = false;

    // Toggle Input Mode (Live Editing to MySQL)
    function toggleCobit4InputMode(forceState = null) {
        isInputMode = (forceState !== null) ? forceState : !isInputMode;
        
        const container = document.getElementById('c4Container');
        const banner = document.getElementById('c4InputModeBanner');
        const toggleBtn = document.getElementById('btnInputModeToggle');

        if (isInputMode) {
            if (container) container.classList.add('input-mode-on');
            if (banner) banner.classList.add('active');
            if (toggleBtn) {
                toggleBtn.classList.remove('btn-outline-warning');
                toggleBtn.classList.add('btn-warning');
                toggleBtn.innerHTML = '<i class="fas fa-check me-1"></i> Edit (Aktif)';
            }
        } else {
            if (container) container.classList.remove('input-mode-on');
            if (banner) banner.classList.remove('active');
            if (toggleBtn) {
                toggleBtn.classList.remove('btn-warning');
                toggleBtn.classList.add('btn-outline-warning');
                toggleBtn.innerHTML = '<i class="fas fa-edit me-1"></i> Edit';
            }
        }

        renderAllDynamicSections();
    }

    // Switch between Tab 1, 2, 3, 4
    function switchCobit4Page(pageId, btnElement) {
        document.querySelectorAll('.c4-nav-pills .c4-nav-btn').forEach(btn => btn.classList.remove('active'));
        if (btnElement) btnElement.classList.add('active');

        const sections = {
            'page1': document.getElementById('c4-section-page1'),
            'page2': document.getElementById('c4-section-page2'),
            'page3': document.getElementById('c4-section-page3'),
            'page4': document.getElementById('c4-section-page4'),
        };

        Object.keys(sections).forEach(key => {
            if (sections[key]) sections[key].style.display = (key === pageId) ? 'block' : 'none';
        });
    }

    // Navigate to selected objective
    function handleCobit4ObjectiveSelect(url) {
        if (url) {
            window.location.href = url;
        }
    }

    // Synchronize title across banners and headings
    function syncTitle(newTitle) {
        DATA.title = newTitle;
        ['displayHeaderTitle', 'bannerTitleText', 'bannerTitleText2', 'bannerTitleText3', 'bannerTitleText4'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = newTitle;
        });
        ['viewProcessTitle', 'processTitlePage2', 'processTitlePage3', 'processTitlePage4'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = `${PROCESS_CODE} ${newTitle}`;
        });
    }

    // Criteria Cube Click Cycling (P -> S -> "")
    function cycleCriteria(key) {
        if (!isInputMode) return;
        const current = (DATA.information_criteria[key] || '').toUpperCase();
        let next = '';
        if (current === '') next = 'P';
        else if (current === 'P') next = 'S';
        else next = '';

        DATA.information_criteria[key] = next;
        updateCriteriaUI(key, next);
    }

    function updateCriteriaUI(key, val) {
        const cell = document.getElementById(`criteriaCell_${key}`);
        const text = document.getElementById(`criteriaText_${key}`);
        if (!cell || !text) return;

        cell.classList.remove('p-active', 's-active');
        if (val === 'P') cell.classList.add('p-active');
        else if (val === 'S') cell.classList.add('s-active');
        text.textContent = val;
    }

    // Domain selection
    function selectDomain(code, name) {
        if (!isInputMode) return;
        DATA.domain_code = code;
        DATA.domain_name = name;

        ['PO', 'AI', 'DS', 'ME'].forEach(d => {
            const btn = document.getElementById(`domainBtn_${d}`);
            if (btn) btn.classList.toggle('active', d === code);
        });
        ['bannerDomainText', 'bannerDomainText2', 'bannerDomainText3', 'bannerDomainText4'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = name;
        });
    }

    // Pentagon Segments Cycling (Primary -> Secondary -> Neutral)
    function cyclePentagon(segmentKey) {
        if (!isInputMode) return;
        const current = (DATA.it_governance_focus[segmentKey] || '').toLowerCase();
        let next = '';
        if (current === '' || current === 'none') next = 'Primary';
        else if (current === 'primary') next = 'Secondary';
        else next = '';

        DATA.it_governance_focus[segmentKey] = next;
        updatePentagonUI(segmentKey, next);
    }

    function updatePentagonUI(key, val) {
        const poly = document.getElementById(`poly_${key}`);
        const txts = document.querySelectorAll(`.c4-txt-${key}`);
        if (!poly) return;

        let fill = '#ffffff';
        let textFill = '#0f2338';

        const normalized = (val || '').toLowerCase();
        if (normalized === 'primary' || normalized === 'p') {
            fill = '#1b4b83';
            textFill = '#ffffff';
        } else if (normalized === 'secondary' || normalized === 's') {
            fill = '#a6bdd7';
            textFill = '#0f2338';
        } else {
            fill = '#ffffff';
            textFill = '#0f2338';
        }

        poly.setAttribute('fill', fill);
        txts.forEach(t => t.setAttribute('fill', textFill));
    }

    // IT Resources Checklist Toggle
    function toggleResource(key) {
        if (!isInputMode) return;
        DATA.it_resources[key] = !DATA.it_resources[key];
        const check = document.getElementById(`resCheck_${key}`);
        if (check) check.textContent = DATA.it_resources[key] ? '✔' : '';
    }

    // Render Dynamic Sections (Waterfalls, Objectives, RACI, Inputs/Outputs, Goals/Metrics, Maturity)
    function renderAllDynamicSections() {
        renderWaterfallBullets();
        renderControlObjectives();
        renderInputsOutputs();
        renderRaciTable();
        renderGoalsAndMetrics();
        renderMaturityLevels();
        initPentagonColors();
    }

    // 1. Waterfall Bullets
    function renderWaterfallBullets() {
        // Achieved By
        const achList = document.getElementById('viewAchievedList');
        const achEdit = document.getElementById('editAchievedContainer');
        achList.innerHTML = (DATA.control_statement.achieved_by || []).map(item => `<li>${escapeHtml(item)}</li>`).join('');
        
        achEdit.innerHTML = (DATA.control_statement.achieved_by || []).map((item, idx) => `
            <div class="c4-bullet-edit-row">
                <input type="text" class="c4-inline-input" value="${escapeHtml(item)}" 
                       oninput="DATA.control_statement.achieved_by[${idx}] = this.value">
                <button class="btn-action-del" onclick="removeAchievedItem(${idx})" title="Hapus Butir">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `).join('') + `
            <button class="btn-action-add mt-1" onclick="addAchievedItem()">
                <i class="fas fa-plus me-1"></i> Tambah Butir Achieved By
            </button>
        `;

        // Measured By
        const measList = document.getElementById('viewMeasuredList');
        const measEdit = document.getElementById('editMeasuredContainer');
        measList.innerHTML = (DATA.control_statement.measured_by || []).map(item => `<li>${escapeHtml(item)}</li>`).join('');
        
        measEdit.innerHTML = (DATA.control_statement.measured_by || []).map((item, idx) => `
            <div class="c4-bullet-edit-row">
                <input type="text" class="c4-inline-input" value="${escapeHtml(item)}" 
                       oninput="DATA.control_statement.measured_by[${idx}] = this.value">
                <button class="btn-action-del" onclick="removeMeasuredItem(${idx})" title="Hapus Butir">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `).join('') + `
            <button class="btn-action-add mt-1" onclick="addMeasuredItem()">
                <i class="fas fa-plus me-1"></i> Tambah Butir Measured By
            </button>
        `;
    }

    function addAchievedItem() {
        if (!DATA.control_statement.achieved_by) DATA.control_statement.achieved_by = [];
        DATA.control_statement.achieved_by.push('Butir pencapaian baru...');
        renderWaterfallBullets();
    }
    function removeAchievedItem(idx) {
        DATA.control_statement.achieved_by.splice(idx, 1);
        renderWaterfallBullets();
    }
    function addMeasuredItem() {
        if (!DATA.control_statement.measured_by) DATA.control_statement.measured_by = [];
        DATA.control_statement.measured_by.push('Indikator pengukuran baru...');
        renderWaterfallBullets();
    }
    function removeMeasuredItem(idx) {
        DATA.control_statement.measured_by.splice(idx, 1);
        renderWaterfallBullets();
    }

    // 2. Control Objectives
    function renderControlObjectives() {
        const container = document.getElementById('controlObjectivesContainer');
        const list = DATA.control_objectives || [];

        if (!isInputMode) {
            container.innerHTML = list.map(co => `
                <div class="c4-obj-item">
                    <div class="c4-obj-item-title">${escapeHtml(co.code)} ${escapeHtml(co.title)}</div>
                    <div class="c4-obj-item-desc">${escapeHtml(co.desc || co.description || '')}</div>
                </div>
            `).join('');
        } else {
            container.innerHTML = list.map((co, idx) => `
                <div class="card p-3 border-warning bg-light mb-3 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex gap-2 align-items-center flex-grow-1 me-3">
                            <input type="text" class="c4-inline-input fw-bold" style="width: 100px;" value="${escapeHtml(co.code)}" 
                                   oninput="DATA.control_objectives[${idx}].code = this.value" placeholder="Kode">
                            <input type="text" class="c4-inline-input fw-bold" value="${escapeHtml(co.title)}" 
                                   oninput="DATA.control_objectives[${idx}].title = this.value" placeholder="Judul Objective">
                        </div>
                        <button class="btn btn-sm btn-outline-danger" onclick="removeControlObjective(${idx})">
                            <i class="fas fa-trash me-1"></i> Hapus
                        </button>
                    </div>
                    <textarea class="c4-inline-input" rows="3" 
                              oninput="DATA.control_objectives[${idx}].desc = this.value" placeholder="Deskripsi Objective...">${escapeHtml(co.desc || co.description || '')}</textarea>
                </div>
            `).join('');
        }
    }

    function addControlObjective() {
        if (!DATA.control_objectives) DATA.control_objectives = [];
        const nextNum = DATA.control_objectives.length + 1;
        DATA.control_objectives.push({
            code: `${PROCESS_CODE}.${nextNum}`,
            title: 'Judul Objective Baru',
            desc: 'Deskripsi lengkap kendali objective baru...',
        });
        renderControlObjectives();
    }
    function removeControlObjective(idx) {
        DATA.control_objectives.splice(idx, 1);
        renderControlObjectives();
    }

    // 3. Inputs & Outputs Table
    function renderInputsOutputs() {
        const inTbody = document.getElementById('inputsTableBody');
        const outTbody = document.getElementById('outputsTableBody');
        const inputs = DATA.management_guidelines?.inputs || [];
        const outputs = DATA.management_guidelines?.outputs || [];

        if (!isInputMode) {
            inTbody.innerHTML = inputs.map(i => `
                <tr>
                    <td class="fw-bold font-monospace">${escapeHtml(i.from)}</td>
                    <td>${escapeHtml(i.input)}</td>
                </tr>
            `).join('');

            outTbody.innerHTML = outputs.map(o => `
                <tr>
                    <td class="fw-semibold">${escapeHtml(o.output)}</td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            ${(o.to || '').split(',').map(tok => `<span class="badge bg-light text-dark border font-monospace" style="font-size: 0.76rem;">${escapeHtml(tok.trim())}</span>`).join('')}
                        </div>
                    </td>
                </tr>
            `).join('');
        } else {
            inTbody.innerHTML = inputs.map((i, idx) => `
                <tr>
                    <td>
                        <input type="text" class="c4-inline-input font-monospace fw-bold" value="${escapeHtml(i.from)}" 
                               oninput="DATA.management_guidelines.inputs[${idx}].from = this.value">
                    </td>
                    <td>
                        <input type="text" class="c4-inline-input" value="${escapeHtml(i.input)}" 
                               oninput="DATA.management_guidelines.inputs[${idx}].input = this.value">
                    </td>
                    <td class="text-center">
                        <button class="btn-action-del" onclick="removeInputRow(${idx})"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `).join('');

            outTbody.innerHTML = outputs.map((o, idx) => `
                <tr>
                    <td>
                        <input type="text" class="c4-inline-input fw-semibold" value="${escapeHtml(o.output)}" 
                               oninput="DATA.management_guidelines.outputs[${idx}].output = this.value">
                    </td>
                    <td>
                        <input type="text" class="c4-inline-input font-monospace" value="${escapeHtml(o.to)}" 
                               oninput="DATA.management_guidelines.outputs[${idx}].to = this.value" placeholder="e.g. PO2, AI1">
                    </td>
                    <td class="text-center">
                        <button class="btn-action-del" onclick="removeOutputRow(${idx})"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `).join('');
        }
    }

    function addInputRow() {
        if (!DATA.management_guidelines.inputs) DATA.management_guidelines.inputs = [];
        DATA.management_guidelines.inputs.push({ from: '*', input: 'Input baru...' });
        renderInputsOutputs();
    }
    function removeInputRow(idx) {
        DATA.management_guidelines.inputs.splice(idx, 1);
        renderInputsOutputs();
    }
    function addOutputRow() {
        if (!DATA.management_guidelines.outputs) DATA.management_guidelines.outputs = [];
        DATA.management_guidelines.outputs.push({ output: 'Output baru...', to: 'AI1, DS1' });
        renderInputsOutputs();
    }
    function removeOutputRow(idx) {
        DATA.management_guidelines.outputs.splice(idx, 1);
        renderInputsOutputs();
    }

    // 4. RACI Chart
    function renderRaciTable() {
        const tbody = document.getElementById('raciTableBody');
        const roles = DATA.management_guidelines?.raci?.roles || [];
        const activities = DATA.management_guidelines?.raci?.activities || [];

        tbody.innerHTML = activities.map((act, actIdx) => `
            <tr>
                <td class="c4-raci-activity-cell">
                    ${!isInputMode ? escapeHtml(act.activity) : `
                        <input type="text" class="c4-inline-input" value="${escapeHtml(act.activity)}" 
                               oninput="DATA.management_guidelines.raci.activities[${actIdx}].activity = this.value">
                    `}
                </td>
                ${roles.map((role, roleIdx) => {
                    const rVal = act.raci[roleIdx] || '';
                    const rClass = rVal ? 'has-' + rVal.charAt(0).toLowerCase() : '';
                    return `
                        <td class="c4-raci-val-cell ${rClass}" 
                            onclick="cycleRaci(${actIdx}, ${roleIdx})"
                            title="${isInputMode ? 'Klik untuk mengganti R / A / C / I' : ''}">
                            ${escapeHtml(rVal)}
                        </td>
                    `;
                }).join('')}
                ${isInputMode ? `
                    <td class="text-center align-middle">
                        <button class="btn-action-del" onclick="removeRaciActivity(${actIdx})"><i class="fas fa-trash"></i></button>
                    </td>
                ` : ''}
            </tr>
        `).join('');
    }

    function cycleRaci(actIdx, roleIdx) {
        if (!isInputMode) return;
        const current = (DATA.management_guidelines.raci.activities[actIdx].raci[roleIdx] || '').toUpperCase();
        let next = '';
        if (current === '') next = 'R';
        else if (current === 'R') next = 'A';
        else if (current === 'A') next = 'C';
        else if (current === 'C') next = 'I';
        else if (current === 'I') next = 'A/R';
        else next = '';

        DATA.management_guidelines.raci.activities[actIdx].raci[roleIdx] = next;
        renderRaciTable();
    }

    function addRaciActivity() {
        if (!DATA.management_guidelines.raci.activities) DATA.management_guidelines.raci.activities = [];
        const roleCount = (DATA.management_guidelines.raci.roles || []).length;
        DATA.management_guidelines.raci.activities.push({
            activity: 'Aktivitas proses baru...',
            raci: new Array(roleCount).fill(''),
        });
        renderRaciTable();
    }
    function removeRaciActivity(idx) {
        DATA.management_guidelines.raci.activities.splice(idx, 1);
        renderRaciTable();
    }

    // 5. Goals & Metrics Boxes
    function renderGoalsAndMetrics() {
        const gm = DATA.management_guidelines?.goals_and_metrics || {};

        renderGmBox('itGoalsBox', gm.it_goals || [], 'it_goals', 'Tambah IT Goal');
        renderGmBox('processGoalsBox', gm.process_goals || [], 'process_goals', 'Tambah Process Goal');
        renderGmBox('activitiesGoalsBox', gm.activities_goals || [], 'activities_goals', 'Tambah Activity Goal');

        renderGmBox('itMetricsBox', gm.it_metrics || [], 'it_metrics', 'Tambah IT Metric');
        renderGmBox('processMetricsBox', gm.process_metrics || [], 'process_metrics', 'Tambah Process Metric');
        renderGmBox('activitiesMetricsBox', gm.activities_metrics || [], 'activities_metrics', 'Tambah Activity Metric');
    }

    function renderGmBox(containerId, list, key, addLabel) {
        const box = document.getElementById(containerId);
        if (!box) return;

        if (!isInputMode) {
            box.innerHTML = `<ul>${list.map(i => `<li>${escapeHtml(i)}</li>`).join('')}</ul>`;
        } else {
            box.innerHTML = `
                <div>
                    ${list.map((item, idx) => `
                        <div class="c4-bullet-edit-row">
                            <input type="text" class="c4-inline-input" value="${escapeHtml(item)}" 
                                   oninput="DATA.management_guidelines.goals_and_metrics['${key}'][${idx}] = this.value">
                            <button class="btn-action-del" onclick="removeGmItem('${key}', ${idx})"><i class="fas fa-times"></i></button>
                        </div>
                    `).join('')}
                    <button class="btn-action-add mt-1" onclick="addGmItem('${key}')">
                        <i class="fas fa-plus me-1"></i> ${addLabel}
                    </button>
                </div>
            `;
        }
    }

    function addGmItem(key) {
        if (!DATA.management_guidelines.goals_and_metrics[key]) {
            DATA.management_guidelines.goals_and_metrics[key] = [];
        }
        DATA.management_guidelines.goals_and_metrics[key].push('Indikator/tujuan baru...');
        renderGoalsAndMetrics();
    }
    function removeGmItem(key, idx) {
        DATA.management_guidelines.goals_and_metrics[key].splice(idx, 1);
        renderGoalsAndMetrics();
    }

    // 6. Maturity Model Levels
    function renderMaturityLevels() {
        const container = document.getElementById('maturityLevelsContainer');
        const levels = DATA.maturity_model?.levels || {};

        container.innerHTML = Object.keys(levels).map(lvl => {
            const item = levels[lvl];
            if (!isInputMode) {
                return `
                    <div class="c4-maturity-level-item">
                        <span class="c4-maturity-badge">${lvl} ${escapeHtml(item.name)}</span> when<br>
                        <span class="text-dark">${escapeHtml(item.desc || item.description || '')}</span>
                    </div>
                `;
            } else {
                return `
                    <div class="c4-maturity-level-item card p-3 mb-2 bg-light border">
                        <div class="d-flex gap-2 align-items-center mb-1">
                            <span class="badge bg-dark fw-bold">Level ${lvl}</span>
                            <input type="text" class="c4-inline-input fw-bold" style="width: 250px;" 
                                   value="${escapeHtml(item.name)}" 
                                   oninput="DATA.maturity_model.levels[${lvl}].name = this.value">
                            <span class="text-muted fw-bold">when</span>
                        </div>
                        <textarea class="c4-inline-input" rows="3" 
                                  oninput="DATA.maturity_model.levels[${lvl}].desc = this.value">${escapeHtml(item.desc || item.description || '')}</textarea>
                    </div>
                `;
            }
        }).join('');
    }

    // Initialize Pentagon Colors on load
    function initPentagonColors() {
        const focus = DATA.it_governance_focus || {};
        ['strategic_alignment', 'value_delivery', 'risk_management', 'resource_management', 'performance_measurement'].forEach(k => {
            updatePentagonUI(k, focus[k] || '');
        });

        // Resources
        const res = DATA.it_resources || {};
        ['applications', 'information', 'infrastructure', 'people'].forEach(k => {
            const check = document.getElementById(`resCheck_${k}`);
            if (check) check.textContent = res[k] ? '✔' : '';
        });
    }

    // AJAX: Tambah GAMO Baru (Create)
    async function handleCreateCobit4Gamo(e) {
        e.preventDefault();
        const code = document.getElementById('newGamoCode').value.trim();
        const title = document.getElementById('newGamoTitle').value.trim();
        const domain_code = document.getElementById('newGamoDomain').value;
        const description = document.getElementById('newGamoDesc').value.trim();

        if (!code || !title) {
            Swal.fire({ icon: 'warning', title: 'Data Kurang', text: 'Kode proses dan nama GAMO wajib diisi!' });
            return;
        }

        try {
            Swal.fire({
                title: 'Menyimpan GAMO Baru...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            const res = await fetch("{{ route('cobit4.create') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    code: code,
                    title: title,
                    domain_code: domain_code,
                    description: description,
                    focus_area_id: FOCUS_AREA_ID
                })
            });

            const result = await res.json();
            if (result.success) {
                await Swal.fire({
                    icon: 'success',
                    title: 'GAMO Berhasil Dibuat!',
                    text: result.message,
                    timer: 1800,
                    showConfirmButton: false
                });
                if (result.redirect_url) {
                    window.location.href = result.redirect_url;
                } else {
                    window.location.reload();
                }
            } else {
                throw new Error(result.message || 'Gagal menambahkan GAMO.');
            }
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Gagal', text: err.message });
        }
    }

    // AJAX: Save Data to MySQL Database
    async function saveCobit4Data() {
        try {
            Swal.fire({
                title: 'Menyimpan ke Database...',
                text: 'Sedang menyimpan data COBIT 4.1 ke database MySQL.',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            const res = await fetch("{{ route('cobit4.save') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    process_code: PROCESS_CODE,
                    objective_id: OBJECTIVE_ID,
                    focus_area_id: FOCUS_AREA_ID,
                    data: DATA,
                })
            });

            const result = await res.json();
            if (result.success) {
                await Swal.fire({
                    icon: 'success',
                    title: 'Berhasil Disimpan!',
                    text: result.message || 'Perubahan COBIT 4.1 berhasil disimpan ke database.',
                    timer: 2000,
                    showConfirmButton: false,
                });
                toggleCobit4InputMode(false);
            } else {
                throw new Error(result.message || 'Gagal menyimpan perubahan.');
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Menyimpan',
                text: err.message,
            });
        }
    }


    // AJAX: Hapus GAMO
    async function deleteCurrentGamo() {
        const confirm = await Swal.fire({
            title: `Hapus GAMO ${PROCESS_CODE}?`,
            text: `Proses ${PROCESS_CODE} - ${DATA.title} beserta seluruh datanya akan dihapus dari database.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            confirmButtonText: 'Ya, Hapus Sekarang!',
            cancelButtonText: 'Batal'
        });

        if (!confirm.isConfirmed) return;

        try {
            Swal.fire({ title: 'Menghapus...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            const res = await fetch("{{ url('objectives/cobit4') }}/" + encodeURIComponent(PROCESS_CODE), {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ focus_area_id: FOCUS_AREA_ID })
            });

            const result = await res.json();
            if (result.success) {
                await Swal.fire({ icon: 'success', title: 'Terhapus!', text: result.message, timer: 1800, showConfirmButton: false });
                window.location.href = "{{ url('objectives') }}?focus_area=" + FOCUS_AREA_ID;
            } else {
                throw new Error(result.message || 'Gagal menghapus proses.');
            }
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Gagal', text: err.message });
        }
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Initialize on document ready
    document.addEventListener('DOMContentLoaded', () => {
        renderAllDynamicSections();
        switchCobit4Page('page1', document.getElementById('btnPage1'));
    });
</script>
@endsection
