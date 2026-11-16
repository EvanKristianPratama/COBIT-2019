@extends('layouts.app')

@section('content')
    <style>
        :root {
            --accent-input: #3b82f6;
            --accent-input-bg: #eff6ff;
            --accent-practice: #8b5cf6;
            --accent-practice-bg: #f5f3ff;
            --accent-output: #ec4899;
            --accent-output-bg: #fdf2f8;
            
            --raci-r: #ef4444;
            --raci-r-bg: #fee2e2;
            --raci-a: #f59e0b;
            --raci-a-bg: #fef3c7;
            --raci-c: #10b981;
            --raci-c-bg: #d1fae5;
            --raci-i: #06b6d4;
            --raci-i-bg: #ecfeff;
            
            --transition-smooth: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            background-color: #f8fafc;
        }

        .premium-header {
            background: #ffffff;
            border: 1px solid rgba(15, 43, 92, 0.08);
            border-radius: 20px;
            padding: 2.25rem;
            margin-bottom: 2.5rem;
            box-shadow: 0 10px 30px -10px rgba(15, 43, 92, 0.06);
            position: relative;
            overflow: hidden;
        }

        .premium-header h1 {
            color: #0f2b5c;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .select-wrapper {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 0.85rem 1.25rem;
            transition: var(--transition-smooth);
        }

        .select-wrapper:focus-within {
            border-color: var(--accent-practice);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
        }

        .gamo-select {
            background-color: #fff !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 10px;
            font-weight: 600;
            color: #0f2b5c !important;
            padding: 0.6rem 1rem;
            transition: var(--transition-smooth);
        }

        .gamo-select:focus {
            border-color: var(--accent-practice) !important;
            box-shadow: none !important;
        }

        /* Node Flow Layout */
        .flow-container {
            margin-top: 3rem;
        }

        .practice-flow-row {
            display: grid;
            grid-template-columns: 1fr 100px 1.2fr 100px 1fr;
            align-items: stretch;
            margin-bottom: 3.5rem;
            position: relative;
        }

        @media (max-width: 1200px) {
            .practice-flow-row {
                display: flex;
                flex-direction: column;
                gap: 2rem;
                grid-template-columns: unset;
            }
            .flow-arrow {
                height: 40px;
                width: 100%;
                padding: 10px 0;
            }
            .arrow-line {
                width: 2px !important;
                height: 100% !important;
                background-image: linear-gradient(to bottom, #cbd5e1 60%, rgba(255, 255, 255, 0) 0%) !important;
                background-position: top !important;
                background-size: 2px 10px !important;
                background-repeat: repeat-y !important;
                animation: flowDashVert 25s linear infinite !important;
            }
            @keyframes flowDashVert {
                from { background-position: 0% 0%; }
                to { background-position: 0% 100%; }
            }
            .arrow-line::after {
                content: '\f063' !important;
                right: auto !important;
                left: 50% !important;
                bottom: -2px !important;
                top: auto !important;
                transform: translateX(-50%) !important;
                padding: 4px 0 !important;
            }
        }

        /* Flow Card Styling */
        .flow-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 1.75rem;
            box-shadow: 0 10px 25px -10px rgba(15, 43, 92, 0.04);
            border: 1px solid #e2e8f0;
            transition: var(--transition-smooth);
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .flow-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 35px -10px rgba(15, 43, 92, 0.08);
            border-color: #cbd5e1;
        }

        .card-badge {
            position: absolute;
            top: -12px;
            left: 24px;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #fff;
            box-shadow: 0 4px 10px -2px rgba(0, 0, 0, 0.1);
        }

        .badge-input { background: linear-gradient(135deg, var(--accent-input), #60a5fa); }
        .badge-practice { background: linear-gradient(135deg, var(--accent-practice), #a78bfa); }
        .badge-output { background: linear-gradient(135deg, var(--accent-output), #f472b6); }

        .flow-card.input-node {
            border-top: 4px solid var(--accent-input);
        }

        .flow-card.practice-node {
            border-top: 4px solid var(--accent-practice);
            text-align: center;
        }

        .flow-card.output-node {
            border-top: 4px solid var(--accent-output);
        }

        /* Arrow Visuals */
        .flow-arrow {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 0 10px;
        }

        .arrow-line {
            height: 2px;
            width: 100%;
            background-image: linear-gradient(to right, #cbd5e1 60%, rgba(255, 255, 255, 0) 0%);
            background-position: left;
            background-size: 10px 2px;
            background-repeat: repeat-x;
            position: relative;
            animation: flowDash 25s linear infinite;
        }

        @keyframes flowDash {
            from { background-position: 0% 0%; }
            to { background-position: 100% 0%; }
        }

        .arrow-line::after {
            content: '\f061';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            color: #94a3b8;
            font-size: 0.85rem;
            position: absolute;
            right: -2px;
            top: 50%;
            transform: translateY(-50%);
            background: #f8fafc;
            padding: 0 4px;
            transition: var(--transition-smooth);
        }

        /* RACI BADGES */
        .raci-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 8px;
            margin-top: auto;
            padding-top: 1.25rem;
            border-top: 1px solid #f1f5f9;
        }

        .raci-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 10px;
            overflow: hidden;
            font-size: 0.75rem;
            font-weight: 600;
            border: 1px solid #e2e8f0;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
            transition: var(--transition-smooth);
        }

        .raci-pill:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            border-color: #cbd5e1;
        }

        .raci-role {
            padding: 4px 10px;
            color: #334155;
        }

        .raci-type {
            padding: 4px 10px;
            font-weight: 800;
            min-width: 28px;
            text-align: center;
        }

        .raci-type.type-r { background-color: var(--raci-r-bg); color: var(--raci-r); }
        .raci-type.type-a { background-color: var(--raci-a-bg); color: var(--raci-a); }
        .raci-type.type-c { background-color: var(--raci-c-bg); color: var(--raci-c); }
        .raci-type.type-i { background-color: var(--raci-i-bg); color: var(--raci-i); }

        .empty-state {
            text-align: center;
            padding: 5rem 2rem;
            background: #fff;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 25px -10px rgba(15, 43, 92, 0.02);
            color: #64748b;
        }

        .empty-state h4 {
            color: #0f2b5c;
            font-weight: 700;
            margin-top: 1rem;
        }

        .flow-item-wrap {
            background: #f8fafc;
            border-radius: 12px;
            padding: 0.85rem 1rem;
            margin-bottom: 0.75rem;
            border: 1px solid #f1f5f9;
            transition: var(--transition-smooth);
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .flow-item-wrap:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .flow-item-wrap:hover {
            background: #ffffff;
            border-color: #cbd5e1;
            box-shadow: 0 4px 12px rgba(15, 43, 92, 0.04);
        }

        .flow-source-target {
            font-size: 0.75rem;
            font-weight: 700;
            color: #1e293b;
            display: inline-flex;
            align-items: center;
            align-self: flex-start;
            background: #e2e8f0;
            padding: 3px 8px;
            border-radius: 6px;
            margin-bottom: 4px;
        }

        .flow-description {
            font-size: 0.825rem;
            color: #475569;
            line-height: 1.5;
            white-space: pre-wrap;
        }
    </style>

    <div class="container py-4">
        <!-- Header Panel -->
        <div class="premium-header">
            <div class="row align-items-center">
                <div class="col-lg-7 mb-4 mb-lg-0">
                    <h1 class="fw-bold display-6 mb-2"><i class="fas fa-project-diagram me-3 text-primary"></i>GAMO Analisis Aliran Informasi</h1>
                </div>
                <div class="col-lg-5">
                    <div class="select-wrapper float-lg-end w-100">
                        <label for="gamoSelector" class="form-label text-secondary fw-semibold mb-2">Pilih Governance & Management Objective:</label>
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
                        <div id="gamoCircle" class="rounded text-white d-flex align-items-center justify-content-center fw-bold fs-2 shadow" style="width: 70px; height: 70px; border-radius: 16px !important; background: linear-gradient(135deg, var(--accent-practice), #a78bfa)"></div>
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
                    const cleanA = a.objective_id.toString().replace(/(^"|"$)/g, '').trim();
                    const cleanB = b.objective_id.toString().replace(/(^"|"$)/g, '').trim();
                    const aPre = cleanA.substring(0, 3).toUpperCase();
                    const bPre = cleanB.substring(0, 3).toUpperCase();
                    const aIdx = preferredOrder.indexOf(aPre);
                    const bIdx = preferredOrder.indexOf(bPre);
                    
                    if (aIdx !== bIdx) return (aIdx === -1 ? 99 : aIdx) - (bIdx === -1 ? 99 : bIdx);
                    return cleanA.localeCompare(cleanB);
                });

                data.forEach(obj => {
                    const cleanId = obj.objective_id.toString().replace(/(^"|"$)/g, '').trim();
                    const cleanName = obj.objective.toString().replace(/(^"|"$)/g, '').trim();
                    const opt = document.createElement('option');
                    opt.value = cleanId;
                    opt.textContent = `${cleanId} - ${cleanName}`;
                    selector.appendChild(opt);
                });
            }

            // 2. On Selector Change
            selector.addEventListener('change', function() {
                const objId = this.value.toString().replace(/(^"|"$)/g, '').trim().toUpperCase();
                const obj = cachedObjectives.find(o => {
                    const cleanId = o.objective_id.toString().replace(/(^"|"$)/g, '').trim().toUpperCase();
                    return cleanId === objId;
                });
                if (obj) {
                    renderGamoFlow(obj);
                } else {
                    console.error('Objective not found:', objId);
                }
            });

            function renderGamoFlow(obj) {
                // Render Meta Data
                metaPanel.classList.remove('d-none');
                metaTitle.textContent = obj.objective_id;
                metaName.textContent = obj.objective;
                metaDesc.textContent = obj.objective_description || 'Tidak ada deskripsi';
                gamoCircle.textContent = obj.objective_id.substring(0, 3);

                const practices = obj.practices || [];
                
                // Sort practices by practice_id to ensure order
                practices.sort((a, b) => {
                    const idA = (a.practice_id || '').replace(/(^"|"$)/g, '');
                    const idB = (b.practice_id || '').replace(/(^"|"$)/g, '');
                    return idA.localeCompare(idB);
                });

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
                            const fromText = formatTextGAMO(inp.from) || 'Eksternal';
                            const descText = formatTextGAMO(inp.description) || '-';
                            inputCardsHtml += `
                                <div class="flow-item-wrap">
                                    <span class="flow-source-target"><i class="fas fa-sign-in-alt me-1 text-primary"></i> Dari: ${fromText}</span>
                                    <div class="flow-description">${descText}</div>
                                </div>
                            `;
                        });
                    } else {
                        inputCardsHtml = `<div class="text-muted small fst-italic text-center my-3">Tidak ada input spesifik</div>`;
                    }

                    // Extract Outputs via connected outputs from inputs (many-to-many via trs_infoflowio)
                    // This matches how the show/information flow page retrieves outputs
                    const outputsMap = new Map(); // deduplicate by output_id
                    inputs.forEach(inp => {
                        const connectedOutputs = inp.connectedoutputs || [];
                        connectedOutputs.forEach(out => {
                            const outId = out.output_id || JSON.stringify(out);
                            if (!outputsMap.has(outId)) {
                                outputsMap.set(outId, out);
                            }
                        });
                    });
                    // Also include direct practice outputs as fallback
                    const directOutputs = practice.infoflowoutput || [];
                    directOutputs.forEach(out => {
                        const outId = out.output_id || JSON.stringify(out);
                        if (!outputsMap.has(outId)) {
                            outputsMap.set(outId, out);
                        }
                    });
                    const outputs = Array.from(outputsMap.values());

                    let outputCardsHtml = '';
                    if (outputs.length) {
                        outputs.forEach(out => {
                            const toText = formatTextGAMO(out.to) || 'Eksternal';
                            const descText = formatTextGAMO(out.description) || '-';
                            outputCardsHtml += `
                                <div class="flow-item-wrap">
                                    <span class="flow-source-target"><i class="fas fa-sign-out-alt me-1 text-danger"></i> Menuju: ${toText}</span>
                                    <div class="flow-description">${descText}</div>
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
                                <h5 class="fw-bold mb-1 text-dark" style="color: var(--accent-practice) !important;">${formatTextGAMO(practice.practice_id)}</h5>
                                <h6 class="small fw-semibold text-muted mb-2">${formatTextGAMO(practice.practice_name || '')}</h6>
                                <p class="small text-muted mb-0 px-2" style="text-align: justify; font-size: 0.8rem;">${formatTextGAMO(practice.practice_description || '')}</p>
                                
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

            function formatTextGAMO(text) {
                if (!text) return '';
                // Remove surrounding quotes from the DB value if they exist
                let clean = text.toString().replace(/(^"|"$)/g, '');
                return escapeHtml(clean);
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
