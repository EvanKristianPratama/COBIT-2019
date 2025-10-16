@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-6">
            <div class="card shadow-lg border-0 rounded-lg">
                <!-- Header Card -->
                <div class="card-header bg-primary text-white text-center py-4 rounded-top">
                    <h3 class="mb-0">{{ __('Fill Account Informationw') }}</h3>
                </div>

                <!-- Body Card with Scrollspy -->
                <div class="card-body p-5" data-bs-spy="scroll" data-bs-target="#navbar-example2" data-bs-offset="0" tabindex="0" style="max-height: 400px; overflow-y: auto;">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <!-- Name Field -->
                        <div id="name-section" class="mb-4">
                            <label for="name" class="form-label fw-bold text-primary">{{ __('Name') }}</label>
                            <input id="name" type="text" class="form-control form-control-lg @error('name') is-invalid @enderror" name="name" value="{{ $name }}" required autocomplete="name" autofocus placeholder="Enter your name">
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Email Field -->
                        <div id="email-section" class="mb-4">
                            <label for="email" class="form-label fw-bold text-primary">{{ __('Email Address') }}</label>
                        <input id="email" type="email" class="form-control form-control-lg bg-light" name="email" value="{{ $email }}" readonly>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Hidden Password Field (untuk form saja) -->
                        <input type="hidden" name="password" value="{{ $password }}">
                        <input type="hidden" name="password_confirmation" value="{{ $password }}">


                        <!-- Organisasi Field -->
                        <div id="organisasi-section" class="mb-4">
                            <label for="organisasi" class="form-label fw-bold text-primary">{{ __('Organisasi') }}</label>
                            <input id="organisasi" type="text" class="form-control form-control-lg @error('organisasi') is-invalid @enderror" name="organisasi" required placeholder="Enter your organization">
                            @error('organisasi')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Jabatan Field -->
                        <div id="jabatan-section" class="mb-4">
                            <label for="jabatan" class="form-label fw-bold text-primary">{{ __('Jabatan') }}</label>

                            <select id="jabatan" name="jabatan" class="form-select form-select-lg @error('jabatan') is-invalid @enderror" required>
                                <option value="" disabled {{ old('jabatan') ? '' : 'selected' }}>-- Select Jabatan --</option>
                                <option value="Board" {{ old('jabatan') === 'Board' ? 'selected' : '' }}>Board</option>
                                <option value="Executive Management" {{ old('jabatan') === 'Executive Management' ? 'selected' : '' }}>Executive Management</option>
                                <option value="Business Managers" {{ old('jabatan') === 'Business Managers' ? 'selected' : '' }}>Business Managers</option>
                                <option value="IT Managers" {{ old('jabatan') === 'IT Managers' ? 'selected' : '' }}>IT Managers</option>
                                <option value="Assurance Providers" {{ old('jabatan') === 'Assurance Providers' ? 'selected' : '' }}>Assurance Providers</option>
                                <option value="Risk Management" {{ old('jabatan') === 'Risk Management' ? 'selected' : '' }}>Risk Management</option>
                                <option value="Staff" {{ old('jabatan') === 'Staff' ? 'selected' : '' }}>Staff</option>
                            </select>

                            @error('jabatan')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Register Button (Tombol berada di dalam form) -->
                        <div class="p-4">
                            <div class="d-grid">
                                <button type="submit" class="btn btn-warning btn-lg text-white fw-bold">
                                    {{ __('Register') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Footer Card -->
                <div class="card-footer text-center bg-light py-3 rounded-bottom">
                    <small class="text-muted">
                        Already have an account?
                        <a href="{{ route('login') }}" class="text-decoration-none text-primary fw-bold">Login here</a>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
