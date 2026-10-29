<div class="modal fade fa-modal" id="faModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="faModalTitle">Tambah Model / Focus Area</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="faEditId" value="">

                <!-- Version selector -->
                <div class="mb-3">
                    <label for="faVersion" class="form-label fw-bold">Versi Model Framework</label>
                    <select id="faVersion" class="form-select">
                        <option value="2019">COBIT 2019 (Focus Area)</option>
                        <option value="5">COBIT 5</option>
                        <option value="4.1">COBIT 4.1</option>
                    </select>
                    <div class="form-text small" id="faVersionHelp">
                        Pilih COBIT 2019 jika ini adalah Focus Area yang akan dikelompokkan dalam COBIT 2019.
                    </div>
                </div>

                <div class="mb-3">
                    <label for="faCode" class="form-label fw-semibold">Code</label>
                    <input type="text" id="faCode" class="form-control" placeholder="Contoh: SECURITY" maxlength="10" style="text-transform: uppercase;">
                </div>
                <div class="mb-3">
                    <label for="faName" class="form-label fw-semibold">Name</label>
                    <input type="text" id="faName" class="form-control" placeholder="Contoh: Security" maxlength="100">
                </div>
                <div class="mb-3">
                    <label for="faDescription" class="form-label fw-semibold">Description</label>
                    <textarea id="faDescription" class="form-control" rows="3" placeholder="Deskripsi model / focus area..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="faSaveBtn" onclick="saveFocusArea()">
                    <i class="fas fa-save me-1"></i>Simpan
                </button>
            </div>
        </div>
    </div>
</div>
