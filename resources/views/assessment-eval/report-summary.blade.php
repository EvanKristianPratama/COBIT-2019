@extends('layouts.app')

@section('content')
    <div class="container">
        @foreach ($objectives as $objective)
            <div class="mb-5">
                {{-- 1. Header Bar --}}
                <div class="d-flex align-items-center mb-2 px-3 py-2 text-white" style="background-color: #0f2b5c;">
                    <div class="fw-bold fs-5">
                        {{ $loop->iteration }}. {{ $objective->objective_id }} - {{ $objective->objective }}
                    </div>
                </div>

                {{-- 2. Max Level Info --}}
                <div class="mb-3 text-secondary fw-bold" style="font-size: 0.9rem;">
                    (Maksimum Level : {{ $objective->max_level }})
                </div>

                <div class="row g-0">
                    {{-- 3. Left Column: Score Card --}}
                    <div class="col-md-3 pe-md-3 mb-3 mb-md-0">
                        <div class="border text-center h-100 d-flex flex-column">
                            {{-- Purple Header --}}
                            <div class="py-3 text-white d-flex flex-column justify-content-center"
                                style="background-color: #9b59b6; min-height: 100px;">
                                <h2 class="m-0 fw-bold display-5">{{ $objective->objective_id }}</h2>
                                <div class="small px-2 mt-1" style="line-height: 1.2;">
                                    {{ $objective->objective }}
                                </div>
                            </div>
                            {{-- Score Body --}}
                            <div
                                class="flex-grow-1 bg-white d-flex align-items-center justify-content-center position-relative py-4">
                                <div class="display-3 fw-bold">
                                    {{ $objective->current_score }} / {{ $objective->max_level }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 4. Right Column: Details --}}
                    <div class="col-md-9">
                        {{-- Tujuan / Purpose Section --}}
                        <div class="d-flex mb-3 border">
                            {{-- Icon Box --}}
                            <div class="d-flex flex-column align-items-center justify-content-center text-white p-3"
                                style="background-color: #0f2b5c; width: 120px; flex-shrink: 0;">
                                <i class="fas fa-bullseye fa-3x mb-2"></i>
                                <div class="fw-bold">Tujuan</div>
                            </div>
                            {{-- Text --}}
                            <div class="p-3 bg-white flex-grow-1 d-flex align-items-center">
                                <p class="m-0 text-secondary" style="font-size: 0.95rem; text-align: justify;">
                                    {{ $objective->objective_purpose ?? ($objective->objective_description ?? 'No description available.') }}
                                </p>
                            </div>
                        </div>

                        {{-- Management Practice Section --}}
                        <div>
                            <div class="text-white text-center py-2 fw-bold" style="background-color: #0f2b5c;">
                                Management Practice
                            </div>
                            <div class="border border-top-0 p-3 bg-white">
                                <div class="row">
                                    @foreach ($objective->practices as $practice)
                                        <div class="col-md-4 mb-2">
                                            <div class="d-flex">

                                                <span class="fw-bold me-1 text-nowrap"
                                                    style="font-size: 0.85rem;">{{ str_replace('"', '', $practice->practice_id) }}</span>
                                                <span class="text-secondary" style="font-size: 0.85rem;">
                                                    {{ Str::limit(str_replace('"', '', $practice->practice_name), 60) }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Detailed Table Section --}}
                <div class="mt-4">
                    <table class="table table-bordered align-middle" style="border-color: #000; border-width: 2px;">
                        <thead>
                            <tr class="text-center">
                                <th class="text-white" style="width: 5%; background-color: #0f2b5c;">Practice</th>
                                <th class="text-white" style="width: 35%; background-color: #0f2b5c;">Kebijakan Pedoman /
                                    Prosedur</th>
                                <th class="text-white" style="width: 25%; background-color: #0f2b5c;">Evidences / Bukti
                                    Pelaksanaan</th>
                                <th class="text-white" style="width: 35%; background-color: #0f2b5c;">Potensi Perbaikan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($objective->practices as $practice)
                                @php
                                    $activityCount = count($practice->activities);
                                @endphp
                                @if ($activityCount > 0)
                                    @foreach ($practice->activities as $index => $activity)
                                        <tr>
                                            {{-- Column 1: Practice (Rowspan) --}}
                                            @if ($index === 0)
                                                <td rowspan="{{ $activityCount }}"
                                                    class="align-middle bg-white text-center" style="width: 1%;">
                                                    <div class="fw-bold text-primary p-2 text-nowrap">
                                                        {{ str_replace('"', '', $practice->practice_id) }}
                                                    </div>
                                                </td>
                                            @endif

                                            {{-- Column 2: Kebijakan / Prosedur (Activity Description) --}}
                                            <td class="align-top">
                                                <div class="small text-justify">{{ $activity->description }}</div>
                                            </td>

                                            {{-- Column 3: Evidence --}}
                                            <td class="align-top">
                                                @if (isset($activity->assessment) && !empty($activity->assessment['evidence']))
                                                    <div class="small text-break">{!! nl2br(e($activity->assessment['evidence'])) !!}</div>
                                                @else
                                                    <div class="text-muted small fst-italic text-center">-</div>
                                                @endif
                                            </td>

                                            {{-- Column 4: Notes (Perbaikan) --}}
                                            <td class="align-top">
                                                <textarea class="form-control form-control-sm bg-white" rows="2" style="resize: vertical; font-size: 0.85rem;">{{ isset($activity->assessment['notes']) ? $activity->assessment['notes'] : '' }}</textarea>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    {{-- Fallback if no activities --}}
                                    <tr>
                                        <td class="align-middle bg-white text-center" style="width: 1%;">
                                            <div class="fw-bold text-primary p-2 text-nowrap">
                                                {{ str_replace('"', '', $practice->practice_id) }}
                                            </div>
                                        </td>
                                        <td colspan="3" class="text-center text-muted small">No activities defined.</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
@endsection
