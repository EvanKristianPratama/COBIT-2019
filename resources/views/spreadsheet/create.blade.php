@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card shadow-lg border-0 rounded-4 bg-white">
                <div class="card-header bg-gradient text-white py-4 rounded-top-4" style="background: linear-gradient(135deg, #0f2b5c, #1a3d6b);">
                    <div class="d-flex align-items-center">
                        <div class="icon-wrapper me-3 text-white">
                            <i class="fas fa-table fa-2x"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold text-white">Create New Spreadsheet</h4>
                            <small class="opacity-75 text-white-50">Start organizing your data</small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4 bg-white">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('spreadsheet.store') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-heading me-2 text-primary"></i>Title
                            </label>
                            <input type="text" 
                                   name="title" 
                                   class="form-control form-control-lg @error('title') is-invalid @enderror" 
                                   required 
                                   placeholder="e.g. Risk Assessment Brainstorming"
                                   value="{{ old('title') }}"
                                   autofocus>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-align-left me-2 text-primary"></i>Description 
                                <span class="text-muted fw-normal">(Optional)</span>
                            </label>
                            <textarea name="description" 
                                      class="form-control @error('description') is-invalid @enderror" 
                                      rows="4"
                                      placeholder="Add a brief description of your spreadsheet...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('spreadsheet.index') }}" class="btn btn-outline-secondary px-4">
                                <i class="fas fa-arrow-left me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="fas fa-plus-circle me-2"></i>Create Spreadsheet
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quick Tips Card -->
            <div class="card border-0 shadow-sm mt-4 rounded-3">
                <div class="card-body">
                    <h6 class="fw-bold text-muted mb-3">
                        <i class="fas fa-lightbulb text-warning me-2"></i>Quick Tips
                    </h6>
                    <ul class="list-unstyled mb-0 small text-muted">
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Use a descriptive title to easily find your spreadsheet later</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>You can edit cell values, format text, and merge cells</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Don't forget to save your changes using the Save button</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .rounded-4 { border-radius: 1rem !important; }
    .rounded-top-4 { border-radius: 1rem 1rem 0 0 !important; }
    .icon-wrapper {
        width: 50px;
        height: 50px;
        background: rgba(255,255,255,0.2);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .form-control:focus {
        border-color: #0f2b5c;
        box-shadow: 0 0 0 0.2rem rgba(15, 43, 92, 0.15);
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
</style>
@endsection
