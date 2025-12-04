@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <div class="row g-4">
    <!-- Main Content -->
    <div class="col-lg-8">
      <div class="card home-hero-card border-0 rounded-3 overflow-hidden">
        <!-- Card Header -->
        <div class="card-header home-hero-header py-4 position-relative">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="home-hero-title mb-1">{{ __('COBIT 2019') }}</div>
            </div>
          </div>
        </div>

        <!-- Card Body -->
        <div class="card-body p-4">
          <!-- Tools Section -->
          <div class="mb-5">
            <h5 class="fw-bold text-primary text-center mb-4 home-section-title">Pilih Tools</h5>
            <div class="row g-4 justify-content-center">
              <!-- COBIT Component Card -->
              <div class="col-md-6 col-xl-4">
                <div class="card home-tool-card border-0 p-3 h-100">
                  <a href="{{ route('cobit2019.objectives.show', 'APO01') }}" class="text-decoration-none">
                    <div class="card-body text-center p-4">
                      <div class="home-icon-circle bg-warning-light mb-3 mx-auto">
                        <i class="fas fa-puzzle-piece fa-2x text-warning"></i>
                      </div>
                      <h6 class="card-title fw-bold mb-1">COBIT Components</h6>
                      <p class="text-muted small mb-0">Kamus komponen COBIT</p>
                    </div>
                  </a>
                </div>
              </div>
              <!-- COBIT Desain Toolkit Card -->
              <div class="col-md-6 col-xl-4">
                <div class="card home-tool-card border-0 p-3 h-100">
                  <a href="{{ route('cobit.home') }}" class="text-decoration-none">
                    <div class="card-body text-center p-4">
                      <div class="home-icon-circle bg-danger-light mb-3 mx-auto">
                        <i class="fas fa-cogs fa-2x text-danger"></i>
                      </div>
                      <h6 class="card-title fw-bold mb-1">Design I&T Tailored Governance System</h6>
                      <p class="text-muted small mb-0">Manajemen tata kelola TI</p>
                    </div>
                  </a>
                </div>
              </div>
              <!-- Assessment Card -->
              <div class="col-md-6 col-xl-4">
                <div class="card home-tool-card border-0 p-3 h-100">
                  <a href="{{ route('assessment-eval.index') }}" class="text-decoration-none">
                    <div class="card-body text-center p-4">
                      <div class="home-icon-circle bg-info-light mb-3 mx-auto">
                        <i class="fas fa-clipboard-check fa-2x text-info"></i>
                      </div>
                      <h6 class="card-title fw-bold mb-1">Assessment Maturity & Capability</h6>
                      <p class="text-muted small mb-0">Evaluasi tata kelola TI</p>
                    </div>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Card Footer -->
        <div class="card-footer bg-light text-center py-3">
          <small class="text-muted d-block mb-2">Butuh bantuan? Hubungi kami melalui:</small>
          <a href="https://wa.me/6287779511667?text=Halo%20saya%20ingin%20bertanya%20tentang%20COBIT2019"
             target="_blank"
             class="btn btn-success px-4">
            <i class="fab fa-whatsapp me-2"></i>WhatsApp Support
          </a>
        </div>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
      <!-- Jam & Tanggal Card -->
      <div class="card home-sidebar-card border-0 rounded-3 mb-4">
        <div class="card-header home-sidebar-header py-3">
          <h5 class="mb-0 fw-semibold">Jam & Tanggal</h5>
        </div>
        <div class="card-body p-4 text-center">
          <div class="datetime-container bg-light p-3 rounded-3 border">
            <div class="display-4 fw-bold text-primary mb-0" id="current-time"></div>
            <div class="h5 text-secondary mb-1" id="current-day"></div>
            <div class="text-muted" id="current-date"></div>
          </div>
        </div>
      </div>

      {{-- Tindakan: hanya ditampilkan untuk admin atau pic --}}
      @if(in_array(Auth::user()->role, ['admin','pic']))
        <div class="card home-sidebar-card border-0 rounded-3 mb-4">
          <div class="card-header home-sidebar-header py-3">
            <h5 class="mb-0 fw-semibold">Admin</h5>
          </div>
          <div class="card-body text-center">
            <div class="d-grid gap-3">
              <a href="{{ route('admin.assessments.index') }}"
               class="btn btn-outline-primary btn-lg w-100 home-admin-btn">
              <i class="fas fa-tachometer-alt me-1"></i> Dashboard
              </a>

              @if(Auth::user()->role === 'admin')
              <a href="{{ route('admin.users.index') }}"
                 class="btn btn-primary btn-lg w-100 home-admin-btn">
                <i class="fas fa-users me-1"></i> Manage Users
              </a>
              @endif
            </div>
          </div>
        </div>
      @endif
    </div>
  </div>
</div>

<!-- Styles -->
<style>
  :root {
    --home-primary: #0f2b5c;
    --home-accent: #0f6ad9;
    --home-soft: #eef2ff;
    --home-muted: #8b92ab;
  }

  .home-hero-card {
    box-shadow: 0 25px 60px rgba(8, 26, 61, 0.18);
    border: none;
    background: #fff;
  }

  .home-hero-header {
    background: linear-gradient(135deg, #081a3d, #0f2b5c);
    border: none;
    color: #fff;
  }

  .home-hero-title {
    font-size: 1.6rem;
    font-weight: 700;
    letter-spacing: 0.04em;
  }

  .home-hero-subtitle {
    font-size: 0.95rem;
    color: rgba(255,255,255,0.75);
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }

  .home-hero-cta {
    border-radius: 999px;
    font-weight: 600;
    padding-inline: 1.4rem;
  }

  .home-section-title {
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }

  .home-tool-card {
    background: #fdfdff;
    border-radius: 1rem;
    border: 1px solid rgba(15,43,92,0.08);
    box-shadow: 0 18px 40px rgba(15,43,92,0.08);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
  }

  .home-tool-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 25px 55px rgba(15,43,92,0.12);
  }

  .home-icon-circle {
    width: 70px;
    height: 70px;
    border-radius: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.6);
  }

  .bg-warning-light { background-color: #fff3cd; }
  .bg-danger-light { background-color: #fde2e4; }
  .bg-info-light { background-color: #d7efff; }

  .home-sidebar-card {
    box-shadow: 0 20px 45px rgba(8, 26, 61, 0.08);
    border: 1px solid rgba(15,43,92,0.06);
  }

  .home-sidebar-header {
    background: linear-gradient(120deg, #0f2b5c, #183d72);
    color: #fff;
    border: none;
  }

  .home-admin-btn {
    border-radius: 0.65rem;
    font-weight: 600;
    padding-block: 0.85rem;
  }

  .datetime-container {
    background: linear-gradient(135deg, #fdfdfd, #eef2ff);
    border: 1px solid rgba(15,43,92,0.08);
  }

  .home-hero-card .card-footer {
    border-top: 1px solid rgba(15,43,92,0.06);
  }

  .home-hero-card .btn-success {
    border-radius: 999px;
    font-weight: 600;
    padding-inline: 1.75rem;
  }

  @media (max-width: 767px) {
    .home-hero-header {
      text-align: center;
    }

    .home-hero-cta {
      margin-top: 1rem;
      width: 100%;
    }
  }
</style>

<!-- Scripts -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Update time setiap detik
    function updateTime() {
      const now = new Date();
      document.getElementById('current-time').textContent =
        now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
      document.getElementById('current-date').textContent =
        now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
      document.getElementById('current-day').textContent =
        now.toLocaleDateString('id-ID', { weekday: 'long' });
    }
    setInterval(updateTime, 1000);
    updateTime();

    // SweetAlert2 untuk tombol Assessment
    const assessBtn = document.getElementById('assessment-btn');
    if (assessBtn) {
      assessBtn.addEventListener('click', function(e) {
        e.preventDefault();
        Swal.fire({
          icon: 'warning',
          title: 'Under Construction!',
          text: 'Fitur ini sedang dalam pengembangan 💻🔧',
          confirmButtonText: 'OK'
        });
      });
    }
  });
</script>
@endsection
