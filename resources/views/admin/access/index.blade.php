@extends('layouts.admin')

@section('admin_title', 'Manage Akses')

@section('admin_content')
<style>
    :root {
        --access-border: #d7dfeb;
        --access-bg: #ffffff;
        --access-muted: #64748b;
        --access-text: #0f172a;
        --access-primary: var(--cobit-primary);
        --access-primary-soft: rgba(15, 43, 92, 0.08);
        --access-bronze: var(--cobit-secondary);
        --access-bronze-soft: rgba(26, 61, 107, 0.1);
        --access-success: var(--cobit-accent);
        --access-success-soft: rgba(15, 106, 217, 0.1);
    }

    .access-card, .access-panel { border: 1px solid var(--access-border); border-radius: 20px; background: var(--access-bg); box-shadow: 0 16px 34px rgba(15, 23, 42, 0.05); }
    .access-card { padding: 1.15rem 1.2rem; height: 100%; }
    .access-label { font-size: 0.74rem; text-transform: uppercase; letter-spacing: 0.06em; font-weight: 800; color: var(--access-muted); }
    .access-value { margin-top: 0.5rem; font-size: 1.9rem; font-weight: 800; line-height: 1; }
    .access-icon { width: 48px; height: 48px; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; background: var(--access-primary-soft); color: var(--access-primary); }
    .access-icon-bronze { background: var(--access-bronze-soft); color: var(--access-bronze); }
    .access-icon-success { background: var(--access-success-soft); color: var(--access-success); }
    .access-panel .card-header { background: transparent; border-bottom: 1px solid var(--access-border); padding: 1rem 1.2rem; }
    .access-panel .card-body { padding: 1rem 1.2rem 1.2rem; }
    .access-panel-title { font-size: 1rem; font-weight: 800; margin-bottom: 0.16rem; }
    .access-chip { display: inline-flex; align-items: center; padding: 0.38rem 0.72rem; border-radius: 999px; background: #f8fafc; border: 1px solid #dbe4ee; color: #334155; font-size: 0.78rem; font-weight: 800; }
    .access-role-card, .access-profile-card { border: 1px solid var(--access-border); border-radius: 18px; padding: 0.9rem; background: linear-gradient(135deg, rgba(23, 50, 75, 0.04), rgba(255, 255, 255, 0.96)); height: 100%; }
    .access-role-title, .access-profile-title { font-size: 0.98rem; font-weight: 800; margin-bottom: 0.2rem; }
    .access-role-count, .access-profile-count { font-size: 1.3rem; font-weight: 800; line-height: 1; margin-bottom: 0.4rem; }
    .access-list { display: grid; gap: 0.45rem; }
    .access-list-item { display: flex; align-items: center; gap: 0.5rem; color: #334155; font-size: 0.83rem; font-weight: 700; }
    .access-profile-form { display: grid; gap: 0.85rem; }
    .access-permission-grid { display: grid; gap: 0.45rem; }
    .access-checklist { padding: 0.72rem 0.78rem; border: 1px solid #dbe4ee; border-radius: 14px; background: #ffffff; }
    .access-checklist .form-check { margin-bottom: 0.4rem; }
    .access-checklist .form-check:last-child { margin-bottom: 0; }
    .access-checklist .form-check-input { margin-top: 0.2rem; }
    .access-checklist .form-check-input:checked { background-color: var(--cobit-accent); border-color: var(--cobit-accent); }
    .access-checklist .form-check-label { color: #334155; font-size: 0.84rem; font-weight: 700; line-height: 1.35; }
    .access-table { margin-bottom: 0; }
    .access-table thead th { background: #f8fafc; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.72rem; font-weight: 800; border-bottom: 1px solid var(--access-border); padding: 0.9rem 1rem; }
    .access-table tbody td { border-color: #edf2f7; padding: 0.95rem 1rem; vertical-align: middle; }
    .matrix-icon { width: 30px; height: 30px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; border: 1px solid transparent; font-size: 0.86rem; }
    .matrix-yes { color: var(--access-success); background: var(--access-success-soft); border-color: rgba(31, 111, 80, 0.18); }
    .matrix-no { color: #94a3b8; background: #f8fafc; border-color: #dbe4ee; }
</style>

<div class="container-fluid px-0">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-circle-exclamation me-2"></i>Setting akses belum valid. Periksa pilihan permission yang dikirim.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3"><div class="access-card"><div class="d-flex justify-content-between align-items-start"><div><div class="access-label">Permission</div><div class="access-value">{{ $stats['permissions'] }}</div></div><span class="access-icon"><i class="fas fa-shield-halved"></i></span></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="access-card"><div class="d-flex justify-content-between align-items-start"><div><div class="access-label">Manual Assignment</div><div class="access-value">{{ $stats['manual_assignments'] }}</div></div><span class="access-icon access-icon-bronze"><i class="fas fa-link"></i></span></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="access-card"><div class="d-flex justify-content-between align-items-start"><div><div class="access-label">Admin</div><div class="access-value">{{ $stats['admins'] }}</div></div><span class="access-icon"><i class="fas fa-user-shield"></i></span></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="access-card"><div class="d-flex justify-content-between align-items-start"><div><div class="access-label">Akun Aktif</div><div class="access-value">{{ $stats['active_users'] }}</div></div><span class="access-icon access-icon-success"><i class="fas fa-user-check"></i></span></div></div></div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-4">
            <div class="card access-panel border-0 h-100">
                <div class="card-header">
                    <div class="access-panel-title">Role Summary</div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($roleSummaries as $roleSummary)
                            <div class="col-12">
                                <div class="access-role-card">
                                    <div class="d-flex justify-content-between align-items-start gap-3">
                                        <div>
                                            <div class="access-role-title">{{ $roleSummary['label'] }}</div>
                                            <div class="access-role-count">{{ $roleSummary['count'] }}</div>
                                        </div>
                                        <span class="access-chip">{{ strtoupper($roleSummary['key']) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card access-panel border-0 h-100">
                <div class="card-header">
                    <div class="access-panel-title">Access Profiles</div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($accessProfileSummaries as $accessProfileSummary)
                            <div class="col-md-4">
                                <div class="access-profile-card">
                                    <form method="POST" action="{{ route('admin.access.update-profile', $accessProfileSummary['key']) }}" class="access-profile-form">
                                        @csrf
                                        @method('PUT')
                                        <div class="d-flex justify-content-between align-items-start gap-3">
                                            <div>
                                                <div class="access-profile-title">{{ $accessProfileSummary['label'] }}</div>
                                                <div class="access-profile-count">{{ $accessProfileSummary['count'] }}</div>
                                            </div>
                                            <span class="access-chip">{{ strtoupper($accessProfileSummary['key']) }}</span>
                                        </div>

                                        <div class="access-permission-grid access-checklist">
                                            @foreach($permissionOptions as $permissionOption)
                                                @php
                                                    $profileHasPermission = in_array($permissionOption['permission'], $accessProfileSummary['permissions'], true);
                                                    $permissionFieldId = 'profile-' . $accessProfileSummary['key'] . '-' . str_replace(['.', '_'], '-', $permissionOption['permission']);
                                                @endphp
                                                <div class="form-check">
                                                    <input
                                                        class="form-check-input"
                                                        type="checkbox"
                                                        id="{{ $permissionFieldId }}"
                                                        name="permissions[]"
                                                        value="{{ $permissionOption['permission'] }}"
                                                        {{ $profileHasPermission ? 'checked' : '' }}
                                                    >
                                                    <label class="form-check-label" for="{{ $permissionFieldId }}">
                                                        {{ $permissionOption['label'] }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>

                                        <button type="submit" class="btn btn-sm btn-primary w-100">
                                            <i class="fas fa-save me-2"></i>Simpan
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-7">
            <div class="card access-panel border-0 h-100">
                <div class="card-header">
                    <div class="access-panel-title">Permission Matrix</div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table access-table align-middle">
                            <thead>
                                <tr>
                                    <th>Permission</th>
                                    <th class="text-center">Admin</th>
                                    <th class="text-center">Auditee</th>
                                    <th class="text-center">DF Editor</th>
                                    <th class="text-center">Assessor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($permissionMatrix as $permissionRow)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $permissionRow['label'] }}</div>
                                            <div class="text-muted small">{{ $permissionRow['permission'] }}</div>
                                        </td>
                                        <td class="text-center"><span class="matrix-icon {{ $permissionRow['admin'] ? 'matrix-yes' : 'matrix-no' }}" aria-label="{{ $permissionRow['admin'] ? 'Yes' : 'No' }}"><i class="fas {{ $permissionRow['admin'] ? 'fa-check' : 'fa-xmark' }}"></i></span></td>
                                        <td class="text-center"><span class="matrix-icon {{ $permissionRow['viewer'] ? 'matrix-yes' : 'matrix-no' }}" aria-label="{{ $permissionRow['viewer'] ? 'Yes' : 'No' }}"><i class="fas {{ $permissionRow['viewer'] ? 'fa-check' : 'fa-xmark' }}"></i></span></td>
                                        <td class="text-center"><span class="matrix-icon {{ $permissionRow['df_editor'] ? 'matrix-yes' : 'matrix-no' }}" aria-label="{{ $permissionRow['df_editor'] ? 'Yes' : 'No' }}"><i class="fas {{ $permissionRow['df_editor'] ? 'fa-check' : 'fa-xmark' }}"></i></span></td>
                                        <td class="text-center"><span class="matrix-icon {{ $permissionRow['assessor'] ? 'matrix-yes' : 'matrix-no' }}" aria-label="{{ $permissionRow['assessor'] ? 'Yes' : 'No' }}"><i class="fas {{ $permissionRow['assessor'] ? 'fa-check' : 'fa-xmark' }}"></i></span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card access-panel border-0 h-100">
                <div class="card-header">
                    <div class="access-panel-title">Recent Access Assignment</div>
                </div>
                <div class="card-body">
                    @if($recentAssignments->isEmpty())
                        <div class="text-center text-muted py-5">Belum ada assignment akses manual.</div>
                    @else
                        <div class="d-grid gap-3">
                            @foreach($recentAssignments as $recentAssignment)
                                <div class="access-profile-card">
                                    <div class="d-flex justify-content-between gap-3 align-items-start">
                                        <div>
                                            <div class="fw-semibold">{{ $recentAssignment['user_name'] }}</div>
                                            <div class="text-muted small">{{ $recentAssignment['target_type'] }}</div>
                                        </div>
                                        <span class="access-chip">{{ $recentAssignment['access_profile'] }}</span>
                                    </div>
                                    <div class="mt-2 small text-muted">Target: {{ $recentAssignment['target_label'] }}</div>
                                    <div class="mt-1 small text-muted">Assigned: {{ optional($recentAssignment['assigned_at'])->format('d M Y H:i') }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
