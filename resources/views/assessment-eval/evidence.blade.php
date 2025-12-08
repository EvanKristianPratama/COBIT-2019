@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container mx-auto p-6">
    {{-- Header --}}
    <div class="card shadow-sm mb-4 hero-card" style="border:none;box-shadow:0 22px 45px rgba(14,33,70,0.15);">
        <div class="card-header hero-header py-4" style="background:linear-gradient(135deg,#081a3d,#0f2b5c);color:#fff;border:none;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="hero-title" style="font-size:1.5rem;font-weight:700;letter-spacing:0.04em;">Evidence Library</div>
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

    {{-- Evidence List --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-primary">List of Evidence</h5>
            @if($isOwner)
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" id="btn-import-prev" data-bs-toggle="modal" data-bs-target="#importEvidenceModal">
                        <i class="fas fa-history me-2"></i>Add Evidence from Previous
                    </button>
                    <button type="button" class="btn btn-primary rounded-pill" id="btn-add-new" data-bs-toggle="modal" data-bs-target="#addEvidenceModal">
                        <i class="fas fa-plus me-2"></i>Add New Evidence
                    </button>
                </div>
            @endif
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="overflow-x: auto;">
                    <table class="table table-bordered table-sm mb-0" id="evidence-table" style="font-size: 0.9rem; min-width: 1400px;">
                        <thead style="background-color: #e9ecef;">
                            <tr>
                                <th class="text-center" style="width: 50px;">No</th>
                                <th>Judul Dokumen</th>
                                <th>No. Dokumen</th>
                                <th class="text-center">Grup</th>
                                <th>Tipe</th>
                                <th class="text-center">Tahun Terbit</th>
                                <th class="text-center">Tahun Kadaluarsa</th>
                                <th>Pemilik</th>
                                <th>Pengesahan</th>
                                <th class="text-center">Klasifikasi</th>
                                <th>Ringkasan</th>
                                <th>Link Dokumen</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                            <tr class="table-light">
                                <th></th>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Cari judul" data-filter-field="judul_dokumen"></th>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Cari no dok" data-filter-field="no_dokumen"></th>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Cari grup" data-filter-field="grup"></th>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Cari tipe" data-filter-field="tipe"></th>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Cari terbit" data-filter-field="tahun_terbit"></th>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Cari kadaluarsa" data-filter-field="tahun_kadaluarsa"></th>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Cari pemilik" data-filter-field="pemilik_dokumen"></th>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Cari pengesahan" data-filter-field="pengesahan"></th>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Cari klasifikasi" data-filter-field="klasifikasi"></th>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Cari ringkasan" data-filter-field="summary"></th>
                                <th></th>
                                <th></th>
                            </tr>
                        </thead>
                    <tbody>
                        @forelse($evidences as $evidence)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td data-field="judul_dokumen">{{ $evidence->judul_dokumen }}</td>
                                <td data-field="no_dokumen">{{ $evidence->no_dokumen ?? '-' }}</td>
                                <td class="text-center" data-field="grup">{{ $evidence->grup ?? '-' }}</td>
                                <td data-field="tipe">{{ $evidence->tipe ?? '-' }}</td>
                                <td class="text-center" data-field="tahun_terbit">{{ $evidence->tahun_terbit ?? '-' }}</td>
                                <td class="text-center" data-field="tahun_kadaluarsa">{{ $evidence->tahun_kadaluarsa ?? '-' }}</td>
                                <td data-field="pemilik_dokumen">{{ $evidence->pemilik_dokumen ?? '-' }}</td>
                                <td data-field="pengesahan">{{ $evidence->pengesahan ?? '-' }}</td>
                                <td class="text-center" data-field="klasifikasi">{{ $evidence->klasifikasi ?? '-' }}</td>
                                <td data-field="summary">{{ $evidence->summary ?? '-' }}</td>
                                <td>
                                    @if($evidence->notes)
                                        <a href="{{ $evidence->notes }}" target="_blank" class="text-decoration-none">Link</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($isOwner)
                                        <button class="btn btn-sm btn-outline-warning btn-edit-evidence" 
                                                title="Edit" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#addEvidenceModal"
                                                data-evidence="{{ json_encode($evidence) }}">
                                            Edit
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr data-empty-row="true">
                                <td colspan="13" class="text-center py-3 text-muted">
                                    Tidak ada dokumen evidence ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">Show</span>
                <select class="form-select form-select-sm" id="items-per-page" style="width: 70px;">
                    <option value="25" selected>25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="-1">All</option>
                </select>
                <span class="text-muted small">entries</span>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                <span id="evidence-page-info" class="text-muted small">Showing 0 to 0 of 0 entries</span>
                <nav aria-label="Evidence pagination">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item disabled" id="evidence-prev-item">
                            <button class="page-link" id="evidence-prev" type="button"><i class="fas fa-chevron-left"></i></button>
                        </li>
                        <li class="page-item disabled" id="evidence-next-item">
                            <button class="page-link" id="evidence-next" type="button"><i class="fas fa-chevron-right"></i></button>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Add Evidence Modal -->
<div class="modal fade" id="addEvidenceModal" tabindex="-1" aria-labelledby="addEvidenceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEvidenceModalLabel">Add New Evidence</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addEvidenceForm">
                    <input type="hidden" id="evidence_id" name="evidence_id">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="judul_dokumen" class="form-label">Judul Dokumen <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="judul_dokumen" name="judul_dokumen" required>
                        </div>
                        <div class="col-md-6">
                            <label for="no_dokumen" class="form-label">No. Dokumen</label>
                            <input type="text" class="form-control" id="no_dokumen" name="no_dokumen">
                        </div>
                        <div class="col-md-6">
                            <label for="grup" class="form-label">Group</label>
                            <select class="form-select" id="grup" name="grup">
                                <option value="">Select Group</option>
                                <option value="EDM">EDM</option>
                                <option value="APO">APO</option>
                                <option value="BAI">BAI</option>
                                <option value="DSS">DSS</option>
                                <option value="MEA">MEA</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="tipe" class="form-label">Tipe Dokumen</label>
                            <input type="text" class="form-control" id="tipe" name="tipe" placeholder="e.g. Policy, Procedure, Report">
                        </div>
                        <div class="col-md-6">
                            <label for="tahun_terbit" class="form-label">Tahun Terbit</label>
                            <input type="number" class="form-control" id="tahun_terbit" name="tahun_terbit" min="1900" max="2099">
                        </div>
                        <div class="col-md-6">
                            <label for="tahun_kadaluarsa" class="form-label">Tahun Kadaluarsa</label>
                            <input type="number" class="form-control" id="tahun_kadaluarsa" name="tahun_kadaluarsa" min="1900" max="2099">
                        </div>
                        <div class="col-md-6">
                            <label for="pemilik_dokumen" class="form-label">Pemilik Dokumen</label>
                            <input type="text" class="form-control" id="pemilik_dokumen" name="pemilik_dokumen">
                        </div>
                        <div class="col-md-6">
                            <label for="pengesahan" class="form-label">Pengesahan</label>
                            <input type="text" class="form-control" id="pengesahan" name="pengesahan">
                        </div>
                        <div class="col-md-6">
                            <label for="klasifikasi" class="form-label">Klasifikasi</label>
                            <select class="form-select" id="klasifikasi" name="klasifikasi">
                                <option value="">Select Classification</option>
                                <option value="Public">Public</option>
                                <option value="Internal">Internal</option>
                                <option value="Confidential">Confidential</option>
                                <option value="Restricted">Restricted</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="summary" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="summary" name="summary" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label for="notes" class="form-label">Link Document (URL)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="https://..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="btn-save-evidence">Save Evidence</button>
            </div>
        </div>
    </div>
</div>

<!-- Import Evidence Modal -->
<div class="modal fade" id="importEvidenceModal" tabindex="-1" aria-labelledby="importEvidenceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importEvidenceModalLabel">Add Evidence from Previous Assessments</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="import-loading" class="d-none text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="mt-2 text-muted">Loading previous evidences...</div>
                </div>
                <div id="import-error" class="alert alert-danger d-none"></div>
                <div id="import-empty" class="text-muted d-none">No previous evidences found.</div>
                <div class="table-responsive" id="import-table-wrapper" style="max-height: 420px; overflow-y: auto;">
                    <table class="table table-sm align-middle" id="import-table">
                        <thead class="align-middle">
                            <tr>
                                <th style="width:40px;" class="text-center">
                                    <input type="checkbox" id="import-check-all" class="form-check-input" title="Pilih semua yang terlihat">
                                </th>
                                <th class="text-center" style="width:50px;">No</th>
                                <th>Judul Dokumen</th>
                                <th>No. Dokumen</th>
                                <th class="text-center">Grup</th>
                                <th>Tipe</th>
                                <th class="text-center" style="width:110px;">Tahun Terbit</th>
                                <th class="text-center" style="width:130px;">Tahun Kadaluarsa</th>
                                <th>Pemilik</th>
                                <th>Pengesahan</th>
                                <th class="text-center">Klasifikasi</th>
                                <th>Ringkasan</th>
                            </tr>
                            <tr class="table-light">
                                <th></th>
                                <th></th>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Cari judul" data-filter-field="judul_dokumen"></th>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Cari no dok" data-filter-field="no_dokumen"></th>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Cari grup" data-filter-field="grup"></th>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Cari tipe" data-filter-field="tipe"></th>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Cari terbit" data-filter-field="tahun_terbit"></th>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Cari kadaluarsa" data-filter-field="tahun_kadaluarsa"></th>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Cari pemilik" data-filter-field="pemilik_dokumen"></th>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Cari pengesahan" data-filter-field="pengesahan"></th>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Cari klasifikasi" data-filter-field="klasifikasi"></th>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Cari ringkasan" data-filter-field="summary"></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="btn-use-imported" disabled style="display:none;">Tambahkan</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    window.SERVER_EVIDENCES_FULL = @json($evidences);
    window.CURRENT_EVIDENCE_KEYS = @json($evidences->map(function($item) {
        return trim(strtolower($item->judul_dokumen . '|' . ($item->no_dokumen ?? '')));
    }));
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const state = {
        isEditing: false,
        currentEvidenceId: null,
        importList: [],
        selectedImportIndices: new Set(),
        existingEvidenceKeys: new Set((window.CURRENT_EVIDENCE_KEYS || []).map(k => k.toLowerCase())),
        importFilters: {},
        tableFilters: {},
        // Pagination state
        pagination: {
            currentPage: 1,
            itemsPerPage: 25,
            totalItems: 0,
            filteredItems: []
        },
        // Store full evidence list in memory
        evidenceList: window.SERVER_EVIDENCES_FULL || []
    };

    const dom = {
        saveEvidenceBtn: document.getElementById('btn-save-evidence'),
        evidenceForm: document.getElementById('addEvidenceForm'),
        modalTitle: document.getElementById('addEvidenceModalLabel'),
        btnAddNew: document.getElementById('btn-add-new'),
        importModalEl: document.getElementById('importEvidenceModal'),
        importTableBody: document.querySelector('#import-table tbody'),
        importCheckAll: document.getElementById('import-check-all'),
        importLoading: document.getElementById('import-loading'),
        importError: document.getElementById('import-error'),
        importEmpty: document.getElementById('import-empty'),
        btnUseImported: document.getElementById('btn-use-imported'),
        btnImportPrev: document.getElementById('btn-import-prev'),
        evidenceTableBody: document.querySelector('#evidence-table tbody'),
        evidenceTableFilterInputs: Array.from(document.querySelectorAll('#evidence-table thead input[data-filter-field]')),
        // Pagination DOM
        itemsPerPageSelect: document.getElementById('items-per-page'),
        pageInfo: document.getElementById('evidence-page-info'),
        prevBtn: document.getElementById('evidence-prev'),
        nextBtn: document.getElementById('evidence-next'),
        prevItem: document.getElementById('evidence-prev-item'),
        nextItem: document.getElementById('evidence-next-item')
    };

    const flags = {
        isOwner: @json($isOwner)
    };

    const importModal = dom.importModalEl ? new bootstrap.Modal(dom.importModalEl) : null;

    /* --------------------
     * Helpers
     * -------------------- */
    const evidenceKey = (item) => {
        const title = (item.judul_dokumen || '').trim().toLowerCase();
        const no = (item.no_dokumen || '').trim().toLowerCase();
        return `${title}|${no}`;
    };

    const setImportLoading = (isLoading) => {
        if (!dom.importLoading) return;
        dom.importLoading.classList.toggle('d-none', !isLoading);
    };

    const showImportError = (message) => {
        if (!dom.importError) return;
        dom.importError.textContent = message;
        dom.importError.classList.remove('d-none');
    };

    const toggleEmptyState = (show) => {
        if (!dom.importEmpty) return;
        dom.importEmpty.classList.toggle('d-none', !show);
    };

    const updateImportButtonState = () => {
        if (!dom.btnUseImported) return;
        dom.btnUseImported.disabled = state.selectedImportIndices.size === 0;
        dom.btnUseImported.style.display = 'inline-block';
    };

    const bindImportFilters = () => {
        document.querySelectorAll('#import-table thead input[data-filter-field]').forEach((input) => {
            input.addEventListener('input', (e) => {
                const field = e.target.getAttribute('data-filter-field');
                state.importFilters[field] = e.target.value || '';
                renderImportTable();
            });
        });
    };

    const applyPagination = () => {
        // 1. Filter
        const filters = Object.entries(state.tableFilters).filter(([, v]) => v && v.toString().trim());
        let filtered = state.evidenceList;

        if (filters.length > 0) {
            filtered = state.evidenceList.filter((item) => {
                return filters.every(([field, value]) => {
                    const hay = (item[field] ?? '').toString().toLowerCase();
                    return hay.includes(value.toLowerCase());
                });
            });
        }
        
        state.pagination.filteredItems = filtered;
        state.pagination.totalItems = filtered.length;

        // 2. Paginate
        const totalPages = Math.max(1, Math.ceil(state.pagination.totalItems / state.pagination.itemsPerPage));
        
        if (state.pagination.itemsPerPage === -1) {
             state.pagination.currentPage = 1;
        } else {
             if (state.pagination.currentPage > totalPages) state.pagination.currentPage = totalPages;
             if (state.pagination.currentPage < 1) state.pagination.currentPage = 1;
        }

        const startIndex = state.pagination.itemsPerPage === -1 ? 0 : (state.pagination.currentPage - 1) * state.pagination.itemsPerPage;
        let endIndex = state.pagination.itemsPerPage === -1 ? state.pagination.totalItems : startIndex + state.pagination.itemsPerPage;
        
        if (endIndex > state.pagination.totalItems) endIndex = state.pagination.totalItems;

        const pagedData = filtered.slice(startIndex, endIndex);
        
        renderEvidenceTable(pagedData, startIndex);
        updatePaginationUI(startIndex, endIndex, state.pagination.totalItems);
    };

    const renderEvidenceTable = (data, startIndex) => {
        if (!dom.evidenceTableBody) return;
        dom.evidenceTableBody.innerHTML = '';

        if (!data.length) {
            const tr = document.createElement('tr');
            tr.setAttribute('data-empty-row', 'true');
            tr.innerHTML = `
                <td colspan="13" class="text-center py-3 text-muted">
                    Tidak ada dokumen evidence ditemukan.
                </td>
            `;
            dom.evidenceTableBody.appendChild(tr);
            return;
        }

        const safe = (val) => val ?? '-';
        
        data.forEach((evidence, idx) => {
            const tr = document.createElement('tr');
            const linkHtml = evidence.notes ? `<a href="${evidence.notes}" target="_blank" class="text-decoration-none">Link</a>` : '-';
            
            let actionHtml = '-';
            if (flags.isOwner) {
                // escape json for data attribute
                const jsonStr = JSON.stringify(evidence).replace(/'/g, "&#39;");
                actionHtml = `
                    <button class="btn btn-sm btn-outline-warning btn-edit-evidence" 
                            title="Edit" 
                            data-bs-toggle="modal" 
                            data-bs-target="#addEvidenceModal"
                            data-evidence='${jsonStr}'>
                        Edit
                    </button>`;
            }

            tr.innerHTML = `
                <td class="text-center">${startIndex + idx + 1}</td>
                <td data-field="judul_dokumen">${safe(evidence.judul_dokumen)}</td>
                <td data-field="no_dokumen">${safe(evidence.no_dokumen)}</td>
                <td class="text-center" data-field="grup">${safe(evidence.grup)}</td>
                <td data-field="tipe">${safe(evidence.tipe)}</td>
                <td class="text-center" data-field="tahun_terbit">${safe(evidence.tahun_terbit)}</td>
                <td class="text-center" data-field="tahun_kadaluarsa">${safe(evidence.tahun_kadaluarsa)}</td>
                <td data-field="pemilik_dokumen">${safe(evidence.pemilik_dokumen)}</td>
                <td data-field="pengesahan">${safe(evidence.pengesahan)}</td>
                <td class="text-center" data-field="klasifikasi">${safe(evidence.klasifikasi)}</td>
                <td data-field="summary">${safe(evidence.summary)}</td>
                <td>${linkHtml}</td>
                <td class="text-center">${actionHtml}</td>
            `;
            dom.evidenceTableBody.appendChild(tr);
        });

        bindEditButtons();
    };

    const updatePaginationUI = (start, end, total) => {
        if (dom.pageInfo) {
            dom.pageInfo.textContent = `Showing ${total === 0 ? 0 : start + 1} to ${end} of ${total} entries`;
        }
        
        const totalPages = Math.ceil(total / state.pagination.itemsPerPage);

        if (dom.prevItem) {
            dom.prevItem.classList.toggle('disabled', state.pagination.currentPage <= 1 || state.pagination.itemsPerPage === -1);
        }
        if (dom.nextItem) {
            dom.nextItem.classList.toggle('disabled', state.pagination.currentPage >= totalPages || state.pagination.itemsPerPage === -1);
            if (total === 0) dom.nextItem.classList.add('disabled');
        }
    };

    const changePage = (direction) => {
        if (state.pagination.itemsPerPage === -1) return;
        state.pagination.currentPage += direction;
        applyPagination();
    };

    const bindEvidenceTableFilters = () => {
        if (!dom.evidenceTableFilterInputs.length) return;
        dom.evidenceTableFilterInputs.forEach((input) => {
            const field = input.getAttribute('data-filter-field');
            if (!field) return;
            input.addEventListener('input', (e) => {
                state.tableFilters[field] = e.target.value || '';
                state.pagination.currentPage = 1; // Reset to page 1
                applyPagination();
            });
        });
    };

    const resetForm = () => {
        dom.evidenceForm.reset();
        document.getElementById('evidence_id').value = '';
        dom.modalTitle.textContent = 'Add New Evidence';
        dom.saveEvidenceBtn.textContent = 'Save Evidence';
        state.isEditing = false;
        state.currentEvidenceId = null;
    };

    const attachEditHandler = (btn) => {
        if (!btn) return;
        btn.addEventListener('click', () => {
            // Because we re-render, sometimes the json in data-evidence might need parsing carefully
            // but usually standard JSON.parse works if stringified correctly.
            const raw = btn.getAttribute('data-evidence');
            // If data attributes were HTMLEntities encoded, browser decodes them in getAttribute usually.
            const evidence = JSON.parse(raw);
            
            state.isEditing = true;
            state.currentEvidenceId = evidence.id;

            dom.modalTitle.textContent = 'Edit Evidence';
            dom.saveEvidenceBtn.textContent = 'Update Evidence';

            document.getElementById('evidence_id').value = evidence.id;
            document.getElementById('judul_dokumen').value = evidence.judul_dokumen;
            document.getElementById('no_dokumen').value = evidence.no_dokumen || '';
            document.getElementById('grup').value = evidence.grup || '';
            document.getElementById('tipe').value = evidence.tipe || '';
            document.getElementById('tahun_terbit').value = evidence.tahun_terbit || '';
            document.getElementById('tahun_kadaluarsa').value = evidence.tahun_kadaluarsa || '';
            document.getElementById('pemilik_dokumen').value = evidence.pemilik_dokumen || '';
            document.getElementById('pengesahan').value = evidence.pengesahan || '';
            document.getElementById('klasifikasi').value = evidence.klasifikasi || '';
            document.getElementById('summary').value = evidence.summary || '';
            document.getElementById('notes').value = evidence.notes || '';
        });
    };

    const bindEditButtons = () => {
        document.querySelectorAll('.btn-edit-evidence').forEach(attachEditHandler);
    };

    const removeEmptyRow = () => {
        if (!dom.evidenceTableBody) return;
        const emptyRow = dom.evidenceTableBody.querySelector('[data-empty-row]');
        if (emptyRow) emptyRow.remove();
    };

    const renumberEvidenceRows = () => {
        if (!dom.evidenceTableBody) return;
        Array.from(dom.evidenceTableBody.querySelectorAll('tr')).forEach((row, idx) => {
            const noCell = row.querySelector('td');
            if (noCell) noCell.textContent = idx + 1;
        });
    };

    const appendEvidenceRow = (evidence) => {
        if (!dom.evidenceTableBody) return;
        removeEmptyRow();

        const safe = (val) => val ?? '-';
        const tr = document.createElement('tr');
        tr.classList.add('table-success');

        const linkHtml = evidence.notes ? `<a href="${evidence.notes}" target="_blank" class="text-decoration-none">Link</a>` : '-';

        let actionHtml = '-';
        if (flags.isOwner) {
            actionHtml = `
                <button class="btn btn-sm btn-outline-warning btn-edit-evidence"
                        title="Edit"
                        data-bs-toggle="modal"
                        data-bs-target="#addEvidenceModal"
                        data-evidence='${JSON.stringify(evidence)}'>
                    Edit
                </button>`;
        }

        tr.innerHTML = `
            <td class="text-center"></td>
            <td>${safe(evidence.judul_dokumen)}</td>
            <td>${safe(evidence.no_dokumen)}</td>
            <td class="text-center">${safe(evidence.grup)}</td>
            <td>${safe(evidence.tipe)}</td>
            <td class="text-center">${safe(evidence.tahun_terbit)}</td>
            <td class="text-center">${safe(evidence.tahun_kadaluarsa)}</td>
            <td>${safe(evidence.pemilik_dokumen)}</td>
            <td>${safe(evidence.pengesahan)}</td>
            <td class="text-center">${safe(evidence.klasifikasi)}</td>
            <td>${safe(evidence.summary)}</td>
            <td>${linkHtml}</td>
            <td class="text-center">${actionHtml}</td>
        `;

        dom.evidenceTableBody.prepend(tr);

        const editBtn = tr.querySelector('.btn-edit-evidence');
        if (editBtn) attachEditHandler(editBtn);
        renumberEvidenceRows();
    };

    const handleSaveEvidence = async () => {
        if (!dom.evidenceForm.checkValidity()) {
            dom.evidenceForm.reportValidity();
            return;
        }

        const formData = new FormData(dom.evidenceForm);
        const data = Object.fromEntries(formData.entries());

        const originalText = dom.saveEvidenceBtn.innerHTML;
        dom.saveEvidenceBtn.disabled = true;
        dom.saveEvidenceBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

        try {
            let url = '{{ route("assessment-eval.evidence.store", $evalId) }}';
            let method = 'POST';

            if (state.isEditing && state.currentEvidenceId) {
                url = `/assessment-eval/evidence/${state.currentEvidenceId}`;
                method = 'PUT';
            }

            const response = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            if (response.ok && result.success) {
                const modalInstance = bootstrap.Modal.getInstance(document.getElementById('addEvidenceModal'));
                modalInstance.hide();
                resetForm();
                await Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: result.message || 'Evidence saved successfully!',
                    timer: 1500,
                    showConfirmButton: false
                });
                window.location.reload();
            } else {
                throw new Error(result.message || 'Failed to save evidence');
            }
        } catch (error) {
            console.error('Error saving evidence:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'An error occurred while saving evidence.'
            });
        } finally {
            dom.saveEvidenceBtn.disabled = false;
            dom.saveEvidenceBtn.innerHTML = originalText;
        }
    };

    /* --------------------
     * Import helpers
     * -------------------- */
    const getFilteredImportList = () => {
        const entries = Object.entries(state.importFilters || {});
        if (!entries.length) return state.importList;
        return state.importList.filter((item) => {
            return entries.every(([field, value]) => {
                if (!value) return true;
                const hay = (item[field] ?? '').toString().toLowerCase();
                return hay.includes(value.toLowerCase());
            });
        });
    };

    const renderImportTable = () => {
        if (!dom.importTableBody) return;
        dom.importTableBody.innerHTML = '';

        const filtered = getFilteredImportList();

        filtered.forEach((item, idx) => {
            const key = evidenceKey(item);
            const isDuplicate = state.existingEvidenceKeys.has(key);
            const originalIndex = typeof item.__idx === 'number' ? item.__idx : idx;

            const row = document.createElement('tr');
            row.dataset.index = originalIndex;
            row.innerHTML = `
                <td class="text-center">
                    <input type="checkbox" name="importEvidence" value="${originalIndex}" ${isDuplicate ? 'disabled' : ''} />
                </td>
                <td class="text-center">${idx + 1}</td>
                <td>${item.judul_dokumen || '-'}</td>
                <td>${item.no_dokumen || '-'}</td>
                <td class="text-center">${item.grup || '-'}</td>
                <td>${item.tipe || '-'}</td>
                <td class="text-center">${item.tahun_terbit || '-'}</td>
                <td class="text-center">${item.tahun_kadaluarsa || '-'}</td>
                <td>${item.pemilik_dokumen || '-'}</td>
                <td>${item.pengesahan || '-'}</td>
                <td class="text-center">${item.klasifikasi || '-'}</td>
                <td>${item.summary || '-'}</td>
            `;

            const checkbox = row.querySelector('input[type="checkbox"]');
            if (!isDuplicate) {
                checkbox.checked = state.selectedImportIndices.has(originalIndex);
                checkbox.addEventListener('change', (e) => {
                    if (e.target.checked) {
                        state.selectedImportIndices.add(originalIndex);
                    } else {
                        state.selectedImportIndices.delete(originalIndex);
                    }
                    updateImportButtonState();
                    updateImportCheckAllState();
                });
            } else {
                checkbox.title = 'Already added';
                row.classList.add('table-light');
            }

            dom.importTableBody.appendChild(row);
        });

        updateImportCheckAllState();
    };

    const loadPreviousEvidences = async () => {
        resetImportState();
        setImportLoading(true);

        try {
            const url = `/assessment-eval/{{ $evalId }}/evidence/previous`;
            const response = await fetch(url, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (!response.ok) throw new Error('Failed to load previous evidences');

            const result = await response.json();
            const list = Array.isArray(result.data) ? result.data : [];

            // Filter out evidences already present in current assessment and remove duplicates within the list
            const seenKeys = new Set(state.existingEvidenceKeys);
            const filtered = list
                .map((item) => ({ ...item, created_at: item.created_at || null }))
                .sort((a, b) => {
                    const da = a.created_at ? new Date(a.created_at).getTime() : 0;
                    const db = b.created_at ? new Date(b.created_at).getTime() : 0;
                    return db - da; // newest first
                })
                .filter((item) => {
                    const key = evidenceKey(item);
                    if (seenKeys.has(key)) return false;
                    seenKeys.add(key);
                    return true;
                });

            if (!filtered.length) {
                toggleEmptyState(true);
                return;
            }

            state.importList = filtered.map((item, idx) => ({ ...item, __idx: idx }));
            renderImportTable();
        } catch (err) {
            console.error('Load previous evidences error:', err);
            showImportError(err.message || 'Unable to fetch previous evidences.');
        } finally {
            setImportLoading(false);
        }
    };

    const addFromPrevious = async (item, checkboxEl, rowEl) => {
        const key = evidenceKey(item);
        if (state.existingEvidenceKeys.has(key)) {
            checkboxEl.checked = false;
            return false;
        }

        checkboxEl.disabled = true;
        rowEl.classList.add('table-warning');

        try {
            const response = await fetch(`{{ route('assessment-eval.evidence.store', $evalId) }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    judul_dokumen: item.judul_dokumen || '',
                    no_dokumen: item.no_dokumen || '',
                    grup: item.grup || '',
                    tipe: item.tipe || '',
                    tahun_terbit: item.tahun_terbit || null,
                    tahun_kadaluarsa: item.tahun_kadaluarsa || null,
                    pemilik_dokumen: item.pemilik_dokumen || '',
                    pengesahan: item.pengesahan || '',
                    klasifikasi: item.klasifikasi || '',
                    summary: item.summary || '',
                    notes: item.notes || ''
                })
            });

            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || 'Failed to import evidence');

            const newEvidence = result.data || item;
            state.existingEvidenceKeys.add(key);
            rowEl.classList.remove('table-warning');
            rowEl.classList.add('table-success');
            checkboxEl.checked = true;
            checkboxEl.disabled = true;
            state.selectedImportIndices.delete(rowEl.dataset.index ? parseInt(rowEl.dataset.index, 10) : null);
            updateImportButtonState();
            appendEvidenceRow(newEvidence);
            return true;
        } catch (err) {
            console.error('Import error:', err);
            checkboxEl.checked = false;
            checkboxEl.disabled = false;
            rowEl.classList.remove('table-warning');
            Swal.fire({
                icon: 'error',
                title: 'Failed',
                text: err.message || 'Failed to import evidence'
            });
            return false;
        }
    };

    const importSelected = async () => {
        if (!dom.btnUseImported || !state.importList.length) return;

        const confirmation = await Swal.fire({
            icon: 'question',
            title: 'Tambahkan evidence terpilih?',
            text: 'Semua evidence yang dicentang akan disalin ke assessment ini.',
            showCancelButton: true,
            confirmButtonText: 'Ya, tambahkan',
            cancelButtonText: 'Batal'
        });

        if (!confirmation.isConfirmed) return;

        dom.btnUseImported.disabled = true;
        dom.btnUseImported.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menambahkan...';

        try {
            const indices = Array.from(state.selectedImportIndices);
            for (const idx of indices) {
                const item = state.importList.find((entry) => entry.__idx === idx) || state.importList[idx];
                const row = dom.importTableBody.querySelector(`tr[data-index="${idx}"]`);
                const checkbox = row ? row.querySelector('input[type="checkbox"]') : null;
                if (!item || !row || !checkbox) continue;
                await addFromPrevious(item, checkbox, row);
            }
        } finally {
            dom.btnUseImported.innerHTML = 'Tambahkan';
            updateImportButtonState();
        }
    };

    const updateImportCheckAllState = () => {
        if (!dom.importCheckAll || !dom.importTableBody) return;
        const visibleRows = Array.from(dom.importTableBody.querySelectorAll('tr'));
        const checkboxes = visibleRows
            .map(row => row.querySelector('input[type="checkbox"]'))
            .filter(cb => cb && !cb.disabled);
        if (!checkboxes.length) {
            dom.importCheckAll.checked = false;
            dom.importCheckAll.indeterminate = false;
            return;
        }
        const checkedCount = checkboxes.filter(cb => cb.checked).length;
        dom.importCheckAll.checked = checkedCount === checkboxes.length;
        dom.importCheckAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
    };

    const bindImportCheckAll = () => {
        if (!dom.importCheckAll) return;
        dom.importCheckAll.addEventListener('change', (e) => {
            const checked = e.target.checked;
            const rows = Array.from(dom.importTableBody.querySelectorAll('tr'));
            rows.forEach((row) => {
                const cb = row.querySelector('input[type="checkbox"]');
                if (!cb || cb.disabled) return;
                cb.checked = checked;
                const idx = parseInt(row.dataset.index, 10);
                if (Number.isNaN(idx)) return;
                if (checked) {
                    state.selectedImportIndices.add(idx);
                } else {
                    state.selectedImportIndices.delete(idx);
                }
            });
            updateImportButtonState();
            updateImportCheckAllState();
        });
    };

    const resetImportState = () => {
        state.importList = [];
        state.selectedImportIndices.clear();
        if (dom.importTableBody) dom.importTableBody.innerHTML = '';
        toggleEmptyState(false);
        if (dom.importError) dom.importError.classList.add('d-none');
        updateImportButtonState();
    };

    /* --------------------
     * Event bindings
     * -------------------- */
    if (dom.btnAddNew) dom.btnAddNew.addEventListener('click', resetForm);

    if (dom.saveEvidenceBtn) dom.saveEvidenceBtn.addEventListener('click', handleSaveEvidence);

    if (dom.btnImportPrev && importModal) {
        dom.btnImportPrev.addEventListener('click', () => {
            resetImportState();
            loadPreviousEvidences();
        });
    }

    if (dom.importCheckAll) bindImportCheckAll();
    
    if (dom.btnUseImported) dom.btnUseImported.addEventListener('click', importSelected);

    bindImportFilters();
    bindEvidenceTableFilters();
    bindEditButtons(); // Initial bind for server-rendered rows before first JS render

    // Pagination Events
    if (dom.itemsPerPageSelect) {
        dom.itemsPerPageSelect.addEventListener('change', (e) => {
            state.pagination.itemsPerPage = parseInt(e.target.value, 10);
            state.pagination.currentPage = 1;
            applyPagination();
        });
    }

    if (dom.prevBtn) {
        dom.prevBtn.addEventListener('click', () => changePage(-1));
    }

    if (dom.nextBtn) {
        dom.nextBtn.addEventListener('click', () => changePage(1));
    }

    // Initial Render
    applyPagination();
});
</script>

<script>
    window.CURRENT_EVIDENCE_KEYS = @json($evidences->map(fn($e) => strtolower(trim(($e->judul_dokumen ?? '')."|".($e->no_dokumen ?? '')))));
</script>

<style>
.btn-icon {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}
</style>
@endsection
