@extends('layouts.app')

@section('content')
    <style>
        :root {
            --fa-primary: #0f2b5c;
            --fa-accent: #0f6ad9;
            --fa-cobit5: #059669;
            --fa-cobit4: #d97706;
            --fa-surface: #ffffff;
            --fa-border: #e2e8f0;
        }

        .fa-page {
            background: #f8fafc;
            padding: 24px;
            border-radius: 16px;
        }

        /* Hero Header */
        .fa-hero {
            background: linear-gradient(135deg, #081a3d 0%, #0f2b5c 50%, #1e3a8a 100%);
            border-radius: 1.2rem;
            padding: 1.8rem 2.2rem;
            color: #fff;
            margin-bottom: 1.8rem;
            box-shadow: 0 16px 36px rgba(15, 43, 92, 0.12);
            position: relative;
            overflow: hidden;
        }

        .fa-hero h1 {
            font-size: 1.75rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin: 0;
        }



        .wizard-container {
            animation: fadeIn 0.22s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(6px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Wizard Cards (Step 1) */
        .wizard-model-card {
            background: #fff;
            border: 2px solid #e2e8f0;
            border-radius: 1.2rem;
            padding: 1.8rem;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            cursor: pointer;
            position: relative;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
            box-shadow: 0 6px 20px rgba(15, 43, 92, 0.04);
        }

        .wizard-model-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 45px rgba(15, 43, 92, 0.12);
            border-color: var(--fa-accent);
            color: inherit;
        }

        .wizard-model-card .model-cta-btn {
            pointer-events: none;
        }

        .model-icon-wrap {
            width: 58px;
            height: 58px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.65rem;
            margin-bottom: 1.2rem;
        }

        .icon-cobit2019 {
            background: rgba(15, 106, 217, 0.12);
            color: var(--fa-accent);
        }

        .icon-cobit5 {
            background: rgba(5, 150, 105, 0.12);
            color: var(--fa-cobit5);
        }

        .icon-cobit4 {
            background: rgba(217, 119, 6, 0.12);
            color: var(--fa-cobit4);
        }

        .icon-evolution {
            background: rgba(124, 58, 237, 0.12);
            color: #7c3aed;
        }

        .badge-evolution {
            background: #ede9fe;
            color: #6d28d9;
            border: 1px solid rgba(124, 58, 237, 0.2);
        }

        .model-title {
            font-size: 1.45rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 0.4rem;
        }

        .model-desc {
            font-size: 0.9rem;
            color: #64748b;
            line-height: 1.5;
            margin-bottom: 1.5rem;
            flex-grow: 1;
        }

        .model-badge {
            display: inline-block;
            font-size: 0.74rem;
            font-weight: 700;
            padding: 0.3rem 0.75rem;
            border-radius: 999px;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .badge-2019 {
            background: #e0f2fe;
            color: #0369a1;
        }

        .badge-5 {
            background: #d1fae5;
            color: #047857;
        }

        .badge-4 {
            background: #fef3c7;
            color: #b45309;
        }

        .model-cta-btn {
            display: inline-flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 0.75rem 1.1rem;
            font-size: 0.92rem;
            font-weight: 700;
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        /* Step 2 Toolbar & Header */
        .step2-toolbar {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 1.1rem 1.5rem;
            margin-bottom: 1.8rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            box-shadow: 0 4px 16px rgba(15, 43, 92, 0.04);
        }

        /* Focus Area Cards (Step 2 - COBIT 2019) */
        .fa-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1.5rem;
            transition: transform 0.22s cubic-bezier(.2, .9, .2, 1), box-shadow 0.22s cubic-bezier(.2, .9, .2, 1);
            box-shadow: 0 6px 18px rgba(15, 43, 92, 0.05);
            cursor: pointer;
            height: 100%;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .fa-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(15, 43, 92, 0.12);
            border-color: #cbd5e1;
            color: inherit;
        }

        .fa-card-code {
            display: inline-block;
            background: rgba(15, 43, 92, 0.08);
            color: var(--fa-primary);
            font-weight: 800;
            font-size: 0.76rem;
            padding: 0.25rem 0.7rem;
            border-radius: 999px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 0.6rem;
        }

        .fa-card h3 {
            font-size: 1.18rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.4rem;
        }

        .fa-card p {
            font-size: 0.88rem;
            color: #64748b;
            margin-bottom: 1rem;
            line-height: 1.5;
            flex-grow: 1;
        }

        .fa-card-stat {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.82rem;
            color: #64748b;
            font-weight: 600;
            padding-top: 0.75rem;
            border-top: 1px solid #f1f5f9;
        }

        .fa-card-stat i {
            color: var(--fa-accent);
        }

        .fa-add-card {
            border: 2px dashed #cbd5e1;
            background: #f8fafc;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 220px;
            cursor: pointer;
            transition: all 0.2s ease;
            border-radius: 1rem;
            text-decoration: none;
        }

        .fa-add-card:hover {
            border-color: var(--fa-accent);
            background: #eff6ff;
            transform: translateY(-3px);
            color: var(--fa-accent);
        }

        .fa-add-card i {
            font-size: 2.2rem;
            color: var(--fa-accent);
            margin-bottom: 0.6rem;
        }

        .fa-add-card span {
            font-weight: 700;
            color: #475569;
            font-size: 0.92rem;
        }

        .fa-actions {
            display: flex;
            gap: 0.4rem;
            margin-top: 0.75rem;
        }

        .fa-actions .btn {
            font-size: 0.76rem;
            padding: 0.25rem 0.65rem;
            border-radius: 6px;
            font-weight: 600;
        }

        /* Framework Direct Model Box (COBIT 5 & 4.1) */
        .direct-model-box {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 1.2rem;
            padding: 2.2rem;
            box-shadow: 0 10px 30px rgba(15, 43, 92, 0.06);
        }

        .direct-model-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1.25rem;
            margin-bottom: 1.75rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .direct-model-stats {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }

        .stat-chip {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.85rem 1.4rem;
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }

        .stat-chip-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        /* Modals */
        .fa-modal .modal-content {
            border: none;
            border-radius: 1.2rem;
            box-shadow: 0 25px 60px rgba(9, 18, 56, 0.2);
            overflow: hidden;
        }

        .fa-modal .modal-header {
            background: linear-gradient(135deg, #081a3d, #0f2b5c);
            color: #fff;
            padding: 1.2rem 1.6rem;
        }

        .fa-notif-wrap {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 2100;
            width: min(380px, calc(100vw - 40px));
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
    </style>

    <div class="container-fluid py-4 fa-page">
        <div id="faNotifWrap" class="fa-notif-wrap" aria-live="polite" aria-atomic="true"></div>

        <!-- Hero Header -->
        <div class="fa-hero d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1><i class="fas fa-cubes me-2"></i>COBIT Model &amp; Focus Area</h1>

            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="{{ route('cobit.evolution') }}" class="btn btn-outline-light fw-bold shadow-sm">
                    <i class="fas fa-code-branch me-1"></i> GAMO Mapping Evolution
                </a>
                @if(auth()->check() && auth()->user()->can('design-factors.input'))
                    <button class="btn btn-light fw-bold shadow-sm" onclick="openCreateModal()">
                        <i class="fas fa-plus me-1 text-primary"></i> Tambah Model / Focus Area
                    </button>
                @endif
            </div>
        </div>

        @php
            $f2019 = $focusAreasByVersion->get('2019', collect());
            $f5 = $focusAreasByVersion->get('5', collect());
            $f4 = $focusAreasByVersion->get('4.1', collect());

            $cobit5Model = $f5->first();
            $cobit4Model = $f4->first();
        @endphp
        <!-- ==================================================== -->
        <!-- WIZARD STEP 1: PILIH FRAMEWORK MODEL -->
        <!-- ==================================================== -->
        <div id="wizardStep1Container" class="wizard-container">

            <div class="row g-4">
                <!-- COBIT 2019 -->
                <div class="col-md-6 col-xl-3">
                    <div class="wizard-model-card" onclick="selectFramework('2019')">
                        <div>
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="model-icon-wrap icon-cobit2019">
                                    <i class="fas fa-cubes"></i>
                                </div>
                                <span class="model-badge badge-2019">
                                    <i class="fas fa-sliders-h me-1"></i> Multi Focus Area
                                </span>
                            </div>
                            <div class="model-title">
                                COBIT 2019
                            </div>
                            <div class="model-desc">
                                Standar tata kelola TI mutakhir dengan pendekatan fleksibel & modular berbasis Focus Area.
                            </div>
                        </div>
                        <div>
                            <div class="small text-muted fw-semibold mb-2">
                                <i class="fas fa-bullseye me-1 text-primary"></i> {{ $f2019->count() }} Focus Area tersedia
                            </div>
                            <button class="btn btn-primary model-cta-btn">
                                <span>Pilih Focus Area</span>
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- COBIT 5 -->
                <div class="col-md-6 col-xl-3">
                    <div class="wizard-model-card" onclick="selectFramework('5')">
                        <div>
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="model-icon-wrap icon-cobit5">
                                    <i class="fas fa-sitemap"></i>
                                </div>
                                <span class="model-badge badge-5">
                                    <i class="fas fa-check-circle me-1"></i> 37 Proses
                                </span>
                            </div>
                            <div class="model-title">
                                COBIT 5
                            </div>
                            <div class="model-desc">
                                Integrasi 5 prinsip tata kelola dan 7 enabler untuk seluruh tata kelola dan manajemen teknologi informasi perusahaan.
                            </div>
                        </div>
                        <div>
                            <div class="small text-muted fw-semibold mb-2">
                                <i class="fas fa-clipboard-list me-1 text-success"></i> {{ $cobit5Model ? $cobit5Model->objectives_count . ' Objectives' : '37 Proses' }}
                            </div>
                            <button class="btn btn-success text-white model-cta-btn">
                                <span>Lanjut ke COBIT 5</span>
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- COBIT 4.1 -->
                <div class="col-md-6 col-xl-3">
                    <div class="wizard-model-card" onclick="selectFramework('4.1')">
                        <div>
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="model-icon-wrap icon-cobit4">
                                    <i class="fas fa-clipboard-check"></i>
                                </div>
                                <span class="model-badge badge-4">
                                    <i class="fas fa-shield-alt me-1"></i> 34 Proses
                                </span>
                            </div>
                            <div class="model-title">
                                COBIT 4.1
                            </div>
                            <div class="model-desc">
                                Standar audit kontrol internal dan tata kelola TI dengan 4 domain utama: Plan & Organize, Acquire & Implement, Deliver & Support, Monitor & Evaluate.
                            </div>
                        </div>
                        <div>
                            <div class="small text-muted fw-semibold mb-2">
                                <i class="fas fa-tasks me-1 text-warning"></i> {{ $cobit4Model ? $cobit4Model->objectives_count . ' Objectives' : 'Framework Audit' }}
                            </div>
                            <button class="btn btn-warning text-dark model-cta-btn">
                                <span>Lanjut ke COBIT 4.1</span>
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- COBIT GAMO MAPPING EVOLUTION -->
                <div class="col-md-6 col-xl-3">
                    <a href="{{ route('cobit.evolution') }}" class="text-decoration-none">
                        <div class="wizard-model-card h-100" style="cursor: pointer; border-color: rgba(124, 58, 237, 0.25);">
                            <div>
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="model-icon-wrap icon-evolution">
                                        <i class="fas fa-code-branch"></i>
                                    </div>
                                    <span class="model-badge badge-evolution">
                                        <i class="fas fa-layer-group me-1"></i> 3 Generasi
                                    </span>
                                </div>
                                <div class="model-title text-dark">
                                    GAMO Evolution
                                </div>
                                <div class="model-desc">
                                    Matriks silsilah & evolusi objektif komparatif lintas generasi COBIT 4.1, COBIT 5, dan COBIT 2019.
                                </div>
                            </div>
                            <div>
                                <div class="small text-muted fw-semibold mb-2">
                                    <i class="fas fa-network-wired me-1" style="color: #7c3aed;"></i> 40 Relasi Objektif
                                </div>
                                <div class="btn text-white model-cta-btn w-100 d-flex justify-content-between align-items-center" style="background: #7c3aed; border-color: #7c3aed;">
                                    <span>Buka Matriks Evolusi</span>
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- ==================================================== -->
        <!-- WIZARD STEP 2: FOCUS AREA / MODEL DETAIL -->
        <!-- ==================================================== -->
        <div id="wizardStep2Container" class="wizard-container" style="display:none;">
            <!-- Control bar with Back Button and Active Model Badge -->
            <div class="step2-toolbar">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-outline-secondary btn-sm fw-bold rounded-pill px-3" onclick="goToWizardStep(1)">
                        <i class="fas fa-arrow-left me-1"></i> Ganti Framework Model
                    </button>
                    <span id="selectedFrameworkBadge" class="badge bg-primary px-3 py-2 rounded-pill fw-bold">
                        <i class="fas fa-cubes me-1"></i> COBIT 2019
                    </span>
                </div>
                <div id="step2ActionWrap">
                    @if(auth()->check() && auth()->user()->can('design-factors.input'))
                        <button class="btn btn-outline-primary btn-sm fw-bold rounded-pill px-3" id="step2AddFaBtn" onclick="openCreateModal('2019')">
                            <i class="fas fa-plus me-1"></i> Tambah Focus Area
                        </button>
                    @endif
                </div>
            </div>

            <!-- SUB VIEW: COBIT 2019 (PILIH FOCUS AREA) -->
            <div id="viewCobit2019" class="version-view">

                <div class="row g-3">
                    @forelse($f2019 as $fa)
                        @php
                            $firstObj = \App\Models\MstObjective::where('focus_area_id', $fa->id)
                                ->orderByRaw("CASE WHEN UPPER(objective_id) LIKE 'EDM%' THEN 0 WHEN UPPER(objective_id) LIKE 'APO%' THEN 1 WHEN UPPER(objective_id) LIKE 'BAI%' THEN 2 WHEN UPPER(objective_id) LIKE 'DSS%' THEN 3 WHEN UPPER(objective_id) LIKE 'MEA%' THEN 4 ELSE 5 END, objective_id")
                                ->first();
                            $objRoute = $firstObj ? route('cobit_component.show', ['id' => $firstObj->objective_id, 'focus_area' => $fa->id]) : route('focus-areas.show', $fa->id);
                        @endphp
                        <div class="col-md-6 col-xl-4" data-fa-id="{{ $fa->id }}">
                            <a href="{{ $objRoute }}" class="fa-card" id="faCard{{ $fa->id }}">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fa-card-code">{{ $fa->code }}</span>
                                        <span class="badge bg-light text-muted border">Focus Area</span>
                                    </div>
                                    <h3>{{ $fa->name }}</h3>
                                    <p>{{ $fa->description ?: 'Tidak ada deskripsi.' }}</p>
                                </div>
                                <div>
                                    <div class="fa-card-stat mb-2">
                                        <i class="fas fa-layer-group"></i>
                                        <span>{{ $fa->objectives_count }} objectives</span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                                        <span class="text-primary fw-bold small">
                                            Buka Kamus Komponen <i class="fas fa-arrow-right ms-1"></i>
                                        </span>
                                        @if(auth()->check() && auth()->user()->can('design-factors.input'))
                                            <div class="fa-actions" onclick="event.preventDefault(); event.stopPropagation();">
                                                <button class="btn btn-outline-secondary btn-sm" onclick="openEditModal({{ $fa->id }}, '{{ addslashes($fa->code) }}', '{{ addslashes($fa->name) }}', `{{ addslashes($fa->description ?? '') }}`, '{{ $fa->version ?? '2019' }}')">
                                                    <i class="fas fa-pen"></i> Edit
                                                </button>
                                                @if($fa->id != 1)
                                                    <button class="btn btn-outline-danger btn-sm" onclick="deleteFocusArea({{ $fa->id }}, '{{ addslashes($fa->name) }}')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="card p-4 text-center text-muted">
                                Belum ada Focus Area untuk COBIT 2019.
                            </div>
                        </div>
                    @endforelse

                    @if(auth()->check() && auth()->user()->can('design-factors.input'))
                        <div class="col-md-6 col-xl-4">
                            <div class="fa-card fa-add-card" onclick="openCreateModal('2019')">
                                <i class="fas fa-plus-circle"></i>
                                <span>Tambah Focus Area Baru</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- SUB VIEW: COBIT 5 -->
            <div id="viewCobit5" class="version-view" style="display:none;">

                @if($cobit5Model)
                    @php
                        $firstCobit5Obj = \App\Models\MstObjective::where('focus_area_id', $cobit5Model->id)->first();
                        $cobit5ObjRoute = $firstCobit5Obj
                            ? route('cobit_component.show', ['id' => $firstCobit5Obj->objective_id, 'focus_area' => $cobit5Model->id])
                            : route('focus-areas.show', $cobit5Model->id);
                    @endphp
                    <div class="direct-model-box">
                        <div class="direct-model-header">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-success text-white fw-bold px-3 py-1">COBIT 5</span>
                                    <span class="badge bg-light text-dark border">{{ $cobit5Model->code }}</span>
                                </div>
                                <h3 class="fw-bold mb-1">{{ $cobit5Model->name }}</h3>
                                <p class="text-muted mb-0">{{ $cobit5Model->description }}</p>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ $cobit5ObjRoute }}" class="btn btn-success fw-bold px-4">
                                    <i class="fas fa-book-open me-2"></i> Buka Kamus Komponen COBIT 5
                                </a>
                                <a href="{{ route('focus-areas.show', $cobit5Model->id) }}" class="btn btn-outline-secondary fw-bold">
                                    <i class="fas fa-cog me-1"></i> Kelola Model
                                </a>
                            </div>
                        </div>

                        <div class="direct-model-stats">
                            <div class="stat-chip">
                                <div class="stat-chip-icon bg-success-subtle text-success">
                                    <i class="fas fa-tasks"></i>
                                </div>
                                <div>
                                    <div class="fw-bold fs-5">{{ $cobit5Model->objectives_count }}</div>
                                    <div class="small text-muted">Total Objectives / Proses</div>
                                </div>
                            </div>
                            <div class="stat-chip">
                                <div class="stat-chip-icon bg-info-subtle text-info">
                                    <i class="fas fa-columns"></i>
                                </div>
                                <div>
                                    <div class="fw-bold fs-5">5 Domain</div>
                                    <div class="small text-muted">EDM, APO, BAI, DSS, MEA</div>
                                </div>
                            </div>
                            <div class="stat-chip">
                                <div class="stat-chip-icon bg-primary-subtle text-primary">
                                    <i class="fas fa-puzzle-piece"></i>
                                </div>
                                <div>
                                    <div class="fw-bold fs-5">Single Framework</div>
                                    <div class="small text-muted">Standar Komprehensif ISACA</div>
                                </div>
                            </div>
                        </div>

                        @if($cobit5Model->objectives_count == 0)
                            <div class="alert alert-warning d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div>
                                    <i class="fas fa-exclamation-triangle me-2"></i> Model COBIT 5 ini belum memiliki data proses. Anda dapat men-generate 37 proses secara otomatis.
                                </div>
                                <button class="btn btn-sm btn-primary fw-bold" onclick="generateCobit5({{ $cobit5Model->id }})">
                                    <i class="fas fa-magic me-1"></i> Generate 37 Proses COBIT 5
                                </button>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="card p-5 text-center shadow-sm">
                        <i class="fas fa-sitemap fa-3x text-muted mb-3"></i>
                        <h4>Model COBIT 5 belum tersedia di database</h4>
                        <button class="btn btn-success fw-bold mx-auto mt-2" onclick="openCreateModal('5', 'COBIT5', 'COBIT 5', 'COBIT 5 Framework - 37 Governance and Management Processes')">
                            <i class="fas fa-plus me-1"></i> Buat Model COBIT 5 Sekarang
                        </button>
                    </div>
                @endif
            </div>

            <!-- SUB VIEW: COBIT 4.1 -->
            <div id="viewCobit4" class="version-view" style="display:none;">

                @if($cobit4Model)
                    @php
                        $firstCobit4Obj = \App\Models\MstObjective::where('focus_area_id', $cobit4Model->id)->first();
                        $cobit4ObjRoute = $firstCobit4Obj
                            ? route('cobit_component.show', ['id' => $firstCobit4Obj->objective_id, 'focus_area' => $cobit4Model->id])
                            : route('focus-areas.show', $cobit4Model->id);
                    @endphp
                    <div class="direct-model-box">
                        <div class="direct-model-header">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-warning text-dark fw-bold px-3 py-1">COBIT 4.1</span>
                                    <span class="badge bg-light text-dark border">{{ $cobit4Model->code }}</span>
                                </div>
                                <h3 class="fw-bold mb-1">{{ $cobit4Model->name }}</h3>
                                <p class="text-muted mb-0">{{ $cobit4Model->description }}</p>
                            </div>
                            <div class="d-flex gap-2">
                                @if($cobit4Model->objectives_count > 0)
                                    <a href="{{ $cobit4ObjRoute }}" class="btn btn-warning fw-bold text-dark px-4">
                                        <i class="fas fa-book-open me-2"></i> Buka Kamus Komponen COBIT 4.1
                                    </a>
                                @endif
                                <a href="{{ route('focus-areas.show', $cobit4Model->id) }}" class="btn btn-outline-secondary fw-bold">
                                    <i class="fas fa-cog me-1"></i> Kelola Model
                                </a>
                            </div>
                        </div>

                        <div class="direct-model-stats">
                            <div class="stat-chip">
                                <div class="stat-chip-icon bg-warning-subtle text-warning">
                                    <i class="fas fa-tasks"></i>
                                </div>
                                <div>
                                    <div class="fw-bold fs-5">{{ $cobit4Model->objectives_count }}</div>
                                    <div class="small text-muted">Total Objectives / Proses</div>
                                </div>
                            </div>
                            <div class="stat-chip">
                                <div class="stat-chip-icon bg-primary-subtle text-primary">
                                    <i class="fas fa-project-diagram"></i>
                                </div>
                                <div>
                                    <div class="fw-bold fs-5">4 Domain</div>
                                    <div class="small text-muted">PO, AI, DS, ME</div>
                                </div>
                            </div>
                            <div class="stat-chip">
                                <div class="stat-chip-icon bg-secondary-subtle text-secondary">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <div>
                                    <div class="fw-bold fs-5">Control Framework</div>
                                    <div class="small text-muted">Internal Control & Audit Standard</div>
                                </div>
                            </div>
                        </div>

                        @if($cobit4Model->objectives_count == 0)
                            <div class="alert alert-warning d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div>
                                    <i class="fas fa-exclamation-triangle me-2"></i> Model COBIT 4.1 ini belum memiliki data proses. Anda dapat men-generate 34 proses COBIT 4.1 secara otomatis dari baseline.
                                </div>
                                <button class="btn btn-sm btn-warning text-dark fw-bold" onclick="generateCobit4({{ $cobit4Model->id }})">
                                    <i class="fas fa-magic me-1"></i> Generate Template COBIT 4.1
                                </button>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="card p-5 text-center shadow-sm">
                        <i class="fas fa-clipboard-check fa-3x text-muted mb-3"></i>
                        <h4>Model COBIT 4.1 belum tersedia di database</h4>
                        <button class="btn btn-warning fw-bold text-dark mx-auto mt-2" onclick="openCreateModal('4.1', 'COBIT41', 'COBIT 4.1', 'COBIT 4.1 Framework - IT Governance & Control Objectives')">
                            <i class="fas fa-plus me-1"></i> Buat Model COBIT 4.1 Sekarang
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal fade fa-modal" id="faModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="faModalTitle">Tambah Model / Focus Area</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="faEditId" value="">

                    <!-- Version selector -->
                    <div class="mb-3">
                        <label for="faVersion" class="form-label fw-bold">Versi Model Framework</label>
                        <select id="faVersion" class="form-select">
                            <option value="2019">COBIT 2019 (Focus Area)</option>
                            <option value="5">COBIT 5</option>
                            <option value="4.1">COBIT 4.1</option>
                        </select>
                        <div class="form-text small" id="faVersionHelp">
                            Pilih COBIT 2019 jika ini adalah Focus Area yang akan dikelompokkan dalam COBIT 2019.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="faCode" class="form-label fw-semibold">Code</label>
                        <input type="text" id="faCode" class="form-control" placeholder="Contoh: SECURITY" maxlength="10" style="text-transform: uppercase;">
                    </div>
                    <div class="mb-3">
                        <label for="faName" class="form-label fw-semibold">Name</label>
                        <input type="text" id="faName" class="form-control" placeholder="Contoh: Security" maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label for="faDescription" class="form-label fw-semibold">Description</label>
                        <textarea id="faDescription" class="form-control" rows="3" placeholder="Deskripsi model / focus area..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="faSaveBtn" onclick="saveFocusArea()">
                        <i class="fas fa-save me-1"></i>Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        let currentStep = 1;
        let currentActiveVersion = @json($selectedVersion ?? '2019');

        function showNotif(message, type = 'success') {
            const wrap = document.getElementById('faNotifWrap');
            const div = document.createElement('div');
            div.className = `alert alert-${type} alert-dismissible fade show shadow-sm`;
            div.style.fontSize = '0.88rem';
            div.innerHTML = `${message}<button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>`;
            wrap.appendChild(div);
            setTimeout(() => div.remove(), 4000);
        }

        function selectFramework(version) {
            currentActiveVersion = version;
            goToWizardStep(2);
        }

        function goToWizardStep(step) {
            currentStep = step;

            const step1Container = document.getElementById('wizardStep1Container');
            const step2Container = document.getElementById('wizardStep2Container');

            if (step === 1) {
                if (step1Container) step1Container.style.display = 'block';
                if (step2Container) step2Container.style.display = 'none';

                // Update URL
                const url = new URL(window.location);
                url.searchParams.set('step', '1');
                window.history.replaceState({}, '', url);
            } else if (step === 2) {
                if (step1Container) step1Container.style.display = 'none';
                if (step2Container) step2Container.style.display = 'block';

                // Toggle sub-views based on currentActiveVersion
                document.querySelectorAll('.version-view').forEach(v => v.style.display = 'none');

                const badge = document.getElementById('selectedFrameworkBadge');
                const addBtn = document.getElementById('step2AddFaBtn');

                if (currentActiveVersion === '2019') {
                    const v2019 = document.getElementById('viewCobit2019');
                    if (v2019) v2019.style.display = 'block';
                    if (badge) {
                        badge.innerHTML = '<i class="fas fa-cubes me-1"></i> COBIT 2019';
                        badge.className = 'badge bg-primary px-3 py-2 rounded-pill fw-bold';
                    }
                    if (addBtn) addBtn.style.display = 'inline-block';
                } else if (currentActiveVersion === '5') {
                    const v5 = document.getElementById('viewCobit5');
                    if (v5) v5.style.display = 'block';
                    if (badge) {
                        badge.innerHTML = '<i class="fas fa-sitemap me-1"></i> COBIT 5';
                        badge.className = 'badge bg-success px-3 py-2 rounded-pill fw-bold';
                    }
                    if (addBtn) addBtn.style.display = 'none';
                } else if (currentActiveVersion === '4.1') {
                    const v4 = document.getElementById('viewCobit4');
                    if (v4) v4.style.display = 'block';
                    if (badge) {
                        badge.innerHTML = '<i class="fas fa-clipboard-check me-1"></i> COBIT 4.1';
                        badge.className = 'badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold';
                    }
                    if (addBtn) addBtn.style.display = 'none';
                }

                // Update URL
                const url = new URL(window.location);
                url.searchParams.set('step', '2');
                url.searchParams.set('version', currentActiveVersion);
                window.history.replaceState({}, '', url);
            }
        }

        function openCreateModal(defaultVersion = null, defaultCode = '', defaultName = '', defaultDesc = '') {
            document.getElementById('faEditId').value = '';
            document.getElementById('faVersion').value = defaultVersion || currentActiveVersion || '2019';
            document.getElementById('faCode').value = defaultCode;
            document.getElementById('faName').value = defaultName;
            document.getElementById('faDescription').value = defaultDesc;
            document.getElementById('faModalTitle').textContent = (defaultVersion === '2019' || currentActiveVersion === '2019') ? 'Tambah Focus Area COBIT 2019' : 'Tambah Model';
            new bootstrap.Modal(document.getElementById('faModal')).show();
        }

        function openEditModal(id, code, name, description, version = '2019') {
            document.getElementById('faEditId').value = id;
            document.getElementById('faVersion').value = version || '2019';
            document.getElementById('faCode').value = code;
            document.getElementById('faName').value = name;
            document.getElementById('faDescription').value = description;
            document.getElementById('faModalTitle').textContent = 'Edit Model / Focus Area';
            new bootstrap.Modal(document.getElementById('faModal')).show();
        }

        async function saveFocusArea() {
            const editId = document.getElementById('faEditId').value;
            const version = document.getElementById('faVersion').value;
            const code = document.getElementById('faCode').value.trim().toUpperCase();
            const name = document.getElementById('faName').value.trim();
            const description = document.getElementById('faDescription').value.trim();

            if (!code || !name) {
                showNotif('Code dan Name wajib diisi.', 'warning');
                return;
            }

            const btn = document.getElementById('faSaveBtn');
            btn.disabled = true;

            try {
                const url = editId
                    ? `{{ url('/focus-areas') }}/${editId}`
                    : `{{ url('/focus-areas') }}`;
                const method = editId ? 'PUT' : 'POST';

                const response = await fetch(url, {
                    method,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    body: JSON.stringify({ version, code, name, description })
                });

                if (!response.ok) {
                    const err = await response.json();
                    let errMsg = err.message || 'Gagal menyimpan.';
                    if (err.errors) {
                        errMsg = Object.values(err.errors).flat().join('\n');
                    }
                    throw new Error(errMsg);
                }

                bootstrap.Modal.getInstance(document.getElementById('faModal'))?.hide();
                showNotif(editId ? 'Model berhasil diperbarui.' : 'Model berhasil ditambahkan.');
                setTimeout(() => {
                    const url = new URL(window.location);
                    url.searchParams.set('step', '2');
                    url.searchParams.set('version', version);
                    window.location.href = url.toString();
                }, 800);
            } catch (e) {
                showNotif(e.message, 'danger');
            } finally {
                btn.disabled = false;
            }
        }

        async function deleteFocusArea(id, name) {
            if (!confirm(`Hapus model/focus area "${name}"? Semua mapping ke objectives akan dihapus juga.`)) return;

            try {
                const response = await fetch(`{{ url('/focus-areas') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    }
                });

                if (!response.ok) throw new Error('Gagal menghapus.');

                showNotif('Model / Focus area berhasil dihapus.');
                setTimeout(() => location.reload(), 800);
            } catch (e) {
                showNotif(e.message, 'danger');
            }
        }

        async function generateCobit5(id) {
            if (!confirm('Apakah Anda yakin ingin melakukan bulk clone 37 proses COBIT 5 ke dalam model ini?\nProses ini mungkin memerlukan beberapa detik.')) return;

            try {
                const res = await fetch(`{{ url('/focus-areas') }}/${id}/generate-cobit5`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    }
                });
                if (!res.ok) throw new Error('Gagal men-generate COBIT 5.');
                const data = await res.json();
                showNotif(data.message);
                setTimeout(() => location.reload(), 1000);
            } catch (e) {
                showNotif(e.message, 'danger');
            }
        }

        async function generateCobit4(id) {
            if (!confirm('Apakah Anda yakin ingin melakukan bulk clone template 34 proses COBIT 4.1 ke dalam model ini?\nProses ini mungkin memerlukan beberapa detik.')) return;

            try {
                const res = await fetch(`{{ url('/focus-areas') }}/${id}/generate-cobit4`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    }
                });
                if (!res.ok) throw new Error('Gagal men-generate COBIT 4.1.');
                const data = await res.json();
                showNotif(data.message);
                setTimeout(() => location.reload(), 1000);
            } catch (e) {
                showNotif(e.message, 'danger');
            }
        }

        // Initialize wizard based on URL params
        document.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const stepParam = urlParams.get('step');
            const versionParam = urlParams.get('version');

            if (versionParam) {
                currentActiveVersion = versionParam;
            }

            if (stepParam === '2') {
                goToWizardStep(2);
            } else {
                goToWizardStep(1);
            }
        });
    </script>
@endsection
