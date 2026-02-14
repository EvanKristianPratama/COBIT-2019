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
            --cobit-light: #f8fafc;
            --cobit-gradient: linear-gradient(135deg, #081a3d, #0f2b5c, #1a3d6b);
            --navbar-height: 68px;
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

        .app-navbar {
            background: var(--cobit-gradient) !important;
            border-bottom: 0;
            box-shadow: 0 4px 20px rgba(15, 43, 92, 0.4);
            min-height: var(--navbar-height);
            z-index: 1030;
        }

        .app-navbar .navbar-brand img {
            height: 40px;
            width: auto;
            filter: brightness(0) invert(1);
            transition: transform 0.3s ease;
        }

        .app-navbar .navbar-brand:hover img {
            transform: scale(1.05);
        }

        .app-navbar .navbar-toggler {
            border-color: rgba(255, 255, 255, 0.25);
            box-shadow: none !important;
        }

        .app-navbar .navbar-toggler-icon {
            filter: none;
        }

        .app-nav-link {
            color: rgba(255, 255, 255, 0.92) !important;
            font-weight: 600;
            padding: 0.5rem 0.9rem;
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        .app-nav-link:hover {
            background: rgba(255, 255, 255, 0.12);
            color: #fff !important;
        }

        .top-user-trigger {
            background: #fff;
            border: 1px solid #dbe4ee;
            border-radius: 999px;
            padding: 0.2rem 0.5rem 0.2rem 0.28rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            box-shadow: 0 4px 14px rgba(15, 43, 92, 0.08);
            color: #1f2937;
            transition: all 0.2s ease;
        }

        .top-user-trigger:hover {
            border-color: #c0d1e5;
            box-shadow: 0 6px 18px rgba(15, 43, 92, 0.14);
        }

        .top-user-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1a3d6b, #0f2b5c);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.78rem;
            text-transform: uppercase;
        }

        .top-user-name {
            color: #0f172a;
            font-weight: 600;
            line-height: 1;
            font-size: 0.78rem;
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .breadcrumb-wrapper {
            background: transparent;
            padding: 12px 0 0;
            position: sticky;
            top: var(--navbar-height);
            z-index: 1020;
        }

        .breadcrumb {
            margin-bottom: 0;
            font-size: 0.88rem;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 0.55rem 0.95rem;
            box-shadow: 0 4px 14px rgba(15, 43, 92, 0.05);
            overflow-x: auto;
            white-space: nowrap;
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
            font-weight: 600;
            font-size: 0.9rem;
            color: #ccc;
        }

        .breadcrumb-item.active {
            color: #6c757d;
            font-weight: 500;
        }

        .breadcrumb-item a.active {
            color: var(--cobit-accent) !important;
            font-weight: 700;
        }

        .offcanvas {
            background: var(--cobit-gradient);
            color: #fff;
        }

        .offcanvas-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .offcanvas .nav-link {
            color: rgba(255, 255, 255, 0.95) !important;
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        .offcanvas .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(4px);
        }

        .sidebar-user-meta {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-top: 0.6rem;
        }

        .sidebar-meta-badge {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            font-size: 0.75rem;
            font-weight: 700;
            border-radius: 999px;
            padding: 0.2rem 0.7rem;
        }

        .sidebar-section-label {
            display: inline-block;
            color: rgba(255, 255, 255, 0.75);
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-top: 0.4rem;
            margin-bottom: 0.5rem;
            padding-left: 0.7rem;
        }

        .offcanvas .logout-btn {
            color: #dc3545 !important;
        }

        .main-content {
            padding-top: 2rem;
            padding-bottom: 1.25rem;
            flex: 1;
        }

        .app-footer {
            padding: 0.35rem 0 0.9rem;
            text-align: center;
            color: #94a3b8;
            font-size: 0.72rem;
            letter-spacing: 0.02em;
        }
    </style>
</head>

<body class="{{ Route::is('login', 'register') ? 'login' : '' }}">
    <div id="app">

        <nav class="navbar navbar-expand-md navbar-dark fixed-top app-navbar">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                    <img src="{{ asset('images/logo-divusi.png') }}" alt="Divusi Logo">
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
                                    <a class="nav-link app-nav-link" href="{{ route('login') }}"><i class="fas fa-sign-in-alt me-1"></i>
                                        {{ __('Login') }}</a>
                                </li>
                            @endif
                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link app-nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item">
                                <a class="nav-link p-0" href="#" role="button" data-bs-toggle="offcanvas"
                                    data-bs-target="#sidebarOffcanvas">
                                    <div class="top-user-trigger">
                                        <span class="top-user-avatar">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                        <span class="top-user-name d-none d-lg-inline">{{ Auth::user()->name }}</span>
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

        <footer class="app-footer">
            COBIT 2019 &middot; {{ config('app.version', '1.5.2') }}
        </footer>

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
                        <div class="sidebar-user-meta">
                            <span class="sidebar-meta-badge">{{ Auth::user()->organisasi ?? 'Organisasi' }}</span>
                            <span class="sidebar-meta-badge">{{ Auth::user()->jabatan ?? 'Jabatan' }}</span>
                        </div>
                    </div>
                    <hr class="border-light">
                    <ul class="nav flex-column gap-1">
                        <li class="nav-item">
                            <span class="sidebar-section-label">Navigasi</span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('home') }}">
                                <i class="fas fa-home me-2"></i> Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('cobit2019.objectives.show', 'APO01') }}">
                                <i class="fas fa-book me-2"></i> COBIT Components
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('cobit.home') }}">
                                <i class="fas fa-tools me-2"></i> Design Factor
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('assessment-eval.index') }}">
                                <i class="fas fa-clipboard-check me-2"></i> Assessment
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('spreadsheet.index') }}">
                                <i class="fas fa-table me-2"></i> Spreadsheet Tools
                            </a>
                        </li>

                        @if(in_array(Auth::user()->role, ['admin','pic']))
                            <li class="nav-item mt-2">
                                <span class="sidebar-section-label">Admin Menu</span>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.assessments.index') }}">
                                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                                </a>
                            </li>
                            @if(Auth::user()->role === 'admin')
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('admin.users.index') }}">
                                        <i class="fas fa-users me-2"></i> Manage Users
                                    </a>
                                </li>
                            @endif
                        @endif

                        <li class="nav-item mt-2">
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
