@extends('layouts.app')

@section('title', 'Mapping COBIT 5 ke COBIT 4.1')

@section('content')
@include('cobit_component.shared.partials.mapping-styles')

@php
    $hasFilter = ($filters['q'] ?? '') !== '' || ($filters['gamo'] ?? '') !== '';
    $link = fn (?string $url, string $text, string $title = '') => $url
        ? '<a class="map-link" href="'.e($url).'"'.($title !== '' ? ' title="'.e($title).'"' : '').'>'.e($text).'</a>'
        : e($text);
@endphp

<div class="container-fluid py-4 px-lg-5 map-page">
    @include('cobit_component.shared.partials.mapping-header', ['title' => 'Mapping COBIT 5 ke COBIT 4.1', 'active' => '5to4'])

    <form method="GET" action="{{ route('cobit5.cobit4-mapping') }}" class="map-filter">
        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control form-control-sm map-search" placeholder="Cari kode atau nama, mis. APO01.02 atau PO4.11" aria-label="Cari">
        <select name="gamo" class="form-select form-select-sm map-select" aria-label="GAMO COBIT 5" onchange="this.form.submit()">
            <option value="">Semua GAMO</option>
            @foreach($gamos as $gamo)
                <option value="{{ $gamo->gamo_code }}" @selected(($filters['gamo'] ?? '') === $gamo->gamo_code)>{{ $gamo->gamo_code }} - {{ $gamo->gamo_title }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary btn-sm">Tampilkan</button>
        @if($hasFilter)
            <a href="{{ route('cobit5.cobit4-mapping') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
        @endif
    </form>

    @if($groups->isEmpty() && $unmappedControls->isEmpty())
        <div class="map-empty">Tidak ada mapping yang cocok dengan filter.</div>
    @else
        <div class="map-box">
            <table class="map-table">
                <thead>
                    <tr>
                        <th colspan="2">COBIT 5 Practice</th>
                        <th colspan="2">COBIT 4.1 Control Objective</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groups as $group)
                        <tr class="map-group" id="gamo-{{ $group->code }}">
                            <td colspan="4">{{ $group->code }}<small>{{ $group->title }}</small></td>
                        </tr>
                        @foreach($group->practices as $practice)
                            @php $mappings = $practice->mappings; $span = max($mappings->count(), 1); @endphp
                            @for($i = 0; $i < $span; $i++)
                                @php $mapping = $mappings[$i] ?? null; @endphp
                                <tr>
                                    @if($i === 0)
                                        <td class="map-code" rowspan="{{ $span }}">{!! $link($practice->component_url, $practice->code, 'Buka COBIT 5 '.$practice->code) !!}</td>
                                        <td rowspan="{{ $span }}" style="min-width:220px">
                                            {!! $link($practice->component_url, $practice->title) !!}
                                            @if($practice->review_note)<span class="map-sub">{{ $practice->review_note }}</span>@endif
                                        </td>
                                    @endif
                                    @if($mapping)
                                        <td class="map-code">{!! $link($mapping->component_url, $mapping->cobit41_code, 'Buka COBIT 4.1 '.$mapping->cobit41_code) !!}</td>
                                        <td>
                                            {!! $link($mapping->component_url, $mapping->cobit41_title) !!}
                                            @if($mapping->review_note)<span class="map-sub">{{ $mapping->review_note }}</span>@endif
                                        </td>
                                    @else
                                        <td class="map-code map-muted">—</td>
                                        <td class="map-muted">—</td>
                                    @endif
                                </tr>
                            @endfor
                        @endforeach
                    @endforeach

                    @if($unmappedControls->isNotEmpty())
                        <tr class="map-group" id="unmapped-cobit41">
                            <td colspan="4">COBIT 4.1<small>Control objective tanpa padanan COBIT 5</small></td>
                        </tr>
                        @foreach($unmappedControls as $control)
                            <tr>
                                <td class="map-code map-muted">—</td>
                                <td class="map-muted">—</td>
                                <td class="map-code">{!! $link($control->component_url, $control->code) !!}</td>
                                <td>
                                    {!! $link($control->component_url, $control->title) !!}
                                    <span class="map-sub" style="color:#6c757d">{{ $control->note ?: 'Deleted / tanpa praktik langsung' }}</span>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
