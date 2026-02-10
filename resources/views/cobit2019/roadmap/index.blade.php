@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
    @php
        $assessments = $assessments ?? collect();
        $evals = $evals ?? collect();
        $selectedAssessmentId = $selectedAssessmentId ?? null;
    @endphp

    {{-- Hero Header (Simple) --}}
    <div class="card shadow-sm mb-4" style="border:none;box-shadow:0 18px 36px rgba(14,33,70,0.12);">
        <div class="card-body" style="background:#fff;border-radius:12px;">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="text-uppercase text-muted" style="letter-spacing:0.08em;font-size:0.75rem;">Roadmap Capability</div>
                    <div class="fw-bold" style="font-size:1.25rem;color:#0f2b5c;">Set target levels and ratings across years</div>
                    <div class="small text-muted mt-1">Total Design Objectives: {{ $objectives->count() }}</div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('target-capability.edit') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                        <i class="fas fa-bullseye me-2"></i>Target Capability
                    </a>
                    <a href="{{ url('/assessment-eval/target-maturity') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                        <i class="fas fa-chart-bar me-2"></i>Target Maturity
                    </a>
                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" disabled>
                        <i class="fas fa-route me-2"></i>Roadmap
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <form id="roadmapForm" action="{{ route('roadmap.store') }}" method="POST">
                @csrf
                <div class="p-3 d-flex flex-wrap justify-content-between align-items-center gap-2 bg-light border-bottom">
                    <div class="small text-muted">Kelola target capability per tahun dengan sumber input yang fleksibel.</div>
                    <div class="d-flex flex-wrap justify-content-end gap-2">
                        <a href="{{ route('roadmap.report') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                            <i class="fas fa-file-alt me-2"></i>View Report
                        </a>
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addYearModal">
                            <i class="fas fa-plus me-2"></i>Add Year
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3" id="deleteYearBtn">
                            <i class="fas fa-trash me-2"></i>Delete Year
                        </button>
                        <button type="submit" class="btn btn-success btn-sm rounded-pill px-4">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                    </div>
                </div>

                <div class="p-3 border-bottom bg-white">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Tahun Target</label>
                            <select id="targetYearSelect" class="form-select form-select-sm">
                                @foreach($years as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Sumber Target</label>
                            <select id="targetSourceSelect" class="form-select form-select-sm">
                                <option value="step4">1. Design Factor / Step 4</option>
                                <option value="scope">2. Scope Assessment</option>
                                <option value="bumn">3. BUMN</option>
                                <option value="manual">4. Manual Input</option>
                            </select>
                        </div>
                        <div class="col-md-3 source-step4">
                            <label class="form-label small mb-1">Assessment (Step 4)</label>
                            <select id="step4AssessmentSelect" class="form-select form-select-sm">
                                <option value="">Pilih assessment</option>
                                @foreach($assessments as $assess)
                                    <option value="{{ $assess->assessment_id }}"
                                        {{ (string) $selectedAssessmentId === (string) $assess->assessment_id ? 'selected' : '' }}>
                                        {{ $assess->kode_assessment ?? ('ID ' . $assess->assessment_id) }}
                                        {{ $assess->instansi ? ' - ' . $assess->instansi : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 source-scope d-none">
                            <label class="form-label small mb-1">Assessment Eval</label>
                            <select id="evalSelect" class="form-select form-select-sm">
                                <option value="">Pilih eval</option>
                                @foreach($evals as $eval)
                                    <option value="{{ $eval->eval_id }}">
                                        Eval {{ $eval->eval_id }}{{ $eval->tahun ? ' - ' . $eval->tahun : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 source-scope d-none">
                            <label class="form-label small mb-1">Scope</label>
                            <select id="scopeSelect" class="form-select form-select-sm">
                                <option value="">Pilih scope</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="applySourceBtn">
                                Terapkan
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm ms-2" id="clearScopeFilterBtn">
                                Tampilkan Semua
                            </button>
                        </div>
                    </div>
                    <div class="small text-muted mt-2" id="sourceInfo"></div>
                </div>

                <div class="table-responsive table-wrapper-scroll-y">
                    <table class="table table-sm table-bordered table-striped table-hover roadmap-table align-middle mb-0" id="roadmap-table">
                        <thead class="text-center">
                            <tr>
                                <th rowspan="2" class="sticky-col" style="width: 100px;">GAMO</th>
                                <th rowspan="2" class="sticky-col" style="left: 100px; min-width: 250px;">Description</th>
                                @foreach($years as $year)
                                    <th colspan="2" class="year-header" data-year="{{ $year }}">
                                        {{ $year }}
                                    </th>
                                @endforeach
                            </tr>
                            <tr>
                                @foreach($years as $year)
                                    <th style="width: 80px;">Level</th>
                                    <th style="width: 100px;">Rating</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($objectives as $idx => $obj)
                            <tr data-objective-id="{{ $obj->objective_id }}">
                                <td class="sticky-col text-center font-weight-bold bg-white">{{ $obj->objective_id }}</td>
                                <td class="sticky-col bg-white small text-muted" style="left: 100px;">
                                    {{ $obj->objective }}
                                </td>
                                @foreach($years as $year)
                                    @php
                                        $data = $mappedRoadmaps[$obj->objective_id][$year] ?? ['level' => '', 'rating' => ''];
                                    @endphp
                                    <td class="p-0">
                                        <input type="hidden" name="roadmap[{{ $idx }}][{{ $year }}][objective_id]" value="{{ $obj->objective_id }}">
                                        <input type="hidden" name="roadmap[{{ $idx }}][{{ $year }}][year]" value="{{ $year }}">
                                        <input type="number" 
                                               name="roadmap[{{ $idx }}][{{ $year }}][level]" 
                                               class="form-control form-control-sm text-center border-0 bg-transparent level-input" 
                                               data-year="{{ $year }}"
                                               placeholder="-"
                                               value="{{ $data['level'] }}"
                                               min="0" max="5">
                                    </td>
                                    <td class="p-0">
                                        <select name="roadmap[{{ $idx }}][{{ $year }}][rating]" 
                                                class="form-select form-select-sm text-center border-0 bg-transparent rating-select"
                                                data-initial="{{ $data['rating'] }}">
                                            <option value="">-</option>
                                        </select>
                                    </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="deleteYearForm" action="{{ route('roadmap.delete-year') }}" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="year" id="deleteYearInput">
</form>

{{-- Add Year Modal --}}
<div class="modal fade" id="addYearModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Add New Year</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Enter Year</label>
                    <input type="number" id="newYearInput" class="form-control" value="{{ date('Y') + 1 }}">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmAddYear">Add Column</button>
            </div>
        </div>
    </div>
</div>

<style>
    .table-wrapper-scroll-y {
        max-height: 75vh;
        overflow: auto;
    }

    .roadmap-table {
        border-collapse: separate !important;
        border-spacing: 0;
    }

    .roadmap-table thead th {
        position: sticky;
        top: 0;
        z-index: 40 !important;
        background-color: #f8f9fa !important;
        border: 1px solid #dee2e6 !important;
    }

    .roadmap-table thead tr:nth-child(2) th {
        top: 33px;
        z-index: 40 !important;
    }

    .sticky-col {
        position: sticky;
        left: 0;
        z-index: 20;
        background-color: #fff !important;
        border-right: 1px solid #dee2e6 !important;
    }

    thead th.sticky-col {
        z-index: 50 !important;
        top: 0;
    }
    
    thead tr:nth-child(2) th.sticky-col {
        top: 33px;
    }

    .form-control-sm, .form-select-sm {
        font-size: 11px;
        padding: 0.2rem;
        border-radius: 0;
    }

    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
        -webkit-appearance: none; 
        margin: 0; 
    }

    .year-header {
        font-weight: bold;
    }

    .scope-hidden {
        display: none;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add Year logic
    document.getElementById('confirmAddYear').addEventListener('click', function() {
        const year = document.getElementById('newYearInput').value;
        if(!year) return;

        const existingYears = Array.from(document.querySelectorAll('.year-header')).map(th => th.dataset.year);
        if(existingYears.includes(year)) {
            alert('Year already exists');
            return;
        }

        const url = new URL(window.location.href);
        url.searchParams.set('add_year', year);
        window.location.href = url.toString();
    });

    // Dynamic Rating Logic
    function updateRatings(levelInput) {
        const level = parseInt(levelInput.value);
        const ratingSelect = levelInput.closest('tr').querySelectorAll('.rating-select')[Array.from(levelInput.closest('tr').querySelectorAll('.level-input')).indexOf(levelInput)];
        
        const initialValue = ratingSelect.dataset.initial || '';
        const currentValue = ratingSelect.value || initialValue;

        // Clear existing options
        ratingSelect.innerHTML = '<option value="">-</option>';

        if (!isNaN(level)) {
            const options = [];
            options.push(`${level}L`);
            options.push(`${level}F`);
            if (level < 5) {
                options.push(`${level + 1}P`);
            }

            options.forEach(opt => {
                const option = document.createElement('option');
                option.value = opt;
                option.textContent = opt;
                if (opt === currentValue) {
                    option.selected = true;
                }
                ratingSelect.appendChild(option);
            });
        }
    }

    document.querySelectorAll('.level-input').forEach(input => {
        input.addEventListener('change', function() {
            updateRatings(this);
        });
        // Initial load
        updateRatings(input);
    });

    // Target Source Logic
    const step4Url = "{{ route('roadmap.step4-scope') }}";
    const scopeUrl = "{{ route('roadmap.scopes') }}";
    const applyBtn = document.getElementById('applySourceBtn');
    const clearScopeBtn = document.getElementById('clearScopeFilterBtn');
    const infoEl = document.getElementById('sourceInfo');
    const sourceSelect = document.getElementById('targetSourceSelect');
    const step4Wrap = document.querySelectorAll('.source-step4');
    const scopeWrap = document.querySelectorAll('.source-scope');

    const BUMN_GAMOS = [
        'EDM01', 'EDM02', 'APO01', 'APO02', 'APO03', 'APO05', 'APO06', 'APO09',
        'APO10', 'APO12', 'APO13', 'APO14', 'BAI02', 'BAI03', 'BAI04', 'BAI06',
        'BAI07', 'BAI09', 'BAI11', 'DSS01', 'DSS02', 'DSS04', 'DSS05', 'MEA01'
    ];

    const scopeCache = {};

    function toggleSourceFields() {
        const source = sourceSelect.value;
        step4Wrap.forEach(el => el.classList.toggle('d-none', source !== 'step4'));
        scopeWrap.forEach(el => el.classList.toggle('d-none', source !== 'scope'));
    }

    async function loadScopes(evalId) {
        if (!evalId) return;
        if (scopeCache[evalId]) return scopeCache[evalId];
        const res = await fetch(`${scopeUrl}?eval_id=${evalId}`);
        const data = await res.json();
        scopeCache[evalId] = data.scopes || [];
        return scopeCache[evalId];
    }

    function setScopeFilter(objectiveIds, year, clearNonScope) {
        const scopeSet = new Set(objectiveIds || []);
        document.querySelectorAll('#roadmap-table tbody tr').forEach(row => {
            const objId = row.dataset.objectiveId;
            const inScope = scopeSet.has(objId);
            row.classList.toggle('scope-hidden', !inScope);
            if (!inScope && clearNonScope && year) {
                const input = row.querySelector(`.level-input[data-year="${year}"]`);
                if (input) {
                    input.value = '';
                    input.dispatchEvent(new Event('change'));
                }
            }
        });
    }

    function clearScopeFilter() {
        document.querySelectorAll('#roadmap-table tbody tr').forEach(row => {
            row.classList.remove('scope-hidden');
        });
    }

    function setLevelForObjectives(objectiveLevelMap, year) {
        Object.entries(objectiveLevelMap || {}).forEach(([objectiveId, level]) => {
            const row = document.querySelector(`#roadmap-table tbody tr[data-objective-id="${objectiveId}"]`);
            if (!row) return;
            const input = row.querySelector(`.level-input[data-year="${year}"]`);
            if (!input) return;
            input.value = level || '';
            input.dispatchEvent(new Event('change'));
        });
    }

    async function applySource() {
        const source = sourceSelect.value;
        const year = document.getElementById('targetYearSelect')?.value;
        if (!year) {
            alert('Pilih tahun terlebih dahulu.');
            return;
        }

        if (infoEl) infoEl.textContent = '';

        if (source === 'step4') {
            const assessmentId = document.getElementById('step4AssessmentSelect')?.value;
            if (!assessmentId) {
                alert('Pilih assessment Step 4.');
                return;
            }
            if (infoEl) infoEl.textContent = 'Memuat Step 4...';
            const res = await fetch(`${step4Url}?assessment_id=${assessmentId}`);
            const data = await res.json();
            const objectives = data.objectives || [];
            if (!objectives.length) {
                if (infoEl) infoEl.textContent = 'Scope Step 4 kosong.';
                return;
            }
            const ids = objectives.map(o => o.objective_id);
            setScopeFilter(ids, year, true);
            const levelMap = {};
            objectives.forEach(o => levelMap[o.objective_id] = o.agreed_level || '');
            setLevelForObjectives(levelMap, year);
            if (infoEl) infoEl.textContent = `Step 4 diterapkan: ${ids.length} GAMO untuk tahun ${year}.`;
            return;
        }

        if (source === 'scope') {
            const evalId = document.getElementById('evalSelect')?.value;
            const scopeId = document.getElementById('scopeSelect')?.value;
            if (!evalId || !scopeId) {
                alert('Pilih eval dan scope.');
                return;
            }
            const scopes = await loadScopes(evalId);
            const scope = scopes.find(s => String(s.id) === String(scopeId));
            const objectives = scope?.objectives || [];
            if (!objectives.length) {
                if (infoEl) infoEl.textContent = 'Scope kosong.';
                return;
            }
            setScopeFilter(objectives, year, true);
            if (infoEl) infoEl.textContent = `Scope assessment diterapkan: ${objectives.length} GAMO.`;
            return;
        }

        if (source === 'bumn') {
            setScopeFilter(BUMN_GAMOS, year, true);
            const levelMap = {};
            BUMN_GAMOS.forEach(id => levelMap[id] = 3);
            setLevelForObjectives(levelMap, year);
            if (infoEl) infoEl.textContent = `BUMN diterapkan: ${BUMN_GAMOS.length} GAMO level 3 untuk tahun ${year}.`;
            return;
        }

        // manual
        clearScopeFilter();
        if (infoEl) infoEl.textContent = 'Manual input aktif.';
    }

    sourceSelect?.addEventListener('change', () => {
        toggleSourceFields();
    });

    document.getElementById('evalSelect')?.addEventListener('change', async (e) => {
        const evalId = e.target.value;
        const scopes = await loadScopes(evalId);
        const scopeSelect = document.getElementById('scopeSelect');
        if (!scopeSelect) return;
        scopeSelect.innerHTML = '<option value="">Pilih scope</option>';
        scopes.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = s.name || `Scope ${s.id}`;
            scopeSelect.appendChild(opt);
        });
    });

    applyBtn?.addEventListener('click', applySource);
    clearScopeBtn?.addEventListener('click', clearScopeFilter);
    document.getElementById('deleteYearBtn')?.addEventListener('click', () => {
        const year = document.getElementById('targetYearSelect')?.value;
        if (!year) {
            alert('Pilih tahun terlebih dahulu.');
            return;
        }
        if (confirm(`Hapus semua roadmap untuk tahun ${year}?`)) {
            const input = document.getElementById('deleteYearInput');
            if (input) input.value = year;
            document.getElementById('deleteYearForm')?.submit();
        }
    });
    toggleSourceFields();
});
</script>
@endsection
