@extends('layouts.app')

@section('content')
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            background: radial-gradient(circle at 10% 20%, rgba(15, 106, 217, 0.3), transparent 45%),
                linear-gradient(135deg, #081a3d, #0f2b5c, #1a3d6b);
        }

        .auth-card {
            border: none;
            border-radius: 28px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.45);
            backdrop-filter: blur(18px);
        }

        .hero-panel {
            position: relative;
            color: #fff;
            padding: 3rem;
            background: linear-gradient(160deg, #0f2b5c, #0f6ad9 60%, #61dafb);
            overflow: hidden;
        }

        .hero-panel::before,
        .hero-panel::after {
            content: '';
            position: absolute;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.15);
        }

        .hero-panel::before {
            width: 220px;
            height: 220px;
            top: -60px;
            right: -80px;
        }

        .hero-panel::after {
            width: 180px;
            height: 180px;
            bottom: -70px;
            left: -50px;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero-content .badge {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            font-weight: 600;
            letter-spacing: 0.08em;
            padding: 0.35rem 0.9rem;
        }

        .hero-content h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-top: 1rem;
        }

        .hero-content p {
            opacity: 0.9;
            margin: 1rem 0 1.25rem;
            line-height: 1.7;
        }

        .hero-content ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .hero-content li {
            display: flex;
            gap: 0.6rem;
            margin-bottom: 0.85rem;
            font-weight: 500;
        }

        .hero-content li i {
            color: #61dafb;
            margin-top: 0.1rem;
        }

        .form-panel {
            padding: 3rem;
            background: #fff;
        }

        .form-heading h3 {
            font-weight: 700;
            color: #0f2b5c;
            margin-bottom: 0.35rem;
        }

        .form-heading p {
            color: #718096;
            margin-bottom: 2rem;
        }

        .register-body {
            max-height: 520px;
            overflow-y: auto;
            padding-right: 0.25rem;
        }

        .register-body::-webkit-scrollbar {
            width: 8px;
        }

        .register-body::-webkit-scrollbar-track {
            background: #edf2f7;
            border-radius: 10px;
        }

        .register-body::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #0f2b5c, #0f6ad9);
            border-radius: 10px;
        }

        .form-label {
            color: #0f2b5c;
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 0.45rem;
        }

        .input-icon-wrapper {
            position: relative;
        }

        .input-icon-wrapper i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #4a6fa5;
            font-size: 1rem;
        }

        .input-icon-wrapper .form-control,
        .input-icon-wrapper .form-select {
            padding-left: 2.75rem;
        }

        .form-control,
        .form-select {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.85rem 1rem;
            font-size: 0.95rem;
            transition: all 0.25s ease;
            background: #f8fafc;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #0f6ad9;
            box-shadow: 0 0 0 4px rgba(15, 106, 217, 0.15);
            background: #fff;
            transform: translateY(-1px);
        }

        .form-control::placeholder {
            color: #94a3b8;
        }

        .invalid-feedback {
            font-size: 0.85rem;
            margin-top: 0.35rem;
        }

        .is-invalid {
            border-color: #dc3545 !important;
            background: #fff5f5;
        }

        .btn-register {
            background: linear-gradient(135deg, #0f2b5c, #0f6ad9);
            border: none;
            border-radius: 12px;
            padding: 0.95rem;
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            color: #fff;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            box-shadow: 0 12px 30px rgba(15, 106, 217, 0.35);
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 40px rgba(15, 106, 217, 0.45);
            background: linear-gradient(135deg, #0f6ad9, #0f2b5c);
        }

        .register-footer {
            margin-top: 2rem;
            text-align: center;
            color: #6b7280;
        }

        .register-footer a {
            color: #0f6ad9;
            font-weight: 600;
            text-decoration: none;
            margin-left: 0.35rem;
        }

        .register-footer a:hover {
            color: #0c4fb5;
            text-decoration: underline;
        }

        @media (max-width: 991.98px) {
            .form-panel {
                padding: 2.25rem;
            }
        }

        @media (max-width: 575.98px) {
            .form-panel {
                padding: 2rem 1.5rem;
            }
        }
    </style>

    <div class="auth-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-11 col-xl-10">
                    <div class="auth-card">
                        <div class="row g-0 align-items-stretch">
                            <div class="col-lg-5 d-none d-lg-flex hero-panel">
                                <div class="hero-content">
                                    <span class="badge text-uppercase">PT LAPI Divusi</span>
                                    <h2>COBIT 2019</h2>
                                    <p>
                                        The latest global framework for the governance and management of enterprise
                                        information and technology </p>
                                    <ul class="ps-0">
                                        <li><i class="fas fa-check-circle"></i>COBIT Component Dictionary</li>
                                        <li><i class="fas fa-check-circle"></i>COBIT Design Toolkit</li>
                                        <li><i class="fas fa-check-circle"></i>COBIT Assessment</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-lg-7 form-panel">
                                <div class="form-heading">
                                    <h3>Create an account</h3>
                                    <p>Complete your profile to personalize the assessment.</p>
                                </div>
                                <div class="register-body">
                                    <form method="POST" action="{{ route('register') }}">
                                        @csrf

                                        <div class="mb-3">
                                            <label for="name" class="form-label">
                                                <i class="fas fa-user me-1"></i>Full Name
                                            </label>
                                            <div class="input-icon-wrapper">
                                                <i class="fas fa-user"></i>
                                                <input id="name" type="text"
                                                    class="form-control @error('name') is-invalid @enderror" name="name"
                                                    value="{{ old('name') }}" required autocomplete="name" autofocus
                                                    placeholder="Enter your full name">
                                            </div>
                                            @error('name')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="email" class="form-label">
                                                <i class="fas fa-envelope me-1"></i>Email Address
                                            </label>
                                            <div class="input-icon-wrapper">
                                                <i class="fas fa-envelope"></i>
                                                <input id="email" type="email"
                                                    class="form-control @error('email') is-invalid @enderror" name="email"
                                                    value="{{ old('email') }}" required autocomplete="email"
                                                    placeholder="your.email@example.com">
                                            </div>
                                            @error('email')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="password" class="form-label">
                                                <i class="fas fa-lock me-1"></i>Password
                                            </label>
                                            <div class="input-icon-wrapper">
                                                <i class="fas fa-lock"></i>
                                                <input id="password" type="password"
                                                    class="form-control @error('password') is-invalid @enderror"
                                                    name="password" required autocomplete="new-password"
                                                    placeholder="Create a strong password">
                                            </div>
                                            @error('password')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="password-confirm" class="form-label">
                                                <i class="fas fa-lock me-1"></i>Confirm Password
                                            </label>
                                            <div class="input-icon-wrapper">
                                                <i class="fas fa-lock"></i>
                                                <input id="password-confirm" type="password" class="form-control"
                                                    name="password_confirmation" required autocomplete="new-password"
                                                    placeholder="Re-enter your password">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="organisasi" class="form-label">
                                                <i class="fas fa-building me-1"></i>Organization
                                            </label>
                                            <div class="input-icon-wrapper">
                                                <i class="fas fa-building"></i>
                                                <input id="organisasi" type="text"
                                                    class="form-control @error('organisasi') is-invalid @enderror"
                                                    name="organisasi" value="{{ old('organisasi') }}" required
                                                    placeholder="Your organization name">
                                            </div>
                                            @error('organisasi')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="mb-4">
                                            <label for="jabatan" class="form-label">
                                                <i class="fas fa-briefcase me-1"></i>Position
                                            </label>
                                            <div class="input-icon-wrapper">
                                                <i class="fas fa-briefcase"></i>
                                                <select id="jabatan" name="jabatan"
                                                    class="form-select @error('jabatan') is-invalid @enderror" required>
                                                    <option value="" disabled {{ old('jabatan') ? '' : 'selected' }}>--
                                                        Select Your Position --</option>
                                                    <option value="Board" {{ old('jabatan') === 'Board' ? 'selected' : '' }}>
                                                        Board</option>
                                                    <option value="Executive Management" {{ old('jabatan') === 'Executive Management' ? 'selected' : '' }}>Executive Management</option>
                                                    <option value="Business Managers" {{ old('jabatan') === 'Business Managers' ? 'selected' : '' }}>Business Managers</option>
                                                    <option value="IT Managers" {{ old('jabatan') === 'IT Managers' ? 'selected' : '' }}>IT Managers</option>
                                                    <option value="Assurance Providers" {{ old('jabatan') === 'Assurance Providers' ? 'selected' : '' }}>Assurance Providers</option>
                                                    <option value="Risk Management" {{ old('jabatan') === 'Risk Management' ? 'selected' : '' }}>Risk Management</option>
                                                    <option value="Staff" {{ old('jabatan') === 'Staff' ? 'selected' : '' }}>
                                                        Staff</option>
                                                </select>
                                            </div>
                                            @error('jabatan')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-register">
                                                <i class="fas fa-user-plus me-2"></i>Create Account
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                <div class="register-footer">
                                    <span class="text-muted">Already have an account?</span>
                                    <a href="{{ route('login') }}">Sign in here</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection