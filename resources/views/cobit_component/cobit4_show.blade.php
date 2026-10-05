@extends('layouts.app')

@section('content')
<style>
    /* ==========================================================================
       COBIT 4.1 OFFICIAL MANUAL & INPUT MODE STYLING
       Faithful recreation of ISACA COBIT 4.1 Process Specification + Live Editing
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

    /* Tab Navigation */
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
        padding-top: 20px;
    }

    .c4-slanted-col-header {
        height: 90px;
        position: relative;
        vertical-align: bottom;
        padding: 0;
        width: 34px;
        min-width: 34px;
    }

    .c4-slanted-label {
        transform: rotate(-45deg);
        transform-origin: bottom left;
        white-space: nowrap;
        position: absolute;
        bottom: 6px;
        left: 14px;
        font-size: 0.74rem;
        font-weight: 600;
        color: #1e293b;
        letter-spacing: -0.01em;
    }

    .c4-criteria-cell {
        width: 34px;
        height: 34px;
        border: 1px solid #7da0c5;
        background-color: var(--c4-neutral-box);
        text-align: center;
        vertical-align: middle;
        font-weight: 800;
        font-size: 0.95rem;
        transition: all 0.15s ease;
    }

    .c4-criteria-cell.p-active {
        background-color: var(--c4-primary-box);
        color: #ffffff;
    }

    .c4-criteria-cell.s-active {
        background-color: var(--c4-secondary-box);
        color: #ffffff;
    }

    /* Editable Criteria in Input Mode */
    .input-mode-on .c4-criteria-cell {
        cursor: pointer;
        position: relative;
    }
    .input-mode-on .c4-criteria-cell:hover {
        outline: 2px dashed #f59e0b;
        filter: brightness(0.95);
    }

    /* 3D Domain Buttons */
    .c4-domain-btn-stack {
        display: flex;
        flex-direction: column;
        gap: 0.45rem;
        width: 170px;
        margin-left: auto;
    }

    .c4-domain-btn {
        background: linear-gradient(180deg, #276497 0%, #154674 50%, #0d355b 100%);
        border: 2px solid #5a8ab8;
        border-radius: 7px;
        padding: 0.55rem 0.6rem;
        color: #ffffff !important;
        font-weight: 700;
        font-size: 0.82rem;
        line-height: 1.15;
        text-align: center;
        text-decoration: none;
        box-shadow: inset 1px 1px 1px rgba(255,255,255,0.4), 0 3px 6px rgba(0,0,0,0.22);
        display: block;
        transition: transform 0.1s ease, filter 0.15s ease;
    }

    .c4-domain-btn:hover {
        filter: brightness(1.1);
        transform: translateY(-1px);
    }

    .c4-domain-btn.active {
        background: linear-gradient(180deg, #09335a 0%, #05213b 100%);
        border-color: #84b3de;
        box-shadow: inset 1px 1px 2px rgba(0,0,0,0.5), 0 0 8px rgba(33, 115, 186, 0.6);
        outline: 2px solid #9fc8f0;
    }

    /* Waterfall Flow */
    .c4-waterfall-box {
        margin: 2rem 0;
        font-size: 0.93rem;
        line-height: 1.6;
    }

    .c4-waterfall-step {
        margin-bottom: 0.85rem;
    }

    .c4-wf-lead {
        font-weight: 800;
        color: #111827;
        margin-bottom: 0.2rem;
    }

    .c4-wf-content {
        color: #1f2937;
    }

    .c4-wf-indent-1 { padding-left: 1.75rem; }
    .c4-wf-indent-2 { padding-left: 3.5rem; }
    .c4-wf-indent-3 { padding-left: 5.25rem; }
    .c4-wf-indent-4 { padding-left: 7rem; }
    .c4-wf-indent-5 { padding-left: 8.75rem; }

    .c4-wf-bullets {
        list-style-type: none;
        padding-left: 0;
        margin: 0.25rem 0;
    }

    .c4-wf-bullets li {
        position: relative;
        padding-left: 1.25rem;
        margin-bottom: 0.4rem;
    }

    .c4-wf-bullets li::before {
        content: "•";
        position: absolute;
        left: 0;
        font-weight: bold;
        color: #111827;
        font-size: 1.1rem;
        line-height: 1;
    }

    /* Pentagon & Resources */
    .c4-pentagon-wrap {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .c4-legend-row {
        display: flex;
        gap: 1.25rem;
        margin-top: 0.75rem;
        font-size: 0.78rem;
        font-weight: 700;
    }

    .c4-legend-item {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .c4-legend-box {
        width: 13px;
        height: 13px;
        display: inline-block;
        border: 1px solid #64748b;
    }

    .c4-resources-table-wrap {
        display: inline-block;
        padding-top: 20px;
        margin-left: auto;
    }

    .c4-resource-cell {
        width: 34px;
        height: 34px;
        border: 1px solid #7da0c5;
        background-color: var(--c4-primary-box);
        color: #ffffff;
        text-align: center;
        vertical-align: middle;
        font-size: 0.95rem;
        font-weight: bold;
        transition: all 0.15s ease;
    }

    .input-mode-on .c4-resource-cell {
        cursor: pointer;
    }
    .input-mode-on .c4-resource-cell:hover {
        outline: 2px dashed #f59e0b;
    }

    /* Page Footer */
    .c4-page-footer {
        border-top: 1px solid #1e293b;
        margin-top: 2.5rem;
        padding-top: 0.6rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.75rem;
        color: #475569;
        font-style: italic;
    }

    .c4-page-num {
        font-weight: 800;
        font-size: 0.95rem;
        font-style: normal;
        color: #1e293b;
    }

    /* Inputs/Outputs Tables */
    .c4-inout-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.85rem;
        margin-bottom: 1.25rem;
    }

    .c4-inout-table th {
        background-color: var(--c4-navy);
        color: #ffffff;
        padding: 0.45rem 0.75rem;
        font-weight: 700;
        border: 1px solid #072b4c;
    }

    .c4-inout-table td {
        border: 1px solid #cbd5e1;
        padding: 0.45rem 0.65rem;
        vertical-align: middle;
        color: #1e293b;
        background: #f8fafc;
    }

    .c4-inout-table tr:nth-child(even) td {
        background: #ffffff;
    }

    /* RACI Table */
    .c4-raci-section {
        margin: 2rem 0;
    }

    .c4-raci-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.82rem;
    }

    .c4-raci-th-activity {
        width: 36%;
        vertical-align: bottom;
        font-weight: 800;
        padding: 0.5rem;
        border-bottom: 2px solid #334155;
    }

    .c4-raci-th-func {
        text-align: center;
        font-weight: 800;
        font-style: italic;
        padding: 0.35rem;
        border-bottom: 1px solid #cbd5e1;
    }

    .c4-raci-slanted-th {
        height: 125px;
        position: relative;
        vertical-align: bottom;
        padding: 0;
        width: 38px;
        min-width: 38px;
    }

    .c4-raci-slanted-label {
        transform: rotate(-60deg);
        transform-origin: bottom left;
        white-space: nowrap;
        position: absolute;
        bottom: 8px;
        left: 18px;
        font-size: 0.73rem;
        font-weight: 700;
        color: #1e293b;
        letter-spacing: -0.01em;
        width: 130px;
        text-align: left;
    }

    .c4-raci-activity-cell {
        padding: 0.5rem 0.65rem;
        border: 1px solid #cbd5e1;
        font-weight: 500;
        color: #1e293b;
    }

    .c4-raci-val-cell {
        border: 1px solid #cbd5e1;
        text-align: center;
        vertical-align: middle;
        font-weight: 800;
        font-size: 0.85rem;
        background-color: #ffffff;
        transition: background-color 0.15s;
    }

    .c4-raci-val-cell.has-r { background-color: #f1f5f9; color: #0a3d68; }
    .c4-raci-val-cell.has-a { background-color: #eff6ff; color: #1e40af; }
    .c4-raci-val-cell.has-c { background-color: #f8fafc; color: #475569; }
    .c4-raci-val-cell.has-i { background-color: #f8fafc; color: #64748b; }

    .input-mode-on .c4-raci-val-cell {
        cursor: pointer;
    }
    .input-mode-on .c4-raci-val-cell:hover {
        background-color: #fef08a !important;
        outline: 1px solid #ca8a04;
    }

    /* Goals & Metrics */
    .c4-gm-grid {
        display: grid;
        grid-template-columns: 36px 1fr 1fr 1fr;
        gap: 0.65rem;
        margin-top: 1.5rem;
    }

    .c4-gm-sidebar {
        background-color: var(--c4-navy);
        color: #ffffff;
        writing-mode: vertical-rl;
        transform: rotate(180deg);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        letter-spacing: 0.1em;
        font-size: 0.85rem;
        border-radius: 4px;
        padding: 0.5rem 0;
    }

    .c4-gm-card {
        border: 1px solid #94a3b8;
        border-radius: 4px;
        display: flex;
        flex-direction: column;
        background: #ffffff;
        position: relative;
    }

    .c4-gm-header {
        background-color: var(--c4-navy);
        color: #ffffff;
        font-weight: 700;
        font-size: 0.82rem;
        padding: 0.35rem 0.65rem;
        text-align: center;
    }

    .c4-gm-body {
        padding: 0.75rem;
        font-size: 0.8rem;
        line-height: 1.45;
        flex-grow: 1;
    }

    .c4-gm-body ul {
        list-style: none;
        padding-left: 0;
        margin: 0;
    }

    .c4-gm-body li {
        position: relative;
        padding-left: 1rem;
        margin-bottom: 0.4rem;
    }

    .c4-gm-body li::before {
        content: "•";
        position: absolute;
        left: 0;
        font-weight: bold;
    }

    .c4-gm-arrow-badge {
        font-size: 0.72rem;
        font-weight: 700;
        color: #0a3d68;
        background: #e2e8f0;
        padding: 0.1rem 0.4rem;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;
    }

    /* Maturity Model */
    .c4-maturity-preamble {
        font-style: italic;
        font-size: 0.95rem;
        line-height: 1.55;
        color: #374151;
        margin-bottom: 1.5rem;
    }

    .c4-maturity-level-item {
        margin-bottom: 1.35rem;
        font-size: 0.93rem;
        line-height: 1.6;
    }

    .c4-maturity-badge {
        font-weight: 800;
        color: #111827;
    }

    /* ==========================================================================
       INPUT MODE CONTROLS (Form fields & buttons)
       ========================================================================== */
    .c4-edit-field {
        display: none;
    }
    .input-mode-on .c4-edit-field {
        display: block !important;
    }
    .input-mode-on .c4-view-field {
        display: none !important;
    }

    .c4-inline-input {
        width: 100%;
        border: 1px solid #f59e0b;
        background-color: #fffbeb;
        padding: 0.35rem 0.6rem;
        border-radius: 5px;
        font-size: 0.92rem;
        font-weight: 500;
        transition: all 0.2s;
    }
    .c4-inline-input:focus {
        background-color: #ffffff;
        border-color: #d97706;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2);
        outline: none;
    }

    .c4-bullet-edit-row {
        display: flex;
        gap: 0.4rem;
        align-items: flex-start;
        margin-bottom: 0.45rem;
    }

    .btn-action-del {
        background: none;
        border: none;
        color: #ef4444;
        cursor: pointer;
        padding: 0.2rem 0.4rem;
        border-radius: 4px;
        transition: background 0.15s;
    }
    .btn-action-del:hover {
        background-color: #fee2e2;
    }

    .btn-action-add {
        font-size: 0.78rem;
        font-weight: 700;
        padding: 0.2rem 0.65rem;
        border-radius: 5px;
        border: 1px dashed #f59e0b;
        background-color: #fffbeb;
        color: #b45309;
        cursor: pointer;
        transition: all 0.15s;
    }
    .btn-action-add:hover {
        background-color: #fef3c7;
        border-color: #d97706;
    }

    /* Print Styles */
    @media print {
        body { background: #ffffff !important; color: #000000 !important; }
        .navbar, .c4-toolbar, .c4-nav-pills, .c4-input-mode-banner, .btn, .btn-action-add, .btn-action-del {
            display: none !important;
        }
        .c4-wrapper { padding: 0 !important; background: transparent !important; }
        .c4-book-page { box-shadow: none !important; border: none !important; margin-bottom: 0 !important; page-break-after: always; }
        .c4-edit-field { display: none !important; }
        .c4-view-field { display: block !important; }
    }
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
                    <small class="text-muted">Domain: <strong>{{ $cobit4Data['domain_name'] }} ({{ $cobit4Data['domain_code'] }})</strong></small>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <!-- INPUT MODE TOGGLE BUTTON -->
                <button type="button" class="btn btn-outline-warning fw-bold btn-sm shadow-sm" id="btnInputModeToggle" onclick="toggleCobit4InputMode()">
                    <i class="fas fa-pen-to-square me-1"></i> Input Mode: <span id="inputModeStatusText">OFF</span>
                </button>

                <!-- Select Objective Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-outline-primary btn-sm dropdown-toggle fw-bold" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-list me-1"></i> Pilih Objective COBIT 4.1
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow" style="max-height: 420px; overflow-y: auto; min-width: 320px;">
                        <li class="dropdown-header fw-bold text-primary small text-uppercase">Daftar 34 Proses COBIT 4.1</li>
                        @foreach($allObjectives as $itemObj)
                            @php
                                $itemCode = app(\App\Services\Cobit4\Cobit4Service::class)->resolveProcessCode($itemObj->objective_id);
                                $isActive = ($itemObj->objective_id === $objective->objective_id);
                            @endphp
                            <li>
                                <a class="dropdown-item d-flex justify-content-between align-items-center {{ $isActive ? 'active' : '' }}" 
                                   href="{{ route('cobit_component.show', ['id' => $itemObj->objective_id, 'focus_area' => $focusAreaId]) }}">
                                    <span>
                                        <strong class="font-monospace">{{ $itemCode }}</strong> - {{ Str::limit($itemObj->objective, 28) }}
                                    </span>
                                    @if($isActive) <i class="fas fa-check small ms-2"></i> @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Model Switcher Dropdown -->
                @php
                    $switcherModels = $allFocusAreas ?? \App\Models\MstFocusArea::orderBy('version', 'desc')->get();
                    $groupedModels = $switcherModels->groupBy(fn($m) => $m->version ?: '2019');
                @endphp
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle fw-bold shadow-sm" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-exchange-alt me-1"></i> Ganti Model
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow" style="min-width: 260px;">
                        <li class="dropdown-header fw-bold text-uppercase text-primary small">
                            <i class="fas fa-cubes me-1"></i> COBIT 2019 Models
                        </li>
                        @foreach($groupedModels->get('2019', collect()) as $m2019)
                            @php
                                $firstObjM = \App\Models\MstObjective::where('focus_area_id', $m2019->id)->first();
                                $mRoute = $firstObjM ? route('cobit_component.show', ['id' => $firstObjM->objective_id, 'focus_area' => $m2019->id]) : route('focus-areas.show', $m2019->id);
                            @endphp
                            <li>
                                <a class="dropdown-item {{ ($focusAreaId ?? 1) == $m2019->id ? 'active' : '' }}" href="{{ $mRoute }}">
                                    {{ $m2019->name }}
                                </a>
                            </li>
                        @endforeach

                        <li><hr class="dropdown-divider"></li>
                        <li class="dropdown-header fw-bold text-uppercase text-success small">
                            <i class="fas fa-sitemap me-1"></i> COBIT 5
                        </li>
                        @foreach($groupedModels->get('5', collect()) as $m5)
                            @php
                                $firstObjM5 = \App\Models\MstObjective::where('focus_area_id', $m5->id)->first();
                                $m5Route = $firstObjM5 ? route('cobit_component.show', ['id' => $firstObjM5->objective_id, 'focus_area' => $m5->id]) : route('focus-areas.show', $m5->id);
                            @endphp
                            <li>
                                <a class="dropdown-item {{ ($focusAreaId ?? 1) == $m5->id ? 'active' : '' }}" href="{{ $m5Route }}">
                                    {{ $m5->name }}
                                </a>
                            </li>
                        @endforeach

                        <li><hr class="dropdown-divider"></li>
                        <li class="dropdown-header fw-bold text-uppercase text-warning small">
                            <i class="fas fa-certificate me-1"></i> COBIT 4.1
                        </li>
                        @foreach($groupedModels->get('4.1', collect()) as $m4)
                            <li>
                                <a class="dropdown-item active fw-bold" href="#">
                                    {{ $m4->name }} <i class="fas fa-check small text-white ms-1"></i>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Print / PDF Button -->
                <button class="btn btn-dark btn-sm fw-bold shadow-sm" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Cetak / PDF
                </button>
            </div>
        </div>

        <!-- ====================================================================
             ACTIVE INPUT MODE ACTION BANNER
             ==================================================================== -->
        <div class="c4-input-mode-banner" id="c4InputModeBanner">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-triangle text-warning fs-4"></i>
                <div>
                    <strong class="text-dark">Mode Input Aktif!</strong>
                    <div class="small text-muted">
                        Anda dapat mengubah data proses secara langsung, mengklik sel kriteria (P/S), mengatur RACI, dan menambah/mengedit Control Objectives.
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-success btn-sm fw-bold shadow-sm px-3" onclick="saveCobit4Data()">
                    <i class="fas fa-save me-1"></i> Simpan Perubahan
                </button>
                <button class="btn btn-outline-danger btn-sm fw-bold" onclick="resetCobit4Data()">
                    <i class="fas fa-undo me-1"></i> Reset ke Standar ISACA
                </button>
                <button class="btn btn-secondary btn-sm" onclick="toggleCobit4InputMode(false)">
                    Selesai / Tutup
                </button>
            </div>
        </div>

        <!-- ====================================================================
             TAB SWITCHER BUTTONS (INTERACTIVE MODE OR ALL 4 PAGES)
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
            <button class="c4-nav-btn ms-auto bg-light text-primary border-primary" id="btnAllPages" onclick="switchCobit4Page('all', this)">
                <i class="fas fa-book-open"></i> 📖 Tampilan Buku Lengkap (Semua 4 Halaman)
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
                        <label class="form-label small fw-bold text-muted mb-1">Judul Proses:</label>
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

                <!-- Control Statement Cascading Flow -->
                <div class="c4-waterfall-box">
                    <div class="c4-waterfall-step">
                        <div class="c4-wf-lead">Control over the IT process of</div>
                    </div>
                    <div class="c4-waterfall-step c4-wf-indent-1">
                        <div class="c4-wf-content c4-view-field" id="viewWfControlOver">
                            {{ $cobit4Data['control_statement']['control_over'] ?? strtolower($cobit4Data['title']) }}
                        </div>
                        <div class="c4-edit-field">
                            <input type="text" class="c4-inline-input" id="inputWfControlOver" 
                                   value="{{ $cobit4Data['control_statement']['control_over'] ?? strtolower($cobit4Data['title']) }}"
                                   oninput="DATA.control_statement.control_over = this.value">
                        </div>
                    </div>

                    <div class="c4-waterfall-step c4-wf-indent-2">
                        <div class="c4-wf-lead">that satisfies the business requirement for IT of</div>
                    </div>
                    <div class="c4-waterfall-step c4-wf-indent-3">
                        <div class="c4-wf-content c4-view-field" id="viewWfSatisfies">
                            {{ $cobit4Data['control_statement']['satisfies_requirement'] ?? '' }}
                        </div>
                        <div class="c4-edit-field">
                            <textarea class="c4-inline-input" id="inputWfSatisfies" rows="2"
                                      oninput="DATA.control_statement.satisfies_requirement = this.value">{{ $cobit4Data['control_statement']['satisfies_requirement'] ?? '' }}</textarea>
                        </div>
                    </div>

                    <div class="c4-waterfall-step c4-wf-indent-3">
                        <div class="c4-wf-lead">by focusing on</div>
                    </div>
                    <div class="c4-waterfall-step c4-wf-indent-4">
                        <div class="c4-wf-content c4-view-field" id="viewWfFocusing">
                            {{ $cobit4Data['control_statement']['focusing_on'] ?? '' }}
                        </div>
                        <div class="c4-edit-field">
                            <textarea class="c4-inline-input" id="inputWfFocusing" rows="2"
                                      oninput="DATA.control_statement.focusing_on = this.value">{{ $cobit4Data['control_statement']['focusing_on'] ?? '' }}</textarea>
                        </div>
                    </div>

                    <div class="c4-waterfall-step c4-wf-indent-4">
                        <div class="c4-wf-lead">is achieved by</div>
                    </div>
                    <div class="c4-waterfall-step c4-wf-indent-5">
                        <ul class="c4-wf-bullets c4-view-field" id="viewAchievedList">
                            @foreach($cobit4Data['control_statement']['achieved_by'] ?? [] as $achItem)
                                <li>{{ $achItem }}</li>
                            @endforeach
                        </ul>
                        <div class="c4-edit-field" id="editAchievedContainer">
                            <!-- Populated dynamically via JS -->
                        </div>
                    </div>

                    <div class="c4-waterfall-step c4-wf-indent-5">
                        <div class="c4-wf-lead">and is measured by</div>
                    </div>
                    <div class="c4-waterfall-step" style="padding-left: 10.5rem;">
                        <ul class="c4-wf-bullets c4-view-field" id="viewMeasuredList">
                            @foreach($cobit4Data['control_statement']['measured_by'] ?? [] as $measItem)
                                <li>{{ $measItem }}</li>
                            @endforeach
                        </ul>
                        <div class="c4-edit-field" id="editMeasuredContainer">
                            <!-- Populated dynamically via JS -->
                        </div>
                    </div>
                </div>

                <!-- Bottom Row: IT Governance Focus Pentagon & IT Resources Checklist -->
                <div class="row align-items-end mt-4 pt-3 border-top">
                    <!-- Left: Pentagon Diagram -->
                    <div class="col-md-6 col-lg-5">
                        <div class="c4-pentagon-wrap">
                            <svg viewBox="0 0 280 260" width="250" height="230" style="overflow: visible;">
                                <defs>
                                    <filter id="c4-drop" x="-10%" y="-10%" width="130%" height="130%">
                                        <feDropShadow dx="2" dy="3" stdDeviation="3" flood-opacity="0.25"/>
                                    </filter>
                                </defs>
                                <g filter="url(#c4-drop)">
                                    <!-- Strategic Alignment -->
                                    <polygon points="140,20 50,85 82,125 140,95" 
                                             id="poly_strategic_alignment" onclick="cyclePentagon('strategic_alignment')"
                                             stroke="#3b6998" stroke-width="1.5" style="cursor: pointer;" />
                                    <!-- Value Delivery -->
                                    <polygon points="140,20 230,85 198,125 140,95" 
                                             id="poly_value_delivery" onclick="cyclePentagon('value_delivery')"
                                             stroke="#3b6998" stroke-width="1.5" style="cursor: pointer;" />
                                    <!-- Risk Management -->
                                    <polygon points="230,85 195,195 175,160 198,125" 
                                             id="poly_risk_management" onclick="cyclePentagon('risk_management')"
                                             stroke="#94a3b8" stroke-width="1.5" style="cursor: pointer;" />
                                    <!-- Resource Management -->
                                    <polygon points="195,195 85,195 105,160 175,160" 
                                             id="poly_resource_management" onclick="cyclePentagon('resource_management')"
                                             stroke="#94a3b8" stroke-width="1.5" style="cursor: pointer;" />
                                    <!-- Performance Measurement -->
                                    <polygon points="85,195 50,85 82,125 105,160" 
                                             id="poly_performance_measurement" onclick="cyclePentagon('performance_measurement')"
                                             stroke="#94a3b8" stroke-width="1.5" style="cursor: pointer;" />

                                    <!-- Center Pentagon: IT Governance -->
                                    <polygon points="140,95 198,125 175,160 105,160 82,125" 
                                             fill="#e2e8f0" stroke="#475569" stroke-width="1.5" />
                                </g>

                                <text x="140" y="137" font-size="7.5" font-weight="800" text-anchor="middle" fill="#0f172a">IT GOVERNANCE</text>

                                <text x="100" y="55" font-size="6.8" font-weight="800" text-anchor="middle" id="txt_strategic_alignment" transform="rotate(-35, 100, 55)" pointer-events="none">
                                    <tspan x="100" dy="0">STRATEGIC</tspan>
                                    <tspan x="100" dy="7.5">ALIGNMENT</tspan>
                                </text>

                                <text x="180" y="55" font-size="6.8" font-weight="800" text-anchor="middle" id="txt_value_delivery" transform="rotate(35, 180, 55)" pointer-events="none">
                                    <tspan x="180" dy="0">VALUE</tspan>
                                    <tspan x="180" dy="7.5">DELIVERY</tspan>
                                </text>

                                <text x="202" y="152" font-size="6.5" font-weight="700" text-anchor="middle" id="txt_risk_management" transform="rotate(80, 202, 152)" pointer-events="none">
                                    <tspan x="202" dy="0">RISK MANAGEMENT</tspan>
                                </text>

                                <text x="140" y="184" font-size="6.5" font-weight="700" text-anchor="middle" id="txt_resource_management" pointer-events="none">
                                    RESOURCE MANAGEMENT
                                </text>

                                <text x="76" y="152" font-size="6" font-weight="700" text-anchor="middle" id="txt_performance_measurement" transform="rotate(-80, 76, 152)" pointer-events="none">
                                    <tspan x="76" dy="0">PERFORMANCE</tspan>
                                    <tspan x="76" dy="7">MEASUREMENT</tspan>
                                </text>
                            </svg>
                            <div class="c4-legend-row">
                                <div class="c4-legend-item">
                                    <span class="c4-legend-box" style="background-color: #13467b;"></span> Primary
                                </div>
                                <div class="c4-legend-item">
                                    <span class="c4-legend-box" style="background-color: #8bb4de;"></span> Secondary
                                </div>
                            </div>
                            <div class="c4-edit-field mt-1 small text-warning-emphasis">
                                <i class="fas fa-hand-pointer me-1"></i> Klik segmen untuk beralih (Primary ➔ Secondary ➔ Netral).
                            </div>
                        </div>
                    </div>

                    <!-- Right: IT Resources Checklist -->
                    <div class="col-md-6 col-lg-7 text-end">
                        <div class="c4-resources-table-wrap">
                            <table style="border-collapse: separate; border-spacing: 0;">
                                <tr>
                                    @php
                                        $resKeys = ['applications', 'information', 'infrastructure', 'people'];
                                        $resLabels = ['Applications', 'Information', 'Infrastructure', 'People'];
                                    @endphp
                                    @foreach($resKeys as $rkey)
                                        <td class="c4-resource-cell" id="resCell_{{ $rkey }}" 
                                            onclick="toggleResource('{{ $rkey }}')"
                                            title="Klik untuk mengubah centang">
                                            <span id="resCheck_{{ $rkey }}">✔</span>
                                        </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    @foreach($resLabels as $rlabel)
                                        <th class="c4-slanted-col-header" style="height: 80px;">
                                            <span class="c4-slanted-label">{{ $rlabel }}</span>
                                        </th>
                                    @endforeach
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ====================================================================
             PAGE 2: CONTROL OBJECTIVES
             ==================================================================== -->
        <div class="c4-book-page c4-view-section" id="c4-section-page2">
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

                <!-- Objectives Container (View Mode & Input Mode) -->
                <div id="controlObjectivesContainer" class="c4-objectives-list">
                    <!-- Populated via JS -->
                </div>

                <div class="c4-edit-field mt-3">
                    <button class="btn btn-action-add py-2 px-3 fw-bold" onclick="addControlObjective()">
                        <i class="fas fa-plus-circle me-1"></i> Tambah Control Objective Baru
                    </button>
                </div>

                <div class="c4-page-footer">
                    <div class="c4-page-num">30</div>
                    <div>© 2007 IT Governance Institute. All rights reserved. www.itgi.org</div>
                </div>
            </div>
        </div>

        <!-- ====================================================================
             PAGE 3: MANAGEMENT GUIDELINES
             ==================================================================== -->
        <div class="c4-book-page c4-view-section" id="c4-section-page3">
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

                <!-- Dual Inputs & Outputs Table -->
                <div class="row g-4 mb-4">
                    <!-- Left: Inputs -->
                    <div class="col-lg-6">
                        <table class="c4-inout-table">
                            <thead>
                                <tr>
                                    <th style="width: 24%;">From</th>
                                    <th>Inputs</th>
                                    <th class="c4-edit-field" style="width: 12%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="inputsTableBody">
                                <!-- Populated via JS -->
                            </tbody>
                        </table>
                        <div class="c4-edit-field mb-2">
                            <button class="btn btn-action-add" onclick="addInputRow()">
                                <i class="fas fa-plus me-1"></i> Tambah Baris Input
                            </button>
                        </div>
                        <div class="small text-muted fst-italic mt-1">* Inputs from outside CobiT</div>
                    </div>

                    <!-- Right: Outputs -->
                    <div class="col-lg-6">
                        <table class="c4-inout-table">
                            <thead>
                                <tr>
                                    <th style="width: 50%;">Outputs</th>
                                    <th>To</th>
                                    <th class="c4-edit-field" style="width: 12%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="outputsTableBody">
                                <!-- Populated via JS -->
                            </tbody>
                        </table>
                        <div class="c4-edit-field mb-2">
                            <button class="btn btn-action-add" onclick="addOutputRow()">
                                <i class="fas fa-plus me-1"></i> Tambah Baris Output
                            </button>
                        </div>
                    </div>
                </div>

                <!-- RACI Chart -->
                <div class="c4-raci-section">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h6 class="fw-bold mb-0 text-dark">RACI Chart</h6>
                        <div class="c4-edit-field">
                            <button class="btn btn-action-add" onclick="addRaciActivity()">
                                <i class="fas fa-plus me-1"></i> Tambah Baris Aktivitas RACI
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="c4-raci-table">
                            <thead>
                                <tr>
                                    <th rowspan="2" class="c4-raci-th-activity">Activities</th>
                                    <th colspan="{{ count($cobit4Data['management_guidelines']['raci']['roles'] ?? []) }}" class="c4-raci-th-func">
                                        Functions
                                    </th>
                                    <th rowspan="2" class="c4-edit-field" style="width: 40px; vertical-align: bottom;">Aksi</th>
                                </tr>
                                <tr>
                                    @foreach($cobit4Data['management_guidelines']['raci']['roles'] ?? [] as $rRole)
                                        <th class="c4-raci-slanted-th">
                                            <span class="c4-raci-slanted-label">{{ $rRole }}</span>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody id="raciTableBody">
                                <!-- Populated via JS -->
                            </tbody>
                        </table>
                    </div>
                    <div class="small text-muted fst-italic mt-2">
                        A RACI chart identifies who is <strong>R</strong>esponsible, <strong>A</strong>ccountable, <strong>C</strong>onsulted and/or <strong>I</strong>nformed.
                        <span class="c4-edit-field text-warning-emphasis ms-2">
                            (Klik pada sel RACI untuk beralih: R ➔ A ➔ C ➔ I ➔ A/R ➔ Kosong)
                        </span>
                    </div>
                </div>

                <!-- Goals and Metrics Diagram -->
                <div class="mt-4 pt-3 border-top">
                    <h6 class="fw-bold mb-3 text-dark">Goals and Metrics</h6>

                    <div class="c4-gm-grid">
                        <div class="c4-gm-sidebar" style="grid-row: 1;">Goals</div>

                        <!-- Top: IT Goals -->
                        <div class="c4-gm-card">
                            <div class="c4-gm-header">IT</div>
                            <div class="c4-gm-body" id="itGoalsBox"></div>
                        </div>

                        <!-- Top: Process Goals -->
                        <div class="c4-gm-card">
                            <div class="c4-gm-header d-flex justify-content-between align-items-center">
                                <span class="c4-gm-arrow-badge me-1"><i class="fas fa-arrow-left"></i> set</span>
                                <span>Process</span>
                                <span class="c4-gm-arrow-badge ms-1">set <i class="fas fa-arrow-right"></i></span>
                            </div>
                            <div class="c4-gm-body" id="processGoalsBox"></div>
                        </div>

                        <!-- Top: Activities Goals -->
                        <div class="c4-gm-card">
                            <div class="c4-gm-header">Activities</div>
                            <div class="c4-gm-body" id="activitiesGoalsBox"></div>
                        </div>

                        <div class="c4-gm-sidebar" style="grid-row: 2;">Metrics</div>

                        <!-- Bottom: IT Metrics -->
                        <div class="c4-gm-card">
                            <div class="c4-gm-body d-flex flex-column justify-content-between" id="itMetricsBox"></div>
                        </div>

                        <!-- Bottom: Process Metrics -->
                        <div class="c4-gm-card">
                            <div class="c4-gm-body d-flex flex-column justify-content-between" id="processMetricsBox"></div>
                        </div>

                        <!-- Bottom: Activities Metrics -->
                        <div class="c4-gm-card">
                            <div class="c4-gm-body d-flex flex-column justify-content-between" id="activitiesMetricsBox"></div>
                        </div>
                    </div>
                </div>

                <div class="c4-page-footer">
                    <div class="c4-page-num">31</div>
                    <div>© 2007 IT Governance Institute. All rights reserved. www.itgi.org</div>
                </div>
            </div>
        </div>

        <!-- ====================================================================
             PAGE 4: MATURITY MODEL
             ==================================================================== -->
        <div class="c4-book-page c4-view-section" id="c4-section-page4">
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

                <div class="c4-maturity-preamble">
                    <span class="c4-view-field" id="viewMaturityIntro">{{ $cobit4Data['maturity_model']['intro'] ?? '' }}</span>
                    <div class="c4-edit-field mb-3">
                        <label class="form-label small fw-bold text-muted mb-1">Pengantar Maturity Model:</label>
                        <textarea class="c4-inline-input" rows="2" 
                                  oninput="DATA.maturity_model.intro = this.value">{{ $cobit4Data['maturity_model']['intro'] ?? '' }}</textarea>
                    </div>
                </div>

                <div class="c4-maturity-levels" id="maturityLevelsContainer">
                    <!-- Populated via JS -->
                </div>

                <div class="c4-page-footer">
                    <div class="c4-page-num">32</div>
                    <div>© 2007 IT Governance Institute. All rights reserved. www.itgi.org</div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    // Global Master Data loaded from PHP
    const DATA = @json($cobit4Data);
    const PROCESS_CODE = @json($processCode);
    const OBJECTIVE_ID = @json($objective->objective_id);
    const FOCUS_AREA_ID = @json($focusAreaId);

    let isInputMode = false;

    // Toggle Input Mode
    function toggleCobit4InputMode(forceState = null) {
        isInputMode = (forceState !== null) ? forceState : !isInputMode;
        
        const container = document.getElementById('c4Container');
        const banner = document.getElementById('c4InputModeBanner');
        const statusText = document.getElementById('inputModeStatusText');
        const toggleBtn = document.getElementById('btnInputModeToggle');

        if (isInputMode) {
            container.classList.add('input-mode-on');
            banner.classList.add('active');
            statusText.textContent = 'ON';
            toggleBtn.classList.remove('btn-outline-warning');
            toggleBtn.classList.add('btn-warning');
        } else {
            container.classList.remove('input-mode-on');
            banner.classList.remove('active');
            statusText.textContent = 'OFF';
            toggleBtn.classList.remove('btn-warning');
            toggleBtn.classList.add('btn-outline-warning');
        }

        renderAllDynamicSections();
    }

    // Switch between Tab 1, 2, 3, 4 or Book Mode (All Pages)
    function switchCobit4Page(pageId, btnElement) {
        document.querySelectorAll('.c4-nav-btn').forEach(btn => btn.classList.remove('active'));
        if (btnElement) btnElement.classList.add('active');

        const sections = {
            'page1': document.getElementById('c4-section-page1'),
            'page2': document.getElementById('c4-section-page2'),
            'page3': document.getElementById('c4-section-page3'),
            'page4': document.getElementById('c4-section-page4'),
        };

        if (pageId === 'all') {
            Object.values(sections).forEach(sec => { if (sec) sec.style.display = 'block'; });
        } else {
            Object.keys(sections).forEach(key => {
                if (sections[key]) sections[key].style.display = (key === pageId) ? 'block' : 'none';
            });
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
        const current = DATA.it_governance_focus[segmentKey] || '';
        let next = '';
        if (current === '') next = 'Primary';
        else if (current === 'Primary') next = 'Secondary';
        else next = '';

        DATA.it_governance_focus[segmentKey] = next;
        updatePentagonUI(segmentKey, next);
    }

    function updatePentagonUI(key, val) {
        const poly = document.getElementById(`poly_${key}`);
        const txt = document.getElementById(`txt_${key}`);
        if (!poly) return;

        let fill = '#f1f5f9';
        let stroke = '#94a3b8';
        let textFill = '#334155';

        if (val === 'Primary') {
            fill = '#13467b';
            stroke = '#3b6998';
            textFill = '#ffffff';
        } else if (val === 'Secondary') {
            fill = '#8bb4de';
            stroke = '#3b6998';
            textFill = '#0f2942';
        }

        poly.setAttribute('fill', fill);
        poly.setAttribute('stroke', stroke);
        if (txt) {
            txt.setAttribute('fill', textFill);
        }
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
                    <div class="c4-obj-item-desc">${escapeHtml(co.desc)}</div>
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
                              oninput="DATA.control_objectives[${idx}].desc = this.value" placeholder="Deskripsi Objective...">${escapeHtml(co.desc)}</textarea>
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

        renderGmBox('itMetricsBox', gm.it_metrics || [], 'it_metrics', 'Tambah IT Metric', `
            <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-2 small">
                <span class="c4-gm-arrow-badge">⇕ measure</span>
                <span class="c4-gm-arrow-badge">drive ↗</span>
            </div>
        `);
        renderGmBox('processMetricsBox', gm.process_metrics || [], 'process_metrics', 'Tambah Process Metric', `
            <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-2 small">
                <span class="c4-gm-arrow-badge">⇕ measure</span>
                <span class="c4-gm-arrow-badge">drive ↗</span>
            </div>
        `);
        renderGmBox('activitiesMetricsBox', gm.activities_metrics || [], 'activities_metrics', 'Tambah Activity Metric', `
            <div class="d-flex justify-content-start align-items-center pt-2 border-top mt-2 small">
                <span class="c4-gm-arrow-badge">⇕ measure</span>
            </div>
        `);
    }

    function renderGmBox(containerId, list, key, addLabel, footerHtml = '') {
        const box = document.getElementById(containerId);
        if (!box) return;

        if (!isInputMode) {
            box.innerHTML = `<ul>${list.map(i => `<li>${escapeHtml(i)}</li>`).join('')}</ul>` + footerHtml;
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
            ` + footerHtml;
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
                        <span class="text-dark">${escapeHtml(item.desc)}</span>
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
                                  oninput="DATA.maturity_model.levels[${lvl}].desc = this.value">${escapeHtml(item.desc)}</textarea>
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

    // AJAX: Save Data to Server
    async function saveCobit4Data() {
        try {
            Swal.fire({
                title: 'Menyimpan...',
                text: 'Sedang menyimpan data COBIT 4.1 ke database & storage.',
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
                    text: result.message || 'Perubahan COBIT 4.1 berhasil disimpan.',
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

    // AJAX: Reset to Baseline
    async function resetCobit4Data() {
        const confirm = await Swal.fire({
            title: 'Reset ke Standar ISACA?',
            text: 'Semua perubahan kustom untuk proses ini akan dikembalikan ke data resmi buku COBIT 4.1.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Reset Sekarang!',
            cancelButtonText: 'Batal'
        });

        if (!confirm.isConfirmed) return;

        try {
            Swal.fire({
                title: 'Mereset...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            const res = await fetch("{{ route('cobit4.reset') }}", {
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
                })
            });

            const result = await res.json();
            if (result.success) {
                await Swal.fire({
                    icon: 'success',
                    title: 'Berhasil di-Reset!',
                    text: 'Data telah dikembalikan ke standar ISACA.',
                    timer: 1800,
                    showConfirmButton: false,
                });
                window.location.reload();
            }
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Error', text: err.message });
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
