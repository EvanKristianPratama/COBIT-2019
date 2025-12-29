@extends('layouts.app')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="container mx-auto p-6">
        {{-- Header --}}
        <div class="card shadow-sm mb-4 hero-card" style="border:none;box-shadow:0 22px 45px rgba(14,33,70,0.15);">
            <div class="card-header hero-header py-4"
                style="background:linear-gradient(135deg,#081a3d,#0f2b5c);color:#fff;border:none;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="hero-title" style="font-size:1.5rem;font-weight:700;letter-spacing:0.04em;">Assessment
                            Report</div>
                        <div class="hero-eval-id"
                            style="font-size:1.05rem;font-weight:600;margin-top:0.25rem;letter-spacing:0.08em;text-transform:uppercase;color:rgba(255,255,255,0.85);">
                            Assessment Id: {{ $evalId }}
                        </div>
                        <div class="hero-eval-year text-uppercase"
                            style="font-size:0.95rem;font-weight:600;color:rgba(255,255,255,0.75);letter-spacing:0.06em;">
                            Assessment Year:
                            {{ $evaluation->year ?? ($evaluation->assessment_year ?? ($evaluation->tahun ?? 'N/A')) }}
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('assessment-eval.show', $evalId) }}"
                            class="btn btn-light btn-sm rounded-pill px-3">
                            <i class="fas fa-arrow-left me-2"></i>Back to Assessment
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Report Result Section --}}
        <div class="card shadow-sm border-0 mb-4" id="report-result-card">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-primary">Assessment Recapitulation Report</h5>
            </div>
            <div class="card-body p-0" id="report-container">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered recap-table align-middle mb-0" id="recap-table">
                        <thead>
                            <tr>
                                <th style="width:50px;" class="text-center">No</th>
                                <th style="width:90px;">Gamo</th>
                                <th>Process Name</th>
                                <th style="width:80px;" class="text-center">Max Level</th>
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        /**
         * Multi-Scope Assessment Report
         * Shows all 40 GAMOs with maturity data for each scope
         */
        (function() {
            'use strict';

            // Configuration
            const Config = {
                assessmentUrl: "{{ route('assessment-eval.show', $evalId) }}",
                maxLevels: {
                    'EDM01': 4,
                    'EDM02': 5,
                    'EDM03': 4,
                    'EDM04': 4,
                    'EDM05': 4,
                    'APO01': 5,
                    'APO02': 4,
                    'APO03': 5,
                    'APO04': 4,
                    'APO05': 5,
                    'APO06': 5,
                    'APO07': 4,
                    'APO08': 5,
                    'APO09': 4,
                    'APO10': 5,
                    'APO11': 5,
                    'APO12': 5,
                    'APO13': 5,
                    'APO14': 5,
                    'BAI01': 5,
                    'BAI02': 4,
                    'BAI03': 4,
                    'BAI04': 5,
                    'BAI05': 5,
                    'BAI06': 4,
                    'BAI07': 5,
                    'BAI08': 5,
                    'BAI09': 5,
                    'BAI10': 4,
                    'BAI11': 5,
                    'DSS01': 5,
                    'DSS02': 5,
                    'DSS03': 5,
                    'DSS04': 4,
                    'DSS05': 5,
                    'DSS06': 5,
                    'MEA01': 5,
                    'MEA02': 5,
                    'MEA03': 5,
                    'MEA04': 4
                },
                defaultMax: 5,
                colors: {
                    bg: ['#ffebee', '#fff3e0', '#fff8e1', '#e8f5e9', '#e3f2fd', '#f3e5f5'],
                    text: ['#c62828', '#ef6c00', '#f57f17', '#2e7d32', '#1565c0', '#6a1b9a']
                }
            };

            // Data from server
            const AppData = {
                objectives: @json($objectives),
                targetMap: @json($targetCapabilityMap ?? []),
                allScopes: @json($allScopes ?? []),
                scopeMaturityData: @json($scopeMaturityData ?? []),
                targetMaturity: @json($targetMaturity ?? null)
            };

            // Utilities
            const Utils = {
                escape: s => (s || '').replace(/[&<>"']/g, c => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                } [c])),
                parseNum: v => {
                    const n = Number(v);
                    return (v === null || v === undefined || v === '' || isNaN(n)) ? null : n;
                },
                fmt: n => (Number(n) || 0).toFixed(2),
                getLink: id => `${Config.assessmentUrl}#objective-${id}`
            };

            const ReportApp = {
                init() {
                    document.addEventListener('DOMContentLoaded', () => {
                        try {
                            this.render();
                        } catch (e) {
                            console.error('Report Error:', e);
                            const tbody = document.getElementById('recap-table-body');
                            if (tbody) tbody.innerHTML =
                                `<tr><td colspan="20" class="text-center text-danger py-3">Failed to load report: ${Utils.escape(e.message)}</td></tr>`;
                        }
                    });
                },

                render() {
                    this.renderHeaders();
                    this.renderBody();
                    this.renderFooter();
                },

                renderHeaders() {
                    const thead = document.querySelector('#recap-table thead');
                    if (!thead) return;

                    let html = `
                <tr>
                    <th style="width:50px;" class="text-center">No</th>
                    <th style="width:90px;">GAMO</th>
                    <th>Process Name</th>`;

                    // Dynamic scope columns
                    AppData.allScopes.forEach(scope => {
                        html += `
                    <th style="width:100px;" class="text-center">${Utils.escape(scope.nama_scope)}</th>`;
                    });

                    html += `
                    <th style="width:80px;" class="text-center">Max Level</th>
                    <th style="width:80px;" class="text-center">Action</th>
                </tr>`;

                    thead.innerHTML = html;
                },

                renderBody() {
                    const tbody = document.getElementById('recap-table-body');
                    if (!tbody) return;

                    if (!AppData.objectives || !AppData.objectives.length) {
                        tbody.innerHTML =
                            `<tr><td colspan="20" class="text-center py-4 text-muted">No data available.</td></tr>`;
                        return;
                    }

                    let html = '';
                    AppData.objectives.forEach((obj, index) => {
                        const id = obj.objective_id;
                        const max = Config.maxLevels[id] || Config.defaultMax;

                        html += `
                    <tr>
                        <td class="text-center fw-semibold">${index + 1}</td>
                        <td>
                            <a href="${Utils.getLink(id)}" class="text-decoration-none fw-bold text-primary">
                                ${Utils.escape(id)}
                            </a>
                        </td>
                        <td><span class="small text-muted">${Utils.escape(obj.objective || '')}</span></td>`;

                        // Dynamic scope columns
                        AppData.allScopes.forEach(scope => {
                            const maturity = AppData.scopeMaturityData[scope.id]?.[id];

                            if (maturity === null || maturity === undefined) {
                                // Not in scope
                                html += `
                            <td class="text-center text-muted">-</td>`;
                            } else {
                                const maturityBg = Config.colors.bg[maturity] || '#f8f9fa';
                                const maturityColor = Config.colors.text[maturity] || '#6c757d';

                                html +=
                                    `
                            <td class="text-center fw-bold" style="background-color: ${maturityBg}; color: ${maturityColor};">${maturity}</td>`;
                            }
                        });

                        html += `
                        <td class="text-center fw-bold text-secondary">${max}</td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <a href="/assessment-eval/{{ $evalId }}/report-activity/${id}" 
                                   class="btn btn-xs btn-outline-primary rounded-pill py-1 px-3 me-1" 
                                   style="font-size: 0.7rem; font-weight: 600;">
                                    Detail
                                </a>
                                <a href="/assessment-eval/{{ $evalId }}/summary/${id}" 
                                   class="btn btn-xs btn-outline-info rounded-pill py-1 px-3" 
                                   style="font-size: 0.7rem; font-weight: 600;">
                                    Summary
                                </a>
                            </div>
                        </td>
                    </tr>`;
                    });

                    tbody.innerHTML = html;
                },

                renderFooter() {
                    const tfoot = document.getElementById('recap-table-footer');
                    if (!tfoot || !AppData.objectives.length) return;

                    const total = AppData.objectives.length;
                    const avgMax = AppData.objectives.reduce((s, o) => s + (Config.maxLevels[o.objective_id] ||
                        Config.defaultMax), 0) / total;

                    // Row 1: Total GAMO Selected
                    let html = `
                <tr class="table-light fw-bold border-top-2">
                    <td colspan="3" class="text-end pe-3">Total GAMO Selected</td>`;

                    AppData.allScopes.forEach(scope => {
                        const scopeData = AppData.scopeMaturityData[scope.id] || {};
                        const gamoCount = Object.values(scopeData).filter(v => v !== null && v !==
                            undefined).length;

                        html += `
                    <td class="text-center bg-light text-dark">
                        ${gamoCount}
                    </td>`;
                    });

                    html += `<td class="text-center bg-light text-dark">-</td></tr>`; // Max column

                    // Row 2: Average Maturity Score
                    html += `
                <tr class="table-light fw-bold">
                    <td colspan="3" class="text-end pe-3">Average Maturity Score</td>`;

                    AppData.allScopes.forEach(scope => {
                        const scopeData = AppData.scopeMaturityData[scope.id] || {};
                        const scopeValues = Object.values(scopeData).filter(v => v !== null && v !==
                            undefined);
                        const avgScore = scopeValues.length ? (scopeValues.reduce((s, v) => s + v, 0) /
                            scopeValues.length) : 0;

                        html += `
                    <td class="text-center bg-primary text-white">
                        ${Utils.fmt(avgScore)}
                    </td>`;
                    });

                    html += `
                    <td class="text-center bg-secondary text-white">${Utils.fmt(avgMax)}</td>
                </tr>`;

                    // Row 3: I&T Target Maturity (if available)
                    if (AppData.targetMaturity !== null) {
                        html += `
                    <tr class="table-info fw-bold">
                        <td colspan="3" class="text-end pe-3">I&T Target Maturity</td>`;

                        AppData.allScopes.forEach(scope => {
                            html += `
                        <td class="text-center bg-info text-white">
                            ${Utils.fmt(AppData.targetMaturity)}
                        </td>`;
                        });

                        html += `<td class="text-center bg-info text-white">-</td></tr>`;
                    }

                    tfoot.innerHTML = html;
                }
            };

            ReportApp.init();

        })();
    </script>
@endsection
