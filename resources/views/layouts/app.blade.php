<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>COBIT 2019</title>
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('images/cobit.png') }}" type="image/png">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    
    <style>
        :root {
            --cobit-primary: #0f2b5c;
            --cobit-secondary: #1a3d6b;
            --cobit-accent: #0f6ad9;
            --cobit-light: #f7f9ff;
            --cobit-gradient: linear-gradient(135deg, #081a3d, #0f2b5c, #1a3d6b);
        }
        
        body {
            background: var(--cobit-light);
            font-family: 'Nunito', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        #app {
            flex: 1;
        }
        
        /* Navbar Styling */
        .navbar {
            background: var(--cobit-gradient) !important;
            box-shadow: 0 4px 20px rgba(15, 43, 92, 0.3);
            padding: 1rem 0;
            border-bottom: 3px solid rgba(15, 106, 217, 0.3);
        }
        
        .navbar-brand {
            transition: transform 0.3s ease;
        }
        
        .navbar-brand:hover {
            transform: translateY(-2px);
        }
        

        
        .navbar-brand img {
            filter: drop-shadow(0 2px 8px rgba(255,255,255,0.3));
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 600;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 8px;
        }
        
        .nav-link:hover {
            color: #fff !important;
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
        }
        
        .badge.bg-warning {
            background: rgba(255, 255, 255, 0.2) !important;
            color: #fff !important;
            font-weight: 600;
            padding: 0.4rem 0.8rem;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        /* Offcanvas Sidebar Styling */
        .offcanvas {
            background: var(--cobit-gradient);
            color: #fff;
        }
        
        .offcanvas-header {
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
            padding: 1.5rem;
        }
        
        .offcanvas-title {
            font-weight: 700;
            color: #fff;
            font-size: 1.25rem;
            letter-spacing: 0.03em;
        }
        
        .offcanvas .btn-close {
            filter: invert(1);
            opacity: 0.8;
        }
        
        .offcanvas .btn-close:hover {
            opacity: 1;
        }
        
        .offcanvas-body {
            padding: 1.5rem;
        }
        
        .offcanvas .nav-link {
            color: rgba(255, 255, 255, 0.85) !important;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .offcanvas .nav-link:hover {
            background: rgba(255, 255, 255, 0.15);
            color: #fff !important;
            transform: translateX(5px);
        }
        
        .offcanvas .nav-link i {
            font-size: 1.1rem;
        }
        
        /* Main Content Styling */
        .container-fluid.mt-5 {
            padding-top: 5rem;
            padding-bottom: 2rem;
            min-height: calc(100vh - 100px);
        }
        
        /* Navbar Toggler */
        .navbar-toggler {
            border-color: rgba(255, 255, 255, 0.3);
        }
        
        .navbar-toggler:focus {
            box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.2);
        }
        
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 1%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }
        
        /* Login/Register Specific Styling */
        body.login {
            background: var(--cobit-gradient);
        }
        
        /* Smooth Animations */
        * {
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .badge.bg-warning {
                font-size: 0.75rem !important;
            }
        }
    </style>
  </head>
  <body class="{{ Route::is('login', 'register') ? 'login' : '' }}">
    <div id="app">
      <!-- Navbar Utama -->
      <nav class="navbar navbar-expand-md navbar-dark fixed-top">
        <div class="container">
          <!-- Logo -->
          <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
            <img src="{{ asset('images/logo.png') }}" alt="COBIT Logo" style="height: 40px;">
          </a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                  data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                  aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Left Side Of Navbar -->
            <ul class="navbar-nav me-auto">
              <!-- Tambahkan link tambahan di sini jika diperlukan -->
            </ul>
            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav ms-auto">
              <!-- Authentication Links -->
              @guest
                @if (Route::has('login'))
                  <li class="nav-item">
                    <a class="nav-link text-white" href="{{ route('login') }}">{{ __('Login') }}</a>
                  </li>
                @endif
                @if (Route::has('register'))
                  <li class="nav-item">
                    <a class="nav-link text-white" href="{{ route('register') }}">{{ __('Register') }}</a>
                  </li>
                @endif
              @else
                <li class="nav-item">
                  <a class="nav-link text-white" href="#" role="button"
                     data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
                    <div class="d-flex align-items-center">
                      <strong class="text-white me-3">Selamat Datang, {{ Auth::user()->name }}</strong>
                      <span class="badge bg-warning me-3" style="font-size: larger;">{{ Auth::user()->organisasi ?? 'Organisasi tidak tersedia' }}</span>
                      <span class="badge bg-warning" style="font-size: larger;">{{ Auth::user()->jabatan ?? 'Jabatan tidak tersedia' }}</span>
                    </div>
                  </a>
                </li>
              @endguest
            </ul>
          </div>
        </div>
      </nav>

      <!-- Offcanvas Sidebar (hanya muncul saat login) -->
      @auth
        <div class="offcanvas offcanvas-start" data-bs-backdrop="static" tabindex="-1" id="sidebarOffcanvas"
             aria-labelledby="sidebarOffcanvasLabel">
          <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="sidebarOffcanvasLabel">
              <i class="fas fa-bars me-2"></i>Menu Navigasi
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
          </div>
          <div class="offcanvas-body">
            <ul class="nav flex-column">
              <li class="nav-item">
                <a class="nav-link" href="#">
                  <i class="fas fa-user-circle me-2"></i> Profile
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link logout-btn" href="{{ route('logout') }}">
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

    <!-- Main Content dengan jarak dari navbar -->
    <div class="container-fluid mt-5 pt-5">
      <div class="row">
        <main class="mx-auto">
          @yield('content')
        </main>
      </div>
    </div>


  </body>
</html>
<!-- SweetAlert dan Script Coming Soon -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Logout confirmation
        document.querySelectorAll('.logout-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Konfirmasi Logout',
                    text: 'Apakah Anda yakin ingin logout?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, logout',
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