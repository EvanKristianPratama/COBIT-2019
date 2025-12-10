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
                </div>
                <div>
                    <a href="{{ route('assessment-eval.show', $evalId) }}" class="btn btn-light btn-sm rounded-pill px-3">
                        <i class="fas fa-arrow-left me-2"></i>Back to Assessment
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Section (diubah menjadi tabel) --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold text-primary">Pilih Objectives (GAMO)</h5>
                <p class="text-muted small mb-0">Centang objective yang ingin dimasukkan ke laporan maturity.</p>
            </div>
            <div class="text-end">
                <button type="button" class="btn btn-sm btn-outline-secondary me-2" id="btn-deselect-all">Deselect All</button>
                <button type="button" class="btn btn-sm btn-outline-primary" id="btn-select-all">Select All</button>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm" id="gamo-table">
                    <thead class="bg-light align-middle">
                        <tr>
                            <th style="width:40px;" class="text-center">
                                <input type="checkbox" id="check-all" title="Select all">
                            </th>
                            <th style="width:90px;">Domain</th>
                            <th style="width:140px;">Objective</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $domains = ['EDM','APO','BAI','DSS','MEA'];
                            // group by first 3 chars (domain)
                            $grouped = $objectives->groupBy(function($item) {
                                return substr($item->objective_id, 0, 3);
                            });
                        @endphp

                        @foreach($domains as $domain)
                            @if(isset($grouped[$domain]))
                                @foreach($grouped[$domain] as $obj)
                                    <tr>
                                        <td class="text-center align-middle">
                                            <input class="form-check-input gamo-checkbox" type="checkbox"
                                                   value="{{ $obj->objective_id }}"
                                                   id="check-{{ $obj->objective_id }}" checked>
                                        </td>
                                        <td class="align-middle fw-bold">{{ $domain }}</td>
                                        <td class="align-middle font-monospace fw-bold">{{ $obj->objective_id }}</td>
                                        <td class="align-middle small text-muted">{{ Str::limit($obj->description, 200) }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex justify-content-between align-items-center">
                <div class="small text-muted" id="selected-count">0 selected</div>
                <div>
                    <button type="button" class="btn btn-primary px-4" id="btn-generate-report">
                        <i class="fas fa-table me-2"></i>Generate Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Report Result Section --}}
    <div class="card shadow-sm border-0 mb-4 d-none" id="report-result-card">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-primary">Maturity Level Report</h5>
            <button class="btn btn-sm btn-outline-success" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Print
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0 report-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th class="text-center" style="width:80px;">Gap</th>
                            <th>Process Name</th>
                            <th class="text-center" style="width: 130px;">Level</th>
                            <th class="text-center" style="width: 90px;">Rating</th>
                            <th class="text-center" style="width: 90px;">Target</th>
                            <th class="text-center" style="width: 90px;">Gap</th>
                            <th class="text-center" style="width: 80px;">Checklist</th>
                        </tr>
                            <td></td>
                            <td class="text-center" id="avg-target">-</td>
                            <td></td>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .report-table {
        border-collapse: collapse;
        font-size: 0.9rem;
    }
    .report-table th, .report-table td {
        padding: 0.35rem 0.5rem;
        border: 1px solid #dee2e6;
    }
    .report-table thead th {
        background: #f6f8ff;
        color: #42507a;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        position: sticky;
        top: 0;
        z-index: 1;
    }
    .report-table tbody tr:nth-child(odd) {
        background: #fbfcff;
    }
    .report-table tbody tr:hover {
        background: #f0f4ff;
    }
    .report-table input[type="checkbox"] {
        width: 16px;
        height: 16px;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Server data injections
    window.OBJECTIVES = @json($objectives);
    window.EVAL_ID = "{{ $evalId }}";
    window.TARGET_CAPABILITY_MAP = @json($targetCapabilityMap ?? []);

    document.addEventListener('DOMContentLoaded', () => {
        const dom = {
            table: document.getElementById('gamo-table'),
            btnSelectAll: document.getElementById('btn-select-all'),
            btnDeselectAll: document.getElementById('btn-deselect-all'),
            btnGenerate: document.getElementById('btn-generate-report'),
            reportCard: document.getElementById('report-result-card'),
            tableBody: document.getElementById('report-table-body'),
            selectedCount: document.getElementById('selected-count'),
            checkAll: document.getElementById('check-all')
        };

        let assessmentData = null; // Will hold loaded assessment data

        // get live NodeList of checkboxes
        const getCheckboxes = () => Array.from(document.querySelectorAll('.gamo-checkbox'));

        // --- Helpers ---
        const renderTable = (metaList) => {
            const body = tableBody();
            body.innerHTML = '';

            const baseLevels = [];
            const targetVals = [];

            metaList.forEach((meta, idx) => {
                const data = objectiveLevels[meta.id] || { level: 0, rating: '-' };
                const level = data.level ?? 0;
                const rating = data.rating ?? '-';
                baseLevels.push(level);

                const target = TARGET_MAP[meta.id] ?? null;
                if (target !== null && target !== undefined) targetVals.push(target);
                const gap = (target === null || target === undefined) ? null : (target - level);

                const tr = document.createElement('tr');
                tr.classList.add(`domain-${meta.domain}`);
                tr.innerHTML = `
                    <td class="text-center sticky-col">${idx + 1}</td>
                    <td class="text-center fw-bold domain-chip">${meta.domain}</td>
                    <td class="text-center fw-bold font-monospace">${meta.id}</td>
                    <td>
                        <div class="fw-bold">${meta.name}</div>
                        <div class="text-muted small">${meta.description}</div>
                    </td>
                    <td class="text-center fw-bold" style="color:${levelColor(level)};">${level}</td>
                    <td class="text-center">${rating}</td>
                    <td class="text-center">${target ?? '-'}</td>
                    <td class="text-center ${gapClass(gap)}">${gap === null ? '-' : gap}</td>
                `;

                dynamicColumns.forEach(col => {
                    const cell = document.createElement('td');
                    const included = col.objectives.has(meta.id);
                    cell.className = 'text-center';
                    cell.textContent = included ? level : '';
                    tr.appendChild(cell);
                });

                body.appendChild(tr);
            });

            document.getElementById('avg-base').textContent = avg(baseLevels);
            document.getElementById('avg-target').textContent = targetVals.length ? avg(targetVals) : '-';

            refreshFooter(metaList);
        };
                dom.checkAll.indeterminate = false;
            } else {
                const all = checkboxes.every(cb => cb.checked);
                const none = checkboxes.every(cb => !cb.checked);
                dom.checkAll.checked = all;
                dom.checkAll.indeterminate = !(all || none);
            }
        };

        const toggleAll = (checked) => {
            getCheckboxes().forEach(cb => cb.checked = checked);
            updateCount();
        };

        // master header checkbox toggle
        dom.checkAll.addEventListener('change', (e) => {
            toggleAll(e.target.checked);
        });

        dom.btnSelectAll.addEventListener('click', () => toggleAll(true));
        dom.btnDeselectAll.addEventListener('click', () => toggleAll(false));

        // delegate per-row checkbox change to update count (checkboxes already exist)
        getCheckboxes().forEach(cb => cb.addEventListener('change', updateCount));

        // --- Logic: Calculate Maturity Level (sama seperti sebelumnya) ---
        const calculateMaturityLevel = (objectiveId) => {
            const objMeta = window.OBJECTIVES.find(o => o.objective_id === objectiveId);
            if (!objMeta || !objMeta.practices) return 0;

            const objActivities = [];
            objMeta.practices.forEach(p => {
                if (p.activities) {
                    p.activities.forEach(a => {
                        objActivities.push(a);
                    });
                }
            });

            if (!objActivities.length) return 0;
            if (!assessmentData || !assessmentData.activityData) return 0;

            const levels = [2, 3, 4, 5];
            let maxLevel = 0;
            
            // Level 1 detection
            const anyRated = objActivities.some(a => {
                const val = assessmentData.activityData?.[a.activity_id]?.value;
                return val === 'P' || val === 'L' || val === 'F'; 
            });
            if (anyRated) maxLevel = 1;

            for (let lvl of levels) {
                const actsAtLvl = objActivities.filter(a => parseInt(a.capability_level) === lvl);
                if (actsAtLvl.length === 0) continue;
                const allAchieved = actsAtLvl.every(a => {
                    const rating = (assessmentData.activityData?.[a.activity_id]?.value || 'N'); 
                    return rating === 'L' || rating === 'F';
                });
                if (allAchieved) {
                    maxLevel = lvl;
                } else {
                    break; 
                }
            }

            return maxLevel;
        };

        // --- Load Data ---
        const loadAssessmentData = async () => {
            try {
                const resp = await fetch(`/assessment-eval/${window.EVAL_ID}/load`);
                const json = await resp.json();
                if (json.success) {
                    assessmentData = json.data;
                } else {
                    throw new Error(json.message || 'Unknown');
                }
            } catch (err) {
                console.error(err);
                Swal.fire('Error', 'Failed to load assessment data', 'error');
            }
        };

        // --- Generate Report ---
            const generateReport = async () => {
                try {
                    if (!assessmentData) {
                        await loadAssessmentData();
                        if (!assessmentData) throw new Error('Data assessment tidak tersedia');
                    }

                const selectedIds = getCheckboxes()
                    .filter(cb => cb.checked)
                    .map(cb => cb.value);

                if (!selectedIds.length) {
                    Swal.fire('Info', 'Please select at least one objective', 'info');
                    return;
                }

                // Generate rows
                let rowsHtml = '';
                let totalLevel = 0;
                
                selectedIds.forEach((objId, idx) => {
                    const level = calculateMaturityLevel(objId);
                    totalLevel += level;
                    
                    // Find objective details
                    const objMeta = window.OBJECTIVES.find(o => o.objective_id === objId);
                    const desc = objMeta ? objMeta.description : '-';
                    const domain = objId.substring(0, 3);
                    const objectiveName = objMeta ? (objMeta.objective || desc || '-') : '-';

                    // Pull target & rating (same as summary result logic)
                    const targetVal = window.TARGET_CAPABILITY_MAP ? window.TARGET_CAPABILITY_MAP[objId] : null;
                    const gapVal = (targetVal === null || targetVal === undefined) ? null : (targetVal - level);

                    const gapClasses = ['fw-bold'];
                    if (gapVal === null) {
                        gapClasses.push('text-muted');
                    } else if (gapVal < 0) {
                        gapClasses.push('text-danger');
                    } else if (gapVal > 0) {
                        gapClasses.push('text-success');
                    } else {
                        gapClasses.push('text-dark');
                    }

                    // Rating letter: take highest rated level letter if available
                    let ratingLetter = '-';
                    const ratingLookup = thisLevelRating(objId, level);
                    if (level > 0 && ratingLookup) {
                        ratingLetter = ratingLookup;
                    }

                    rowsHtml += `
                        <tr>
                            <td class="text-center align-middle">${idx + 1}</td>
                            <td class="text-center align-middle fw-bold">${domain}</td>
                            <td class="text-center align-middle fw-bold font-monospace">${objId}</td>
                            <td class="align-middle">${objectiveName}</td>
                            <td class="text-center align-middle fw-bold" style="background:${levelBg(level)}; color:${levelColor(level)};">${level}</td>
                            <td class="text-center align-middle">${ratingLetter}</td>
                            <td class="text-center align-middle">${targetVal === null || targetVal === undefined ? '-' : targetVal}</td>
                            <td class="text-center align-middle ${gapClasses.join(' ')}">${gapVal === null ? '-' : gapVal}</td>
                            <td class="text-center align-middle"><input type="checkbox" class="form-check-input" /></td>
                        </tr>
                    `;
                });
                
                // Average Calculation
                const average = selectedIds.length > 0 ? (totalLevel / selectedIds.length).toFixed(2) : "0.00";

                // Add Footer Row for Average
                rowsHtml += `
                    <tr class="table-primary fw-bold" style="border-top: 2px solid #0d6efd;">
                        <td colspan="4" class="text-end">Rata-Rata Maturity Level (Average)</td>
                        <td class="text-center fs-5">${average}</td>
                        <td colspan="4"></td>
                    </tr>
                `;

                dom.tableBody.innerHTML = rowsHtml;
                dom.reportCard.classList.remove('d-none');
                dom.reportCard.scrollIntoView({ behavior: 'smooth' });
            } catch (err) {
                console.error('Generate report failed', err);
                Swal.fire('Error', err.message || 'Gagal membuat report', 'error');
            }
        };


        // --- Events ---
        dom.btnGenerate.addEventListener('click', () => {
            const btn = dom.btnGenerate;
            if (btn) {
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating...';
                btn.disabled = true;
                setTimeout(async () => {
                    await generateReport();
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }, 150);
            } else {
                generateReport();
            }
        });

        // Initialize selected count
        updateCount();

        // Auto-generate report on load without requiring button click
        generateReport();
    });
</script>
@endsection
