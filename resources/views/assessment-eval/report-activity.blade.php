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
                                            <td style="background-color: #fff; color: #000; text-align: center; vertical-align: middle; font-weight: bold; font-size: 1.15rem; border: 1px solid #000; padding: 4px;">{{ $targetLevel === null ? '-' : $targetLevel }}</td>
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
                                    <div class="fw-bold text-dark" style="font-size: 1.1rem;">{{ $evalId }}</div>
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

    {{-- Toggle View Buttons --}}
    <div class="btn-group mb-4 shadow-sm w-100" role="group" aria-label="View toggle">
        <button type="button" class="btn btn-outline-primary px-4 py-2 active" id="btn-view-practice">
            <i class="fas fa-list me-2"></i>View by Practice
        </button>
        <button type="button" class="btn btn-outline-secondary px-4 py-2" id="btn-view-level">
            <i class="fas fa-layer-group me-2"></i>View by Level
        </button>
    </div>

    {{-- VIEW BY PRACTICE --}}
    <div id="view-practice" class="view-section">
    {{-- Activity Table --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="d-flex align-items-center gap-3">
                        <h5 class="mb-0 fw-bold text-primary">Filled Activities (by Practice)</h5>
                        <span class="badge bg-secondary" id="activity-count-practice">{{ count($activityData) }} activities</span>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2">
                                <label for="filter-level-practice" class="form-label mb-0 small fw-bold text-secondary" style="white-space: nowrap;">Filter by Level:</label>
                                <select class="form-select form-select-sm" id="filter-level-practice">
                                    <option value="">All Levels</option>
                                    <option value="2">Level 2</option>
                                    <option value="3">Level 3</option>
                                    <option value="4">Level 4</option>
                                    <option value="5">Level 5</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2">
                                <label for="filter-practice" class="form-label mb-0 small fw-bold text-secondary" style="white-space: nowrap;">Filter by Practice:</label>
                                <select class="form-select form-select-sm" id="filter-practice">
                                    <option value="">All Practices</option>
                                    @php
                                        $practices = collect($activityData)
                                            ->map(fn($a) => ['id' => $a['practice_id'], 'name' => $a['practice_name']])
                                            ->unique('id')
                                            ->sortBy('id')
                                            ->values();
                                    @endphp
                                    @foreach($practices as $practice)
                                        <option value="{{ $practice['id'] }}">{{ $practice['id'] }} - {{ Str::limit($practice['name'], 30) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
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
                            <tr data-level="{{ $activity['capability_level'] }}" data-practice="{{ $activity['practice_id'] }}" class="activity-row-practice">
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

    {{-- VIEW BY LEVEL --}}
    <div id="view-level" class="view-section" style="display: none;">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="d-flex align-items-center gap-3">
                        <h5 class="mb-0 fw-bold text-primary">Filled Activities (by Level)</h5>
                        <span class="badge bg-secondary" id="activity-count-level">{{ count($activityData) }} activities</span>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2">
                                <label for="filter-level-level" class="form-label mb-0 small fw-bold text-secondary" style="white-space: nowrap;">Filter by Level:</label>
                                <select class="form-select form-select-sm" id="filter-level-level">
                                    <option value="">All Levels</option>
                                    <option value="2">Level 2</option>
                                    <option value="3">Level 3</option>
                                    <option value="4">Level 4</option>
                                    <option value="5">Level 5</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2">
                                <label for="filter-practice-level" class="form-label mb-0 small fw-bold text-secondary" style="white-space: nowrap;">Filter by Practice:</label>
                                <select class="form-select form-select-sm" id="filter-practice-level">
                                    <option value="">All Practices</option>
                                    @foreach($practices as $practice)
                                        <option value="{{ $practice['id'] }}">{{ $practice['id'] }} - {{ Str::limit($practice['name'], 30) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 70vh; overflow: auto;">
                <table class="table table-sm table-bordered align-middle mb-0" id="activity-report-table-level">
                    <thead style="background-color: #e9ecef;">
                        <tr>
                            <th class="text-center" style="min-width: 50px;">NO</th>
                            <th class="text-center" style="min-width: 60px;">LEVEL</th>
                            <th style="min-width: 100px;">PRACTICE</th>
                            <th style="min-width: 180px;">PRACTICE NAME</th>
                            <th style="min-width: 300px;">ACTIVITY</th>
                            <th class="text-center" style="min-width: 90px;">ANSWER</th>
                            <th style="min-width: 250px;">EVIDENCE</th>
                            <th style="min-width: 200px;">NOTES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Group by level first, then practice
                            $activityDataByLevel = collect($activityData)->sortBy([['capability_level', 'asc'], ['practice_id', 'asc'], ['activity_id', 'asc']]);
                            $groupsByLevel = $activityDataByLevel->groupBy(function($item) {
                                return $item['capability_level'] . '-' . $item['practice_id'];
                            });
                            $rowIndexLevel = 0;
                            $processedGroupsLevel = [];
                            $previousLevel = null;
                        @endphp
                        
                        @forelse($activityDataByLevel as $index => $activity)
                            @php
                                $groupIdLevel = $activity['capability_level'] . '-' . $activity['practice_id'];
                                $isFirstInGroupLevel = !in_array($groupIdLevel, $processedGroupsLevel);
                                $groupRowspanLevel = $isFirstInGroupLevel ? $groupsByLevel[$groupIdLevel]->count() : 0;
                                $currentLevel = $activity['capability_level'];
                                $isNewLevel = $previousLevel !== null && $currentLevel !== $previousLevel;
                                
                                if ($isFirstInGroupLevel) {
                                    $processedGroupsLevel[] = $groupIdLevel;
                                    $rowIndexLevel++;
                                }
                            @endphp
                            
                            {{-- Level separator row --}}
                            @if($isNewLevel)
                                <tr class="level-separator-row" data-separator-level="{{ $currentLevel }}">
                                    <td class="text-center fw-bold" style="background-color: #e9ecef;">LEVEL</td>
                                    <td class="text-center fw-bold" style="background-color: #e9ecef;">{{ $currentLevel }}</td>
                                    <td class="text-center fw-bold" style="background-color: #e9ecef;">RATING</td>
                                    <td class="text-center fw-bold" style="background-color: #e9ecef;">
                                        @if(isset($levelRatings[$currentLevel]))
                                            @php
                                                $score = $levelRatings[$currentLevel]['score'] / 100;
                                                $letter = 'N';
                                                if ($score > 0.85) $letter = 'F';
                                                elseif ($score > 0.50) $letter = 'L';
                                                elseif ($score > 0.15) $letter = 'P';
                                            @endphp
                                            {{ $letter }} {{ number_format($score, 2) }}
                                        @else
                                            N 0.00
                                        @endif
                                    </td>
                                    <td colspan="4" style="background-color: #e9ecef;"></td>
                                </tr>
                                @php $previousLevel = $currentLevel; @endphp
                            @elseif($previousLevel === null)
                                {{-- First level header --}}
                                <tr class="level-separator-row" data-separator-level="{{ $currentLevel }}">
                                    <td class="text-center fw-bold" style="background-color: #e9ecef;">LEVEL</td>
                                    <td class="text-center fw-bold" style="background-color: #e9ecef;">{{ $currentLevel }}</td>
                                    <td class="text-center fw-bold" style="background-color: #e9ecef;">RATING</td>
                                    <td class="text-center fw-bold" style="background-color: #e9ecef;">
                                        @if(isset($levelRatings[$currentLevel]))
                                            @php
                                                $score = $levelRatings[$currentLevel]['score'] / 100;
                                                $letter = 'N';
                                                if ($score > 0.85) $letter = 'F';
                                                elseif ($score > 0.50) $letter = 'L';
                                                elseif ($score > 0.15) $letter = 'P';
                                            @endphp
                                            {{ $letter }} {{ number_format($score, 2) }}
                                        @else
                                            N 0.00
                                        @endif
                                    </td>
                                    <td colspan="4" style="background-color: #e9ecef;"></td>
                                </tr>
                                @php $previousLevel = $currentLevel; @endphp
                            @endif
                            
                            <tr data-level="{{ $activity['capability_level'] }}" data-practice="{{ $activity['practice_id'] }}" class="activity-row-level">
                                @if($isFirstInGroupLevel)
                                    <td class="text-center" rowspan="{{ $groupRowspanLevel }}">{{ $rowIndexLevel }}</td>
                                    <td class="text-center fw-bold" rowspan="{{ $groupRowspanLevel }}" style="background-color: #f8f9fa;">{{ $activity['capability_level'] ?? '-' }}</td>
                                    <td class="fw-semibold" rowspan="{{ $groupRowspanLevel }}">{{ str_replace('"', '', $activity['practice_id']) }}</td>
                                    <td rowspan="{{ $groupRowspanLevel }}">{{ str_replace('"', '', $activity['practice_name']) }}</td>
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
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // View toggle buttons
        const btnViewPractice = document.getElementById('btn-view-practice');
        const btnViewLevel = document.getElementById('btn-view-level');
        const viewPractice = document.getElementById('view-practice');
        const viewLevel = document.getElementById('view-level');
        
        // Toggle between views
        btnViewPractice.addEventListener('click', function() {
            viewPractice.style.display = 'block';
            viewLevel.style.display = 'none';
            btnViewPractice.classList.add('active');
            btnViewLevel.classList.remove('active');
        });
        
        btnViewLevel.addEventListener('click', function() {
            viewPractice.style.display = 'none';
            viewLevel.style.display = 'block';
            btnViewLevel.classList.add('active');
            btnViewPractice.classList.remove('active');
        });
        
        // Filter for VIEW BY PRACTICE
        const filterLevelPractice = document.getElementById('filter-level-practice');
        const filterPractice = document.getElementById('filter-practice');
        const activityRowsPractice = document.querySelectorAll('.activity-row-practice');
        const activityCountPractice = document.getElementById('activity-count-practice');
        
        function applyFiltersPractice() {
            const selectedLevel = filterLevelPractice.value;
            const selectedPractice = filterPractice.value;
            let visibleCount = 0;
            
            activityRowsPractice.forEach(row => {
                const rowLevel = row.dataset.level;
                const rowPractice = row.dataset.practice;
                
                const levelMatch = !selectedLevel || rowLevel === selectedLevel;
                const practiceMatch = !selectedPractice || rowPractice === selectedPractice;
                
                if (levelMatch && practiceMatch) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Update count badge
            if (activityCountPractice) {
                let filterText = [];
                if (selectedLevel) filterText.push(`Level ${selectedLevel}`);
                if (selectedPractice) filterText.push(`Practice ${selectedPractice}`);
                const suffix = filterText.length > 0 ? ` (${filterText.join(', ')})` : '';
                activityCountPractice.textContent = `${visibleCount} activities${suffix}`;
            }
        }
        
        if (filterLevelPractice) {
            filterLevelPractice.addEventListener('change', applyFiltersPractice);
        }
        if (filterPractice) {
            filterPractice.addEventListener('change', applyFiltersPractice);
        }
        
        // Filter for VIEW BY LEVEL
        const filterLevelLevel = document.getElementById('filter-level-level');
        const filterPracticeLevel = document.getElementById('filter-practice-level');
        const activityRowsLevel = document.querySelectorAll('.activity-row-level');
        const levelSeparatorRows = document.querySelectorAll('.level-separator-row');
        const activityCountLevel = document.getElementById('activity-count-level');
        
        function applyFiltersLevel() {
            const selectedLevel = filterLevelLevel.value;
            const selectedPractice = filterPracticeLevel.value;
            let visibleCount = 0;
            const visibleLevels = new Set();
            
            activityRowsLevel.forEach(row => {
                const rowLevel = row.dataset.level;
                const rowPractice = row.dataset.practice;
                
                const levelMatch = !selectedLevel || rowLevel === selectedLevel;
                const practiceMatch = !selectedPractice || rowPractice === selectedPractice;
                
                if (levelMatch && practiceMatch) {
                    row.style.display = '';
                    visibleCount++;
                    visibleLevels.add(rowLevel);
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Show/hide level separators based on visible activities
            levelSeparatorRows.forEach(separator => {
                const separatorLevel = separator.dataset.separatorLevel;
                if (visibleLevels.has(separatorLevel)) {
                    separator.style.display = '';
                } else {
                    separator.style.display = 'none';
                }
            });
            
            // Update count badge
            if (activityCountLevel) {
                let filterText = [];
                if (selectedLevel) filterText.push(`Level ${selectedLevel}`);
                if (selectedPractice) filterText.push(`Practice ${selectedPractice}`);
                const suffix = filterText.length > 0 ? ` (${filterText.join(', ')})` : '';
                activityCountLevel.textContent = `${visibleCount} activities${suffix}`;
            }
        }
        
        if (filterLevelLevel) {
            filterLevelLevel.addEventListener('change', applyFiltersLevel);
        }
        if (filterPracticeLevel) {
            filterPracticeLevel.addEventListener('change', applyFiltersLevel);
        }
        
        // Initialize filters
        applyFiltersPractice();
        applyFiltersLevel();
    });
</script>

<style>
    .extreme-small {
        font-size: 0.65rem;
        letter-spacing: 0.05em;
    }
    #activity-report-table th, #activity-report-table-level th {
        position: sticky;
        top: 0;
        background-color: #e9ecef;
        z-index: 10;
    }
    #activity-report-table td, #activity-report-table th,
    #activity-report-table-level td, #activity-report-table-level th {
        vertical-align: top;
    }
    #activity-report-table td[rowspan], #activity-report-table-level td[rowspan] {
        vertical-align: middle;
        background-color: #f8f9fa;
    }
    .table-responsive {
        -webkit-overflow-scrolling: touch;
    }
    .level-separator-row td {
        font-size: 0.9rem;
        padding: 0.75rem 0.5rem;
    }
</style>
@endsection
