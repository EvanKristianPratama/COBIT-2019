@extends('layouts.admin')

@section('admin_title', 'Manage User')

@section('admin_content')
@php
    $managedUsers = $activatedUsers->concat($deactivatedUsers)->values();
    $allUsers = $pendingUsers->concat($managedUsers)->values();
@endphp

<style>
    :root {
        --panel-border: #d9e1ec;
        --panel-bg: #ffffff;
        --panel-muted: #6b7280;
        --panel-text: #0f172a;
        --panel-primary: var(--cobit-primary);
        --panel-primary-soft: rgba(15, 43, 92, 0.08);
        --panel-success: var(--cobit-secondary);
        --panel-success-soft: rgba(26, 61, 107, 0.1);
        --panel-warning: var(--cobit-accent);
        --panel-warning-soft: rgba(15, 106, 217, 0.1);
        --panel-danger: #8a3c2d;
        --panel-danger-soft: #f8ece8;
        --panel-surface: #f5f7fb;
    }

    .users-shell {
        color: var(--panel-text);
    }

    .hero-panel,
    .stats-card,
    .filter-panel,
    .directory-panel,
    .modal-content {
        border: 1px solid var(--panel-border);
        border-radius: 18px;
        background: var(--panel-bg);
        box-shadow: 0 14px 40px rgba(15, 23, 42, 0.06);
    }

    .hero-panel {
        padding: 1.5rem 1.6rem;
        background:
            linear-gradient(135deg, rgba(15, 43, 92, 0.08), rgba(255, 255, 255, 0.92)),
            var(--panel-bg);
    }

    .hero-kicker {
        letter-spacing: 0.08em;
        text-transform: uppercase;
        font-size: 0.76rem;
        font-weight: 700;
        color: var(--panel-primary);
    }

    .hero-title {
        font-size: clamp(1.55rem, 2vw, 2.1rem);
        font-weight: 800;
        margin-bottom: 0.35rem;
    }

    .stats-card {
        padding: 1.1rem 1.2rem;
        height: 100%;
    }

    .stats-label {
        font-size: 0.76rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--panel-muted);
    }

    .stats-value {
        font-size: 1.9rem;
        font-weight: 700;
        line-height: 1;
        margin-top: 0.55rem;
    }

    .stats-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    .icon-primary { background: var(--panel-primary-soft); color: var(--panel-primary); }
    .icon-success { background: var(--panel-success-soft); color: var(--panel-success); }
    .icon-warning { background: var(--panel-warning-soft); color: var(--panel-warning); }
    .icon-danger { background: var(--panel-danger-soft); color: var(--panel-danger); }

    .filter-panel {
        padding: 1rem 1.2rem;
    }

    .directory-panel .card-header,
    .modal-header-clean {
        background: transparent;
        border-bottom: 1px solid var(--panel-border);
        padding: 1rem 1.25rem;
    }

    .directory-panel .card-body {
        padding: 0;
    }

    .table-enterprise {
        margin-bottom: 0;
    }

    .table-enterprise thead th {
        position: sticky;
        top: 0;
        z-index: 1;
        background: #f8fafc;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-size: 0.73rem;
        font-weight: 700;
        padding: 0.95rem 1rem;
        border-bottom: 1px solid var(--panel-border);
    }

    .table-enterprise tbody td {
        padding: 1rem;
        vertical-align: top;
        border-color: #eef2f7;
    }

    .table-enterprise tbody tr:hover {
        background: rgba(15, 106, 217, 0.04);
    }

    .user-name {
        font-weight: 700;
        margin-bottom: 0.15rem;
    }

    .user-meta {
        color: var(--panel-muted);
        font-size: 0.88rem;
        margin-bottom: 0.2rem;
    }

    .user-id {
        color: #94a3b8;
        font-size: 0.76rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .chip-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
    }

    .chip {
        display: inline-flex;
        align-items: center;
        padding: 0.38rem 0.72rem;
        border-radius: 999px;
        border: 1px solid transparent;
        font-size: 0.79rem;
        font-weight: 600;
        line-height: 1;
    }

    .chip-primary {
        background: var(--panel-primary-soft);
        color: var(--panel-primary);
        border-color: rgba(15, 43, 92, 0.16);
    }

    .chip-neutral {
        background: #f8fafc;
        color: #475569;
        border-color: #dbe5f0;
    }

    .chip-success {
        background: var(--panel-success-soft);
        color: var(--panel-success);
        border-color: rgba(26, 61, 107, 0.14);
    }

    .chip-danger {
        background: var(--panel-danger-soft);
        color: var(--panel-danger);
        border-color: rgba(185, 28, 28, 0.12);
    }

    .chip-warning {
        background: var(--panel-warning-soft);
        color: var(--panel-warning);
        border-color: rgba(15, 106, 217, 0.14);
    }

    .status-badge,
    .role-badge,
    .access-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 999px;
        padding: 0.42rem 0.82rem;
        font-size: 0.79rem;
        font-weight: 700;
    }

    .status-active {
        background: var(--panel-success-soft);
        color: var(--panel-success);
    }

    .status-pending {
        background: #fff7ed;
        color: #c2410c;
    }

    .status-inactive {
        background: var(--panel-danger-soft);
        color: var(--panel-danger);
    }

    .role-admin {
        background: var(--panel-primary-soft);
        color: var(--panel-primary);
    }

    .role-user {
        background: rgba(26, 61, 107, 0.08);
        color: var(--panel-success);
    }

    .role-pending {
        background: #fff7ed;
        color: #c2410c;
    }

    .access-viewer {
        background: #f8fafc;
        color: #475569;
    }

    .access-pending {
        background: #fff7ed;
        color: #c2410c;
    }

    .access-df_editor {
        background: var(--panel-warning-soft);
        color: var(--panel-warning);
    }

    .access-assessor {
        background: var(--panel-success-soft);
        color: var(--panel-success);
    }

    .access-admin {
        background: var(--panel-primary-soft);
        color: var(--panel-primary);
    }

    .actions-stack {
        display: flex;
        gap: 0.55rem;
        flex-wrap: wrap;
    }

    .empty-panel {
        padding: 3rem 1.5rem;
        text-align: center;
        color: var(--panel-muted);
    }

    .empty-panel i {
        font-size: 2.4rem;
        margin-bottom: 1rem;
        color: #c0cad8;
    }

    .table-scroll {
        max-height: 70vh;
        overflow: auto;
    }

    @media (max-width: 991.98px) {
        .actions-stack {
            flex-direction: column;
        }
    }
</style>

<div class="container-fluid py-4 users-shell">
    <div class="mb-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-circle-exclamation me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->createUser->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-triangle-exclamation me-2"></i>Form tambah user belum valid. Periksa field yang ditandai.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any() && old('_editing_user_id'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-triangle-exclamation me-2"></i>Form edit user belum valid. Periksa field yang ditandai.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <div class="hero-panel mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center">
            <div>
                <div class="hero-kicker">Admin Workspace</div>
                <h1 class="hero-title">User Management</h1>
            </div>

            @if(auth()->user()->isAdmin())
                <div class="d-flex gap-2">
                    <button
                        type="button"
                        class="btn btn-primary px-4"
                        data-bs-toggle="modal"
                        data-bs-target="#createUserModal"
                    >
                        <i class="fas fa-user-plus me-2"></i>Tambah User
                    </button>
                </div>
            @endif
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stats-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stats-label">Total User</div>
                        <div class="stats-value">{{ $stats['total'] }}</div>
                    </div>
                    <span class="stats-icon icon-primary"><i class="fas fa-users"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stats-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stats-label">Pending Approval</div>
                        <div class="stats-value">{{ $stats['pending'] }}</div>
                    </div>
                    <span class="stats-icon icon-warning"><i class="fas fa-hourglass-half"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stats-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stats-label">Akun Aktif</div>
                        <div class="stats-value">{{ $stats['active'] }}</div>
                    </div>
                    <span class="stats-icon icon-success"><i class="fas fa-user-check"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stats-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stats-label">Multi Organisasi</div>
                        <div class="stats-value">{{ $stats['multi_org'] }}</div>
                    </div>
                    <span class="stats-icon icon-warning"><i class="fas fa-diagram-project"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stats-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stats-label">Admin</div>
                        <div class="stats-value">{{ $stats['admins'] }}</div>
                    </div>
                    <span class="stats-icon icon-danger"><i class="fas fa-user-shield"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="filter-panel mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-lg-5">
                <label for="userSearchInput" class="form-label">Cari User</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input id="userSearchInput" type="text" class="form-control" placeholder="Cari nama, email, jabatan, atau organisasi">
                </div>
            </div>
            <div class="col-md-4 col-lg-2">
                <label for="statusFilter" class="form-label">Status</label>
                <select id="statusFilter" class="form-select">
                    <option value="all">Semua</option>
                    <option value="pending">Pending</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
            </div>
            <div class="col-md-4 col-lg-2">
                <label for="roleFilter" class="form-label">Role</label>
                <select id="roleFilter" class="form-select">
                    <option value="all">Semua</option>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>
            </div>
            <div class="col-md-4 col-lg-3">
                <label for="accessFilter" class="form-label">Paket Akses</label>
                <select id="accessFilter" class="form-select">
                    <option value="all">Semua</option>
                    <option value="pending">Pending Approval</option>
                    <option value="admin">Admin Full Access</option>
                    <option value="viewer">Auditee</option>
                    <option value="df_editor">Client DF Editor</option>
                    <option value="assessor">Assessor</option>
                </select>
            </div>
        </div>
    </div>

    @if($pendingUsers->isNotEmpty())
        <div class="card directory-panel border-0 mb-4">
            <div class="card-header d-flex flex-column flex-lg-row justify-content-between gap-2 align-items-lg-center">
                <div>
                    <h5 class="mb-1">Pending Approval</h5>
                </div>
                <span class="chip chip-warning"><span id="visiblePendingCount">{{ $pendingUsers->count() }}</span>&nbsp;user terlihat</span>
            </div>
            <div class="card-body">
                <div class="table-scroll">
                    <div class="table-responsive">
                        <table class="table table-enterprise align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Organisasi Utama</th>
                                    <th>Coverage Organisasi</th>
                                    <th>Role</th>
                                    <th>Paket Akses</th>
                                    <th>Status</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingUsers as $u)
                                    @php
                                        $organizations = $u->organizationNames();
                                        $accessKey = 'pending';
                                        $statusKey = $u->accountStatusKey();
                                    @endphp
                                    <tr
                                        data-user-row
                                        data-section="pending"
                                        data-status="{{ $statusKey }}"
                                        data-role="{{ $u->role }}"
                                        data-access="{{ $accessKey }}"
                                        data-search="{{ strtolower(implode(' ', array_filter([
                                            $u->name,
                                            $u->email,
                                            $u->jabatan,
                                            $u->displayOrganizationSummary(),
                                            $organizations->implode(' ')
                                        ]))) }}"
                                    >
                                        <td>
                                            <div class="user-name">{{ $u->name }}</div>
                                            <div class="user-meta">{{ $u->email }}</div>
                                            <div class="user-meta">{{ $u->jabatan }}</div>
                                            <div class="user-id">User #{{ $u->id }}</div>
                                        </td>
                                        <td>
                                            <div class="chip-list">
                                                <span class="chip chip-neutral">{{ $u->organisasi ?? '-' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="chip-list mb-2">
                                                @forelse($organizations->take(3) as $organizationName)
                                                    <span class="chip {{ $loop->first ? 'chip-primary' : 'chip-neutral' }}">{{ $organizationName }}</span>
                                                @empty
                                                    <span class="chip chip-neutral">Belum diassign</span>
                                                @endforelse
                                                @if($organizations->count() > 3)
                                                    <span class="chip chip-neutral">+{{ $organizations->count() - 3 }} lagi</span>
                                                @endif
                                            </div>
                                            <div class="small text-muted">{{ $u->organizationCount() }} organisasi terhubung</div>
                                        </td>
                                        <td>
                                            <span class="role-badge role-pending">
                                                <i class="fas fa-hourglass-half"></i>
                                                {{ $u->displayRoleLabel() }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="access-badge access-pending">
                                                <i class="fas fa-hourglass-half"></i>
                                                {{ $u->displayAccessProfileLabel() }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-pending">
                                                <i class="fas {{ $u->accountStatusIcon() }}"></i>
                                                {{ $u->accountStatusLabel() }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="actions-stack justify-content-end">
                                                <button
                                                    type="button"
                                                    class="btn btn-outline-primary btn-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModal{{ $u->id }}"
                                                >
                                                    <i class="fas fa-user-check me-1"></i>Proses
                                                </button>

                                                @if($u->isActivated)
                                                    <form action="{{ route('admin.users.deactivate', $u->id) }}" method="POST" onsubmit="return confirm('Nonaktifkan akun ini?');">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                                            <i class="fas fa-user-slash me-1"></i>Nonaktifkan
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="card directory-panel border-0">
        <div class="card-header d-flex flex-column flex-lg-row justify-content-between gap-2 align-items-lg-center">
            <div>
                <h5 class="mb-1">User Directory</h5>
            </div>
            <span class="chip chip-neutral"><span id="visibleUserCount">{{ $managedUsers->count() }}</span>&nbsp;user terlihat</span>
        </div>
        <div class="card-body">
            @if($managedUsers->isEmpty())
                <div class="empty-panel">
                    <i class="fas fa-users"></i>
                    <div class="fw-semibold mb-1">Belum ada user</div>
                    <div>Mulai dengan menambahkan akun baru dari panel admin.</div>
                </div>
            @else
                <div class="table-scroll">
                    <div class="table-responsive">
                        <table class="table table-enterprise align-middle">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Organisasi Utama</th>
                                    <th>Coverage Organisasi</th>
                                    <th>Role</th>
                                    <th>Paket Akses</th>
                                    <th>Status</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($managedUsers as $u)
                                    @php
                                        $organizations = $u->organizationNames();
                                        $accessKey = $u->isAdmin() ? 'admin' : ($u->access_profile ?? 'viewer');
                                        $accessLabel = $u->isAdmin() ? 'Full Access' : $u->displayAccessProfileLabel();
                                        $statusKey = $u->accountStatusKey();
                                    @endphp
                                    <tr
                                        data-user-row
                                        data-section="directory"
                                        data-status="{{ $statusKey }}"
                                        data-role="{{ $u->role }}"
                                        data-access="{{ $accessKey }}"
                                        data-search="{{ strtolower(implode(' ', array_filter([
                                            $u->name,
                                            $u->email,
                                            $u->jabatan,
                                            $u->displayOrganizationSummary(),
                                            $organizations->implode(' ')
                                        ]))) }}"
                                    >
                                        <td>
                                            <div class="user-name">{{ $u->name }}</div>
                                            <div class="user-meta">{{ $u->email }}</div>
                                            <div class="user-meta">{{ $u->jabatan }}</div>
                                            <div class="user-id">User #{{ $u->id }}</div>
                                        </td>
                                        <td>
                                            <div class="chip-list">
                                                <span class="chip chip-primary">{{ $u->organisasi ?? '-' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="chip-list mb-2">
                                                @foreach($organizations->take(3) as $organizationName)
                                                    <span class="chip {{ $loop->first ? 'chip-primary' : 'chip-neutral' }}">{{ $organizationName }}</span>
                                                @endforeach
                                                @if($organizations->count() > 3)
                                                    <span class="chip chip-neutral">+{{ $organizations->count() - 3 }} lagi</span>
                                                @endif
                                            </div>
                                            <div class="small text-muted">{{ $u->organizationCount() }} organisasi terhubung</div>
                                        </td>
                                        <td>
                                            <span class="role-badge {{ $u->isAdmin() ? 'role-admin' : 'role-user' }}">
                                                <i class="fas {{ $u->isAdmin() ? 'fa-user-shield' : 'fa-user' }}"></i>
                                                {{ $u->displayRoleLabel() }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="access-badge access-{{ $accessKey }}">
                                                <i class="fas {{ $u->isAdmin() ? 'fa-key' : 'fa-sliders' }}"></i>
                                                {{ $accessLabel }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-{{ $statusKey }}">
                                                <i class="fas {{ $u->accountStatusIcon() }}"></i>
                                                {{ $u->accountStatusLabel() }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="actions-stack justify-content-end">
                                                <button
                                                    type="button"
                                                    class="btn btn-outline-primary btn-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModal{{ $u->id }}"
                                                >
                                                    <i class="fas fa-pen-to-square me-1"></i>Edit
                                                </button>

                                                @if($u->isActivated)
                                                    <form action="{{ route('admin.users.deactivate', $u->id) }}" method="POST" onsubmit="return confirm('Nonaktifkan akun ini?');">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                                            <i class="fas fa-user-slash me-1"></i>Nonaktifkan
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('admin.users.activate', $u->id) }}" method="POST" onsubmit="return confirm('Aktifkan kembali akun ini?');">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" class="btn btn-outline-success btn-sm">
                                                            <i class="fas fa-user-check me-1"></i>Aktifkan
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@foreach($allUsers as $u)
    <div class="modal fade" id="editModal{{ $u->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $u->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <form method="POST" action="{{ route('admin.users.update', $u->id) }}" data-user-form>
                @csrf
                @method('PUT')
                <input type="hidden" name="_editing_user_id" value="{{ $u->id }}">

                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header modal-header-clean">
                        <div>
                            <h5 class="modal-title mb-1" id="editModalLabel{{ $u->id }}">Edit User</h5>
                            <p class="text-muted small mb-0">Perbarui akun, paket akses, dan coverage organisasi berbasis master.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        @include('admin.users.partials.user-form-fields', [
                            'prefix' => 'edit_user_'.$u->id,
                            'user' => $u,
                            'errorBag' => null,
                            'includePassword' => false,
                            'organizationCatalog' => $organizationCatalog,
                            'organizationIds' => $u->organizations->pluck('organization_id')->map(fn ($value) => (int) $value)->all(),
                        ])
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-floppy-disk me-1"></i>Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endforeach

@if(auth()->user()->isAdmin())
    @include('admin.users.partials.create-user-modal')
@endif

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const rows = Array.from(document.querySelectorAll('[data-user-row]'));
        const searchInput = document.getElementById('userSearchInput');
        const statusFilter = document.getElementById('statusFilter');
        const roleFilter = document.getElementById('roleFilter');
        const accessFilter = document.getElementById('accessFilter');
        const visibleUserCount = document.getElementById('visibleUserCount');
        const visiblePendingCount = document.getElementById('visiblePendingCount');

        const applyTableFilters = () => {
            const searchValue = (searchInput?.value || '').trim().toLowerCase();
            const statusValue = statusFilter?.value || 'all';
            const roleValue = roleFilter?.value || 'all';
            const accessValue = accessFilter?.value || 'all';

            let visibleManagedCount = 0;
            let visiblePending = 0;

            rows.forEach((row) => {
                const matchesSearch = searchValue === '' || row.dataset.search.includes(searchValue);
                const matchesStatus = statusValue === 'all' || row.dataset.status === statusValue;
                const matchesRole = roleValue === 'all' || row.dataset.role === roleValue;
                const matchesAccess = accessValue === 'all' || row.dataset.access === accessValue;
                const visible = matchesSearch && matchesStatus && matchesRole && matchesAccess;

                row.style.display = visible ? '' : 'none';

                if (visible) {
                    if (row.dataset.section === 'pending') {
                        visiblePending += 1;
                    } else {
                        visibleManagedCount += 1;
                    }
                }
            });

            if (visibleUserCount) {
                visibleUserCount.textContent = String(visibleManagedCount);
            }

            if (visiblePendingCount) {
                visiblePendingCount.textContent = String(visiblePending);
            }
        };

        [searchInput, statusFilter, roleFilter, accessFilter].forEach((element) => {
            element?.addEventListener('input', applyTableFilters);
            element?.addEventListener('change', applyTableFilters);
        });

        const syncAccessState = (form) => {
            const roleSelect = form.querySelector('[data-role-select]');
            const accessSelect = form.querySelector('[data-access-select]');
            const accessWrapper = form.querySelector('[data-access-wrapper]');

            if (!roleSelect || !accessSelect || !accessWrapper) {
                return;
            }

            const isAdmin = roleSelect.value === 'admin';
            accessSelect.disabled = isAdmin;
            accessWrapper.classList.toggle('opacity-50', isAdmin);

            if (!isAdmin && accessSelect.value === '') {
                accessSelect.value = 'viewer';
            }

            if (isAdmin) {
                accessSelect.value = '';
            }
        };

        document.querySelectorAll('[data-user-form]').forEach((form) => {
            const roleSelect = form.querySelector('[data-role-select]');

            roleSelect?.addEventListener('change', () => syncAccessState(form));

            syncAccessState(form);
        });

        @if($errors->createUser->any())
            if (window.bootstrap) {
                const createUserModal = new window.bootstrap.Modal(document.getElementById('createUserModal'));
                createUserModal.show();
            }
        @endif

        @if($errors->any() && old('_editing_user_id'))
            if (window.bootstrap) {
                const editModal = document.getElementById('editModal{{ old('_editing_user_id') }}');
                if (editModal) {
                    const modalInstance = new window.bootstrap.Modal(editModal);
                    modalInstance.show();
                }
            }
        @endif

        applyTableFilters();
    });
</script>
@endsection
