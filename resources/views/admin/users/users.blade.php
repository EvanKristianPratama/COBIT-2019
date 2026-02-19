@extends('layouts.app')

@section('content')
<style>
    :root {
        --soft-primary: #6c9bcf;
        --soft-secondary: #a8aabc;
        --soft-success: #77c7a9;
        --soft-info: #6ec6ca;
        --soft-warning: #e9c46a;
        --soft-danger: #e88a8a;
        --soft-light: #f8f9fa;
        --soft-dark: #343a40;
    }
    
    .card {
        border: none;
        border-radius: 0.75rem;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }
    
    .card-header {
        border-radius: 0.75rem 0.75rem 0 0 !important;
        padding: 1.25rem 1.5rem;
    }
    
    .soft-success-header {
        background-color: var(--soft-success);
        color: #0d5d45;
    }
    
    .soft-danger-header {
        background-color: var(--soft-danger);
        color: #7a1a1a;
    }
    
    .table-container {
        max-height: 500px;
        overflow-y: auto;
        border-radius: 0 0 0.75rem 0.75rem;
    }
    
    .table-sticky thead th {
        position: sticky;
        top: -1px;
        background: #f8f9fa;
        z-index: 10;
        padding: 1rem 1.25rem;
        font-weight: 600;
        color: #495057;
        border-top: none;
    }
    
    .table-responsive {
        border-radius: 0 0 0.75rem 0.75rem;
    }
    
    .empty-state {
        min-height: 300px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 2rem;
    }
    
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
    }
    
    .badge-soft {
        padding: 0.5em 0.75em;
        font-weight: 500;
    }
    
    .badge-soft-primary {
        background-color: rgba(108, 155, 207, 0.2);
        color: var(--soft-primary);
    }
    
    .badge-soft-success {
        background-color: rgba(119, 199, 169, 0.2);
        color: #0d5d45;
    }
    
    .badge-soft-danger {
        background-color: rgba(232, 138, 138, 0.2);
        color: #7a1a1a;
    }
    
    .badge-soft-secondary {
        background-color: rgba(168, 170, 188, 0.2);
        color: #4a4c5c;
    }
    
    .btn-soft {
        border-width: 1px;
        background-color: transparent;
    }
    
    .btn-soft-primary {
        border-color: var(--soft-primary);
        color: var(--soft-primary);
    }
    
    .btn-soft-primary:hover {
        background-color: var(--soft-primary);
        color: white;
    }
    
    .btn-soft-success {
        border-color: var(--soft-success);
        color: #0d5d45;
    }
    
    .btn-soft-success:hover {
        background-color: var(--soft-success);
        color: white;
    }
    
    .btn-soft-danger {
        border-color: var(--soft-danger);
        color: #7a1a1a;
    }
    
    .btn-soft-danger:hover {
        background-color: var(--soft-danger);
        color: white;
    }
    
    .modal-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
    }
    
    .input-group-text {
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
    }
    
    @media (max-width: 768px) {
        .action-buttons {
            flex-direction: column;
            align-items: center;
        }
        
        .card-header h6 {
            font-size: 1rem;
        }
    }
    
    .page-header {
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .avatar {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .avatar-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .bg-soft-primary {
        background-color: rgba(108, 155, 207, 0.15);
    }
    
    .bg-soft-secondary {
        background-color: rgba(168, 170, 188, 0.15);
    }
</style>

<div class="container py-4">
    <div class="mb-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->createUser->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>Gagal menambahkan user. Periksa input form.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <div class="page-header">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
            <div class="mb-3 mb-md-0">
                <h2 class="mb-1">Daftar Akun</h2>
                <p class="text-muted mb-0">Kelola akun pengguna sistem</p>
            </div>
            <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2">
                @if(auth()->user()->role === 'admin')
                    <button
                        type="button"
                        class="btn btn-primary d-flex align-items-center justify-content-center"
                        data-bs-toggle="modal"
                        data-bs-target="#createUserModal"
                    >
                        <i class="fas fa-user-plus me-2"></i>Add User
                    </button>
                @endif
                <div class="input-group" style="min-width: 280px;">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" placeholder="Cari akun..." id="searchInput">
                </div>
            </div>
        </div>
    </div>

    {{-- Akun Aktif --}}
    <div class="card mb-4">
        <div class="card-header soft-success-header">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold">
                    <i class="fas fa-user-check me-2"></i>Akun Aktif
                </h6>
                <span class="badge badge-soft-success">{{ count($activatedUsers) }}</span>
            </div>
        </div>
        
        @if($activatedUsers->isEmpty())
            <div class="card-body">
                <div class="empty-state py-5">
                    <i class="fas fa-database fa-3x text-muted mb-3"></i>
                    <p class="h5 text-muted mb-0">Belum ada akun terdaftar</p>
                </div>
            </div>
        @else
            <div class="table-container">
                <div class="table-responsive">
                    <table class="table table-hover table-sticky mb-0">
                        <thead>
                            <tr>
                                <th class="py-3">Nama</th>
                                <th class="py-3">Email</th>
                                <th class="py-3">Role</th>
                                <th class="py-3">Organisasi</th>
                                <th class="py-3">Jabatan</th>
                                <th class="py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activatedUsers as $u)
                                <tr>
                                    <td class="align-middle">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar me-3">
                                                <div class="avatar-icon bg-soft-primary">
                                                    <i class="fas fa-user text-primary"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="fw-medium">{{ $u->name }}</div>
                                                <small class="text-muted">ID: {{ $u->id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle">{{ $u->email }}</td>
                                    <td class="align-middle">
                                        <span class="badge badge-soft 
                                            @if($u->role == 'admin') badge-soft-danger
                                            @elseif($u->role == 'pic') badge-soft-primary
                                            @else badge-soft-secondary @endif">
                                            {{ ucfirst($u->role) }}
                                        </span>
                                    </td>
                                    <td class="align-middle">{{ $u->organisasi }}</td>
                                    <td class="align-middle">{{ $u->jabatan }}</td>
                                    <td class="align-middle text-center">
                                        <div class="action-buttons">
                                            <button class="btn btn-soft btn-soft-primary btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editModal{{ $u->id }}">
                                                <i class="fas fa-edit me-1"></i>Edit
                                            </button>
                                            <form action="{{ route('admin.users.deactivate', $u->id) }}" method="POST" 
                                                onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan akun ini?');">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn btn-soft btn-soft-danger btn-sm">
                                                    <i class="fas fa-user-slash me-1"></i>Nonaktifkan
                                                </button>
                                            </form>
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

    {{-- Akun Nonaktif --}}
    <div class="card">
        <div class="card-header soft-danger-header">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold">
                    <i class="fas fa-user-slash me-2"></i>Akun Nonaktif
                </h6>
                <span class="badge badge-soft-danger">{{ count($deactivatedUsers) }}</span>
            </div>
        </div>
        
        @if($deactivatedUsers->isEmpty())
            <div class="card-body">
                <div class="empty-state py-5">
                    <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                    <p class="h5 text-muted mb-0">Tidak ada akun nonaktif</p>
                </div>
            </div>
        @else
            <div class="table-container" style="max-height: 400px;">
                <div class="table-responsive">
                    <table class="table table-hover table-sticky mb-0">
                        <thead>
                            <tr>
                                <th class="py-3">Nama</th>
                                <th class="py-3">Email</th>
                                <th class="py-3">Role</th>
                                <th class="py-3">Organisasi</th>
                                <th class="py-3">Jabatan</th>
                                <th class="py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deactivatedUsers as $u)
                                <tr>
                                    <td class="align-middle">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar me-3">
                                                <div class="avatar-icon bg-soft-secondary">
                                                    <i class="fas fa-user text-secondary"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="fw-medium">{{ $u->name }}</div>
                                                <small class="text-muted">ID: {{ $u->id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle">{{ $u->email }}</td>
                                    <td class="align-middle">
                                        <span class="badge badge-soft-secondary">
                                            {{ ucfirst($u->role) }}
                                        </span>
                                    </td>
                                    <td class="align-middle">{{ $u->organisasi }}</td>
                                    <td class="align-middle">{{ $u->jabatan }}</td>
                                    <td class="align-middle text-center">
                                        <form action="{{ route('admin.users.activate', $u->id) }}" method="POST" 
                                            onsubmit="return confirm('Aktifkan kembali akun ini?');">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-soft btn-soft-success btn-sm">
                                                <i class="fas fa-user-check me-1"></i>Aktifkan
                                            </button>
                                        </form>
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

{{-- Modal Edit --}}
@foreach($activatedUsers as $u)
<div class="modal fade" id="editModal{{ $u->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $u->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('admin.users.update', $u->id) }}">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel{{ $u->id }}">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" class="form-control" name="name" value="{{ $u->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" value="{{ $u->email }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <option value="user" {{ $u->role == 'user' ? 'selected' : '' }}>User</option>
                            <option value="admin" {{ $u->role == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="pic" {{ $u->role == 'pic' ? 'selected' : '' }}>PIC</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jabatan</label>
                        <input type="text" class="form-control" name="jabatan" value="{{ $u->jabatan }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Organisasi</label>
                        <input type="text" class="form-control" name="organisasi" value="{{ $u->organisasi }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endforeach

@if(auth()->user()->role === 'admin')
    @include('admin.users.partials.create-user-modal')
@endif

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Fungsi pencarian
    document.getElementById('searchInput').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const tables = document.querySelectorAll('.table-container table tbody');
        
        tables.forEach(table => {
            const rows = table.querySelectorAll('tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    });
    
    // Animasi saat memuat halaman
    document.addEventListener('DOMContentLoaded', function() {
        @if($errors->createUser->any())
            const createUserModalElement = document.getElementById('createUserModal');
            if (createUserModalElement) {
                const createUserModal = new bootstrap.Modal(createUserModalElement);
                createUserModal.show();
            }
        @endif

        const cards = document.querySelectorAll('.card');
        cards.forEach((card, index) => {
            setTimeout(() => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 50);
            }, index * 100);
        });
    });
</script>
@endsection
