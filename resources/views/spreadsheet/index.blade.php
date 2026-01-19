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

            <!-- Spreadsheets List -->
            @if($spreadsheets->count() > 0)
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="table-responsive" style="overflow: visible;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 border-0 text-uppercase small fw-bold text-muted py-3" style="width: 50%;">Name</th>
                                    <th class="border-0 text-uppercase small fw-bold text-muted py-3">Last Modified</th>
                                    <th class="pe-4 border-0 text-uppercase small fw-bold text-muted py-3 text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($spreadsheets as $sheet)
                                    <tr class="spreadsheet-row" data-href="{{ route('spreadsheet.show', $sheet->id) }}" style="cursor: pointer;">
                                        <td class="ps-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="list-icon me-3">
                                                    <i class="fas fa-file-excel"></i>
                                                </div>
                                                <div>
                                                    <a href="{{ route('spreadsheet.show', $sheet->id) }}" class="text-decoration-none d-block">
                                                        <h6 class="mb-0 fw-bold text-dark">{{ $sheet->title }}</h6>
                                                    </a>
                                                    <small class="text-muted d-block text-truncate" style="max-width: 400px;">
                                                        {{ $sheet->description ?: 'No description' }}
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3">
                                            <span class="text-muted small">
                                                <i class="far fa-clock me-1"></i>
                                                {{ $sheet->updated_at->diffForHumans() }}
                                            </span>
                                        </td>
                                        <td class="pe-4 py-3 text-end action-column">
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="{{ route('spreadsheet.show', $sheet->id) }}" target="_blank" class="btn btn-light btn-sm text-success rounded-circle shadow-sm" title="Open in New Tab" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>
                                                <button type="button" class="btn btn-light btn-sm text-warning rounded-circle shadow-sm btn-edit-details" 
                                                    title="Edit Details"
                                                    style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;"
                                                    data-id="{{ $sheet->id }}" 
                                                    data-title="{{ $sheet->title }}" 
                                                    data-description="{{ $sheet->description }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="{{ route('spreadsheet.destroy', $sheet->id) }}" method="POST" class="d-inline delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-light btn-sm text-danger rounded-circle shadow-sm btn-delete" 
                                                        title="Delete"
                                                        style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-trash"></i>
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
            @else
                <!-- Empty State -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="empty-state-icon mb-4">
                            <i class="fas fa-table"></i>
                        </div>
                        <h4 class="fw-bold text-muted mb-2">No Spreadsheets Yet</h4>
                        <p class="text-muted mb-4">Get started by creating your first spreadsheet</p>
                        <a href="{{ route('spreadsheet.create') }}" class="btn btn-primary btn-lg px-4">
                            <i class="fas fa-plus me-2"></i>Create Your First Spreadsheet
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Edit Spreadsheet Modal -->
<div class="modal fade" id="editSpreadsheetModal" tabindex="-1" aria-labelledby="editSpreadsheetModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="editSpreadsheetModalLabel" style="color: #0f2b5c;">Edit Spreadsheet Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editSpreadsheetForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="edit_title" class="form-label fw-bold small text-muted text-uppercase">Title</label>
                        <input type="text" class="form-control form-control-lg border-0 bg-light" id="edit_title" name="title" required placeholder="Enter spreadsheet title">
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label fw-bold small text-muted text-uppercase">Description</label>
                        <textarea class="form-control border-0 bg-light" id="edit_description" name="description" rows="4" placeholder="Enter a brief description"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                </div>
            </form>
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
    .list-icon {
        width: 40px;
        height: 40px;
        background: rgba(40, 167, 69, 0.1);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #28a745;
        font-size: 1.1rem;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(15, 43, 92, 0.02);
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

        // Row Click Navigation
        document.querySelectorAll('.spreadsheet-row').forEach(row => {
            row.addEventListener('click', function(e) {
                // Ignore if clicked on buttons, links, or dropdowns
                if (e.target.closest('.action-column') || e.target.closest('.dropdown') || e.target.closest('a') || e.target.closest('button')) {
                    return;
                }
                const href = this.getAttribute('data-href');
                if (href) window.location.href = href;
            });
        });

        // Edit Modal Handling
        const editButtons = document.querySelectorAll('.btn-edit-details');
        const editModal = new bootstrap.Modal(document.getElementById('editSpreadsheetModal'));
        const editForm = document.getElementById('editSpreadsheetForm');
        const editTitleInput = document.getElementById('edit_title');
        const editDescriptionInput = document.getElementById('edit_description');

        editButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const id = this.getAttribute('data-id');
                const title = this.getAttribute('data-title');
                const description = this.getAttribute('data-description');

                editForm.action = `/spreadsheet/${id}`;
                editTitleInput.value = title;
                editDescriptionInput.value = (description === 'null' || !description) ? '' : description;

                editModal.show();
            });
        });
    });
</script>
@endsection
