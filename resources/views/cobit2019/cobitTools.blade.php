@extends('layouts.app')

@section('content')
    <div class="card border-0 shadow-lg my-4" style="border-radius: 1rem;">
        <!-- Enhanced Header -->
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3"
            style="border-radius: 1rem 1rem 0 0;">
            <div class="d-flex align-items-center">
                <i class="fas fa-tools fa-2x me-3"></i>
                <h2 class="h3 mb-0 fw-light">COBIT 2019 Tools</h2>
            </div>
            <a href="{{ route('cobit.home') }}" class="btn btn-link text-white p-1 hover-scale">
                <i class="fas fa-times fa-lg"></i>
            </a>
        </div>

        <!-- Body Content -->
        <div class="card-body p-4">
            @php
                use App\Models\Assessment;

                $user = Auth::user();
                if ($user && $user->jabatan === 'guest') {
                    $assessmentId = 'Guest';
                    $instansi = 'Guest';
                    $kodeAssessment = '-';
                    $createdAt = '-';
                } else {
                    $assessmentId = session('assessment_id');
                    $instansi = session('instansi');
                    $assessment = \App\Models\Assessment::find($assessmentId);

                    $kodeAssessment = $assessment?->kode_assessment ?? '-';
                    $createdAt = $assessment?->created_at->format('d M Y H:i') ?? '-';
                }
                $userJabatan = $user->jabatan ?? 'Jabatan tidak tersedia';
            @endphp

            @if ($user && $user->jabatan === 'guest')
                <div class="alert alert-danger" role="alert">
                    Anda menggunakan akun guest. Semua data rancangan tidak akan disimpan.
                </div>
            @endif

            <!-- User Info Panel -->
            <div class="user-info-panel bg-light rounded-3 p-4 mb-4 shadow-sm">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-id-badge text-primary me-2 fs-5"></i>
                            <div>
                                <div class="text-muted small">Assessment ID</div>
                                <div class="fw-bold text-dark">{{ $assessmentId }}</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-building text-primary me-2 fs-5"></i>
                            <div>
                                <div class="text-muted small">Instansi</div>
                                <div class="fw-bold text-dark">{{ $instansi }}</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-calendar-alt text-primary me-2 fs-5"></i>
                            <div>
                                <div class="text-muted small">Timestamp Assessment</div>
                                <div class="fw-bold text-dark">{{ $createdAt }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-user-tie text-primary me-2 fs-5"></i>
                            <div>
                                <div class="text-muted small">Pengguna</div>
                                <div class="fw-bold text-dark">{{ $user->name }}</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-briefcase text-primary me-2 fs-5"></i>
                            <div>
                                <div class="text-muted small">Jabatan</div>
                                <div class="fw-bold text-dark">{{ $userJabatan }}</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-key text-primary me-2 fs-5"></i>
                            <div>
                                <div class="text-muted small">Kode Assessment</div>
                                <div class="fw-bold text-dark">{{ $kodeAssessment }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Content Section -->
        <div class="tools-content-section border-top pt-4">
            @yield('cobit-tools-content')
        </div>
    </div>
    </div>

    <style>
        .hover-scale {
            transition: transform 0.2s ease;
        }

        .hover-scale:hover {
            transform: scale(1.1);
        }

        .user-info-panel {
            border: 1px solid rgba(44, 110, 213, 0.15);
        }

        .tools-content-section {
            min-height: 300px;
        }
    </style>
@endsection