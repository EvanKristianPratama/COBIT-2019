@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">
            <!-- Back Button -->
            <div class="mb-4">
                <a href="{{ route('spreadsheet.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                </a>
            </div>

            <!-- Main Card -->
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header py-4 rounded-top-4" style="background: linear-gradient(135deg, #0f2b5c, #1a3d6b) !important;">
                    <h4 class="mb-0 fw-bold text-white text-center">
                        <i class="fas fa-table me-2"></i>New Spreadsheet
                    </h4>
                </div>
                
                <div class="card-body p-4">
                    <!-- Error Messages -->
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Tab Navigation -->
                    <ul class="nav nav-pills nav-fill mb-4" id="createTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="blank-tab" data-bs-toggle="pill" data-bs-target="#blank" type="button">
                                <i class="fas fa-plus-circle me-2"></i>Blank Spreadsheet
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="import-tab" data-bs-toggle="pill" data-bs-target="#import" type="button">
                                <i class="fas fa-file-upload me-2"></i>Import Excel
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="createTabsContent">
                        <!-- Blank Spreadsheet Tab -->
                        <div class="tab-pane fade show active" id="blank" role="tabpanel">
                            <form action="{{ route('spreadsheet.store') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control form-control-lg" 
                                           required placeholder="e.g. Risk Assessment" value="{{ old('title') }}" autofocus>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Description <span class="text-muted fw-normal">(Optional)</span></label>
                                    <textarea name="description" class="form-control" rows="3"
                                              placeholder="Brief description...">{{ old('description') }}</textarea>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-plus-circle me-2"></i>Create Spreadsheet
                                </button>
                            </form>
                        </div>

                        <!-- Import Excel Tab -->
                        <div class="tab-pane fade" id="import" role="tabpanel">
                            <form action="{{ route('spreadsheet.import') }}" method="POST" enctype="multipart/form-data" id="import-form">
                                @csrf
                                
                                <!-- Drop Zone -->
                                <div class="drop-zone mb-3" id="drop-zone">
                                    <input type="file" name="file" id="file-input" accept=".xlsx,.xls,.csv" class="d-none">
                                    <div class="drop-content text-center py-4" id="drop-content">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                        <p class="mb-2 fw-semibold">Drag & drop file here</p>
                                        <p class="text-muted small mb-3">or</p>
                                        <button type="button" class="btn btn-outline-primary" id="browse-btn">
                                            <i class="fas fa-folder-open me-2"></i>Browse Files
                                        </button>
                                        <p class="text-muted small mt-3 mb-0">.xlsx, .xls, .csv (Max 10MB)</p>
                                    </div>
                                    <div class="file-info text-center py-4 d-none" id="file-info">
                                        <i class="fas fa-file-excel fa-3x text-success mb-3"></i>
                                        <p class="mb-1 fw-semibold" id="file-name">filename.xlsx</p>
                                        <p class="text-muted small mb-3" id="file-size">2.5 MB</p>
                                        <button type="button" class="btn btn-sm btn-outline-danger" id="remove-file">
                                            <i class="fas fa-times me-1"></i>Remove
                                        </button>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Title <span class="text-muted fw-normal">(Optional - uses filename)</span></label>
                                    <input type="text" name="title" class="form-control" placeholder="Custom title">
                                </div>

                                <button type="submit" class="btn btn-success btn-lg w-100" id="import-btn" disabled>
                                    <i class="fas fa-file-import me-2"></i>Import Spreadsheet
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .rounded-4 { border-radius: 1rem !important; }
    .rounded-top-4 { border-radius: 1rem 1rem 0 0 !important; }
    
    .nav-pills .nav-link {
        color: #454749ff;
        background: #909090ff;
        border: 2px solid #bec0c2ff;
        border-radius: 10px;
        margin: 0 4px;
        padding: 12px 20px;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    .nav-pills .nav-link.active {
        background: linear-gradient(135deg, #0f2b5c, #1a3d6b);
        border-color: #0f2b5c;
        color: #ffffff !important;
    }
    .nav-pills .nav-link:hover:not(.active) {
        background: #bbbbbbff;
        color: #495057;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #0f2b5c, #1a3d6b);
        border: none;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #1a3d6b, #2a4d7b);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(15, 43, 92, 0.3);
    }
    .btn-success {
        background: linear-gradient(135deg, #28a745, #20c997);
        border: none;
    }
    .btn-success:hover:not(:disabled) {
        background: linear-gradient(135deg, #218838, #1db98c);
        transform: translateY(-1px);
    }
    
    .drop-zone {
        border: 2px dashed #dee2e6;
        border-radius: 12px;
        background: #fafbfc;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .drop-zone:hover, .drop-zone.dragover {
        border-color: #28a745;
        background: #f0fff4;
    }
    
    .form-control:focus {
        border-color: #0f2b5c;
        box-shadow: 0 0 0 0.2rem rgba(15, 43, 92, 0.15);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var dropZone = document.getElementById('drop-zone');
    var fileInput = document.getElementById('file-input');
    var browseBtn = document.getElementById('browse-btn');
    var removeBtn = document.getElementById('remove-file');
    var importBtn = document.getElementById('import-btn');
    var dropContent = document.getElementById('drop-content');
    var fileInfo = document.getElementById('file-info');
    var fileName = document.getElementById('file-name');
    var fileSize = document.getElementById('file-size');

    // Browse button click
    browseBtn.addEventListener('click', function() {
        fileInput.click();
    });

    // Drop zone click
    dropZone.addEventListener('click', function(e) {
        if (e.target === dropZone || e.target.closest('.drop-content')) {
            fileInput.click();
        }
    });

    // File input change
    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            showFile(this.files[0]);
        }
    });

    // Drag events
    ['dragenter', 'dragover'].forEach(function(eventName) {
        dropZone.addEventListener(eventName, function(e) {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });
    });

    ['dragleave', 'drop'].forEach(function(eventName) {
        dropZone.addEventListener(eventName, function(e) {
            e.preventDefault();
            dropZone.classList.remove('dragover');
        });
    });

    // Drop file
    dropZone.addEventListener('drop', function(e) {
        var files = e.dataTransfer.files;
        if (files.length > 0) {
            var file = files[0];
            var ext = file.name.split('.').pop().toLowerCase();
            if (['xlsx', 'xls', 'csv'].indexOf(ext) !== -1) {
                fileInput.files = files;
                showFile(file);
            } else {
                alert('Please upload .xlsx, .xls or .csv file only');
            }
        }
    });

    // Remove file
    removeBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        fileInput.value = '';
        hideFile();
    });

    function showFile(file) {
        fileName.textContent = file.name;
        fileSize.textContent = formatBytes(file.size);
        dropContent.classList.add('d-none');
        fileInfo.classList.remove('d-none');
        importBtn.disabled = false;
    }

    function hideFile() {
        dropContent.classList.remove('d-none');
        fileInfo.classList.add('d-none');
        importBtn.disabled = true;
    }

    function formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        var k = 1024;
        var sizes = ['Bytes', 'KB', 'MB', 'GB'];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
});
</script>
@endsection

