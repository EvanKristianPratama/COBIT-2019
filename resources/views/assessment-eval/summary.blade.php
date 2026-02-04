@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6">
        {{-- Header (Same as Report) --}}
        <div class="card shadow-sm mb-4 hero-card" style="border:none;box-shadow:0 22px 45px rgba(14,33,70,0.15);">
            <div class="card-header hero-header py-4"
                style="background:linear-gradient(135deg,#081a3d,#0f2b5c);color:#fff;border:none;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="hero-title" style="font-size:1.5rem;font-weight:700;letter-spacing:0.04em;">Assessment
                            Report</div>
                        <div class="hero-eval-id"
                            style="font-size:1.05rem;font-weight:600;margin-top:0.25rem;letter-spacing:0.08em;text-transform:uppercase;color:rgba(255,255,255,0.85);">
                            Assessment Id: {{ $evalId }}
                        </div>
                        <div class="hero-eval-year text-uppercase"
                            style="font-size:0.95rem;font-weight:600;color:rgba(255,255,255,0.75);letter-spacing:0.06em;">
                            Assessment Year:
                            {{ $evaluation->year ?? ($evaluation->assessment_year ?? ($evaluation->tahun ?? 'N/A')) }}
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('assessment-eval.show', $evalId) }}"
                            class="btn btn-light btn-sm rounded-pill px-3">
                            <i class="fas fa-arrow-left me-2"></i>Back to Assessment
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Navigation Tabs --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('assessment-eval.report', $evalId) }}"
                                class="text-decoration-none {{ Route::currentRouteName() == 'assessment-eval.report' ? 'active-tab text-primary fw-bold' : '' }}"
                                style="{{ Route::currentRouteName() != 'assessment-eval.report' ? 'color: #0f2b5c;' : '' }}">
                                <i class="fas fa-file-alt me-1"></i> Assessment Recapitulation Report
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('assessment-eval.note', $evalId) }}"
                                class="text-decoration-none {{ Route::currentRouteName() == 'assessment-eval.note' ? 'active-tab text-primary fw-bold' : '' }}"
                                style="{{ Route::currentRouteName() != 'assessment-eval.note' ? 'color: #0f2b5c;' : '' }}">
                                <i class="fas fa-clipboard-list me-1"></i> Summary
                            </a>
                        </li>
                    </ol>
                </nav>
            </div>

            {{-- Summary Table Section --}}
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead>
                            <tr class="bg-light">
                                <th style="width:50px;" class="text-center">No</th>
                                <th style="width:90px;" class="text-center ">GAMO</th>
                                <th style="width:20%;">Process Name</th>
                                <th style="width:25%;" class="text-center">Kesimpulan</th>
                                <th style="width:25%;" class="text-center">Catatan</th>
                                <th style="width:80px;" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reports as $index => $report)
                                <tr>
                                    <td class="text-center fw-semibold align-middle">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center h-100">
                                            <span class="fw-bold text-primary">{{ $report->objective_id }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span
                                            class="small text-muted">{{ $objectives[$report->objective_id] ?? '-' }}</span>
                                    </td>
                                    <td>
                                        @if (!empty($report->kesimpulan) && $report->kesimpulan !== '-')
                                            <div class="small"
                                                style="white-space: pre-line; max-height: 150px; overflow-y: auto;">
                                                {{ $report->kesimpulan }}
                                            </div>
                                        @else
                                            <div
                                                class="small text-muted fst-italic text-center d-flex align-items-center justify-content-center h-100">
                                                Belum ada Kesimpulan
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if (!empty($report->rekomendasi) && $report->rekomendasi !== '-')
                                            <div class="small"
                                                style="white-space: pre-line; max-height: 150px; overflow-y: auto;">
                                                {{ $report->rekomendasi }}
                                            </div>
                                        @else
                                            <div
                                                class="small text-muted fst-italic text-center d-flex align-items-center justify-content-center h-100">
                                                Belum ada Catatan
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="{{ url('/assessment-eval/' . $evalId . '/report-activity/' . $report->objective_id) }}"
                                                class="btn btn-xs btn-outline-primary rounded-pill py-1 px-3 me-1"
                                                style="font-size: 0.7rem; font-weight: 600;">
                                                Detail
                                            </a>
                                            <a href="{{ route('assessment-eval.summary', ['evalId' => $evalId, 'objectiveId' => $report->objective_id]) }}"
                                                class="btn btn-xs btn-outline-info rounded-pill py-1 px-3"
                                                style="font-size: 0.7rem; font-weight: 600;">
                                                Summary
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No summary data available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
