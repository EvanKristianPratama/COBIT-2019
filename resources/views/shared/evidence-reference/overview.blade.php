    @php
        $overviewText = static fn ($value) => preg_replace('/^[\s"“”]+|[\s"“”]+$/u', '', (string) ($value ?? ''));
        $isMasterReferenceView = $referenceReturnContext === 'master';
        $overviewSelectId = $isMasterReferenceView ? 'referenceGamoSelect' : 'overviewObjective';
    @endphp
    <div class="card border-0 shadow-sm mb-3 overflow-hidden">
        <div class="card-header border-0 text-white py-3" style="background: linear-gradient(135deg, #081a3d, #0f2b5c);">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h4 class="mb-1">{{ $overviewTitle }}</h4>
                    <div class="small text-white-50">{{ $referenceReturnContext !== 'component' ? 'Ringkasan GAMO · ' : '' }}{{ $overviewText($focusArea->name) }} · COBIT {{ $focusArea->version }}</div>
                </div>
                <a href="{{ $overviewBackUrl }}" class="btn btn-sm btn-outline-light">@if(!$isMasterReferenceView)<i class="fas fa-arrow-left me-2"></i>@endif{{ $overviewBackLabel }}</a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route($overviewRoute) }}" class="row g-2 align-items-end" id="{{ $isMasterReferenceView ? 'referenceGamoForm' : 'overviewGamoForm' }}">
                <input type="hidden" name="focus_area" value="{{ $focusAreaId }}">
                @if($dictionaryOrganizationId)
                    <input type="hidden" name="organization_id" value="{{ $dictionaryOrganizationId }}">
                @endif
                @if($isMasterReferenceView)
                    <input type="hidden" name="search" value="{{ $searchTerm }}">
                    <input type="hidden" name="grup" value="{{ $selectedGroup }}">
                    <input type="hidden" name="tipe" value="{{ $selectedType }}">
                @endif
                <div class="col-12 col-lg-9">
                    <label for="{{ $overviewSelectId }}" class="form-label small fw-semibold">Pilih GAMO</label>
                    <select id="{{ $overviewSelectId }}" name="{{ $isMasterReferenceView ? 'reference_gamo' : 'objective_id' }}" class="form-select form-select-sm">
                        @foreach($objectives as $option)
                            <option value="{{ $option->objective_id }}" @selected($objective->objective_id === $option->objective_id)>{{ $overviewText($option->objective_id) }} — {{ $overviewText($option->objective) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto"><button class="btn btn-sm btn-primary px-3">Tampilkan</button></div>
                @if($referenceReturnContext === 'component')
                    @can('assessments.view')
                        <div class="col-auto"><a href="{{ route('master-evidence.index') }}" class="btn btn-sm btn-outline-primary">Master Evidence <i class="fas fa-arrow-right ms-1"></i></a></div>
                    @endcan
                @endif
            </form>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger py-2">{{ $errors->first() }}</div>@endif

    <div class="card border-0 shadow-sm mb-3">
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0 overview-table">
                <thead class="overview-component-head">
                    <tr>
                        <th style="width: 19%;">Governance &amp; Management Objective</th>
                        <th style="width: 25%;">Management Practice</th>
                        <th style="width: 32%;">Contoh Metrik</th>
                        <th style="width: 24%;">Keluaran / Referensi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($objective->practices->sortBy('practice_id') as $practice)
                        <tr>
                            @if($loop->first)
                                <td rowspan="{{ $objective->practices->count() }}" class="align-top"><strong class="text-primary">{{ $overviewText($objective->objective_id) }}</strong><div class="mt-1">{{ $overviewText($objective->objective) }}</div></td>
                            @endif
                            <td class="align-top"><strong>{{ $overviewText($practice->practice_id) }}</strong><div>{{ $overviewText($practice->practice_name) }}</div></td>
                            <td class="align-top">
                                <ul class="mb-0 ps-3">
                                    @forelse($practice->practicemetr as $metric)<li>{{ $overviewText($metric->description) }}</li>@empty<li class="text-muted">Belum tersedia.</li>@endforelse
                                </ul>
                            </td>
                            @if($loop->first)
                                <td rowspan="{{ $objective->practices->count() }}" class="align-top">
                                    <ul class="mb-0 ps-3">
                                        @forelse($workProducts as $product)<li>{{ $overviewText($product->description) }}</li>@empty<li class="text-muted">Belum tersedia pada komponen.</li>@endforelse
                                    </ul>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td><strong>{{ $overviewText($objective->objective_id) }}</strong> {{ $overviewText($objective->objective) }}</td><td colspan="3" class="text-muted">Praktik belum tersedia.</td></tr>
                    @endforelse
                    <tr><th class="overview-label">Deskripsi Objective</th><td colspan="3" class="overview-text">{{ $overviewText($objective->objective_description) ?: '-' }}</td></tr>
                    <tr><th class="overview-label">Tujuan Objective</th><td colspan="3" class="overview-text">{{ $overviewText($objective->objective_purpose) ?: '-' }}</td></tr>
                    @forelse($objective->policies as $policy)
                        <tr>
                            @if($loop->first)<th rowspan="{{ $objective->policies->count() }}" class="overview-label">Contoh Kebijakan / Prosedur</th>@endif
                            <td class="fw-semibold">{{ $overviewText($policy->policy) }}</td><td colspan="2" class="overview-text">{{ $overviewText($policy->description) ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><th class="overview-label">Contoh Kebijakan / Prosedur</th><td colspan="3" class="text-muted">Belum tersedia.</td></tr>
                    @endforelse
                    <tr>
                        <th class="overview-label">Layanan, Infrastruktur &amp; Aplikasi</th>
                        <td colspan="3"><ul class="mb-0 ps-3">@forelse($objective->s_i_a as $service)<li>{{ $overviewText($service->description) }}</li>@empty<li class="text-muted">Belum tersedia.</li>@endforelse</ul></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
            <div><h5 class="mb-1 text-primary">Referensi Kebutuhan Dokumen · {{ $overviewText($objective->objective_id) }}</h5><span class="small text-muted">Contoh dokumen pendukung dalam bahasa Indonesia yang dapat disesuaikan organisasi.</span></div>
            @if($catalogReady)
                @can('design-factors.input')<button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newEvidenceRefModal"><i class="fas fa-plus me-1"></i>Tambah Ref</button>@endcan
            @endif
        </div>
        @include('shared.evidence-reference.requirements-table', [
            'compact' => false,
            'editable' => auth()->user()->can('design-factors.input'),
            'selectable' => $isMasterReferenceView && $canManageMasterEvidence,
        ])
        @if($isMasterReferenceView && $catalogReady && $canManageMasterEvidence)
            <div class="card-footer bg-white d-flex flex-wrap align-items-center justify-content-between gap-2 py-2">
                <div class="overflow-hidden">
                    <strong class="small" id="selectedEvidenceReferenceCount">0 ref dipilih</strong>
                    <div class="small text-muted text-truncate" id="selectedEvidenceReferenceSummary">Pilih ref untuk evidence baru sebelum menekan Tambah Evidence.</div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="clearEvidenceReferenceSelection">Kosongkan Pilihan</button>
            </div>
        @endif
    </div>

    @if($catalogReady)
        @can('design-factors.input')
            @include('shared.evidence-reference.modal', ['reference' => null, 'modalId' => 'newEvidenceRefModal'])
            @foreach($references as $reference)
                @include('shared.evidence-reference.modal', ['reference' => $reference, 'modalId' => 'editEvidenceRef'.$reference->id])
            @endforeach
        @endcan
    @endif

    <style>
        .overview-table { font-size: .85rem; }
        .overview-table th, .overview-table td { padding: .55rem .7rem; }
        .overview-table .overview-component-head th { background: #12558c; color: #fff; }
        .overview-table .overview-label { background: #102b50; color: #fff; vertical-align: top; }
        .overview-text { white-space: pre-line; }
    </style>
