@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container mx-auto p-6">
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
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-0" id="activity-report-table">
                    <thead style="background-color: #e9ecef;">
                        <tr>
                            <th class="text-center" style="width: 50px;">NO</th>
                            <th style="width: 100px;">PRACTICE</th>
                            <th style="width: 200px;">PRACTICE NAME</th>
                            <th>ACTIVITY</th>
                            <th class="text-center" style="width: 100px;">ANSWER</th>
                            <th style="width: 200px;">EVIDENCE</th>
                            <th style="width: 200px;">NOTES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activityData as $index => $activity)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td class="fw-semibold">{{ $activity['practice_id'] }}</td>
                                <td>{{ $activity['practice_name'] }}</td>
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
                                    @if($activity['evidence'])
                                        @php
                                            $evidences = is_array($activity['evidence']) 
                                                ? $activity['evidence'] 
                                                : json_decode($activity['evidence'], true) ?? [];
                                        @endphp
                                        @if(count($evidences) > 0)
                                            <ul class="mb-0 ps-3 small">
                                                @foreach($evidences as $ev)
                                                    <li>{{ is_array($ev) ? ($ev['name'] ?? $ev['id'] ?? json_encode($ev)) : $ev }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="text-muted small">No evidence</span>
                                        @endif
                                    @else
                                        <span class="text-muted small">No evidence</span>
                                    @endif
                                </td>
                                <td>
                                    @if($activity['notes'])
                                        <span class="small">{{ $activity['notes'] }}</span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
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
        vertical-align: middle;
    }
</style>
@endsection
