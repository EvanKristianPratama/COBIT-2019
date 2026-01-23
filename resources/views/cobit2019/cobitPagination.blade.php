<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@auth
    @php
        // Determine active DF
        $currentDF = 1;
        for ($i = 1; $i <= 10; $i++) {
            if (Route::currentRouteName() == 'df' . $i . '.form') {
                $currentDF = $i;
                break;
            }
        }
        $validasiAktif = session('jabatan_df_middleware_enabled', true);
        $routeName = Route::currentRouteName();
    @endphp

    <!-- Modern Stepper Navigation -->
    <div class="stepper-container d-flex align-items-center justify-content-center mb-4">
        <div class="stepper-scroll d-flex gap-2 p-2 rounded-4 bg-white shadow-sm" style="overflow-x: auto; max-width: 100%;">
            
            {{-- Prev Button --}}
            <a href="{{ $currentDF == 1 ? '#' : route('df' . ($currentDF - 1) . '.form', ['id' => $currentDF - 1]) }}" 
               class="stepper-btn {{ $currentDF == 1 ? 'disabled' : '' }}" title="Previous DF">
                <i class="fas fa-chevron-left"></i>
            </a>

            {{-- Steps Loop --}}
            @for ($i = 1; $i <= 11; $i++)
                @if ($i <= 10)
                    @php $isActive = ($routeName == 'df' . $i . '.form'); @endphp
                    <a href="{{ route('df' . $i . '.form', ['id' => $i]) }}" 
                       class="stepper-item {{ $isActive ? 'active' : '' }}">
                        <span class="step-label">DF {{ $i }}</span>
                    </a>

                    @if ($i == 4)
                        <a href="{{ route('step2.index') }}" 
                           class="stepper-item {{ $routeName == 'step2.index' ? 'active' : '' }}">
                            Step 2
                        </a>
                    @endif

                    @if ($i == 10)
                        <a href="{{ route('step3.index') }}" 
                           class="stepper-item {{ $routeName == 'step3.index' ? 'active' : '' }}">
                            Step 3
                        </a>
                    @endif
                @else
                    {{-- Canvas (Step 4) --}}
                    <a href="{{ route('step4.index') }}" 
                       class="stepper-item {{ $routeName == 'step4.index' ? 'active' : '' }}">
                        Canvas
                    </a>
                @endif
            @endfor

            {{-- Next Button --}}
            <a href="{{ $currentDF == 10 ? '#' : route('df' . ($currentDF + 1) . '.form', ['id' => $currentDF + 1]) }}" 
               class="stepper-btn {{ $currentDF == 10 ? 'disabled' : '' }}" title="Next DF">
                <i class="fas fa-chevron-right"></i>
            </a>
        </div>
    </div>

    <!-- Validation Toggle & Alerts -->
    <div class="d-flex flex-column align-items-center mb-4">
        <form action="{{ route('akses-df.toggle') }}" method="get" class="mb-3">
            <div class="modern-toggle d-flex align-items-center bg-white rounded-pill shadow-sm px-3 py-2 cursor-pointer" 
                 onclick="this.querySelector('input').click()">
                <div class="form-check form-switch m-0 d-flex align-items-center">
                    <input class="form-check-input me-2" type="checkbox" id="validasiJabatanSwitch"
                        name="validasiJabatanSwitch"
                        onchange="this.form.submit()" {{ $validasiAktif ? 'checked' : '' }}
                        style="cursor: pointer;">
                    <label class="form-check-label small fw-semibold text-secondary" for="validasiJabatanSwitch" style="cursor: pointer;">
                        {{ $validasiAktif ? 'Jabatan Validation Active' : 'Validation Disabled (All Access)' }}
                    </label>
                </div>
            </div>
        </form>

        @if(session('show_alert'))
            <div class="alert {{ $validasiAktif ? 'alert-info' : 'alert-warning' }} border-0 shadow-sm rounded-3 py-2 px-3 small d-inline-flex align-items-center">
                <i class="fas {{ $validasiAktif ? 'fa-info-circle' : 'fa-exclamation-triangle' }} me-2"></i>
                <span>
                    @if($validasiAktif)
                        Anda hanya punya akses ke DF sesuai jabatan.
                    @else
                        Anda memiliki akses penuh ke DF 1 sampai 10.
                    @endif
                </span>
                <button type="button" class="btn-close ms-2 small" data-bs-dismiss="alert" aria-label="Close" style="font-size: 0.7em;"></button>
            </div>
        @endif
    </div>

    @if(session('jabatan_warning'))
    <script>
        Swal.fire({
            icon: 'warning',
            title: 'Akses Ditolak',
            text: '{{ session('jabatan_warning') }}',
            confirmButtonText: 'OK',
            confirmButtonColor: '#3085d6'
        });
    </script>
    @endif

    <style>
        .stepper-scroll::-webkit-scrollbar {
            height: 4px;
        }
        .stepper-scroll::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        .stepper-scroll::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 4px;
        }

        .stepper-item {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px 16px;
            background: #f8f9fa;
            border-radius: 20px;
            color: #6c757d;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            white-space: nowrap;
            transition: all 0.2s ease;
            border: 1px solid transparent;
        }

        .stepper-item:hover {
            background: #e9ecef;
            color: #495057;
            transform: translateY(-1px);
        }

        .stepper-item.active {
            background: var(--cobit-gradient);
            color: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .stepper-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background: #f8f9fa;
            border-radius: 50%;
            color: #6c757d;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        
        .stepper-btn:hover:not(.disabled) {
            background: #e9ecef;
            color: #0d6efd;
        }

        .stepper-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        .modern-toggle:hover {
            background-color: #f8f9fa !important;
        }
    </style>
@endauth
