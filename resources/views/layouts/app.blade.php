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
  </head>
  <body class="{{ Route::is('login', 'register') ? 'login' : '' }}">
    <div id="app">
      <!-- Navbar Utama -->
      <nav class="navbar navbar-expand-md navbar-dark bg-primary fixed-top">
        <div class="container">
          <!-- Logo dan Teks PORTAL -->
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
            <h5 class="offcanvas-title" id="sidebarOffcanvasLabel">Sidebar Menu</h5>
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

    <!-- Footer -->
    <footer class="container-fluid text-center py-3 mt-4">
      <p class="mb-1">By: Evan Kristian Pratama</p>
      <p class="mb-0">
        <a href="https://www.linkedin.com/in/evan-pratama-196119271/" target="_blank">LinkedIn</a>
      </p>
      <p class="mb-0">Version: 0.0.0</p>
    </footer>
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