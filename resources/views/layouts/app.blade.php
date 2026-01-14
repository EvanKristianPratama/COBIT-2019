<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>COBIT 2019</title>

    <link rel="icon" href="{{ asset('images/cobit.png') }}" type="image/png">

    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        :root {
            --cobit-primary: #0f2b5c;
            --cobit-secondary: #1a3d6b;
            --cobit-accent: #0f6ad9;
            --cobit-light: #f4f6f9;
            --cobit-gradient: linear-gradient(135deg, #081a3d, #0f2b5c, #1a3d6b);
            --navbar-height: 80px;
            /* Variabel tinggi navbar */
        }

        body {
            background: var(--cobit-light);
            font-family: 'Nunito', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding-top: var(--navbar-height);
        }

        #app {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* --- Navbar Styling --- */
        .navbar {
            background: var(--cobit-gradient) !important;
            box-shadow: 0 4px 20px rgba(15, 43, 92, 0.4);
            min-height: var(--navbar-height);
            z-index: 1030;
        }

        .navbar-brand img {
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
            transition: transform 0.3s ease;
        }

        .navbar-brand:hover img {
            transform: scale(1.05);
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
        }

        /* Badge Styling */
        .user-badge {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            backdrop-filter: blur(4px);
        }

        /* --- Breadcrumb Styling (Updated) --- */
        .breadcrumb-wrapper {
            background: #ffffff;
            border-bottom: 1px solid #e9ecef;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
            padding: 12px 0;
            position: sticky;
            top: var(--navbar-height);
            z-index: 1020;
        }

        .breadcrumb {
            margin-bottom: 0;
            font-size: 0.9rem;
        }

        .breadcrumb-item a {
            color: var(--cobit-secondary);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color 0.2s;
        }

        .breadcrumb-item a:hover {
            color: var(--cobit-accent);
            text-decoration: none;
        }

        .breadcrumb-item+.breadcrumb-item::before {
            content: "/";
            /* font-family: "Font Awesome 6 Free"; removed to use text slash */
            font-weight: 600;
            font-size: 0.9rem;
            color: #ccc;
            /* padding-top: 3px; removed alignment tweak for icon */
        }

        .breadcrumb-item.active {
            color: #6c757d;
            font-weight: 500;
        }

        .breadcrumb-item a.active {
            color: var(--cobit-accent) !important;
            font-weight: 700;
        }

        /* --- Offcanvas --- */
        .offcanvas {
            background: var(--cobit-gradient);
            color: #fff;
        }

        .offcanvas-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .offcanvas .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        .offcanvas .logout-btn {
            color: #dc3545 !important;
        }

        /* --- Main Content --- */
        .main-content {
            padding-top: 2rem;
            padding-bottom: 3rem;
            flex: 1;
        }

        /* --- Login Page specific --- */
        body.login {
            background: var(--cobit-gradient);
        }

        body.login .breadcrumb-wrapper {
            display: none;
        }

        /* Hide breadcrumb on login */
    </style>
</head>

<body class="{{ Route::is('login', 'register') ? 'login' : '' }}">
    <div id="app">

        <nav class="navbar navbar-expand-md navbar-dark fixed-top">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                    <img src="{{ asset('images/logo.png') }}" alt="COBIT Logo" style="height: 45px;">
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto align-items-center">
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}"><i class="fas fa-sign-in-alt me-1"></i>
                                        {{ __('Login') }}</a>
                                </li>
                            @endif
                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item">
                                <a class="nav-link p-0" href="#" role="button" data-bs-toggle="offcanvas"
                                    data-bs-target="#sidebarOffcanvas">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="d-flex flex-column text-end d-none d-lg-block"
                                            style="line-height: 1.2;">
                                            <small class="text-white-50" style="font-size: 0.75rem;">Selamat Datang,</small>
                                            <span class="fw-bold">{{ Auth::user()->name }}</span>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <span class="user-badge">{{ Auth::user()->organisasi ?? 'Organisasi' }}</span>
                                            <span
                                                class="user-badge bg-warning text-dark border-0">{{ Auth::user()->jabatan ?? 'Jabatan' }}</span>
                                        </div>
                                        <i class="fas fa-bars ms-2 fs-5"></i>
                                    </div>
                                </a>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        @auth
            <div class="breadcrumb-wrapper">
                <div class="container">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home') }}" class="{{ Route::is('home') ? 'active' : '' }}">
                                    <i class="fas fa-home"></i> Home
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('cobit2019.objectives.show', 'APO01') }}"
                                    class="{{ Route::is('cobit2019.*') ? 'active' : '' }}">
                                    <i class="fas fa-book"></i> COBIT Components
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('cobit.home') }}" class="{{ Route::is('cobit.*') ? 'active' : '' }}">
                                    <i class="fas fa-tools"></i> Design I&T Tailored Governance System
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('assessment-eval.index') }}"
                                    class="{{ Route::is('assessment-eval.*') ? 'active' : '' }}">
                                    <i class="fas fa-clipboard-check"></i> Assessment Maturity & Capability
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('spreadsheet.index') }}"
                                    class="{{ Route::is('spreadsheet.*') ? 'active' : '' }}">
                                    <i class="fas fa-table"></i> Spreadsheet Tools
                                </a>
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
        @endauth

        <main class="container-fluid main-content">
            <div class="container">
                @yield('content')
            </div>
        </main>

        @auth
            <div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarOffcanvas"
                aria-labelledby="sidebarOffcanvasLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="sidebarOffcanvasLabel">
                        <i class="fas fa-user-circle me-2"></i> Menu Pengguna
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
                        aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <div class="text-center mb-4">
                        <div class="avatar-circle bg-light text-primary mx-auto mb-2 d-flex align-items-center justify-content-center"
                            style="width: 60px; height: 60px; border-radius: 50%; font-size: 1.5rem; font-weight: bold;">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <h6 class="mb-0">{{ Auth::user()->name }}</h6>
                        <small class="text-white-50">{{ Auth::user()->email }}</small>
                    </div>
                    <hr class="border-light">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-id-card me-2"></i> Profile Saya
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link logout-btn text-danger bg-white rounded" href="{{ route('logout') }}">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        @endauth

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Logout confirmation
            document.querySelectorAll('.logout-btn').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Logout?',
                        text: 'Anda akan keluar dari sesi ini.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#0f2b5c',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, Logout',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('logout-form').submit();
                        }
                    });
                });
            });
        });
    </script>
    @stack('scripts')
</body>

</html>
