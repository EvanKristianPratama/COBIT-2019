@extends('layouts.app')

@section('content')
    @php
        $f2019 = $focusAreasByVersion->get('2019', collect());
        $f5 = $focusAreasByVersion->get('5', collect());
        $f4 = $focusAreasByVersion->get('4.1', collect());
        $cobit5Model = $f5->first();
        $cobit4Model = $f4->first();
    @endphp

    <div class="container-fluid py-4 fa-page" data-focus-area-page
        data-selected-version="{{ $selectedVersion }}" data-initial-step="{{ $initialStep }}"
        data-selected-view="{{ $selectedView }}" data-base-url="{{ url('/focus-areas') }}">
        <div id="faNotifWrap" class="fa-notif-wrap" aria-live="polite" aria-atomic="true"></div>

        <div class="fa-hero d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h1>COBIT Model &amp; Focus Area</h1>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="{{ route('cobit.evolution') }}" class="btn fa-hero-action">GAMO Mapping Evolution</a>
                @can('design-factors.input')
                    <button class="btn fa-hero-action fa-hero-action-primary" type="button" onclick="openCreateModal()">
                        Tambah Model / Focus Area
                    </button>
                @endcan
            </div>
        </div>

        <div class="model-view-toolbar">
            <div class="model-view-switch" role="group" aria-label="Tampilan navigasi COBIT">
                <a href="{{ route('focus-areas.index', ['view' => 'map']) }}" data-model-view="map"
                    class="model-view-option {{ $selectedView === 'map' && $initialStep === 1 ? 'is-active' : '' }}"
                    aria-current="{{ $selectedView === 'map' && $initialStep === 1 ? 'page' : 'false' }}">
                    <i class="fas fa-project-diagram" aria-hidden="true"></i> Map View
                </a>
                <a href="{{ route('focus-areas.index', ['view' => 'cards']) }}" data-model-view="cards"
                    class="model-view-option {{ $selectedView === 'cards' || $initialStep === 2 ? 'is-active' : '' }}"
                    aria-current="{{ $selectedView === 'cards' || $initialStep === 2 ? 'page' : 'false' }}">
                    <i class="fas fa-th-large" aria-hidden="true"></i> Card View
                </a>
            </div>
            <p class="model-view-hint"><i class="fas fa-mouse-pointer" aria-hidden="true"></i> Klik bagian peta untuk membuka komponennya.</p>
        </div>

        <div id="modelMapContainer" @if($selectedView !== 'map' || $initialStep !== 1) hidden @endif>
            @include('focus_area.partials.model-map')
        </div>
        @include('focus_area.partials.model-cards')
        @include('focus_area.partials.model-library')
    </div>

    @can('design-factors.input')
        @include('focus_area.modals.focus-area')
    @endcan
@endsection

@push('scripts')
    @vite('resources/js/focus-area/index.js')
@endpush
