@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
    {{-- Hero Header --}}
    <div class="card shadow-sm mb-4 hero-card" style="border:none;box-shadow:0 22px 45px rgba(14,33,70,0.15);">
        <div class="card-header hero-header py-4" style="background:linear-gradient(135deg,#081a3d,#0f2b5c);color:#fff;border:none;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="hero-title" style="font-size:1.5rem;font-weight:700;letter-spacing:0.04em;">Roadmap Summary Report</div>
                    <div class="hero-subtitle" style="font-size:1.05rem;font-weight:400;margin-top:0.25rem;color:rgba(255,255,255,0.85);">
                        Consolidated view of all capability targets
                    </div>
                </div>
                <div>
                    <a href="{{ route('roadmap.index') }}" class="btn btn-light btn-sm rounded-pill px-3">
                        <i class="fas fa-edit me-2"></i>Back to Editor
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive table-wrapper-scroll-y">
                <table class="table table-sm table-bordered table-striped table-hover roadmap-table align-middle mb-0">
                    <thead class="text-center">
                        <tr>
                            <th rowspan="2" class="sticky-col" style="width: 100px;">GAMO</th>
                            <th rowspan="2" class="sticky-col" style="left: 100px;">Description</th>
                            @foreach($years as $year)
                                <th colspan="2" class="year-header">
                                    {{ $year }}
                                </th>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($years as $year)
                                <th style="width: 60px;">Level</th>
                                <th style="width: 80px;">Rating</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($objectives as $idx => $obj)
                        <tr>
                            <td class="sticky-col text-center fw-bold bg-white">{{ $obj->objective_id }}</td>
                            <td class="sticky-col bg-white small text-muted" style="left: 100px; min-width: 250px;">
                                {{ $obj->objective }}
                            </td>
                            @foreach($years as $year)
                                @php
                                    $data = $mappedRoadmaps[$obj->objective_id][$year] ?? ['level' => null, 'rating' => ''];
                                @endphp
                                <td class="text-center fw-bold {{ $data['level'] !== null ? 'bg-light' : '' }}">
                                    {{ $data['level'] ?? '-' }}
                                </td>
                                <td class="text-center {{ $data['rating'] ? 'bg-light' : '' }}">
                                    @if($data['rating'])
                                        {{ $data['rating'] }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .table-wrapper-scroll-y {
        max-height: 80vh;
        overflow: auto;
    }

    /* Support sticky headers with Bootstrap table-bordered */
    .roadmap-table {
        border-collapse: separate !important;
        border-spacing: 0;
    }

    .roadmap-table thead th {
        position: sticky;
        top: 0;
        z-index: 40 !important;
        background-color: #f8f9fa !important;
        border: 1px solid #dee2e6 !important;
    }

    /* Secondary header row */
    .roadmap-table thead tr:nth-child(2) th {
        top: 33px;
        z-index: 40 !important;
    }

    .sticky-col {
        position: sticky;
        left: 0;
        z-index: 20;
        background-color: #fff !important;
        border-right: 1px solid #dee2e6 !important;
    }

    thead th.sticky-col {
        z-index: 50 !important;
        top: 0;
    }
    
    thead tr:nth-child(2) th.sticky-col {
        top: 33px;
    }
    
    .year-header {
        font-weight: bold;
    }

    .badge {
        padding: 0.4em 0.8em;
    }
</style>
@endsection
