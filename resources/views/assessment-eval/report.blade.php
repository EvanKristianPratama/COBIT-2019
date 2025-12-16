@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container mx-auto p-6">
    {{-- Header --}}
    <div class="card shadow-sm mb-4 hero-card" style="border:none;box-shadow:0 22px 45px rgba(14,33,70,0.15);">
        <div class="card-header hero-header py-4" style="background:linear-gradient(135deg,#081a3d,#0f2b5c);color:#fff;border:none;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="hero-title" style="font-size:1.5rem;font-weight:700;letter-spacing:0.04em;">Assessment Report</div>
                    <div class="hero-eval-id" style="font-size:1.05rem;font-weight:600;margin-top:0.25rem;letter-spacing:0.08em;text-transform:uppercase;color:rgba(255,255,255,0.85);">
                        Assessment Id: {{ $evalId }}
                    </div>
                    <div class="hero-eval-year text-uppercase" style="font-size:0.95rem;font-weight:600;color:rgba(255,255,255,0.75);letter-spacing:0.06em;">
                        Assessment Year: {{ $evaluation->year ?? $evaluation->assessment_year ?? $evaluation->tahun ?? 'N/A' }}
                    </div>
                </div>
                <div>
                    <a href="{{ route('assessment-eval.show', $evalId) }}" class="btn btn-light btn-sm rounded-pill px-3">
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
                            <th style="width:80px;">Domain</th>
                            <th style="width:90px;">Gamo</th>
                            <th>Process Name</th>
                            <th style="width:80px;" class="text-center">Score</th>
                            <th style="width:80px;" class="text-center">Rating</th>
                            <th style="width:80px;" class="text-center">Target</th>
                            <th style="width:80px;" class="text-center">Gap</th>
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
 * Assessment Report Module
 * Optimized for performance (O(1) lookups) and maintainability (Centralized Config).
 * Uses IIFE to prevent global namespace pollution.
 */
(function() {
    'use strict';

    // 1. Configuration & Static Data
    const Config = {
        // Assessment Form Base URL
        assessmentUrl: "{{ route('assessment-eval.show', $evalId) }}",
        
        // COBIT 2019 Max Capability Levels (Direct Map for O(1) Access)
        maxLevels: {
            'EDM01': 4, 'EDM02': 5, 'EDM03': 4, 'EDM04': 4, 'EDM05': 4,
            'APO01': 5, 'APO02': 4, 'APO03': 5, 'APO04': 4, 'APO05': 5, 'APO06': 5, 'APO07': 4, 'APO08': 5, 'APO09': 4, 'APO10': 5, 'APO11': 5, 'APO12': 5, 'APO13': 5, 'APO14': 5,
            'BAI01': 5, 'BAI02': 4, 'BAI03': 4, 'BAI04': 5, 'BAI05': 5, 'BAI06': 4, 'BAI07': 5, 'BAI08': 5, 'BAI09': 5, 'BAI10': 4, 'BAI11': 5,
            'DSS01': 5, 'DSS02': 5, 'DSS03': 5, 'DSS04': 4, 'DSS05': 5, 'DSS06': 5,
            'MEA01': 5, 'MEA02': 5, 'MEA03': 5, 'MEA04': 4
        },
        defaultMax: 5,
        // Styling Configuration
        colors: {
            bg:   ['#ffebee', '#fff3e0', '#fff8e1', '#e8f5e9', '#e3f2fd', '#f3e5f5'],
            text: ['#c62828', '#ef6c00', '#f57f17', '#2e7d32', '#1565c0', '#6a1b9a']
        }
    };

    // 2. Data Provider (Single Source of Truth from Server)
    const AppData = {
        objectives: @json($objectives),
        targetMap: @json($targetCapabilityMap ?? []),
        calculatedLevels: @json($calculatedLevels ?? []) // Generated server-side
    };

    // 3. Utilities (Pure Functions)
    const Utils = {
        escape: s => (s || '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c])),
        parseNum: v => {
            const n = Number(v);
            return (v === null || v === undefined || v === '' || isNaN(n)) ? null : n;
        },
        fmt: n => (n || 0).toFixed(2),
        getRating: lvl => (lvl > 0 ? `${lvl} F` : '0 N'),
        getLink: id => `${Config.assessmentUrl}#objective-${id}`
    };

    // 4. Core Application Logic
    const ReportApp = {
        init() {
            document.addEventListener('DOMContentLoaded', () => {
                try {
                    const data = this.processData();
                    this.render(data);
                } catch (e) {
                    console.error('Report App Error:', e);
                    const tbody = document.getElementById('recap-table-body');
                    if(tbody) tbody.innerHTML = `<tr><td colspan="9" class="text-center text-danger py-3">Failed to load report: ${Utils.escape(e.message)}</td></tr>`;
                }
            });
        },

        processData() {
            if (!AppData.objectives || !AppData.objectives.length) return [];

            return AppData.objectives.map((obj, i) => {
                const id = obj.objective_id;
                const level = AppData.calculatedLevels[id] || 0;
                const target = Utils.parseNum(AppData.targetMap[id]);
                const max = Config.maxLevels[id] || Config.defaultMax;
                const gap = target !== null ? (level - target) : null;

                // Pre-calculate styling to keep render logic clean
                let gapClass = 'text-muted';
                if (gap !== null) {
                    gapClass = gap < 0 ? 'text-danger fw-bold' : (gap > 0 ? 'text-success fw-bold' : 'text-dark fw-bold');
                }

                return {
                    index: i + 1,
                    domain: id.substring(0, 3),
                    id: id,
                    name: obj.objective || obj.description,
                    level: level,
                    rating: Utils.getRating(level),
                    target: target,
                    max: max,
                    gap: gap,
                    style: {
                        bg: Config.colors.bg[level] || '#f8f9fa',
                        color: Config.colors.text[level] || '#6c757d',
                        gapClass: gapClass
                    }
                };
            });
        },

        render(rows) {
            this.renderBody(rows);
            this.renderFooter(rows);
        },

        renderBody(rows) {
            const tbody = document.getElementById('recap-table-body');
            if (!tbody) return;

            if (!rows.length) {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-muted">Tidak ada data objective tersedia.</td></tr>';
                return;
            }

            // Using array join for faster DOM insertion
            tbody.innerHTML = rows.map(r => `
                <tr>
                    <td class="text-center fw-semibold">${r.index}</td>
                    <td class="fw-bold text-secondary">${Utils.escape(r.domain)}</td>
                    <td>
                        <a href="${Utils.getLink(r.id)}" class="text-decoration-none fw-bold text-primary report-link">
                            ${Utils.escape(r.id)}
                        </a>
                    </td>
                    <td><span class="small text-muted">${Utils.escape(r.name)}</span></td>
                    <td class="text-center fw-bold" style="background-color: ${r.style.bg}; color: ${r.style.color};">${r.level}</td>
                    <td class="text-center fw-bold text-dark">${r.rating}</td>
                    <td class="text-center text-muted">${r.target !== null ? r.target : '-'}</td>
                    <td class="text-center"><div class="gap-box ${r.style.gapClass}">${r.gap !== null ? r.gap : '-'}</div></td>
                    <td class="text-center fw-bold text-secondary">${r.max}</td>
                </tr>
            `).join('');
        },

        renderFooter(rows) {
            const tfoot = document.getElementById('recap-table-footer');
            if (!tfoot || !rows.length) return;

            const total = rows.length;
            const avgScore = rows.reduce((s, r) => s + r.level, 0) / total;
            const avgMax = rows.reduce((s, r) => s + r.max, 0) / total;

            const validTargets = rows.filter(r => r.target !== null);
            const avgTarget = validTargets.length ? (validTargets.reduce((s, r) => s + r.target, 0) / validTargets.length) : null;

            tfoot.innerHTML = `
                <tr class="table-light fw-bold border-top-2">
                    <td colspan="4" class="text-end pe-3">Average Maturity Score</td>
                    <td class="text-center bg-primary text-white">${Utils.fmt(avgScore)}</td>
                    <td class="bg-light"></td>
                    <td class="text-center bg-info text-white">${avgTarget !== null ? Utils.fmt(avgTarget) : '-'}</td>
                    <td class="bg-light"></td>
                    <td class="text-center bg-secondary text-white">${Utils.fmt(avgMax)}</td>
                </tr>
            `;
        }
    };

    // Run App
    ReportApp.init();

})();
</script>
@endsection
