@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6">
        @include('assessment.master-evidence.partials.reference-panel', $referenceOverview)

        <div class="card shadow-sm mb-4 hero-card" id="company-evidence">
            <div class="card-header hero-header py-4 d-flex flex-wrap justify-content-between align-items-center gap-3" style="background: linear-gradient(135deg, #081a3d, #0f2b5c); color: #fff; border: 0;">
                <div>
                    <div class="hero-title" style="font-size: 1.5rem; font-weight: 700; letter-spacing: 0.04em;">Evidence Company</div>
                </div>
            </div>
            <div class="card-body hero-body">
                <form method="GET" action="{{ route('master-evidence.index') }}#company-evidence" class="row g-3 align-items-end">
                    @if($selectedReferenceGamo)<input type="hidden" name="reference_gamo" value="{{ $selectedReferenceGamo }}">@endif
                    <div class="col-12 col-lg-8">
                        <label for="organization_id" class="form-label fw-semibold">Pilih company</label>
                        <select class="form-select" id="organization_id" name="organization_id" required>
                            <option value="">Pilih company...</option>
                            @foreach($organizationOptions as $organization)
                                <option value="{{ $organization->organization_id }}" @selected((string) $selectedOrganizationId === (string) $organization->organization_id)>
                                    {{ $organization->organization_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-lg-auto">
                        <button class="btn btn-primary px-4" type="submit" @disabled($organizationOptions->isEmpty())>
                            <i class="fas fa-search me-2"></i>Tampilkan Evidence
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if($organizationOptions->isEmpty())
            <div class="alert alert-info mb-0">
                Belum ada company yang ditetapkan untuk akun ini.
            </div>
        @elseif(!$selectedOrganization)
            <div class="alert alert-info">
                Pilih company di atas untuk melihat atau menambahkan evidence.
            </div>
        @else
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('master-evidence.index') }}#company-evidence" class="row g-2 align-items-end">
                        <input type="hidden" name="organization_id" value="{{ $selectedOrganizationId }}">
                        <input type="hidden" name="reference_gamo" value="{{ $selectedReferenceGamo }}">
                        <div class="col-12 col-lg-5">
                            <label for="evidence_search" class="form-label small fw-semibold mb-1">Cari evidence</label>
                            <input type="search" class="form-control form-control-sm" id="evidence_search" name="search" value="{{ $searchTerm }}" placeholder="Judul, nomor, deskripsi, assessment...">
                        </div>
                        <div class="col-6 col-lg-2">
                            <label for="evidence_group_filter" class="form-label small fw-semibold mb-1">Grup</label>
                            <select class="form-select form-select-sm" id="evidence_group_filter" name="grup">
                                <option value="">Semua grup</option>
                                @foreach(['EDM', 'APO', 'BAI', 'DSS', 'MEA'] as $group)
                                    <option value="{{ $group }}" @selected($selectedGroup === $group)>{{ $group }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-lg-2">
                            <label for="evidence_type_filter" class="form-label small fw-semibold mb-1">Tipe</label>
                            <select class="form-select form-select-sm" id="evidence_type_filter" name="tipe">
                                <option value="">Semua tipe</option>
                                <option value="Design" @selected($selectedType === 'Design')>Design</option>
                                <option value="Implementation" @selected($selectedType === 'Implementation')>Implementation</option>
                            </select>
                        </div>
                        <div class="col-12 col-lg-auto d-flex gap-2">
                            <button type="submit" class="btn btn-sm btn-primary px-3">
                                <i class="fas fa-search me-1"></i>Filter
                            </button>
                            <a href="{{ route('master-evidence.index', ['organization_id' => $selectedOrganizationId, 'reference_gamo' => $selectedReferenceGamo]) }}#company-evidence" class="btn btn-sm btn-outline-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="mb-1 fw-bold text-primary">{{ $selectedOrganization->organization_name }}</h5>
                        <span class="text-muted small">{{ number_format($evidences->total()) }} evidence ditemukan</span>
                    </div>
                    @if($canManageMasterEvidence)
                        <button type="button" class="btn btn-primary rounded-pill px-3 fw-bold" id="addMasterEvidenceButton" data-bs-toggle="modal" data-bs-target="#addMasterEvidenceModal">
                            <i class="fas fa-plus me-2"></i>Tambah Evidence
                        </button>
                    @endif
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm align-middle mb-0" style="table-layout: fixed; min-width: 1180px; font-size: .875rem;">
                        <thead class="table-secondary text-center align-middle">
                            <tr>
                                <th style="width: 45px;">No</th>
                                <th style="width: 180px;">Judul Evidence</th>
                                @if($referenceCatalogReady)<th style="width: 170px;">Ref Kebutuhan</th>@endif
                                <th style="width: 110px;">No. Dokumen</th>
                                <th style="width: 210px;">Deskripsi</th>
                                <th style="width: 90px;">Grup</th>
                                <th style="width: 105px;">Tipe</th>
                                <th style="width: 95px;">Tahun</th>
                                <th style="width: 120px;">Pemilik</th>
                                <th style="width: 100px;">Klasifikasi</th>
                                <th style="width: 110px;">Digunakan di</th>
                                <th style="width: 55px;">Link</th>
                                @if($canManageMasterEvidence)
                                    <th style="width: 150px;">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($evidences as $evidence)
                                @php
                                    $evidenceFormData = $evidence->only([
                                        'id', 'judul_dokumen', 'no_dokumen', 'tahun_terbit', 'tahun_kadaluarsa',
                                        'tipe', 'pengesahan', 'pemilik_dokumen', 'klasifikasi', 'grup', 'link', 'ket_tipe', 'summary',
                                    ]);
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $evidences->firstItem() + $loop->index }}</td>
                                    <td class="fw-semibold" title="{{ $evidence->judul_dokumen }}"><div class="text-truncate" style="max-width: 170px;">{{ $evidence->judul_dokumen ?: '-' }}</div></td>
                                    @if($referenceCatalogReady)
                                        <td title="{{ $evidence->references->map(fn ($ref) => $ref->objective_id.' · '.$ref->nama_dokumen)->join('; ') }}">
                                            @if($evidence->references->isNotEmpty())
                                                <div class="text-truncate small">{{ $evidence->references->first()->nama_dokumen }}</div>
                                                <div class="small text-muted text-truncate">{{ $evidence->references->pluck('objective_id')->unique()->join(', ') }} · {{ $evidence->references->count() }} ref</div>
                                            @else<span class="text-muted">-</span>@endif
                                        </td>
                                    @endif
                                    <td class="text-truncate" title="{{ $evidence->no_dokumen ?? '-' }}">{{ $evidence->no_dokumen ?: '-' }}</td>
                                    <td title="{{ $evidence->summary ?? '-' }}"><div class="text-truncate" style="max-width: 200px;">{{ $evidence->summary ?: '-' }}</div></td>
                                    <td class="text-center text-truncate" title="{{ $evidence->grup ?? '-' }}">{{ $evidence->grup ?: '-' }}</td>
                                    <td class="text-truncate" title="{{ $evidence->ket_tipe ?: $evidence->tipe ?: '-' }}">{{ $evidence->ket_tipe ?: $evidence->tipe ?: '-' }}</td>
                                    <td class="text-center text-nowrap">
                                        {{ $evidence->tahun_terbit ?: '-' }}
                                        @if($evidence->tahun_kadaluarsa)
                                            <span class="text-muted">– {{ $evidence->tahun_kadaluarsa }}</span>
                                        @endif
                                    </td>
                                    <td class="text-truncate" title="{{ $evidence->pemilik_dokumen ?? '-' }}">{{ $evidence->pemilik_dokumen ?: '-' }}</td>
                                    <td class="text-truncate" title="{{ $evidence->klasifikasi ?? '-' }}">{{ $evidence->klasifikasi ?: '-' }}</td>
                                    <td class="text-center text-nowrap">
                                        @if($evidence->eval_id && $evidence->evaluation)
                                            <a href="{{ route('assessment.evidence.index', $evidence->eval_id) }}" class="badge bg-light text-primary border text-decoration-none" title="Digunakan di assessment #{{ $evidence->eval_id }}, tahun {{ $evidence->evaluation->tahun ?? '-' }}">
                                                #{{ $evidence->eval_id }} · {{ $evidence->evaluation->tahun ?? '-' }}
                                            </a>
                                        @else
                                            <span class="badge bg-primary-subtle text-primary">Master</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php($linkScheme = strtolower((string) parse_url($evidence->link ?? '', PHP_URL_SCHEME)))
                                        @if(in_array($linkScheme, ['http', 'https'], true))
                                            <a href="{{ $evidence->link }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary" aria-label="Buka dokumen">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    @if($canManageMasterEvidence)
                                        <td class="text-center">
                                            @if($evidence->eval_id === null || in_array((int) $evidence->eval_id, $manageableEvaluationIds, true))
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-primary me-1"
                                                        data-edit-master-evidence
                                                        data-evidence="{{ json_encode($evidenceFormData) }}">
                                                    <i class="fas fa-edit me-1"></i>Edit
                                                </button>
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-danger"
                                                        data-delete-master-evidence="{{ $evidence->id }}"
                                                        data-assessment-id="{{ $evidence->eval_id }}">
                                                    <i class="fas fa-trash-alt me-1"></i>Hapus
                                                </button>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ 11 + (int) $canManageMasterEvidence + (int) $referenceCatalogReady }}" class="text-center py-4 text-muted">Tidak ada evidence yang sesuai dengan filter.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($evidences->hasPages())
                    <div class="card-footer bg-white d-flex justify-content-end">
                        {{ $evidences->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        @endif
    </div>

    @if($selectedOrganization && $canManageMasterEvidence)
        <div class="modal fade" id="addMasterEvidenceModal" tabindex="-1" aria-labelledby="addMasterEvidenceModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addMasterEvidenceModalLabel">Tambah Evidence</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <form id="masterEvidenceForm">
                        <div class="modal-body">
                            <input type="hidden" name="evidence_id" value="">
                            <input type="hidden" name="organization_id" value="{{ $selectedOrganizationId }}">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="master_evidence_judul" class="form-label">Judul Dokumen <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="master_evidence_judul" name="judul_dokumen" maxlength="255" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="master_evidence_no" class="form-label">No. Dokumen</label>
                                    <input type="text" class="form-control" id="master_evidence_no" name="no_dokumen" maxlength="100">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label d-block">Grup</label>
                                    <div class="d-flex flex-wrap gap-3" role="group" aria-label="Pilih satu atau lebih grup">
                                        @foreach(['EDM', 'APO', 'BAI', 'DSS', 'MEA'] as $group)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="master_evidence_grup_{{ strtolower($group) }}" name="grup_options[]" value="{{ $group }}">
                                                <label class="form-check-label" for="master_evidence_grup_{{ strtolower($group) }}">{{ $group }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="form-text">Pilih satu atau lebih grup.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="master_evidence_tipe" class="form-label">Tipe Dokumen</label>
                                    <select class="form-select" id="master_evidence_tipe" name="tipe">
                                        <option value="">Pilih tipe...</option>
                                        <option value="Design">Design</option>
                                        <option value="Implementation">Implementation</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="master_evidence_ket_tipe" class="form-label">Keterangan Tipe</label>
                                    <input type="text" class="form-control" id="master_evidence_ket_tipe" name="ket_tipe" maxlength="255">
                                </div>
                                <div class="col-12">
                                    <label for="master_evidence_summary" class="form-label">Deskripsi</label>
                                    <textarea class="form-control" id="master_evidence_summary" name="summary" rows="3"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="master_evidence_tahun_terbit" class="form-label">Tahun Terbit</label>
                                    <input type="number" class="form-control" id="master_evidence_tahun_terbit" name="tahun_terbit" min="1900" max="2099">
                                </div>
                                <div class="col-md-6">
                                    <label for="master_evidence_tahun_kadaluarsa" class="form-label">Tahun Kadaluarsa</label>
                                    <input type="number" class="form-control" id="master_evidence_tahun_kadaluarsa" name="tahun_kadaluarsa" min="1900" max="2099">
                                </div>
                                <div class="col-md-6">
                                    <label for="master_evidence_pemilik" class="form-label">Pemilik Dokumen</label>
                                    <input type="text" class="form-control" id="master_evidence_pemilik" name="pemilik_dokumen" maxlength="255">
                                </div>
                                <div class="col-md-6">
                                    <label for="master_evidence_pengesahan" class="form-label">Pengesahan</label>
                                    <input type="text" class="form-control" id="master_evidence_pengesahan" name="pengesahan" maxlength="255">
                                </div>
                                <div class="col-md-6">
                                    <label for="master_evidence_klasifikasi" class="form-label">Klasifikasi</label>
                                    <select class="form-select" id="master_evidence_klasifikasi" name="klasifikasi">
                                        <option value="">Pilih klasifikasi...</option>
                                        @foreach(['Public', 'Internal', 'Confidential', 'Restricted'] as $classification)
                                            <option value="{{ $classification }}">{{ $classification }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="master_evidence_link" class="form-label">Link Dokumen</label>
                                    <input type="text" class="form-control" id="master_evidence_link" name="link" placeholder="https://...">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary" id="saveMasterEvidenceButton">
                                <i class="fas fa-save me-2"></i>Simpan Evidence
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        @php($referenceSelectionOptions = $evidenceReferenceOptions->map(fn ($ref) => ['id' => (int) $ref->id, 'label' => $ref->objective_id.' · '.$ref->nama_dokumen])->values())
        <script>
            const masterEvidenceForm = document.getElementById('masterEvidenceForm');
            const masterEvidenceModalElement = document.getElementById('addMasterEvidenceModal');
            const masterEvidenceModalTitle = document.getElementById('addMasterEvidenceModalLabel');
            const saveMasterEvidenceButton = document.getElementById('saveMasterEvidenceButton');
            const referenceOptions = new Map(@json($referenceSelectionOptions).map((ref) => [String(ref.id), ref.label]));
            const referenceSelectionKey = @json('master-evidence-ref-selection:'.auth()->id().':'.$selectedOrganizationId);
            const selectedEvidenceReferenceIds = new Set();

            try {
                const storedSelection = JSON.parse(sessionStorage.getItem(referenceSelectionKey) || '[]');
                if (Array.isArray(storedSelection)) storedSelection.forEach((id) => {
                    if (referenceOptions.has(String(id))) selectedEvidenceReferenceIds.add(String(id));
                });
            } catch (_) { /* The current page still works when browser storage is unavailable. */ }

            const refreshReferenceSelection = () => {
                document.querySelectorAll('[data-select-evidence-ref]').forEach((checkbox) => {
                    checkbox.checked = selectedEvidenceReferenceIds.has(checkbox.value);
                });
                const count = document.getElementById('selectedEvidenceReferenceCount');
                const summary = document.getElementById('selectedEvidenceReferenceSummary');
                if (count) count.textContent = `${selectedEvidenceReferenceIds.size} ref dipilih`;
                if (summary) {
                    const labels = [...selectedEvidenceReferenceIds].map((id) => referenceOptions.get(id));
                    summary.textContent = labels.join('; ') || 'Pilih ref untuk evidence baru sebelum menekan Tambah Evidence.';
                    summary.title = labels.join('; ');
                }
                try { sessionStorage.setItem(referenceSelectionKey, JSON.stringify([...selectedEvidenceReferenceIds])); } catch (_) {}
            };

            document.querySelectorAll('[data-select-evidence-ref]').forEach((checkbox) => {
                checkbox.addEventListener('change', () => {
                    if (checkbox.checked) selectedEvidenceReferenceIds.add(checkbox.value);
                    else selectedEvidenceReferenceIds.delete(checkbox.value);
                    refreshReferenceSelection();
                });
            });
            document.getElementById('clearEvidenceReferenceSelection')?.addEventListener('click', () => {
                selectedEvidenceReferenceIds.clear();
                refreshReferenceSelection();
            });
            refreshReferenceSelection();

            const resetMasterEvidenceForm = () => {
                masterEvidenceForm.reset();
                masterEvidenceForm.elements.namedItem('evidence_id').value = '';
                masterEvidenceModalTitle.textContent = 'Tambah Evidence';
                saveMasterEvidenceButton.innerHTML = '<i class="fas fa-save me-2"></i>Simpan Evidence';
            };

            document.getElementById('addMasterEvidenceButton').addEventListener('click', resetMasterEvidenceForm);

            document.querySelectorAll('[data-edit-master-evidence]').forEach((button) => {
                button.addEventListener('click', () => {
                    const evidence = JSON.parse(button.dataset.evidence);

                    masterEvidenceForm.elements.namedItem('evidence_id').value = evidence.id;
                    [
                        'judul_dokumen', 'no_dokumen', 'tipe', 'ket_tipe', 'summary',
                        'tahun_terbit', 'tahun_kadaluarsa', 'pemilik_dokumen',
                        'pengesahan', 'klasifikasi', 'link'
                    ].forEach((field) => {
                        masterEvidenceForm.elements.namedItem(field).value = evidence[field] ?? '';
                    });

                    const selectedGroups = (evidence.grup ?? '')
                        .split(',')
                        .map((group) => group.trim())
                        .filter(Boolean);
                    masterEvidenceForm.querySelectorAll('input[name="grup_options[]"]').forEach((checkbox) => {
                        checkbox.checked = selectedGroups.includes(checkbox.value);
                    });

                    masterEvidenceModalTitle.textContent = 'Edit Evidence';
                    saveMasterEvidenceButton.innerHTML = '<i class="fas fa-save me-2"></i>Simpan Perubahan';
                    bootstrap.Modal.getOrCreateInstance(masterEvidenceModalElement).show(button);
                });
            });

            masterEvidenceForm.addEventListener('submit', async function (event) {
                event.preventDefault();

                const form = event.currentTarget;
                if (!form.reportValidity()) return;

                const submitButton = saveMasterEvidenceButton;
                const originalButtonHtml = submitButton.innerHTML;
                const evidenceId = form.elements.namedItem('evidence_id').value;
                const isEditing = Boolean(evidenceId);
                const endpoint = isEditing
                    ? @json(route('master-evidence.update', '__EVIDENCE_ID__')).replace('__EVIDENCE_ID__', encodeURIComponent(evidenceId))
                    : @json(route('master-evidence.store'));
                const formData = new FormData(form);
                const evidenceData = Object.fromEntries(formData.entries());
                delete evidenceData.evidence_id;
                if (!isEditing && @json($referenceCatalogReady)) {
                    evidenceData.reference_ids = [...selectedEvidenceReferenceIds].map(Number);
                }
                evidenceData.grup = Array.from(form.querySelectorAll('input[name="grup_options[]"]:checked'))
                    .map((input) => input.value)
                    .join(', ');
                delete evidenceData['grup_options[]'];

                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Menyimpan...';

                try {
                    const response = await fetch(endpoint, {
                        method: isEditing ? 'PUT' : 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': @json(csrf_token())
                        },
                        body: JSON.stringify(evidenceData)
                    });

                    const result = await response.json();
                    if (!response.ok || !result.success) {
                        throw new Error(result.message || Object.values(result.errors || {}).flat()[0] || 'Evidence gagal disimpan.');
                    }

                    if (!isEditing) {
                        selectedEvidenceReferenceIds.clear();
                        refreshReferenceSelection();
                    }
                    window.location.reload();
                } catch (error) {
                    await Swal.fire({
                        icon: 'error',
                        title: 'Gagal menyimpan evidence',
                        text: error.message
                    });
                } finally {
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonHtml;
                }
            });

            document.querySelectorAll('[data-delete-master-evidence]').forEach((button) => {
                button.addEventListener('click', async () => {
                    const confirmation = await Swal.fire({
                        icon: 'warning',
                        title: 'Hapus evidence?',
                        text: button.dataset.assessmentId
                            ? `Evidence ini tertaut ke assessment #${button.dataset.assessmentId}. Penghapusan juga akan menghilangkannya dari assessment tersebut.`
                            : 'Evidence master akan dihapus dari library company ini.',
                        input: 'text',
                        inputLabel: 'Ketik "Saya Yakin" untuk melanjutkan',
                        inputPlaceholder: 'Saya Yakin',
                        inputValidator: (value) => value === 'Saya Yakin' ? undefined : 'Ketik persis: Saya Yakin',
                        showCancelButton: true,
                        confirmButtonText: 'Hapus Evidence',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#dc3545'
                    });

                    if (!confirmation.isConfirmed) return;

                    try {
                        const deleteUrl = @json(route('master-evidence.destroy', '__EVIDENCE_ID__'))
                            .replace('__EVIDENCE_ID__', encodeURIComponent(button.dataset.deleteMasterEvidence));
                        const response = await fetch(deleteUrl, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': @json(csrf_token())
                            },
                            body: JSON.stringify({
                                organization_id: @json($selectedOrganizationId),
                                confirmation: confirmation.value
                            })
                        });
                        const result = await response.json();

                        if (!response.ok || !result.success) {
                            throw new Error(result.message || Object.values(result.errors || {}).flat()[0] || 'Evidence gagal dihapus.');
                        }

                        window.location.reload();
                    } catch (error) {
                        await Swal.fire({
                            icon: 'error',
                            title: 'Gagal menghapus evidence',
                            text: error.message
                        });
                    }
                });
            });
        </script>
    @endif
@endsection
