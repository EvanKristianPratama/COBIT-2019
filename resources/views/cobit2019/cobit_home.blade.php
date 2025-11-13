@extends('layouts.app')

@section('content')
@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Storage;
    use App\Models\Assessment;

    $user = Auth::user();
    $isGuest = Auth::check() && (strtolower($user->role ?? '') === 'guest' || strtolower($user->jabatan ?? '') === 'guest');

    $requests = Storage::exists('requests.json')
        ? collect(json_decode(Storage::get('requests.json'), true))
        : collect();

    // Default: empty collection(s)
    $assessments = collect();
    $assessments_same = collect();
    $assessments_other = collect();

    // Jika authenticated dan bukan guest => tampilkan assessment yang relevan
    if (Auth::check() && ! $isGuest) {
        $sort = request('sort', 'terbaru');
        $orderDir = $sort === 'terlama' ? 'asc' : 'desc';

        // Khusus admin: dua box — sama organisasi (atas) dan lainnya/semua (bawah)
        if (! empty($user->role) && strtolower($user->role) === 'admin') {
            if (! empty($user->organisasi)) {
                $querySame = Assessment::query()
                    ->where('instansi', 'like', '%' . $user->organisasi . '%');

                $queryOther = Assessment::query()
                    ->where(function ($q) use ($user) {
                        $q->where('instansi', 'not like', '%' . $user->organisasi . '%')
                          ->orWhereNull('instansi')
                          ->orWhere('instansi', '');
                    });

                if (! empty(request('kode'))) {
                    $querySame->where('kode_assessment', 'like', '%' . request('kode') . '%');
                    $queryOther->where('kode_assessment', 'like', '%' . request('kode') . '%');
                }
                if (! empty(request('instansi'))) {
                    $querySame->where('instansi', 'like', '%' . request('instansi') . '%');
                    $queryOther->where('instansi', 'like', '%' . request('instansi') . '%');
                }

                $assessments_same = $querySame->orderBy('created_at', $orderDir)->get();
                $assessments_other = $queryOther->orderBy('created_at', $orderDir)->get();
            } else {
                $assessments_same = collect();
                $queryAll = Assessment::query();

                if (! empty(request('kode'))) {
                    $queryAll->where('kode_assessment', 'like', '%' . request('kode') . '%');
                }
                if (! empty(request('instansi'))) {
                    $queryAll->where('instansi', 'like', '%' . request('instansi') . '%');
                }

                $assessments_other = $queryAll->orderBy('created_at', $orderDir)->get();
            }
        } else {
            // Bukan admin: perilaku lama (PIC lihat semua; organisasi filter; fallback user_id)
            $query = Assessment::query();

            if (! empty($user->role) && in_array(strtolower($user->role), ['pic'])) {
                // PIC lihat semua
            } elseif (! empty($user->organisasi)) {
                $query->where('instansi', 'like', '%' . $user->organisasi . '%');
            } else {
                $query->where('user_id', $user->id);
            }

            if (! empty(request('kode'))) {
                $query->where('kode_assessment', 'like', '%' . request('kode') . '%');
            }
            if (! empty(request('instansi'))) {
                $query->where('instansi', 'like', '%' . request('instansi') . '%');
            }

            $assessments = $query->orderBy('created_at', $orderDir)->get();
        }
    }
@endphp

<div class="container">
  <div class="row g-4 justify-content-center">
    <div class="col-md-9">
      <div class="card shadow-sm rounded-3 overflow-hidden">
        <div class="card-header bg-primary text-white py-3">
          <div class="d-flex align-items-center">
            <h3 class="mb-0 flex-grow-1 text-center text-md-start">COBIT 2019 Design Toolkit</h3>
            <a href="{{ route('home') }}" class="home-btn btn btn-sm ms-3" aria-label="Kembali ke Home">
              <span class="d-flex align-items-center">
                <i class="fas fa-home me-2"></i>
                <span class="fw-semibold">Kembali ke Home</span>
              </span>
            </a>
          </div>
        </div>

        <div class="card-body p-4">
          <div class="mb-3">
            <h5 class="mb-0 text-primary fw-bold">I&T Tailored Governance System Design</h5>
          </div>

          <!-- Alerts -->
          @if(session('error'))
            <div class="alert alert-danger mb-3">{{ session('error') }}</div>
          @endif
          @if(session('success'))
            <div class="alert alert-success mb-3">{{ session('success') }}</div>
          @endif

          {{-- CREATE / QUICK JOIN --}}
          <div class="mb-3">
            <div class="d-flex gap-2 flex-wrap">
              <form method="POST" action="{{ route('assessment.join.store') }}" class="d-inline">
                @csrf
                <input type="hidden" name="kode_assessment" value="new">
                <button type="submit" class="btn btn-primary btn-sm">
                  <i class="fas fa-plus me-1"></i> Buat Design Factor
                </button>
              </form>

              <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="collapse" data-bs-target="#quickJoinBox" aria-expanded="false">
                <i class="fas fa-sign-in-alt me-1"></i> Join Design Factor
              </button>

            </div>

            <div class="collapse mt-2" id="quickJoinBox">
              <div class="card card-body p-3">
                <form method="POST" action="{{ route('assessment.join.store') }}" class="row g-2">
                  @csrf
                  <div class="col-8">
                    <input name="kode_assessment" type="text" class="form-control form-control-sm" placeholder="Masukkan kode assessment" required>
                  </div>
                  <div class="col-4 d-grid">
                    <button class="btn btn-sm btn-primary">Join</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <!-- Daftar assessment -->
          @if(! Auth::check())
            <div class="alert alert-warning">Silakan login untuk melihat daftar assessment Anda.</div>
          @else
            @if($isGuest)
              <div class="alert alert-info">Guest tidak menampilkan daftar assessment. Silakan register untuk melihat daftar assessment Anda.</div>
            @else
              {{-- Jika admin: tampilkan dua box terpisah --}}
              @if(! empty($user->role) && strtolower($user->role) === 'admin')
                <div class="mb-4">
                  <h6 class="mb-2">Assessment — Organisasi Anda ({{ $user->organisasi ?? 'tidak tersedia' }})</h6>
                  <div class="card border-0 mb-3">
                    <div class="card-body p-2">
                      @if($assessments_same->isEmpty())
                        <p class="text-muted mb-0">Tidak ada assessment yang instansinya sesuai organisasi Anda.</p>
                      @else
                        <div class="list-group">
                          @foreach($assessments_same as $assessment)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                              <div>
                                <strong>{{ $assessment->kode_assessment }}</strong><br>
                                <small class="text-secondary">{{ $assessment->instansi }}</small><br>
                                <small class="text-muted">{{ $assessment->created_at->translatedFormat('d M Y, H:i') }}</small>
                              </div>
                              <div class="text-end">
                                <form method="POST" action="{{ route('assessment.join.store') }}" class="d-inline">
                                  @csrf
                                  <input type="hidden" name="kode_assessment" value="{{ $assessment->kode_assessment }}">
                                  <button class="btn btn-sm btn-success"><i class="fas fa-sign-in-alt me-1"></i>Masuk</button>
                                </form>
                              </div>
                            </div>
                          @endforeach
                        </div>
                      @endif
                    </div>
                  </div>
                </div>

                {{-- Accordion untuk "Lainnya / Semua" --}}
                <div class="mb-2">
                  <div class="accordion" id="accordionAssessmentsOther">
                    <div class="accordion-item">
                      <h2 class="accordion-header" id="headingAssessmentsOther">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAssessmentsOther" aria-expanded="false" aria-controls="collapseAssessmentsOther">
                          Assessment — Lainnya / Semua
                          <span class="ms-2 text-muted small">({{ $assessments_other->count() }} item)</span>
                        </button>
                      </h2>
                      <div id="collapseAssessmentsOther" class="accordion-collapse collapse" aria-labelledby="headingAssessmentsOther" data-bs-parent="#accordionAssessmentsOther">
                        <div class="accordion-body p-2">
                          @if($assessments_other->isEmpty())
                            <p class="text-muted mb-0">Tidak ada assessment lain.</p>
                          @else
                            <div class="list-group">
                              @foreach($assessments_other as $assessment)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                  <div>
                                    <strong>{{ $assessment->kode_assessment }}</strong><br>
                                    <small class="text-secondary">{{ $assessment->instansi }}</small><br>
                                    <small class="text-muted">{{ $assessment->created_at->translatedFormat('d M Y, H:i') }}</small>
                                  </div>
                                  <div class="text-end">
                                    <form method="POST" action="{{ route('assessment.join.store') }}" class="d-inline">
                                      @csrf
                                      <input type="hidden" name="kode_assessment" value="{{ $assessment->kode_assessment }}">
                                      <button class="btn btn-sm btn-success"><i class="fas fa-sign-in-alt me-1"></i>Masuk</button>
                                    </form>
                                  </div>
                                </div>
                              @endforeach
                            </div>
                          @endif
                        </div>
                      </div>
                    </div> <!-- /.accordion-item -->
                  </div> <!-- /.accordion -->
                </div>

              @else
                {{-- Non-admin: tampilkan daftar seperti sebelumnya (satu list) --}}
                <h6 class="mb-2">Daftar Assessment yang Anda buat</h6>
                @if($assessments->isEmpty())
                  <p class="text-muted">Belum ada assessment yang Anda buat.</p>
                @else
                  <div class="list-group mb-3">
                    @foreach($assessments as $assessment)
                      <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                          <strong>{{ $assessment->kode_assessment }}</strong><br>
                          <small class="text-secondary">{{ $assessment->instansi }}</small><br>
                          <small class="text-muted">{{ $assessment->created_at->translatedFormat('d M Y, H:i') }}</small>
                        </div>
                        <div class="text-end">
                          <form method="POST" action="{{ route('assessment.join.store') }}" class="d-inline">
                            @csrf
                            <input type="hidden" name="kode_assessment" value="{{ $assessment->kode_assessment }}">
                            <button class="btn btn-sm btn-success"><i class="fas fa-sign-in-alt me-1"></i>Masuk</button>
                          </form>
                        </div>
                      </div>
                    @endforeach
                  </div>
                @endif
              @endif
            @endif
          @endif

        </div>

        <div class="card-footer text-center py-3">
          <small class="text-muted d-block mb-2">Butuh bantuan? Hubungi kami melalui:</small>
          <a href="https://wa.me/6287779511667?text=Halo%20saya%20ingin%20bertanya%20tentang%20COBIT2019" target="_blank" class="btn btn-sm btn-success px-4">
            <i class="fab fa-whatsapp me-2"></i>WhatsApp
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  /* kalender dihapus => gaya terkait dihapus/dirapikan */
  .home-btn {
    --accent: #0d6efd; /* bootstrap primary */
    background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    color: var(--accent);
    border: 1px solid rgba(13,110,253,0.08);
    box-shadow: 0 2px 8px rgba(13,110,253,0.06);
    border-radius: 999px;
    padding: .38rem .9rem;
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    transition: transform .12s ease, box-shadow .12s ease, background .12s ease;
    text-decoration: none;
  }
  .home-btn i.fas { color: var(--accent); }
  .home-btn:hover, .home-btn:focus {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(13,110,253,0.12);
    text-decoration: none;
    background: linear-gradient(180deg, #ffffff 0%, #eef6ff 100%);
  }
  .home-btn:active { transform: translateY(-1px); }
</style>
@endsection
