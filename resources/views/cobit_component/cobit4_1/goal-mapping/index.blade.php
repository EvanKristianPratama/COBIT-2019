@extends('layouts.app')

@section('title', 'COBIT 4.1 Business Goals & IT Goals')

@section('content')
<style>
    .c4gm-box { border: 2px solid #302b2c; background: #fff; overflow-x: auto; }
    .c4gm-table {
        border-collapse: collapse; margin: 0; color: #292526; width: 100%;
        font-family: "Arial Narrow", "Roboto Condensed", Arial, sans-serif; font-size: .98rem;
    }
    .c4gm-table th, .c4gm-table td { border: 1px solid #302b2c; padding: .22rem .5rem; vertical-align: middle; background: #fff; }
    .c4gm-table .c4gm-head th { background: #fff; border: 0; border-bottom: 1px solid #302b2c; font-weight: 700; text-align: left; }
    .c4gm-table .c4gm-persp { font-style: italic; font-weight: 700; text-align: center; line-height: 1.1; width: 130px; }
    .c4gm-table .c4gm-no { font-weight: 700; text-align: center; width: 36px; }
    .c4gm-table .c4gm-text { min-width: 360px; }
    .c4gm-table .c4gm-cell { text-align: center; width: 42px; min-width: 42px; }
    .c4gm-table .c4gm-cell a, .c4gm-table .c4gm-text a { color: #075da8; text-decoration: none; }
    .c4gm-table .c4gm-cell a:hover { text-decoration: underline; }
    .c4gm-table .c4gm-crit { width: 34px; min-width: 34px; text-align: center; font-weight: 600; }
    .c4gm-table th.c4gm-critH {
        height: 120px; width: 34px; padding: 0; position: relative; background: #d9d9e8; border-color: #302b2c;
        vertical-align: bottom;
    }
    .c4gm-table th.c4gm-critH span {
        position: absolute; left: 8px; bottom: 6px; transform-origin: left bottom; transform: rotate(-70deg);
        white-space: nowrap; font-size: .8rem; font-weight: 700; font-style: italic;
    }
    .c4gm-section-title { font-weight: 800; text-transform: uppercase; letter-spacing: .02em; }
    .c4gm-note { font-size: .82rem; }
    .c4gm-table tr:target td { background: #fff7cc; }
</style>

@php
    $critKeys = array_keys($criteria);
    $maxBg = max(1, (int) $businessGoals->flatten()->map(fn ($g) => $g->itGoals->count())->max());
    $maxProc = max(1, (int) $itGoals->map(fn ($g) => $g->processMaps->count())->max());
    $perspectiveLabels = ['Financial' => 'Financial Perspective', 'Customer' => 'Customer Perspective', 'Internal' => 'Internal Perspective', 'Learning and Growth' => 'Learning and Growth Perspective'];
@endphp

<div class="container-fluid py-4 px-lg-5">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
        <div>
            <div class="small text-muted">COBIT 4.1</div>
            <h1 class="h3 mb-1">Business Goals, IT Goals &amp; Process Mapping</h1>
            <p class="text-muted mb-0">Linking business goals to IT goals, and IT goals to IT processes with COBIT information criteria.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('focus-areas.index', ['version' => '4.1']) }}" class="btn btn-outline-secondary btn-sm">Kembali ke COBIT 4.1</a>
        </div>
    </div>

    @unless($ready)
        <div class="alert alert-warning">
            Tabel mapping belum tersedia. Jalankan <code>php artisan migrate</code> atau import
            <code>database/sql/cobit4_goal_mapping_cpanel.sql</code>.
        </div>
    @else
        {{-- ===================== BUSINESS GOALS -> IT GOALS ===================== --}}
        <h2 class="h5 c4gm-section-title mb-2">Linking Business Goals to IT Goals</h2>
        <div class="c4gm-box mb-2">
            <table class="c4gm-table">
                <thead>
                    <tr class="c4gm-head">
                        <th colspan="2"></th>
                        <th class="c4gm-text">Business Goals</th>
                        <th colspan="{{ $maxBg }}">IT Goals</th>
                        <th colspan="{{ count($criteria) }}" class="text-end" style="font-variant: small-caps;">COBIT Information Criteria</th>
                    </tr>
                    <tr>
                        <th colspan="{{ 3 + $maxBg }}" style="border-bottom:0;border-top:0;background:#fff"></th>
                        @foreach($criteria as $label)
                            <th class="c4gm-critH"><span>{{ $label }}</span></th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($businessGoals as $perspective => $goals)
                        @foreach($goals as $goal)
                            <tr>
                                @if($loop->first)
                                    <td class="c4gm-persp" rowspan="{{ $goals->count() }}">{!! str_replace(' Perspective', '<br>Perspective', $perspectiveLabels[$perspective] ?? $perspective . ' Perspective') !!}</td>
                                @endif
                                <td class="c4gm-no">{{ $goal->code }}</td>
                                <td class="c4gm-text">{{ $goal->title }}</td>
                                @for($i = 0; $i < $maxBg; $i++)
                                    @php $it = $goal->itGoals[$i] ?? null; @endphp
                                    <td class="c4gm-cell">
                                        @if($it)<a href="#it-goal-{{ $it->code }}" title="IT Goal {{ $it->code }}">{{ $it->code }}</a>@endif
                                    </td>
                                @endfor
                                @foreach($criteria as $label)
                                    <td class="c4gm-crit"></td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="c4gm-note text-muted mb-5">Angka pada kolom IT Goals merujuk ke nomor IT Goal pada tabel di bawah.</p>

        {{-- ===================== IT GOALS -> PROCESSES ===================== --}}
        <h2 class="h5 c4gm-section-title mb-2">Linking IT Goals to IT Processes</h2>
        <div class="c4gm-box mb-2">
            <table class="c4gm-table">
                <thead>
                    <tr class="c4gm-head">
                        <th colspan="2">IT Goals</th>
                        <th colspan="{{ $maxProc }}" class="text-center">Processes</th>
                        <th colspan="{{ count($criteria) }}" class="text-end" style="font-variant: small-caps;">COBIT Information Criteria</th>
                    </tr>
                    <tr>
                        <th colspan="{{ 2 + $maxProc }}" style="border-bottom:0;border-top:0;background:#fff"></th>
                        @foreach($criteria as $label)
                            <th class="c4gm-critH"><span>{{ $label }}</span></th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($itGoals as $goal)
                        <tr id="it-goal-{{ $goal->code }}">
                            <td class="c4gm-no">{{ $goal->code }}</td>
                            <td class="c4gm-text">{{ $goal->title }}</td>
                            @for($i = 0; $i < $maxProc; $i++)
                                @php $code = $goal->processMaps[$i]->process_code ?? null; @endphp
                                <td class="c4gm-cell" style="width:48px">
                                    @if($code)
                                        @if(isset($processLinks[strtoupper($code)]))
                                            <a href="{{ $processLinks[strtoupper($code)] }}" title="Buka proses {{ $code }}">{{ $code }}</a>
                                        @else
                                            {{ $code }}
                                        @endif
                                    @endif
                                </td>
                            @endfor
                            @foreach($critKeys as $key)
                                <td class="c4gm-crit">{{ $goal->{'criteria_' . $key} }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="c4gm-note text-muted mb-0">P = primary, S = secondary. Sumber: COBIT 4.1 (ITGI).</p>
    @endunless
</div>
@endsection
