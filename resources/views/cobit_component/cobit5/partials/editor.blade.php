@can('design-factors.input')
    @php
        $documents = app(\App\Services\Cobit5\DocumentService::class);
        $overview = $documents->overviewData($document);
        $flows = [];
        foreach ($document['practices'] as $practice) {
            if (empty($practice['practice_id'])) continue;
            $flow = $documents->flowData($practice);
            $flows[$practice['code']] = $flow + ['practice_id' => $practice['practice_id'], 'code' => $practice['code'],
                'title' => $practice['title'], 'revision' => $documents->revision($flow)];
        }
        $editor = [
            'focus_area_id' => $focusAreaId, 'overview' => $overview, 'revision' => $documents->revision($overview), 'flows' => $flows,
            'workProducts' => ($workProducts ?? collect())->map(fn ($row) => ['id' => $row->id, 'code' => $row->wp_id, 'name' => $row->wp])->values(),
            'overviewUrl' => route('cobit5.document.update', ['objectiveId' => $objective->objective_id]),
            'flowUrl' => route('cobit5.flow.update', ['objectiveId' => $objective->objective_id]), 'csrf' => csrf_token(),
        ];
    @endphp
    <script type="application/json" id="c5EditorData">@json($editor)</script>
    <div class="modal fade" id="c5DocumentEditor" tabindex="-1" aria-labelledby="c5DocumentEditorTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable"><form class="modal-content" id="c5DocumentForm">
            <div class="modal-header"><h2 class="modal-title fs-5" id="c5DocumentEditorTitle">Edit Dokumen COBIT 5</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
            <div class="modal-body">
                <div id="c5DocumentError" class="alert alert-danger d-none" role="alert"></div>
                <div class="mb-3"><label class="form-label" for="c5EditTitle">Nama proses</label><input id="c5EditTitle" class="form-control" required maxlength="255"></div>
                <div class="mb-3"><label class="form-label" for="c5EditDescription">Process Description</label><textarea id="c5EditDescription" class="form-control" rows="4" maxlength="20000"></textarea></div>
                <div class="mb-4"><label class="form-label" for="c5EditPurpose">Process Purpose Statement</label><textarea id="c5EditPurpose" class="form-control" rows="4" maxlength="20000"></textarea></div>
                <div class="d-flex justify-content-between align-items-center mb-2"><h3 class="h6 mb-0">IT-related Goals and Metrics</h3><button type="button" class="btn btn-sm btn-outline-primary" data-c5-add-goal="it">Tambah Goal</button></div>
                <div id="c5EditItGoals"></div>
                <div class="d-flex justify-content-between align-items-center mt-4 mb-2"><h3 class="h6 mb-0">Process Goals and Metrics</h3><button type="button" class="btn btn-sm btn-outline-primary" data-c5-add-goal="process">Tambah Goal</button></div>
                <div id="c5EditProcessGoals"></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
        </form></div>
    </div>
    <div class="modal fade" id="c5FlowEditor" tabindex="-1" aria-labelledby="c5FlowEditorTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable"><form class="modal-content" id="c5FlowForm">
            <div class="modal-header"><h2 class="modal-title fs-5" id="c5FlowEditorTitle">Edit Information Flow</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
            <div class="modal-body">
                <div id="c5FlowError" class="alert alert-danger d-none" role="alert"></div>
                <div class="row g-4">
                    <section class="col-lg-6"><div class="d-flex justify-content-between align-items-center mb-3"><h3 class="h6 mb-0">Inputs</h3><button type="button" class="btn btn-sm btn-outline-primary" id="c5AddInput">Tambah Input</button></div><div id="c5EditInputs"></div></section>
                    <section class="col-lg-6"><div class="d-flex justify-content-between align-items-center mb-3"><h3 class="h6 mb-0">Outputs / Work Product</h3><button type="button" class="btn btn-sm btn-outline-primary" id="c5AddOutput">Tambah Output</button></div><div id="c5EditOutputs"></div></section>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
        </form></div>
    </div>
    @push('scripts')
        @vite('resources/js/cobit5/document-editor.js')
    @endpush
@endcan
