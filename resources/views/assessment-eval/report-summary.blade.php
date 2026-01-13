@extends('layouts.app')

@section('content')
    <div class="container">
        @foreach ($objectives as $objective)
            <div class="mb-5">
                {{-- 1. Header Bar --}}
                <div class="d-flex align-items-center mb-2 px-3 py-2 text-white justify-content-between"
                    style="background-color: #0f2b5c;">
                    <div class="fw-bold fs-5">
                        {{ $loop->iteration }}. {{ $objective->objective_id }} - {{ $objective->objective }}
                    </div>
                    <div>
                        <a href="{{ route('assessment-eval.summary-pdf', ['evalId' => $evaluation->eval_id, 'objectiveId' => $objective->objective_id]) }}"
                            class="btn btn-sm btn-danger text-white fw-bold rounded-pill px-3" target="_blank">
                            <i class="fas fa-file-pdf me-1"></i> Export PDF
                        </a>
                    </div>
                </div>

                <div class="row g-0">
                    {{-- 3. Left Column: Score Card (3 Boxes) --}}
                    <div class="col-md-4 pe-md-3 mb-3 mb-md-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0"
                                style="border: 1px solid #000; border-collapse: collapse; width: 100%;">
                                <thead>
                                    {{-- New Top Row: Objective ID --}}
                                    <tr style="background-color: #9b59b6; color: #fff;">
                                        <th colspan="4"
                                            style="border: 1px solid #fff; background-color: #9b59b6; color: #fff; text-align: center; vertical-align: middle; padding: 8px;">
                                            <div class="fw-bold" style="font-size: 1.1rem; line-height: 1.2;">
                                                {{ $objective->objective_id }}
                                            </div>
                                            <div style="font-size: 0.65rem; margin-top: 4px;">{{ $objective->objective }}
                                            </div>
                                        </th>
                                    </tr>
                                    {{-- Header Row --}}
                                    <tr style="background-color: #9b59b6; color: #fff;">
                                        <th
                                            style="width: 25%; border: 1px solid #fff; font-size: 0.65rem; font-style: italic; text-align: center; vertical-align: middle; background-color: #9b59b6; color: #fff;">
                                            Capability Level
                                        </th>
                                        <th
                                            style="width: 25%; border: 1px solid #fff; font-size: 0.65rem; font-style: italic; text-align: center; vertical-align: middle; background-color: #9b59b6; color: #fff;">
                                            Max Level
                                        </th>
                                        <th
                                            style="width: 25%; border: 1px solid #fff; font-size: 0.65rem; font-style: italic; text-align: center; vertical-align: middle; background-color: #9b59b6; color: #fff;">
                                            Rating
                                        </th>
                                        <th
                                            style="width: 25%; border: 1px solid #fff; font-size: 0.65rem; font-style: italic; text-align: center; vertical-align: middle; background-color: #9b59b6; color: #fff;">
                                            Capability Target {{ $evaluation->tahun ?? '2025' }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr style="height: 35px;">
                                        <td
                                            style="background-color: #fff; color: #000; text-align: center; vertical-align: middle; font-weight: bold; font-size: 1rem; border: 1px solid #000;">
                                            {{ $objective->current_score }}
                                        </td>
                                        <td
                                            style="background-color: #fff; color: #000; text-align: center; vertical-align: middle; font-weight: bold; font-size: 1rem; border: 1px solid #000;">
                                            {{ $objective->max_level }}
                                        </td>
                                        <td
                                            style="background-color: #fff; color: #000; text-align: center; vertical-align: middle; font-weight: bold; font-size: 1rem; border: 1px solid #000;">
                                            {{ $objective->rating_string }}
                                        </td>
                                        <td
                                            style="background-color: #fff; color: #000; text-align: center; vertical-align: middle; font-weight: bold; font-size: 1rem; border: 1px solid #000;">
                                            {{ $objective->target_level == 0 ? '-' : $objective->target_level }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- 4. Right Column: Details --}}
                    <div class="col-md-8">
                        {{-- Tujuan / Purpose Section --}}
                        <div class="d-flex mb-2 border">
                            {{-- Icon Box --}}
                            <div class="d-flex flex-column align-items-center justify-content-center text-white p-2"
                                style="background-color: #0f2b5c; width: 70px; flex-shrink: 0;">
                                <div class="fw-bold" style="font-size: 0.65rem;">Deskripsi</div>
                            </div>
                            {{-- Text --}}
                            <div class="p-2 bg-white flex-grow-1 d-flex align-items-center">
                                <p class="m-0 text-secondary" style="font-size: 0.75rem; text-align: justify;">
                                    {{ $objective->objective_description ?? 'No description available.' }}
                                </p>
                            </div>
                        </div>
                        <div class="d-flex border">
                            {{-- Icon Box --}}
                            <div class="d-flex flex-column align-items-center justify-content-center text-white p-2"
                                style="background-color: #0f2b5c; width: 70px; flex-shrink: 0;">
                                <div class="fw-bold" style="font-size: 0.65rem;">Tujuan</div>
                            </div>
                            {{-- Text --}}
                            <div class="p-2 bg-white flex-grow-1 d-flex align-items-center">
                                <p class="m-0 text-secondary" style="font-size: 0.75rem; text-align: justify;">
                                    {{ $objective->objective_purpose ?? 'No description available.' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Management Practice Section --}}
                    <div class="col-12 mt-2">
                        <div class="text-center py-1 fw-bold small text-white" style="background-color: #0f2b5c;">
                            Management Practices List
                        </div>
                        <div class="border border-top-0 p-2 bg-white">
                            <div style="column-count: 3; ">
                                @foreach ($objective->practices as $practice)
                                    <div class="d-flex align-items-center mb-2" style="break-inside: avoid;">
                                        <span class="fw-bold me-2 text-dark text-nowrap"
                                            style="font-size: 0.75rem; line-height: 1.2;">{{ str_replace('"', '', $practice->practice_id) }}</span>
                                        <span class="text-secondary" style="font-size: 0.75rem; line-height: 1.2;">
                                            {{ str_replace('"', '', $practice->practice_name) }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Detailed Table Section --}}
                <div class="mt-2">
                    <table class="table table-bordered align-middle mb-0" style="border-color: #000; border-width: 2px;">
                        <thead>
                            <tr class="text-center">
                                <th class="text-white" style="width: 5%; background-color: #0f2b5c;">Practice</th>
                                <th class="text-white" style="width: 45%; background-color: #0f2b5c;">Kebijakan Pedoman /
                                    Prosedur</th>
                                <th class="text-white" style="width: 50%; background-color: #0f2b5c;">Evidences / Bukti
                                    Pelaksanaan</th>
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
                                        <td colspan="2" class="text-center fst-italic text-muted small">Belum ada
                                            Kebijakan & Bukti Pelaksanaan</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Potensi Perbaikan Section (Per GAMO) --}}
                <div class="mt-2 border">
                    <form action="{{ route('assessment-eval.summary.save-note', ['evalId' => $evaluation->eval_id]) }}"
                        method="POST">
                        @csrf
                        <input type="hidden" name="objective_id" value="{{ $objective->objective_id }}">

                        <div class="d-flex justify-content-between align-items-center text-white px-2 py-1"
                            style="background-color: #0f2b5c;">
                            <div class="fw-bold small">Potensi Perbaikan</div>
                            <button type="submit" class="btn btn-sm btn-light py-0 px-2 fw-bold d-flex align-items-center"
                                style="font-size: 0.7rem;">
                                <i class="fas fa-save me-1"></i> Simpan
                            </button>
                        </div>
                        <div class="p-2 bg-white">
                            <textarea name="notes" class="form-control border-0" rows="3"
                                placeholder="Masukkan catatan perbaikan untuk {{ $objective->objective_id }}...">{{ $objective->saved_note }}</textarea>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: '{{ session('success') }}',
                    timer: 2000,
                    showConfirmButton: false
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: '{{ session('error') }}'
                });
            @endif
        });
    </script>
@endpush
