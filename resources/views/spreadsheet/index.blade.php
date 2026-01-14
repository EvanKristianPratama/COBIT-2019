@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <!-- Header Section -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1" style="color: #0f2b5c;">
                        <i class="fas fa-table me-2"></i>My Spreadsheets
                    </h2>
                    <p class="text-muted mb-0">Manage and organize your spreadsheet data</p>
                </div>
                <a href="{{ route('spreadsheet.create') }}" class="btn btn-primary btn-lg shadow-sm">
                    <i class="fas fa-plus me-2"></i>Create New
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Spreadsheets Grid -->
            @if($spreadsheets->count() > 0)
                <div class="row g-4">
                    @foreach($spreadsheets as $sheet)
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card h-100 border-0 shadow-sm spreadsheet-card">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="spreadsheet-icon">
                                            <i class="fas fa-file-excel"></i>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('spreadsheet.show', $sheet->id) }}">
                                                        <i class="fas fa-edit me-2 text-primary"></i>Open & Edit
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form action="{{ route('spreadsheet.destroy', $sheet->id) }}" method="POST" class="delete-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="dropdown-item text-danger btn-delete">
                                                            <i class="fas fa-trash me-2"></i>Delete
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <a href="{{ route('spreadsheet.show', $sheet->id) }}" class="text-decoration-none">
                                        <h5 class="card-title fw-bold text-dark mb-2">{{ $sheet->title }}</h5>
                                    </a>
                                    
                                    <p class="card-text text-muted small mb-3">
                                        {{ Str::limit($sheet->description, 80) ?: 'No description' }}
                                    </p>
                                    
                                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                        <div class="d-flex gap-2 w-100">
                                            <a href="{{ route('spreadsheet.show', $sheet->id) }}" class="btn btn-sm btn-outline-primary flex-grow-1">
                                                Open <i class="fas fa-arrow-right ms-1"></i>
                                            </a>
                                            <form action="{{ route('spreadsheet.destroy', $sheet->id) }}" method="POST" class="delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete" title="Delete Spreadsheet">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- Empty State -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="empty-state-icon mb-4">
                            <i class="fas fa-table"></i>
                        </div>
                        <h4 class="fw-bold text-muted mb-2">No Spreadsheets Yet</h4>
                        <p class="text-muted mb-4">Get started by creating your first spreadsheet</p>
                        <a href="{{ route('spreadsheet.create') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus me-2"></i>Create Your First Spreadsheet
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .spreadsheet-card {
        transition: all 0.3s ease;
        border-radius: 12px;
    }
    .spreadsheet-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.12) !important;
    }
    .spreadsheet-icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, #28a745, #20c997);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.25rem;
    }
    .empty-state-icon {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, #e9ecef, #dee2e6);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        font-size: 2.5rem;
        color: #6c757d;
    }
    .btn-primary {
        background: linear-gradient(135deg, #0f2b5c, #1a3d6b);
        border: none;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #1a3d6b, #2a4d7b);
    }
    .dropdown-menu {
        border-radius: 8px;
        border: none;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteButtons = document.querySelectorAll('.btn-delete');
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const form = this.closest('.delete-form');
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Delete Spreadsheet?',
                        text: "This action cannot be undone!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="fas fa-trash me-1"></i> Yes, delete it',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                } else {
                    if (confirm('Are you sure you want to delete this spreadsheet? This action cannot be undone.')) {
                        form.submit();
                    }
                }
            });
        });
    });
</script>
@endsection
