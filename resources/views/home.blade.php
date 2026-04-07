@extends('layouts.app')

@section('content')
<div class="container-fluid home-clean-wrap">
  <section class="home-clean-card home-main-card">
    <header class="home-main-head">
      <img src="{{ asset('images/cobitColour.png') }}" alt="COBIT Logo" class="home-brand-logo">
    </header>

    @if ($approvalPending)
      <article class="home-pending-state">
        <span class="home-pending-icon">
          <i class="fas fa-user-clock"></i>
        </span>
        <h1>Selamat Datang, {{ $user->name }}</h1>
        <p>Mohon menunggu approval dari admin.</p>
      </article>
    @endif

    @unless ($approvalPending)
      <div class="row g-3 home-tool-grid">
        @forelse ($modules as $module)
          <div class="col-md-6 col-xl-3">
            <a href="{{ $module['route'] }}" class="home-tool-link">
              <article class="home-tool-item">
                <span class="home-tool-icon {{ $module['icon_class'] }}"><i class="{{ $module['icon'] }}"></i></span>
                <div>
                  <h2>{{ $module['title'] }}</h2>
                  <p>{{ $module['description'] }}</p>
                </div>
              </article>
            </a>
          </div>
        @empty
          <div class="col-12">
            <article class="home-empty-state">
              <h2>Tidak ada modul aktif</h2>
              <p>Hubungi admin untuk assignment akses modul pada akun ini.</p>
            </article>
          </div>
        @endforelse
      </div>

      <footer class="home-support-row">

        <span class="text-muted fw-bold">
          {{ $user->displayOrganizationSummary() ?: 'Nama Organisasi' }}
        </span>
      </footer>
    @endunless
  </section>
</div>

@if ($approvalPending)
  <div class="modal fade" id="approvalPendingModal" tabindex="-1" aria-labelledby="approvalPendingModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold" id="approvalPendingModalLabel">Menunggu Persetujuan Admin</h5>
        </div>
        <div class="modal-body pt-2">
          <div class="d-flex align-items-start gap-3">
            <span class="approval-modal-icon">
              <i class="fas fa-user-clock"></i>
            </span>
            <div>
              <p class="mb-2 fw-semibold text-dark">Akun Anda berhasil terdaftar melalui Google.</p>
              <p class="mb-0 text-muted">Admin perlu mengatur organisasi, role, dan paket akses sebelum modul dapat digunakan.</p>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary">Logout</button>
          </form>
          <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal">Saya Mengerti</button>
        </div>
      </div>
    </div>
  </div>
@endif

<style>
  .home-clean-wrap {
    --home-primary: #0f2b5c;
    --home-border: #e5e7eb;
    --home-text: #111827;
    --home-muted: #6b7280;
    --home-card-shadow: 0 14px 35px rgba(15, 43, 92, 0.08);
    padding-top: 0.5rem;
    padding-bottom: 1.5rem;
  }

  .home-clean-card {
    background: #fff;
    border: 1px solid var(--home-border);
    border-radius: 20px;
    box-shadow: var(--home-card-shadow);
  }

  .home-main-card {
    padding: 1.5rem;
  }

  .home-main-head {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.25rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--home-border);
  }

  .home-brand-logo {
    height: 52px;
    width: auto;
    object-fit: contain;
  }

  .home-main-title {
    margin: 0;
    font-size: 1.55rem;
    font-weight: 800;
    color: var(--home-text);
    letter-spacing: .01em;
  }

  .home-main-subtitle {
    margin: 0.25rem 0 0;
    color: var(--home-muted);
    font-size: .95rem;
  }

  .home-tool-grid {
    margin-bottom: 1.25rem;
  }

  .home-tool-link {
    text-decoration: none;
    color: inherit;
    display: block;
    height: 100%;
  }

  .home-tool-item {
    height: 100%;
    border: 1px solid var(--home-border);
    border-radius: 16px;
    padding: 1rem;
    display: flex;
    gap: .9rem;
    align-items: flex-start;
    transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    background: #fff;
  }

  .home-tool-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 26px rgba(15, 43, 92, 0.1);
    border-color: #cbd5e1;
  }

  .home-tool-item h2 {
    font-size: .95rem;
    line-height: 1.35;
    margin: 0;
    color: var(--home-text);
    font-weight: 700;
  }

  .home-tool-item p {
    margin: .35rem 0 0;
    color: var(--home-muted);
    font-size: .84rem;
  }

  .home-tool-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex: 0 0 auto;
    color: #111827;
  }

  .bg-soft-amber { background: #fef3c7; }
  .bg-soft-red { background: #fee2e2; }
  .bg-soft-blue { background: #dbeafe; }
  .bg-soft-green { background: #dcfce7; }

  .home-support-row {
    border-top: 1px solid var(--home-border);
    padding-top: 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    flex-wrap: wrap;
    color: var(--home-muted);
  }

  .home-empty-state {
    border: 1px dashed #cbd5e1;
    border-radius: 16px;
    padding: 1.5rem;
    text-align: center;
    background: #f8fafc;
  }

  .home-empty-state h2 {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    color: var(--home-text);
  }

  .home-empty-state p {
    margin: .45rem 0 0;
    color: var(--home-muted);
    font-size: .88rem;
  }

  .home-pending-state {
    min-height: 320px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 2rem 1rem;
    color: var(--home-text);
  }

  .home-pending-state h1 {
    margin: 1rem 0 .5rem;
    font-size: 1.75rem;
    font-weight: 800;
  }

  .home-pending-state p {
    margin: 0;
    color: var(--home-muted);
    font-size: 1rem;
  }

  .home-pending-icon {
    width: 72px;
    height: 72px;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #dbeafe;
    color: #0f2b5c;
    font-size: 1.4rem;
  }

  .approval-modal-icon {
    width: 52px;
    height: 52px;
    border-radius: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #dbeafe;
    color: #0f2b5c;
    font-size: 1.15rem;
    flex: 0 0 auto;
  }

  .home-support-btn {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    text-decoration: none;
    background: #16a34a;
    color: #fff;
    border-radius: 999px;
    padding: .55rem 1rem;
    font-weight: 700;
    font-size: .86rem;
  }

  .home-support-btn:hover {
    background: #15803d;
    color: #fff;
  }

  @media (max-width: 767.98px) {
    .home-main-card {
      padding: 1rem;
      border-radius: 16px;
    }

    .home-main-head {
      flex-direction: column;
      align-items: flex-start;
      gap: .7rem;
    }
  }
</style>

@if ($approvalPending)
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const approvalPendingModal = document.getElementById('approvalPendingModal');

      if (approvalPendingModal && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(approvalPendingModal).show();
      }
    });
  </script>
@endif
@endsection
