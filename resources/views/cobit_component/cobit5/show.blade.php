@extends('layouts.app')
@section('title', 'COBIT 5 — '.$document['code'])
@push('styles')
    @vite('resources/css/cobit5/document.css')
@endpush

@section('content')
<div class="c5-component container-fluid py-4">
    <header class="c5-toolbar">
        <div>
            <div class="small text-muted mb-1">COBIT 5</div>
            <h1>{{ $document['code'] }} — {{ $document['title'] }}</h1>
        </div>
        <div class="d-flex gap-2">
            @can('design-factors.input')<button type="button" class="btn btn-sm btn-outline-primary" data-c5-overview-edit>Edit</button>@endcan
            <a href="{{ route('cobit5.goal-mapping') }}" class="btn btn-sm btn-outline-primary">Goals &amp; Mapping</a>
            <a href="{{ route('focus-areas.index', ['version' => '5', 'view' => 'cards', 'step' => 2]) }}" class="btn btn-sm btn-outline-secondary">Models</a>
        </div>
        <div class="c5-process-navigation">
            @if($previous)<a href="{{ route('cobit_component.show', ['id' => $previous->objective_id, 'focus_area' => $focusAreaId]) }}" class="btn btn-link btn-sm text-decoration-none">Sebelumnya</a>@endif
            <label for="c5-process-select" class="small text-muted">Proses</label>
            <select id="c5-process-select" class="form-select form-select-sm" onchange="window.location.href = this.value">
                @foreach($allObjectives as $item)
                    <option value="{{ route('cobit_component.show', ['id' => $item->objective_id, 'focus_area' => $focusAreaId]) }}" @selected($item->objective_id === $objective->objective_id)>{{ str_replace('"', '', preg_replace('/\.M\d+(?:\.\d+)?$/', '', $item->objective_id)) }} — {{ trim($item->objective, '"') }}</option>
                @endforeach
            </select>
            @if($next)<a href="{{ route('cobit_component.show', ['id' => $next->objective_id, 'focus_area' => $focusAreaId]) }}" class="btn btn-link btn-sm text-decoration-none">Selanjutnya</a>@endif
        </div>
    </header>

    <nav class="nav nav-tabs c5-navigation" aria-label="COBIT 5 views">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#c5-document" type="button" role="tab" aria-selected="true">Dokumen COBIT 5</button>
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#c5-work-products" type="button" role="tab" aria-selected="false">Master Work Product</button>
        <button class="nav-link" id="master-evidence-tab" data-bs-toggle="tab" data-bs-target="#c5-master-evidence" type="button" role="tab" aria-selected="false">Master Evidence</button>
        @can('design-factors.input')
            <a class="nav-link" href="{{ route('cobit_component.show', ['id' => $objective->objective_id, 'focus_area' => $focusAreaId, 'view' => 'data']) }}">Kelola Data</a>
        @endcan
    </nav>
    <div class="tab-content">
        <div id="c5-document" class="tab-pane fade show active" role="tabpanel">
            <article class="c5-sheet" aria-label="Process description, goals and RACI">
                @include('cobit_component.cobit5.partials.overview')
                @include('cobit_component.cobit5.partials.raci')
            </article>
            <article class="c5-sheet" aria-label="Process practices, inputs, outputs and activities">
                @include('cobit_component.cobit5.partials.practices', ['practices' => array_slice($document['practices'], 0, 2), 'continued' => false])
            </article>
            <article class="c5-sheet" aria-label="Continued practices and related guidance">
                @if(count($document['practices']) > 2)
                    @include('cobit_component.cobit5.partials.practices', ['practices' => array_slice($document['practices'], 2), 'continued' => true])
                @endif
                @include('cobit_component.cobit5.partials.guidance')
            </article>
        </div>
        <div id="c5-work-products" class="tab-pane fade" role="tabpanel">
            @include('cobit_component.cobit5.partials.work-products')
        </div>
        <div id="c5-master-evidence" class="tab-pane fade c5-evidence-panel" role="tabpanel">
            @include('cobit_component.shared.partials.master-evidence-query')
        </div>
    </div>
    @include('cobit_component.cobit5.partials.editor')
</div>
@endsection
