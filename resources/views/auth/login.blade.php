@extends('layouts.app')

@section('content')
<style>
    .auth-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
        background: radial-gradient(circle at top, rgba(15, 106, 217, 0.25), transparent 50%),
            linear-gradient(135deg, #081a3d 0%, #0f2b5c 50%, #1a3d6b 100%);
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

    .hero-panel::after,
    .hero-panel::before {
        content: '';
        position: absolute;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.12);
        filter: blur(0.5px);
    }

    .hero-panel::before {
        width: 180px;
        height: 180px;
        top: -40px;
        right: -40px;
    }

    .hero-panel::after {
        width: 240px;
        height: 240px;
        bottom: -80px;
        left: -60px;
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
        margin: 1rem 0 1.5rem;
        line-height: 1.7;
    }

    .hero-content ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .hero-content li {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 0.9rem;
        font-weight: 500;
    }

    .hero-content li i {
        font-size: 1rem;
        color: #61dafb;
        margin-top: 0.2rem;
    }

    .form-panel {
        padding: 3rem;
        background: #fff;
    }

    .form-heading h3 {
        font-weight: 700;
        color: #0f2b5c;
        margin-bottom: 0.25rem;
    }

    .form-heading p {
        color: #718096;
        margin-bottom: 2rem;
    }

    .form-label {
        font-weight: 600;
        color: #0f2b5c;
        font-size: 0.95rem;
        margin-bottom: 0.4rem;
    }

    .input-icon {
        position: relative;
    }

    .input-icon i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #4a6fa5;
        font-size: 1rem;
    }

    .input-icon .form-control {
        padding-left: 2.75rem;
    }

    .form-control {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 0.85rem 1rem;
        font-size: 1rem;
        transition: all 0.25s ease;
        background: #f8fafc;
    }

    .form-control:focus {
        border-color: #0f6ad9;
        box-shadow: 0 0 0 4px rgba(15, 106, 217, 0.15);
        background: #fff;
        transform: translateY(-1px);
    }

    .form-control::placeholder {
        color: #94a3b8;
    }

    .btn-login {
        background: linear-gradient(135deg, #0f2b5c, #0f6ad9);
        border: none;
        border-radius: 12px;
        padding: 0.95rem;
        font-weight: 700;
        font-size: 1.05rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        box-shadow: 0 12px 30px rgba(15, 106, 217, 0.35);
    }

    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 40px rgba(15, 106, 217, 0.45);
    }

    .btn-google {
        background: #fff;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 0.9rem;
        font-weight: 600;
        color: #0f2b5c;
        transition: all 0.25s ease;
    }

    .btn-google:hover {
        background: #f8fafc;
        border-color: #cbd5e0;
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(15, 106, 217, 0.15);
    }

    .divider {
        display: flex;
        align-items: center;
        text-align: center;
        margin: 1.75rem 0;
    }

    .divider::before,
    .divider::after {
        content: '';
        flex: 1;
        border-bottom: 1px solid #e2e8f0;
    }

    .divider span {
        padding: 0 1rem;
        color: #94a3b8;
        font-weight: 600;
        font-size: 0.85rem;
        letter-spacing: 0.08em;
    }

    .form-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .form-meta a {
        color: #0f6ad9;
        font-weight: 600;
        text-decoration: none;
        font-size: 0.9rem;
    }

    .form-meta a:hover {
        text-decoration: underline;
    }

    .login-footer {
        margin-top: 1.5rem;
        text-align: center;
        color: #6b7280;
    }

    .login-footer a {
        color: #0f6ad9;
        font-weight: 600;
        text-decoration: none;
        margin-left: 0.35rem;
    }

    .login-footer a:hover {
        color: #0c4fb5;
        text-decoration: underline;
    }

    .extra-links {
        text-align: center;
        margin-top: 0.75rem;
    }

    .extra-links a {
        color: #0f2b5c;
        font-weight: 600;
        text-decoration: none;
    }

    .extra-links a:hover {
        color: #0f6ad9;
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

        .form-meta {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<div class="auth-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                <div class="auth-card">
                    <div class="row g-0 align-items-stretch">
                        <div class="col-lg-5 d-none d-lg-flex hero-panel">
                            <div class="hero-content">
                                <span class="badge text-uppercase">COBIT 2019</span>
                                <h2>Insights that guide every decision</h2>
                                <p>
                                    Access dashboards, benchmark progress, and collaborate across teams with a secure COBIT assessment workspace.
                                </p>
                                <ul class="ps-0">
                                    <li><i class="fas fa-check-circle"></i>Comprehensive governance scoring</li>
                                    <li><i class="fas fa-check-circle"></i>Guided remediation roadmap</li>
                                    <li><i class="fas fa-check-circle"></i>Secure multi-role access</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-7 form-panel">
                            <div class="form-heading">
                                <h3>{{ __('Welcome Back') }}</h3>
                                <p>Sign in to continue your COBIT maturity journey</p>
                            </div>
                            <form method="POST" action="{{ route('login') }}">
                                @csrf

                                <div class="mb-4">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope me-2"></i>{{ __('Email Address') }}
                                    </label>
                                    <div class="input-icon">
                                        <i class="fas fa-envelope"></i>
                                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="your.email@example.com">
                                    </div>
                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock me-2"></i>{{ __('Password') }}
                                    </label>
                                    <div class="input-icon">
                                        <i class="fas fa-lock"></i>
                                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="Enter your password">
                                    </div>
                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-meta">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="remember">
                                            {{ __('Remember Me') }}
                                        </label>
                                    </div>
                                    @if (Route::has('password.request'))
                                        <a class="text-decoration-none" href="{{ route('password.request') }}">
                                            {{ __('Forgot Password?') }}
                                        </a>
                                    @endif
                                </div>

                                <div class="d-grid mb-2">
                                    <button type="submit" class="btn btn-login text-white">
                                        <i class="fas fa-sign-in-alt me-2"></i>{{ __('Sign In') }}
                                    </button>
                                </div>

                                <div class="divider">
                                    <span>or continue with</span>
                                </div>

                                <div class="d-grid">
                                    <a href="{{ url('/login/google') }}" class="btn btn-google">
                                        <i class="fab fa-google me-2"></i>Sign in with Google
                                    </a>
                                </div>
                            </form>

                            <div class="login-footer">
                                <span>New to the platform?</span>
                                <a href="{{ route('register') }}">Create an account</a>
                                <div class="extra-links">
                                    <small class="text-muted">or</small>
                                    <a href="{{ route('guest.login') }}" class="ms-1">Continue as guest</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
