@extends('layouts.app')

@section('content')
<div class="design-toolkit rounded-6">
  {{-- Hero Banner (full width) --}}
  <div class="hero-banner">
    <div class="container">
      <h1>COBIT 2019 : Design I&T Tailored Governance System</h1>
    </div>
  </div>

  {{-- Content --}}
  <div class="container py-4">
    <div class="row justify-content-center">
      <div class="col-lg-10">

        {{-- Tools Row --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h5 class="section-title mb-0">Design Factor List</h5>
          <div class="d-flex gap-2">
            <a href="{{ route('target-capability.edit') }}" class="btn btn-outline-primary">
              Target Capability
            </a>
            <a href="{{ route('target-maturity.index') }}" class="btn btn-outline-primary">
              Target Maturity
            </a>
          </div>
        </div>

        {{-- Alerts --}}
        @if(session('error'))
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        @endif
        @if(session('success'))
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        @endif

        {{-- Assessment List --}}
        @if(! Auth::check())
          <div class="alert alert-warning">
            Silakan login untuk melihat daftar assessment Anda.
          </div>
        @elseif($isGuest)
          <div class="alert alert-light border">
            Guest tidak menampilkan daftar assessment. Silakan register untuk akses penuh.
          </div>
        @else
          <div class="card mb-4">
            <div class="card-body p-0">
              {{-- Admin: only show same organization --}}
              @if(! empty($user->role) && strtolower($user->role) === 'admin')
                @if($assessments_same->isEmpty())
                  <div class="empty-state">
                    <p>Belum ada design factor</p>
                  </div>
                @else
                  <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                      <thead>
                        <tr>
                          <th>NAME</th>
                          <th class="text-end" style="width: 120px">ACTIONS</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($assessments_same as $item)
                        <tr>
                          <td>
                            <div class="fw-semibold">{{ $item->kode_assessment }}</div>
                            <small class="text-muted">{{ $item->instansi }}</small>
                          </td>
                          <td class="text-end">
                            <form method="POST" action="{{ route('assessment.join.store') }}" class="d-inline">
                              @csrf
                              <input type="hidden" name="kode_assessment" value="{{ $item->kode_assessment }}">
                              <button class="btn btn-sm btn-light border">Open</button>
                            </form>
                          </td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                @endif

              @else
                {{-- Regular User View --}}
                @if($assessments->isEmpty())
                  <div class="empty-state">
                    <p>Belum ada design factor</p>
                  </div>
                @else
                  <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                      <thead>
                        <tr>
                          <th>NAME</th>
                          <th class="text-end" style="width: 120px">ACTIONS</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($assessments as $item)
                        <tr>
                          <td>
                            <div class="fw-semibold">{{ $item->kode_assessment }}</div>
                            <small class="text-muted">{{ $item->instansi }}</small>
                          </td>
                          <td class="text-end">
                            <form method="POST" action="{{ route('assessment.join.store') }}" class="d-inline">
                              @csrf
                              <input type="hidden" name="kode_assessment" value="{{ $item->kode_assessment }}">
                              <button class="btn btn-sm btn-light border">Open</button>
                            </form>
                          </td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                @endif
              @endif
            </div>
          </div>
        @endif

        {{-- Action Buttons --}}
        <div class="d-flex gap-3 justify-content-end">
          <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#joinModal">
            Join dengan Kode
          </button>
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
            Buat Baru
          </button>
        </div>

      </div>
    </div>
  </div>

  {{-- Floating Home Button --}}
  <a href="{{ route('home') }}" class="floating-home">
    <i class="fas fa-home"></i>
  </a>
</div>

{{-- Join Modal --}}
<div class="modal fade" id="joinModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title">Join Assessment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('assessment.join.store') }}">
        @csrf
        <div class="modal-body">
          <input type="text" name="kode_assessment" class="form-control form-control-lg" placeholder="Masukkan kode" required autofocus>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="submit" class="btn btn-primary w-100">Join</button>
        </div>
      </form>
    </div>
  </div>
</div>


{{-- Create Assessment Modal --}}
<div class="modal fade" id="createModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title">Buat Assessment Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('assessment.join.store') }}">
        @csrf
        <input type="hidden" name="kode_assessment" value="new">
        <div class="modal-body">
          <div class="mb-3">
            <label for="tahun" class="form-label">Tahun Assessment</label>
            <input type="number" id="tahun" name="tahun" class="form-control form-control-lg" placeholder="YYYY" min="2000" max="2100" value="{{ date('Y') }}" required>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="submit" class="btn btn-primary w-100">Buat Assessment</button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
.design-toolkit {
  min-height: 100vh;
  background: #fff;
  margin: 1rem;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 0 20px rgba(0,0,0,0.05);
}

.hero-banner {
  background: linear-gradient(135deg, #0f2b5c 0%, #1a3a6e 100%);
  color: #fff;
  padding: 2rem 0;
}

.hero-banner h1 {
  font-size: 1.75rem;
  font-weight: 600;
  margin: 0;
}

.section-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1a1a2e;
}

.card {
  border: 1px solid #e9ecef;
  border-radius: 12px;
  box-shadow: none;
  overflow: hidden;
}

.table thead th {
  background: #f8f9fa;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #6c757d;
  border-bottom: 1px solid #e9ecef;
  padding: 1rem 1.25rem;
}

.table tbody td {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #f1f3f4;
}

.table tbody tr:last-child td {
  border-bottom: none;
}

.table tbody tr:hover {
  background: #f8f9fa;
}

.empty-state {
  padding: 4rem 2rem;
  text-align: center;
  color: #adb5bd;
}

.empty-state p {
  margin: 0;
  font-size: 1rem;
}

.btn-outline-primary {
  border-radius: 8px;
  padding: 0.5rem 1.25rem;
  font-weight: 500;
}

.btn-primary {
  border-radius: 8px;
  padding: 0.5rem 1.25rem;
  font-weight: 500;
}

.floating-home {
  position: fixed;
  bottom: 1.5rem;
  right: 1.5rem;
  width: 48px;
  height: 48px;
  background: #fff;
  border: 1px solid #e9ecef;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #1a1a2e;
  text-decoration: none;
  transition: all 0.2s;
  z-index: 1000;
}

.floating-home:hover {
  background: #1a1a2e;
  color: #fff;
  border-color: #1a1a2e;
}

@media (max-width: 768px) {
  .page-title {
    font-size: 1.5rem;
  }
  
  .d-flex.gap-2 {
    flex-direction: column;
  }
  
  .d-flex.gap-3 {
    flex-direction: column;
  }
  
  .d-flex.gap-3 .btn {
    width: 100%;
  }
}
</style>
@endsection
