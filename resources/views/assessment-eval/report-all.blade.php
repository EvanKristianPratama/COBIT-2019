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
                <div class="card-footer bg-white text-center">
                     <button class="btn btn-sm btn-outline-primary" id="btn-select-all">Select All</button>
                     <button class="btn btn-sm btn-outline-secondary" id="btn-deselect-all">Deselect All</button>
                </div>
            </div>
        </div>

        {{-- Report Table --}}
        <div class="col-md-9 mb-4">
            <div class="card shadow-sm border-0" id="report-result-card">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-primary">All Maturity Report</h5>
                    <div>
                         <span class="badge bg-info text-dark" id="selected-count-badge">0 selected</span>
                    </div>
                </div>
                <div class="card-body p-0" id="report-container">
                    <div class="table-responsive">
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

<style>
    /* Sticky first 3 columns for horizontal scroll */
    .sticky-col {
        position: sticky;
        left: 0;
        background-color: #fff !important;
        z-index: 2;
    }
    th.sticky-col:nth-child(1), td.sticky-col:nth-child(1) { left: 0px; z-index: 3; width: 50px;}
    th.sticky-col:nth-child(2), td.sticky-col:nth-child(2) { left: 50px; z-index: 3; width: 90px;}
    th.sticky-col:nth-child(3), td.sticky-col:nth-child(3) { left: 140px; z-index: 3; }
    
    thead th { position: sticky; top: 0; z-index: 5; background-color: #f8f9fa; }
</style>

<script>
/**
 * All-Years Assessment Report
 */
(function() {
    'use strict';

    // Configuration
    const Config = {
        maxLevels: 5, // Simplified max level for visuals
        colors: {
            bg:   ['#ffebee', '#fff3e0', '#fff8e1', '#e8f5e9', '#e3f2fd', '#f3e5f5'],
            text: ['#c62828', '#ef6c00', '#f57f17', '#2e7d32', '#1565c0', '#6a1b9a']
        }
    };

    // Data from server
    const AppData = {
        objectives: @json($objectives),
        scopes: @json($processedData ?? []) // Array of processed scope objects
    };

    // State
    const State = {
        selectedScopeIds: new Set()
    };

    // Utilities
    const Utils = {
        escape: s => (s || '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c])),
        fmt: n => (n || 0).toFixed(2),
    };

    const ReportApp = {
        init() {
            document.addEventListener('DOMContentLoaded', () => {
                // Initialize selection (default: select latest 5)
                AppData.scopes.slice(0, 5).forEach(s => State.selectedScopeIds.add(s.scope_id));
                
                this.renderFilter();
                this.renderTable();
                this.bindEvents();
            });
        },

        bindEvents() {
            // Search Filter
            document.getElementById('scope-search').addEventListener('input', (e) => {
                const term = e.target.value.toLowerCase();
                document.querySelectorAll('.scope-item').forEach(el => {
                    const text = el.textContent.toLowerCase();
                    el.style.display = text.includes(term) ? 'block' : 'none';
                });
            });

            // Select/Deselect All
            document.getElementById('btn-select-all').addEventListener('click', () => {
                AppData.scopes.forEach(s => State.selectedScopeIds.add(s.scope_id));
                this.updateUI();
            });

            document.getElementById('btn-deselect-all').addEventListener('click', () => {
                State.selectedScopeIds.clear();
                this.updateUI();
            });

            // Delegation for checkbox changes
            document.getElementById('scope-filters').addEventListener('change', (e) => {
                if (e.target.matches('.scope-checkbox')) {
                    const id = parseInt(e.target.value);
                    if (e.target.checked) State.selectedScopeIds.add(id);
                    else State.selectedScopeIds.delete(id);
                    this.renderTable();
                    this.updateBadge(); // Only update badge and table, not re-render filter
                }
            });
        },

        updateUI() {
            this.renderFilter(); // Re-render checkboxes to match state
            this.renderTable();
        },
        
        updateBadge() {
            document.getElementById('selected-count-badge').textContent = `${State.selectedScopeIds.size} selected`;
        },

        renderFilter() {
            const container = document.getElementById('scope-filters');
            if (!container) return;

            let html = '';
            // Group by Year for better readability
            const grouped = {};
            AppData.scopes.forEach(s => {
                if (!grouped[s.year]) grouped[s.year] = [];
                grouped[s.year].push(s);
            });

            // Sort years asc
            const years = Object.keys(grouped).sort((a,b) => a - b);

            years.forEach(year => {
                html += `<div class="scope-group-year fw-bold mt-2 mb-1 text-muted small border-bottom">${year}</div>`;
                grouped[year].forEach(scope => {
                    const checked = State.selectedScopeIds.has(scope.scope_id) ? 'checked' : '';
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

        renderTable() {
            const selectedScopes = AppData.scopes.filter(s => State.selectedScopeIds.has(s.scope_id));
            
            // Render Headers
            const theadRow = document.getElementById('table-header-row');
            let headerHtml = `
                    <th style="width:50px;" class="text-center sticky-col">No</th>
                    <th style="width:90px;" class=" sticky-col">GAMO</th>
                    <th class="sticky-col">Process Name</th>`;
            
            selectedScopes.forEach(s => {
                headerHtml += `
                    <th style="width:100px;" class="text-center">
                        <div>${Utils.escape(s.year)}</div>
                        <div class="small fw-normal text-muted">${Utils.escape(s.scope_name)}</div>
                    </th>`;
            });
            theadRow.innerHTML = headerHtml;

            // Render Body
            const tbody = document.getElementById('recap-table-body');
            let bodyHtml = '';

            if (selectedScopes.length === 0) {
                bodyHtml = `<tr><td colspan="20" class="text-center py-5 text-muted">Please select scopes from the left panel to view data.</td></tr>`;
            } else {
                AppData.objectives.forEach((obj, index) => {
                    bodyHtml += `
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
                            bodyHtml += `<td class="text-center text-muted bg-light border-start">-</td>`;
                        } else {
                            const bg = Config.colors.bg[score] || '#f8f9fa';
                            const text = Config.colors.text[score] || '#6c757d';
                            bodyHtml += `<td class="text-center fw-bold border-start" style="background-color: ${bg}; color: ${text};">${score}</td>`;
                        }
                    });

                    bodyHtml += `</tr>`;
                });
            }
            tbody.innerHTML = bodyHtml;

            // Render Footer (Counts & Averages)
            const tfoot = document.getElementById('recap-table-footer');
            if (selectedScopes.length === 0) {
                tfoot.innerHTML = '';
            } else {
                // Row 1: Total GAMO Selected
                let footerHtml = `
                    <tr class="table-light fw-bold border-top-2">
                         <td colspan="3" class="text-end pe-3 sticky-col">Total GAMO Selected</td>`;
                
                selectedScopes.forEach(s => {
                    // Count non-null scores
                    const count = Object.values(s.maturity_scores).filter(v => v !== null && v !== undefined).length;
                    
                    footerHtml += `
                        <td class="text-center bg-light text-dark border-start">
                            ${count}
                        </td>`;
                });
                footerHtml += `</tr>`;

                // Row 2: Average Maturity Score
                footerHtml += `
                    <tr class="table-light fw-bold">
                         <td colspan="3" class="text-end pe-3 sticky-col">Average Maturity Score</td>`;
                
                selectedScopes.forEach(s => {
                    // Calculate average of displayed scores
                    const values = Object.values(s.maturity_scores).filter(v => v !== null && v !== undefined);
                    const avg = values.length ? (values.reduce((a,b) => a+b, 0) / values.length) : 0;
                    
                    footerHtml += `
                        <td class="text-center bg-primary text-white border-start">
                            ${Utils.fmt(avg)}
                        </td>`;
                });
                footerHtml += `</tr>`;
                tfoot.innerHTML = footerHtml;
            }
        }
    };

    ReportApp.init();

})();
</script>
@endsection
