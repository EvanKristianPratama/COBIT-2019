@extends('layouts.app')

@section('content')
<div class="design-toolkit">
  <div class="container py-4">
    <div class="row justify-content-center">
      <div class="col-lg-8 col-md-10">

        {{-- Header --}}
        <div class="toolkit-header mb-4">
          <h1 class="toolkit-title">
            <i class="fas fa-cubes me-2"></i>COBIT 2019 Design Toolkit
          </h1>
          <p class="toolkit-subtitle mb-0">I&T Tailored Governance System Design</p>
        </div>

        {{-- Alerts --}}
        @if(session('error'))
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        @endif
        @if(session('success'))
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        @endif

        {{-- Main Actions Card --}}
        <div class="card action-card mb-4">
          <div class="card-body">
            <h5 class="card-title mb-3">
              <i class="fas fa-rocket me-2 text-primary"></i>Mulai Design Factor
            </h5>
            <div class="row g-3">
              <div class="col-sm-6">
                <form method="POST" action="{{ route('assessment.join.store') }}">
                  @csrf
                  <input type="hidden" name="kode_assessment" value="new">
                  <button type="submit" class="btn btn-primary btn-action w-100">
                    <i class="fas fa-plus-circle me-2"></i>Buat Baru
                  </button>
                </form>
              </div>
              <div class="col-sm-6">
                <button class="btn btn-outline-primary btn-action w-100" data-bs-toggle="modal" data-bs-target="#joinModal">
                  <i class="fas fa-sign-in-alt me-2"></i>Join dengan Kode
                </button>
              </div>
            </div>
          </div>
        </div>

        {{-- Tools Card --}}
        <div class="card tools-card mb-4">
          <div class="card-body">
            <h5 class="card-title mb-3">
              <i class="fas fa-tools me-2 text-secondary"></i>Tools
            </h5>
            <div class="d-flex flex-wrap gap-2">
              <a href="{{ route('target-capability.edit') }}" class="btn btn-outline-info btn-sm">
                <i class="fas fa-bullseye me-1"></i>Target Capability
              </a>
              <a href="{{ route('target-maturity.index') }}" class="btn btn-outline-warning btn-sm">
                <i class="fas fa-chart-line me-1"></i>Target Maturity
              </a>
            </div>
          </div>
        </div>

        {{-- Assessment List --}}
        @if(! Auth::check())
          <div class="alert alert-warning">
            <i class="fas fa-info-circle me-2"></i>Silakan login untuk melihat daftar assessment Anda.
          </div>
        @elseif($isGuest)
          <div class="alert alert-info">
            <i class="fas fa-user me-2"></i>Guest tidak menampilkan daftar assessment. Silakan register untuk melihat daftar assessment Anda.
          </div>
        @else
          {{-- Admin View --}}
          @if(! empty($user->role) && strtolower($user->role) === 'admin')
            {{-- Same Organization --}}
            <div class="card assessment-card mb-3">
              <div class="card-header">
                <i class="fas fa-building me-2"></i>Assessment — {{ $user->organisasi ?? 'Organisasi Anda' }}
              </div>
              <div class="card-body p-0">
                @if($assessments_same->isEmpty())
                  <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <p>Tidak ada assessment di organisasi Anda</p>
                  </div>
                @else
                  <div class="table-responsive">
                    <table class="table table-hover mb-0">
                      <thead>
                        <tr>
                          <th>Kode</th>
                          <th>Instansi</th>
                          <th>Tanggal</th>
                          <th class="text-end">Aksi</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($assessments_same as $item)
                        <tr>
                          <td><code>{{ $item->kode_assessment }}</code></td>
                          <td>{{ $item->instansi }}</td>
                          <td><small class="text-muted">{{ $item->created_at->format('d M Y') }}</small></td>
                          <td class="text-end">
                            <form method="POST" action="{{ route('assessment.join.store') }}" class="d-inline">
                              @csrf
                              <input type="hidden" name="kode_assessment" value="{{ $item->kode_assessment }}">
                              <button class="btn btn-sm btn-success"><i class="fas fa-arrow-right"></i></button>
                            </form>
                          </td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                @endif
              </div>
            </div>

            {{-- Other Organizations --}}
            @if($assessments_other->isNotEmpty())
            <div class="accordion mb-3" id="otherAccordion">
              <div class="accordion-item">
                <h2 class="accordion-header">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#otherCollapse">
                    <i class="fas fa-globe me-2"></i>Assessment Lainnya <span class="badge bg-secondary ms-2">{{ $assessments_other->count() }}</span>
                  </button>
                </h2>
                <div id="otherCollapse" class="accordion-collapse collapse" data-bs-parent="#otherAccordion">
                  <div class="accordion-body p-0">
                    <div class="table-responsive">
                      <table class="table table-hover mb-0">
                        <thead>
                          <tr>
                            <th>Kode</th>
                            <th>Instansi</th>
                            <th>Tanggal</th>
                            <th class="text-end">Aksi</th>
                          </tr>
                        </thead>
                        <tbody>
                          @foreach($assessments_other as $item)
                          <tr>
                            <td><code>{{ $item->kode_assessment }}</code></td>
                            <td>{{ $item->instansi }}</td>
                            <td><small class="text-muted">{{ $item->created_at->format('d M Y') }}</small></td>
                            <td class="text-end">
                              <form method="POST" action="{{ route('assessment.join.store') }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="kode_assessment" value="{{ $item->kode_assessment }}">
                                <button class="btn btn-sm btn-success"><i class="fas fa-arrow-right"></i></button>
                              </form>
                            </td>
                          </tr>
                          @endforeach
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            @endif

          @else
            {{-- Regular User View --}}
            <div class="card assessment-card mb-3">
              <div class="card-header">
                <i class="fas fa-list me-2"></i>Daftar Assessment Anda
              </div>
              <div class="card-body p-0">
                @if($assessments->isEmpty())
                  <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <p>Belum ada assessment yang Anda buat</p>
                  </div>
                @else
                  <div class="table-responsive">
                    <table class="table table-hover mb-0">
                      <thead>
                        <tr>
                          <th>Kode</th>
                          <th>Instansi</th>
                          <th>Tanggal</th>
                          <th class="text-end">Aksi</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($assessments as $item)
                        <tr>
                          <td><code>{{ $item->kode_assessment }}</code></td>
                          <td>{{ $item->instansi }}</td>
                          <td><small class="text-muted">{{ $item->created_at->format('d M Y') }}</small></td>
                          <td class="text-end">
                            <form method="POST" action="{{ route('assessment.join.store') }}" class="d-inline">
                              @csrf
                              <input type="hidden" name="kode_assessment" value="{{ $item->kode_assessment }}">
                              <button class="btn btn-sm btn-success"><i class="fas fa-arrow-right"></i></button>
                            </form>
                          </td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                @endif
              </div>
            </div>
          @endif
        @endif

        {{-- Help Footer --}}
        <div class="text-center mt-4">
          <small class="text-muted">Butuh bantuan?</small>
          <a href="https://wa.me/6287779511667?text=Halo%20saya%20ingin%20bertanya%20tentang%20COBIT2019" target="_blank" class="btn btn-sm btn-success ms-2">
            <i class="fab fa-whatsapp me-1"></i>WhatsApp
          </a>
        </div>

      </div>
    </div>
  </div>

  {{-- Floating Home Button --}}
  <a href="{{ route('home') }}" class="floating-home" aria-label="Home">
    <i class="fas fa-home"></i>
  </a>
</div>

{{-- Join Modal --}}
<div class="modal fade" id="joinModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title"><i class="fas fa-sign-in-alt me-2"></i>Join Assessment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('assessment.join.store') }}">
        @csrf
        <div class="modal-body">
          <input type="text" name="kode_assessment" class="form-control" placeholder="Masukkan kode assessment" required autofocus>
        </div>
        <div class="modal-footer border-0">
          <button type="submit" class="btn btn-primary w-100">Join</button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
.design-toolkit {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
}

.toolkit-header {
  text-align: center;
  padding: 1.5rem 0;
}

.toolkit-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #0f2b5c;
  margin-bottom: 0.5rem;
}

.toolkit-subtitle {
  color: #5a6a85;
  font-size: 1rem;
}

.card {
  border: none;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
}

.action-card {
  background: linear-gradient(135deg, #fff 0%, #f8faff 100%);
}

.btn-action {
  padding: 0.75rem 1rem;
  font-weight: 500;
  border-radius: 8px;
}

.tools-card .card-title {
  font-size: 0.9rem;
  color: #6c757d;
}

.assessment-card .card-header {
  background: #0f2b5c;
  color: #fff;
  font-weight: 500;
  border-radius: 12px 12px 0 0;
  padding: 0.75rem 1rem;
}

.assessment-card .table thead {
  background: #f8f9fa;
}

.assessment-card .table th {
  font-size: 0.75rem;
  text-transform: uppercase;
  color: #6c757d;
  font-weight: 600;
  border-bottom: none;
}

.assessment-card .table td {
  vertical-align: middle;
  padding: 0.75rem 1rem;
}

.empty-state {
  padding: 3rem 1rem;
  text-align: center;
  color: #adb5bd;
}

.empty-state i {
  font-size: 2.5rem;
  margin-bottom: 0.75rem;
  display: block;
}

.empty-state p {
  margin: 0;
  font-size: 0.9rem;
}

.floating-home {
  position: fixed;
  bottom: 1.5rem;
  right: 1.5rem;
  width: 48px;
  height: 48px;
  background: #fff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #0f2b5c;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
  text-decoration: none;
  transition: transform 0.2s, box-shadow 0.2s;
  z-index: 1000;
}

.floating-home:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
  color: #0f2b5c;
}

.accordion-button:not(.collapsed) {
  background: #f8f9fa;
  color: #333;
}

@media (max-width: 576px) {
  .toolkit-title {
    font-size: 1.35rem;
  }
  
  .btn-action {
    font-size: 0.9rem;
  }
}
</style>
@endsection
