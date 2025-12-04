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
                <button type="button" class="btn btn-primary rounded-pill" id="btn-add-new" data-bs-toggle="modal" data-bs-target="#addEvidenceModal">
                    <i class="fas fa-plus me-2"></i>Add New Evidence
                </button>
            @endif
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="overflow-x: auto;">
                <table class="table table-bordered table-sm mb-0" style="font-size: 0.9rem; min-width: 1400px;">
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
                    </thead>
                    <tbody>
                        @forelse($evidences as $evidence)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $evidence->judul_dokumen }}</td>
                                <td>{{ $evidence->no_dokumen ?? '-' }}</td>
                                <td class="text-center">{{ $evidence->grup ?? '-' }}</td>
                                <td>{{ $evidence->tipe ?? '-' }}</td>
                                <td class="text-center">{{ $evidence->tahun_terbit ?? '-' }}</td>
                                <td class="text-center">{{ $evidence->tahun_kadaluarsa ?? '-' }}</td>
                                <td>{{ $evidence->pemilik_dokumen ?? '-' }}</td>
                                <td>{{ $evidence->pengesahan ?? '-' }}</td>
                                <td class="text-center">{{ $evidence->klasifikasi ?? '-' }}</td>
                                <td>{{ $evidence->Description ?? '-' }}</td>
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
                            <tr>
                                <td colspan="13" class="text-center py-3 text-muted">
                                    Tidak ada dokumen evidence ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const saveEvidenceBtn = document.getElementById('btn-save-evidence');
    const evidenceForm = document.getElementById('addEvidenceForm');
    const modalTitle = document.getElementById('addEvidenceModalLabel');
    const btnAddNew = document.getElementById('btn-add-new');
    let isEditing = false;
    let currentEvidenceId = null;

    function resetForm() {
        evidenceForm.reset();
        document.getElementById('evidence_id').value = '';
        modalTitle.textContent = 'Add New Evidence';
        saveEvidenceBtn.textContent = 'Save Evidence';
        isEditing = false;
        currentEvidenceId = null;
    }

    if (btnAddNew) {
        btnAddNew.addEventListener('click', resetForm);
    }

    document.querySelectorAll('.btn-edit-evidence').forEach(btn => {
        btn.addEventListener('click', function() {
            const evidence = JSON.parse(this.getAttribute('data-evidence'));
            isEditing = true;
            currentEvidenceId = evidence.id;
            
            modalTitle.textContent = 'Edit Evidence';
            saveEvidenceBtn.textContent = 'Update Evidence';
            
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
    });
    
    if(saveEvidenceBtn) {
        saveEvidenceBtn.addEventListener('click', async function() {
            if (!evidenceForm.checkValidity()) {
                evidenceForm.reportValidity();
                return;
            }

            const formData = new FormData(evidenceForm);
            const data = Object.fromEntries(formData.entries());
            
            // Show loading state
            const originalText = saveEvidenceBtn.innerHTML;
            saveEvidenceBtn.disabled = true;
            saveEvidenceBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

            try {
                let url = '{{ route("assessment-eval.evidence.store", $evalId) }}';
                let method = 'POST';

                if (isEditing && currentEvidenceId) {
                    url = `/assessment-eval/evidence/${currentEvidenceId}`;
                    method = 'PUT';
                }

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    // Close modal
                    const modalEl = document.getElementById('addEvidenceModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    modal.hide();
                    
                    // Reset form
                    resetForm();
                    
                    // Show success message
                    await Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: result.message || 'Evidence saved successfully!',
                        timer: 1500,
                        showConfirmButton: false
                    });

                    // Reload page to show new evidence
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
                saveEvidenceBtn.disabled = false;
                saveEvidenceBtn.innerHTML = originalText;
            }
        });
    }
});
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
