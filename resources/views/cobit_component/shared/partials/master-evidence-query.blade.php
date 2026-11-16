<div id="masterEvidenceQuery">
    <div class="btn-group mb-3" role="group" aria-label="Tampilan Master Evidence">
        <button type="button" id="masterEvidenceListMode" class="btn btn-outline-primary active" aria-pressed="true">Daftar</button>
        <button type="button" id="masterEvidenceTrackingMode" class="btn btn-outline-primary" aria-pressed="false">Lintas Versi COBIT</button>
    </div>
    @include('cobit_component.shared.partials.master-evidence-tracking')
    <div id="masterEvidenceListView">
    <form id="masterEvidenceQueryForm" class="row g-3 align-items-end mb-3">
        <div class="col-md-2">
            <label for="masterEvidenceVersion" class="form-label">Versi COBIT</label>
            <select id="masterEvidenceVersion" name="version" class="form-select">
                @foreach(['2019' => 'COBIT 2019', '5' => 'COBIT 5', '4.1' => 'COBIT 4.1'] as $version => $label)
                    <option value="{{ $version }}" @selected((string) ($objective->focusArea?->version ?? '2019') === (string) $version)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label for="masterEvidenceModel" class="form-label">Model / Focus Area</label>
            <select id="masterEvidenceModel" name="focus_area" class="form-select"></select>
        </div>
        <div class="col-md-3">
            <label for="masterEvidenceGamo" class="form-label">GAMO</label>
            <select id="masterEvidenceGamo" name="objective_id" class="form-select"></select>
        </div>
        <div class="col-md-4">
            <label for="masterEvidenceKeyword" class="form-label">Kata kunci</label>
            <div class="input-group">
                <input id="masterEvidenceKeyword" name="q" type="search" maxlength="255" class="form-control" placeholder="Cari nama dokumen, WP, atau deskripsi">
                <button class="btn btn-primary" type="submit">Cari</button>
            </div>
        </div>
    </form>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
        <div id="masterEvidenceStatus" class="small text-muted" role="status" aria-live="polite">Pilih filter untuk melihat Master Evidence.</div>
        <div class="d-flex align-items-center gap-2">
            <label for="masterEvidencePageSize" class="small text-nowrap">Baris per halaman</label>
            <select id="masterEvidencePageSize" class="form-select form-select-sm w-auto">
                <option value="25">25</option><option value="50">50</option><option value="100">100</option>
            </select>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-bordered table-striped align-middle mb-0" id="masterEvidenceTable" style="min-width: 900px;">
            <thead class="table-primary">
                <tr>
                    <th scope="col">Model</th>
                    <th scope="col">Focus Area</th>
                    <th scope="col">GAMO</th>
                    <th scope="col">Referensi</th>
                    <th scope="col" style="width: 28%;">Evidence / Work Product</th>
                    <th scope="col" style="width: 32%;">Deskripsi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <div class="d-flex justify-content-end align-items-center gap-2 mt-3">
        <button type="button" id="masterEvidencePrevious" class="btn btn-sm btn-outline-primary" disabled>Sebelumnya</button>
        <span id="masterEvidencePage" class="small">Halaman 1</span>
        <button type="button" id="masterEvidenceNext" class="btn btn-sm btn-outline-primary" disabled>Berikutnya</button>
    </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const metadata = @json($masterEvidenceFilters);
    const form = document.getElementById('masterEvidenceQueryForm');
    const version = document.getElementById('masterEvidenceVersion');
    const model = document.getElementById('masterEvidenceModel');
    const gamo = document.getElementById('masterEvidenceGamo');
    const pageSize = document.getElementById('masterEvidencePageSize');
    const body = document.querySelector('#masterEvidenceTable tbody');
    const status = document.getElementById('masterEvidenceStatus');
    const previous = document.getElementById('masterEvidencePrevious');
    const next = document.getElementById('masterEvidenceNext');
    const pageLabel = document.getElementById('masterEvidencePage');
    let page = 1;
    let loaded = false;
    let requestNumber = 0;
    let pendingRequest;

    function options(select, rows, placeholder, value, label, selected = '') {
        select.replaceChildren(new Option(placeholder, ''));
        rows.forEach(row => select.add(new Option(label(row), value(row))));
        select.value = Array.from(select.options).some(option => option.value === String(selected)) ? String(selected) : '';
    }

    function updateGamo() {
        const rows = metadata.objectives.filter(row => String(row.version) === version.value && (!model.value || String(row.focus_area_id) === model.value));
        options(gamo, rows, 'Semua GAMO', row => row.objective_id, row => `${row.objective_code || row.objective_id.split('.')[0]} — ${row.objective}`);
    }

    function updateModels(selected = '') {
        options(model, metadata.models.filter(row => String(row.version) === version.value), 'Semua Model / Focus Area', row => row.id, row => row.name, selected);
        updateGamo();
    }

    function message(text) {
        const row = body.insertRow();
        const cell = row.insertCell();
        cell.colSpan = 6;
        cell.className = 'text-center text-muted py-4';
        cell.textContent = text;
    }

    function render(rows) {
        body.replaceChildren();
        if (!rows.length) return message('Tidak ada Master Evidence yang sesuai dengan filter.');
        rows.forEach(item => {
            const row = body.insertRow();
            const reference = item.source === 'Referensi Dokumen' ? `REF-${String(item.source_id).padStart(4, '0')}` : item.reference;
            [`COBIT ${item.version}`, item.focus_area, item.objective_code || item.objective_id.split('.')[0], reference || '-', item.name, item.description || '-'].forEach(value => {
                const cell = row.insertCell();
                cell.textContent = value || '-';
                cell.style.whiteSpace = 'pre-line';
            });
            const source = document.createElement('div');
            source.className = 'small text-muted mt-1';
            source.textContent = item.source;
            row.cells[4].append(source);
        });
    }

    async function search(targetPage = 1) {
        loaded = true;
        const request = ++requestNumber;
        pendingRequest?.abort();
        pendingRequest = new AbortController();
        previous.disabled = next.disabled = true;
        form.querySelector('button[type="submit"]').disabled = true;
        status.textContent = 'Memuat Master Evidence…';
        body.replaceChildren();
        message('Memuat data…');
        const params = new URLSearchParams(new FormData(form));
        for (const [key, value] of Array.from(params.entries())) if (!value) params.delete(key);
        params.set('page', targetPage);
        params.set('per_page', pageSize.value);
        try {
            const response = await fetch(@json(route('cobit_component.master-evidence.query')) + '?' + params, {
                headers: {'Accept': 'application/json'},
                signal: pendingRequest.signal,
            });
            const result = await response.json();
            if (request !== requestNumber) return;
            if (!response.ok) throw new Error(Object.values(result.errors || {}).flat()[0] || 'Master Evidence gagal dimuat. Silakan coba lagi.');
            page = result.current_page;
            render(result.data);
            status.textContent = `Menampilkan ${result.from || 0}–${result.to || 0} dari ${result.total} data`;
            pageLabel.textContent = `Halaman ${page} dari ${result.last_page}`;
            previous.disabled = page <= 1;
            next.disabled = page >= result.last_page;
        } catch (error) {
            if (request !== requestNumber || error.name === 'AbortError') return;
            body.replaceChildren();
            message(error.message);
            status.textContent = 'Data gagal dimuat. Ulangi pencarian untuk mencoba lagi.';
            pageLabel.textContent = '—';
        } finally {
            if (request === requestNumber) form.querySelector('button[type="submit"]').disabled = false;
        }
    }

    updateModels(@json((string) $focusAreaId));
    form.addEventListener('submit', event => { event.preventDefault(); search(); });
    version.addEventListener('change', () => { updateModels(); search(); });
    model.addEventListener('change', () => { updateGamo(); search(); });
    gamo.addEventListener('change', () => search());
    pageSize.addEventListener('change', () => search());
    previous.addEventListener('click', () => search(page - 1));
    next.addEventListener('click', () => search(page + 1));
    document.getElementById('masterEvidenceListMode').addEventListener('click', () => { if (!loaded) search(); });
    document.getElementById('master-evidence-tab').addEventListener('shown.bs.tab', () => {
        if (!document.getElementById('masterEvidenceListView').hidden && !loaded) search();
    });
});
</script>
