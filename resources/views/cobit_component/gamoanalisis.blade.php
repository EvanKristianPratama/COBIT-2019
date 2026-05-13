@extends('layouts.app')

@section('content')
    <style>
        :root {
            --bg-gradient-start: #0a1128;
            --bg-gradient-end: #1a2a6c;
            --panel-bg: rgba(255, 255, 255, 0.98);
            --card-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            --accent-input: #3a86ff;
            --accent-practice: #8338ec;
            --accent-output: #ff006e;
            --accent-r: #ef233c;
            --accent-a: #ffb703;
            --accent-c: #06d6a0;
            --accent-i: #118ab2;
        }

        body {
            background-color: #f4f7fe;
        }

        .premium-header {
            background: linear-gradient(135deg, var(--bg-gradient-start), var(--bg-gradient-end));
            color: #fff;
            border-radius: 16px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 15px 35px rgba(10, 17, 40, 0.15);
            position: relative;
            overflow: hidden;
        }

        .premium-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 50%;
        }

        .select-wrapper {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 1rem;
            max-width: 500px;
        }

        .gamo-select {
            background-color: #fff !important;
            border-radius: 8px;
            font-weight: 600;
            color: #0f2b5c !important;
            border: 2px solid transparent;
            transition: border-color 0.2s;
        }

        .gamo-select:focus {
            border-color: var(--accent-practice);
            box-shadow: 0 0 0 0.25rem rgba(131, 56, 236, 0.25);
        }

        /* Node Flow Layout */
        .flow-container {
            margin-top: 3rem;
        }

        .practice-flow-row {
            display: grid;
            grid-template-columns: 1fr 120px 1.2fr 120px 1fr;
            align-items: center;
            margin-bottom: 4rem;
            position: relative;
        }

        @media (max-width: 1200px) {
            .practice-flow-row {
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
                grid-template-columns: unset;
            }
            .flow-arrow {
                transform: rotate(90deg);
                margin: 1rem auto !important;
                height: 40px !important;
            }
        }

        /* Flow Card Styling */
        .flow-card {
            background: var(--panel-bg);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(226, 232, 240, 0.8);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .flow-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
        }

        .card-badge {
            position: absolute;
            top: -12px;
            left: 20px;
            padding: 4px 16px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #fff;
        }

        .badge-input { background-color: var(--accent-input); }
        .badge-practice { background-color: var(--accent-practice); }
        .badge-output { background-color: var(--accent-output); }

        .flow-card.input-node {
            border-left: 6px solid var(--accent-input);
        }

        .flow-card.practice-node {
            border-top: 6px solid var(--accent-practice);
            background: #ffffff;
            text-align: center;
        }

        .flow-card.output-node {
            border-right: 6px solid var(--accent-output);
        }

        /* Arrow Visuals */
        .flow-arrow {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            height: 100%;
            min-height: 40px;
        }

        .arrow-line {
            height: 3px;
            background: #cbd5e1;
            width: 100%;
            position: relative;
        }

        .arrow-line::after {
            content: '';
            position: absolute;
            right: 0;
            top: -5px;
            width: 0;
            height: 0;
            border-top: 6px solid transparent;
            border-bottom: 6px solid transparent;
            border-left: 10px solid #cbd5e1;
        }

        /* RACI BADGES */
        .raci-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 8px;
            margin-top: 1.2rem;
            padding-top: 1rem;
            border-top: 1px solid #f1f5f9;
        }

        .raci-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 20px;
            overflow: hidden;
            font-size: 0.75rem;
            border: 1px solid #e2e8f0;
            background: #fff;
        }

        .raci-role {
            padding: 3px 8px 3px 10px;
            font-weight: 600;
            color: #475569;
        }

        .raci-type {
            padding: 3px 8px;
            color: #fff;
            font-weight: 800;
            min-width: 25px;
            text-align: center;
        }

        .raci-type.type-r { background-color: var(--accent-r); }
        .raci-type.type-a { background-color: var(--accent-a); }
        .raci-type.type-c { background-color: var(--accent-c); }
        .raci-type.type-i { background-color: var(--accent-i); }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: #fff;
            border-radius: 16px;
            border: 2px dashed #e2e8f0;
            color: #64748b;
        }

        .flow-item-wrap {
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px dashed #e2e8f0;
        }

        .flow-item-wrap:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .flow-source-target {
            font-size: 0.8rem;
            font-weight: 700;
            color: #1e293b;
            display: inline-block;
            margin-bottom: 4px;
            background: #f1f5f9;
            padding: 2px 8px;
            border-radius: 4px;
        }

        .flow-description {
            font-size: 0.85rem;
            color: #334155;
            line-height: 1.5;
        }

        /* Micro Animations */
        @keyframes linePulse {
            0% { opacity: 0.6; }
            50% { opacity: 1; background: #94a3b8; }
            100% { opacity: 0.6; }
        }
        .arrow-line {
            animation: linePulse 2s infinite;
        }
    </style>

    <div class="container py-4">
        <!-- Header Panel -->
        <div class="premium-header">
            <div class="row align-items-center">
                <div class="col-lg-7 mb-4 mb-lg-0">
                    <h1 class="fw-bold display-6 mb-2"><i class="fas fa-project-diagram me-3 text-warning"></i>GAMO Analisis Aliran Informasi</h1>
                    <p class="lead opacity-75 mb-0">Visualisasi komprehensif input proses, RACI role penanggung jawab, serta output antar-GAMO COBIT 2019.</p>
                </div>
                <div class="col-lg-5">
                    <div class="select-wrapper float-lg-end w-100">
                        <label for="gamoSelector" class="form-label text-white fw-semibold mb-2">Pilih Governance & Management Objective:</label>
                        <select id="gamoSelector" class="form-select gamo-select shadow-sm">
                            <option value="" disabled selected>-- Pilih GAMO --</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Display Metadata Panel -->
        <div id="metaPanel" class="card shadow-sm mb-4 d-none" style="border-radius: 14px;">
            <div class="card-body bg-light p-4" style="border-radius: 14px;">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div id="gamoCircle" class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold fs-2 shadow" style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--accent-practice), var(--accent-input))"></div>
                    </div>
                    <div class="col">
                        <h3 class="mb-1 fw-bold text-dark" id="metaTitle">APO01</h3>
                        <h5 class="mb-2 text-muted" id="metaName">Managed IT Management Framework</h5>
                        <div class="d-flex gap-3 small text-muted">
                            <span><i class="fas fa-info-circle me-1"></i> <span id="metaDesc">Deskripsi</span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Interactive Flow Container -->
        <div id="flowArea">
            <div class="empty-state">
                <img src="https://illustrations.popsy.co/gray/idea-launch.svg" alt="pilih gamo" class="mb-3" style="max-width: 180px;">
                <h4>Mulai Analisis Alur</h4>
                <p class="mb-0">Silakan pilih salah satu modul GAMO dari dropdown di atas untuk melihat peta informasi dan perannya.</p>
            </div>
        </div>
    </div>

    <!-- Script Logic -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selector = document.getElementById('gamoSelector');
            const flowArea = document.getElementById('flowArea');
            const metaPanel = document.getElementById('metaPanel');
            const metaTitle = document.getElementById('metaTitle');
            const metaName = document.getElementById('metaName');
            const metaDesc = document.getElementById('metaDesc');
            const gamoCircle = document.getElementById('gamoCircle');

            let cachedObjectives = null;

            // 1. Fetch all objectives
            fetch('{{ url('/objectives') }}', {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                cachedObjectives = data;
                populateSelector(data);
            })
            .catch(err => {
                console.error('Gagal memuat data:', err);
                flowArea.innerHTML = `<div class="alert alert-danger">Gagal memuat master objectives dari API.</div>`;
            });

            function populateSelector(data) {
                selector.innerHTML = '<option value="" disabled selected>-- Pilih GAMO untuk Analisis --</option>';
                
                // Sort by priority then prefix
                const preferredOrder = ['EDM', 'APO', 'BAI', 'DSS', 'MEA'];
                data.sort((a, b) => {
                    const aPre = a.objective_id.substring(0, 3).toUpperCase();
                    const bPre = b.objective_id.substring(0, 3).toUpperCase();
                    const aIdx = preferredOrder.indexOf(aPre);
                    const bIdx = preferredOrder.indexOf(bPre);
                    
                    if (aIdx !== bIdx) return (aIdx === -1 ? 99 : aIdx) - (bIdx === -1 ? 99 : bIdx);
                    return a.objective_id.localeCompare(b.objective_id);
                });

                data.forEach(obj => {
                    const opt = document.createElement('option');
                    opt.value = obj.objective_id;
                    opt.textContent = `${obj.objective_id} - ${obj.objective}`;
                    selector.appendChild(opt);
                });
            }

            // 2. On Selector Change
            selector.addEventListener('change', function() {
                const objId = this.value;
                const obj = cachedObjectives.find(o => o.objective_id === objId);
                if (obj) renderGamoFlow(obj);
            });

            function renderGamoFlow(obj) {
                // Render Meta Data
                metaPanel.classList.remove('d-none');
                metaTitle.textContent = obj.objective_id;
                metaName.textContent = obj.objective;
                metaDesc.textContent = obj.objective_description || 'Tidak ada deskripsi';
                gamoCircle.textContent = obj.objective_id.substring(0, 3);

                const practices = obj.practices || [];
                if (!practices.length) {
                    flowArea.innerHTML = `<div class="empty-state"><h4>Tidak Ada Data</h4><p>Objektif ini belum memiliki konfigurasi practice.</p></div>`;
                    return;
                }

                let html = '<div class="flow-container">';

                practices.forEach(practice => {
                    // Extract Inputs
                    const inputs = practice.infoflowinput || [];
                    let inputCardsHtml = '';
                    if (inputs.length) {
                        inputs.forEach(inp => {
                            inputCardsHtml += `
                                <div class="flow-item-wrap">
                                    <span class="flow-source-target"><i class="fas fa-sign-in-alt me-1 text-primary"></i> Dari: ${escapeHtml(inp.from || 'Eksternal')}</span>
                                    <div class="flow-description">${escapeHtml(inp.description || '-')}</div>
                                </div>
                            `;
                        });
                    } else {
                        inputCardsHtml = `<div class="text-muted small fst-italic text-center my-3">Tidak ada input spesifik</div>`;
                    }

                    // Extract Outputs (e.g. direct query from outputs connected directly to the practice)
                    // In the controller we load: 'practices.infoflowoutput' as extra relation, or linked via inputs
                    const outputs = practice.infoflowoutput || [];
                    let outputCardsHtml = '';
                    if (outputs.length) {
                        outputs.forEach(out => {
                            outputCardsHtml += `
                                <div class="flow-item-wrap">
                                    <span class="flow-source-target"><i class="fas fa-sign-out-alt me-1 text-danger"></i> Menuju: ${escapeHtml(out.to || 'Eksternal')}</span>
                                    <div class="flow-description">${escapeHtml(out.description || '-')}</div>
                                </div>
                            `;
                        });
                    } else {
                        outputCardsHtml = `<div class="text-muted small fst-italic text-center my-3">Tidak ada output spesifik</div>`;
                    }

                    // Extract RACI (Roles)
                    const roles = practice.roles || [];
                    let raciHtml = '';
                    if (roles.length) {
                        roles.forEach(r => {
                            const typeLetter = (r.pivot ? r.pivot.r_a : '').toUpperCase() || '-';
                            if(typeLetter === '-') return; // Skip roles with no relation
                            const typeClass = `type-${typeLetter.toLowerCase()}`;
                            raciHtml += `
                                <div class="raci-pill shadow-sm" title="${escapeHtml(r.description || '')}">
                                    <span class="raci-role">${escapeHtml(r.role)}</span>
                                    <span class="raci-type ${typeClass}">${typeLetter}</span>
                                </div>
                            `;
                        });
                    }

                    html += `
                        <div class="practice-flow-row">
                            <!-- COLUMN 1: INPUTS -->
                            <div class="flow-card input-node">
                                <span class="card-badge badge-input">Inputs</span>
                                ${inputCardsHtml}
                            </div>

                            <!-- COLUMN 2: ARROW 1 -->
                            <div class="flow-arrow">
                                <div class="arrow-line"></div>
                            </div>

                            <!-- COLUMN 3: CENTRAL PRACTICE & RACI -->
                            <div class="flow-card practice-node">
                                <span class="card-badge badge-practice">Management Practice</span>
                                <h5 class="fw-bold mb-1 text-dark" style="color: var(--accent-practice) !important;">${practice.practice_id}</h5>
                                <h6 class="small fw-semibold text-muted mb-2">${escapeHtml(practice.practice_name || '')}</h6>
                                <p class="small text-muted mb-0 px-2" style="text-align: justify; font-size: 0.8rem;">${escapeHtml(practice.practice_description || '')}</p>
                                
                                <div class="raci-container">
                                    ${raciHtml || '<span class="text-muted small">Tidak ada pemetaan RACI</span>'}
                                </div>
                            </div>

                            <!-- COLUMN 4: ARROW 2 -->
                            <div class="flow-arrow">
                                <div class="arrow-line"></div>
                            </div>

                            <!-- COLUMN 5: OUTPUTS -->
                            <div class="flow-card output-node">
                                <span class="card-badge badge-output">Outputs</span>
                                ${outputCardsHtml}
                            </div>
                        </div>
                    `;
                });

                html += '</div>';
                flowArea.innerHTML = html;
            }

            function escapeHtml(text) {
                if (!text) return '';
                return text
                    .toString()
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }
        });
    </script>
@endsection
