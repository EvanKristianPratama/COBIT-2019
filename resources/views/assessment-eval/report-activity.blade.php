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
                </div>
                <div>
                    <a href="{{ route('assessment-eval.report-activity-pdf', ['evalId' => $evalId, 'objectiveId' => $objective->objective_id]) }}{{ $filterLevel ? '?level=' . $filterLevel : '' }}" 
                       class="btn btn-sm btn-danger text-white fw-bold rounded-pill px-3 me-2" 
                       target="_blank">
                        <i class="fas fa-file-pdf me-1"></i>Export PDF
                    </a>
                    <a href="{{ route('assessment-eval.report', $evalId) }}" class="btn btn-light btn-sm rounded-pill px-3">
                        <i class="fas fa-arrow-left me-2"></i>Back to Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Brief Info Section --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                <div class="card-body p-0">
                    <div class="row g-0">
                        <div class="col-md-5 p-3 text-center bg-light border-end d-flex flex-column justify-content-center">
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0" style="border: 1px solid #000; border-collapse: collapse; width: 100%;">
                                    <thead>
                                        <tr style="background-color: #9b59b6; color: #fff;">
                                            <th style="width: 25%; border: 1px solid #fff; background-color: #9b59b6; color: #fff; text-align: center; vertical-align: middle;">{{ $objective->objective_id }}</th>
                                            <th style="width: 25%; border: 1px solid #fff; font-size: 0.65rem; font-style: italic; text-align: center; vertical-align: middle; padding: 4px; background-color: #9b59b6; color: #fff;">Capability Level</th>
                                            <th style="width: 25%; border: 1px solid #fff; font-size: 0.65rem; font-style: italic; text-align: center; vertical-align: middle; padding: 4px; background-color: #9b59b6; color: #fff;">Rating</th>
                                            <th style="width: 25%; border: 1px solid #fff; font-size: 0.65rem; font-style: italic; text-align: center; vertical-align: middle; padding: 4px; background-color: #9b59b6; color: #fff;">Capability Target {{ $evaluation->tahun ?? '2025' }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr style="height: 40px;">
                                            <td style="background-color: #9b59b6; color: #fff; font-size: 0.65rem; font-weight: bold; font-style: italic; text-align: center; vertical-align: middle; border: 1px solid #fff; padding: 4px;">Capability Level:</td>
                                            <td style="background-color: #fff; color: #000; text-align: center; vertical-align: middle; font-weight: bold; font-size: 1.15rem; border: 1px solid #000; padding: 4px;">{{ $currentLevel }}</td>
                                            <td style="background-color: #fff; color: #000; text-align: center; vertical-align: middle; font-weight: bold; font-size: 1.15rem; border: 1px solid #000; padding: 4px;">{{ $ratingString }}</td>
                                            <td style="background-color: #fff; color: #000; text-align: center; vertical-align: middle; font-weight: bold; font-size: 1.15rem; border: 1px solid #000; padding: 4px;">{{ $targetLevel }}</td>
                                        </tr>
                                        <tr style="height: 40px;">
                                            <td style="background-color: #9b59b6; color: #fff; font-size: 0.65rem; font-weight: bold; font-style: italic; text-align: center; vertical-align: middle; border: 1px solid #fff; padding: 4px;">Max Level:</td>
                                            <td style="background-color: #fff; color: #000; text-align: center; vertical-align: middle; font-weight: bold; font-size: 1.15rem; border: 1px solid #000; padding: 4px;">{{ $maxLevel }}</td>
                                            <td style="background-color: #fff; border: 1px solid #000;"></td>
                                            <td style="background-color: #fff; border: 1px solid #000;"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-7 p-4">
                            <div class="row">
                                <div class="col-sm-4 mb-3 mb-sm-0">
                                    <div class="text-uppercase extreme-small fw-bold text-muted mb-1">Assessment ID</div>
                                    <div class="fw-bold text-dark" style="font-size: 1.1rem;">#{{ $evalId }}</div>
                                </div>
                                <div class="col-sm-4 mb-3 mb-sm-0">
                                    <div class="text-uppercase extreme-small fw-bold text-muted mb-1">Assessment Year</div>
                                    <div class="fw-bold text-dark" style="font-size: 1.1rem;">{{ $evaluation->year ?? $evaluation->assessment_year ?? $evaluation->tahun ?? 'N/A' }}</div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="text-uppercase extreme-small fw-bold text-muted mb-1">Organization</div>
                                    <div class="fw-bold text-dark text-truncate" style="font-size: 1.1rem;" title="{{ $organization }}">
                                        {{ $organization }}
                                    </div>
                                </div>
                            </div>
                            <hr class="my-3 opacity-10">
                            <div class="row">
                                <div class="col-12">
                                    <div class="text-uppercase extreme-small fw-bold text-muted mb-1">{{ $areaObjective->area }} Objective</div>
                                    <div class="fw-semibold text-secondary small">
                                        {{ $objective->objective_id }} - {{ $objective->objective }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Activity Table --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <h5 class="mb-0 fw-bold text-primary">Filled Activities</h5>
                <span class="badge bg-secondary">{{ count($activityData) }} activities</span>
            </div>
            
            <div class="d-flex align-items-center gap-2">
                <label for="level-filter" class="form-label mb-0 small fw-bold text-secondary">Filter by Level:</label>
                <select class="form-select form-select-sm" id="level-filter" style="width: 120px;">
                    <option value="">All Levels</option>
                    @for($i = 2; $i <= 5; $i++)
                        <option value="{{ $i }}" {{ (string)$filterLevel === (string)$i ? 'selected' : '' }}>Level {{ $i }}</option>
                    @endfor
                </select>
            </div>
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
                            // Group activities by (level, practice_id) for rowspan calculation
                            $groups = collect($activityData)->groupBy(function($item) {
                                return $item['capability_level'] . '-' . $item['practice_id'];
                            });
                            $rowIndex = 0;
                            $processedGroups = [];
                        @endphp
                        
                        @forelse($activityData as $index => $activity)
                            @php
                                $groupId = $activity['capability_level'] . '-' . $activity['practice_id'];
                                $isFirstInGroup = !in_array($groupId, $processedGroups);
                                $groupRowspan = $isFirstInGroup ? $groups[$groupId]->count() : 0;
                                if ($isFirstInGroup) {
                                    $processedGroups[] = $groupId;
                                    $rowIndex++;
                                }
                            @endphp
                            <tr>
                                @if($isFirstInGroup)
                                    <td class="text-center" rowspan="{{ $groupRowspan }}">{{ $rowIndex }}</td>
                                    <td class="fw-semibold" rowspan="{{ $groupRowspan }}">{{ str_replace('"', '', $activity['practice_id']) }}</td>
                                    <td rowspan="{{ $groupRowspan }}">{{ str_replace('"', '', $activity['practice_name']) }}</td>
                                @endif
                                <td>{{ str_replace('"', '', $activity['activity_description']) }}</td>
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
                                    No filled activities for this criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const levelFilter = document.getElementById('level-filter');
        if (levelFilter) {
            levelFilter.addEventListener('change', function() {
                const val = this.value;
                const url = new URL(window.location.href);
                if (val) {
                    url.searchParams.set('level', val);
                } else {
                    url.searchParams.delete('level');
                }
                window.location.href = url.toString();
            });
        }
    });
</script>

<style>
    .extreme-small {
        font-size: 0.65rem;
        letter-spacing: 0.05em;
    }
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
