<div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf

            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="createUserModalLabel">Tambah User Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="create_user_name" class="form-label">Nama</label>
                            <input
                                id="create_user_name"
                                type="text"
                                name="name"
                                class="form-control @error('name', 'createUser') is-invalid @enderror"
                                value="{{ old('name') }}"
                                required
                            >
                            @error('name', 'createUser')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="create_user_email" class="form-label">Email</label>
                            <input
                                id="create_user_email"
                                type="email"
                                name="email"
                                class="form-control @error('email', 'createUser') is-invalid @enderror"
                                value="{{ old('email') }}"
                                required
                            >
                            @error('email', 'createUser')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="create_user_password" class="form-label">Password</label>
                            <input
                                id="create_user_password"
                                type="password"
                                name="password"
                                class="form-control @error('password', 'createUser') is-invalid @enderror"
                                required
                            >
                            @error('password', 'createUser')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="create_user_password_confirmation" class="form-label">Konfirmasi Password</label>
                            <input
                                id="create_user_password_confirmation"
                                type="password"
                                name="password_confirmation"
                                class="form-control"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label for="create_user_role" class="form-label">Role</label>
                            <select
                                id="create_user_role"
                                name="role"
                                class="form-select @error('role', 'createUser') is-invalid @enderror"
                                required
                            >
                                <option value="user" {{ old('role', 'user') === 'user' ? 'selected' : '' }}>User</option>
                                <option value="pic" {{ old('role') === 'pic' ? 'selected' : '' }}>PIC</option>
                                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                            @error('role', 'createUser')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="create_user_jabatan" class="form-label">Jabatan</label>
                            <input
                                id="create_user_jabatan"
                                type="text"
                                name="jabatan"
                                class="form-control @error('jabatan', 'createUser') is-invalid @enderror"
                                value="{{ old('jabatan') }}"
                                required
                            >
                            @error('jabatan', 'createUser')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="create_user_organisasi" class="form-label">Organisasi</label>
                            <input
                                id="create_user_organisasi"
                                type="text"
                                name="organisasi"
                                class="form-control @error('organisasi', 'createUser') is-invalid @enderror"
                                value="{{ old('organisasi') }}"
                                required
                            >
                            @error('organisasi', 'createUser')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <input type="hidden" name="isActivated" value="0">
                            <div class="form-check">
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
