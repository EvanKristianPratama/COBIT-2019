@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container-fluid p-4">
    {{-- Header --}}
    <div class="card shadow-sm mb-4 hero-card" style="border:none;box-shadow:0 22px 45px rgba(14,33,70,0.15);">
        <div class="card-header hero-header py-4" style="background:linear-gradient(135deg,#081a3d,#0f2b5c);color:#fff;border:none;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="hero-title" style="font-size:1.5rem;font-weight:700;letter-spacing:0.04em;">Spiderweb Analysis</div>
                    <div class="hero-subtitle" style="font-size:1.05rem;font-weight:400;margin-top:0.25rem;color:rgba(255,255,255,0.85);">
                        Visualize and compare maturity levels across scopes
                    </div>
                </div>
                <div>
                     <a href="{{ route('assessment-eval.report.all') }}" class="btn btn-outline-light btn-sm px-3 me-2">
                        <i class="fas fa-table me-2"></i>Back to Table View
                    </a>
                    <a href="{{ route('assessment-eval.list') }}" class="btn btn-light btn-sm rounded-pill px-3">
                        <i class="fas fa-list me-2"></i>Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Table (Top) --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <h6 class="mb-0 fw-bold text-primary"><i class="fas fa-filter me-2"></i>Select Scopes</h6>
                <input type="text" class="form-control form-control-sm" id="scope-search" placeholder="Search..." style="width: 200px;">
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-primary" id="btn-select-all"><i class="fas fa-check-double me-1"></i>Select All</button>
                <button class="btn btn-sm btn-outline-secondary" id="btn-deselect-all"><i class="fas fa-times me-1"></i>Deselect All</button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                <table class="table table-sm table-bordered mb-0" id="scope-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;" class="text-center">Pilih</th>
                            <th style="width: 70px;">Tahun</th>
                            <th>Nama Scope</th>
                            <th style="width: 80px;" class="text-center">Jumlah GAMO</th>
                            <th style="width: 100px;" class="text-center">Avg Maturity</th>
                            <th style="width: 120px;" class="text-center">Target Maturity</th>
                        </tr>
                    </thead>
                    <tbody id="scope-filters">
                        {{-- JS will populate this --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Chart Content (Full Width, Large) --}}
    <div class="view-section" id="spiderweb-result-card">
        <div id="charts-container">
            {{-- Chart will be populated here --}}
        </div>
        <div id="spiderweb-empty-state" class="text-center py-5 bg-white rounded shadow-sm border d-none">
            <div class="text-muted mb-2"><i class="fas fa-spider fa-3x opacity-25"></i></div>
            <div class="text-muted">Please select scopes to view charts.</div>
        </div>
    </div>
</div>

<script>
/**
 * Spiderweb Report
 */
(function() {
    'use strict';

    // Data from server
    const AppData = {
        objectives: @json($objectives),
        scopes: @json($processedData ?? []) 
    };

    // State
    const State = {
        selectedScopeIds: new Set(),
        chart: null 
    };

    // Utilities
    const Utils = {
        escape: s => (s || '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c])),
        // Helper to generate a vibrant color from string
        stringToColor: function(str) {
            let hash = 0;
            for (let i = 0; i < str.length; i++) {
                hash = str.charCodeAt(i) + ((hash << 5) - hash);
            }
            const c = (hash & 0x00FFFFFF).toString(16).toUpperCase();
            return '#' + '00000'.substring(0, 6 - c.length) + c;
        }
    };

    // Max Levels for all 40 GAMOs
    const MAX_LEVELS_REF = {
        'EDM01': 4, 'EDM02': 5, 'EDM03': 4, 'EDM04': 4, 'EDM05': 4,
        'APO01': 5, 'APO02': 4, 'APO03': 5, 'APO04': 4, 'APO05': 5, 'APO06': 5, 'APO07': 4, 'APO08': 5, 'APO09': 4, 'APO10': 5, 'APO11': 5, 'APO12': 5, 'APO13': 5, 'APO14': 5,
        'BAI01': 5, 'BAI02': 4, 'BAI03': 4, 'BAI04': 5, 'BAI05': 5, 'BAI06': 4, 'BAI07': 5, 'BAI08': 5, 'BAI09': 5, 'BAI10': 4, 'BAI11': 5,
        'DSS01': 5, 'DSS02': 5, 'DSS03': 5, 'DSS04': 4, 'DSS05': 5, 'DSS06': 5,
        'MEA01': 5, 'MEA02': 5, 'MEA03': 5, 'MEA04': 4
    };

    // BUMN 24 GAMOs with target level 3
    const BUMN_GAMOS = [
        'EDM01', 'EDM02', 'APO01', 'APO02', 'APO03', 'APO05', 'APO06', 'APO09', 
        'APO10', 'APO12', 'APO13', 'APO14', 'BAI02', 'BAI03', 'BAI04', 'BAI06', 
        'BAI07', 'BAI09', 'BAI11', 'DSS01', 'DSS02', 'DSS04', 'DSS05', 'MEA01'
    ];
    const BUMN_TARGET = 3;

    const ReportApp = {
        init() {
            document.addEventListener('DOMContentLoaded', () => {
                // Initialize selection (default: select latest 5)
                AppData.scopes.slice(0, 5).forEach(s => State.selectedScopeIds.add(s.scope_id));
                
                this.renderFilter();
                this.renderCharts();
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
                    this.updateUI();
                }
            });
        },

        updateUI() {
            this.renderFilter(); // Re-render to update badge styles
            this.renderCharts();
        },

        renderFilter() {
            const container = document.getElementById('scope-filters');
            if (!container) return;

            let html = '';
            
            // Group by year first for rowspan
            const grouped = {};
            AppData.scopes.forEach(s => {
                if (!grouped[s.year]) grouped[s.year] = [];
                grouped[s.year].push(s);
            });
            
            // Sort years desc
            const years = Object.keys(grouped).sort((a, b) => b - a);
            
            years.forEach(year => {
                const scopes = grouped[year].sort((a, b) => a.scope_name.localeCompare(b.scope_name));
                const rowspan = scopes.length;
                // Target maturity is same for all scopes in same year
                const targetMaturity = scopes[0].target_maturity ? Number(scopes[0].target_maturity).toFixed(2) : '-';
                
                scopes.forEach((scope, idx) => {
                    const isSelected = State.selectedScopeIds.has(scope.scope_id);
                    
                    // Calculate GAMO count and average maturity for this scope
                    const maturityScores = scope.maturity_scores || {};
                    const validScores = Object.values(maturityScores).filter(v => v !== null && v !== undefined && v !== 0);
                    const gamoCount = validScores.length;
                    const avgMaturity = gamoCount > 0 ? (validScores.reduce((a, b) => a + b, 0) / gamoCount).toFixed(2) : '-';
                    
                    html += `<tr class="scope-item" data-scope-id="${scope.scope_id}">
                        <td class="text-center">
                            <input type="checkbox" class="scope-checkbox" value="${scope.scope_id}" ${isSelected ? 'checked' : ''}>
                        </td>`;
                    
                    // Only add year and target maturity cell for first row of each year group
                    if (idx === 0) {
                        html += `<td rowspan="${rowspan}" class="align-middle text-center fw-bold">${year}</td>`;
                    }
                    
                    html += `<td>${Utils.escape(scope.scope_name)}</td>`;
                    html += `<td class="text-center">${gamoCount}</td>`;
                    html += `<td class="text-center fw-semibold">${avgMaturity}</td>`;
                    
                    // Only add target maturity cell for first row of each year group
                    if (idx === 0) {
                        html += `<td rowspan="${rowspan}" class="align-middle text-center">${targetMaturity}</td>`;
                    }
                    
                    html += `</tr>`;
                });
            });

            container.innerHTML = html;
            
            // Row click handler
            container.querySelectorAll('tr.scope-item').forEach(row => {
                row.addEventListener('click', (e) => {
                    if (e.target.type !== 'checkbox') {
                        const checkbox = row.querySelector('.scope-checkbox');
                        checkbox.checked = !checkbox.checked;
                        checkbox.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                });
            });
        },

        renderCharts() {
            const container = document.getElementById('charts-container');
            const emptyState = document.getElementById('spiderweb-empty-state');
            
            // Destroy existing chart to free memory
            if (State.chart) {
                State.chart.destroy();
                State.chart = null;
            }
            container.innerHTML = '';

            const selectedScopes = AppData.scopes.filter(s => State.selectedScopeIds.has(s.scope_id));

            if (selectedScopes.length === 0) {
                emptyState.classList.remove('d-none');
                return;
            } else {
                emptyState.classList.add('d-none');
            }

            // Create ONE large container (full width, tall chart)
            const col = document.createElement('div');
            col.className = 'w-100';
            col.innerHTML = `
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-gradient py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0"><i class="fas fa-spider me-2"></i>Assessment Comparison</h5>
                            <span class="badge bg-white text-dark">${selectedScopes.length} Scopes Selected</span>
                        </div>
                    </div>
                    <div class="card-body p-4" style="min-height: 70vh;">
                         <canvas id="chart-combined"></canvas>
                    </div>
                </div>
            `;
            container.appendChild(col);

            // Collect union of all GAMOs that have data in selected scopes
            const activeGamoIds = new Set();
            selectedScopes.forEach(scope => {
                Object.entries(scope.maturity_scores || {}).forEach(([objId, val]) => {
                    if (val !== null && val !== undefined && val !== 0) {
                        activeGamoIds.add(objId);
                    }
                });
            });

            // Filter objectives to only those in activeGamoIds (keep original order)
            const filteredObjectives = AppData.objectives.filter(o => activeGamoIds.has(o.objective_id));
            
            // Prepare Labels (only active objectives)
            const labels = filteredObjectives.map(o => o.objective_id);

            // Prepare Datasets (only active objectives data)
            const datasets = selectedScopes.map((s, index) => {
                 // Extract scores in order of filtered objectives
                const data = filteredObjectives.map(o => {
                    const val = s.maturity_scores[o.objective_id];
                    return (val === null || val === undefined) ? 0 : val;
                });
                
                // Color Management
                const palette = [
                    '#e60049', '#0bb4ff', '#50e991', '#e6d800', '#9b19f5', '#ffa300', '#dc0ab4', '#b3d4ff', '#00bfa0'
                ];
                const color = palette[index % palette.length];

                return {
                    label: `${s.year} - ${s.scope_name} (${s.user_name})`,
                    data: data,
                    fill: true,
                    backgroundColor: color + '1A', // 10% opacity
                    borderColor: color,
                    pointBackgroundColor: color,
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: color,
                    borderWidth: 2
                };
            });

            // Add Max Level reference line
            const maxLevelData = filteredObjectives.map(o => MAX_LEVELS_REF[o.objective_id] || 5);
            datasets.push({
                label: 'Max Level',
                data: maxLevelData,
                fill: false,
                backgroundColor: 'transparent',
                borderColor: '#6c757d',
                borderDash: [5, 5],
                pointBackgroundColor: '#6c757d',
                pointBorderColor: '#fff',
                pointRadius: 3,
                borderWidth: 2
            });

            // Add BUMN Target reference line (only if any BUMN GAMOs are in filtered list)
            const bumnData = filteredObjectives.map(o => BUMN_GAMOS.includes(o.objective_id) ? BUMN_TARGET : 0);
            const hasBumnData = bumnData.some(v => v > 0);
            if (hasBumnData) {
                datasets.push({
                    label: 'BUMN Target (Level 3)',
                    data: bumnData,
                    fill: false,
                    backgroundColor: 'transparent',
                    borderColor: '#fd7e14',
                    borderDash: [8, 4],
                    pointBackgroundColor: '#fd7e14',
                    pointBorderColor: '#fff',
                    pointRadius: 3,
                    borderWidth: 2
                });
            }

            // Init Chart
            const ctx = document.getElementById('chart-combined').getContext('2d');
            State.chart = new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        r: {
                            angleLines: { display: true },
                            suggestedMin: 0,
                            suggestedMax: 5,
                            ticks: { 
                                stepSize: 1,
                                backdropColor: 'transparent' 
                            },
                        }
                    },
                    plugins: {
                        legend: { 
                            display: true,
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 10
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    }
                }
            });
        }
    };

    ReportApp.init();

})();
</script>
@endsection
