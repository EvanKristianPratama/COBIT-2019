import '../../css/focus-area/index.css';
import { initializeMapControls } from './map-view.js';

const page = document.querySelector('[data-focus-area-page]');

if (page) {
    const baseUrl = page.dataset.baseUrl;
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    let currentStep = 1;
    let currentActiveVersion = page.dataset.selectedVersion || '2019';

    function showNotif(message, type = 'success') {
        const wrap = document.getElementById('faNotifWrap');
        const div = document.createElement('div');
        div.className = `alert alert-${type} alert-dismissible fade show shadow-sm`;
        div.style.fontSize = '0.88rem';
        div.textContent = message;
        const closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.className = 'btn-close btn-sm';
        closeButton.dataset.bsDismiss = 'alert';
        closeButton.setAttribute('aria-label', 'Close');
        div.appendChild(closeButton);
        wrap.appendChild(div);
        setTimeout(() => div.remove(), 4000);
    }

    let currentView = page.dataset.selectedView === 'cards' ? 'cards' : 'map';
    const availableVersions = ['2019', '5', '4.1'];

    function selectFramework(version) {
        currentActiveVersion = availableVersions.includes(version) ? version : '2019';
        currentView = 'cards';
        goToWizardStep(2);
    }

    function goToWizardStep(step, updateHistory = true) {
        currentStep = step === 2 ? 2 : 1;
        const map = page.querySelector('#modelMapContainer');
        const cards = page.querySelector('#wizardStep1Container');
        const library = page.querySelector('#wizardStep2Container');
        map.hidden = currentStep !== 1 || currentView !== 'map';
        cards.hidden = currentStep !== 1 || currentView !== 'cards';
        library.hidden = currentStep !== 2;

        page.querySelectorAll('.version-view').forEach(view => {
            const activeId = {'2019': 'viewCobit2019', '5': 'viewCobit5', '4.1': 'viewCobit4'}[currentActiveVersion];
            view.style.display = '';
            view.hidden = view.id !== activeId;
        });
        page.querySelectorAll('[data-model-view]').forEach(button => {
            const active = button.dataset.modelView === (currentStep === 2 ? 'cards' : currentView);
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-current', active ? 'page' : 'false');
        });

        const badge = page.querySelector('#selectedFrameworkBadge');
        const addButton = page.querySelector('#step2AddFaBtn');
        if (badge) {
            badge.textContent = `COBIT ${currentActiveVersion}`;
            badge.className = `badge px-3 py-2 rounded-pill fw-bold ${currentActiveVersion === '5' ? 'bg-success' : currentActiveVersion === '4.1' ? 'bg-warning text-dark' : 'bg-primary'}`;
        }
        if (addButton) addButton.hidden = currentActiveVersion !== '2019';

        if (updateHistory) {
            const url = new URL(window.location.href);
            url.searchParams.set('view', currentStep === 2 ? 'cards' : currentView);
            url.searchParams.set('step', String(currentStep));
            if (currentStep === 2) url.searchParams.set('version', currentActiveVersion);
            else url.searchParams.delete('version');
            window.history.pushState({}, '', url);
        }
    }

    function openCreateModal(defaultVersion = null, defaultCode = '', defaultName = '', defaultDesc = '') {
        document.getElementById('faEditId').value = '';
        document.getElementById('faVersion').value = defaultVersion || currentActiveVersion || '2019';
        document.getElementById('faCode').value = defaultCode;
        document.getElementById('faName').value = defaultName;
        document.getElementById('faDescription').value = defaultDesc;
        document.getElementById('faModalTitle').textContent = (defaultVersion === '2019' || currentActiveVersion === '2019') ? 'Tambah Focus Area COBIT 2019' : 'Tambah Model';
        new bootstrap.Modal(document.getElementById('faModal')).show();
    }

    function openEditModal(id, code, name, description, version = '2019') {
        document.getElementById('faEditId').value = id;
        document.getElementById('faVersion').value = version || '2019';
        document.getElementById('faCode').value = code;
        document.getElementById('faName').value = name;
        document.getElementById('faDescription').value = description;
        document.getElementById('faModalTitle').textContent = 'Edit Model / Focus Area';
        new bootstrap.Modal(document.getElementById('faModal')).show();
    }

    async function saveFocusArea() {
        const editId = document.getElementById('faEditId').value;
        const version = document.getElementById('faVersion').value;
        const code = document.getElementById('faCode').value.trim().toUpperCase();
        const name = document.getElementById('faName').value.trim();
        const description = document.getElementById('faDescription').value.trim();

        if (!code || !name) {
            showNotif('Code dan Name wajib diisi.', 'warning');
            return;
        }

        const btn = document.getElementById('faSaveBtn');
        btn.disabled = true;

        try {
            const url = editId
                ? `${baseUrl}/${editId}`
                : `${baseUrl}`;
            const method = editId ? 'PUT' : 'POST';

            const response = await fetch(url, {
                method,
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: JSON.stringify({ version, code, name, description })
            });

            if (!response.ok) {
                const err = await response.json();
                let errMsg = err.message || 'Gagal menyimpan.';
                if (err.errors) {
                    errMsg = Object.values(err.errors).flat().join('\n');
                }
                throw new Error(errMsg);
            }

            bootstrap.Modal.getInstance(document.getElementById('faModal'))?.hide();
            showNotif(editId ? 'Model berhasil diperbarui.' : 'Model berhasil ditambahkan.');
            setTimeout(() => {
                const url = new URL(window.location);
                url.searchParams.set('step', '2');
                url.searchParams.set('version', version);
                window.location.href = url.toString();
            }, 800);
        } catch (e) {
            showNotif(e.message, 'danger');
        } finally {
            btn.disabled = false;
        }
    }

    async function deleteFocusArea(id, name) {
        if (!confirm(`Hapus model/focus area "${name}"? Semua mapping ke objectives akan dihapus juga.`)) return;

        try {
            const response = await fetch(`${baseUrl}/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                }
            });

            if (!response.ok) throw new Error('Gagal menghapus.');

            showNotif('Model / Focus area berhasil dihapus.');
            setTimeout(() => location.reload(), 800);
        } catch (e) {
            showNotif(e.message, 'danger');
        }
    }

    async function generateCobit5(id) {
        if (!confirm('Apakah Anda yakin ingin melakukan bulk clone 37 proses COBIT 5 ke dalam model ini?\nProses ini mungkin memerlukan beberapa detik.')) return;

        try {
            const res = await fetch(`${baseUrl}/${id}/generate-cobit5`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                }
            });
            if (!res.ok) throw new Error('Gagal men-generate COBIT 5.');
            const data = await res.json();
            showNotif(data.message);
            setTimeout(() => location.reload(), 1000);
        } catch (e) {
            showNotif(e.message, 'danger');
        }
    }

    async function generateCobit4(id) {
        if (!confirm('Apakah Anda yakin ingin melakukan bulk clone template 34 proses COBIT 4.1 ke dalam model ini?\nProses ini mungkin memerlukan beberapa detik.')) return;

        try {
            const res = await fetch(`${baseUrl}/${id}/generate-cobit4`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                }
            });
            if (!res.ok) throw new Error('Gagal men-generate COBIT 4.1.');
            const data = await res.json();
            showNotif(data.message);
            setTimeout(() => location.reload(), 1000);
        } catch (e) {
            showNotif(e.message, 'danger');
        }
    }

    Object.assign(window, {selectFramework, goToWizardStep, openCreateModal, openEditModal, saveFocusArea, deleteFocusArea, generateCobit5, generateCobit4});
    page.querySelectorAll('[data-model-view]').forEach(button => {
        button.addEventListener('click', event => {
            if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button !== 0) return;
            event.preventDefault();
            currentView = button.dataset.modelView;
            goToWizardStep(1);
        });
    });
    page.querySelectorAll('[data-framework]').forEach(link => {
        link.addEventListener('click', event => {
            if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button !== 0) return;
            event.preventDefault();
            selectFramework(link.dataset.framework);
        });
    });
    window.addEventListener('popstate', () => {
        const parameters = new URL(window.location.href).searchParams;
        currentView = parameters.get('view') === 'cards' ? 'cards' : 'map';
        currentActiveVersion = availableVersions.includes(parameters.get('version')) ? parameters.get('version') : '2019';
        goToWizardStep(parameters.get('step') === '2' ? 2 : 1, false);
    });
    if (!availableVersions.includes(currentActiveVersion)) currentActiveVersion = '2019';
    goToWizardStep(page.dataset.initialStep === '2' ? 2 : 1, false);
    initializeMapControls(page);
}
