@extends('layouts.app')

@section('title', 'COBIT 5 Goals Cascade')

@section('content')
<style>
    .c5gm-box { border: 2px solid #302b2c; background: #fff; overflow-x: auto; margin-bottom: .5rem; }
    .c5gm-table {
        border-collapse: collapse; margin: 0; width: 100%; color: #222;
        font-family: Arial, "Helvetica Neue", sans-serif; font-size: .88rem;
    }
    .c5gm-table th, .c5gm-table td { border: 1px solid #221f20; padding: .2rem .45rem; vertical-align: middle; background: #fff; }
    .c5gm-table .c5gm-title th { background: #951946; color: #fff; text-align: center; font-size: 1rem; padding: .3rem; }
    .c5gm-table .c5gm-sub th { background: #d8dae8; font-weight: 700; text-align: center; }
    .c5gm-table .c5gm-dim { width: 130px; vertical-align: top; }
    .c5gm-table .c5gm-no { text-align: center; width: 44px; white-space: nowrap; }
    .c5gm-table .c5gm-rel { text-align: center; font-weight: 700; width: 92px; }
    .c5gm-table td a { color: #075da8; text-decoration: none; }
    .c5gm-table td a:hover { text-decoration: underline; }

    /* matrix */
    .c5gm-matrix th.c5gm-rot { background: #fff; height: 175px; width: 29px; min-width: 29px; padding: 0; position: relative; border-bottom: 0; }
    .c5gm-matrix th.c5gm-rot > span {
        position: absolute; left: 50%; bottom: 4px; width: 165px; text-align: left;
        transform-origin: left bottom; transform: rotate(-90deg) translateY(50%);
        font-weight: 400; font-size: .72rem; line-height: 1.05; white-space: normal;
    }
    .c5gm-matrix th.c5gm-num { background: #fff; font-weight: 700; text-align: center; padding: .1rem; border-top: 0; }
    .c5gm-matrix th.c5gm-grp { background: #d8dae8; text-align: center; font-weight: 700; }
    .c5gm-matrix td.c5gm-cell { width: 29px; min-width: 29px; text-align: center; padding: 0; height: 34px; border: 2px solid #fff; outline: 1px solid #fff; font-weight: 700; color: #fff; }
    .c5gm-matrix td.c5gm-P { background: #083c79; }
    .c5gm-matrix td.c5gm-S { background: #7482ad; }
    .c5gm-matrix td.c5gm-0 { background: #fff; border: 1px solid #ddd; }
    .c5gm-matrix .c5gm-side { background: #d8dae8; font-weight: 400; writing-mode: vertical-rl; transform: rotate(180deg); text-align: center; width: 28px; }
    .c5gm-matrix .c5gm-label { min-width: 230px; }
    .c5gm-section-nav a { text-decoration: none; }
    .c5gm-note { font-size: .82rem; }
    html { scroll-behavior: smooth; }
</style>

@php
    $dims = ['Financial', 'Customer', 'Internal', 'Learning and Growth'];
    $egByDim = $enterpriseGoals->groupBy('bsc_dimension');
    $itByDim = $itGoals->groupBy('bsc_dimension');
    $pad = fn ($n) => str_pad((string) $n, 2, '0', STR_PAD_LEFT);
    // BSC groups for the column header band of the matrices: [label => colspan]
    $egBand = $egByDim->map->count();
    $itBand = $itByDim->map->count();
@endphp

<div class="container-fluid py-4 px-lg-5">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
        <div>
            <div class="small text-muted">COBIT 5</div>
            <h1 class="h3 mb-1">Enterprise Goals, IT-related Goals &amp; Process (GAMO) Mapping</h1>
            <p class="text-muted mb-0">Goals cascade COBIT 5: Enterprise Goals, IT-related Goals, lalu pemetaan ke proses.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-start c5gm-section-nav">
            <a href="#fig5" class="btn btn-outline-primary btn-sm">Enterprise Goals</a>
            <a href="#fig6" class="btn btn-outline-primary btn-sm">IT-related Goals</a>
            <a href="#fig17" class="btn btn-outline-primary btn-sm">EG &rarr; IT Goals</a>
            <a href="#fig18" class="btn btn-outline-primary btn-sm">IT Goals &rarr; Proses</a>
        </div>
    </div>

    @unless($ready)
        <div class="alert alert-warning">
            Tabel belum tersedia. Import <code>database/sql/cobit5_goal_mapping_cpanel.sql</code>.
        </div>
    @else
        {{-- ============ FIGURE 5 ============ --}}
        <div id="fig5" class="c5gm-box">
            <table class="c5gm-table">
                <thead>
                    <tr class="c5gm-title"><th colspan="6">Figure 5&mdash;COBIT 5 Enterprise Goals</th></tr>
                    <tr class="c5gm-sub">
                        <th rowspan="2" class="c5gm-dim align-bottom">BSC Dimension</th>
                        <th rowspan="2" class="align-bottom">Enterprise Goal</th>
                        <th colspan="3">Relation to Governance Objectives</th>
                    </tr>
                    <tr class="c5gm-sub">
                        <th>Benefits Realisation</th><th>Risk Optimisation</th><th>Resource Optimisation</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dims as $dim)
                        @foreach($egByDim[$dim] ?? [] as $goal)
                            <tr>
                                @if($loop->first)<td class="c5gm-dim" rowspan="{{ $egByDim[$dim]->count() }}">{{ $dim }}</td>@endif
                                <td>{{ $goal->code }}. {{ $goal->title }}</td>
                                <td class="c5gm-rel">{{ $goal->relation_benefits_realisation }}</td>
                                <td class="c5gm-rel">{{ $goal->relation_risk_optimisation }}</td>
                                <td class="c5gm-rel">{{ $goal->relation_resource_optimisation }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="c5gm-note text-muted mb-5">P = primary, S = secondary.</p>

        {{-- ============ FIGURE 6 ============ --}}
        <div id="fig6" class="c5gm-box">
            <table class="c5gm-table">
                <thead>
                    <tr class="c5gm-title"><th colspan="3">Figure 6&mdash;IT-related Goals</th></tr>
                    <tr class="c5gm-sub"><th class="c5gm-dim">IT BSC Dimension</th><th colspan="2">Information and Related Technology Goal</th></tr>
                </thead>
                <tbody>
                    @foreach($dims as $dim)
                        @foreach($itByDim[$dim] ?? [] as $goal)
                            <tr id="it-goal-{{ $goal->code }}">
                                @if($loop->first)<td class="c5gm-dim" rowspan="{{ $itByDim[$dim]->count() }}">{{ $dim }}</td>@endif
                                <td class="c5gm-no">{{ $pad($goal->code) }}</td>
                                <td>{{ $goal->title }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mb-5"></div>

        {{-- ============ FIGURE 17 ============ --}}
        <div id="fig17" class="c5gm-box">
            <table class="c5gm-table c5gm-matrix">
                <thead>
                    <tr class="c5gm-title"><th colspan="{{ 3 + $enterpriseGoals->count() }}">Mapping COBIT 5 Enterprise Goals to IT-related Goals</th></tr>
                    <tr class="c5gm-sub">
                        <th colspan="3" style="background:#fff;border-bottom:0"></th>
                        <th colspan="{{ $enterpriseGoals->count() }}">Enterprise Goal</th>
                    </tr>
                    <tr>
                        <th colspan="3" style="border-top:0"></th>
                        @foreach($enterpriseGoals as $eg)
                            <th class="c5gm-rot"><span>{{ $eg->title }}</span></th>
                        @endforeach
                    </tr>
                    <tr>
                        <th colspan="3" style="border-top:0"></th>
                        @foreach($enterpriseGoals as $eg)
                            <th class="c5gm-num">{{ $eg->code }}.</th>
                        @endforeach
                    </tr>
                    <tr>
                        <th colspan="3" class="c5gm-grp" style="background:#d8dae8">IT-related Goal</th>
                        @foreach($dims as $dim)
                            @if(($egBand[$dim] ?? 0) > 0)
                                <th colspan="{{ $egBand[$dim] }}" class="c5gm-grp">{{ $dim }}</th>
                            @endif
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($dims as $dim)
                        @foreach($itByDim[$dim] ?? [] as $it)
                            <tr>
                                @if($loop->first)<td class="c5gm-side" rowspan="{{ $itByDim[$dim]->count() }}">{{ $dim }}</td>@endif
                                <td class="c5gm-no">{{ $pad($it->code) }}</td>
                                <td class="c5gm-label">{{ $it->title }}</td>
                                @foreach($enterpriseGoals as $eg)
                                    @php $lv = $egMatrix[$it->code][$eg->code] ?? null; @endphp
                                    <td class="c5gm-cell c5gm-{{ $lv ?? '0' }}">{{ $lv }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="c5gm-note text-muted mb-5">P = primary, S = secondary.</p>

        {{-- ============ FIGURE 18 ============ --}}
        <div id="fig18" class="c5gm-box">
            <table class="c5gm-table c5gm-matrix">
                <thead>
                    <tr class="c5gm-title"><th colspan="{{ 3 + $itGoals->count() }}">Figure 18&mdash;Mapping COBIT 5 IT-related Goals to Processes</th></tr>
                    <tr class="c5gm-sub">
                        <th colspan="3" style="background:#fff;border-bottom:0"></th>
                        <th colspan="{{ $itGoals->count() }}">IT-related Goal</th>
                    </tr>
                    <tr>
                        <th colspan="3" style="border-top:0"></th>
                        @foreach($itGoals as $it)
                            <th class="c5gm-rot"><span>{{ $it->title }}</span></th>
                        @endforeach
                    </tr>
                    <tr>
                        <th colspan="3" style="border-top:0"></th>
                        @foreach($itGoals as $it)
                            <th class="c5gm-num">{{ $pad($it->code) }}</th>
                        @endforeach
                    </tr>
                    <tr>
                        <th colspan="3" class="c5gm-grp">COBIT 5 Process</th>
                        @foreach($dims as $dim)
                            @if(($itBand[$dim] ?? 0) > 0)
                                <th colspan="{{ $itBand[$dim] }}" class="c5gm-grp">{{ $dim }}</th>
                            @endif
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($processes as $domainCode => $group)
                        @foreach($group as $proc)
                            <tr>
                                @if($loop->first)<td class="c5gm-side" rowspan="{{ $group->count() }}">{{ $proc->domain_name }}</td>@endif
                                <td class="c5gm-no">
                                    @if(isset($processLinks[$proc->code]))
                                        <a href="{{ $processLinks[$proc->code] }}" title="Buka {{ $proc->code }}">{{ $proc->code }}</a>
                                    @else
                                        {{ $proc->code }}
                                    @endif
                                </td>
                                <td class="c5gm-label">{{ $proc->title }}</td>
                                @foreach($itGoals as $it)
                                    @php $lv = $processMatrix[$proc->id][$it->code] ?? null; @endphp
                                    <td class="c5gm-cell c5gm-{{ $lv ?? '0' }}">{{ $lv }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="c5gm-note text-muted mb-0">P = primary, S = secondary. Sumber: COBIT 5 Framework (ISACA), Figure 5, 6, 17 dan 18.</p>
    @endunless
</div>
@endsection
