<div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <form method="POST" action="{{ route('admin.users.store') }}" data-user-form>
            @csrf

            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header modal-header-clean">
                    <div>
                        <h5 class="modal-title mb-1" id="createUserModalLabel">Tambah User Baru</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    @include('admin.users.partials.user-form-fields', [
                        'prefix' => 'create_user',
                        'user' => null,
                        'errorBag' => 'createUser',
                        'includePassword' => true,
                        'organizationCatalog' => $organizationCatalog,
                        'organizationIds' => collect(old('organization_ids', []))->map(fn ($value) => (int) $value)->all(),
                    ])

                    <div class="border rounded-3 p-3 mt-4 bg-light-subtle">
                        <input type="hidden" name="isActivated" value="0">
                        <div class="form-check form-switch mb-0">
                            <input
                                id="create_user_is_activated"
                                type="checkbox"
                                name="isActivated"
                                value="1"
                                class="form-check-input"
                                {{ old('isActivated', '1') == '1' ? 'checked' : '' }}
                            >
                            <label for="create_user_is_activated" class="form-check-label">Aktifkan akun saat dibuat</label>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus me-1"></i>Simpan User
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
