@extends('layouts.app')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php($displayEvalId = $evaluation->eval_id)
    @php($routeEvalId = $evaluation->encrypted_id)

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
                            Assessment Id: {{ $displayEvalId }}
                        </div>
                        <div class="hero-eval-year text-uppercase"
                            style="font-size:0.95rem;font-weight:600;color:rgba(255,255,255,0.75);letter-spacing:0.06em;">
                            Assessment Year:
                            {{ $evaluation->year ?? ($evaluation->assessment_year ?? ($evaluation->tahun ?? 'N/A')) }}
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('assessment.show', $routeEvalId) }}"
                            class="btn btn-light btn-sm rounded-pill px-3">
                            <i class="fas fa-arrow-left me-2"></i>Back to Assessment
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Report Result Section --}}
        <div class="card shadow-sm border-0 mb-4" id="report-result-card">
            <div class="card-header bg-white py-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('assessment.report', $routeEvalId) }}"
                                    class="text-decoration-none {{ Route::currentRouteName() == 'assessment.report' ? 'active-tab text-primary fw-bold' : '' }}"
                                    style="{{ Route::currentRouteName() != 'assessment.report' ? 'color: #0f2b5c;' : '' }}">
                                    <i class="fas fa-file-alt me-1"></i> Assessment Recapitulation Report
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('assessment.note', $routeEvalId) }}"
                                    class="text-decoration-none {{ Route::currentRouteName() == 'assessment.note' ? 'active-tab text-primary fw-bold' : '' }}"
                                    style="{{ Route::currentRouteName() != 'assessment.note' ? 'color: #0f2b5c;' : '' }}">
                                    <i class="fas fa-clipboard-list me-1"></i> Summary
                                </a>
                            </li>
                        </ol>
                    </nav>
                    <div>
                        <button class="btn btn-sm btn-danger" type="button" id="btn-export-recap-menu">
                            <i class="fas fa-file-export me-1"></i>Export
                        </button>
                    </div>
                </div>
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
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.2/dist/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/pptxgenjs@3.12.0/dist/pptxgen.bundle.js"></script>
    <script>
        /**
         * Multi-Scope Assessment Report
         * Shows all 40 GAMOs with maturity data for each scope
         */
        (function() {
            'use strict';

            // Configuration
            const Config = {
                assessmentUrl: "{{ route('assessment.show', $routeEvalId) }}",
                routeEvalId: "{{ $routeEvalId }}",
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
                getLink: id => `${Config.assessmentUrl}#objective-${id}`,
                getDomain: id => (id || '').replace(/\d+/g, ''),
                formatGap: n => {
                    if (n === null || n === undefined || isNaN(Number(n))) return '-';
                    const num = Number(n);
                    return `${num > 0 ? '+' : ''}${num.toFixed(2)}`;
                },
                scoreToRating: score => {
                    const val = Number(score) || 0;
                    if (val <= 0) return '0N';
                    const level = Math.floor(val);
                    const fraction = val - level;
                    if (fraction >= 0.67) return `${level}F`;
                    if (fraction >= 0.34) return `${level}L`;
                    if (fraction > 0) return `${level}P`;
                    return `${level}N`;
                },
                getScorePalette: score => {
                    const idx = Math.max(0, Math.min(5, Math.floor(Number(score) || 0)));
                    return {
                        bg: Config.colors.bg[idx] || '#f8f9fa',
                        text: Config.colors.text[idx] || '#374151'
                    };
                }
            };

            const ReportApp = {
                init() {
                    document.addEventListener('DOMContentLoaded', () => {
                        try {
                            this.render();
                            this.bindExportEvents();
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

                bindExportEvents() {
                    const menuBtn = document.getElementById('btn-export-recap-menu');
                    const jpgBtn = document.getElementById('btn-export-recap-jpg');
                    const pdfBtn = document.getElementById('btn-export-recap-pdf');
                    const allBtn = document.getElementById('btn-export-recap-all');

                    if (menuBtn) {
                        menuBtn.addEventListener('click', () => this.openExportMenu());
                    }

                    if (jpgBtn) {
                        jpgBtn.addEventListener('click', () => this.exportRecapAsJpg());
                    }

                    if (pdfBtn) {
                        pdfBtn.addEventListener('click', () => this.exportRecapAsPdf());
                    }

                    if (allBtn) {
                        allBtn.addEventListener('click', () => {
                            window.open("{{ route('assessment.summary-pdf', ['evalId' => $routeEvalId]) }}", '_blank');
                        });
                    }
                },

                openExportMenu() {
                    Swal.fire({
                        title: 'Pilih Jenis Export',
                        html: `
                            <div class="d-grid gap-2 mb-3">
                                <button type="button" class="btn btn-outline-secondary" id="swal-export-jpg">
                                    <i class="fas fa-image me-2"></i>Export JPG
                                </button>
                                <button type="button" class="btn btn-danger" id="swal-export-pdf">
                                    <i class="fas fa-file-pdf me-2"></i>Export PDF
                                </button>
                                <button type="button" class="btn btn-success" id="swal-export-excel">
                                    <i class="fas fa-file-excel me-2"></i>Export Excel
                                </button>
                                <button type="button" class="btn btn-warning text-white" id="swal-export-ppt">
                                    <i class="fas fa-file-powerpoint me-2"></i>Export All (PPT)
                                </button>
                                <button type="button" class="btn btn-primary" id="swal-export-all">
                                    <i class="fas fa-layer-group me-2"></i>Export All
                                </button>
                            </div>
                            <div class="form-check text-start">
                                <input class="form-check-input" type="checkbox" id="swal-include-roadmap" checked>
                                <label class="form-check-label" style="font-size: 0.9rem;" for="swal-include-roadmap">
                                    Include Roadmap Target Capability (Export All)
                                </label>
                            </div>
                        `,
                        showConfirmButton: false,
                        showCloseButton: true,
                        didOpen: () => {
                            const jpgOption = document.getElementById('swal-export-jpg');
                            const pdfOption = document.getElementById('swal-export-pdf');
                            const excelOption = document.getElementById('swal-export-excel');
                            const pptOption = document.getElementById('swal-export-ppt');
                            const allOption = document.getElementById('swal-export-all');

                            if (jpgOption) {
                                jpgOption.addEventListener('click', () => {
                                    Swal.close();
                                    this.exportRecapAsJpg();
                                });
                            }

                            if (pdfOption) {
                                pdfOption.addEventListener('click', () => {
                                    Swal.close();
                                    this.exportRecapAsPdf();
                                });
                            }

                            if (excelOption) {
                                excelOption.addEventListener('click', () => {
                                    Swal.close();
                                    this.exportRecapAsExcel();
                                });
                            }

                            if (pptOption) {
                                pptOption.addEventListener('click', () => {
                                    Swal.close();
                                    this.exportRecapAsPpt();
                                });
                            }

                            if (allOption) {
                                allOption.addEventListener('click', () => {
                                    const includeRoadmap = document.getElementById('swal-include-roadmap').checked ? 1 : 0;
                                    Swal.close();
                                    window.open("{{ route('assessment.summary-pdf', ['evalId' => $routeEvalId]) }}?include_roadmap=" + includeRoadmap, '_blank');
                                });
                            }
                        }
                    });
                },

                buildRecapExportRows() {
                    const rows = [];

                    AppData.objectives.forEach((obj, index) => {
                        const objectiveId = obj.objective_id;
                        const objectiveName = obj.objective || '-';
                        const domain = Utils.getDomain(objectiveId);
                        const maxLevel = Config.maxLevels[objectiveId] || Config.defaultMax;

                        const scopeScores = AppData.allScopes
                            .map(scope => AppData.scopeMaturityData?.[scope.id]?.[objectiveId])
                            .filter(v => v !== null && v !== undefined);

                        const score = scopeScores.length
                            ? (scopeScores.reduce((sum, v) => sum + Number(v), 0) / scopeScores.length)
                            : 0;

                        const objectiveTargetRaw = AppData.targetMap?.[objectiveId];
                        const target = (objectiveTargetRaw !== null && objectiveTargetRaw !== undefined)
                            ? Number(objectiveTargetRaw)
                            : (AppData.targetMaturity !== null && AppData.targetMaturity !== undefined ? Number(AppData.targetMaturity) : null);

                        const gap = target === null ? null : (score - target);

                        rows.push({
                            no: index + 1,
                            domain,
                            gamo: objectiveId,
                            gamoName: objectiveName,
                            score: Utils.fmt(score),
                            value: Utils.fmt(score),
                            rating: Utils.scoreToRating(score),
                            target: target === null ? '-' : Utils.fmt(target),
                            gap: Utils.formatGap(gap),
                            maxLevel: String(maxLevel),
                            actionUrl: `/assessment/${Config.routeEvalId}/report-activity/${objectiveId}`,
                            summaryUrl: `/assessment/${Config.routeEvalId}/summary/${objectiveId}`
                        });
                    });

                    return rows;
                },

                buildRecapSummary(rows) {
                    const numericScores = rows.map(r => Number(r.score)).filter(v => !isNaN(v));
                    const avgScore = numericScores.length
                        ? (numericScores.reduce((sum, v) => sum + v, 0) / numericScores.length)
                        : 0;

                    const numericMax = rows.map(r => Number(r.maxLevel)).filter(v => !isNaN(v));
                    const avgMax = numericMax.length
                        ? (numericMax.reduce((sum, v) => sum + v, 0) / numericMax.length)
                        : 0;

                    const target = (AppData.targetMaturity === null || AppData.targetMaturity === undefined)
                        ? null
                        : Number(AppData.targetMaturity);

                    const gap = target === null ? null : (avgScore - target);

                    return {
                        score: Utils.fmt(avgScore),
                        target: target === null ? '-' : Utils.fmt(target),
                        gap: Utils.formatGap(gap),
                        maxLevel: Utils.fmt(avgMax)
                    };
                },

                createExportTableHtml(rows) {
                    const summary = this.buildRecapSummary(rows);
                    const headerHtml = `
                        <tr>
                            <th style="padding:6px;border:1px solid #000;background:#f3f4f6;">No</th>
                            <th style="padding:6px;border:1px solid #000;background:#f3f4f6;">Domain</th>
                            <th style="padding:6px;border:1px solid #000;background:#f3f4f6;">GAMO</th>
                            <th style="padding:6px;border:1px solid #000;background:#f3f4f6;">GAMO Name</th>
                            <th style="padding:6px;border:1px solid #000;background:#f3f4f6;">Score</th>
                            <th style="padding:6px;border:1px solid #000;background:#f3f4f6;">Value</th>
                            <th style="padding:6px;border:1px solid #000;background:#f3f4f6;">Rating</th>
                            <th style="padding:6px;border:1px solid #000;background:#f3f4f6;">Target</th>
                            <th style="padding:6px;border:1px solid #000;background:#f3f4f6;">Gap</th>
                            <th style="padding:6px;border:1px solid #000;background:#f3f4f6;">Max Level</th>
                        </tr>
                    `;

                    const rowsHtml = rows.map(row => `
                        ${(() => {
                            const palette = Utils.getScorePalette(row.score);
                            return `
                        <tr>
                            <td style="padding:6px;border:1px solid #000;text-align:center;">${row.no}</td>
                            <td style="padding:6px;border:1px solid #000;text-align:center;">${Utils.escape(row.domain)}</td>
                            <td style="padding:6px;border:1px solid #000;text-align:center;">${Utils.escape(row.gamo)}</td>
                            <td style="padding:6px;border:1px solid #000;">${Utils.escape(row.gamoName)}</td>
                            <td style="padding:6px;border:1px solid #000;text-align:center;background:${palette.bg};color:${palette.text};font-weight:700;">${row.score}</td>
                            <td style="padding:6px;border:1px solid #000;text-align:center;">${row.value}</td>
                            <td style="padding:6px;border:1px solid #000;text-align:center;">${Utils.escape(row.rating)}</td>
                            <td style="padding:6px;border:1px solid #000;text-align:center;">${Utils.escape(row.target)}</td>
                            <td style="padding:6px;border:1px solid #000;text-align:center;">${Utils.escape(row.gap)}</td>
                            <td style="padding:6px;border:1px solid #000;text-align:center;">${Utils.escape(row.maxLevel)}</td>
                        </tr>
                    `;
                        })()}
                    `).join('');

                    const summaryHtml = `
                        <tr>
                            <td colspan="4" style="padding:6px;border:1px solid #000;text-align:right;font-weight:700;">I&T Maturity Score</td>
                            <td style="padding:6px;border:1px solid #000;text-align:center;background:#0b6da8;color:#fff;font-weight:700;">${summary.score}</td>
                            <td style="padding:6px;border:1px solid #000;text-align:center;">-</td>
                            <td style="padding:6px;border:1px solid #000;text-align:center;">-</td>
                            <td style="padding:6px;border:1px solid #000;text-align:center;background:#22b8cf;color:#fff;font-weight:700;">${summary.target}</td>
                            <td style="padding:6px;border:1px solid #000;text-align:center;">${summary.gap}</td>
                            <td style="padding:6px;border:1px solid #000;text-align:center;background:#6c757d;color:#fff;font-weight:700;">${summary.maxLevel}</td>
                        </tr>
                    `;

                    return `
                        <div style="background:#fff;padding:16px;width:1400px;">
                            <h3 style="margin:0 0 10px 0;color:#0f2b5c;">Assessment Recapitulation Result</h3>
                            <table style="border-collapse:collapse;width:100%;font-family:Arial,sans-serif;font-size:12px;">
                                <thead>${headerHtml}</thead>
                                <tbody>${rowsHtml}</tbody>
                                <tfoot>${summaryHtml}</tfoot>
                            </table>
                        </div>
                    `;
                },

                async exportRecapAsJpg() {
                    if (typeof html2canvas === 'undefined') {
                        Swal.fire('Error', 'Library JPG belum termuat. Coba refresh halaman.', 'error');
                        return;
                    }

                    const rows = this.buildRecapExportRows();
                    if (!rows.length) {
                        Swal.fire('Info', 'Tidak ada data untuk diexport.', 'info');
                        return;
                    }

                    const wrapper = document.createElement('div');
                    wrapper.style.position = 'fixed';
                    wrapper.style.left = '-99999px';
                    wrapper.style.top = '0';
                    wrapper.innerHTML = this.createExportTableHtml(rows);
                    document.body.appendChild(wrapper);

                    try {
                        const canvas = await html2canvas(wrapper.firstElementChild, {
                            scale: 2,
                            backgroundColor: '#ffffff',
                            useCORS: true
                        });

                        const link = document.createElement('a');
                        link.href = canvas.toDataURL('image/jpeg', 0.95);
                        link.download = `assessment_recapitulation_result_${new Date().toISOString().slice(0, 10)}.jpg`;
                        link.click();
                    } catch (error) {
                        console.error('Export JPG failed:', error);
                        Swal.fire('Error', 'Gagal export JPG.', 'error');
                    } finally {
                        document.body.removeChild(wrapper);
                    }
                },

                exportRecapAsPdf() {
                    if (typeof window.jspdf === 'undefined') {
                        Swal.fire('Error', 'Library PDF belum termuat. Coba refresh halaman.', 'error');
                        return;
                    }

                    const rows = this.buildRecapExportRows();
                    const summary = this.buildRecapSummary(rows);
                    if (!rows.length) {
                        Swal.fire('Info', 'Tidak ada data untuk diexport.', 'info');
                        return;
                    }

                    try {
                        const { jsPDF } = window.jspdf;
                        const doc = new jsPDF({ orientation: 'landscape', unit: 'pt', format: 'a4' });

                        const head = [[
                            'No', 'Domain', 'GAMO', 'GAMO Name', 'Score', 'Value', 'Rating', 'Target', 'Gap', 'Max Level'
                        ]];
                        const body = rows.map(row => [
                            row.no,
                            row.domain,
                            row.gamo,
                            row.gamoName,
                            row.score,
                            row.value,
                            row.rating,
                            row.target,
                            row.gap,
                            row.maxLevel
                        ]);

                        body.push([
                            '',
                            '',
                            '',
                            'I&T Maturity Score',
                            summary.score,
                            '-',
                            '-',
                            summary.target,
                            summary.gap,
                            summary.maxLevel
                        ]);

                        doc.setFontSize(13);
                        doc.text('Assessment Recapitulation Result', 40, 30);

                        doc.autoTable({
                            head,
                            body,
                            startY: 42,
                            styles: { fontSize: 8, cellPadding: 4 },
                            headStyles: { fillColor: [15, 43, 92] },
                            columnStyles: {
                                0: { halign: 'center', cellWidth: 30 },
                                1: { halign: 'center', cellWidth: 45 },
                                2: { halign: 'center', cellWidth: 55 },
                                3: { cellWidth: 190 },
                                4: { halign: 'center', cellWidth: 48 },
                                5: { halign: 'center', cellWidth: 48 },
                                6: { halign: 'center', cellWidth: 48 },
                                7: { halign: 'center', cellWidth: 48 },
                                8: { halign: 'center', cellWidth: 48 },
                                9: { halign: 'center', cellWidth: 55 }
                            },
                            didParseCell: function(data) {
                                if (data.section === 'body' && data.row.index === body.length - 1) {
                                    if (data.column.index === 4) {
                                        data.cell.styles.fillColor = [11, 109, 168];
                                        data.cell.styles.textColor = [255, 255, 255];
                                        data.cell.styles.fontStyle = 'bold';
                                    }
                                    if (data.column.index === 7) {
                                        data.cell.styles.fillColor = [34, 184, 207];
                                        data.cell.styles.textColor = [255, 255, 255];
                                        data.cell.styles.fontStyle = 'bold';
                                    }
                                    if (data.column.index === 9) {
                                        data.cell.styles.fillColor = [108, 117, 125];
                                        data.cell.styles.textColor = [255, 255, 255];
                                        data.cell.styles.fontStyle = 'bold';
                                    }
                                    if (data.column.index === 3) {
                                        data.cell.styles.fontStyle = 'bold';
                                        data.cell.styles.halign = 'right';
                                    }
                            }
                            }
                        });

                        doc.save(`assessment_recapitulation_result_${new Date().toISOString().slice(0, 10)}.pdf`);
                    } catch (error) {
                        console.error('Export PDF failed:', error);
                        Swal.fire('Error', 'Gagal export PDF.', 'error');
                    }
                },

                async exportRecapAsPpt() {
                    const includeRoadmap = document.getElementById('swal-include-roadmap') ? (document.getElementById('swal-include-roadmap').checked ? 1 : 0) : 1;
                    
                    Swal.fire({
                        title: 'Generating PPT...',
                        text: 'Memproses data semua GAMO, harap tunggu...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    try {
                        const response = await fetch(`{{ route('assessment.summary-json', ['evalId' => $routeEvalId]) }}?include_roadmap=${includeRoadmap}`);
                        if (!response.ok) throw new Error('Network response was not ok');
                        const data = await response.json();

                        console.log('PPT data received:', data);

                        let pptx = new PptxGenJS();
                        pptx.layout = 'LAYOUT_16x9';

                        const objectives = Array.isArray(data.objectives) ? data.objectives : [];
                        if (objectives.length === 0) {
                            Swal.fire('Info', 'Tidak ada data assessment untuk diexport.', 'info');
                            return;
                        }

                        // === Slide dimensions: 16:9 = 13.33" x 7.5" ===
                        // Margins: 0.2" each side → usable width = 12.93"
                        // Layout:
                        //   Title:     y=0.15, h=0.40  → ends 0.55
                        //   Scorecard: y=0.60, h=0.50  → ends 1.10
                        //   Content:   y=1.15           → available to 7.30 = 6.15" total
                        //   7 sections: header(0.22) + content(0.56) = 0.78 each
                        //   7 × 0.78 = 5.46 → fits in 6.15

                        const SLIDE_W = 12.93;
                        const MARGIN_X = 0.2;
                        const SEC_HEADER_H = 0.22;
                        const SEC_CONTENT_H = 0.56;
                        const SEC_TOTAL = SEC_HEADER_H + SEC_CONTENT_H; // 0.78
                        const CONTENT_START_Y = 1.15;

                        objectives.forEach((obj, idx) => {
                            let slide = pptx.addSlide();

                            // Title
                            slide.addText(
                                String(obj.objective_id || '') + ' - ' + String(obj.objective || ''),
                                { x:MARGIN_X, y:0.15, w:SLIDE_W, h:0.40, fill:{color:'0f2b5c'}, color:'ffffff', bold:true, fontSize:13, align:'left', valign:'middle' }
                            );

                            // Scorecard table
                            let scoreRows = [
                                [
                                    { text: 'Capability Level', options: { fill:{color:'9b59b6'}, color:'ffffff', bold:true, align:'center', fontSize:9 } },
                                    { text: 'Max Level', options: { fill:{color:'9b59b6'}, color:'ffffff', bold:true, align:'center', fontSize:9 } },
                                    { text: 'Rating', options: { fill:{color:'9b59b6'}, color:'ffffff', bold:true, align:'center', fontSize:9 } },
                                    { text: 'Capability Target', options: { fill:{color:'9b59b6'}, color:'ffffff', bold:true, align:'center', fontSize:9 } }
                                ],
                                [
                                    { text: String(obj.current_score != null ? obj.current_score : '-'), options: { align:'center', bold:true, fontSize:10 } },
                                    { text: String(obj.max_level != null ? obj.max_level : '-'), options: { align:'center', bold:true, fontSize:10 } },
                                    { text: String(obj.rating_string || '-'), options: { align:'center', bold:true, fontSize:10 } },
                                    { text: String(obj.target_level == 0 || obj.target_level == null ? '-' : obj.target_level), options: { align:'center', bold:true, fontSize:10 } }
                                ]
                            ];
                            slide.addTable(scoreRows, {
                                x: MARGIN_X, y: 0.60, w: SLIDE_W,
                                rowH: [0.25, 0.25],
                                colW: [3.23, 3.23, 3.23, 3.23],
                                border: { type:'solid', pt:1, color:'000000' },
                                margin: [2, 2, 2, 2]
                            });

                            // Build sections data
                            let practicesArr = Array.isArray(obj.practices) ? obj.practices : [];
                            let practicesText = practicesArr.length > 0
                                ? practicesArr.map(p => '• ' + String(p.practice_id || '').replace(/"/g, '') + ' - ' + String(p.practice_name || '').replace(/"/g, '')).join('\n')
                                : 'No management practices available.';

                            let policyArr = Array.isArray(obj.policy_list) ? obj.policy_list : [];
                            let policyText = policyArr.length > 0 ? policyArr.map(p => '• ' + p).join('\n') : 'Belum ada Kebijakan / Prosedur';

                            let evidenceArr = Array.isArray(obj.execution_list) ? obj.execution_list : [];
                            let evidenceText = evidenceArr.length > 0 ? evidenceArr.map(p => '• ' + p).join('\n') : 'Belum ada Evidences / Bukti Pelaksanaan';

                            let kesimpulanText = (obj.saved_note && obj.saved_note.kesimpulan) ? String(obj.saved_note.kesimpulan).trim() : 'Belum ada kesimpulan';
                            let rekomendasiText = (obj.saved_note && obj.saved_note.rekomendasi) ? String(obj.saved_note.rekomendasi).trim() : 'Belum ada rekomendasi';

                            const sections = [
                                { title: 'Description', content: obj.objective_description || 'No description available.' },
                                { title: 'Purpose', content: obj.objective_purpose || 'No purpose available.' },
                                { title: 'Management Practices', content: practicesText },
                                { title: 'Kebijakan Pedoman / Prosedur', content: policyText },
                                { title: 'Evidences / Bukti Pelaksanaan', content: evidenceText },
                                { title: 'Kesimpulan', content: kesimpulanText },
                                { title: 'Rekomendasi', content: rekomendasiText }
                            ];

                            // Render each section with fixed height
                            let yPos = CONTENT_START_Y;
                            sections.forEach(sec => {
                                // Section header
                                slide.addText(sec.title, {
                                    x: MARGIN_X, y: yPos, w: SLIDE_W, h: SEC_HEADER_H,
                                    fill:{color:'0f2b5c'}, color:'ffffff', bold:true, fontSize:8,
                                    valign:'middle', margin:[0,4,0,4]
                                });
                                yPos += SEC_HEADER_H;

                                // Section content (with shrinkText to auto-fit)
                                slide.addText(String(sec.content || '-'), {
                                    x: MARGIN_X, y: yPos, w: SLIDE_W, h: SEC_CONTENT_H,
                                    fontSize: 8, color:'333333', valign:'top',
                                    margin:[2,4,2,4], shrinkText: true
                                });
                                yPos += SEC_CONTENT_H;
                            });
                        });
                        
                        // Roadmap slide
                        const roadmapObjs = (data.roadmap && Array.isArray(data.roadmap.objectives)) ? data.roadmap.objectives : [];
                        const roadmapYears = (data.roadmap && Array.isArray(data.roadmap.years)) ? data.roadmap.years : [];
                        
                        if (data.includeRoadmap && roadmapObjs.length > 0) {
                            let slide = pptx.addSlide();
                            slide.addText('Roadmap Target Capability', { x:0.2, y:0.2, w:12.93, fontSize:14, bold:true, color:'0f2b5c' });

                            // Build flat header row (no colspan/rowspan)
                            let headerRow = [
                                { text: 'Obj ID', options: { fill:{color:'0f2b5c'}, color:'ffffff', bold:true, align:'center', fontSize:7 } },
                                { text: 'Objective Name', options: { fill:{color:'0f2b5c'}, color:'ffffff', bold:true, align:'center', fontSize:7 } },
                                { text: 'Level', options: { fill:{color:'0f2b5c'}, color:'ffffff', bold:true, align:'center', fontSize:7 } },
                                { text: 'Rating', options: { fill:{color:'0f2b5c'}, color:'ffffff', bold:true, align:'center', fontSize:7 } }
                            ];
                            roadmapYears.forEach(yr => {
                                headerRow.push({ text: 'Lvl ' + yr, options: { fill:{color:'0f2b5c'}, color:'ffffff', bold:true, align:'center', fontSize:7 } });
                                headerRow.push({ text: 'Rtg ' + yr, options: { fill:{color:'0f2b5c'}, color:'ffffff', bold:true, align:'center', fontSize:7 } });
                            });

                            let tableRows = [headerRow];

                            roadmapObjs.forEach(rObj => {
                                let sObj = objectives.find(o => o.objective_id === rObj.objective_id);
                                let row = [
                                    { text: String(rObj.objective_id || ''), options: { align:'center', fontSize:7 } },
                                    { text: String(rObj.objective || ''), options: { align:'left', fontSize:7 } },
                                    { text: String(sObj ? (sObj.current_score || '-') : '-'), options: { align:'center', fontSize:7 } },
                                    { text: String(sObj ? (sObj.rating_string || '-') : '-'), options: { align:'center', fontSize:7 } }
                                ];
                                roadmapYears.forEach(yr => {
                                    let rv = rObj.roadmap_values;
                                    let val = (rv && rv[yr]) ? rv[yr] : null;
                                    row.push({ text: String(val ? (val.level || '-') : '-'), options: { align:'center', fontSize:7 } });
                                    row.push({ text: String(val ? (val.rating || '-') : '-'), options: { align:'center', fontSize:7 } });
                                });
                                tableRows.push(row);
                            });

                            slide.addTable(tableRows, {
                                x: 0.2, y: 0.6, w: 12.93,
                                border: { type:'solid', pt:1, color:'000000' },
                                margin: [2, 2, 2, 2],
                                fontSize: 7
                            });
                        }

                        await pptx.writeFile({ fileName: 'Export_All_' + Config.routeEvalId + '_' + new Date().toISOString().slice(0, 10) + '.pptx' });
                        Swal.close();
                        Swal.fire('Success', 'Berhasil export PPT!', 'success');

                    } catch (error) {
                        console.error('Failed to generate PPT', error);
                        Swal.fire('Error', 'Gagal memuat data untuk PPT: ' + error.message, 'error');
                    }
                },

                exportRecapAsExcel() {
                    if (typeof XLSX === 'undefined') {
                        Swal.fire('Error', 'Library Excel belum termuat. Coba refresh halaman.', 'error');
                        return;
                    }

                    const rows = this.buildRecapExportRows();
                    if (!rows.length) {
                        Swal.fire('Info', 'Tidak ada data untuk diexport.', 'info');
                        return;
                    }

                    const summary = this.buildRecapSummary(rows);
                    const exportRows = rows.map((row) => ({
                        No: row.no,
                        Domain: row.domain,
                        GAMO: row.gamo,
                        'GAMO Name': row.gamoName,
                        Score: row.score,
                        Value: row.value,
                        Rating: row.rating,
                        Target: row.target,
                        Gap: row.gap,
                        'Max Level': row.maxLevel
                    }));

                    exportRows.push({
                        No: '',
                        Domain: '',
                        GAMO: '',
                        'GAMO Name': 'I&T Maturity Score',
                        Score: summary.score,
                        Value: '-',
                        Rating: '-',
                        Target: summary.target,
                        Gap: summary.gap,
                        'Max Level': summary.maxLevel
                    });

                    const ws = XLSX.utils.json_to_sheet(exportRows);
                    const wb = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(wb, ws, 'Recapitulation');
                    XLSX.writeFile(wb, `assessment_recapitulation_result_${new Date().toISOString().slice(0, 10)}.xlsx`);
                },

                renderHeaders() {
                    const thead = document.querySelector('#recap-table thead');
                    if (!thead) return;

                    const html = `
                <tr>
                    <th style="width:50px;" class="text-center">No</th>
                    <th style="width:90px;">Domain</th>
                    <th style="width:90px;">GAMO</th>
                    <th>GAMO Name</th>
                    <th style="width:90px;" class="text-center">Score</th>
                    <th style="width:90px;" class="text-center">Value</th>
                    <th style="width:90px;" class="text-center">Rating</th>
                    <th style="width:90px;" class="text-center">Target</th>
                    <th style="width:90px;" class="text-center">Gap</th>
                    <th style="width:90px;" class="text-center">Max Level</th>
                    <th style="width:120px;" class="text-center">Action</th>
                </tr>`;

                    thead.innerHTML = html;
                },

                renderBody() {
                    const tbody = document.getElementById('recap-table-body');
                    if (!tbody) return;

                    if (!AppData.objectives || !AppData.objectives.length) {
                        tbody.innerHTML =
                            `<tr><td colspan="11" class="text-center py-4 text-muted">No data available.</td></tr>`;
                        return;
                    }

                    let html = '';
                    const rows = this.buildRecapExportRows();
                    rows.forEach((row) => {
                        const palette = Utils.getScorePalette(row.score);

                        html += `
                    <tr>
                        <td class="text-center fw-semibold">${row.no}</td>
                        <td class="fw-semibold">${Utils.escape(row.domain)}</td>
                        <td>
                            <a href="${Utils.getLink(row.gamo)}" class="text-decoration-none fw-bold text-primary">
                                ${Utils.escape(row.gamo)}
                            </a>
                        </td>
                        <td><span class="small text-muted">${Utils.escape(row.gamoName)}</span></td>
                        <td class="text-center fw-bold" style="background-color: ${palette.bg}; color: ${palette.text};">${row.score}</td>
                        <td class="text-center fw-semibold">${row.value}</td>
                        <td class="text-center fw-semibold">${row.rating}</td>
                        <td class="text-center fw-semibold">${row.target}</td>
                        <td class="text-center fw-semibold">${row.gap}</td>
                        <td class="text-center fw-bold text-secondary">${row.maxLevel}</td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <a href="${row.actionUrl}" 
                                   class="btn btn-xs btn-outline-primary rounded-pill py-1 px-3 me-1" 
                                   style="font-size: 0.7rem; font-weight: 600;">
                                    Detail
                                </a>
                                <a href="${row.summaryUrl}" 
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

                    const rows = this.buildRecapExportRows();
                    const summary = this.buildRecapSummary(rows);

                    const html = `
                        <tr class="table-light fw-bold border-top-2">
                            <td colspan="4" class="text-end pe-3">I&T Maturity Score</td>
                            <td class="text-center bg-primary text-white">${summary.score}</td>
                            <td class="text-center bg-light text-dark">-</td>
                            <td class="text-center bg-light text-dark">-</td>
                            <td class="text-center bg-info text-white">${summary.target}</td>
                            <td class="text-center bg-light text-dark">${summary.gap}</td>
                            <td class="text-center bg-secondary text-white">${summary.maxLevel}</td>
                            <td class="text-center bg-light text-dark">-</td>
                        </tr>
                    `;

                    tfoot.innerHTML = html;
                }
            };

            ReportApp.init();

        })();
    </script>
@endsection
