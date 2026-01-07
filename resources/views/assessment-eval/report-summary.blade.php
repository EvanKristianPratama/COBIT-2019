@extends('layouts.app')

@section('content')
    <div class="container">
        @foreach ($objectives as $objective)
            <div class="mb-5">
                {{-- 1. Header Bar --}}
                <div class="d-flex align-items-center mb-2 px-3 py-2 text-white justify-content-between" style="background-color: #0f2b5c;">
                    <div class="fw-bold fs-5">
                        {{ $loop->iteration }}. {{ $objective->objective_id }} - {{ $objective->objective }}
                    </div>
                    <div>
                        <a href="{{ route('assessment-eval.summary-pdf', ['evalId' => $evaluation->eval_id, 'objectiveId' => $objective->objective_id]) }}" class="btn btn-sm btn-danger text-white fw-bold rounded-pill px-3" target="_blank">
                            <i class="fas fa-file-pdf me-1"></i> Export PDF
                        </a>
                    </div>
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
                        <div class="d-flex mb-2 border">
                            {{-- Icon Box --}}
                            <div class="d-flex flex-column align-items-center justify-content-center text-white p-2"
                                style="background-color: #0f2b5c; width: 90px; flex-shrink: 0;">
                                <i class="fas fa-bullseye fa-2x mb-1"></i>
                                <div class="fw-bold small">Tujuan</div>
                            </div>
                            {{-- Text --}}
                            <div class="p-2 bg-white flex-grow-1 d-flex align-items-center">
                                <p class="m-0 text-secondary" style="font-size: 0.85rem; text-align: justify;">
                                    {{ $objective->objective_purpose ?? ($objective->objective_description ?? 'No description available.') }}
                                </p>
                            </div>
                        </div>

                        {{-- Management Practice Section --}}
                        <div>
                            <div class="text-white text-center py-2 fw-bold" style="background-color: #0f2b5c;">
                                Management Practice
                            </div>
                            <div class="border border-top-0 p-1 bg-white">
                                <div class="d-grid overflow-auto pb-1"
                                    style="grid-template-rows: repeat(5, min-content); grid-auto-flow: column; grid-auto-columns: max-content; gap: 1px 10px;">
                                    @foreach ($objective->practices as $practice)
                                        <div>
                                            <div class="d-flex align-items-start">
                                                <span class="fw-bold me-1 text-primary text-nowrap"
                                                    style="font-size: 0.75rem;">{{ str_replace('"', '', $practice->practice_id) }}</span>
                                                <span class="text-secondary" style="font-size: 0.75rem;">
                                                    {{ str_replace('"', '', $practice->practice_name) }}
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
                                <th class="text-white" style="width: 30%; background-color: #0f2b5c;">Kebijakan Pedoman /
                                    Prosedur</th>
                                <th class="text-white" style="width: 30%; background-color: #0f2b5c;">Evidences / Bukti
                                    Pelaksanaan</th>
                                <th class="text-white" style="width: 35%; background-color: #0f2b5c;">Potensi Perbaikan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($objective->practices as $practice)
                                @if ($practice->filled_evidence_count > 0)
                                    @foreach ($practice->activities as $index => $activity)
                                        <tr>
                                            {{-- Column 1: Practice (Rowspan) --}}
                                            @if ($index === 0)
                                                <td rowspan="{{ $practice->filled_evidence_count }}"
                                                    class="align-middle text-center" style="width: 1%;">
                                                    <div class="fw-bold text-primary p-2 text-nowrap">
                                                        {{ str_replace('"', '', $practice->practice_id) }}
                                                    </div>
                                                </td>
                                            @endif

                                            {{-- Column 2: Kebijakan / Prosedur (Activity Description) --}}
                                            <td class="align-middle">
                                                @if (isset($activity->assessment->policy_list) && count($activity->assessment->policy_list) > 0)
                                                    <div class="small text-break">
                                                        @foreach ($activity->assessment->policy_list as $line)
                                                            <div class="mb-1">{{ $line }}</div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="text-muted small fst-italic text-center">Belum ada Kebijakan
                                                        / Prosedur</div>
                                                @endif
                                            </td>

                                            {{-- Column 3: Evidence --}}
                                            <td class="align-middle">
                                                @if (isset($activity->assessment->execution_list) && count($activity->assessment->execution_list) > 0)
                                                    <div class="small text-break">
                                                        @foreach ($activity->assessment->execution_list as $line)
                                                            <div class="mb-1">{{ $line }}</div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="text-muted small fst-italic text-center">Belum ada Evidences
                                                        / Bukti Pelaksanaan</div>
                                                @endif
                                            </td>

                                            {{-- Column 4: Notes (Perbaikan) --}}
                                            <td class="align-top">
                                                <textarea class="form-control form-control-sm bg-white" rows="1" style="resize: vertical; font-size: 0.85rem;"></textarea>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    {{-- Fallback if no evidence found for this practice --}}
                                    <tr>
                                        <td class="align-middle text-center" style="width: 1%;">
                                            <div class="fw-bold text-primary p-2 text-nowrap">
                                                {{ str_replace('"', '', $practice->practice_id) }}
                                            </div>
                                        </td>
                                        <td colspan="3" class="text-center fst-italic text-muted small">Belum ada
                                            Evidences / Bukti Pelaksanaan</td>
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
