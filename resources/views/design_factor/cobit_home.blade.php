@extends('layouts.app')

@section('content')
<div class="df-workspace container-fluid py-4">
    <header class="df-page-hero">
        <div>
            <h1>Design I&amp;T Tailored Governance System</h1>
        </div>
        @can('design-factors.view')
            <a class="btn btn-light" href="{{ route('cobit.design-factors.index') }}"><i class="fas fa-list me-2" aria-hidden="true"></i>Design Factor List <span class="badge bg-primary ms-2">{{ $designFactorCount }}</span></a>
        @endcan
    </header>

    @include('design_factor.partials.workspace-context')
    @include('design_factor.partials.workspace-alerts')

    <section class="df-workflow-panel" aria-label="Empat langkah desain tata kelola">
        @include('design_factor.partials.workflow')
    </section>

    @can('design-factors.view')
        <div class="df-tool-links">
            <a href="{{ route('target-capability.edit') }}"><i class="fas fa-bullseye" aria-hidden="true"></i><span>Target Capability</span><i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            <a href="{{ route('target-maturity.index') }}"><i class="fas fa-chart-line" aria-hidden="true"></i><span>Target Maturity</span><i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            <a href="{{ route('roadmap.index') }}"><i class="fas fa-route" aria-hidden="true"></i><span>Roadmap Capability <small>40 GAMO · COBIT Core Model</small></span><i class="fas fa-arrow-right" aria-hidden="true"></i></a>
        </div>
    @else
        <p class="alert alert-light border mt-4">Akses Design Factor mengikuti permission akun Anda.</p>
    @endcan
</div>
@include('design_factor.partials.workspace-styles')
@endsection
