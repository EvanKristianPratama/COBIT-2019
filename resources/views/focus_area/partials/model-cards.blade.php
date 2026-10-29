<div id="wizardStep1Container" class="wizard-container" @if($selectedView !== 'cards' || $initialStep !== 1) hidden @endif>
    <div class="row g-4">
        <div class="col-md-6 col-xl-3">
            <div class="wizard-model-card model-card-2019">
                <div>
                    <h2 class="model-title">COBIT 2019</h2>
                    <p class="model-desc">Standar tata kelola TI mutakhir dengan pendekatan fleksibel dan modular berbasis Focus Area.</p>
                </div>
                <a class="btn model-cta-btn model-cta-2019" href="{{ route('focus-areas.index', ['view' => 'cards', 'step' => 2, 'version' => '2019']) }}" data-framework="2019">Buka Focus Area</a>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="wizard-model-card model-card-5">
                <div>
                    <h2 class="model-title">COBIT 5</h2>
                    <p class="model-desc">Integrasi lima prinsip tata kelola dan tujuh enabler untuk tata kelola dan manajemen teknologi informasi perusahaan.</p>
                </div>
                <a class="btn model-cta-btn model-cta-5" href="{{ route('focus-areas.index', ['view' => 'cards', 'step' => 2, 'version' => '5']) }}" data-framework="5">Buka COBIT 5</a>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="wizard-model-card model-card-4">
                <div>
                    <h2 class="model-title">COBIT 4.1</h2>
                    <p class="model-desc">Standar audit kontrol internal dan tata kelola TI dengan empat domain: Plan &amp; Organize, Acquire &amp; Implement, Deliver &amp; Support, Monitor &amp; Evaluate.</p>
                </div>
                <a class="btn model-cta-btn model-cta-4" href="{{ route('focus-areas.index', ['view' => 'cards', 'step' => 2, 'version' => '4.1']) }}" data-framework="4.1">Buka COBIT 4.1</a>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <a href="{{ route('cobit.evolution') }}" class="wizard-model-card model-card-evolution">
                <div>
                    <h2 class="model-title">GAMO Evolution</h2>
                    <p class="model-desc">Matriks evolusi objektif yang membandingkan COBIT 4.1, COBIT 5, dan COBIT 2019.</p>
                </div>
                <span class="model-cta-btn model-cta-evolution">Buka GAMO Evolution</span>
            </a>
        </div>
    </div>
</div>
