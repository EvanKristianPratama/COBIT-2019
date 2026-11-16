@extends('layouts.app')

@section('title', 'Mapping COBIT 4.1 ke COBIT 5')

@section('content')
@include('cobit_component.shared.partials.mapping-styles')

@php
    $hasFilter = ($filters['q'] ?? '') !== '' || ($filters['domain'] ?? '') !== '' || ($filters['process'] ?? '') !== '';
    $link = fn (?string $url, string $text, string $title = '') => $url
        ? '<a class="map-link" href="'.e($url).'"'.($title !== '' ? ' title="'.e($title).'"' : '').'>'.e($text).'</a>'
        : e($text);
@endphp

<div class="container-fluid py-4 px-lg-5 map-page">
    @include('cobit_component.shared.partials.mapping-header', ['title' => 'Mapping COBIT 4.1 ke COBIT 5', 'active' => '4to5'])

    <form method="GET" action="{{ route('cobit4.cobit5-mapping') }}" class="map-filter">
        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control form-control-sm map-search" placeholder="Cari kode atau nama, mis. PO1.4 atau Strategic IT Plan" aria-label="Cari">
        <select name="domain" class="form-select form-select-sm map-select" aria-label="Kelompok COBIT 4.1" onchange="this.form.submit()">
            <option value="">Semua kelompok</option>
            @foreach($domains as $domain)
                <option value="{{ $domain }}" @selected(($filters['domain'] ?? '') === $domain)>{{ $domain === 'AC' ? 'Application Controls' : $domain }}</option>
            @endforeach
        </select>
        <select name="process" class="form-select form-select-sm map-select" aria-label="Proses COBIT 4.1" onchange="this.form.submit()">
            <option value="">Semua proses</option>
            @foreach($processes as $domain => $domainProcesses)
                <optgroup label="{{ $domain === 'AC' ? 'Application Controls' : $domain }}">
                    @foreach($domainProcesses as $process)
                        <option value="{{ $process->code }}" @selected(($filters['process'] ?? '') === $process->code)>{{ $process->code === 'AC' ? 'AC1-AC6' : $process->code }}{{ $process->title ? ' - '.$process->title : '' }}</option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary btn-sm">Tampilkan</button>
        @if($hasFilter)
            <a href="{{ route('cobit4.cobit5-mapping') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
        @endif
    </form>

    @if($rows->isEmpty())
        <div class="map-empty">Tidak ada mapping yang cocok dengan filter.</div>
    @else
        <div class="map-box">
            <table class="map-table">
                <thead>
                    <tr>
                        <th colspan="2">COBIT 4.1 Control Objective</th>
                        <th colspan="2">COBIT 5</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        @if($loop->first || $rows[$loop->index - 1]->process_code !== $row->process_code)
                            <tr class="map-group" id="process-{{ $row->process_code }}">
                                <td colspan="4">{{ $row->process_code === 'AC' ? 'AC1-AC6' : $row->process_code }}@if($row->process_title)<small>{{ $row->process_title }}</small>@endif</td>
                            </tr>
                        @endif

                        @php $targets = $row->target_components; $span = max($targets->count(), 1); @endphp
                        @for($i = 0; $i < $span; $i++)
                            @php $target = $targets[$i] ?? null; @endphp
                            <tr>
                                @if($i === 0)
                                    <td class="map-code" rowspan="{{ $span }}">{!! $link($row->source_component_url, $row->source_code, 'Buka COBIT 4.1 '.$row->source_code) !!}</td>
                                    <td rowspan="{{ $span }}" style="min-width:220px">{!! $link($row->source_component_url, $row->source_title) !!}</td>
                                @endif
                                @if($target)
                                    <td class="map-code">{!! $link($target->component_url, $target->code, 'Buka COBIT 5 '.$target->code) !!}</td>
                                    <td>
                                        {!! $link($target->component_url, $target->gamo_title) !!}
                                        @if($i === 0 && $row->review_note)<span class="map-sub">{{ $row->review_note }}</span>@endif
                                    </td>
                                @else
                                    <td class="map-code map-muted">{{ $row->mapping_status === 'deleted' ? 'Deleted' : '—' }}</td>
                                    <td class="map-muted">{{ $row->mapping_note ?: 'Belum ada padanan langsung' }}</td>
                                @endif
                            </tr>
                        @endfor
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
