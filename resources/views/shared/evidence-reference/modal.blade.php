@php
    $referenceFormId = $reference ? (string) $reference->id : 'new';
    $restoreReferenceForm = (string) old('reference_form_id', '') === $referenceFormId;
@endphp
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Title" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header"><h5 id="{{ $modalId }}Title" class="modal-title">{{ $reference ? 'Edit Ref Dokumen' : 'Tambah Ref Dokumen' }} · {{ $objective->objective_id }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <form method="POST" action="{{ $reference ? route('cobit_component.evidence-ref.update', $reference->id) : route('cobit_component.evidence-ref.store') }}">
            @csrf
            @if($reference) @method('PUT') @endif
            <input type="hidden" name="reference_form_id" value="{{ $referenceFormId }}">
            <input type="hidden" name="focus_area_id" value="{{ $focusAreaId }}"><input type="hidden" name="objective_id" value="{{ $objective->objective_id }}">
            <input type="hidden" name="return_context" value="{{ $referenceReturnContext }}">
            @if($dictionaryOrganizationId)
                <input type="hidden" name="return_organization_id" value="{{ $dictionaryOrganizationId }}">
            @endif
            @if($referenceReturnContext === 'master')
                <input type="hidden" name="return_search" value="{{ $searchTerm }}">
                <input type="hidden" name="return_grup" value="{{ $selectedGroup }}">
                <input type="hidden" name="return_tipe" value="{{ $selectedType }}">
            @endif
            <div class="modal-body">
                <div class="mb-3"><label for="{{ $modalId }}Name" class="form-label">Kebutuhan Dokumen <span class="text-danger">*</span></label><input id="{{ $modalId }}Name" name="nama_dokumen" class="form-control" value="{{ $restoreReferenceForm ? old('nama_dokumen') : $reference?->nama_dokumen }}" maxlength="255" required></div>
                <div class="mb-3"><label for="{{ $modalId }}Description" class="form-label">Penjelasan dalam Bahasa Indonesia</label><textarea id="{{ $modalId }}Description" name="keterangan" class="form-control" rows="3" maxlength="5000">{{ $restoreReferenceForm ? old('keterangan') : $reference?->keterangan }}</textarea></div>
                @if($flowMappingReady ?? false)
                    @include('shared.evidence-reference.flow-mapping-editor')
                @endif
                <div><label for="{{ $modalId }}Source" class="form-label">Catatan Sumber / Referensi Lain (opsional)</label><textarea id="{{ $modalId }}Source" name="referensi_sumber" class="form-control" rows="2" maxlength="2000" placeholder="Sumber lain atau penjelasan tambahan untuk pemetaan">{{ $restoreReferenceForm ? old('referensi_sumber') : $reference?->referensi_sumber }}</textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan Ref</button></div>
        </form>
    </div></div>
</div>
