<div class="modal fade" id="deleteDesignFactorModal" tabindex="-1" aria-labelledby="deleteDesignFactorTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" id="deleteDesignFactorForm">
            @csrf
            @method('DELETE')
            <input type="hidden" name="confirmation_code" id="deleteDesignFactorCodeInput">
            <input type="hidden" name="step" value="{{ $step }}">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="deleteDesignFactorTitle">Hapus Design Factor?</h2>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1 fw-semibold" id="deleteDesignFactorCode"></p>
                <p class="text-muted" id="deleteDesignFactorOrganization"></p>
                <p class="mb-0">Seluruh input DF1–DF10 dan hasil Step 2–4 untuk kode ini, termasuk milik pengguna lain, akan dihapus permanen.</p>
                <label for="deleteDesignFactorConfirmation" class="form-label mt-3">Ketik <strong>saya yakin</strong> untuk melanjutkan.</label>
                <input type="text" class="form-control" name="confirmation_text" id="deleteDesignFactorConfirmation" autocomplete="off" spellcheck="false" required pattern="saya yakin" aria-describedby="deleteDesignFactorConfirmationHelp">
                <small class="text-muted" id="deleteDesignFactorConfirmationHelp">Gunakan huruf kecil sesuai teks di atas.</small>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-danger" type="submit" id="confirmDeleteDesignFactor" disabled>Hapus Design Factor</button>
            </div>
        </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('deleteDesignFactorModal');
    const form = document.getElementById('deleteDesignFactorForm');
    const confirmButton = document.getElementById('confirmDeleteDesignFactor');
    const confirmationInput = document.getElementById('deleteDesignFactorConfirmation');
    let submitting = false;
    const updateConfirmation = () => {
        confirmButton.disabled = submitting || !form.hasAttribute('action') || confirmationInput.value !== 'saya yakin';
    };
    confirmationInput.addEventListener('input', updateConfirmation);
    modal.addEventListener('show.bs.modal', event => {
        const trigger = event.relatedTarget;
        const code = trigger?.dataset.code;
        const url = trigger?.dataset.deleteUrl;
        form.removeAttribute('action');
        form.reset();
        submitting = false;
        confirmButton.textContent = 'Hapus Design Factor';
        confirmButton.disabled = true;
        if (!code || !url) return;
        form.action = url;
        document.getElementById('deleteDesignFactorCodeInput').value = code;
        document.getElementById('deleteDesignFactorCode').textContent = code;
        document.getElementById('deleteDesignFactorOrganization').textContent = trigger.dataset.organization || '';
    });
    modal.addEventListener('shown.bs.modal', () => confirmationInput.focus());
    modal.addEventListener('hidden.bs.modal', () => {
        form.removeAttribute('action');
        form.reset();
        confirmButton.disabled = true;
    });
    form.addEventListener('submit', event => {
        if (!form.hasAttribute('action') || confirmButton.disabled || confirmationInput.value !== 'saya yakin') { event.preventDefault(); return; }
        submitting = true;
        confirmButton.disabled = true;
        confirmButton.textContent = 'Menghapus...';
    });
});
</script>
