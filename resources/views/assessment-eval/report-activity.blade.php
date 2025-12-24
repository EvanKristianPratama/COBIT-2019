@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container-fluid px-4 py-3">
    {{-- Header --}}
    <div class="card shadow-sm mb-4 hero-card" style="border:none;box-shadow:0 22px 45px rgba(14,33,70,0.15);">
        <div class="card-header hero-header py-4" style="background:linear-gradient(135deg,#081a3d,#0f2b5c);color:#fff;border:none;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="hero-title" style="font-size:1.5rem;font-weight:700;letter-spacing:0.04em;">Activity Report</div>
                    <div class="hero-eval-id" style="font-size:1.05rem;font-weight:600;margin-top:0.25rem;letter-spacing:0.08em;text-transform:uppercase;color:rgba(255,255,255,0.85);">
                        {{ $objective->objective_id }} - {{ $objective->objective ?? 'Objective' }}
                    </div>
                    <div class="hero-eval-year text-uppercase" style="font-size:0.95rem;font-weight:600;color:rgba(255,255,255,0.75);letter-spacing:0.06em;">
                        Assessment Year: {{ $evaluation->year ?? $evaluation->assessment_year ?? $evaluation->tahun ?? 'N/A' }}
                    </div>
                </div>
                <div>
                    <a href="{{ route('assessment-eval.report', $evalId) }}" class="btn btn-light btn-sm rounded-pill px-3">
                        <i class="fas fa-arrow-left me-2"></i>Back to Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Activity Table --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-primary">Filled Activities</h5>
            <span class="badge bg-secondary">{{ count($activityData) }} activities</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 70vh; overflow: auto;">
                <table class="table table-sm table-bordered align-middle mb-0" id="activity-report-table">
                    <thead style="background-color: #e9ecef;">
                        <tr>
                            <th class="text-center" style="min-width: 50px;">NO</th>
                            <th style="min-width: 100px;">PRACTICE</th>
                            <th style="min-width: 180px;">PRACTICE NAME</th>
                            <th style="min-width: 300px;">ACTIVITY</th>
                            <th class="text-center" style="min-width: 90px;">ANSWER</th>
                            <th style="min-width: 250px;">EVIDENCE</th>
                            <th style="min-width: 200px;">NOTES</th>
                            <th class="text-center" style="min-width: 60px;">LEVEL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Group activities by practice_id for rowspan calculation
                            $practiceGroups = collect($activityData)->groupBy('practice_id');
                            $rowIndex = 0;
                            $processedPractices = [];
                        @endphp
                        
                        @forelse($activityData as $index => $activity)
                            @php
                                $practiceId = $activity['practice_id'];
                                $isFirstInPractice = !in_array($practiceId, $processedPractices);
                                $practiceRowspan = $isFirstInPractice ? $practiceGroups[$practiceId]->count() : 0;
                                if ($isFirstInPractice) {
                                    $processedPractices[] = $practiceId;
                                    $rowIndex++;
                                }
                            @endphp
                            <tr>
                                @if($isFirstInPractice)
                                    <td class="text-center" rowspan="{{ $practiceRowspan }}">{{ $rowIndex }}</td>
                                    <td class="fw-semibold" rowspan="{{ $practiceRowspan }}">{{ $activity['practice_id'] }}</td>
                                    <td rowspan="{{ $practiceRowspan }}">{{ $activity['practice_name'] }}</td>
                                @endif
                                <td>{{ $activity['activity_description'] }}</td>
                                <td class="text-center">
                                    @php
                                        $answer = $activity['answer'];
                                        $badgeClass = match(strtolower($answer)) {
                                            'fully', 'f' => 'bg-success',
                                            'largely', 'l' => 'bg-info',
                                            'partially', 'p' => 'bg-warning text-dark',
                                            'not', 'n' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ $answer }}</span>
                                </td>
                                <td>
                                    @if(!empty($activity['evidence']) && is_array($activity['evidence']) && count($activity['evidence']) > 0)
                                        <ul class="mb-0 ps-3 small">
                                            @foreach($activity['evidence'] as $ev)
                                                <li>{{ $ev }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($activity['notes'])
                                        <span class="small">{{ $activity['notes'] }}</span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="text-center fw-bold">
                                    {{ $activity['capability_level'] ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    No filled activities for this objective.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    #activity-report-table th {
        position: sticky;
        top: 0;
        background-color: #e9ecef;
        z-index: 10;
    }
    #activity-report-table td, #activity-report-table th {
        vertical-align: top;
    }
    #activity-report-table td[rowspan] {
        vertical-align: middle;
        background-color: #f8f9fa;
    }
    .table-responsive {
        -webkit-overflow-scrolling: touch;
    }
</style>
@endsection
