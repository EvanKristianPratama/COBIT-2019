<style>
    .evidence-tracking-table { min-width: 850px; table-layout: fixed; }
    .evidence-tracking-table > thead > tr > th { width: 33.333%; background: #112d60; color: #fff; padding: .75rem 1rem; }
    .evidence-tracking-table > tbody > tr > td { vertical-align: top; padding: 1rem; background: #fff; }
    .evidence-tracking-process + .evidence-tracking-process { margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #dce2eb; }
    .evidence-tracking-title { margin: .35rem 0 .75rem; font-size: .85rem; color: #526174; }
    .evidence-tracking-item { margin: .55rem 0; font-size: .9rem; }
    .evidence-tracking-item summary { cursor: pointer; overflow-wrap: anywhere; }
    .evidence-tracking-detail { margin: .5rem 0 .75rem; white-space: pre-line; font-size: .85rem; }
    .evidence-tracking-more > summary { margin-top: .75rem; color: #153e76; cursor: pointer; font-size: .85rem; }
    .evidence-tracking-note { margin-top: 1rem; font-size: .8rem; color: #687487; }
    .evidence-tracking-note summary { cursor: pointer; }
</style>
<div id="masterEvidenceTrackingView" hidden>
    <form id="masterEvidenceTrackingForm" class="row g-3 align-items-end mb-3">
        <div class="col-md-3">
            <label for="trackingEvidenceModel" class="form-label">Model COBIT 2019</label>
            <select id="trackingEvidenceModel" name="focus_area" class="form-select"></select>
        </div>
        <div class="col-md-5">
            <label for="trackingEvidenceGamo" class="form-label">Mulai dari GAMO</label>
            <select id="trackingEvidenceGamo" name="objective_id" class="form-select"></select>
        </div>
        <div class="col-md-4">
            <label for="trackingEvidenceKeyword" class="form-label">Kata kunci</label>
            <div class="input-group">
                <input id="trackingEvidenceKeyword" name="q" type="search" maxlength="255" class="form-control" placeholder="Cari proses atau evidence">
                <button class="btn btn-primary" type="submit">Cari</button>
            </div>
        </div>
    </form>
    <p class="small text-muted mb-2">Pemetaan proses lintas versi COBIT. Buka nama evidence untuk melihat detailnya.</p>
    <div class="table-responsive">
        <table class="table table-bordered evidence-tracking-table mb-0" aria-label="Evidence lintas versi COBIT 2019, COBIT 5, dan COBIT 4.1">
            <thead><tr>
                <th scope="col">COBIT 2019 <span class="float-end" aria-hidden="true">→</span><div class="small fw-normal" id="trackingModel2019"></div></th>
                <th scope="col">COBIT 5 <span class="float-end" aria-hidden="true">→</span><div class="small fw-normal" id="trackingModel5"></div></th>
                <th scope="col">COBIT 4.1<div class="small fw-normal" id="trackingModel41"></div></th>
            </tr></thead>
            <tbody id="masterEvidenceTrackingBody"></tbody>
        </table>
    </div>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
        <div id="masterEvidenceTrackingStatus" class="small text-muted" role="status" aria-live="polite">Pilih GAMO untuk melihat evidence lintas versi COBIT.</div>
        <div class="d-flex align-items-center gap-2">
            <button id="trackingEvidencePrevious" type="button" class="btn btn-sm btn-outline-primary" disabled>Sebelumnya</button>
            <span id="trackingEvidencePage" class="small">Halaman 1</span>
            <button id="trackingEvidenceNext" type="button" class="btn btn-sm btn-outline-primary" disabled>Berikutnya</button>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const trackingMetadata = @json($masterEvidenceFilters);
    const form = document.getElementById('masterEvidenceTrackingForm');
    const model = document.getElementById('trackingEvidenceModel');
    const gamo = document.getElementById('trackingEvidenceGamo');
    const body = document.getElementById('masterEvidenceTrackingBody');
    const status = document.getElementById('masterEvidenceTrackingStatus');
    const previous = document.getElementById('trackingEvidencePrevious');
    const next = document.getElementById('trackingEvidenceNext');
    const pageLabel = document.getElementById('trackingEvidencePage');
    const listView = document.getElementById('masterEvidenceListView');
    const trackingView = document.getElementById('masterEvidenceTrackingView');
    const listButton = document.getElementById('masterEvidenceListMode');
    const trackingButton = document.getElementById('masterEvidenceTrackingMode');
    let loaded = false;
    let page = 1;
    let pending;
    let sequence = 0;

    function element(tag, className, text) {
        const node = document.createElement(tag);
        node.className = className;
        if (text !== undefined) node.textContent = text;
        return node;
    }

    function updateGamo(preferred = '') {
        const rows = trackingMetadata.objectives.filter(row => String(row.version) === '2019' && String(row.focus_area_id) === model.value);
        gamo.replaceChildren(new Option('Semua GAMO', ''));
        rows.forEach(row => gamo.add(new Option(`${row.objective_id.split('.')[0]} — ${row.objective}`, row.objective_id)));
        gamo.value = rows.some(row => row.objective_id === preferred) ? preferred : '';
    }

    function renderEvidence(item, parent) {
        const title = String(item.name || 'Evidence tanpa nama').replace(/\s+/g, ' ').trim();
        if (!item.description && !item.reference) {
            parent.append(element('div', 'evidence-tracking-item', title));
            return;
        }
        const details = element('details', 'evidence-tracking-item');
        details.append(element('summary', '', title));
        if (item.description) details.append(element('p', 'evidence-tracking-detail', item.description));
        if (item.reference) details.append(element('div', 'small text-muted mb-2', item.reference));
        parent.append(details);
    }

    function renderColumn(column, cell) {
        if (column.processes.length && /parsial|dipisah|AC\d/i.test(column.mapping)) {
            cell.append(element('div', 'small text-muted mb-2', column.mapping));
        }
        column.processes.forEach(process => {
            const section = element('section', 'evidence-tracking-process');
            section.append(element('span', 'badge bg-primary', process.code));
            section.append(element('div', 'evidence-tracking-title', process.title));
            const items = process.evidence;
            if (!items.length) section.append(element('p', 'small text-muted mb-0', 'Belum ada evidence untuk proses ini.'));
            items.slice(0, 3).forEach(item => renderEvidence(item, section));
            if (items.length > 3) {
                const more = element('details', 'evidence-tracking-more');
                more.append(element('summary', '', `Lihat ${items.length - 3} evidence lainnya`));
                items.slice(3).forEach(item => renderEvidence(item, more));
                section.append(more);
            }
            cell.append(section);
        });
        column.missing_codes.forEach(code => cell.append(element('p', 'small text-muted mb-2', `${code} — proses belum tersedia.`)));
        if (!column.processes.length && !column.missing_codes.length) cell.append(element('p', 'small text-muted mb-0', 'Belum ada mapping untuk versi ini.'));
    }

    function message(text) {
        body.replaceChildren();
        const cell = body.insertRow().insertCell();
        cell.colSpan = 3;
        cell.className = 'text-center text-muted py-4';
        cell.textContent = text;
    }

    function render(rows) {
        body.replaceChildren();
        if (!rows.length) return message('Tidak ada pemetaan lintas versi yang sesuai dengan filter.');
        rows.forEach(row => {
            const tableRow = body.insertRow();
            row.columns.forEach(column => renderColumn(column, tableRow.insertCell()));
            if (row.notes) {
                const note = element('details', 'evidence-tracking-note');
                note.append(element('summary', '', 'Catatan mapping'));
                note.append(element('p', 'evidence-tracking-detail', row.notes));
                tableRow.cells[0].append(note);
            }
        });
    }

    async function search(targetPage = 1) {
        loaded = true;
        const request = ++sequence;
        pending?.abort();
        pending = new AbortController();
        previous.disabled = next.disabled = true;
        form.querySelector('button[type="submit"]').disabled = true;
        status.textContent = 'Memuat evidence lintas versi…';
        message('Memuat mapping dan evidence…');
        const params = new URLSearchParams(new FormData(form));
        for (const [key, value] of Array.from(params.entries())) if (!value) params.delete(key);
        params.set('page', targetPage);
        try {
            const response = await fetch(@json(route('cobit_component.master-evidence.tracking')) + '?' + params, {
                headers: {'Accept': 'application/json'}, signal: pending.signal,
            });
            const result = await response.json();
            if (request !== sequence) return;
            if (!response.ok) throw new Error(Object.values(result.errors || {}).flat()[0] || 'Evidence lintas versi gagal dimuat. Silakan coba lagi.');
            result.models.forEach(item => {
                document.getElementById(`trackingModel${item.version.replace('.', '')}`).textContent = item.name || 'Model belum tersedia';
            });
            const data = result.tracking;
            page = data.current_page;
            render(data.data);
            status.textContent = `Menampilkan ${data.from || 0}–${data.to || 0} dari ${data.total} GAMO`;
            pageLabel.textContent = `Halaman ${page} dari ${data.last_page}`;
            previous.disabled = page <= 1;
            next.disabled = page >= data.last_page;
        } catch (error) {
            if (request !== sequence || error.name === 'AbortError') return;
            message(error.message);
            status.textContent = 'Evidence lintas versi gagal dimuat. Ulangi pencarian untuk mencoba lagi.';
            pageLabel.textContent = '—';
        } finally {
            if (request === sequence) form.querySelector('button[type="submit"]').disabled = false;
        }
    }

    function setMode(tracking) {
        listView.hidden = tracking;
        trackingView.hidden = !tracking;
        trackingButton.classList.toggle('active', tracking);
        listButton.classList.toggle('active', !tracking);
        trackingButton.setAttribute('aria-pressed', String(tracking));
        listButton.setAttribute('aria-pressed', String(!tracking));
        if (tracking && !loaded) search();
    }

    const models = trackingMetadata.models.filter(row => String(row.version) === '2019');
    models.forEach(row => model.add(new Option(row.name, row.id)));
    const initial = models.find(row => String(row.id) === @json((string) $focusAreaId)) || models[0];
    model.value = initial ? String(initial.id) : '';
    const current = @json($objective->objective_id);
    const first = trackingMetadata.objectives.find(row => String(row.focus_area_id) === model.value && row.objective_id.split('.')[0] === 'EDM01');
    updateGamo(trackingMetadata.objectives.some(row => row.objective_id === current && String(row.version) === '2019') ? current : first?.objective_id);
    form.addEventListener('submit', event => { event.preventDefault(); search(); });
    model.addEventListener('change', () => { updateGamo(); search(); });
    gamo.addEventListener('change', () => search());
    previous.addEventListener('click', () => search(page - 1));
    next.addEventListener('click', () => search(page + 1));
    trackingButton.addEventListener('click', () => setMode(true));
    listButton.addEventListener('click', () => setMode(false));
    document.getElementById('master-evidence-tab').addEventListener('shown.bs.tab', () => { if (!trackingView.hidden && !loaded) search(); });
});
</script>
