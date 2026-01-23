@extends('layouts.app')

@section('content')
    <div class="cobit-layout-container">
        <!-- Modern Toolbar Header -->
        <div class="cobit-header d-flex justify-content-between align-items-center px-4 py-3 shadow-sm text-white">
            <div class="d-flex align-items-center">
                <div class="icon-box me-3">
                    <i class="fas fa-tools fa-lg"></i>
                </div>
                <div>
                    <h2 class="h5 mb-0 fw-bold">COBIT 2019 Tools</h2>
                    <small class="opacity-75" style="letter-spacing: 0.5px;">Design Factor Analysis</small>
                </div>
            </div>
            <a href="{{ route('cobit.home') }}" class="btn btn-link text-white p-2 hover-scale opacity-75 text-decoration-none">
                <i class="fas fa-times me-1"></i> Close
            </a>
        </div>

        <!-- Main Content Area -->
        <div class="cobit-content px-4 py-4">
            @php
                // Logic for user/assessment removed as requested for UI cleanup
                // Only keeping necessary PHP if any (currently none strictly needed for display)
                $user = Auth::user();
                if ($user && $user->jabatan === 'guest') {
                    // Quick alert if needed
                }
            @endphp

            @if ($user && $user->jabatan === 'guest')
                <div class="alert alert-danger shadow-sm border-0 rounded-3 mb-4" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Anda menggunakan akun guest. Semua data rancangan tidak akan disimpan.
                </div>
            @endif

            <div class="content-wrapper">
                @yield('cobit-tools-content')
            </div>
        </div>
    </div>

    <style>
        :root {
            --cobit-gradient: linear-gradient(135deg, #081a3d, #0f2b5c, #1a3d6b);
        }

        /* Modern Layout Overrides */
        .cobit-layout-container {
            background-color: #f8f9fc;
            min-height: calc(100vh - 80px); /* Adjust based on navbar height */
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            margin-top: 2rem;
            margin-bottom: 2rem;
        }

        .cobit-header {
            background: var(--cobit-gradient);
        }

        .icon-box {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(5px);
        }

        .hover-scale {
            transition: all 0.2s ease;
        }
        .hover-scale:hover {
            transform: translateY(-1px);
            opacity: 1 !important;
        }

        .content-wrapper {
            animation: fadeIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
@endsection