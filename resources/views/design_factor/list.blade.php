@extends('layouts.app')

@section('content')
<div class="df-workspace container-fluid py-4">
    <header class="df-page-hero">
        <div><h1>Design Factor List</h1></div>
        <a class="btn btn-light" href="{{ route('cobit.home') }}"><i class="fas fa-arrow-left me-2" aria-hidden="true"></i>Empat Langkah Desain</a>
    </header>
    @include('design_factor.partials.workspace-context')
    @include('design_factor.partials.workspace-alerts')

    <section class="df-workflow-panel">
        <div class="df-section-heading">
            <div><h2>Daftar Design Factor <span class="badge bg-light text-primary">{{ $assessments->total() }}</span></h2></div>
            @can('design-factors.input')
                @if($organizations->isNotEmpty())
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createDesignFactorModal"><i class="fas fa-plus me-2" aria-hidden="true"></i>Buat Design Factor</button>
                @endif
            @endcan
        </div>
        <form class="row g-3 align-items-end mb-4" method="GET" action="{{ route('cobit.design-factors.index') }}">
            <input type="hidden" name="step" value="{{ $step }}">
            <div class="col-md-4"><label class="form-label" for="df-company-filter">Organisasi</label><select class="form-select" name="organization_id" id="df-company-filter"><option value="">Semua perusahaan dipilih</option>@foreach($organizations as $organization)<option value="{{ $organization->organization_id }}" @selected((string) request('organization_id') === (string) $organization->organization_id)>{{ $organization->organization_name }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label" for="df-code-filter">Kode Design Factor</label><input class="form-control" id="df-code-filter" name="kode" value="{{ request('kode') }}" placeholder="Cari kode Design Factor"></div>
            <div class="col-md-2"><button class="btn btn-outline-primary w-100">Tampilkan</button></div>
        </form>
        @include('design_factor.partials.progress-table')
        <div class="mt-3">{{ $assessments->links('pagination::bootstrap-5') }}</div>
    </section>
</div>
@can('design-factors.input')
<div class="modal fade" id="createDesignFactorModal" tabindex="-1" aria-labelledby="createDesignFactorTitle" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><form class="modal-content" method="POST" action="{{ route('assessment.join.store') }}">@csrf<input type="hidden" name="kode_assessment" value="new"><div class="modal-header"><h2 class="modal-title fs-5" id="createDesignFactorTitle">Buat Design Factor</h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Tutup"></button></div><div class="modal-body"><label class="form-label" for="df-create-company">Organisasi</label><select class="form-select" name="organization_id" id="df-create-company" required>@foreach($organizations as $organization)<option value="{{ $organization->organization_id }}">{{ $organization->organization_name }}</option>@endforeach</select><p class="text-muted small mt-3 mb-0">Kode Design Factor dibuat otomatis untuk organisasi yang dipilih.</p></div><div class="modal-footer"><button class="btn btn-primary" type="submit">Buat &amp; Mulai</button></div></form></div></div>
@endcan
@can('design-factors.input')
    @include('design_factor.partials.delete-modal')
@endcan
@include('design_factor.partials.workspace-styles')
@endsection
