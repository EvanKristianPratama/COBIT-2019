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

    // Default: empty collection
    $assessments = collect();

    // Jika authenticated dan bukan guest => tampilkan assessment yang relevan
    if (Auth::check() && ! $isGuest) {
        $query = Assessment::query();
        // Admin/PIC lihat semua assessment
        if (! empty($user->role) && in_array(strtolower($user->role), ['admin','pic'])) {
            // no extra where — admin/pic see all
        } elseif (! empty($user->organisasi)) {
            // Tampilkan semua assessment yg instansi/organisasi nya sesuai user
            $query->where('instansi', 'like', '%' . $user->organisasi . '%');
        } else {
            // fallback: hanya milik user
            $query->where('user_id', $user->id);
        }

        // optional filter by kode/instansi from query string
        if (! empty(request('kode'))) {
            $query->where('kode_assessment', 'like', '%' . request('kode') . '%');
        }
        if (! empty(request('instansi'))) {
            $query->where('instansi', 'like', '%' . request('instansi') . '%');
        }

        $sort = request('sort', 'terbaru');
        $query->orderBy('created_at', $sort === 'terlama' ? 'asc' : 'desc');

        $assessments = $query->get();
    }
@endphp

<div class="container">
  <div class="row g-4 justify-content-center">
    <div class="col-md-8">
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
                  <i class="fas fa-plus me-1"></i> Buat Assessment Baru
                </button>
              </form>

              <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="collapse" data-bs-target="#quickJoinBox" aria-expanded="false">
                <i class="fas fa-sign-in-alt me-1"></i> Quick Join
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

          <!-- Daftar assessment: hanya untuk user biasa (bukan guest) -->
          @if(! Auth::check())
            <div class="alert alert-warning">Silakan login untuk melihat daftar assessment Anda.</div>
          @else
            @if($isGuest)
              <div class="alert alert-info">Guest tidak menampilkan daftar assessment. Silakan register untuk melihat daftar assessment Anda.</div>
            @else
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
                        {{-- Detail dihapus --}}
                      </div>
                    </div>
                  @endforeach
                </div>
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
