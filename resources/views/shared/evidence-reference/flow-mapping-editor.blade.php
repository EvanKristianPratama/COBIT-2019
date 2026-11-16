@php
    $savedFlowMappings = $restoreReferenceForm ? old('flow_mappings', [])
        : ($reference?->relationLoaded('infoflowMappings') ? $reference->infoflowMappings->map(fn ($mapping) => [
            'direction' => $mapping->input_id !== null ? 'input' : 'output',
            'flow_id' => $mapping->input_id ?? $mapping->output_id,
        ])->values()->all() : []);
@endphp
<input type="hidden" name="flow_mappings_present" value="1">
<div class="border rounded p-3 mb-3" data-ref-flow-editor data-prefix="{{ $modalId }}"
    data-options="{{ $flowMappingOptions->toJson() }}" data-value="{{ json_encode($savedFlowMappings) }}"
    data-restore="{{ $restoreReferenceForm && $errors->any() ? '1' : '0' }}">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
        <h6 class="mb-0">Asal Referensi · Information Flow</h6>
        <button type="button" class="btn btn-sm btn-outline-primary" data-add-ref-flow>Tambah Pemetaan</button>
    </div>
    <p class="small text-muted mb-3">Input digunakan oleh practice. Output dihasilkan oleh practice. Pilih alur yang sesuai; satu dokumen dapat memiliki beberapa pemetaan.</p>
    <div data-ref-flow-rows></div>
</div>

@once
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-ref-flow-editor]').forEach(editor => {
        const options = JSON.parse(editor.dataset.options);
        const saved = JSON.parse(editor.dataset.value);
        const container = editor.querySelector('[data-ref-flow-rows]');
        let nextIndex = 0;
        function addRow(value = {}) {
            const index = nextIndex++;
            const row = document.createElement('div');
            row.className = 'border-top pt-3 mb-3';
            const grid = document.createElement('div');
            grid.className = 'row g-2';
            function selectField(labelText, field, width) {
                const group = document.createElement('div');
                group.className = width;
                const label = document.createElement('label');
                label.className = 'form-label small';
                label.textContent = labelText;
                const select = document.createElement('select');
                select.className = 'form-select form-select-sm';
                select.id = `${editor.dataset.prefix}-flow-${index}-${field}`;
                label.htmlFor = select.id;
                select.required = true;
                group.append(label, select);
                grid.append(group);
                return select;
            }
            const direction = selectField('Jenis alur', 'direction', 'col-md-3');
            direction.name = `flow_mappings[${index}][direction]`;
            direction.add(new Option('Output', 'output'));
            direction.add(new Option('Input', 'input'));
            direction.value = value.direction || 'output';
            const practice = selectField('Practice', 'practice', 'col-md-9');
            practice.add(new Option('Pilih practice', ''));
            const practices = new Map(options.map(option => [option.practice_id, option]));
            practices.forEach(option => practice.add(new Option(`${option.practice_code} — ${option.practice_name}`, option.practice_id)));
            const flow = selectField('Input / Output', 'flow_id', 'col-12');
            flow.name = `flow_mappings[${index}][flow_id]`;
            const endpoint = document.createElement('div');
            endpoint.className = 'small text-muted mt-2';
            endpoint.style.whiteSpace = 'pre-line';
            function showEndpoint() {
                const selected = options.find(option => option.direction === direction.value && String(option.id) === flow.value);
                endpoint.textContent = selected ? `${direction.value === 'input' ? 'Dari' : 'Ke'}: ${selected.endpoint || '-'}` : '';
            }
            function updateFlows(selectedId = '') {
                flow.replaceChildren(new Option('Pilih alur Information Flow', ''));
                options.filter(option => option.direction === direction.value && option.practice_id === practice.value)
                    .forEach(option => flow.add(new Option(option.description.replace(/\s+/g, ' '), option.id)));
                flow.value = String(selectedId);
                showEndpoint();
            }
            const selected = options.find(option => option.direction === direction.value && Number(option.id) === Number(value.flow_id));
            practice.value = selected?.practice_id || '';
            updateFlows(value.flow_id || '');
            direction.addEventListener('change', () => updateFlows());
            practice.addEventListener('change', () => updateFlows());
            flow.addEventListener('change', showEndpoint);
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn btn-link btn-sm text-danger text-decoration-none p-0 mt-2';
            remove.textContent = 'Hapus pemetaan';
            remove.addEventListener('click', () => row.remove());
            row.append(grid, endpoint, remove);
            container.append(row);
        }
        if (Array.isArray(saved)) saved.forEach(addRow);
        else Object.values(saved).forEach(addRow);
        editor.querySelector('[data-add-ref-flow]').addEventListener('click', () => addRow());
        if (editor.dataset.restore === '1' && window.bootstrap?.Modal) {
            bootstrap.Modal.getOrCreateInstance(editor.closest('.modal')).show();
        }
    });
});
</script>
@endonce
