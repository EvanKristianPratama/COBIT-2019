@extends('layouts.admin')

@section('admin_title', 'Manage Organization')

@section('admin_content')
@php
    $activeOrganizationModal = old('_organization_modal');
@endphp
<style>
    :root {
        --org-panel-border: #d7dfeb;
        --org-panel-bg: #ffffff;
        --org-panel-muted: #64748b;
        --org-panel-text: #0f172a;
        --org-panel-navy: var(--cobit-primary);
        --org-panel-blue: var(--cobit-secondary);
        --org-panel-steel: var(--cobit-accent);
        --org-panel-bronze: var(--cobit-accent);
        --org-panel-navy-soft: rgba(15, 43, 92, 0.08);
        --org-panel-bronze-soft: rgba(15, 106, 217, 0.1);
        --org-panel-success: var(--cobit-secondary);
        --org-panel-success-soft: rgba(26, 61, 107, 0.1);
    }

    .organizations-shell { color: var(--org-panel-text); }
    .org-hero, .org-stats-card, .org-table-panel, .org-create-panel, .org-modal .modal-content {
        border: 1px solid var(--org-panel-border);
        border-radius: 20px;
        background: var(--org-panel-bg);
        box-shadow: 0 18px 42px rgba(15, 23, 42, 0.06);
    }

    .org-hero {
        padding: 1.6rem 1.7rem;
        background: linear-gradient(135deg, rgba(15, 43, 92, 0.08), rgba(15, 106, 217, 0.08)), var(--org-panel-bg);
    }

    .org-kicker {
        color: var(--org-panel-blue);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-size: 0.74rem;
        font-weight: 800;
        margin-bottom: 0.35rem;
    }

    .org-title {
        font-size: clamp(1.55rem, 2vw, 2.15rem);
        font-weight: 800;
        margin-bottom: 0.35rem;
    }

    .org-stats-card { padding: 1.15rem 1.2rem; height: 100%; }
    .org-stats-label {
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--org-panel-muted);
    }

    .org-stats-value {
        margin-top: 0.55rem;
        font-size: 1.9rem;
        font-weight: 800;
        line-height: 1;
    }

    .org-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    .org-icon-navy { background: var(--org-panel-navy-soft); color: var(--org-panel-navy); }
    .org-icon-bronze { background: var(--org-panel-bronze-soft); color: var(--org-panel-bronze); }
    .org-icon-success { background: var(--org-panel-success-soft); color: var(--org-panel-success); }
    .org-icon-steel { background: #eef4f7; color: var(--org-panel-steel); }
    .org-create-panel { padding: 1.3rem; }
    .org-create-title, .org-table-title { font-size: 1rem; font-weight: 800; margin-bottom: 0.2rem; }
    .org-table-panel .card-header, .org-modal .modal-header {
        border-bottom: 1px solid var(--org-panel-border);
        background: transparent;
        padding: 1rem 1.25rem;
    }

    .org-table-panel .card-body { padding: 0; }
    .org-table { margin-bottom: 0; }
    .org-table thead th {
        position: sticky;
        top: 0;
        z-index: 1;
        background: #f8fafc;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-size: 0.73rem;
        font-weight: 800;
        padding: 0.95rem 1rem;
        border-bottom: 1px solid var(--org-panel-border);
    }

    .org-table tbody td {
        padding: 1rem;
        vertical-align: top;
        border-color: #edf2f7;
    }

    .org-table tbody tr:hover { background: rgba(36, 82, 122, 0.03); }
    .org-table tbody tr:hover { background: rgba(15, 106, 217, 0.04); }
    .org-name { font-weight: 800; margin-bottom: 0.18rem; }
    .org-key {
        color: var(--org-panel-muted);
        font-size: 0.86rem;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    }

    .org-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.42rem 0.82rem;
        border-radius: 999px;
        font-size: 0.79rem;
        font-weight: 800;
    }

    .org-badge-active { background: var(--org-panel-success-soft); color: var(--org-panel-success); }
    .org-badge-inactive { background: #fbe9e8; color: #b42318; }
    .org-chip {
        display: inline-flex;
        align-items: center;
        padding: 0.35rem 0.7rem;
        border-radius: 999px;
        background: #f8fafc;
        border: 1px solid #dbe4ee;
        color: #334155;
        font-size: 0.79rem;
        font-weight: 700;
    }

    .org-actions { display: flex; justify-content: flex-end; gap: 0.55rem; flex-wrap: wrap; }
    .org-empty { padding: 3rem 1.5rem; text-align: center; color: var(--org-panel-muted); }
    .org-empty i { font-size: 2.5rem; margin-bottom: 0.9rem; color: #c0cad6; }
    .org-scroll { max-height: 72vh; overflow: auto; }
</style>

<div class="container-fluid px-0 organizations-shell">
    <div class="mb-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-circle-check me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-circle-exclamation me-2"></i>Form organisasi belum valid. Periksa data yang dimasukkan.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <div class="org-hero mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center">
            <div>
                <div class="org-kicker">Master Data</div>
                <h1 class="org-title">Organization Directory</h1>
            </div>
            <button type="button" class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#createOrganizationModal">
                <i class="fas fa-building-circle-arrow-right me-2"></i>Tambah Organisasi
            </button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="org-stats-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="org-stats-label">Total Organisasi</div>
                        <div class="org-stats-value">{{ $stats['total'] }}</div>
                    </div>
                    <span class="org-icon org-icon-navy"><i class="fas fa-building"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="org-stats-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="org-stats-label">Aktif</div>
                        <div class="org-stats-value">{{ $stats['active'] }}</div>
                    </div>
                    <span class="org-icon org-icon-success"><i class="fas fa-circle-check"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="org-stats-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="org-stats-label">Mapping User</div>
                        <div class="org-stats-value">{{ $stats['mapped_users'] }}</div>
                    </div>
                    <span class="org-icon org-icon-bronze"><i class="fas fa-diagram-project"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="org-stats-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="org-stats-label">Assessment</div>
                        <div class="org-stats-value">{{ $stats['assessments'] }}</div>
                    </div>
                    <span class="org-icon org-icon-steel"><i class="fas fa-clipboard-list"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="org-create-panel h-100">
                <div class="org-create-title">Arah Pengelolaan</div>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createOrganizationModal">
                        <i class="fas fa-plus me-2"></i>Tambah Organisasi Baru
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-users me-2"></i>Buka Manage User
                    </a>
                    <a href="{{ route('admin.assessments.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-clipboard-check me-2"></i>Buka Manage Assessment
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card org-table-panel border-0">
                <div class="card-header d-flex flex-column flex-lg-row justify-content-between gap-2 align-items-lg-center">
                    <div>
                        <div class="org-table-title">Organization Directory</div>
                    </div>
                    <span class="org-chip">{{ $organizations->count() }} organisasi</span>
                </div>
                <div class="card-body">
                    @if($organizations->isEmpty())
                        <div class="org-empty">
                            <i class="fas fa-buildings"></i>
                            <div class="fw-semibold mb-1">Belum ada organisasi</div>
                            <div>Tambahkan organisasi pertama untuk memulai mapping user dan assessment.</div>
                        </div>
                    @else
                        <div class="org-scroll">
                            <div class="table-responsive">
                                <table class="table org-table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Organisasi</th>
                                            <th>Status</th>
                                            <th>Mapping User</th>
                                            <th>Assessment</th>
                                            <th>Evaluation</th>
                                            <th class="text-end">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($organizations as $organization)
                                            <tr>
                                                <td>
                                                    <div class="org-name">{{ $organization->organization_name }}</div>
                                                    <div class="org-key">{{ $organization->organization_key }}</div>
                                                </td>
                                                <td>
                                                    <span class="org-badge {{ $organization->is_active ? 'org-badge-active' : 'org-badge-inactive' }}">
                                                        <i class="fas {{ $organization->is_active ? 'fa-circle-check' : 'fa-circle-pause' }}"></i>
                                                        {{ $organization->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                                <td><span class="org-chip">{{ $organization->users_count }} user</span></td>
                                                <td><span class="org-chip">{{ $organization->assessments_count }} assessment</span></td>
                                                <td><span class="org-chip">{{ $organization->evaluations_count }} evaluation</span></td>
                                                <td>
                                                    <div class="org-actions">
                                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editOrganizationModal{{ $organization->organization_id }}">
                                                            <i class="fas fa-pen-to-square me-1"></i>Edit
                                                        </button>
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
    </div>
</div>

<div class="modal fade org-modal" id="createOrganizationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1">Tambah Organisasi</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('admin.organizations.store') }}">
                @csrf
                <input type="hidden" name="_organization_modal" value="create">
                <div class="modal-body pt-3">
                    <div class="mb-3">
                        <label for="organization_name_create" class="form-label">Nama Organisasi</label>
                        <input id="organization_name_create" type="text" name="organization_name" class="form-control @error('organization_name') is-invalid @enderror" value="{{ old('organization_name') }}" required>
                        @error('organization_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active_create" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active_create">Aktif untuk assignment baru</label>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Organisasi</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($organizations as $organization)
    <div class="modal fade org-modal" id="editOrganizationModal{{ $organization->organization_id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-1">Edit Organisasi</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.organizations.update', $organization->organization_id) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_organization_modal" value="edit-{{ $organization->organization_id }}">
                    <div class="modal-body pt-3">
                        <div class="mb-3">
                            <label for="organization_name_{{ $organization->organization_id }}" class="form-label">Nama Organisasi</label>
                            <input id="organization_name_{{ $organization->organization_id }}" type="text" name="organization_name" class="form-control" value="{{ $activeOrganizationModal === 'edit-' . $organization->organization_id ? old('organization_name', $organization->organization_name) : $organization->organization_name }}" required>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active_{{ $organization->organization_id }}" name="is_active" value="1" {{ ($activeOrganizationModal === 'edit-' . $organization->organization_id ? old('is_active', $organization->is_active) : $organization->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active_{{ $organization->organization_id }}">Aktif untuk assignment baru</label>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Update Organisasi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

@if($errors->any())
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modalId = @json($activeOrganizationModal === 'create' ? 'createOrganizationModal' : ($activeOrganizationModal ? 'editOrganizationModal' . str_replace('edit-', '', $activeOrganizationModal) : 'createOrganizationModal'));
                const targetModal = document.getElementById(modalId);

                if (!targetModal) {
                    return;
                }

                const modal = new bootstrap.Modal(targetModal);
                modal.show();
            });
        </script>
    @endpush
@endif
@endsection
