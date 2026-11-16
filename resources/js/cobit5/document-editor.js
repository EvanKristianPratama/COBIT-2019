function initializeDocumentEditor() {
    const configElement = document.getElementById('c5EditorData');
    if (!configElement) return;
    const config = JSON.parse(configElement.textContent);
    const documentModal = document.getElementById('c5DocumentEditor');
    const flowModal = document.getElementById('c5FlowEditor');
    let activeFlow;
    let fieldNumber = 0;

    function element(tag, className = '', text = '') {
        const node = document.createElement(tag);
        node.className = className;
        if (text) node.textContent = text;
        return node;
    }

    function field(container, labelText, value = '', options = {}) {
        const wrapper = element('div', 'mb-3');
        const id = `c5-editor-field-${++fieldNumber}`;
        const label = element('label', 'form-label', labelText);
        label.htmlFor = id;
        const control = element(options.tag || 'textarea', 'form-control');
        control.id = id;
        control.value = value || '';
        if (control.tagName === 'TEXTAREA') control.rows = options.rows || 3;
        if (options.required) control.required = true;
        if (options.maxLength) control.maxLength = options.maxLength;
        control.dataset.field = options.name || '';
        wrapper.append(label, control);
        container.append(wrapper);
        return control;
    }

    function removableCard(container) {
        const card = element('div', 'border rounded p-3 mb-3');
        const remove = element('button', 'btn btn-link btn-sm text-danger text-decoration-none p-0 mb-2', 'Hapus');
        remove.type = 'button';
        remove.addEventListener('click', () => card.remove());
        card.append(remove);
        container.append(card);
        return card;
    }

    function goalCard(type, goal = {}) {
        const container = document.getElementById(type === 'it' ? 'c5EditItGoals' : 'c5EditProcessGoals');
        const card = removableCard(container);
        if (type === 'it') field(card, 'Kode goal', goal.code, { tag: 'input', name: 'code', required: true, maxLength: 30 });
        field(card, 'Goal', goal.title, { name: 'title', required: true, maxLength: 5000, rows: 2 });
        field(card, 'Related Metrics — satu metrik per baris', (goal.metrics || []).join('\n'), { name: 'metrics', rows: 4 });
    }

    function clearError(id) {
        const box = document.getElementById(id);
        box.textContent = '';
        box.classList.add('d-none');
    }

    document.querySelector('[data-c5-overview-edit]')?.addEventListener('click', () => {
        clearError('c5DocumentError');
        document.getElementById('c5EditTitle').value = config.overview.title;
        document.getElementById('c5EditDescription').value = config.overview.description;
        document.getElementById('c5EditPurpose').value = config.overview.purpose;
        document.getElementById('c5EditItGoals').replaceChildren();
        document.getElementById('c5EditProcessGoals').replaceChildren();
        config.overview.it_goals.forEach(goal => goalCard('it', goal));
        config.overview.process_goals.forEach(goal => goalCard('process', goal));
        bootstrap.Modal.getOrCreateInstance(documentModal).show();
    });

    document.querySelectorAll('[data-c5-add-goal]').forEach(button => {
        button.addEventListener('click', () => goalCard(button.dataset.c5AddGoal));
    });

    function readGoals(containerId, withCode) {
        return Array.from(document.getElementById(containerId).children).map(card => {
            const goal = {
                title: card.querySelector('[data-field="title"]').value,
                metrics: card.querySelector('[data-field="metrics"]').value.split(/\r?\n/).map(line => line.trim()).filter(Boolean),
            };
            if (withCode) goal.code = card.querySelector('[data-field="code"]').value;
            return goal;
        });
    }

    function inputCard(input = {}) {
        const card = removableCard(document.getElementById('c5EditInputs'));
        card.dataset.id = input.id || '';
        field(card, 'From', input.from, { name: 'from', maxLength: 255, rows: 2 });
        field(card, 'Description', input.description, { name: 'description', maxLength: 20000, rows: 5 });
    }

    function outputCard(output = {}) {
        const card = removableCard(document.getElementById('c5EditOutputs'));
        card.dataset.id = output.id || '';
        const id = `c5-editor-field-${++fieldNumber}`;
        const label = element('label', 'form-label', 'Work Product');
        label.htmlFor = id;
        const select = element('select', 'form-select mb-3');
        select.id = id;
        select.dataset.field = 'work_product_id';
        select.required = true;
        select.add(new Option('Pilih Work Product', ''));
        config.workProducts.forEach(wp => select.add(new Option(`${wp.code} — ${wp.name}`, wp.id)));
        select.value = output.work_product_id || '';
        card.append(label, select);
        field(card, 'To', output.to, { name: 'to', maxLength: 255, rows: 3 });
    }

    document.querySelectorAll('[data-c5-flow-edit]').forEach(button => {
        button.addEventListener('click', () => {
            activeFlow = config.flows[button.dataset.c5FlowEdit];
            if (!activeFlow) return;
            clearError('c5FlowError');
            document.getElementById('c5FlowEditorTitle').textContent = `Edit Information Flow — ${activeFlow.code}`;
            document.getElementById('c5EditInputs').replaceChildren();
            document.getElementById('c5EditOutputs').replaceChildren();
            activeFlow.inputs.forEach(inputCard);
            activeFlow.outputs.forEach(outputCard);
            bootstrap.Modal.getOrCreateInstance(flowModal).show();
        });
    });
    document.getElementById('c5AddInput').addEventListener('click', () => inputCard());
    document.getElementById('c5AddOutput').addEventListener('click', () => outputCard());

    async function save(form, errorId, url, payload, fragment = '') {
        const submit = form.querySelector('[type="submit"]');
        clearError(errorId);
        submit.disabled = true;
        submit.textContent = 'Menyimpan…';
        try {
            const response = await fetch(url, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': config.csrf },
                body: JSON.stringify({ focus_area_id: config.focus_area_id, ...payload }),
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.errors ? Object.values(result.errors).flat().join('\n') : result.message || 'Data gagal disimpan.');
            if (fragment) history.replaceState(null, '', `${location.pathname}${location.search}#${fragment}`);
            location.reload();
        } catch (error) {
            const box = document.getElementById(errorId);
            box.textContent = error.message;
            box.classList.remove('d-none');
        } finally {
            submit.disabled = false;
            submit.textContent = 'Simpan';
        }
    }

    document.getElementById('c5DocumentForm').addEventListener('submit', event => {
        event.preventDefault();
        save(event.currentTarget, 'c5DocumentError', config.overviewUrl, {
            revision: config.revision,
            title: document.getElementById('c5EditTitle').value,
            description: document.getElementById('c5EditDescription').value,
            purpose: document.getElementById('c5EditPurpose').value,
            it_goals: readGoals('c5EditItGoals', true),
            process_goals: readGoals('c5EditProcessGoals', false),
        });
    });
    document.getElementById('c5FlowForm').addEventListener('submit', event => {
        event.preventDefault();
        if (!activeFlow) return;
        const inputs = Array.from(document.getElementById('c5EditInputs').children).map(card => ({
            id: card.dataset.id ? Number(card.dataset.id) : null,
            from: card.querySelector('[data-field="from"]').value,
            description: card.querySelector('[data-field="description"]').value,
        }));
        const outputs = Array.from(document.getElementById('c5EditOutputs').children).map(card => ({
            id: card.dataset.id ? Number(card.dataset.id) : null,
            work_product_id: Number(card.querySelector('[data-field="work_product_id"]').value),
            to: card.querySelector('[data-field="to"]').value,
        }));
        save(event.currentTarget, 'c5FlowError', config.flowUrl, {
            revision: activeFlow.revision, practice_id: activeFlow.practice_id, inputs, outputs,
        }, `c5-flow-${activeFlow.code.replaceAll('.', '-')}`);
    });
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initializeDocumentEditor);
else initializeDocumentEditor();
