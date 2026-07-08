@extends('layouts.app')

@section('content')
    <style>
        :root {
            --fa-primary: #0f2b5c;
            --fa-accent: #0f6ad9;
        }

        .fa-show-page { background: #f6f8ff; padding: 20px; border-radius: 12px; }

        .fa-detail-hero {
            background: linear-gradient(135deg, #081a3d, #0f2b5c);
            border-radius: 1rem;
            padding: 1.5rem 2rem;
            color: #fff;
            margin-bottom: 1.5rem;
            box-shadow: 0 25px 60px rgba(9, 18, 56, 0.18);
        }
        .fa-detail-hero h1 { font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem; }
        .fa-detail-hero .fa-code-badge {
            display: inline-block;
            background: rgba(255,255,255,0.15);
            font-weight: 700;
            font-size: 0.78rem;
            padding: 0.3rem 0.9rem;
            border-radius: 999px;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }
        .fa-detail-hero p { color: rgba(255,255,255,0.75); margin: 0; font-size: 0.92rem; }

        .fa-toolbar {
            display: flex; gap: 0.75rem; align-items: center; margin-bottom: 1rem; flex-wrap: wrap;
        }

        .btn-primary { background-color: var(--fa-primary) !important; border-color: var(--fa-primary) !important; }
        .btn-outline-primary { color: var(--fa-primary) !important; border-color: var(--fa-primary) !important; }
        .btn-outline-primary:hover { background-color: var(--fa-primary) !important; color: #fff !important; }

        .bg-primary { background-color: var(--fa-primary) !important; }

        .obj-accordion-card {
            border: 1px solid #e3e8ff;
            border-radius: 0.85rem;
            margin-bottom: 0.75rem;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(15,43,92,0.04);
        }
        .obj-accordion-header {
            background: #f8f9ff;
            padding: 0.85rem 1.25rem;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e3e8ff;
            transition: background 0.15s;
        }
        .obj-accordion-header:hover { background: #eef2ff; }
        .obj-accordion-header .obj-id {
            font-weight: 700; color: var(--fa-primary); font-size: 0.95rem;
        }
        .obj-accordion-header .obj-name {
            color: #374151; font-size: 0.88rem; margin-left: 0.5rem;
        }
        .obj-accordion-header .chevron {
            transition: transform 0.2s; color: #9ca3af; font-size: 0.9rem;
        }
        .obj-accordion-header.open .chevron { transform: rotate(180deg); }
        .obj-accordion-body { display: none; padding: 1rem 1.25rem; background: #fff; }
        .obj-accordion-body.open { display: block; }

        .comp-section { margin-bottom: 1rem; }
        .comp-section-title {
            font-size: 0.82rem; font-weight: 700; color: var(--fa-primary);
            text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 0.5rem;
            padding-bottom: 0.25rem; border-bottom: 2px solid #e3e8ff;
        }
        .comp-table { width: 100%; font-size: 0.84rem; border-collapse: collapse; }
        .comp-table th {
            background: #f1f5f9; padding: 0.4rem 0.6rem; font-weight: 600; color: #4b5563;
            border-bottom: 1px solid #e3e8ff; text-align: left;
        }
        .comp-table td {
            padding: 0.4rem 0.6rem; border-bottom: 1px solid #f1f5f9; color: #374151;
            vertical-align: top;
        }
        .comp-table tr:last-child td { border-bottom: none; }
        .comp-empty { color: #9ca3af; font-style: italic; font-size: 0.84rem; }

        .mini-edit-btn {
            width: 1.45rem;
            height: 1.45rem;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.35rem;
            line-height: 1;
        }

        .metric-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.5rem;
            padding: 0.2rem 0;
        }

        .metric-list {
            padding-left: 1rem;
            margin-bottom: 0;
        }

        .org-table {
            font-size: 0.82rem;
        }

        .org-table th,
        .org-table td {
            vertical-align: middle;
            text-align: center;
        }

        .org-table th:first-child,
        .org-table td:first-child {
            text-align: left;
            min-width: 280px;
        }

        .vertical-text {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            white-space: nowrap;
            min-height: 120px;
            padding: 0.35rem 0;
        }

        .raci-badge {
            display: inline-block; padding: 0.15rem 0.5rem; border-radius: 4px;
            font-weight: 700; font-size: 0.75rem; margin-right: 0.25rem;
        }
        .raci-R { background: #fee2e2; color: #991b1b; }
        .raci-A { background: #fef9c3; color: #854d0e; }
        .raci-C { background: #dcfce7; color: #166534; }
        .raci-I { background: #dbeafe; color: #1e40af; }

        .focus-area-pill {
            display: inline-block; padding: 0.2rem 0.65rem; border-radius: 999px;
            font-size: 0.72rem; font-weight: 700; background: rgba(15,106,217,0.1);
            color: var(--fa-accent); margin-right: 0.3rem; margin-bottom: 0.2rem;
            letter-spacing: 0.04em;
        }

        .fa-notif-wrap {
            position: fixed; top: 16px; right: 16px; z-index: 2000;
            width: min(360px, calc(100vw - 32px));
            display: flex; flex-direction: column; gap: 8px;
        }

        .select2-like {
            max-height: 250px; overflow-y: auto; border: 1px solid #dee2e6;
            border-radius: 0.5rem; padding: 0.5rem;
        }
        .select2-like label {
            display: flex; align-items: center; gap: 0.5rem; padding: 0.3rem 0.5rem;
            cursor: pointer; border-radius: 0.4rem; font-size: 0.85rem; transition: background 0.1s;
        }
        .select2-like label:hover { background: #f1f5f9; }
        .select2-like input[type="checkbox"] { accent-color: var(--fa-primary); }

        .component-tabs .nav-link {
            font-size: 0.8rem; font-weight: 600; color: #6b7392;
            padding: 0.35rem 0.8rem; border-radius: 0.5rem; text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .component-tabs .nav-link.active {
            background: var(--fa-primary) !important; color: #fff !important;
        }
    </style>

    <div class="container py-4 fa-show-page">
        <div id="faNotifWrap" class="fa-notif-wrap" aria-live="polite" aria-atomic="true"></div>
        @php
            $displayObjectiveId = fn ($value) => preg_replace('/\.(?:M|FA)\d+(?:\.\d+)?$/i', '', (string) $value);
        @endphp

        <!-- Hero -->
        <div class="fa-detail-hero">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <span class="fa-code-badge">{{ $focusArea->code }}</span>
                    <h1>{{ $focusArea->name }}</h1>
                    <p>{{ $focusArea->description ?: 'Tidak ada deskripsi.' }}</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary btn-sm fw-semibold" onclick="createObjModal.show()">
                        <i class="fas fa-plus me-1"></i>Tambah Objective
                    </button>
                    <a href="{{ route('focus-areas.index') }}" class="btn btn-sm btn-outline-light fw-bold">
                        <i class="fas fa-arrow-left me-1"></i>Kembali
                    </a>
                </div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="fa-toolbar">
            @if(auth()->check() && auth()->user()->can('design-factors.input'))
                <button class="btn btn-sm btn-primary fw-bold" onclick="openCreateObjective()">
                    <i class="fas fa-plus me-1"></i>Tambah Objective
                </button>
                <button class="btn btn-sm btn-info text-white fw-bold" onclick="generateCobit5()">
                    <i class="fas fa-magic me-1"></i>Generate COBIT 5
                </button>
                <button class="btn btn-sm btn-outline-secondary fw-bold" onclick="openEditFaModal()">
                    <i class="fas fa-pen me-1"></i>Edit Model
                </button>
            @endif
        </div>

        <!-- Objectives List -->
        @if($objectives->isEmpty())
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Belum ada objectives di model ini.</h5>
                    @if(auth()->check() && auth()->user()->can('design-factors.input'))
                        <button class="btn btn-primary mt-2" onclick="openCreateObjective()">
                            <i class="fas fa-plus me-1"></i>Tambah Objective Sekarang
                        </button>
                    @endif
                </div>
            </div>
        @else
            @foreach($objectives as $obj)
                @php $safeId = str_replace('.', '_', $obj->objective_id); @endphp
                <div class="obj-accordion-card">
                    <div class="obj-accordion-header" onclick="toggleAccordion('{{ $safeId }}')">
                        <div>
                            <a href="{{ route('cobit_component.show', ['id' => $obj->objective_id, 'focus_area' => $focusArea->id]) }}" 
                               class="text-decoration-none">
                                <span class="obj-id">{{ $displayObjectiveId($obj->objective_id) }}</span>
                                <span class="obj-name">— {{ $obj->objective }}</span>
                            </a>
                            @if($obj->focusArea)
                                <span class="focus-area-pill">{{ $obj->focusArea->code }}</span>
                            @endif
                        </div>
                        <div class="d-flex gap-1 align-items-center">
                            @if(auth()->check() && auth()->user()->can('design-factors.input'))
                <button class="btn btn-xs btn-outline-primary btn-sm py-0 px-1" onclick="event.stopPropagation(); openEditObjective('{{ $obj->objective_id }}', '{{ addslashes($obj->objective) }}', '{{ addslashes($obj->objective_description) }}', '{{ addslashes($obj->objective_purpose) }}')">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button class="btn btn-xs btn-outline-danger btn-sm py-0 px-1" onclick="event.stopPropagation(); deleteObjective('{{ $obj->objective_id }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endif
                            <i class="fas fa-chevron-down chevron"></i>
                        </div>
                    </div>
                    <div class="obj-accordion-body" id="accordion_{{ $safeId }}">

                        <!-- Component Tabs -->
                        <ul class="nav nav-pills component-tabs mb-3" id="compTabs_{{ $safeId }}">
                            <li class="nav-item"><a class="nav-link active" data-bs-toggle="pill" href="#overview_{{ $safeId }}">Overview</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#entergoals_{{ $safeId }}" data-tab-name="entergoals">Enterprise Goals</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#aligngoals_{{ $safeId }}" data-tab-name="aligngoals">Alignment Goals</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#practices_{{ $safeId }}">Practices</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#organizational_{{ $safeId }}">Organizational</a></li>
                            <li class="nav-item li-tab-policies"><a class="nav-link" data-bs-toggle="pill" href="#policies_{{ $safeId }}">Policies</a></li>
                            <li class="nav-item li-tab-skills"><a class="nav-link" data-bs-toggle="pill" href="#skills_{{ $safeId }}">Skills</a></li>
                            <li class="nav-item li-tab-culture"><a class="nav-link" data-bs-toggle="pill" href="#culture_{{ $safeId }}">Culture</a></li>
                            <li class="nav-item li-tab-services"><a class="nav-link" data-bs-toggle="pill" href="#sia_{{ $safeId }}">Services</a></li>
                        </ul>

                        <div class="tab-content">
                            <!-- Overview -->
                            <div class="tab-pane fade show active" id="overview_{{ $safeId }}">
                                <div class="comp-section">
                                    <div class="comp-section-title">Description</div>
                                    <p style="font-size:0.88rem; color:#374151;">{{ $obj->objective_description ?: 'Tidak ada deskripsi.' }}</p>
                                </div>
                                <div class="comp-section">
                                    <div class="comp-section-title">Purpose</div>
                                    <p style="font-size:0.88rem; color:#374151;">{{ $obj->objective_purpose ?: 'Tidak ada purpose.' }}</p>
                                </div>
                                @if($obj->domains->isNotEmpty())
                                    <div class="comp-section">
                                        <div class="comp-section-title">Domains</div>
                                        @foreach($obj->domains as $d)
                                            <span class="badge bg-primary me-1">{{ $d->pivot->domain ?? $d->area }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <!-- Enterprise Goals -->
                            <div class="tab-pane fade" id="entergoals_{{ $safeId }}">
                                @forelse($obj->entergoals as $eg)
                                    <div class="comp-section">
                                        <div class="comp-section-title d-flex justify-content-between align-items-center gap-2">
                                            <span>{{ $displayObjectiveId($eg->entergoals_id) }}</span>
                                            @if(auth()->check() && auth()->user()->can('design-factors.input'))
                                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2"
                                                    data-child-type="entergoal"
                                                    data-child-id="{{ $eg->entergoals_id }}"
                                                    data-child-description="{{ $eg->description }}"
                                                    data-child-focus-area-id="{{ $focusArea->id }}"
                                                    onclick="event.stopPropagation(); openChildEditorFromButton(this)">
                                                    <i class="fas fa-pen"></i>
                                                </button>
                                            @endif
                                        </div>
                                        <div style="font-size:0.88rem; color:#374151;">{{ $eg->description }}</div>
                                        @if($eg->entergoalsmetr->isNotEmpty())
                                            <ul class="metric-list mt-2">
                                                @foreach($eg->entergoalsmetr as $metr)
                                                    <li class="metric-item">
                                                        <span class="text-muted small">• {{ $metr->description }}</span>
                                                        @if(auth()->check() && auth()->user()->can('design-factors.input'))
                                                            <button type="button" class="btn btn-sm btn-outline-secondary mini-edit-btn"
                                                                data-child-type="entergoalmetric"
                                                                data-child-id="{{ $metr->entergoalsmetr_id }}"
                                                                data-child-field1="{{ $metr->description }}"
                                                                data-child-focus-area-id="{{ $focusArea->id }}"
                                                                onclick="event.stopPropagation(); openChildEditorFromButton(this)">
                                                                <i class="fas fa-pen" style="font-size:0.7rem;"></i>
                                                            </button>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                @empty
                                    <p class="comp-empty">Tidak ada enterprise goals.</p>
                                @endforelse
                            </div>

                            <!-- Alignment Goals -->
                            <div class="tab-pane fade" id="aligngoals_{{ $safeId }}">
                                @forelse($obj->aligngoals as $ag)
                                    <div class="comp-section">
                                        <div class="comp-section-title d-flex justify-content-between align-items-center gap-2">
                                            <span>{{ $displayObjectiveId($ag->aligngoals_id) }}</span>
                                            @if(auth()->check() && auth()->user()->can('design-factors.input'))
                                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2"
                                                    data-child-type="aligngoal"
                                                    data-child-id="{{ $ag->aligngoals_id }}"
                                                    data-child-description="{{ $ag->description }}"
                                                    data-child-focus-area-id="{{ $focusArea->id }}"
                                                    onclick="event.stopPropagation(); openChildEditorFromButton(this)">
                                                    <i class="fas fa-pen"></i>
                                                </button>
                                            @endif
                                        </div>
                                        <div style="font-size:0.88rem; color:#374151;">{{ $ag->description }}</div>
                                        @if($ag->aligngoalsmetr->isNotEmpty())
                                            <ul class="metric-list mt-2">
                                                @foreach($ag->aligngoalsmetr as $metr)
                                                    <li class="metric-item">
                                                        <span class="text-muted small">• {{ $metr->description }}</span>
                                                        @if(auth()->check() && auth()->user()->can('design-factors.input'))
                                                            <button type="button" class="btn btn-sm btn-outline-secondary mini-edit-btn"
                                                                data-child-type="aligngoalmetric"
                                                                data-child-id="{{ $metr->aligngoalsmetr_id }}"
                                                                data-child-field1="{{ $metr->description }}"
                                                                data-child-focus-area-id="{{ $focusArea->id }}"
                                                                onclick="event.stopPropagation(); openChildEditorFromButton(this)">
                                                                <i class="fas fa-pen" style="font-size:0.7rem;"></i>
                                                            </button>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                @empty
                                    <p class="comp-empty">Tidak ada alignment goals.</p>
                                @endforelse
                            </div>

                            <!-- Practices -->
                            <div class="tab-pane fade" id="practices_{{ $safeId }}">
                                @if(auth()->check() && auth()->user()->can('design-factors.input'))
                                    <div class="mb-3 text-end">
                                        <button type="button" class="btn btn-sm btn-primary"
                                            data-child-type="practice"
                                            data-child-objective-id="{{ $obj->objective_id }}"
                                            data-child-focus-area-id="{{ $focusArea->id }}"
                                            onclick="event.stopPropagation(); openChildEditorFromButton(this)">
                                            <i class="fas fa-plus me-1"></i>Tambah Practices
                                        </button>
                                    </div>
                                @endif
                                @forelse($obj->practices as $practice)
                                    <div class="comp-section">
                                        <div class="comp-section-title d-flex justify-content-between align-items-center gap-2">
                                            <span>{{ $displayObjectiveId($practice->practice_id) }} — {{ $practice->practice_name }}</span>
                                            @if(auth()->check() && auth()->user()->can('design-factors.input'))
                                                <div>
                                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2"
                                                        data-child-type="practice"
                                                        data-child-id="{{ $practice->practice_id }}"
                                                        data-child-field1="{{ $practice->practice_name }}"
                                                        data-child-field2="{{ $practice->practice_description }}"
                                                        data-child-focus-area-id="{{ $focusArea->id }}"
                                                        onclick="event.stopPropagation(); openChildEditorFromButton(this)">
                                                        <i class="fas fa-pen"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 ms-1"
                                                        onclick="event.stopPropagation(); deletePractice('{{ $practice->practice_id }}')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                        <p style="font-size:0.84rem; color:#6b7392;">{{ $practice->practice_description }}</p>

                                        @if($practice->roles->isNotEmpty())
                                            <div class="mb-2">
                                                <strong style="font-size:0.8rem; color:#4b5563;">RACI:</strong>
                                                @foreach($practice->roles as $role)
                                                    @php $raci = strtoupper($role->pivot->r_a ?? ''); @endphp
                                                    @if($raci && $raci !== '-')
                                                        <span class="raci-badge raci-{{ $raci }}">{{ $role->role }}: {{ $raci }}</span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif

                                        @if($practice->activities->isNotEmpty())
                                            <table class="comp-table">
                                                <thead>
                                                    <tr>
                                                        <th>Activity</th>
                                                        <th class="activity-level-col-{{ $safeId }}" style="width:80px">Level</th>
                                                        @if(auth()->check() && auth()->user()->can('design-factors.input'))
                                                            <th style="width:70px"></th>
                                                        @endif
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($practice->activities as $act)
                                                        <tr>
                                                            <td>{{ $act->description }}</td>
                                                            <td class="activity-level-col-{{ $safeId }}">{{ $act->capability_lvl }}</td>
                                                            @if(auth()->check() && auth()->user()->can('design-factors.input'))
                                                                <td class="text-end">
                                                                    <button type="button" class="btn btn-sm btn-outline-secondary mini-edit-btn"
                                                                        data-child-type="activity"
                                                                        data-child-id="{{ $act->activity_id }}"
                                                                        data-child-field1="{{ $act->description }}"
                                                                        data-child-field2="{{ $act->capability_lvl }}"
                                                                        data-child-focus-area-id="{{ $focusArea->id }}"
                                                                        onclick="event.stopPropagation(); openChildEditorFromButton(this)">
                                                                        <i class="fas fa-pen" style="font-size:0.7rem;"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-sm btn-outline-danger mini-edit-btn ms-1"
                                                                        onclick="event.stopPropagation(); deleteActivity('{{ $act->activity_id }}')">
                                                                        <i class="fas fa-trash" style="font-size:0.7rem;"></i>
                                                                    </button>
                                                                </td>
                                                            @endif
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @endif
                                        @if(auth()->check() && auth()->user()->can('design-factors.input'))
                                            <div class="mt-2 mb-3 text-end">
                                                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2"
                                                    data-child-type="activity"
                                                    data-child-practice-id="{{ $practice->practice_id }}"
                                                    data-child-focus-area-id="{{ $focusArea->id }}"
                                                    onclick="event.stopPropagation(); openChildEditorFromButton(this)">
                                                    <i class="fas fa-plus me-1"></i>Tambah Activity
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <p class="comp-empty">Tidak ada practices.</p>
                                @endforelse
                            </div>

                            <!-- Organizational -->
                            <div class="tab-pane fade" id="organizational_{{ $safeId }}">
                                @if($masterRoles->isEmpty())
                                    <p class="comp-empty">Tidak ada master roles untuk ditampilkan.</p>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered table-striped org-table mb-0">
                                            <thead class="table-primary text-white">
                                                <tr>
                                                    <th>Key Management Practice</th>
                                                    @foreach($masterRoles as $role)
                                                        <th class="text-center position-relative" style="width:64px; vertical-align: top; padding-bottom: 65px !important;">
                                                            <div class="vertical-text mx-auto">{{ $role->role }}</div>
                                                            @if(auth()->check() && auth()->user()->can('design-factors.input'))
                                                                <div class="position-absolute bottom-0 start-50 translate-middle-x w-100 pb-2">
                                                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-1 mt-1 mx-auto d-block"
                                                                        data-child-type="masterrole"
                                                                        data-child-id="{{ $role->role_id }}"
                                                                        data-child-field1="{{ $role->role }}"
                                                                        data-child-field2="{{ $role->description }}"
                                                                        onclick="event.stopPropagation(); openChildEditorFromButton(this)">
                                                                        <i class="fas fa-pen" style="font-size:0.7rem;"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1 mt-1 mx-auto d-block"
                                                                        onclick="event.stopPropagation(); deleteObjectiveRole('{{ $obj->objective_id }}', '{{ $role->role_id }}', '{{ addslashes($role->role) }}')">
                                                                        <i class="fas fa-trash" style="font-size:0.7rem;"></i>
                                                                    </button>
                                                                </div>
                                                            @endif
                                                        </th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($obj->practices as $practice)
                                                    <tr>
                                                        <td>
                                                            <div class="fw-semibold">
                                                                {{ $displayObjectiveId($practice->practice_id) }}{{ $practice->practice_name ? ' - ' . $practice->practice_name : '' }}
                                                            </div>
                                                            <div class="text-muted small">{{ $practice->practice_description }}</div>
                                                            @if(auth()->check() && auth()->user()->can('design-factors.input'))
                                                                <div class="mt-2">
                                                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2"
                                                                        data-child-type="practice"
                                                                        data-child-id="{{ $practice->practice_id }}"
                                                                        data-child-field1="{{ $practice->practice_name }}"
                                                                        data-child-field2="{{ $practice->practice_description }}"
                                                                        data-child-focus-area-id="{{ $focusArea->id }}"
                                                                        onclick="event.stopPropagation(); openChildEditorFromButton(this)">
                                                                        <i class="fas fa-pen me-1"></i>Edit
                                                                    </button>
                                                                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 ms-1"
                                                                        onclick="event.stopPropagation(); deletePractice('{{ $practice->practice_id }}')">
                                                                        <i class="fas fa-trash me-1"></i>Hapus
                                                                    </button>
                                                                </div>
                                                            @endif
                                                        </td>
                                                        @foreach($masterRoles as $role)
                                                            @php
                                                                $matchedRole = $practice->roles->firstWhere('role_id', $role->role_id);
                                                                $currentRaci = strtoupper($matchedRole->pivot->r_a ?? '-');
                                                            @endphp
                                                            <td class="text-center">
                                                                <div class="d-flex flex-column align-items-center gap-1">
                                                                    <span class="raci-badge raci-{{ in_array($currentRaci, ['R','A','C','I']) ? $currentRaci : 'I' }}">{{ $currentRaci }}</span>
                                                                    @if(auth()->check() && auth()->user()->can('design-factors.input'))
                                                                        <button type="button" class="btn btn-sm btn-outline-secondary mini-edit-btn"
                                                                            data-child-type="practicerole"
                                                                            data-child-id="{{ $practice->practice_id }}"
                                                                            data-child-role-id="{{ $role->role_id }}"
                                                                            data-child-field1="{{ $currentRaci }}"
                                                                            data-child-practice-name="{{ $practice->practice_name }}"
                                                                            data-child-role-name="{{ $role->role }}"
                                                                            data-child-focus-area-id="{{ $focusArea->id }}"
                                                                            onclick="event.stopPropagation(); openChildEditorFromButton(this)">
                                                                            <i class="fas fa-pen" style="font-size:0.7rem;"></i>
                                                                        </button>
                                                                    @endif
                                                                </div>
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>

                            <!-- Policies -->
                            <div class="tab-pane fade" id="policies_{{ $safeId }}">
                                @forelse($obj->policies as $policy)
                                    <div class="comp-section">
                                        <div class="comp-section-title d-flex justify-content-between align-items-center gap-2">
                                            <span>{{ $displayObjectiveId($policy->policy_id) }}</span>
                                            @if(auth()->check() && auth()->user()->can('design-factors.input'))
                                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2"
                                                    data-child-type="policy"
                                                    data-child-id="{{ $policy->policy_id }}"
                                                    data-child-field1="{{ $policy->policy }}"
                                                    data-child-field2="{{ $policy->description }}"
                                                    data-child-focus-area-id="{{ $focusArea->id }}"
                                                    onclick="event.stopPropagation(); openChildEditorFromButton(this)">
                                                    <i class="fas fa-pen"></i>
                                                </button>
                                            @endif
                                        </div>
                                        <div class="mb-1 fw-semibold">{{ $policy->policy }}</div>
                                        <div style="font-size:0.88rem; color:#374151;">{{ $policy->description }}</div>
                                        @if($policy->guidances->isNotEmpty())
                                            <div class="mt-1 ms-3">
                                                @foreach($policy->guidances as $g)
                                                    <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                                                        <small class="text-muted d-block">📖 {{ $g->guidance }} — {{ $g->reference }}</small>
                                                        @if(auth()->check() && auth()->user()->can('design-factors.input'))
                                                            <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1"
                                                                data-child-type="guidance"
                                                                data-child-id="{{ $g->guidance_id }}"
                                                                data-child-field1="{{ $g->guidance }}"
                                                                data-child-field2="{{ $g->reference }}"
                                                                data-child-focus-area-id="{{ $focusArea->id }}"
                                                                onclick="event.stopPropagation(); openChildEditorFromButton(this)">
                                                                <i class="fas fa-pen"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <p class="comp-empty">Tidak ada policies.</p>
                                @endforelse
                            </div>

                            <!-- Skills -->
                            <div class="tab-pane fade" id="skills_{{ $safeId }}">
                                @if(auth()->check() && auth()->user()->can('design-factors.input'))
                                    <div class="mb-3 text-end">
                                        <button type="button" class="btn btn-sm btn-primary"
                                            data-child-type="skill"
                                            data-child-objective-id="{{ $obj->objective_id }}"
                                            data-child-focus-area-id="{{ $focusArea->id }}"
                                            onclick="event.stopPropagation(); openChildEditorFromButton(this)">
                                            <i class="fas fa-plus"></i> Tambah Skill
                                        </button>
                                    </div>
                                @endif
                                @forelse($obj->skill as $sk)
                                    <div class="comp-section">
                                        <div class="comp-section-title d-flex justify-content-between align-items-center gap-2">
                                            <span>{{ $displayObjectiveId($sk->skill_id) }}</span>
                                            @if(auth()->check() && auth()->user()->can('design-factors.input'))
                                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2"
                                                    data-child-type="skill"
                                                    data-child-id="{{ $sk->skill_id }}"
                                                    data-child-field1="{{ $sk->skill }}"
                                                    data-child-focus-area-id="{{ $focusArea->id }}"
                                                    onclick="event.stopPropagation(); openChildEditorFromButton(this)">
                                                    <i class="fas fa-pen"></i>
                                                </button>
                                            @endif
                                        </div>
                                        <div style="font-size:0.88rem; color:#374151;">{{ $sk->skill }}</div>
                                        @if($sk->guidances->isNotEmpty())
                                            <div class="mt-1 ms-3">
                                                @foreach($sk->guidances as $g)
                                                    <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                                                        <small class="text-muted d-block">📖 {{ $g->guidance }} — {{ $g->reference }}</small>
                                                        @if(auth()->check() && auth()->user()->can('design-factors.input'))
                                                            <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1"
                                                                data-child-type="guidance"
                                                                data-child-id="{{ $g->guidance_id }}"
                                                                data-child-field1="{{ $g->guidance }}"
                                                                data-child-field2="{{ $g->reference }}"
                                                                data-child-focus-area-id="{{ $focusArea->id }}"
                                                                onclick="event.stopPropagation(); openChildEditorFromButton(this)">
                                                                <i class="fas fa-pen"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <p class="comp-empty">Tidak ada skills.</p>
                                @endforelse
                            </div>

                            <!-- Culture -->
                            <div class="tab-pane fade" id="culture_{{ $safeId }}">
                                @forelse($obj->keyculture as $cul)
                                    <div class="comp-section">
                                        <div class="comp-section-title d-flex justify-content-between align-items-center gap-2">
                                            <span>{{ $displayObjectiveId($cul->keyculture_id) }}</span>
                                            @if(auth()->check() && auth()->user()->can('design-factors.input'))
                                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2"
                                                    data-child-type="culture"
                                                    data-child-id="{{ $cul->keyculture_id }}"
                                                    data-child-field1="{{ $cul->element }}"
                                                    data-child-focus-area-id="{{ $focusArea->id }}"
                                                    onclick="event.stopPropagation(); openChildEditorFromButton(this)">
                                                    <i class="fas fa-pen"></i>
                                                </button>
                                            @endif
                                        </div>
                                        <div style="font-size:0.88rem; color:#374151;">{{ $cul->element }}</div>
                                        @if($cul->guidances->isNotEmpty())
                                            <div class="mt-1 ms-3">
                                                @foreach($cul->guidances as $g)
                                                    <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                                                        <small class="text-muted d-block">📖 {{ $g->guidance }} — {{ $g->reference }}</small>
                                                        @if(auth()->check() && auth()->user()->can('design-factors.input'))
                                                            <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1"
                                                                data-child-type="guidance"
                                                                data-child-id="{{ $g->guidance_id }}"
                                                                data-child-field1="{{ $g->guidance }}"
                                                                data-child-field2="{{ $g->reference }}"
                                                                data-child-focus-area-id="{{ $focusArea->id }}"
                                                                onclick="event.stopPropagation(); openChildEditorFromButton(this)">
                                                                <i class="fas fa-pen"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <p class="comp-empty">Tidak ada culture elements.</p>
                                @endforelse
                            </div>

                            <!-- Services (SIA) -->
                            <div class="tab-pane fade" id="sia_{{ $safeId }}">
                                @forelse($obj->s_i_a as $sia)
                                    <div class="comp-section">
                                        <div class="comp-section-title d-flex justify-content-between align-items-center gap-2">
                                            <span>{{ $displayObjectiveId($sia->sia_id) }}</span>
                                            @if(auth()->check() && auth()->user()->can('design-factors.input'))
                                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2"
                                                    data-child-type="sia"
                                                    data-child-id="{{ $sia->sia_id }}"
                                                    data-child-field1="{{ $sia->description }}"
                                                    data-child-focus-area-id="{{ $focusArea->id }}"
                                                    onclick="event.stopPropagation(); openChildEditorFromButton(this)">
                                                    <i class="fas fa-pen"></i>
                                                </button>
                                            @endif
                                        </div>
                                        <div style="font-size:0.88rem; color:#374151;">{{ $sia->description }}</div>
                                    </div>
                                @empty
                                    <p class="comp-empty">Tidak ada services/infrastructure.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <!-- Edit Model Modal -->
    <div class="modal fade" id="editFaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0" style="border-radius:1rem;">
                <div class="modal-header" style="background:linear-gradient(135deg,#081a3d,#0f2b5c); color:#fff; border-radius:1rem 1rem 0 0;">
                    <h5 class="modal-title"><i class="fas fa-pen me-2"></i>Edit Model</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="editFaCode" class="form-label fw-semibold">Code</label>
                        <input type="text" id="editFaCode" class="form-control" value="{{ $focusArea->code }}" maxlength="10" style="text-transform:uppercase;">
                    </div>
                    <div class="mb-3">
                        <label for="editFaName" class="form-label fw-semibold">Name</label>
                        <input type="text" id="editFaName" class="form-control" value="{{ $focusArea->name }}" maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label for="editFaDesc" class="form-label fw-semibold">Description</label>
                        <textarea id="editFaDesc" class="form-control" rows="3">{{ $focusArea->description }}</textarea>
                    </div>
                    <hr>
                    <h6 class="fw-semibold mb-3">Pengaturan Khusus (Disimpan di Browser)</h6>
                    <div class="mb-3 form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="editFaIsCobit5">
                        <label class="form-check-label fw-semibold" for="editFaIsCobit5">Mode COBIT 5 (Ubah Nama Tab Goals)</label>
                    </div>
                    <div class="mb-3 form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="editFaShowLevel" checked>
                        <label class="form-check-label fw-semibold" for="editFaShowLevel">Tampilkan Kolom Level (2-5) di semua Tabel Activity</label>
                    </div>
                    
                    <div id="cobit5TabSettings" style="background:#f8f9fa; padding:1rem; border-radius:0.5rem; margin-bottom:1rem;">
                        <div class="mb-2 form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="editFaShowPolicies" checked>
                            <label class="form-check-label" for="editFaShowPolicies">Tampilkan Tab Policies</label>
                        </div>
                        <div class="mb-2 form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="editFaShowSkills" checked>
                            <label class="form-check-label" for="editFaShowSkills">Tampilkan Tab Skills</label>
                        </div>
                        <div class="mb-2 form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="editFaShowCulture" checked>
                            <label class="form-check-label" for="editFaShowCulture">Tampilkan Tab Culture</label>
                        </div>
                        <div class="mb-1 form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="editFaShowServices" checked>
                            <label class="form-check-label" for="editFaShowServices">Tampilkan Tab Services & Infra</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="saveEditFa()">
                        <i class="fas fa-save me-1"></i>Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const FOCUS_AREA_ID = {{ $focusArea->id }};
        const FLASH_NOTIF_KEY = 'focus_area_flash_notif';

        function displayObjectiveId(value) {
            return String(value || '').replace(/\.(?:M|FA)\d+(?:\.\d+)?$/i, '');
        }

        function showNotif(message, type = 'success') {
            const wrap = document.getElementById('faNotifWrap');
            const div = document.createElement('div');
            div.className = `alert alert-${type} alert-dismissible fade show shadow-sm`;
            div.style.fontSize = '0.88rem';
            div.style.whiteSpace = 'pre-line';
            div.textContent = message;
            const closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.className = 'btn-close btn-sm';
            closeBtn.setAttribute('data-bs-dismiss', 'alert');
            div.appendChild(closeBtn);
            wrap.appendChild(div);
            setTimeout(() => div.remove(), 4000);
        }

        function queueFlashNotif(message, type = 'success') {
            sessionStorage.setItem(FLASH_NOTIF_KEY, JSON.stringify({ message, type }));
        }

        function consumeFlashNotif() {
            const raw = sessionStorage.getItem(FLASH_NOTIF_KEY);
            if (!raw) return null;

            sessionStorage.removeItem(FLASH_NOTIF_KEY);

            try {
                return JSON.parse(raw);
            } catch (error) {
                return null;
            }
        }

        async function extractErrorMessage(response, fallback = 'Gagal menyimpan.') {
            const contentType = response.headers.get('content-type') || '';

            try {
                if (contentType.includes('application/json')) {
                    const payload = await response.json();
                    if (payload?.errors) {
                        return Object.values(payload.errors).flat().join('\n') || fallback;
                    }
                    return payload?.message || fallback;
                }

                const text = await response.text();
                return text.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim() || fallback;
            } catch (error) {
                return fallback;
            }
        }

        function toggleAccordion(safeId) {
            const body = document.getElementById('accordion_' + safeId);
            const header = body?.previousElementSibling;
            if (body) {
                const isOpen = body.classList.toggle('open');
                header?.classList.toggle('open');
                
                let openAccordions = JSON.parse(sessionStorage.getItem('openAccordions') || '[]');
                if (isOpen) {
                    if (!openAccordions.includes(safeId)) openAccordions.push(safeId);
                } else {
                    openAccordions = openAccordions.filter(id => id !== safeId);
                }
                sessionStorage.setItem('openAccordions', JSON.stringify(openAccordions));
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const openAccordions = JSON.parse(sessionStorage.getItem('openAccordions') || '[]');
            openAccordions.forEach(safeId => {
                const body = document.getElementById('accordion_' + safeId);
                const header = body?.previousElementSibling;
                if (body && header) {
                    body.classList.add('open');
                    header.classList.add('open');
                }
            });
            
            const scrollPos = sessionStorage.getItem('scrollPos');
            if (scrollPos) {
                setTimeout(() => window.scrollTo(0, parseInt(scrollPos)), 50);
                sessionStorage.removeItem('scrollPos');
            }
            
            // Restore active tabs
            document.querySelectorAll('.obj-accordion-body').forEach(body => {
                const activeTabHref = sessionStorage.getItem('activeTab_' + body.id);
                if (activeTabHref) {
                    const tabTrigger = body.querySelector(`a[href="${activeTabHref}"]`);
                    if (tabTrigger) {
                        const tab = new bootstrap.Tab(tabTrigger);
                        tab.show();
                    }
                }
            });

            // Listen for tab changes
            document.querySelectorAll('a[data-bs-toggle="pill"]').forEach(tab => {
                tab.addEventListener('shown.bs.tab', event => {
                    const targetId = event.target.getAttribute('href');
                    const parent = event.target.closest('.obj-accordion-body');
                    if (parent) {
                        sessionStorage.setItem('activeTab_' + parent.id, targetId);
                    }
                });
            });

            window.addEventListener('beforeunload', () => {
                sessionStorage.setItem('scrollPos', window.scrollY);
            });
        });

        function openEditFaModal() {
            new bootstrap.Modal(document.getElementById('editFaModal')).show();
        }

        async function saveEditFa() {
            const code = document.getElementById('editFaCode').value.trim().toUpperCase();
            const name = document.getElementById('editFaName').value.trim();
            const description = document.getElementById('editFaDesc').value.trim();
            const showLevel = document.getElementById('editFaShowLevel').checked;
            const isCobit5 = document.getElementById('editFaIsCobit5').checked;
            const showPolicies = document.getElementById('editFaShowPolicies').checked;
            const showSkills = document.getElementById('editFaShowSkills').checked;
            const showCulture = document.getElementById('editFaShowCulture').checked;
            const showServices = document.getElementById('editFaShowServices').checked;

            if (!code || !name) {
                showNotif('Code dan Name wajib diisi.', 'warning');
                return;
            }
            
            localStorage.setItem('showLevel_FA_' + FOCUS_AREA_ID, showLevel ? '1' : '0');
            localStorage.setItem('isCobit5_FA_' + FOCUS_AREA_ID, isCobit5 ? '1' : '0');
            localStorage.setItem('showPolicies_FA_' + FOCUS_AREA_ID, showPolicies ? '1' : '0');
            localStorage.setItem('showSkills_FA_' + FOCUS_AREA_ID, showSkills ? '1' : '0');
            localStorage.setItem('showCulture_FA_' + FOCUS_AREA_ID, showCulture ? '1' : '0');
            localStorage.setItem('showServices_FA_' + FOCUS_AREA_ID, showServices ? '1' : '0');

                try {
                    const response = await fetch(`{{ url('/focus-areas') }}/${FOCUS_AREA_ID}`, {
                        method: 'PUT',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    body: JSON.stringify({ code, name, description })
                    });

                    if (!response.ok) {
                        throw new Error(await extractErrorMessage(response));
                    }

                bootstrap.Modal.getInstance(document.getElementById('editFaModal'))?.hide();
                queueFlashNotif('Focus area berhasil diperbarui.');
                location.reload();
            } catch (e) {
                showNotif(e.message, 'danger');
            }
        }

        // Auto-open first accordion if only 1 objective
        // ===== CREATE / EDIT OBJECTIVE MODAL =====
        document.addEventListener('DOMContentLoaded', () => {
            const showLevel = localStorage.getItem('showLevel_FA_' + FOCUS_AREA_ID) !== '0';
            const isCobit5 = localStorage.getItem('isCobit5_FA_' + FOCUS_AREA_ID) === '1';
            const showPolicies = localStorage.getItem('showPolicies_FA_' + FOCUS_AREA_ID) !== '0';
            const showSkills = localStorage.getItem('showSkills_FA_' + FOCUS_AREA_ID) !== '0';
            const showCulture = localStorage.getItem('showCulture_FA_' + FOCUS_AREA_ID) !== '0';
            const showServices = localStorage.getItem('showServices_FA_' + FOCUS_AREA_ID) !== '0';

            const editFaShowLevel = document.getElementById('editFaShowLevel');
            const editFaIsCobit5 = document.getElementById('editFaIsCobit5');
            const editFaShowPolicies = document.getElementById('editFaShowPolicies');
            const editFaShowSkills = document.getElementById('editFaShowSkills');
            const editFaShowCulture = document.getElementById('editFaShowCulture');
            const editFaShowServices = document.getElementById('editFaShowServices');
            
            if (editFaShowLevel) editFaShowLevel.checked = showLevel;
            if (editFaIsCobit5) editFaIsCobit5.checked = isCobit5;
            if (editFaShowPolicies) editFaShowPolicies.checked = showPolicies;
            if (editFaShowSkills) editFaShowSkills.checked = showSkills;
            if (editFaShowCulture) editFaShowCulture.checked = showCulture;
            if (editFaShowServices) editFaShowServices.checked = showServices;
            
            document.querySelectorAll('th[class^="activity-level-col-"], td[class^="activity-level-col-"]').forEach(col => {
                col.style.display = showLevel ? 'table-cell' : 'none';
            });
            
            if (isCobit5) {
                document.querySelectorAll('a[data-tab-name="entergoals"]').forEach(el => el.textContent = 'IT-related Goal');
                document.querySelectorAll('a[data-tab-name="aligngoals"]').forEach(el => el.textContent = 'Process Goals and Metrics');
            }
            
            document.querySelectorAll('.li-tab-policies').forEach(el => el.style.display = showPolicies ? 'block' : 'none');
            document.querySelectorAll('.li-tab-skills').forEach(el => el.style.display = showSkills ? 'block' : 'none');
            document.querySelectorAll('.li-tab-culture').forEach(el => el.style.display = showCulture ? 'block' : 'none');
            document.querySelectorAll('.li-tab-services').forEach(el => el.style.display = showServices ? 'block' : 'none');
            
            const flashNotif = consumeFlashNotif();
            if (flashNotif?.message) {
                showNotif(flashNotif.message, flashNotif.type || 'success');
            }

            const cards = document.querySelectorAll('.obj-accordion-card');
            if (cards.length === 1) {
                const header = cards[0].querySelector('.obj-accordion-header');
                if (header) header.click();
            }

            const createObjModal = new bootstrap.Modal(document.getElementById('createObjModal'));
            const editObjModal = new bootstrap.Modal(document.getElementById('editObjModal'));
            const createObjForm = document.getElementById('createObjForm');
            const editObjForm = document.getElementById('editObjForm');
            const createObjModalTitle = document.getElementById('createObjModalTitle');
            const editObjModalTitle = document.getElementById('editObjModalTitle');
            const editObjIdInput = document.getElementById('editObjId');
            const baselineObjectiveSelect = document.getElementById('baselineObjectiveSelect');
            const childEditModal = new bootstrap.Modal(document.getElementById('childEditModal'));
            const childEditTypeInput = document.getElementById('childEditType');
            const childEditIdInput = document.getElementById('childEditId');
            const childEditRoleIdInput = document.getElementById('childEditRoleId');
            const childEditFocusAreaIdInput = document.getElementById('childEditFocusAreaId');
            const childEditTitle = document.getElementById('childEditModalTitle');
            const childEditField1 = document.getElementById('childEditField1');
            const childEditField1Select = document.getElementById('childEditField1Select');
            const childEditField1InputWrap = document.getElementById('childEditField1InputWrap');
            const childEditField1SelectWrap = document.getElementById('childEditField1SelectWrap');
            const childEditField2 = document.getElementById('childEditField2');
            const childEditField1Label = document.getElementById('childEditField1Label');
            const childEditField2Wrap = document.getElementById('childEditField2Wrap');
            const childEditField2Label = document.getElementById('childEditField2Label');
            const childEditHelpText = document.getElementById('childEditHelpText');
            const childEditSaveBtn = document.getElementById('childEditSaveBtn');

            window.createObjForm = createObjForm;
            window.editObjForm = editObjForm;
            window.openChildEditorFromButton = function(button) {
                if (!button) return;
                window.currentEditButton = button;
                window.openChildEditor(button.dataset.childType, {
                    id: button.dataset.childId || '',
                    role_id: button.dataset.childRoleId || '',
                    description: button.dataset.childDescription || '',
                    field1: button.dataset.childField1 || '',
                    field2: button.dataset.childField2 || '',
                    focus_area_id: button.dataset.childFocusAreaId || FOCUS_AREA_ID,
                    objective_id: button.dataset.childObjectiveId || '',
                    practice_id: button.dataset.childPracticeId || '',
                    practice_name: button.dataset.childPracticeName || '',
                    role_name: button.dataset.childRoleName || '',
                    raci: button.dataset.childField1 || '',
                    policy: button.dataset.childField1 || '',
                    practice_description: button.dataset.childField2 || '',
                    skill: button.dataset.childField1 || '',
                    element: button.dataset.childField1 || '',
                    guidance: button.dataset.childField1 || '',
                    reference: button.dataset.childField2 || '',
                });
            };
            window.openChildEditor = function(type, data) {
                const payload = data || {};
                const config = {
                    entergoal: {
                        title: 'Edit Enterprise Goal',
                        field1Label: 'Description',
                        field2Hidden: true,
                        route: `{{ url('/objectives/entergoals') }}`,
                    },
                    entergoalmetric: {
                        title: 'Edit Enterprise Goal Metric',
                        field1Label: 'Metric',
                        field2Hidden: true,
                        route: `{{ url('/objectives/entergoals-metrics') }}`,
                    },
                    aligngoal: {
                        title: 'Edit Alignment Goal',
                        field1Label: 'Description',
                        field2Hidden: true,
                        route: `{{ url('/objectives/aligngoals') }}`,
                    },
                    aligngoalmetric: {
                        title: 'Edit Alignment Goal Metric',
                        field1Label: 'Metric',
                        field2Hidden: true,
                        route: `{{ url('/objectives/aligngoals-metrics') }}`,
                    },
                    masterrole: {
                        title: 'Edit Master Role',
                        field1Label: 'Role Name',
                        field2Label: 'Description',
                        field2Hidden: false,
                        route: `{{ url('/objectives/roles') }}`,
                        isMasterRole: true
                    },
                    practice: {
                        title: 'Edit Practice',
                        field1Label: 'Practice Name',
                        field2Label: 'Practice Description',
                        route: `{{ url('/objectives/practices') }}`,
                    },
                    activity: {
                        title: 'Edit Activity',
                        field1Label: 'Activity Description',
                        field2Label: 'Level',
                        route: `{{ url('/objectives/activities') }}`,
                    },
                    practicerole: {
                        title: 'Edit Practice Role',
                        field1Label: 'RACI',
                        field1Select: true,
                        field2Hidden: true,
                        route: `{{ url('/objectives/practices') }}`,
                    },
                    policy: {
                        title: 'Edit Policy',
                        field1Label: 'Policy',
                        field2Label: 'Description',
                        route: `{{ url('/objectives/policies') }}`,
                    },
                    skill: {
                        title: 'Edit Skill',
                        field1Label: 'Skill',
                        field2Hidden: true,
                        route: `{{ url('/objectives/skills') }}`,
                    },
                    culture: {
                        title: 'Edit Culture Element',
                        field1Label: 'Element',
                        field2Hidden: true,
                        route: `{{ url('/objectives/key-culture') }}`,
                    },
                    sia: {
                        title: 'Edit Service / SIA',
                        field1Label: 'Description',
                        field2Hidden: true,
                        route: `{{ url('/objectives/sia') }}`,
                    },
                    guidance: {
                        title: 'Edit Guidance',
                        field1Label: 'Guidance',
                        field2Label: 'Reference',
                        route: `{{ url('/objectives/guidance') }}`,
                    }
                }[type];

                if (!config) {
                    showNotif('Tipe data belum didukung untuk edit.', 'warning');
                    return;
                }

                childEditTypeInput.value = type;
                childEditIdInput.value = payload.id || '';
                childEditRoleIdInput.value = payload.role_id || '';
                childEditFocusAreaIdInput.value = payload.focus_area_id || FOCUS_AREA_ID;
                childEditTitle.textContent = config.title;
                childEditField1Label.textContent = config.field1Label || 'Field 1';
                childEditField1.value = '';
                childEditField1Select.value = 'R';
                childEditField2.value = '';
                childEditField2Wrap.style.display = config.field2Hidden ? 'none' : '';
                childEditField2Label.textContent = config.field2Label || 'Field 2';
                childEditField1InputWrap.style.display = config.field1Select ? 'none' : '';
                childEditField1SelectWrap.style.display = config.field1Select ? '' : 'none';

                if (type === 'entergoal' || type === 'aligngoal' || type === 'sia') {
                    childEditField1.value = payload.description || payload.field1 || '';
                } else if (type === 'entergoalmetric' || type === 'aligngoalmetric') {
                    childEditField1.value = payload.field1 || '';
                } else if (type === 'practice') {
                    childEditField1.value = payload.practice_name || payload.field1 || '';
                    childEditField2.value = payload.practice_description || payload.field2 || '';
                    if (!payload.id) {
                        document.getElementById('childEditObjectiveId').value = payload.objective_id;
                    }
                } else if (type === 'activity') {
                    childEditField1.value = payload.field1 || '';
                    childEditField2.value = payload.field2 || '';
                    if (!payload.id) {
                        document.getElementById('childEditPracticeId').value = payload.practice_id;
                    }
                } else if (type === 'practicerole') {
                    childEditField1Select.value = (payload.raci || payload.field1 || '-').toUpperCase();
                } else if (type === 'policy') {
                    childEditField1.value = payload.policy || payload.field1 || '';
                    childEditField2.value = payload.description || payload.field2 || '';
                } else if (type === 'skill') {
                    childEditField1.value = payload.skill || payload.field1 || '';
                    if (!payload.id) {
                        document.getElementById('childEditObjectiveId').value = payload.objective_id;
                    }
                } else if (type === 'culture') {
                    childEditField1.value = payload.element || payload.field1 || '';
                } else if (type === 'guidance') {
                    childEditField1.value = payload.guidance || payload.field1 || '';
                    childEditField2.value = payload.reference || payload.field2 || '';
                } else if (type === 'masterrole') {
                    childEditField1.value = payload.field1 || '';
                    childEditField2.value = payload.field2 || '';
                }

                childEditHelpText.textContent = config.field2Hidden
                    ? 'Perubahan akan diterapkan hanya pada clone di model ini.'
                    : 'Perubahan akan diterapkan hanya pada clone di model ini.';

                if (config.isMasterRole) {
                    childEditHelpText.textContent = 'Perhatian: Mengubah nama Role ini akan mengubah master data dan berdampak pada SELURUH komponen GAMO.';
                    childEditHelpText.classList.add('text-danger');
                    childEditHelpText.classList.remove('text-muted');
                } else {
                    childEditHelpText.classList.remove('text-danger');
                    childEditHelpText.classList.add('text-muted');
                }

                if (type === 'practicerole') {
                    childEditHelpText.textContent = `Edit RACI untuk ${payload.practice_name || 'practice'}${payload.role_name ? ' · ' + payload.role_name : ''}.`;
                }

                childEditSaveBtn.dataset.endpoint = config.route;
                childEditModal.show();
            };

            window.saveChildEditor = async function() {
                const type = childEditTypeInput.value;
                const id = childEditIdInput.value;
                const roleId = childEditRoleIdInput.value;
                const focusAreaId = childEditFocusAreaIdInput.value || FOCUS_AREA_ID;
                const endpoint = childEditSaveBtn.dataset.endpoint;

                if (!type || !endpoint) {
                    showNotif('Data edit belum lengkap.', 'warning');
                    return;
                }
                if (type === 'practicerole' && !roleId) {
                    showNotif('Role target belum ditemukan.', 'warning');
                    return;
                }

                const payload = { focus_area_id: focusAreaId };
                if (type === 'entergoal' || type === 'aligngoal' || type === 'sia' || type === 'entergoalmetric' || type === 'aligngoalmetric') {
                    payload.description = childEditField1.value.trim();
                } else if (type === 'practice') {
                    payload.practice_name = childEditField1.value.trim();
                    payload.practice_description = childEditField2.value.trim();
                    if (!id) payload.objective_id = document.getElementById('childEditObjectiveId').value;
                } else if (type === 'activity') {
                    payload.description = childEditField1.value.trim();
                    payload.capability_lvl = childEditField2.value.trim();
                    if (!id) payload.practice_id = document.getElementById('childEditPracticeId').value;
                } else if (type === 'practicerole') {
                    payload.r_a = childEditField1Select.value.trim().toUpperCase();
                } else if (type === 'policy') {
                    payload.policy = childEditField1.value.trim();
                    payload.description = childEditField2.value.trim();
                } else if (type === 'skill') {
                    payload.skill = childEditField1.value.trim();
                    if (!id) payload.objective_id = document.getElementById('childEditObjectiveId').value;
                } else if (type === 'culture') {
                    payload.element = childEditField1.value.trim();
                } else if (type === 'guidance') {
                    payload.guidance = childEditField1.value.trim();
                    payload.reference = childEditField2.value.trim();
                } else if (type === 'masterrole') {
                    payload.role = childEditField1.value.trim();
                    payload.description = childEditField2.value.trim();
                }

                if (type === 'practice' && !payload.practice_name) {
                    showNotif('Practice name wajib diisi.', 'warning');
                    return;
                }
                if (type === 'practicerole' && !['R', 'A', 'C', 'I', '-'].includes(payload.r_a)) {
                    showNotif('RACI harus R, A, C, I, atau -.', 'warning');
                    return;
                }
                if (type === 'masterrole' && !payload.role) {
                    showNotif('Role name wajib diisi.', 'warning');
                    return;
                }
                if (type === 'policy' && !payload.policy) {
                    showNotif('Policy wajib diisi.', 'warning');
                    return;
                }
                if ((type === 'skill' || type === 'culture' || type === 'entergoal' || type === 'aligngoal' || type === 'sia' || type === 'guidance' || type === 'entergoalmetric' || type === 'aligngoalmetric') && !String(payload.description || payload.skill || payload.element || payload.guidance || payload.field1 || '').trim()) {
                    showNotif('Field utama wajib diisi.', 'warning');
                    return;
                }
                if (type === 'guidance' && !payload.guidance) {
                    showNotif('Guidance wajib diisi.', 'warning');
                    return;
                }

                try {
                    let saveUrl = endpoint;
                    let method = 'PUT';

                    if (!id) {
                        method = 'POST';
                    } else if (type === 'practicerole') {
                        saveUrl = `${endpoint}/${encodeURIComponent(id)}/roles/${encodeURIComponent(roleId)}`;
                    } else {
                        saveUrl = `${endpoint}/${encodeURIComponent(id)}`;
                    }

                    const res = await fetch(saveUrl, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify(payload),
                    });

                    if (!res.ok) {
                        throw new Error(await extractErrorMessage(res));
                    }

                    if (type === 'practicerole' && window.currentEditButton) {
                        const btn = window.currentEditButton;
                        btn.dataset.childField1 = payload.r_a;
                        const badge = btn.parentElement.querySelector('.raci-badge');
                        if (badge) {
                            badge.textContent = payload.r_a;
                            badge.className = 'raci-badge raci-' + (['R','A','C','I'].includes(payload.r_a) ? payload.r_a : 'I');
                        }
                        childEditModal.hide();
                        queueFlashNotif('Data berhasil disimpan.');
                        return; // Prevent reload for instant UI update
                    }

                    childEditModal.hide();
                    queueFlashNotif('Data berhasil disimpan.');
                    location.reload();
                } catch (e) {
                    showNotif(e.message, 'danger');
                }
            };

            childEditSaveBtn.addEventListener('click', window.saveChildEditor);

            window.openCreateObjective = function() {
                createObjModalTitle.textContent = 'Tambah Objective Baru';
                createObjForm.reset();
                if (baselineObjectiveSelect) baselineObjectiveSelect.value = '';
                createObjModal.show();
            };

            window.openEditObjective = function(id, name, desc, purpose) {
                editObjModalTitle.textContent = `Edit Objective ${id}`;
                editObjIdInput.value = id;
                document.getElementById('editObjName').value = name;
                document.getElementById('editObjDesc').value = desc || '';
                document.getElementById('editObjPurpose').value = purpose || '';
                editObjModal.show();
            };

            createObjForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const selectedBaselineId = baselineObjectiveSelect?.value || '';

                const url = `{{ route('focus-areas.objectives.store', $focusArea->id) }}`;

                const payload = {};
                if (selectedBaselineId) {
                    payload.baseline_objective_id = selectedBaselineId;
                    const customCode = document.getElementById('customObjCode')?.value.trim();
                    const customName = document.getElementById('customObjName')?.value.trim();
                    if (customCode) payload.custom_objective_id = customCode;
                    if (customName) payload.custom_objective_name = customName;
                } else {
                    showNotif('Pilih 1 baseline objective dulu.', 'warning');
                    return;
                }

                try {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify(payload),
                    });
                    if (!res.ok) {
                        throw new Error(await extractErrorMessage(res));
                    }
                    createObjModal.hide();
                    queueFlashNotif('Objective baru berhasil ditambahkan.');
                    location.reload();
                } catch (e) {
                    showNotif(e.message, 'danger');
                }
            });

            editObjForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const objectiveId = editObjIdInput.value;
                const url = `{{ route('focus-areas.objectives.update', [$focusArea->id, '__id__']) }}`.replace('__id__', objectiveId);
                const payload = {
                    objective: document.getElementById('editObjName').value.trim(),
                    objective_description: document.getElementById('editObjDesc').value.trim(),
                    objective_purpose: document.getElementById('editObjPurpose').value.trim(),
                };

                if (!payload.objective) {
                    showNotif('Nama objective wajib diisi.', 'warning');
                    return;
                }

                try {
                    const res = await fetch(url, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify(payload),
                    });
                    if (!res.ok) {
                        throw new Error(await extractErrorMessage(res));
                    }
                    editObjModal.hide();
                    queueFlashNotif('Objective berhasil diperbarui.');
                    location.reload();
                } catch (e) {
                    showNotif(e.message, 'danger');
                }
            });
        });

        async function deleteObjective(objectiveId) {
            if (!confirm('Hapus objective beserta semua data child-nya?')) return;
            const url = `{{ route('focus-areas.objectives.destroy', [$focusArea->id, '__id__']) }}`.replace('__id__', objectiveId);
            try {
                const res = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
                if (!res.ok) throw new Error('Gagal menghapus.');
                queueFlashNotif('Objective berhasil dihapus.');
                location.reload();
            } catch (e) {
                showNotif(e.message, 'danger');
            }
        }

        async function deleteObjectiveRole(objectiveId, roleId, roleName) {
            if (!confirm(`Apakah Anda yakin ingin menghapus role "${roleName}" dari objective ${objectiveId}? Semua data RACI terkait role ini akan ikut terhapus.`)) return;
            const url = `{{ url('/objectives') }}/${encodeURIComponent(objectiveId)}/roles/${encodeURIComponent(roleId)}`;
            try {
                const res = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                });
                if (!res.ok) throw new Error('Gagal menghapus role.');
                queueFlashNotif('Role berhasil dihapus.');
                location.reload();
            } catch (e) {
                showNotif(e.message, 'danger');
            }
        }
        
        async function deletePractice(practiceId) {
            if (!confirm(`Apakah Anda yakin ingin menghapus practice ${practiceId}? Semua data terkait (Activities, Metrics, Roles, dll) akan ikut terhapus.`)) return;
            const url = `{{ url('/objectives/practices') }}/${encodeURIComponent(practiceId)}`;
            try {
                const res = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                });
                if (!res.ok) throw new Error('Gagal menghapus practice.');
                queueFlashNotif('Practice berhasil dihapus.');
                location.reload();
            } catch (e) {
                showNotif(e.message, 'danger');
            }
        }
        async function generateCobit5() {
            if (!confirm('Apakah Anda yakin ingin melakukan bulk clone 37 proses COBIT 5 ke dalam model ini?\nProses ini mungkin memerlukan waktu beberapa detik.')) return;
            
            const url = `{{ route('focus-areas.generate-cobit5', $focusArea->id) }}`;
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                });
                if (!res.ok) {
                    throw new Error(await extractErrorMessage(res));
                }
                const data = await res.json();
                queueFlashNotif(data.message);
                location.reload();
            } catch (e) {
                showNotif(e.message, 'danger');
            }
        }
    </script>

    <!-- Create Objective Modal -->
    <div class="modal fade" id="createObjModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background:var(--fa-primary); color:#fff;">
                    <h5 class="modal-title" id="createObjModalTitle">Tambah Objective</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <form id="createObjForm">
                        @csrf
                        <div class="mb-3">
                            <label for="baselineObjectiveSelect" class="form-label fw-semibold">Clone dari 40 GAMO baseline</label>
                            <select id="baselineObjectiveSelect" class="form-select">
                                <option value="">-- Pilih 1 baseline objective --</option>
                                @foreach($baselineObjectives as $o)
                                    <option value="{{ $o->objective_id }}">
                                        {{ $displayObjectiveId($o->objective_id) }} — {{ $o->objective }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">
                                Pilih satu baseline objective untuk dikopi relasinya ke model ini.
                            </div>
                        </div>

                        <div class="mb-3 mt-4">
                            <label class="form-label fw-semibold">Kustomisasi Nama/Kode (Opsional)</label>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <input type="text" id="customObjCode" class="form-control" placeholder="Kode (Cth: PO1)">
                                </div>
                                <div class="col-md-8">
                                    <input type="text" id="customObjName" class="form-control" placeholder="Nama (Cth: Define a Strategic IT Plan)">
                                </div>
                            </div>
                            <div class="form-text">
                                Jika ingin membuat objective custom (seperti COBIT 4/5), isikan kode dan nama di sini. Jika kosong, akan menggunakan bawaan dari baseline yang dipilih.
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="createObjForm.requestSubmit()">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Objective Modal -->
    <div class="modal fade" id="editObjModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background:var(--fa-primary); color:#fff;">
                    <h5 class="modal-title" id="editObjModalTitle">Edit Objective</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <form id="editObjForm">
                        @csrf
                        <input type="hidden" id="editObjId" value="">
                        <div class="mb-3">
                            <label for="editObjName" class="form-label">Nama Objective <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editObjName" maxlength="255" required>
                        </div>
                        <div class="mb-3">
                            <label for="editObjDesc" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="editObjDesc" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editObjPurpose" class="form-label">Purpose</label>
                            <textarea class="form-control" id="editObjPurpose" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="editObjForm.requestSubmit()">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Child Component Modal -->
    <div class="modal fade" id="childEditModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow-lg border-0" style="border-radius:1rem;">
                <div class="modal-header" style="background:linear-gradient(135deg,#081a3d,#0f2b5c); color:#fff; border-radius:1rem 1rem 0 0;">
                    <h5 class="modal-title" id="childEditModalTitle">Edit Item</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="childEditType">
                    <input type="hidden" id="childEditId">
                    <input type="hidden" id="childEditRoleId">
                    <input type="hidden" id="childEditFocusAreaId">
                    <input type="hidden" id="childEditObjectiveId">
                    <input type="hidden" id="childEditPracticeId">

                    <div class="mb-3" id="childEditField1InputWrap">
                        <label for="childEditField1" class="form-label fw-semibold" id="childEditField1Label">Field 1</label>
                        <input type="text" id="childEditField1" class="form-control">
                    </div>

                    <div class="mb-3" id="childEditField1SelectWrap" style="display:none;">
                        <label for="childEditField1Select" class="form-label fw-semibold" id="childEditField1SelectLabel">RACI</label>
                        <select id="childEditField1Select" class="form-select">
                            <option value="-">-</option>
                            <option value="R">R</option>
                            <option value="A">A</option>
                            <option value="C">C</option>
                            <option value="I">I</option>
                        </select>
                    </div>

                    <div class="mb-3" id="childEditField2Wrap">
                        <label for="childEditField2" class="form-label fw-semibold" id="childEditField2Label">Field 2</label>
                        <textarea id="childEditField2" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="small text-muted" id="childEditHelpText"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary btn-sm" id="childEditSaveBtn">
                        <i class="fas fa-save me-1"></i>Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script>
        window.deleteActivity = async function(activityId) {
            if (!confirm('Apakah anda yakin ingin menghapus activity ini?')) return;
            try {
                const res = await fetch(`{{ url('/objectives/activities') }}/${activityId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                if (!res.ok) throw new Error(await extractErrorMessage(res));
                queueFlashNotif('Activity berhasil dihapus.');
                location.reload();
            } catch (e) {
                showNotif(e.message, 'danger');
            }
        };
    </script>
@endsection
