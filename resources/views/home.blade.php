@extends('layouts.app')

@section('content')
<div class="container-fluid home-clean-wrap">
  <section class="home-clean-card home-main-card">
    <header class="home-main-head">
      <img src="{{ asset('images/cobitColour.png') }}" alt="COBIT Logo" class="home-brand-logo">
      <div>
        <h1 class="home-main-title">Selamat Datang</h1>
        <p class="home-main-subtitle">Pilih modul COBIT 2019 yang ingin Anda gunakan.</p>
      </div>
    </header>

    <div class="row g-3 home-tool-grid">
      <div class="col-md-6 col-xl-3">
        <a href="{{ route('cobit2019.objectives.show', 'APO01') }}" class="home-tool-link">
          <article class="home-tool-item">
            <span class="home-tool-icon bg-soft-amber"><i class="fas fa-puzzle-piece"></i></span>
            <div>
              <h2>COBIT Components</h2>
              <p>Kamus komponen COBIT.</p>
            </div>
          </article>
        </a>
      </div>

      <div class="col-md-6 col-xl-3">
        <a href="{{ route('cobit.home') }}" class="home-tool-link">
          <article class="home-tool-item">
            <span class="home-tool-icon bg-soft-red"><i class="fas fa-cogs"></i></span>
            <div>
              <h2>Design I&T Tailored Governance System</h2>
              <p>Perancangan tata kelola TI.</p>
            </div>
          </article>
        </a>
      </div>

      <div class="col-md-6 col-xl-3">
        <a href="{{ route('assessment-eval.index') }}" class="home-tool-link">
          <article class="home-tool-item">
            <span class="home-tool-icon bg-soft-blue"><i class="fas fa-clipboard-check"></i></span>
            <div>
              <h2>Assessment Maturity & Capability</h2>
              <p>Evaluasi maturity dan capability.</p>
            </div>
          </article>
        </a>
      </div>

      <div class="col-md-6 col-xl-3">
        <a href="{{ route('spreadsheet.index') }}" class="home-tool-link">
          <article class="home-tool-item">
            <span class="home-tool-icon bg-soft-green"><i class="fas fa-table"></i></span>
            <div>
              <h2>Spreadsheet Tools</h2>
              <p>Analisis data format spreadsheet.</p>
            </div>
          </article>
        </a>
      </div>
    </div>

    <footer class="home-support-row">
      <small>Butuh bantuan?</small>
      <a href="https://wa.me/6287779511667?text=Halo%20saya%20ingin%20bertanya%20tentang%20COBIT2019" target="_blank" class="home-support-btn">
        <i class="fab fa-whatsapp"></i>
        WhatsApp Support
      </a>
    </footer>
  </section>
</div>

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
@endsection
