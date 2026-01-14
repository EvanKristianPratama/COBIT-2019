@extends('layouts.app')

@section('content')


<div class="container-fluid p-4">
    {{-- Header --}}
    <div class="card shadow-sm mb-4 hero-card" style="border:none;box-shadow:0 22px 45px rgba(14,33,70,0.15);">
        <div class="card-header hero-header py-4" style="background:linear-gradient(135deg,#081a3d,#0f2b5c);color:#fff;border:none;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="hero-title" style="font-size:1.5rem;font-weight:700;letter-spacing:0.04em;">All Assessments Report</div>
                    <div class="hero-subtitle" style="font-size:1.05rem;font-weight:400;margin-top:0.25rem;color:rgba(255,255,255,0.85);">
                        Comparative view across years and scopes
                    </div>
                </div>
                <div>
                     <div class="btn-group me-2" role="group">
                    <a href="{{ route('assessment-eval.report.spiderweb') }}" class="btn btn-outline-light btn-sm px-3 me-2">
                        <i class="fas fa-spider me-2"></i>Spiderweb View
                    </a>

                    <button class="btn btn-danger btn-sm px-3 me-2" id="btn-export-pdf">
                         <i class="fas fa-file-pdf me-2"></i>Export PDF
                    </button>

                    <a href="{{ route('assessment-eval.list') }}" class="btn btn-light btn-sm rounded-pill px-3">
                        <i class="fas fa-list me-2"></i>Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Filter Sidebar --}}
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold text-primary"><i class="fas fa-filter me-2"></i>Select Scopes</h6>
                </div>
                <div class="card-body overflow-auto" style="max-height: 80vh;">
                    <div class="mb-3">
                        <input type="text" class="form-control form-control-sm" id="scope-search" placeholder="Search scopes...">
                    </div>
                    
                    <div id="scope-filters">
                        {{-- JS will populate this --}}
                    </div>
                </div>
                <div class="card-footer bg-white">
                     <div class="mb-2 text-start">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="show-max-level">
                            <label class="form-check-label small" for="show-max-level">
                                Show Max Level
                            </label>
                        </div>
                     </div>
                     <button class="btn btn-sm btn-outline-primary w-100 mb-1" id="btn-select-all">Select All</button>
                     <button class="btn btn-sm btn-outline-secondary w-100" id="btn-deselect-all">Deselect All</button>
                </div>
            </div>
        </div>

        {{-- Report Content --}}
        <div class="col-md-9 mb-4">
            {{-- VIEW: TABLE --}}
            <div class="card shadow-sm border-0 view-section" id="report-result-card">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-primary">All Maturity Report</h5>
                    <div>
                         <span class="badge bg-info text-dark" id="selected-count-badge">0 selected</span>
                    </div>
                </div>
                <div class="card-body p-0" id="report-container">
                    <div class="table-responsive table-wrapper-scroll-y my-custom-scrollbar">
                        <table class="table table-sm table-bordered recap-table align-middle mb-0" id="recap-table">
                            <thead>
                                <tr id="table-header-row">
                                    <th style="width:50px;" class="text-center sticky-col">No</th>
                                    <th style="width:90px;" class=" sticky-col">GAMO</th>
                                    <th class="sticky-col">Process Name</th>
                                    {{-- Dynamic Headers --}}
                                </tr>
                            </thead>
                            <tbody id="recap-table-body">
                                {{-- JS will populate this --}}
                            </tbody>
                            <tfoot id="recap-table-footer">
                                {{-- JS will populate this --}}
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
                </div>
            </div>


        </div>
    </div>
</div>

<style>
    /* Scrollable Table Container */
    .table-wrapper-scroll-y {
        max-height: 80vh;
        overflow-y: auto;
        overflow-x: auto;
        position: relative; /* Context for sticky */
    }

    /* Sticky first 3 columns for horizontal scroll */
    .sticky-col {
        position: sticky;
        left: 0;
        background-color: #fff !important;
        /* Default z-index for body sticky cols */
        z-index: 10; 
    }
    
    /* Specific offsets for the 3 columns */
    th.sticky-col:nth-child(1), td.sticky-col:nth-child(1) { left: 0px; width: 50px; }
    th.sticky-col:nth-child(2), td.sticky-col:nth-child(2) { left: 50px; width: 90px; }
    th.sticky-col:nth-child(3), td.sticky-col:nth-child(3) { left: 140px; }
    
    /* Sticky Header */
    thead th { 
        position: sticky; 
        top: 0; 
        z-index: 20; /* Higher than body sticky cols */
        background-color: #f8f9fa !important; 
        box-shadow: 0 2px 2px -1px rgba(0,0,0,0.1); /* Subtle shadow for header */
    }

    /* Top Left Intersection (Header + Sticky Col) needs highest Z */
    thead th.sticky-col {
        z-index: 30 !important;
        background-color: #f0f0f0 !important; /* Match PDF Header Gray */
    }
    
    /* ANALOG STYLE OVERRIDES */
    #recap-table, #recap-table th, #recap-table td {
        border: 1px solid #000 !important;
        font-family: sans-serif;
        font-size: 11px; /* Slightly larger than PDF 10px for screen readability */
    }

    #recap-table thead th {
        background-color: #f0f0f0 !important;
        color: #000 !important;
    }

    /* MATCH PDF FOOTER: Only Row 1 (Total GAMO) has gray background */
    #recap-table tfoot tr:first-child td {
        background-color: #f0f0f0 !important;
        color: #000 !important;
    }

    #recap-table tfoot tr:not(:first-child) td {
        background-color: #fff !important;
        color: #000 !important;
    }

    /* Override bootstrap specific classes if needed */
    .bg-primary, .bg-info, .bg-light, .bg-success, .bg-danger {
        background-color: transparent !important;
        color: inherit !important;
    }

    /* Re-apply heat map logic via JS mostly, but footer needs helpers */
    table#recap-table tfoot tr td.gap-pos { color: #008000 !important; font-weight: bold; } /* Pure Green */
    table#recap-table tfoot tr td.gap-neg { color: #ff0000 !important; font-weight: bold; } /* Pure Red */
    
    /* Sticky overrides for analog look */
    .sticky-col {
         border-right: 1px solid #000 !important; 
    }
    
    /* Footer Sticky Columns (optional, keeps left cols visible in footer) */
    /* Footer Sticky Bottom Implementation */
    tfoot tr { 
        height: 40px; /* Essential for accurate stacking calculation */
    }
    
    tfoot td {
        position: sticky;
        z-index: 25; /* Layer above body content */
        background-color: #f8f9fa !important;
        vertical-align: middle;
        /* box-shadow: 0 -1px 0 #dee2e6;  Optional divider */
    }

    /* Stack Strategy: Bottom-Up Stacking for 4 known rows */
    /* Row 4: Gap Analysis */
    tfoot tr:nth-last-child(1) td { 
        bottom: 0px; 
        border-bottom: 2px solid #dee2e6;
    }
    
    /* Row 3: Target */
    tfoot tr:nth-last-child(2) td { 
        bottom: 40px; 
    }
    
    /* Row 2: Maturity Score */
    tfoot tr:nth-last-child(3) td { 
        bottom: 80px; 
    }
    
    /* Row 1: Total Selected */
    tfoot tr:nth-last-child(4) td { 
        bottom: 120px; 
        border-top: 4px solid #fff; /* Visual separation from body */
        box-shadow: 0 -2px 5px rgba(0,0,0,0.1); /* Shadow throwing upwards */
    }

    /* Sticky Columns (Left Horizontal Scroll) within Sticky Footer */
    /* Needs highest Z-index to float over everything */
    tfoot td.sticky-col {
        z-index: 35 !important; 
        left: 0;
        background-color: #f8f9fa !important;
    }
    
    /* Specific offsets for footer sticky cols */
    tfoot td.sticky-col:nth-child(1) { left: 0px; }
    tfoot td.sticky-col:nth-child(2) { left: 50px; }
    tfoot td.sticky-col:nth-child(3) { left: 140px; }
</style>

<script>
/**
 * All-Years Assessment Report
 * Refactored for Maintainability & Scalability (KISS, SOLID)
 */
(function() {
    'use strict';

    // ==========================================================================
    // 1. CONFIGURATION & CONSTANTS
    // ==========================================================================
    const CONFIG = {
        MAX_LEVELS: 5,
        TARGET_BUMN_OVERRIDE: 3.00, // Hardcoded target for BUMN scopes
        
        // Static Max Levels Reference per Objective
        MAX_LEVELS_REF: {
            'EDM01': 4, 'EDM02': 5, 'EDM03': 4, 'EDM04': 4, 'EDM05': 4,
            'APO01': 5, 'APO02': 4, 'APO03': 5, 'APO04': 4, 'APO05': 5, 'APO06': 5, 'APO07': 4, 'APO08': 5, 'APO09': 4, 'APO10': 5, 'APO11': 5, 'APO12': 5, 'APO13': 5, 'APO14': 5,
            'BAI01': 5, 'BAI02': 4, 'BAI03': 4, 'BAI04': 5, 'BAI05': 5, 'BAI06': 4, 'BAI07': 5, 'BAI08': 5, 'BAI09': 5, 'BAI10': 4, 'BAI11': 5,
            'DSS01': 5, 'DSS02': 5, 'DSS03': 5, 'DSS04': 4, 'DSS05': 5, 'DSS06': 5,
            'MEA01': 5, 'MEA02': 5, 'MEA03': 5, 'MEA04': 4
        },

        COLORS: {
            // Updated to match PDF exactly
            bg:   ['#ffebee', '#fff3e0', '#fff8e1', '#e8f5e9', '#e3f2fd', '#f3e5f5'],
            text: ['#c62828', '#ef6c00', '#f57f17', '#2e7d32', '#1565c0', '#6a1b9a']
        },
        SELECTORS: {
            scopeSearch: 'scope-search',
            scopeFilters: 'scope-filters',
            btnSelectAll: 'btn-select-all',
            btnDeselectAll: 'btn-deselect-all',
            chkShowMaxLevel: 'show-max-level',
            selectedCountBadge: 'selected-count-badge',
            tableHeader: 'table-header-row',
            tableBody: 'recap-table-body',
            tableBody: 'recap-table-body',
            tableFooter: 'recap-table-footer',
            btnExportPdf: 'btn-export-pdf'
        }
    };

    // ==========================================================================
    // 2. GLOBAL STATE
    // ==========================================================================
    const STATE = {
        selectedScopeIds: new Set(),
        currentView: 'table',
        showMaxLevel: false
    };

    // Data from Server (Considered Read-Only)
    const SERVER_DATA = {
        objectives: @json($objectives),
        scopes: @json($processedData ?? []) 
    };

    // ==========================================================================
    // 3. UTILITIES & BUSINESS LOGIC
    // ==========================================================================
    const Utils = {
        escape: s => (s || '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c])),
        
        fmt: n => (Number(n) || 0).toFixed(2),
        
        getEffectiveTarget(scope) {
            if (scope.scope_name && scope.scope_name.toLowerCase().includes('bumn')) {
                return CONFIG.TARGET_BUMN_OVERRIDE;
            }
            return scope.target_maturity ? Number(scope.target_maturity) : 0;
        },

        calculateAverage(values) {
            const validValues = values.filter(v => v !== null && v !== undefined);
            return validValues.length ? (validValues.reduce((a,b) => a+b, 0) / validValues.length) : 0;
        },

        countValidScores(values) {
            return values.filter(v => v !== null && v !== undefined).length;
        },

        getScoreColor(score) {
            return {
                bg: CONFIG.COLORS.bg[score] || '#f8f9fa',
                text: CONFIG.COLORS.text[score] || '#6c757d'
            };
        }
    };

    // ==========================================================================
    // 4. VIEW RENDERER (Presentation Layer)
    // ==========================================================================
    const View = {
        updateBadge() {
            const el = document.getElementById(CONFIG.SELECTORS.selectedCountBadge);
            if (el) el.textContent = `${STATE.selectedScopeIds.size} selected`;
        },

        renderFilter(scopes) {
            const container = document.getElementById(CONFIG.SELECTORS.scopeFilters);
            if (!container) return;

            // Group by Year
            const grouped = {};
            scopes.forEach(s => {
                if (!grouped[s.year]) grouped[s.year] = [];
                grouped[s.year].push(s);
            });

            // Sort Years Descending
            const years = Object.keys(grouped).sort((a,b) => b - a);

            let html = '';
            years.forEach(year => {
                html += `<div class="scope-group-year fw-bold mt-2 mb-1 text-muted small border-bottom">${year}</div>`;
                grouped[year].forEach(scope => {
                    const checked = STATE.selectedScopeIds.has(scope.scope_id) ? 'checked' : '';
                    html += `
                        <div class="form-check scope-item">
                            <input class="form-check-input scope-checkbox" type="checkbox" value="${scope.scope_id}" id="chk-${scope.scope_id}" ${checked}>
                            <label class="form-check-label small text-truncate d-block" for="chk-${scope.scope_id}" title="${Utils.escape(scope.scope_name)} (by ${Utils.escape(scope.user_name)})">
                                ${Utils.escape(scope.scope_name)}
                            </label>
                        </div>`;
                });
            });
            container.innerHTML = html;
            this.updateBadge();
        },

        renderTableHeader(selectedScopes) {
            const row = document.getElementById(CONFIG.SELECTORS.tableHeader);
            if (!row) return;

            let html = `
                <th style="width:50px;" class="text-center sticky-col">No</th>
                <th style="width:90px;" class=" sticky-col">GAMO</th>
                <th class="sticky-col">Process Name</th>`;
            
            selectedScopes.forEach(s => {
                html += `
                    <th style="width:100px;" class="text-center align-middle">
                        <div class="fw-bold fs-6 mb-1 border-bottom pb-1">${Utils.escape(s.year)}</div>
                        <div class="small fw-normal text-dark text-wrap lh-sm">${Utils.escape(s.scope_name)}</div>
                    </th>`;
            });

            if (STATE.showMaxLevel) {
                 html += `
                    <th style="width:80px;" class="text-center align-middle bg-secondary text-white">
                        <div class="fw-bold fs-6">Max Level</div>
                    </th>`;
            }
            row.innerHTML = html;
        },

        renderTableBody(selectedScopes, objectives) {
            const tbody = document.getElementById(CONFIG.SELECTORS.tableBody);
            if (!tbody) return;

            if (selectedScopes.length === 0) {
                tbody.innerHTML = `<tr><td colspan="20" class="text-center py-5 text-muted">Please select scopes from the left panel to view data.</td></tr>`;
                return;
            }

            let html = '';
            objectives.forEach((obj, index) => {
                html += `
                    <tr>
                        <td class="text-center sticky-col fw-semibold bg-white">${index + 1}</td>
                        <td class="sticky-col bg-white text-wrap fw-bold text-primary" style="font-size:0.85rem;">
                            ${Utils.escape(obj.objective_id)}
                        </td>
                        <td class="sticky-col bg-white" style="min-width:200px;">
                            <span class="small text-muted">${Utils.escape(obj.objective || '')}</span>
                        </td>`;
                
                selectedScopes.forEach(s => {
                    const score = s.maturity_scores[obj.objective_id];
                    if (score === null || score === undefined) {
                        html += `<td class="text-center text-muted bg-light border-start">-</td>`;
                    } else {
                        const colors = Utils.getScoreColor(score);
                        html += `<td class="text-center fw-bold border-start" style="background-color: ${colors.bg}; color: ${colors.text};">${score}</td>`;
                    }
                });

                if (STATE.showMaxLevel) {
                    // Use Static Max Level Reference
                    const refMax = CONFIG.MAX_LEVELS_REF[obj.objective_id] || '-';
                    html += `<td class="text-center fw-bold border-start bg-secondary text-white">${refMax}</td>`;
                }
                html += `</tr>`;
            });
            tbody.innerHTML = html;
        },

        renderTableFooter(selectedScopes) {
            const tfoot = document.getElementById(CONFIG.SELECTORS.tableFooter);
            if (!tfoot) return;

            if (selectedScopes.length === 0) {
                tfoot.innerHTML = '';
                return;
            }

            // --- Helper for footer cells ---
            const createCell = (content, classes = '') => `<td class="text-center border-start ${classes}">${content}</td>`;
            const createMaxLevelCell = (val = '-') => STATE.showMaxLevel ? `<td class="text-center bg-secondary text-white">${val}</td>` : '';
            
            let html = '';

            // ROW 1: Total GAMO Selected
            html += `<tr class="table-light fw-bold border-top-2">
                        <td colspan="3" class="text-end pe-3 sticky-col">Total GAMO Selected</td>`;
            selectedScopes.forEach(s => {
                const count = Utils.countValidScores(Object.values(s.maturity_scores));
                html += createCell(count, 'bg-light text-dark');
            });
            html += createMaxLevelCell() + `</tr>`;

            // ROW 2: I&T Maturity Score
            html += `<tr class="table-light fw-bold">
                        <td colspan="3" class="text-end pe-3 sticky-col">I&T Maturity Score</td>`;
            selectedScopes.forEach(s => {
                const avg = Utils.calculateAverage(Object.values(s.maturity_scores));
                html += createCell(Utils.fmt(avg), 'bg-primary text-white');
            });
            html += createMaxLevelCell() + `</tr>`;

            // ROW 3: I&T Target Maturity
            html += `<tr class="table-info fw-bold">
                        <td colspan="3" class="text-end pe-3 sticky-col">I&T Target Maturity</td>`;
            selectedScopes.forEach(s => {
                const tm = Utils.getEffectiveTarget(s);
                html += createCell(tm ? Utils.fmt(tm) : '-', 'bg-info text-white');
            });
            html += createMaxLevelCell() + `</tr>`;

            // ROW 4: Gap Analysis
            html += `<tr class="fw-bold text-white">
                        <td colspan="3" class="text-end pe-3 sticky-col text-dark">Gap Analysis</td>`;
            selectedScopes.forEach(s => {
                const avg = Utils.calculateAverage(Object.values(s.maturity_scores));
                const target = Utils.getEffectiveTarget(s);
                const gap = avg - target; // Actual - Target
                
                const gapClass = gap >= 0 ? 'gap-pos' : 'gap-neg';
                const gapStr = (gap > 0 ? '+' : '') + Utils.fmt(gap);

                html += createCell(gapStr, gapClass);
            });
            html += createMaxLevelCell() + `</tr>`;

            tfoot.innerHTML = html;
        }
    };

    // ==========================================================================
    // 5. CORE APPLICATION CONTROLLER
    // ==========================================================================
    const AssessmentReport = {
        init() {
            document.addEventListener('DOMContentLoaded', () => {
                this.loadInitialData();
                this.cacheElements();
                this.bindEvents();
                this.renderAll();
            });
        },

        loadInitialData() {
            // Default: Select latest 5 scopes
            SERVER_DATA.scopes.slice(0, 5).forEach(s => STATE.selectedScopeIds.add(s.scope_id));
            
            // Load Toggle Preference
            const stored = localStorage.getItem('showMaxLevel');
            // Default to true if user says it's missing, but logic says default false. 
            // Let's rely on storage. If null, false.
            STATE.showMaxLevel = (stored === 'true');
        },

        cacheElements() {
            // Optional: Cache frequent DOM elements if needed
        },

        getSelectedScopes() {
            return SERVER_DATA.scopes.filter(s => STATE.selectedScopeIds.has(s.scope_id));
        },

        renderAll() {
            // Render Filter Sidebar (Only needs to happen once or on data update, but OK to refresh)
            View.renderFilter(SERVER_DATA.scopes);
            
            // Render Main Table
            const selected = this.getSelectedScopes();
            View.renderTableHeader(selected);
            View.renderTableBody(selected, SERVER_DATA.objectives);
            View.renderTableFooter(selected);
        },

        // Optimized re-render only for table parts (when checkbox changes)
        refreshTable() {
             const selected = this.getSelectedScopes();
             View.renderTableHeader(selected);
             View.renderTableBody(selected, SERVER_DATA.objectives);
             View.renderTableFooter(selected);
             View.updateBadge();
        },

        bindEvents() {
            // 1. Search Scope
            const searchInput = document.getElementById(CONFIG.SELECTORS.scopeSearch);
            if (searchInput) {
                searchInput.addEventListener('input', (e) => {
                    const term = e.target.value.toLowerCase();
                    document.querySelectorAll('.scope-item').forEach(el => {
                        el.style.display = el.textContent.toLowerCase().includes(term) ? 'block' : 'none';
                    });
                });
            }

            // 2. Select All
            const btnAll = document.getElementById(CONFIG.SELECTORS.btnSelectAll);
            if (btnAll) {
                btnAll.addEventListener('click', () => {
                   SERVER_DATA.scopes.forEach(s => STATE.selectedScopeIds.add(s.scope_id));
                   this.renderAll(); // Need to re-render renderFilter checkboxes too
                });
            }

            // 3. Deselect All
            const btnNone = document.getElementById(CONFIG.SELECTORS.btnDeselectAll);
            if (btnNone) {
                btnNone.addEventListener('click', () => {
                   STATE.selectedScopeIds.clear();
                   this.renderAll();
                });
            }

            // 4. Checkbox Delegation (Filter List)
            const filterContainer = document.getElementById(CONFIG.SELECTORS.scopeFilters);
            if (filterContainer) {
                filterContainer.addEventListener('change', (e) => {
                    if (e.target.matches('.scope-checkbox')) {
                        const id = parseInt(e.target.value);
                        if (e.target.checked) STATE.selectedScopeIds.add(id);
                        else STATE.selectedScopeIds.delete(id);
                        
                        this.refreshTable();
                    }
                });
            }

            // 5. Max Level Toggle
            const toggleMax = document.getElementById(CONFIG.SELECTORS.chkShowMaxLevel);
            if (toggleMax) {
                toggleMax.checked = STATE.showMaxLevel; // Set initial state
                toggleMax.addEventListener('change', (e) => {
                    STATE.showMaxLevel = e.target.checked;
                    localStorage.setItem('showMaxLevel', e.target.checked);
                    this.refreshTable();
                });
            }

            // 6. Export PDF
            const btnExport = document.getElementById(CONFIG.SELECTORS.btnExportPdf);
            if (btnExport) {
                btnExport.addEventListener('click', () => {
                    const selected = Array.from(STATE.selectedScopeIds);
                    if (selected.length === 0) {
                        alert('Please select at least one scope to export.');
                        return;
                    }

                    // Create hidden form to submit IDs
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = "{{ route('assessment-eval.report.all-pdf') }}";
                    form.target = '_blank'; // Open in new tab (optional, but good for downloads)

                    // CSRF Token
                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = "{{ csrf_token() }}";
                    form.appendChild(csrf);

                    // IDs
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'scope_ids';
                    input.value = selected.join(',');
                    form.appendChild(input);

                    // Show Max Level
                    const showMaxInput = document.createElement('input');
                    showMaxInput.type = 'hidden';
                    showMaxInput.name = 'show_max_level';
                    showMaxInput.value = STATE.showMaxLevel ? '1' : '0';
                    form.appendChild(showMaxInput);

                    document.body.appendChild(form);
                    form.submit();
                    document.body.removeChild(form);
                });
            }
        }
    };

    // Start App
    AssessmentReport.init();

})();
</script>
@endsection
