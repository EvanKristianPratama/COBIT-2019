@extends('layouts.app')

@section('content')
    <!-- JSpreadsheet v5 CDN -->
    <script src="https://bossanova.uk/jspreadsheet/v5/jspreadsheet.js"></script>
    <script src="https://jsuites.net/v5/jsuites.js"></script>
    <link rel="stylesheet" href="https://bossanova.uk/jspreadsheet/v5/jspreadsheet.css" type="text/css" />
    <link rel="stylesheet" href="https://jsuites.net/v5/jsuites.css" type="text/css" />

    <!-- Material Icons (Required for Toolbar) -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Material+Icons" />

    <div class="container">
        {{-- Navigation Breadcrumb --}}
        <div class="card shadow-sm border-0 mb-4 d-print-none sticky-nav"
            style="position: sticky; top: 135px; z-index: 1000; transition: top 0.3s;">
            <div class="card-header bg-white py-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('assessment-eval.report', ['evalId' => $evaluation->eval_id]) }}"
                                class="text-decoration-none text-muted" style="color: #0f2b5c !important;">
                                <i class="fas fa-chart-bar me-1"></i>Assessment Recapitulation Report
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('assessment-eval.report-activity', ['evalId' => $evaluation->eval_id, 'objectiveId' => $objectives->first()->objective_id]) }}"
                                class="text-decoration-none text-muted" style="color: #0f2b5c !important;">
                                <i class="fas fa-file-alt me-1"></i>Detail Recapitulation Report
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('assessment-eval.note', $evaluation->eval_id) }}"
                                class="text-decoration-none text-muted" style="color: #0f2b5c !important;">
                                <i class="fas fa-clipboard-list me-1"></i> Summary Report
                            </a>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>

        @foreach ($objectives as $objective)
            <div class="mb-5">
                {{-- 1. Header Bar --}}
                <div class="d-flex align-items-center mb-2 px-3 py-2 text-white justify-content-between"
                    style="background-color: #0f2b5c;">
                    <div class="fw-bold">
                        {{ $objective->objective_id }} - {{ $objective->objective }}
                    </div>
                </div>

                <div class="row g-0">
                    {{-- 3. Left Column: Score Card (3 Boxes) --}}
                    <div class="col-md-4 pe-md-3 mb-3 mb-md-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0"
                                style="border: 1px solid #000; border-collapse: collapse; width: 100%;">
                                <thead>
                                    {{-- Header Row --}}
                                    <tr style="background-color: #9b59b6; color: #fff; height: 30px;">
                                        <th
                                            style="width: 25%; border: 1px solid #fff; font-size: 0.61rem; text-align: center; vertical-align: middle; background-color: #9b59b6; color: #fff; padding: 4px;">
                                            Capability Level
                                        </th>
                                        <th
                                            style="width: 25%; border: 1px solid #fff; font-size: 0.61rem; text-align: center; vertical-align: middle; background-color: #9b59b6; color: #fff; padding: 4px;">
                                            Max Level
                                        </th>
                                        <th
                                            style="width: 25%; border: 1px solid #fff; font-size: 0.61rem; text-align: center; vertical-align: middle; background-color: #9b59b6; color: #fff; padding: 4px;">
                                            Rating
                                        </th>
                                        <th
                                            style="width: 25%; border: 1px solid #fff; font-size: 0.61rem; text-align: center; vertical-align: middle; background-color: #9b59b6; color: #fff; padding: 4px;">
                                            Capability Target {{ $evaluation->tahun ?? '2025' }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr style="height: 30px;">
                                        <td
                                            style="background-color: #fff; color: #000; text-align: center; vertical-align: middle; font-weight: bold; font-size: 10pt; border: 1px solid #000; padding: 5px;">
                                            {{ $objective->current_score }}
                                        </td>
                                        <td
                                            style="background-color: #fff; color: #000; text-align: center; vertical-align: middle; font-weight: bold; font-size: 10pt; border: 1px solid #000; padding: 5px;">
                                            {{ $objective->max_level }}
                                        </td>
                                        <td
                                            style="background-color: #fff; color: #000; text-align: center; vertical-align: middle; font-weight: bold; font-size: 10pt; border: 1px solid #000; padding: 5px;">
                                            {{ $objective->rating_string }}
                                        </td>
                                        <td
                                            style="background-color: #fff; color: #000; text-align: center; vertical-align: middle; font-weight: bold; font-size: 10pt; border: 1px solid #000; padding: 5px;">
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
                                <div class="fw-bold" style="font-size: 0.70rem;">Description</div>
                            </div>
                            {{-- Text --}}
                            <div class="p-2 bg-white flex-grow-1 d-flex align-items-center">
                                <p class="m-0 text-dark" style="font-size: 0.70rem; text-align: justify;">
                                    {{ $objective->objective_description ?? 'No description available.' }}
                                </p>
                            </div>
                        </div>
                        <div class="d-flex border">
                            {{-- Icon Box --}}
                            <div class="d-flex flex-column align-items-center justify-content-center text-white p-2"
                                style="background-color: #0f2b5c; width: 70px; flex-shrink: 0;">
                                <div class="fw-bold" style="font-size: 0.70rem;">Purpose</div>
                            </div>
                            {{-- Text --}}
                            <div class="p-2 bg-white flex-grow-1 d-flex align-items-center">
                                <p class="m-0 text-dark" style="font-size: 0.70rem; text-align: justify;">
                                    {{ $objective->objective_purpose ?? 'No description available.' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Management Practice Section --}}
                <div class="mt-2">
                    <div class="text-center fw-bold text-white d-flex align-items-center justify-content-center"
                        style="background-color: #0f2b5c; height: 39px;">
                        Management Practices List
                    </div>
                    <div class="border border-top-0 p-2 bg-white">
                        <div style="column-count: 3; ">
                            @foreach ($objective->practices as $practice)
                                <div class="d-flex align-items-center mb-2" style="break-inside: avoid;">
                                    <span class="me-2 text-dark text-nowrap"
                                        style="font-size: 0.75rem; line-height: 1.2;">{{ str_replace('"', '', $practice->practice_id) }}</span>
                                    <span class="text-dark text-secondary" style="font-size: 0.75rem; line-height: 1.2;">
                                        {{ str_replace('"', '', $practice->practice_name) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>


                {{-- Detailed Table Sections with Tabs --}}
                <div class="mt-2">
                    {{-- Toggle View Buttons (matching report-activity.blade.php style) --}}
                    <div class="btn-group mb-2 shadow-sm w-100" role="group" aria-label="View toggle">
                        <button type="button" class="btn btn-outline-primary px-4 py-2 active" data-view="gamo"
                            data-target="{{ $loop->index }}">
                            <i class="fas fa-layer-group me-2"></i>View by GAMO
                        </button>
                        <button type="button" class="btn btn-outline-secondary px-4 py-2" data-view="practice"
                            data-target="{{ $loop->index }}">
                            <i class="fas fa-list me-2"></i>View by Practice
                        </button>
                    </div>

                    {{-- View Content Sections --}}
                    <div class="view-sections" id="viewSections-{{ $loop->index }}">
                        {{-- View 1: Per GAMO (Aggregated) --}}
                        <div class="view-section-gamo" id="view-gamo-{{ $loop->index }}">
                            <table class="table table-bordered align-middle mb-0"
                                style="border-color: #000; border-width: 2px;">
                                <thead>
                                    <tr class="text-center">
                                        <th class="text-white" style="width: 50%; background-color: #0f2b5c;">Kebijakan
                                            Pedoman /
                                            Prosedur</th>
                                        <th class="text-white" style="width: 50%; background-color: #0f2b5c;">Evidences /
                                            Bukti
                                            Pelaksanaan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($objective->has_evidence)
                                        <tr>
                                            {{-- Column 1: Kebijakan / Prosedur (Design) --}}
                                            <td
                                                class="{{ isset($objective->policy_list) && count($objective->policy_list) > 0 ? 'align-top' : 'align-middle' }}">
                                                @if (isset($objective->policy_list) && count($objective->policy_list) > 0)
                                                    <div class="small text-break">
                                                        @foreach ($objective->policy_list as $line)
                                                            <div class="mb-1">• {{ $line }}</div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="text-muted small fst-italic text-center">Belum ada
                                                        Kebijakan
                                                        / Prosedur</div>
                                                @endif
                                            </td>

                                            {{-- Column 2: Evidence / Bukti Pelaksanaan (Execution) --}}
                                            <td
                                                class="{{ isset($objective->execution_list) && count($objective->execution_list) > 0 ? 'align-top' : 'align-middle' }}">
                                                @if (isset($objective->execution_list) && count($objective->execution_list) > 0)
                                                    <div class="small text-break">
                                                        @foreach ($objective->execution_list as $line)
                                                            <div class="mb-1">• {{ $line }}</div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="text-muted small fst-italic text-center">Belum ada
                                                        Evidences / Bukti Pelaksanaan</div>
                                                @endif
                                            </td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td colspan="2" class="text-center fst-italic text-muted small">Belum ada
                                                Kebijakan & Bukti Pelaksanaan</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        {{-- View 2: Per Practice (Detailed) --}}
                        <div class="view-section-practice" id="view-practice-{{ $loop->index }}"
                            style="display: none;">
                            <table class="table table-bordered align-middle mb-0"
                                style="border-color: #000; border-width: 2px;">
                                <thead>
                                    <tr class="text-center">
                                        <th class="text-white" style="width: 10%; background-color: #0f2b5c;">Practice
                                        </th>
                                        <th class="text-white" style="width: 45%; background-color: #0f2b5c;">Kebijakan
                                            Pedoman / Prosedur</th>
                                        <th class="text-white" style="width: 45%; background-color: #0f2b5c;">Evidences /
                                            Bukti Pelaksanaan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($objective->practices as $practice)
                                        <tr>
                                            <td class="align-middle text-center">
                                                <div class="fw-bold small">
                                                    {{ str_replace('"', '', $practice->practice_id) }}</div>
                                            </td>
                                            <td
                                                class="{{ isset($practice->policy_list) && count($practice->policy_list) > 0 ? 'align-top' : 'align-middle' }}">
                                                @if (isset($practice->policy_list) && count($practice->policy_list) > 0)
                                                    <div class="small text-break">
                                                        @foreach ($practice->policy_list as $line)
                                                            <div class="mb-1">• {{ $line }}</div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="text-muted small fst-italic text-center">Belum ada
                                                        Kebijakan / Prosedur</div>
                                                @endif
                                            </td>
                                            <td
                                                class="{{ isset($practice->execution_list) && count($practice->execution_list) > 0 ? 'align-top' : 'align-middle' }}">
                                                @if (isset($practice->execution_list) && count($practice->execution_list) > 0)
                                                    <div class="small text-break">
                                                        @foreach ($practice->execution_list as $line)
                                                            <div class="mb-1">• {{ $line }}</div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="text-muted small fst-italic text-center">Belum ada
                                                        Evidences / Bukti Pelaksanaan</div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="mt-2">
                    <form action="{{ route('assessment-eval.summary.save-note', ['evalId' => $evaluation->eval_id]) }}"
                        method="POST">
                        @csrf
                        <input type="hidden" name="objective_id" value="{{ $objective->objective_id }}">

                        {{-- Kesimpulan --}}
                        <div class="text-white px-2 py-1" style="background-color: #0f2b5c;">
                            <div class="fw-bold small">Kesimpulan</div>
                        </div>
                        <div class="p-2 bg-white border">
                            <textarea name="kesimpulan" class="form-control border-0" rows="3"
                                placeholder="Masukkan kesimpulan untuk {{ $objective->objective_id }}...">{{ is_array($objective->saved_note) ? $objective->saved_note['kesimpulan'] : '' }}</textarea>
                        </div>

                        {{-- Rekomendasi --}}
                        <div class="text-white px-2 py-1 mt-2" style="background-color: #0f2b5c;">
                            <div class="fw-bold small">Rekomendasi</div>
                        </div>
                        <div class="p-2 bg-white border">
                            <textarea name="rekomendasi" class="form-control border-0" rows="3"
                                placeholder="Masukkan rekomendasi untuk {{ $objective->objective_id }}...">{{ is_array($objective->saved_note) ? $objective->saved_note['rekomendasi'] : '' }}</textarea>
                        </div>

                        {{-- Roadmap Rekomendasi --}}
                        <div class="text-white px-2 py-1 mt-2" style="background-color: #0f2b5c;">
                            <div class="fw-bold small">Roadmap Rekomendasi</div>
                        </div>
                        <div id="roadmap-rekomendasi-container-{{ $objective->objective_id }}"
                            style="overflow: auto; max-height: 350px; border: 1px solid #ddd; border-radius: 4px;"></div>
                        <input type="hidden" name="roadmap_rekomendasi"
                            id="roadmap-rekomendasi-data-{{ $objective->objective_id }}"
                            value="{{ is_array($objective->saved_note) && isset($objective->saved_note['roadmap_rekomendasi']) ? json_encode($objective->saved_note['roadmap_rekomendasi']) : '' }}">


                        {{-- Roadmap Target Capability --}}
                        <div class="text-white px-2 py-1 mt-2" style="background-color: #0f2b5c;">
                            <div class="fw-bold small">Roadmap Target Capability</div>
                        </div>
                        <div class="p-2 bg-white border">
                            @if (isset($roadmap) && isset($roadmap['objectives']) && $roadmap['objectives']->isNotEmpty())
                                <div class="table-responsive" style="max-height: 300px; overflow: auto;">
                                    <table class="table table-bordered table-sm table-striped text-center align-middle">
                                        <thead class="sticky-top">
                                            <tr>
                                                <th rowspan="2" class="align-middle"
                                                    style="min-width: 80px; border: 2px solid #dee2e6; background-color: white;">
                                                    Objective
                                                    ID</th>
                                                <th rowspan="2" class="align-middle"
                                                    style="min-width: 200px; border: 2px solid #dee2e6; background-color: white;">
                                                    Objective Name</th>
                                                @foreach ($roadmap['years'] as $year)
                                                    <th colspan="2" class="align-middle border-bottom"
                                                        style="border: 2px solid #dee2e6; background-color: white;">
                                                        {{ $year }}
                                                    </th>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                @foreach ($roadmap['years'] as $year)
                                                    <th class="small font-weight-bold"
                                                        style="min-width: 50px; border: 2px solid #dee2e6; background-color: white;">
                                                        Level</th>
                                                    <th class="small font-weight-bold"
                                                        style="min-width: 50px; border: 2px solid #dee2e6; background-color: white;">
                                                        Rating
                                                    </th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($roadmap['objectives'] as $obj)
                                                <tr>
                                                    <td class="fw-bold small">
                                                        {{ str_replace('"', '', $obj->objective_id) }}</td>
                                                    <td class="text-start small">
                                                        {{ str_replace('"', '', $obj->objective) }}
                                                    </td>
                                                    @foreach ($roadmap['years'] as $year)
                                                        <td class="small">
                                                            {{ data_get($obj->roadmap_values, "$year.level") ?? '-' }}
                                                        </td>
                                                        <td class="small">
                                                            {{ data_get($obj->roadmap_values, "$year.rating") ?? '-' }}
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center text-muted fst-italic">Belum ada data roadmap</div>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- Floating Action Buttons --}}
            <div class="sticky-action-group">
                <a href="{{ route('assessment-eval.summary-pdf', ['evalId' => $evaluation->eval_id, 'objectiveId' => $objective->objective_id]) }}"
                    class="btn btn-danger sticky-action-btn" target="_blank" title="Export PDF Per GAMO">
                    <i class="fas fa-file-pdf"></i>
                    <span>PDF Per GAMO</span>
                </a>
                <a href="{{ route('assessment-eval.summary-detail-pdf', ['evalId' => $evaluation->eval_id, 'objectiveId' => $objective->objective_id]) }}"
                    class="btn btn-warning sticky-action-btn" target="_blank" title="Export PDF Per Practice">
                    <i class="fas fa-file-pdf"></i>
                    <span>PDF Per Practice</span>
                </a>
                <button type="button" class="btn btn-primary sticky-action-btn" id="saveAllNotesBtn"
                    title="Simpan Semua">
                    <i class="fas fa-save"></i>
                    <span>Simpan Semua</span>
                </button>
            </div>
        @endforeach

    </div>
@endsection

@push('scripts')
    @vite(['resources/js/report-summary.js', 'resources/css/report-summary.css'])

    {{-- Hidden Flash Messages for JS to read --}}
    @if (session('success'))
        <div id="flash-success" data-message="{{ session('success') }}" style="display:none;"></div>
    @endif
    @if (session('error'))
        <div id="flash-error" data-message="{{ session('error') }}" style="display:none;"></div>
    @endif
@endpush
