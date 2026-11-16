@extends('layouts.app')

@section('title', 'COBIT 2019 Goals Cascade')

@section('content')
<style>
    .c19gc-box { border: 1px solid #221f20; background: #fff; overflow-x: auto; margin-bottom: .5rem; }
    .c19gc-table {
        border-collapse: collapse; margin: 0; width: 100%; color: #222;
        font-family: Arial, "Helvetica Neue", sans-serif; font-size: .88rem;
    }
    .c19gc-table th, .c19gc-table td { border: 1px solid #221f20; padding: .25rem .5rem; vertical-align: top; background: #fff; }
    .c19gc-table .c19gc-title th { background: #802741; color: #fff; text-align: center; font-size: 1.02rem; padding: .35rem; vertical-align: middle; }
    .c19gc-table .c19gc-sub th { background: #b0b7d2; font-weight: 700; text-align: center; vertical-align: middle; }
    .c19gc-table .c19gc-ref { font-weight: 700; white-space: nowrap; width: 80px; }
    .c19gc-table .c19gc-dim { width: 120px; }
    .c19gc-table .c19gc-goal { width: 28%; }
    .c19gc-table ul { margin: 0; padding-left: 1.05rem; }
    .c19gc-table ul li::marker { font-size: .8em; }

    /* matrices */
    .c19gc-matrix th, .c19gc-matrix td { vertical-align: middle; }
    .c19gc-matrix .c19gc-colh { text-align: center; font-weight: 700; padding: .2rem .15rem 0; border-bottom: 0; min-width: 78px; }
    .c19gc-matrix .c19gc-colt { text-align: center; font-weight: 400; font-size: .72rem; line-height: 1.1; padding: .15rem .2rem .3rem; border-top: 0; }
    .c19gc-matrix .c19gc-rowh { font-weight: 700; white-space: nowrap; width: 64px; }
    .c19gc-matrix .c19gc-rowt { font-size: .76rem; line-height: 1.15; min-width: 190px; max-width: 260px; }
    .c19gc-matrix .c19gc-rowt a, .c19gc-matrix .c19gc-rowh a { color: inherit; text-decoration: none; }
    .c19gc-matrix .c19gc-rowt a:hover, .c19gc-matrix .c19gc-rowh a:hover { text-decoration: underline; }
    .c19gc-matrix td.c19gc-cell { text-align: center; font-weight: 700; height: 30px; padding: 0; background: #e7e8e8; border-color: #fff; }
    .c19gc-matrix td.c19gc-P { background: #173d7a; color: #fff; }
    .c19gc-matrix td.c19gc-S { background: #b0b7d2; color: #222; }
    .c19gc-matrix.c19gc-tall td.c19gc-cell { height: 38px; }
    .c19gc-note { font-size: .82rem; }
    html { scroll-behavior: smooth; }
</style>

@php
    $egGroups = $enterpriseGoals;
    $agGroups = $alignmentGoals;
@endphp

<div class="container-fluid py-4 px-lg-5">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
        <div>
            <div class="small text-muted">COBIT 2019</div>
            <h1 class="h3 mb-1">Goals Cascade: Enterprise Goals, Alignment Goals &amp; Objectives</h1>
            <p class="text-muted mb-0">Enterprise Goals dan Alignment Goals beserta metrik, lalu pemetaan EG &rarr; AG dan AG &rarr; Governance/Management Objectives.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-start">
            <a href="#eg" class="btn btn-outline-primary btn-sm">Enterprise Goals</a>
            <a href="#ag" class="btn btn-outline-primary btn-sm">Alignment Goals</a>
            <a href="#eg-ag" class="btn btn-outline-primary btn-sm">EG &rarr; AG</a>
            <a href="#ag-obj" class="btn btn-outline-primary btn-sm">AG &rarr; Objectives</a>
        </div>
    </div>

    @unless($ready)
        <div class="alert alert-warning">
            Tabel belum tersedia. Import <code>database/sql/cobit2019_goal_cascade_cpanel.sql</code>.
        </div>
    @else
        {{-- ============ Enterprise Goals and Metrics ============ --}}
        <div id="eg" class="c19gc-box">
            <table class="c19gc-table">
                <thead>
                    <tr class="c19gc-title"><th colspan="4">Goals Cascade: Enterprise Goals and Metrics</th></tr>
                    <tr class="c19gc-sub"><th>Reference</th><th>BSC Dimension</th><th>Enterprise Goal</th><th>Example Metrics</th></tr>
                </thead>
                <tbody>
                    @foreach($egGroups as $goal)
                        <tr id="{{ $goal->code }}">
                            <td class="c19gc-ref">{{ $goal->code }}</td>
                            <td class="c19gc-dim">{{ $goal->dimension }}</td>
                            <td class="c19gc-goal">{{ $goal->title }}</td>
                            <td>
                                <ul>@foreach($goal->metrics as $metric)<li>{{ $metric }}</li>@endforeach</ul>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mb-5"></div>

        {{-- ============ Alignment Goals and Metrics ============ --}}
        <div id="ag" class="c19gc-box">
            <table class="c19gc-table">
                <thead>
                    <tr class="c19gc-title"><th colspan="4">Goals Cascade: Alignment Goals and Metrics</th></tr>
                    <tr class="c19gc-sub"><th>Reference</th><th>IT BSC Dimension</th><th>Alignment Goal</th><th>Metrics</th></tr>
                </thead>
                <tbody>
                    @foreach($agGroups as $goal)
                        <tr id="{{ $goal->code }}">
                            <td class="c19gc-ref">{{ $goal->code }}</td>
                            <td class="c19gc-dim">{{ $goal->dimension }}</td>
                            <td class="c19gc-goal">{{ $goal->title }}</td>
                            <td>
                                <ul>@foreach($goal->metrics as $metric)<li>{{ $metric }}</li>@endforeach</ul>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mb-5"></div>

        {{-- ============ EG -> AG ============ --}}
        <div id="eg-ag" class="c19gc-box">
            <table class="c19gc-table c19gc-matrix c19gc-tall">
                <thead>
                    <tr class="c19gc-title"><th colspan="{{ 2 + $enterpriseGoals->count() }}">Mapping Enterprise Goals and Alignment Goals</th></tr>
                    <tr>
                        <th colspan="2" rowspan="2" style="background:#fff"></th>
                        @foreach($enterpriseGoals as $eg)
                            <th class="c19gc-colh">{{ $eg->code }}</th>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach($enterpriseGoals as $eg)
                            <th class="c19gc-colt">{{ $eg->title }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($alignmentGoals as $ag)
                        <tr>
                            <td class="c19gc-rowh">{{ $ag->code }}</td>
                            <td class="c19gc-rowt">{{ $ag->title }}</td>
                            @foreach($enterpriseGoals as $eg)
                                @php $lv = $egAgMatrix[$ag->code][$eg->code] ?? null; @endphp
                                <td class="c19gc-cell c19gc-{{ $lv ?? '0' }}">{{ $lv }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="c19gc-note text-muted mb-5">P = primary, S = secondary.</p>

        {{-- ============ AG -> Objectives ============ --}}
        <div id="ag-obj" class="c19gc-box">
            <table class="c19gc-table c19gc-matrix">
                <thead>
                    <tr class="c19gc-title"><th colspan="{{ 2 + $alignmentGoals->count() }}">Mapping Governance and Management Objectives to Alignment Goals</th></tr>
                    <tr>
                        <th colspan="2" rowspan="2" style="background:#fff"></th>
                        @foreach($alignmentGoals as $ag)
                            <th class="c19gc-colh">{{ $ag->code }}</th>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach($alignmentGoals as $ag)
                            <th class="c19gc-colt">{{ $ag->title }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($objectives as $obj)
                        <tr>
                            <td class="c19gc-rowh"><a href="{{ $obj->url }}" title="Buka {{ $obj->code }}">{{ $obj->code }}</a></td>
                            <td class="c19gc-rowt"><a href="{{ $obj->url }}">{{ $obj->title }}</a></td>
                            @foreach($alignmentGoals as $ag)
                                @php $lv = $agObjectiveMatrix[$obj->code][$ag->code] ?? null; @endphp
                                <td class="c19gc-cell c19gc-{{ $lv ?? '0' }}">{{ $lv }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="c19gc-note text-muted mb-0">P = primary, S = secondary.</p>
    @endunless
</div>
@endsection
