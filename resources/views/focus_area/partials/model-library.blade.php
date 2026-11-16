<div id="wizardStep2Container" class="wizard-container" {{ $initialStep === 2 ? '' : 'hidden' }}>
    <!-- Control bar with Back Button and Active Model Badge -->
    <div class="step2-toolbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-outline-secondary btn-sm px-3" onclick="goToWizardStep(1)">
                Ganti Framework
            </button>
            <span id="selectedFrameworkBadge" class="small text-muted">
                COBIT {{ $selectedVersion }}
            </span>
        </div>
        <div id="step2ActionWrap">
            @if(auth()->check() && auth()->user()->can('design-factors.input'))
                <button class="btn btn-outline-primary btn-sm fw-bold rounded-pill px-3" id="step2AddFaBtn" onclick="openCreateModal('2019')">
                    Tambah Focus Area
                </button>
            @endif
        </div>
    </div>

    <!-- SUB VIEW: COBIT 2019 (PILIH FOCUS AREA) -->
    <div id="viewCobit2019" class="version-view" @if($selectedVersion !== '2019') hidden @endif>

        <div class="row g-3">
            @forelse($f2019 as $fa)
                @php
                    $objRoute = $modelLinks[$fa->id];
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
                                <span>{{ $fa->objectives_count }} objectives</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                                <span class="text-primary fw-bold small">
                                    Buka Kamus Komponen
                                </span>
                                @if(auth()->check() && auth()->user()->can('design-factors.input'))
                                    <div class="fa-actions" onclick="event.preventDefault(); event.stopPropagation();">
                                        <button class="btn btn-outline-secondary btn-sm" onclick="openEditModal({{ $fa->id }}, '{{ addslashes($fa->code) }}', '{{ addslashes($fa->name) }}', `{{ addslashes($fa->description ?? '') }}`, '{{ $fa->version ?? '2019' }}')">
                                            Edit
                                        </button>
                                        @if($fa->id != 1)
                                            <button class="btn btn-outline-danger btn-sm" onclick="deleteFocusArea({{ $fa->id }}, '{{ addslashes($fa->name) }}')">
                                                Hapus
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
                        <span>Tambah Focus Area Baru</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- SUB VIEW: COBIT 5 -->
    <div id="viewCobit5" class="version-view" @if($selectedVersion !== '5') hidden @endif>

        @if($cobit5Model)
            @php
                $cobit5ObjRoute = $modelLinks[$cobit5Model->id];
            @endphp
            <div class="direct-model-box">
                <div class="direct-model-header">
                    <div>
                        <h3 class="fw-bold mb-1">{{ $cobit5Model->name }}</h3>
                        @if(filled($cobit5Model->description))<p class="text-muted mb-0">{{ $cobit5Model->description }}</p>@endif
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ $cobit5ObjRoute }}" class="btn btn-success fw-bold px-4">
                            Buka Kamus Komponen
                        </a>
                        <a href="{{ route('focus-areas.show', $cobit5Model->id) }}" class="btn btn-outline-secondary fw-bold">
                            Kelola Model
                        </a>
                    </div>
                </div>

                <dl class="direct-model-stats">
                    <div class="stat-chip">
                        <dt>Proses</dt>
                        <dd class="model-stat-value">{{ $cobit5Model->objectives_count }}</dd>
                    </div>
                    <div class="stat-chip">
                        <dt>Domain</dt>
                        <dd class="model-stat-value">5</dd>
                        <dd class="small text-muted mb-0">EDM, APO, BAI, DSS, MEA</dd>
                    </div>
                </dl>

                @if($cobit5Model->objectives_count == 0)
                    <div class="alert alert-warning d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            Model COBIT 5 ini belum memiliki data proses. Anda dapat men-generate 37 proses secara otomatis.
                        </div>
                        <button class="btn btn-sm btn-primary fw-bold" onclick="generateCobit5({{ $cobit5Model->id }})">
                            Generate 37 Proses COBIT 5
                        </button>
                    </div>
                @endif
            </div>
        @else
            <div class="card p-5 text-center shadow-sm">
                <h4>Model COBIT 5 belum tersedia di database</h4>
                <button class="btn btn-success fw-bold mx-auto mt-2" onclick="openCreateModal('5', 'COBIT5', 'COBIT 5', 'COBIT 5 Framework - 37 Governance and Management Processes')">
                    Buat Model COBIT 5 Sekarang
                </button>
            </div>
        @endif
    </div>

    <!-- SUB VIEW: COBIT 4.1 -->
    <div id="viewCobit4" class="version-view" @if($selectedVersion !== '4.1') hidden @endif>

        @if($cobit4Model)
            @php
                $cobit4ObjRoute = $modelLinks[$cobit4Model->id];
            @endphp
            <div class="direct-model-box">
                <div class="direct-model-header">
                    <div>
                        <h3 class="fw-bold mb-1">{{ $cobit4Model->name }}</h3>
                        @if(filled($cobit4Model->description))<p class="text-muted mb-0">{{ $cobit4Model->description }}</p>@endif
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        @if($cobit4Model->objectives_count > 0)
                            <a href="{{ $cobit4ObjRoute }}" class="btn btn-warning fw-bold text-dark px-4">
                                Buka Kamus Komponen
                            </a>
                        @endif
                        <a href="{{ route('focus-areas.show', $cobit4Model->id) }}" class="btn btn-outline-secondary fw-bold">
                            Kelola Model
                        </a>
                    </div>
                </div>

                <dl class="direct-model-stats">
                    <div class="stat-chip">
                        <dt>Proses</dt>
                        <dd class="model-stat-value">{{ $cobit4Model->objectives_count }}</dd>
                    </div>
                    <div class="stat-chip">
                        <dt>Domain</dt>
                        <dd class="model-stat-value">4</dd>
                        <dd class="small text-muted mb-0">PO, AI, DS, ME</dd>
                    </div>
                </dl>

                @if($cobit4Model->objectives_count == 0)
                    <div class="alert alert-warning d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            Model COBIT 4.1 ini belum memiliki data proses. Anda dapat men-generate 34 proses COBIT 4.1 secara otomatis dari baseline.
                        </div>
                        <button class="btn btn-sm btn-warning text-dark fw-bold" onclick="generateCobit4({{ $cobit4Model->id }})">
                            Generate Template COBIT 4.1
                        </button>
                    </div>
                @endif
            </div>
        @else
            <div class="card p-5 text-center shadow-sm">
                <h4>Model COBIT 4.1 belum tersedia di database</h4>
                <button class="btn btn-warning fw-bold text-dark mx-auto mt-2" onclick="openCreateModal('4.1', 'COBIT41', 'COBIT 4.1', 'COBIT 4.1 Framework - IT Governance & Control Objectives')">
                    Buat Model COBIT 4.1 Sekarang
                </button>
            </div>
        @endif
    </div>
</div>
