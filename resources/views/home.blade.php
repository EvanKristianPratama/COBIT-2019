@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <div class="row g-4">
    <!-- Main Content -->
    <div class="col-lg-8">
      <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
        <!-- Card Header -->
        <div class="card-header bg-primary text-white py-4 position-relative">
          <div class="d-flex justify-content-between align-items-center">
            <h3 class="mb-0 fw-semibold">{{ __('COBIT 2019') }}</h3>
            @if(Auth::check() && in_array(Auth::user()->role, ['admin','pic']))
              <a href="{{ route('admin.assessments.index') }}"
                 class="btn btn-outline-light btn-sm shadow-sm ms-3"
                 title="Admin Dashboard">
                <i class="fas fa-tachometer-alt me-1"></i> Dashboard Admin
              </a>
            @endif
          </div>
        </div>

        <!-- Card Body -->
        <div class="card-body p-4">
          <!-- Tools Section -->
          <div class="mb-5">
            <h5 class="fw-bold text-primary text-center mb-4">Pilih Tools</h5>
            <div class="row g-4 justify-content-center">
              <!-- COBIT Component Card -->
              <div class="col-md-6 col-xl-4">
                <div class="card border-0 shadow p-3 h-100 transition-all hover-lift">
                  <a href="{{ route('cobit2019.objectives.show', 'APO01') }}" class="text-decoration-none">
                    <div class="card-body text-center p-4">
                      <div class="icon-circle bg-warning-light mb-3 mx-auto">
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
                <div class="card border-0 shadow p-3 h-100 transition-all hover-lift">
                  <a href="{{ route('cobit.home') }}" class="text-decoration-none">
                    <div class="card-body text-center p-4">
                      <div class="icon-circle bg-danger-light mb-3 mx-auto">
                        <i class="fas fa-cogs fa-2x text-danger"></i>
                      </div>
                      <h6 class="card-title fw-bold mb-1">COBIT Desain Toolkit</h6>
                      <p class="text-muted small mb-0">Manajemen tata kelola TI</p>
                    </div>
                  </a>
                </div>
              </div>
              <!-- Assessment Card -->
              <div class="col-md-6 col-xl-4">
                <div class="card border-0 shadow p-3 h-100 transition-all hover-lift">
                  <a href="{{ route('assessment-eval.index') }}" class="text-decoration-none">
                    <div class="card-body text-center p-4">
                      <div class="icon-circle bg-info-light mb-3 mx-auto">
                        <i class="fas fa-clipboard-check fa-2x text-info"></i>
                      </div>
                      <h6 class="card-title fw-bold mb-1">Assessment</h6>
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
      <div class="card shadow-lg border-0 rounded-3 mb-4">
        <div class="card-header bg-primary text-white py-3">
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
        <div class="card shadow-lg border-0 rounded-3 mb-4">
          <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0 fw-semibold">Admin</h5>
          </div>
          <div class="card-body text-center">
            <div class="d-grid gap-3">
              <a href="{{ route('admin.assessments.index') }}"
               class="btn btn-light btn-lg w-100 shadow-sm">
              <i class="fas fa-tachometer-alt me-1"></i> Dashboard
              </a>

              @if(Auth::user()->role === 'admin')
              <a href="{{ route('admin.users.index') }}"
                 class="btn btn-success btn-lg w-100 shadow-sm">
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
    --primary-light: rgba(13, 110, 253, 0.1);
    --secondary-light: rgba(108, 117, 125, 0.1);
    --success-light: rgba(25, 135, 84, 0.1);
    --info-light: rgba(13, 202, 240, 0.1);
  }
  .card { transition: transform 0.3s ease, box-shadow 0.3s ease; }
  .card.hover-lift:hover { transform: translateY(-5px); box-shadow: 0 1rem 3rem rgba(0,0,0,0.1) !important; }
  .icon-circle { width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
  .bg-primary-light { background-color: var(--primary-light); }
  .bg-secondary-light { background-color: var(--secondary-light); }
  .bg-info-light { background-color: var(--info-light); }
  .bg-success-light { background-color: var(--success-light); }
  .bg-warning-light { background-color: #fff3cd; }
  .bg-danger-light { background-color: #f8d7da; }
  .datetime-container { background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); }
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
