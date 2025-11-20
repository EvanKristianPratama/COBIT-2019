@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- Loading Overlay with Progress --}}
<div id="loading-overlay" class="loading-overlay">
    <div class="loading-content">
        <div class="loading-spinner"></div>
        <div class="loading-text" id="loading-text">Loading...</div>
        <div class="loading-progress-container">
            <div class="loading-progress-bar" id="loading-progress-bar"></div>
        </div>
        <div class="loading-percentage" id="loading-percentage">0%</div>
    </div>
</div>

{{-- Non-blocking Corner Loading Indicator --}}
<div id="corner-loading" class="corner-loading" style="display: none;">
    <div class="corner-loading-content">
        <div class="corner-spinner"></div>
        <div class="corner-loading-info">
            <div class="corner-loading-text" id="corner-loading-text">Loading data...</div>
            <div class="corner-loading-progress">
                <div class="corner-progress-bar" id="corner-progress-bar"></div>
            </div>
            <div class="corner-loading-percentage" id="corner-loading-percentage">0%</div>
        </div>
    </div>
</div>

<div class="container mx-auto p-6" id="page-top">
    {{-- Main Card --}}
    <div class="card shadow-sm mb-4 hero-card" style="border:none;box-shadow:0 22px 45px rgba(14,33,70,0.15);">
        <div class="card-header hero-header py-4" style="background:linear-gradient(135deg,#081a3d,#0f2b5c);color:#fff;border:none;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="hero-title" style="font-size:1.5rem;font-weight:700;letter-spacing:0.04em;">COBIT 2019 Assessment Evaluation</div>
                    <div class="hero-eval-id" style="font-size:1.05rem;font-weight:600;margin-top:0.25rem;letter-spacing:0.08em;text-transform:uppercase;color:rgba(255,255,255,0.85);">
                        Assessment Id: {{ $evalId }}
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">

            <div class="domain-tabs-wrapper">
                <div class="domain-tabs" role="tablist">
                    <button type="button" class="domain-tab active" data-domain="all">All Domains</button>
                    <button type="button" class="domain-tab" data-domain="EDM">EDM</button>
                    <button type="button" class="domain-tab" data-domain="APO">APO</button>
                    <button type="button" class="domain-tab" data-domain="BAI">BAI</button>
                    <button type="button" class="domain-tab" data-domain="DSS">DSS</button>
                    <button type="button" class="domain-tab" data-domain="MEA">MEA</button>
                    <button type="button" class="domain-tab" data-domain="recap">Rekap Domain</button>
                </div>
                <div id="objective-filter-wrapper" class="objective-filter-wrapper" style="display: none;">
                    <div class="objective-filter-tabs" id="objective-filter-tabs" role="tablist"></div>
                </div>
            </div>

            <div id="domain-overview-wrapper" class="domain-overview-wrapper mt-3" style="display: none;">
                <button class="domain-overview-toggle" type="button" id="domain-overview-toggle" aria-expanded="true">
                    <span><i class="fas fa-chart-line me-2"></i>Ringkasan Level Domain</span>
                    <i class="fas fa-chevron-up toggle-indicator"></i>
                </button>
                <div class="domain-level-card" id="domain-level-overview">
                    <div class="domain-level-header">
                        <div>
                            <h5 class="domain-level-title" id="domain-level-title"></h5>
                        </div>
                        <span class="domain-level-pill" id="domain-level-pill">EDM</span>
                    </div>
                    <div class="domain-level-table">
                        <div class="domain-level-table-head">
                            <span>Objective</span>
                            <span>Capability Progress</span>
                            <span class="text-end">Level Saat Ini</span>
                        </div>
                        <div id="domain-level-rows"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row" id="recap-standalone-row" style="display: none;">
        <div class="col-12">
            <div class="recap-standalone-wrapper" id="recap-standalone-wrapper">
                <div class="recap-standalone-header">
                    <div>
                        <h5 class="recap-title">Rekap Level Seluruh Gamo</h5>
                    </div>
                </div>
                <div id="recap-standalone-body"></div>
            </div>
        </div>
    </div>

    {{-- Objectives Cards --}}
    @php
        $domainOrderMap = ['EDM' => 1, 'APO' => 2, 'BAI' => 3, 'DSS' => 4, 'MEA' => 5];
        $sortedObjectives = collect($objectives)->sortBy(function($objective) use ($domainOrderMap) {
            $domain = preg_replace('/\d+/', '', $objective->objective_id);
            $domainRank = $domainOrderMap[$domain] ?? 99;
            return sprintf('%02d_%s', $domainRank, $objective->objective_id);
        });
    @endphp

    <div class="row" id="objectives-container">
        @foreach($sortedObjectives as $objective)
            @php
                $domain = preg_replace('/\d+/', '', $objective->objective_id);
            @endphp
            <div class="col-12 mb-4 objective-card" data-domain="{{ $domain }}" data-objective-id="{{ $objective->objective_id }}" data-objective-name="{{ $objective->objective }}">
                <div class="card shadow-sm h-100">
                    {{-- Card Header --}}
                    <div class="card-header objective-header py-3">
                        @php
                            $domainFullNames = [
                                'EDM' => 'Evaluate, Direct, and Monitor',
                                'APO' => 'Align, Plan, and Organize',
                                'BAI' => 'Build, Acquire, and Implement',
                                'DSS' => 'Deliver, Service, and Support',
                                'MEA' => 'Monitor, Evaluate, and Assess'
                            ];
                            $fullDomainName = $domainFullNames[$domain] ?? $domain;
                            $totalLevel2Activities = $objective->practices->sum(function($practice) {
                                return $practice->activities ? $practice->activities->where('capability_lvl', 2)->count() : 0;
                            });
                        @endphp
                        <div class="objective-hero d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="objective-title mb-1">{{ $objective->objective_id }} - {{ $objective->objective }}</h5>
                                <div class="objective-domain">{{ $fullDomainName }}</div>
                            </div>
                            <div class="objective-stats text-end">
                                <div class="objective-stat">{{ $totalLevel2Activities }} activities</div>
                                <div class="capability-level-display mt-2">
                                    <span class="capability-badge badge-level-1" id="capability-level-{{ $objective->objective_id }}">
                                        Level <span class="level-number">1</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        @if($objective->objective_description || $objective->objective_purpose)
                            <div class="objective-summary mt-3">
                                @if($objective->objective_description)
                                    <div class="summary-column">
                                        <div class="summary-label">Activity</div>
                                        <p class="objective-description-text mb-0">
                                            {{ $objective->objective_description }}
                                        </p>
                                    </div>
                                @endif
                                @if($objective->objective_purpose)
                                    <div class="summary-column">
                                        <div class="summary-label">Purpose</div>
                                        <p class="objective-purpose-text mb-0">
                                            {{ $objective->objective_purpose }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Card Footer with Multi-Level Assessment --}}
                    <div class="card-footer bg-light">
                        @php
                            $availableLevels = [];
                            foreach($objective->practices as $practice) {
                                if($practice->activities) {
                                    foreach($practice->activities as $activity) {
                                        if(!in_array($activity->capability_lvl, $availableLevels)) {
                                            $availableLevels[] = $activity->capability_lvl;
                                        }
                                    }
                                }
                            }
                            sort($availableLevels);
                            $minLevel = min($availableLevels ?? [2]);
                            $maxLevel = min(5, max($availableLevels ?? [2]));
                        @endphp
                        
                        @for($level = $minLevel; $level <= $maxLevel; $level++)
                            @php
                                $levelActivities = 0;
                                foreach($objective->practices as $practice) {
                                    if($practice->activities) {
                                        $levelActivities += $practice->activities->where('capability_lvl', $level)->count();
                                    }
                                }
                            @endphp
                            @if($levelActivities > 0)
                                <div class="capability-level-section mb-3" data-level="{{ $level }}">
                                    <div class="level-section-header">
                                        <div class="level-section-info">
                                            <span class="level-pill">Level {{ $level }}</span>
                                            <span class="level-section-subtext">{{ $levelActivities }} activities</span>
                                        </div>
                                        <div class="level-section-actions">
                                            <span class="capability-score level-score-chip" id="level-score-{{ $objective->objective_id }}-{{ $level }}">
                                                N (0.00)
                                            </span>
                                            <button class="btn btn-sm level-toggle-btn toggle-level-details" type="button" 
                                                    data-objective-id="{{ $objective->objective_id }}"
                                                    data-level="{{ $level }}"
                                                    data-min-level="{{ $minLevel }}"
                                                    data-required-previous="{{ $level > $minLevel ? 'true' : 'false' }}">
                                                <i class="fas me-1 fa-chevron-down toggle-icon"></i>
                                                <span class="toggle-text">Start Assessment</span>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    {{-- Assessment Section for this level --}}
                                    <div class="assessment-section mt-3" id="assessment-{{ $objective->objective_id }}-{{ $level }}" style="display: none;">

                                        {{-- Activities Assessment for this level --}}
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered align-middle assessment-table">
                                                    <thead>
                                                        <tr>
                                                            <th class="text-center" style="width: 55px;">No</th>
                                                            <th style="width: 120px;">Practice</th>
                                                            <th style="width: 220px;">Practice Name</th>
                                                            <th>Activity</th>
                                                            <th style="width: 160px;">Answer</th>
                                                            <th style="width: 220px;">Evidence</th>
                                                            <th style="width: 220px;">Notes</th>
                                                            <th class="text-center" style="width: 70px;">Level</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php 
                                                            $activityCounter = 1;
                                                            $levelCellRendered = false;
                                                        @endphp
                                                        @foreach($objective->practices as $practice)
                                                            @php
                                                                $levelSpecificActivities = $practice->activities ? $practice->activities->where('capability_lvl', $level) : collect();
                                                            @endphp
                                                            @foreach($levelSpecificActivities as $activity)
                                                                <tr class="activity-row">
                                                                    <td class="text-center fw-semibold">{{ $activityCounter }}</td>
                                                                    <td class="practice-code-cell text-center">
                                                                        <span class="practice-code-text">{{ trim($practice->practice_id, '"') }}</span>
                                                                    </td>
                                                                    <td class="practice-name-cell">
                                                                        <div class="fw-semibold">{{ trim($practice->practice_name, '"') }}</div>
                                                                    </td>
                                                                    <td class="description-cell">
                                                                        <p class="mb-1 fw-medium">{{ $activity->description }}</p>
                                                                    </td>
                                                                    <td class="rating-cell">
                                                                        <select 
                                                                            class="form-select form-select-sm activity-rating-select" 
                                                                            data-activity-id="{{ $activity->activity_id }}"
                                                                            data-objective-id="{{ $objective->objective_id }}"
                                                                            data-level="{{ $level }}">
                                                                            <option value="">Select Rating</option>
                                                                            <option value="N">None</option>
                                                                            <option value="P">Partial</option>
                                                                            <option value="L">Largely</option>
                                                                            <option value="F">Full</option>
                                                                        </select>
                                                                    </td>
                                                                    <td class="evidence-cell">
                                                                        <div class="evidence-input-wrapper">
                                                                            <textarea 
                                                                                class="form-control form-control-sm assessment-textarea evidence-input" 
                                                                                id="evidence_{{ $activity->activity_id }}" 
                                                                                name="evidence_{{ $activity->activity_id }}"
                                                                                data-field-type="evidence"
                                                                                data-activity-id="{{ $activity->activity_id }}"
                                                                                data-objective-id="{{ $objective->objective_id }}"
                                                                                data-level="{{ $level }}"
                                                                                rows="2" 
                                                                                placeholder="Enter evidence or document references..."></textarea>
                                                                            <select 
                                                                                class="form-select form-select-sm evidence-history-select"
                                                                                data-activity-id="{{ $activity->activity_id }}"
                                                                                data-objective-id="{{ $objective->objective_id }}"
                                                                                data-level="{{ $level }}"
                                                                                data-placeholder="Select saved evidence">
                                                                                <option value="">Select saved evidence...</option>
                                                                            </select>
                                                                        </div>
                                                                    </td>
                                                                    <td class="notes-cell">
                                                                        <textarea 
                                                                            class="form-control form-control-sm assessment-textarea note-input" 
                                                                            id="note_{{ $activity->activity_id }}" 
                                                                            name="note_{{ $activity->activity_id }}"
                                                                            data-field-type="note"
                                                                            data-activity-id="{{ $activity->activity_id }}"
                                                                            data-objective-id="{{ $objective->objective_id }}"
                                                                            data-level="{{ $level }}"
                                                                            rows="2" 
                                                                            placeholder="Enter additional notes or comments..."></textarea>
                                                                    </td>
                                                                    @if(!$levelCellRendered)
                                                                        <td class="level-cell text-center" rowspan="{{ $levelActivities }}">
                                                                            Level {{ $level }}
                                                                        </td>
                                                                        @php $levelCellRendered = true; @endphp
                                                                    @endif
                                                                </tr>
                                                                @php $activityCounter++; @endphp
                                                            @endforeach
                                                        @endforeach
                                                        @if($activityCounter === 1)
                                                            <tr>
                                                                <td colspan="8" class="text-center text-muted py-4">
                                                                    <i class="fas fa-info-circle me-2"></i>
                                                                    No practices with Level {{ $level }} activities found for assessment.
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    </tbody>
                                                </table>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endfor
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- No Results Message --}}
    <div id="no-results" class="text-center py-5" style="display: none;">
        <div class="card shadow-sm">
            <div class="card-body py-5">
                <i class="fas fa-search text-muted mb-3" style="font-size: 3rem;"></i>
                <h5 class="text-muted">No objectives found</h5>
                <p class="text-muted">Try selecting a different domain filter.</p>
            </div>
        </div>
    </div>
</div>

<div class="sticky-action-group">
    <a href="{{ route('assessment-eval.list') }}" class="sticky-action-btn btn btn-light" title="Back to List">
        <i class="fas fa-arrow-left me-2"></i>Back
    </a>
    <button type="button" class="sticky-action-btn btn btn-primary" id="save-assessment" title="Save Assessment">
        <i class="fas fa-save me-2"></i>Save
    </button>
    <button type="button" class="sticky-action-btn btn btn-light" id="back-to-top-btn" title="Back to Top">
        <i class="fas fa-arrow-up me-2"></i>Top
    </button>
    <a href="{{ url('/') }}" class="sticky-action-btn btn btn-light" title="Go to Home">
        <i class="fas fa-home me-2"></i>Home
    </a>
</div>

<script>
class COBITAssessmentManager {
    constructor(evalId) {
        this.assessmentData = {};
        this.levelScores = {};
        this.currentEvalId = evalId;
        this.evidenceLibrary = new Set();
        this.objectiveCapabilityLevels = {};
        this.activeDomainFilter = 'all';
        this.activeObjectiveFilter = 'all';
        this.domainChartContainer = null;
        this.domainChartWrapper = null;
        this.domainOverviewToggle = null;
        this.domainChartCollapsed = true;
        this.domainChartHasData = false;
        this.domainChartRows = null;
        this.domainChartTitle = null;
        this.domainChartPill = null;
        this.objectiveFilterWrapper = null;
        this.objectiveFilterTabs = null;
        this.domainObjectiveMap = {};
        this.objectiveCards = [];
        this.cache = {
            elements: new Map(),
            data: new Map()
        };
        this.elementCache = {
            ratingSelects: new Map(),
            evidenceInputs: new Map(),
            noteInputs: new Map(),
            evidenceDropdowns: new Map()
        };
        this.init();
    }

    init() {
        this.cacheLoadingElements();
        this.buildElementCache();
        this.showLoading('Initializing assessment...', 0);
        
        setTimeout(() => {
            this.cacheDomainChartElements();
            this.updateLoadingProgress('Loading components...', 10);
            
            this.cacheObjectiveFilterElements();
            this.updateLoadingProgress('Building domain map...', 20);
            
            this.buildDomainObjectiveMap();
            this.updateLoadingProgress('Setting up filters...', 30);
            
            this.setupDomainFiltering();
            this.updateLoadingProgress('Setting up assessment...', 40);
            
            this.setupAssessmentToggles();
            this.updateLoadingProgress('Setting up activity rating...', 50);
            
            this.setupActivityRating();
            this.updateLoadingProgress('Initializing states...', 60);
            
            this.initializeDefaultStates();
            this.updateLoadingProgress('Setting up buttons...', 70);
            
            this.setupSaveLoadButtons();
            this.setupDomainOverviewAccordion();
            this.setupBackToTopButton();
            this.updateLoadingProgress('Loading assessment data...', 80);
            
            this.loadAssessment();
            this.updateLoadingProgress('Finalizing...', 90);
            
            this.refreshEvidenceDropdowns();
            this.updateLoadingProgress('Complete!', 100);
            
            setTimeout(() => this.hideLoading(), 500);
        }, 100);
    }

    initializeDefaultStates() {
        const objectiveCards = document.querySelectorAll('.objective-card');
        objectiveCards.forEach(card => {
            const objectiveId = card.getAttribute('data-objective-id');
            if (!this.objectiveCapabilityLevels[objectiveId]) {
                this.objectiveCapabilityLevels[objectiveId] = 1;
            }
            const levelSections = card.querySelectorAll('.capability-level-section');
            
            levelSections.forEach(section => {
                const level = parseInt(section.getAttribute('data-level'));
                this.initializeLevelScore(objectiveId, level);
                this.updateLevelDisplay(objectiveId, level);
                this.checkLevelLock(objectiveId, level);
            });
            
            // Update overall objective capability level
            this.updateObjectiveCapabilityLevel(objectiveId);
        });
    }

    resetAssessmentFields() {
        this.levelScores = {};
        this.objectiveCapabilityLevels = {};
        this.evidenceLibrary = new Set();

        if (this.elementCache.ratingSelects) {
            this.elementCache.ratingSelects.forEach(select => {
                select.value = '';
                select.classList.remove('rating-full', 'rating-high', 'rating-medium', 'rating-low');
            });
        }

        if (this.elementCache.evidenceInputs) {
            this.elementCache.evidenceInputs.forEach(textarea => {
                textarea.value = '';
            });
        }

        if (this.elementCache.noteInputs) {
            this.elementCache.noteInputs.forEach(textarea => {
                textarea.value = '';
            });
        }

        this.initializeDefaultStates();
    }

    initializeLevelScore(objectiveId, level) {
        if (!this.levelScores[objectiveId]) {
            this.levelScores[objectiveId] = {};
        }
        if (!this.levelScores[objectiveId][level]) {
            this.levelScores[objectiveId][level] = {
                letter: 'N',
                score: 0.00,
                activities: {},
                evidence: {},
                notes: {}
            };
        }
    }

    setupSaveLoadButtons() {
        const saveBtn = document.getElementById('save-assessment');
        const loadBtn = document.getElementById('load-assessment');

        if (saveBtn) {
            saveBtn.addEventListener('click', () => this.saveAssessment());
        }

        if (loadBtn) {
            loadBtn.addEventListener('click', () => this.loadAssessment(true));
        }
    }

    cacheLoadingElements() {
        this.loadingOverlay = document.getElementById('loading-overlay');
        this.loadingText = document.getElementById('loading-text');
        this.loadingProgressBar = document.getElementById('loading-progress-bar');
        this.loadingPercentage = document.getElementById('loading-percentage');
        this.cornerLoading = document.getElementById('corner-loading');
        this.cornerLoadingText = document.getElementById('corner-loading-text');
        this.cornerProgressBar = document.getElementById('corner-progress-bar');
        this.cornerLoadingPercentage = document.getElementById('corner-loading-percentage');
    }

    buildElementCache() {
        // Cache all rating selects once
        document.querySelectorAll('.activity-rating-select').forEach(el => {
            this.elementCache.ratingSelects.set(el.dataset.activityId, el);
        });
        
        // Cache all evidence inputs
        document.querySelectorAll('.evidence-input').forEach(el => {
            this.elementCache.evidenceInputs.set(el.dataset.activityId, el);
        });
        
        // Cache all note inputs
        document.querySelectorAll('.note-input').forEach(el => {
            this.elementCache.noteInputs.set(el.dataset.activityId, el);
        });

        document.querySelectorAll('.evidence-history-select').forEach(el => {
            this.elementCache.evidenceDropdowns.set(el.dataset.activityId, el);
        });
        
        console.log('Element cache built:', {
            ratings: this.elementCache.ratingSelects.size,
            evidence: this.elementCache.evidenceInputs.size,
            notes: this.elementCache.noteInputs.size,
            evidenceDropdowns: this.elementCache.evidenceDropdowns.size
        });
    }

    getEvidenceSelectElements() {
        if (this.elementCache && this.elementCache.evidenceDropdowns.size) {
            return Array.from(this.elementCache.evidenceDropdowns.values());
        }
        return Array.from(document.querySelectorAll('.evidence-history-select'));
    }

    showLoading(message = 'Loading...', percentage = 0) {
        if (this.loadingOverlay) {
            this.loadingOverlay.style.display = 'flex';
            if (this.loadingText) this.loadingText.textContent = message;
            if (this.loadingProgressBar) this.loadingProgressBar.style.width = percentage + '%';
            if (this.loadingPercentage) this.loadingPercentage.textContent = Math.round(percentage) + '%';
        }
    }

    updateLoadingProgress(message, percentage) {
        if (this.loadingText) this.loadingText.textContent = message;
        if (this.loadingProgressBar) this.loadingProgressBar.style.width = percentage + '%';
        if (this.loadingPercentage) this.loadingPercentage.textContent = Math.round(percentage) + '%';
    }

    hideLoading() {
        if (this.loadingOverlay) {
            this.loadingOverlay.style.opacity = '0';
            setTimeout(() => {
                this.loadingOverlay.style.display = 'none';
                this.loadingOverlay.style.opacity = '1';
            }, 300);
        }
    }

    showCornerLoading(message = 'Loading...', percentage = 0) {
        if (this.cornerLoading) {
            this.cornerLoading.style.display = 'flex';
            this.cornerLoading.classList.add('show');
            if (this.cornerLoadingText) this.cornerLoadingText.textContent = message;
            if (this.cornerProgressBar) this.cornerProgressBar.style.width = percentage + '%';
            if (this.cornerLoadingPercentage) this.cornerLoadingPercentage.textContent = Math.round(percentage) + '%';
        }
    }

    updateCornerProgress(message, percentage) {
        if (this.cornerLoadingText) this.cornerLoadingText.textContent = message;
        if (this.cornerProgressBar) this.cornerProgressBar.style.width = percentage + '%';
        if (this.cornerLoadingPercentage) this.cornerLoadingPercentage.textContent = Math.round(percentage) + '%';
    }

    hideCornerLoading() {
        if (this.cornerLoading) {
            this.cornerLoading.classList.remove('show');
            setTimeout(() => {
                this.cornerLoading.style.display = 'none';
            }, 400);
        }
    }

    cacheDomainChartElements() {
        this.domainChartWrapper = document.getElementById('domain-overview-wrapper');
        this.domainChartContainer = document.getElementById('domain-level-overview');
        this.domainChartRows = document.getElementById('domain-level-rows');
        this.domainChartTitle = document.getElementById('domain-level-title');
        this.domainChartPill = document.getElementById('domain-level-pill');
        this.domainOverviewToggle = document.getElementById('domain-overview-toggle');
        this.domainChartTable = document.querySelector('.domain-level-table');
        this.recapStandaloneRow = document.getElementById('recap-standalone-row');
        this.recapStandaloneWrapper = document.getElementById('recap-standalone-wrapper');
        this.recapStandaloneBody = document.getElementById('recap-standalone-body');
    }

    cacheObjectiveFilterElements() {
        this.objectiveFilterWrapper = document.getElementById('objective-filter-wrapper');
        this.objectiveFilterTabs = document.getElementById('objective-filter-tabs');
    }

    buildDomainObjectiveMap() {
        this.objectiveCards = Array.from(document.querySelectorAll('.objective-card'));
        this.domainObjectiveMap = {};

        this.objectiveCards.forEach(card => {
            const domain = card.getAttribute('data-domain');
            const objectiveId = card.getAttribute('data-objective-id');
            const objectiveName = card.getAttribute('data-objective-name') || objectiveId;

            if (!domain || !objectiveId) {
                return;
            }

            if (!this.domainObjectiveMap[domain]) {
                this.domainObjectiveMap[domain] = [];
            }

            this.domainObjectiveMap[domain].push({
                id: objectiveId,
                name: objectiveName
            });
        });

        Object.keys(this.domainObjectiveMap).forEach(domainKey => {
            this.domainObjectiveMap[domainKey].sort((a, b) => a.id.localeCompare(b.id, undefined, { numeric: true }));
        });
    }

    renderObjectiveFilterTabs(domain) {
        if (!this.objectiveFilterWrapper || !this.objectiveFilterTabs) {
            return;
        }

        if (domain === 'all' || domain === 'recap' || !this.domainObjectiveMap[domain] || this.domainObjectiveMap[domain].length === 0) {
            this.objectiveFilterWrapper.style.display = 'none';
            this.objectiveFilterTabs.innerHTML = '';
            this.activeObjectiveFilter = 'all';
            return;
        }

        this.objectiveFilterWrapper.style.display = 'block';
        this.activeObjectiveFilter = 'all';

        const objectives = this.domainObjectiveMap[domain];
        const tabsHtml = [
            `<button type="button" class="objective-filter-tab active" data-objective="all">
                All
            </button>`
        ];

        objectives.forEach(obj => {
            const safeCode = this.escapeHtml(obj.id);
            tabsHtml.push(`
                <button type="button" class="objective-filter-tab" data-objective="${safeCode}">
                    ${safeCode}
                </button>
            `);
        });

        this.objectiveFilterTabs.innerHTML = tabsHtml.join('');
        const buttons = this.objectiveFilterTabs.querySelectorAll('.objective-filter-tab');

        buttons.forEach(button => {
            button.addEventListener('click', () => {
                buttons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                const objectiveId = button.getAttribute('data-objective');
                this.applyObjectiveFilter(objectiveId);
            });
        });
    }

    applyObjectiveFilter(objectiveId) {
        this.activeObjectiveFilter = objectiveId || 'all';
        this.updateObjectiveVisibility();
    }

    updateObjectiveVisibility() {
        const cards = this.objectiveCards.length ? this.objectiveCards : Array.from(document.querySelectorAll('.objective-card'));
        const noResultsDiv = document.getElementById('no-results');
        let visibleCount = 0;

        if (this.activeDomainFilter === 'recap') {
            cards.forEach(card => {
                card.style.display = 'none';
            });
            if (noResultsDiv) {
                noResultsDiv.style.display = 'none';
            }
            return;
        }

        cards.forEach(card => {
            const cardDomain = card.getAttribute('data-domain');
            const cardObjective = card.getAttribute('data-objective-id');
            const matchesDomain = this.activeDomainFilter === 'all'
                || this.activeDomainFilter === 'recap'
                || cardDomain === this.activeDomainFilter;
            const matchesObjective = this.activeObjectiveFilter === 'all' || cardObjective === this.activeObjectiveFilter;

            if (matchesDomain && matchesObjective) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (noResultsDiv) {
            noResultsDiv.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    }

    setupDomainOverviewAccordion() {
        if (!this.domainOverviewToggle) {
            return;
        }
        this.domainOverviewToggle.addEventListener('click', () => {
            this.domainChartCollapsed = !this.domainChartCollapsed;
            this.updateDomainOverviewVisibility();
        });
    }

    updateDomainOverviewVisibility() {
        if (!this.domainChartWrapper || !this.domainChartContainer || !this.domainOverviewToggle) {
            return;
        }
        if (!this.domainChartHasData) {
            this.domainChartWrapper.style.display = 'none';
            return;
        }
        this.domainChartWrapper.style.display = 'block';
        if (this.domainChartCollapsed) {
            this.domainChartWrapper.classList.add('collapsed');
            this.domainChartContainer.style.display = 'none';
            this.domainOverviewToggle.setAttribute('aria-expanded', 'false');
        } else {
            this.domainChartWrapper.classList.remove('collapsed');
            this.domainChartContainer.style.display = 'block';
            this.domainOverviewToggle.setAttribute('aria-expanded', 'true');
        }
    }

    setupBackToTopButton() {
        const backToTopBtn = document.getElementById('back-to-top-btn');
        if (!backToTopBtn) {
            return;
        }
        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    async saveAssessment() {
        this.showLoading('Collecting assessment data...', 0);
        
        try {
            await new Promise(resolve => setTimeout(resolve, 100));
            this.updateLoadingProgress('Preparing data...', 20);
            
            const fieldPayload = this.collectFieldData();
            this.updateLoadingProgress('Building payload...', 40);
            
            const assessmentData = {
                assessmentData: this.levelScores,
                notes: fieldPayload.notes,
                evidence: fieldPayload.evidence
            };

            this.updateLoadingProgress('Saving to server...', 60);
            
            const response = await fetch(`/assessment-eval/${this.currentEvalId}/save`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(assessmentData)
            });

            this.updateLoadingProgress('Processing response...', 90);
            const result = await response.json();

            if (response.ok) {
                this.updateLoadingProgress('Saved successfully!', 100);
                setTimeout(() => {
                    this.hideLoading();
                    this.showNotification('Assessment saved successfully!', 'success');
                }, 500);
            } else {
                this.hideLoading();
                this.showNotification(result.message || 'Failed to save assessment', 'error');
            }
        } catch (error) {
            console.error('Save error:', error);
            this.hideLoading();
            this.showNotification('Failed to save assessment', 'error');
        }
    }

    async loadAssessment(triggeredManually = false) {
        const showLoadingFn = triggeredManually ? this.showLoading.bind(this) : this.showCornerLoading.bind(this);
        const updateProgressFn = triggeredManually ? this.updateLoadingProgress.bind(this) : this.updateCornerProgress.bind(this);
        const hideLoadingFn = triggeredManually ? this.hideLoading.bind(this) : this.hideCornerLoading.bind(this);

        showLoadingFn('Fetching assessment data...', 5);
        
        try {
            updateProgressFn('Requesting data from server...', 15);

            const response = await fetch(`/assessment-eval/${this.currentEvalId}/load`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const result = await response.json();

            if (!response.ok || !result.data) {
                hideLoadingFn();
                if (triggeredManually) {
                    this.showNotification(result.message || 'Failed to load assessment', 'error');
                }
                return;
            }

            const data = result.data;
            updateProgressFn('Resetting assessment fields...', 30);
            this.resetAssessmentFields();

            const aggregatedEvidence = {};
            const aggregatedNotes = {};
            const payloadKeys = new Set();
            if (data.notes) {
                Object.keys(data.notes).forEach(key => payloadKeys.add(key));
            }
            if (data.evidence) {
                Object.keys(data.evidence).forEach(key => payloadKeys.add(key));
            }

            payloadKeys.forEach(activityId => {
                const parsed = this.parseNotePayload(
                    data.notes ? data.notes[activityId] : null,
                    data.evidence ? data.evidence[activityId] : null
                );
                if (parsed.evidence !== undefined) {
                    aggregatedEvidence[activityId] = parsed.evidence || '';
                }
                if (parsed.note !== undefined) {
                    aggregatedNotes[activityId] = parsed.note || '';
                }
            });
            const activityEntries = data.activityData ? Object.entries(data.activityData) : [];

            activityEntries.forEach(([activityId, entry]) => {
                const parsed = this.parseNotePayload(entry.notes, entry.evidence);
                if (parsed.evidence !== undefined) {
                    aggregatedEvidence[activityId] = parsed.evidence || '';
                }
                if (parsed.note !== undefined) {
                    aggregatedNotes[activityId] = parsed.note || '';
                }
            });

            updateProgressFn('Applying evidence and notes...', 55);
            Object.entries(aggregatedEvidence).forEach(([activityId, evidenceValue]) => {
                const textarea = this.elementCache.evidenceInputs.get(activityId);
                if (textarea) {
                    textarea.value = evidenceValue || '';
                }
                const meta = this.getActivityMeta(activityId);
                if (meta) {
                    this.setActivityEvidence(meta.objectiveId, meta.level, activityId, evidenceValue || '');
                }
                this.addEvidenceToLibrary(evidenceValue, { refresh: false });
            });

            Object.entries(aggregatedNotes).forEach(([activityId, noteValue]) => {
                const textarea = this.elementCache.noteInputs.get(activityId);
                if (textarea) {
                    textarea.value = noteValue || '';
                }
                const meta = this.getActivityMeta(activityId);
                if (meta) {
                    this.setActivityNote(meta.objectiveId, meta.level, activityId, noteValue || '');
                }
            });

            updateProgressFn('Building evidence dropdowns...', 70);
            await this.refreshEvidenceDropdowns();

            updateProgressFn('Applying ratings...', 85);
            activityEntries.forEach(([activityId, entry]) => {
                const objectiveId = entry.objective_id;
                const capabilityLevel = entry.capability_lvl;
                const levelAchieved = entry.level_achieved;
                const ratingSelect = this.elementCache.ratingSelects.get(activityId);

                if (ratingSelect) {
                    ratingSelect.value = levelAchieved || '';
                }

                if (objectiveId && capabilityLevel) {
                    this.setActivityRating(objectiveId, capabilityLevel, activityId, levelAchieved);
                    if (entry.evidence) {
                        this.setActivityEvidence(objectiveId, capabilityLevel, activityId, entry.evidence);
                    }
                    if (entry.notes) {
                        this.setActivityNote(objectiveId, capabilityLevel, activityId, entry.notes);
                    }
                }

                this.updateActivityScore(activityId, levelAchieved);
            });

            updateProgressFn('Finalizing calculations...', 95);
            await this.updateAllCalculationsParallel();

            updateProgressFn('Complete!', 100);
            hideLoadingFn();
            if (triggeredManually) {
                this.showNotification('Assessment loaded successfully!', 'success');
            }
        } catch (error) {
            console.error('Load error:', error);
            hideLoadingFn();
            if (triggeredManually) {
                this.showNotification('Failed to load assessment', 'error');
            }
        }
    }

    async populateAssessmentDataParallel(data) {
        this.levelScores = {};
        this.evidenceLibrary = new Set();
        
        // Clear fields dengan chunk size lebih kecil untuk smooth interaction
        const allSelects = document.querySelectorAll('.activity-rating-select');
        const allTextareas = document.querySelectorAll('.assessment-textarea');
        const chunkSize = 25; // Smaller chunks = more responsive
        
        // Clear selects
        for (let i = 0; i < allSelects.length; i += chunkSize) {
            const chunk = Array.from(allSelects).slice(i, i + chunkSize);
            chunk.forEach(select => select.value = '');
            await new Promise(resolve => requestAnimationFrame(() => setTimeout(resolve, 0)));
        }
        
        // Clear textareas
        for (let i = 0; i < allTextareas.length; i += chunkSize) {
            const chunk = Array.from(allTextareas).slice(i, i + chunkSize);
            chunk.forEach(textarea => textarea.value = '');
            await new Promise(resolve => requestAnimationFrame(() => setTimeout(resolve, 0)));
        }

        this.updateCornerProgress('Initializing scores...', 55);
        
        // Initialize level scores
        const objectiveCards = document.querySelectorAll('.objective-card');
        for (const card of objectiveCards) {
            const objectiveId = card.getAttribute('data-objective-id');
            const levelSections = card.querySelectorAll('.capability-level-section');
            
            levelSections.forEach(section => {
                const level = parseInt(section.getAttribute('data-level'));
                this.initializeLevelScore(objectiveId, level);
                
                const activityInputs = section.querySelectorAll('.activity-rating-select');
                activityInputs.forEach(input => {
                    const activityId = input.getAttribute('data-activity-id');
                    this.levelScores[objectiveId][level].activities[activityId] = 0;
                });
            });
            await new Promise(resolve => requestAnimationFrame(() => setTimeout(resolve, 0)));
        }

        this.updateCornerProgress('Loading notes and evidence...', 65);
        
        // Load notes and evidence
        if (data.notes || data.evidence) {
            const activityIds = new Set();
            if (data.notes) Object.keys(data.notes).forEach(id => activityIds.add(id));
            if (data.evidence) Object.keys(data.evidence).forEach(id => activityIds.add(id));

            const activityIdsArray = Array.from(activityIds);
            for (let i = 0; i < activityIdsArray.length; i += chunkSize) {
                const chunk = activityIdsArray.slice(i, i + chunkSize);
                
                chunk.forEach(activityId => {
                    const parsedNotes = this.parseNotePayload(
                        data.notes ? data.notes[activityId] : null,
                        data.evidence ? data.evidence[activityId] : null
                    );

                    // Use cached elements instead of querySelector
                    const evidenceField = this.elementCache.evidenceInputs.get(activityId);
                    const noteField = this.elementCache.noteInputs.get(activityId);
                    if (evidenceField) evidenceField.value = parsedNotes.evidence || '';
                    if (noteField) noteField.value = parsedNotes.note || '';
                    this.addEvidenceToLibrary(parsedNotes.evidence, { refresh: false });
                });
                
                await new Promise(resolve => requestAnimationFrame(() => setTimeout(resolve, 0)));
            }
        }

        this.updateCornerProgress('Loading activity ratings...', 75);
        
        // Load activity data
        if (data.activityData) {
            const activityIds = Object.keys(data.activityData);
            const totalActivities = activityIds.length;
            
            for (let i = 0; i < activityIds.length; i += chunkSize) {
                const chunk = activityIds.slice(i, i + chunkSize);
                
                chunk.forEach(activityId => {
                    const activityData = data.activityData[activityId];
                    const levelAchieved = activityData.level_achieved;
                    const capabilityLevel = activityData.capability_lvl;
                    const objectiveId = activityData.objective_id;
                    
                    const ratingSelect = document.querySelector(`select.activity-rating-select[data-activity-id="${activityId}"]`);
                    if (ratingSelect && objectiveId && capabilityLevel) {
                        ratingSelect.value = levelAchieved;
                        
                        if (this.levelScores[objectiveId] && this.levelScores[objectiveId][capabilityLevel]) {
                            this.levelScores[objectiveId][capabilityLevel].activities[activityId] = this.getRatingValue(levelAchieved);
                            
                            if (activityData.notes || activityData.evidence) {
                                const parsedNotes = this.parseNotePayload(activityData.notes, activityData.evidence);
                                this.levelScores[objectiveId][capabilityLevel].evidence[activityId] = parsedNotes.evidence || '';
                                this.levelScores[objectiveId][capabilityLevel].notes[activityId] = parsedNotes.note || '';
                                this.addEvidenceToLibrary(parsedNotes.evidence, { refresh: false });
                            }
                            
                            this.updateActivityScore(activityId, levelAchieved);
                        }
                    }
                });
                
                // Update progress dynamically
                const progress = 75 + Math.round((i / totalActivities) * 15);
                this.updateCornerProgress(`Loading: ${Math.min(i + chunk.length, totalActivities)}/${totalActivities} activities`, progress);
                
                // Use requestAnimationFrame for smoother updates
                await new Promise(resolve => requestAnimationFrame(() => setTimeout(resolve, 0)));
            }
        }

        this.updateCornerProgress('Refreshing dropdowns...', 92);
        await this.refreshEvidenceDropdownsParallel();
        
        this.updateCornerProgress('Calculating scores...', 96);
        await this.updateAllCalculationsParallel();
    }

    async populateAssessmentData(data) {
        this.levelScores = {};
        this.evidenceLibrary = new Set();
        
        const allSelects = document.querySelectorAll('.activity-rating-select');
        const allTextareas = document.querySelectorAll('.assessment-textarea');
        
        // Clear in chunks untuk avoid blocking
        const chunkSize = 100;
        for (let i = 0; i < allSelects.length; i += chunkSize) {
            const chunk = Array.from(allSelects).slice(i, i + chunkSize);
            chunk.forEach(select => select.value = '');
            if (i % 200 === 0) {
                await new Promise(resolve => setTimeout(resolve, 0));
            }
        }
        
        for (let i = 0; i < allTextareas.length; i += chunkSize) {
            const chunk = Array.from(allTextareas).slice(i, i + chunkSize);
            chunk.forEach(textarea => textarea.value = '');
            if (i % 200 === 0) {
                await new Promise(resolve => setTimeout(resolve, 0));
            }
        }

        const objectiveCards = document.querySelectorAll('.objective-card');
        objectiveCards.forEach(card => {
            const objectiveId = card.getAttribute('data-objective-id');
            const levelSections = card.querySelectorAll('.capability-level-section');
            
            levelSections.forEach(section => {
                const level = parseInt(section.getAttribute('data-level'));
                this.initializeLevelScore(objectiveId, level);
                
                const activityInputs = section.querySelectorAll('.activity-rating-select');
                const activityIds = new Set();
                activityInputs.forEach(input => {
                    const activityId = input.getAttribute('data-activity-id');
                    activityIds.add(activityId);
                });
                
                activityIds.forEach(activityId => {
                    this.levelScores[objectiveId][level].activities[activityId] = 0;
                });
            });
        });

        if (data.notes || data.evidence) {
            const activityIds = new Set();
            if (data.notes) {
                Object.keys(data.notes).forEach(id => activityIds.add(id));
            }
            if (data.evidence) {
                Object.keys(data.evidence).forEach(id => activityIds.add(id));
            }

            activityIds.forEach(activityId => {
                const parsedNotes = this.parseNotePayload(
                    data.notes ? data.notes[activityId] : null,
                    data.evidence ? data.evidence[activityId] : null
                );

                const evidenceField = document.querySelector(`textarea.evidence-input[data-activity-id="${activityId}"]`);
                const noteField = document.querySelector(`textarea.note-input[data-activity-id="${activityId}"]`);
                if (evidenceField) {
                    evidenceField.value = parsedNotes.evidence || '';
                }
                if (noteField) {
                    noteField.value = parsedNotes.note || '';
                }
                this.addEvidenceToLibrary(parsedNotes.evidence, { refresh: false });
            });
        }

        if (data.activityData) {
            const activityIds = Object.keys(data.activityData);
            const totalActivities = activityIds.length;
            const chunkSize = 50;
            
            for (let i = 0; i < activityIds.length; i += chunkSize) {
                const chunk = activityIds.slice(i, i + chunkSize);
                
                chunk.forEach(activityId => {
                    const activityData = data.activityData[activityId];
                    const levelAchieved = activityData.level_achieved;
                    const capabilityLevel = activityData.capability_lvl;
                    const objectiveId = activityData.objective_id;
                    
                    const ratingSelect = document.querySelector(`select.activity-rating-select[data-activity-id="${activityId}"]`);
                    if (ratingSelect && objectiveId && capabilityLevel) {
                        ratingSelect.value = levelAchieved;
                        
                        if (this.levelScores[objectiveId] && this.levelScores[objectiveId][capabilityLevel]) {
                            this.levelScores[objectiveId][capabilityLevel].activities[activityId] = this.getRatingValue(levelAchieved);
                            
                            if (activityData.notes || activityData.evidence) {
                                const parsedNotes = this.parseNotePayload(activityData.notes, activityData.evidence);
                                this.levelScores[objectiveId][capabilityLevel].evidence[activityId] = parsedNotes.evidence || '';
                                this.levelScores[objectiveId][capabilityLevel].notes[activityId] = parsedNotes.note || '';
                                this.addEvidenceToLibrary(parsedNotes.evidence, { refresh: false });
                            }
                            
                            this.updateActivityScore(activityId, levelAchieved);
                        }
                    }
                });
                
                // Update progress
                const progress = 70 + Math.round((i / totalActivities) * 20);
                this.updateLoadingProgress(`Loading activities: ${i + chunk.length}/${totalActivities}`, progress);
                
                // Yield to browser untuk avoid freeze
                await new Promise(resolve => setTimeout(resolve, 0));
            }
        }

        this.updateLoadingProgress('Refreshing dropdowns...', 92);
        await this.refreshEvidenceDropdowns();
        
        this.updateLoadingProgress('Calculating scores...', 96);
        await this.updateAllCalculations();
    }

    async updateAllCalculationsParallel() {
        const objectiveCards = document.querySelectorAll('.objective-card');
        const cardsArray = Array.from(objectiveCards);
        
        for (let i = 0; i < cardsArray.length; i++) {
            const card = cardsArray[i];
            const objectiveId = card.getAttribute('data-objective-id');
            const levelSections = card.querySelectorAll('.capability-level-section');
            
            levelSections.forEach(section => {
                const level = parseInt(section.getAttribute('data-level'));
                this.updateLevelCapability(objectiveId, level);
                this.checkLevelLock(objectiveId, level);
            });
            
            this.updateObjectiveCapabilityLevel(objectiveId);
            
            // Yield every 3 objectives for smoother interaction
            if (i % 3 === 0) {
                await new Promise(resolve => requestAnimationFrame(() => setTimeout(resolve, 0)));
            }
        }

        this.updateDomainChart(this.activeDomainFilter);
    }

    async updateAllCalculations() {
        const objectiveCards = document.querySelectorAll('.objective-card');
        const cardsArray = Array.from(objectiveCards);
        
        for (let i = 0; i < cardsArray.length; i++) {
            const card = cardsArray[i];
            const objectiveId = card.getAttribute('data-objective-id');
            const levelSections = card.querySelectorAll('.capability-level-section');
            
            levelSections.forEach(section => {
                const level = parseInt(section.getAttribute('data-level'));
                this.updateLevelCapability(objectiveId, level);
                this.checkLevelLock(objectiveId, level);
            });
            
            // Update overall objective capability level
            this.updateObjectiveCapabilityLevel(objectiveId);
            
            // Yield setiap 5 objectives
            if (i % 5 === 0) {
                await new Promise(resolve => setTimeout(resolve, 0));
            }
        }

        this.updateDomainChart(this.activeDomainFilter);
    }

    collectFieldData() {
        const notes = {};
        const evidence = {};

        document.querySelectorAll('.assessment-textarea[data-activity-id]').forEach(field => {
            const activityId = field.getAttribute('data-activity-id');
            const fieldType = field.getAttribute('data-field-type');
            const value = field.value.trim();

            if (!value) {
                return;
            }

            if (fieldType === 'note') {
                notes[activityId] = value;
            } else {
                evidence[activityId] = value;
            }
        });

        return { notes, evidence };
    }

    escapeHtml(value) {
        if (value === null || value === undefined) {
            return '';
        }
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }

    setupDomainFiltering() {
        const filterButtons = document.querySelectorAll('.domain-tab');
        this.objectiveCards = this.objectiveCards.length ? this.objectiveCards : Array.from(document.querySelectorAll('.objective-card'));
        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                filterButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');

                const selectedDomain = button.getAttribute('data-domain');
                this.activeDomainFilter = selectedDomain;
                this.activeObjectiveFilter = 'all';
                this.renderObjectiveFilterTabs(selectedDomain);
                this.updateObjectiveVisibility();
                this.updateDomainChart(selectedDomain);
            });
        });

        this.renderObjectiveFilterTabs(this.activeDomainFilter);
        this.updateObjectiveVisibility();
    }

    setupAssessmentToggles() {
        const toggleButtons = document.querySelectorAll('.toggle-level-details');
        
        toggleButtons.forEach(button => {
            button.addEventListener('click', () => {
                const objectiveId = button.getAttribute('data-objective-id');
                const level = button.getAttribute('data-level');
                const assessmentSection = document.getElementById(`assessment-${objectiveId}-${level}`);
                const icon = button.querySelector('.toggle-icon');
                const text = button.querySelector('.toggle-text');
                
                if (text.textContent === 'Locked') {
                    return;
                }
                
                if (assessmentSection.style.display === 'none' || !assessmentSection.style.display) {
                    assessmentSection.style.display = 'block';
                    assessmentSection.style.animation = 'slideDown 0.3s ease-out';
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                    text.textContent = 'Hide Assessment';
                } else {
                    assessmentSection.style.display = 'none';
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                    text.textContent = 'Start Assessment';
                }
            });
        });
    }

    setupActivityRating() {
        const ratingInputs = document.querySelectorAll('.activity-rating-select');
        const evidenceTextareas = document.querySelectorAll('.evidence-input');
        const noteTextareas = document.querySelectorAll('.note-input');
        const evidenceSelects = this.getEvidenceSelectElements();
        
        ratingInputs.forEach(select => {
            select.addEventListener('change', () => {
                const activityId = select.getAttribute('data-activity-id');
                const objectiveId = select.getAttribute('data-objective-id');
                const level = parseInt(select.getAttribute('data-level'));
                const rating = select.value;
                
                if (rating) {
                    this.setActivityRating(objectiveId, level, activityId, rating);
                    this.updateActivityScore(activityId, rating);
                } else {
                    this.clearActivityRating(objectiveId, level, activityId);
                    this.updateActivityScore(activityId, null);
                }
                this.updateLevelCapability(objectiveId, level);
                this.checkAllLevelLocks(objectiveId);
            });
        });

        evidenceTextareas.forEach(textarea => {
            textarea.addEventListener('input', () => {
                const activityId = textarea.getAttribute('data-activity-id');
                const objectiveId = textarea.getAttribute('data-objective-id');
                const level = parseInt(textarea.getAttribute('data-level'));
                const evidence = textarea.value;
                
                this.setActivityEvidence(objectiveId, level, activityId, evidence);
            });
        });

        noteTextareas.forEach(textarea => {
            textarea.addEventListener('input', () => {
                const activityId = textarea.getAttribute('data-activity-id');
                const objectiveId = textarea.getAttribute('data-objective-id');
                const level = parseInt(textarea.getAttribute('data-level'));
                const note = textarea.value;

                this.setActivityNote(objectiveId, level, activityId, note);
            });
        });

        evidenceSelects.forEach(select => {
            select.addEventListener('change', () => {
                const selectedEvidence = select.value;
                if (!selectedEvidence) {
                    return;
                }
                const wrapper = select.closest('.evidence-input-wrapper');
                const textarea = wrapper ? wrapper.querySelector('.evidence-input') : null;
                if (textarea) {
                    textarea.value = selectedEvidence;
                    textarea.dispatchEvent(new Event('input', { bubbles: true }));
                }
            });
        });
    }

    setActivityRating(objectiveId, level, activityId, rating) {
        this.initializeLevelScore(objectiveId, level);
        if (!rating) {
            delete this.levelScores[objectiveId][level].activities[activityId];
            return;
        }
        this.levelScores[objectiveId][level].activities[activityId] = this.getRatingValue(rating);
    }

    setActivityEvidence(objectiveId, level, activityId, evidence) {
        this.initializeLevelScore(objectiveId, level);
        this.levelScores[objectiveId][level].evidence[activityId] = evidence;
    }

    setActivityNote(objectiveId, level, activityId, note) {
        this.initializeLevelScore(objectiveId, level);
        this.levelScores[objectiveId][level].notes[activityId] = note;
    }

    clearActivityRating(objectiveId, level, activityId) {
        if (this.levelScores[objectiveId] && this.levelScores[objectiveId][level]) {
            delete this.levelScores[objectiveId][level].activities[activityId];
            delete this.levelScores[objectiveId][level].evidence[activityId];
            delete this.levelScores[objectiveId][level].notes[activityId];
            
            const evidenceTextarea = document.getElementById(`evidence_${activityId}`);
            if (evidenceTextarea) {
                evidenceTextarea.value = '';
            }

            const noteTextarea = document.getElementById(`note_${activityId}`);
            if (noteTextarea) {
                noteTextarea.value = '';
            }
        }
    }

    updateActivityScore(activityId, rating) {
        // Use cached element
        const ratingSelect = this.elementCache.ratingSelects.get(activityId);
        if (ratingSelect) {
            ratingSelect.classList.remove('rating-full', 'rating-high', 'rating-medium', 'rating-low');
            if (rating === 'F') {
                ratingSelect.classList.add('rating-full');
            } else if (rating === 'L') {
                ratingSelect.classList.add('rating-high');
            } else if (rating === 'P') {
                ratingSelect.classList.add('rating-medium');
            } else if (rating === 'N') {
                ratingSelect.classList.add('rating-low');
            }
        }
    }

    updateLevelCapability(objectiveId, level) {
        const levelData = this.levelScores[objectiveId][level];
        const totalActivities = this.getTotalActivitiesForLevel(objectiveId, level);
        const ratedActivities = Object.keys(levelData.activities).length;
        
        let totalScore = 0;
        if (levelData.activities) {
            totalScore = Object.values(levelData.activities).reduce((sum, score) => sum + score, 0);
        }
        const averageScore = totalActivities > 0 ? totalScore / totalActivities : 0;
        
        levelData.score = averageScore;
        levelData.letter = this.getScoreLetter(averageScore);
        
        this.updateLevelDisplay(objectiveId, level);
        this.updateLevelStats(objectiveId, level, averageScore, ratedActivities, totalActivities);
    }

    updateLevelDisplay(objectiveId, level) {
        const levelData = this.levelScores[objectiveId][level];
        const scoreElement = document.getElementById(`level-score-${objectiveId}-${level}`);
        
        if (scoreElement) {
            scoreElement.textContent = `${levelData.letter} (${levelData.score.toFixed(2)})`;
            scoreElement.className = `capability-score level-score-chip ${this.getScoreColorClass(levelData.score)}`;
        }
    }

    updateLevelStats(objectiveId, level, averageScore, ratedActivities, totalActivities) {
        // Update average score display
        const averageScoreElement = document.getElementById(`average-score-${objectiveId}-${level}`);
        if (averageScoreElement) {
            averageScoreElement.textContent = averageScore.toFixed(2);
            averageScoreElement.className = `badge ${this.getScoreColorClass(averageScore)}`;
        }
        
        // Update activities count display
        const activitiesCountElement = document.getElementById(`activities-count-${objectiveId}-${level}`);
        if (activitiesCountElement) {
            activitiesCountElement.textContent = `${ratedActivities}/${totalActivities}`;
            const completionRatio = totalActivities > 0 ? ratedActivities / totalActivities : 0;
            activitiesCountElement.className = `badge ${this.getCompletionColorClass(completionRatio)}`;
        }
        
        // Update the overall objective capability level
        this.updateObjectiveCapabilityLevel(objectiveId);
    }

    updateObjectiveCapabilityLevel(objectiveId) {
        // Find the highest level that is at least L (Largely)
        let highestLevel = 1; // Default to level 1
        
        // Get all levels for this objective
        const objectiveCard = document.querySelector(`[data-objective-id="${objectiveId}"].objective-card`);
        if (objectiveCard) {
            const levelSections = objectiveCard.querySelectorAll('.capability-level-section');
            
            levelSections.forEach(section => {
                const level = parseInt(section.getAttribute('data-level'));
                const levelData = this.levelScores[objectiveId] && this.levelScores[objectiveId][level];
                
                if (levelData && levelData.score >= 0.50) { // L (Largely) threshold is 0.50
                    highestLevel = Math.max(highestLevel, level);
                }
            });
        }
        
        // Update the objective capability level badge
        const capabilityBadge = document.getElementById(`capability-level-${objectiveId}`);
        if (capabilityBadge) {
            this.updateCapabilityBadge(capabilityBadge, highestLevel);
        }

        this.objectiveCapabilityLevels[objectiveId] = highestLevel;
    }

    getMinLevelForObjective(objectiveId) {
        const firstButton = document.querySelector(`[data-objective-id="${objectiveId}"].toggle-level-details`);
        if (firstButton) {
            return parseInt(firstButton.getAttribute('data-min-level')) || 2;
        }
        return 2;
    }

    checkLevelLock(objectiveId, level) {
        const minLevel = this.getMinLevelForObjective(objectiveId);
        
        if (level === minLevel) {
            return;
        }
        
        const previousLevel = level - 1;
        const prevLevelData = this.levelScores[objectiveId] && this.levelScores[objectiveId][previousLevel];
        const isLocked = !prevLevelData || prevLevelData.letter !== 'F';
        
        const button = document.querySelector(`[data-objective-id="${objectiveId}"][data-level="${level}"]`);
        const scoreElement = document.getElementById(`level-score-${objectiveId}-${level}`);
        
        if (button && scoreElement) {
            if (isLocked) {
                button.querySelector('.toggle-text').textContent = 'Locked';
                button.disabled = true;
                button.classList.add('level-toggle-btn-disabled');
                scoreElement.textContent = 'N (0.00)';
                scoreElement.className = 'capability-score level-score-chip score-chip-muted';
                
                // Also lock the level data
                this.levelScores[objectiveId][level] = {
                    letter: 'N',
                    score: 0.00,
                    activities: {},
                    evidence: {},
                    notes: {}
                };
            } else {
                button.querySelector('.toggle-text').textContent = 'Start Assessment';
                button.disabled = false;
                button.classList.remove('level-toggle-btn-disabled');
            }
        }
    }

    checkAllLevelLocks(objectiveId) {
        const objectiveCard = document.querySelector(`[data-objective-id="${objectiveId}"].objective-card`);
        if (objectiveCard) {
            const levelSections = objectiveCard.querySelectorAll('.capability-level-section');
            levelSections.forEach(section => {
                const level = parseInt(section.getAttribute('data-level'));
                this.checkLevelLock(objectiveId, level);
            });
        }
    }

    getScoreLetter(score) {
        if (score > 0.85) return 'F';
        if (score > 0.50) return 'L';
        if (score > 0.15) return 'P';
        return 'N';
    }

    calculateCapabilityFromScore(score) {
        if (score <= 0.15) return 1;
        if (score <= 0.5) return 1;
        if (score <= 0.85) return 2;
        return 2;
    }

    updateCapabilityBadge(badge, level) {
        const badgeClasses = ['badge-level-1','badge-level-2','badge-level-3','badge-level-4','badge-level-5'];
        badgeClasses.forEach(c => badge.classList.remove(c));
        const levelKey = Math.min(Math.max(level, 1), 5);
        badge.classList.add(`badge-level-${levelKey}`);
        
        const levelNumber = badge.querySelector('.level-number');
        if (levelNumber) {
            levelNumber.textContent = level;
        }
    }

    getTotalActivitiesForLevel(objectiveId, level) {
        const assessmentSection = document.getElementById(`assessment-${objectiveId}-${level}`);
        if (assessmentSection) {
            return assessmentSection.querySelectorAll('.activity-rating-select').length;
        }
        return 0;
    }

    getActivityMeta(activityId) {
        const ratingSelect = this.elementCache.ratingSelects.get(activityId);
        if (!ratingSelect) {
            return null;
        }
        const objectiveId = ratingSelect.getAttribute('data-objective-id');
        const levelValue = parseInt(ratingSelect.getAttribute('data-level'), 10);
        if (!objectiveId || Number.isNaN(levelValue)) {
            return null;
        }
        return {
            objectiveId,
            level: levelValue
        };
    }

    getScoreColorClass(score) {
        if (score === 0) return 'score-chip-danger';
        if (score < 0.5) return 'score-chip-warning';
        if (score < 0.8) return 'score-chip-info';
        return 'score-chip-success';
    }

    getCompletionColorClass(ratio) {
        if (ratio === 0) return 'bg-secondary text-white';
        if (ratio < 0.5) return 'bg-warning text-dark';
        if (ratio < 1) return 'bg-info text-white';
        return 'bg-success text-white';
    }

    getRatingValue(rating) {
        const ratingMap = {
            'N': 0,
            'P': 1/3,
            'L': 2/3,
            'F': 1
        };
        return ratingMap[rating] || 0;
    }

    addEvidenceToLibrary(value, { refresh = true } = {}) {
        const trimmed = (value || '').trim();
        if (!trimmed || this.evidenceLibrary.has(trimmed)) {
            return;
        }
        this.evidenceLibrary.add(trimmed);
        if (refresh) {
            this.refreshEvidenceDropdowns();
        }
    }

    async refreshEvidenceDropdownsParallel() {
        return this.refreshEvidenceDropdowns();
    }

    async refreshEvidenceDropdowns() {
        const selectsArray = this.getEvidenceSelectElements();
        if (!selectsArray.length) {
            return;
        }

        const evidenceList = Array.from(this.evidenceLibrary)
            .filter(Boolean)
            .sort((a, b) => a.localeCompare(b));

        const optionsHtml = evidenceList.map(entry => {
            const safeValue = this.escapeHtml(entry);
            const truncated = entry.length > 90 ? `${this.escapeHtml(entry.slice(0, 87))}...` : safeValue;
            return `<option value="${safeValue}">${truncated}</option>`;
        }).join('');

        for (let i = 0; i < selectsArray.length; i++) {
            const select = selectsArray[i];
            const placeholder = select.getAttribute('data-placeholder') || 'Select saved evidence';
            const previousValue = select.value;

            select.innerHTML = `<option value="">${this.escapeHtml(placeholder)}...</option>${optionsHtml}`;
            if (previousValue && this.evidenceLibrary.has(previousValue)) {
                select.value = previousValue;
            }

            if (i > 0 && i % 120 === 0) {
                await new Promise(resolve => requestAnimationFrame(resolve));
            }
        }
    }

    updateDomainChart(selectedDomain = 'all') {
        if (!this.domainChartContainer || !this.domainChartRows) {
            return;
        }

        if (!selectedDomain || selectedDomain === 'all') {
            this.toggleRecapStandalone(false);
            this.domainChartHasData = false;
            this.updateDomainOverviewVisibility();
            return;
        }

        if (selectedDomain === 'recap') {
            const recapData = this.buildGamoRecapData();
            if (!recapData.length) {
                this.toggleRecapStandalone(false);
                this.domainChartHasData = false;
                this.updateDomainOverviewVisibility();
                return;
            }

            this.toggleRecapStandalone(true, recapData);
            this.domainChartHasData = false;
            this.updateDomainOverviewVisibility();
            return;
        }

        this.toggleRecapStandalone(false);

        const objectiveCards = document.querySelectorAll(`.objective-card[data-domain="${selectedDomain}"]`);
        const chartData = Array.from(objectiveCards).map(card => {
            const objectiveId = card.getAttribute('data-objective-id');
            const objectiveName = card.getAttribute('data-objective-name') || objectiveId;
            const currentLevel = Math.min(Math.max(this.objectiveCapabilityLevels[objectiveId] || 1, 1), 5);
            return {
                objectiveId,
                objectiveName,
                level: currentLevel
            };
        }).sort((a, b) => a.objectiveId.localeCompare(b.objectiveId, undefined, { numeric: true }));

        if (!chartData.length) {
            this.domainChartHasData = false;
            this.updateDomainOverviewVisibility();
            return;
        }

        this.domainChartHasData = true;

        if (this.domainChartTitle) {
            this.domainChartTitle.textContent = `${this.getDomainFullName(selectedDomain)}`;
        }
        if (this.domainChartPill) {
            this.domainChartPill.textContent = selectedDomain;
        }

        this.domainChartRows.innerHTML = '';
        chartData.forEach(row => {
            this.domainChartRows.appendChild(this.createDomainChartRow(row));
        });

        this.updateDomainOverviewVisibility();
    }

    buildGamoRecapData() {
        this.objectiveCards = this.objectiveCards.length ? this.objectiveCards : Array.from(document.querySelectorAll('.objective-card'));
        if (!this.objectiveCards.length) {
            return [];
        }

        const domainOrder = ['EDM', 'APO', 'BAI', 'DSS', 'MEA'];
        const domainRank = (domain) => {
            const idx = domainOrder.indexOf(domain);
            return idx === -1 ? domainOrder.length : idx;
        };

        const data = this.objectiveCards
            .map(card => {
                const domain = card.getAttribute('data-domain');
                const objectiveId = card.getAttribute('data-objective-id');
                const objectiveName = card.getAttribute('data-objective-name') || objectiveId;
                if (!domain || !objectiveId) {
                    return null;
                }
                const level = Math.min(Math.max(this.objectiveCapabilityLevels[objectiveId] || 1, 1), 5);
                return {
                    domain,
                    objectiveId,
                    objectiveName,
                    level
                };
            })
            .filter(Boolean)
            .sort((a, b) => {
                const domainComparison = domainRank(a.domain) - domainRank(b.domain);
                if (domainComparison !== 0) {
                    return domainComparison;
                }
                return a.objectiveId.localeCompare(b.objectiveId, undefined, { numeric: true });
            });

        return data;
    }

    createRecapTable(data) {
        const wrapper = document.createElement('div');
        wrapper.className = 'recap-table-wrapper';

        const table = document.createElement('table');
        table.className = 'table table-sm table-bordered recap-table align-middle mb-0';

        const thead = document.createElement('thead');
        thead.innerHTML = `
            <tr>
                <th style="width:60px;">No</th>
                <th style="width:110px;">Domain</th>
                <th>Gamo / Objective</th>
                <th style="width:140px;" class="text-center">Level</th>
            </tr>
        `;

        const tbody = document.createElement('tbody');
        data.forEach((row, index) => {
            const domainCode = (row.domain || '').toLowerCase();
            const safeDomain = this.escapeHtml(row.domain);
            const safeObjectiveId = this.escapeHtml(row.objectiveId);
            const safeObjectiveName = this.escapeHtml(row.objectiveName);
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="text-center fw-semibold">${index + 1}</td>
                <td>
                    <span class="recap-domain-pill domain-pill-${domainCode}">${safeDomain}</span>
                </td>
                <td>
                    <div class="recap-objective-code">${safeObjectiveId}</div>
                    <div class="recap-objective-name">${safeObjectiveName}</div>
                </td>
                <td class="text-center">
                    <span class="level-badge level-badge-${row.level}">Level ${row.level}</span>
                </td>
            `;
            tbody.appendChild(tr);
        });

        table.appendChild(thead);
        table.appendChild(tbody);
        wrapper.appendChild(table);
        return wrapper;
    }

    toggleRecapStandalone(show, data = []) {
        if (!this.recapStandaloneRow || !this.recapStandaloneWrapper || !this.recapStandaloneBody) {
            return;
        }

        if (!show || !data.length) {
            this.recapStandaloneRow.style.display = 'none';
            this.recapStandaloneWrapper.style.display = 'none';
            this.recapStandaloneBody.innerHTML = '';
            return;
        }

        this.recapStandaloneBody.innerHTML = '';
        this.recapStandaloneBody.appendChild(this.createRecapTable(data));
        this.recapStandaloneRow.style.display = 'flex';
        this.recapStandaloneWrapper.style.display = 'block';
    }

    createDomainChartRow({ objectiveId, objectiveName, level, meta = {} }) {
        const row = document.createElement('div');
        row.className = 'domain-level-row';

        const label = document.createElement('div');
        label.className = 'domain-level-objective';
        label.innerHTML = `<strong>${objectiveId}</strong><span>${objectiveName}</span>`;

        const bar = document.createElement('div');
        bar.className = 'domain-level-bar';
        for (let i = 1; i <= 5; i++) {
            const segment = document.createElement('span');
            segment.className = 'level-segment';
            segment.classList.add(`level-tier-${i}`);
            segment.textContent = i;
            if (i <= level) {
                segment.classList.add('active');
            }
            bar.appendChild(segment);
        }

        const currentLevel = document.createElement('div');
        currentLevel.className = 'domain-level-current';
        const averageNote = meta && typeof meta.average === 'number'
            ? `<small>Rata-rata ${meta.average.toFixed(2)}</small>`
            : '';
        currentLevel.innerHTML = `
            <span class="level-badge level-badge-${level}" data-level="${level}">
                <strong>Level ${level}</strong>
                ${averageNote}
            </span>
        `;

        row.appendChild(label);
        row.appendChild(bar);
        row.appendChild(currentLevel);
        return row;
    }

    getDomainFullName(domainCode) {
        const names = {
            'EDM': 'Evaluate, Direct, and Monitor',
            'APO': 'Align, Plan, and Organize',
            'BAI': 'Build, Acquire, and Implement',
            'DSS': 'Deliver, Service, and Support',
            'MEA': 'Monitor, Evaluate, and Assess'
        };
        return names[domainCode] || domainCode;
    }

    parseNotePayload(rawValue, rawEvidence = null) {
        const emptyPayload = { evidence: '', note: '' };
        if (rawEvidence !== null && rawEvidence !== undefined) {
            return {
                evidence: rawEvidence || '',
                note: rawValue || ''
            };
        }

        if (!rawValue) {
            return emptyPayload;
        }

        if (typeof rawValue === 'string') {
            try {
                const parsed = JSON.parse(rawValue);
                if (parsed && typeof parsed === 'object') {
                    return {
                        evidence: parsed.evidence || parsed.comment || '',
                        note: parsed.note || parsed.notes || ''
                    };
                }
            } catch (error) {
                return { evidence: rawValue, note: '' };
            }
            return { evidence: rawValue, note: '' };
        }

        if (typeof rawValue === 'object') {
            return {
                evidence: rawValue.evidence || rawValue.comment || '',
                note: rawValue.note || rawValue.notes || ''
            };
        }

        return emptyPayload;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new COBITAssessmentManager({{ $evalId }});
});
</script>

{{-- Custom CSS for better styling --}}
<style>
.objective-card {
    transition: transform 0.2s ease-in-out;
}

.objective-card:hover {
    transform: translateY(-5px);
}

.card {
    border: none;
    border-radius: 10px;
    overflow: hidden;
}

.card-header {
    border-bottom: none;
}

.objective-header {
    background-color: #0f2b5c;
    color: #fff;
    border-bottom: 1px solid rgba(255,255,255,0.15);
}

.objective-title {
    font-size: 1.15rem;
    font-weight: 600;
}

.objective-domain {
    font-size: 0.95rem;
    opacity: 0.85;
}

.objective-stat {
    font-size: 0.9rem;
}

.capability-badge {
    display: inline-block;
    padding: 0.35rem 0.8rem;
    border-radius: 999px;
    font-weight: 600;
}

.capability-badge.badge-level-1 { background-color: #f8d7da; color: #58151c; }
.capability-badge.badge-level-2 { background-color: #fff3cd; color: #664d03; }
.capability-badge.badge-level-3 { background-color: #cff4fc; color: #055160; }
.capability-badge.badge-level-4 { background-color: #dbe4ff; color: #102a5b; }
.capability-badge.badge-level-5 { background-color: #d1e7dd; color: #0f5132; }

.objective-description-text,
.objective-purpose-text {
    font-size: 0.95rem;
    line-height: 1.5;
    color: rgba(255,255,255,0.9);
}

.objective-summary {
    display: flex;
    gap: 1.25rem;
    flex-wrap: wrap;
}

.summary-column {
    flex: 1 1 260px;
    background-color: rgba(255,255,255,0.08);
    border-radius: 0.5rem;
    padding: 0.85rem 1rem;
}

.summary-label {
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 0.35rem;
    opacity: 0.75;
}

.btn-group .btn {
    border-radius: 0;
}

.btn-group .btn:first-child {
    border-top-left-radius: 0.375rem;
    border-bottom-left-radius: 0.375rem;
}

.btn-group .btn:last-child {
    border-top-right-radius: 0.375rem;
    border-bottom-right-radius: 0.375rem;
}

.badge {
    font-size: 0.75rem;
}

.domain-tabs-wrapper {
    background: #f7f9ff;
    border-radius: 0.8rem;
    padding: 0.75rem 1rem 0.25rem;
    border: 1px solid #e1e6f5;
}

.domain-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    justify-content: space-between;
}

.domain-tab {
    border: none;
    background: transparent;
    color: #0f6ad9;
    padding: 0.4rem 1.4rem;
    border-radius: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    transition: all 0.2s ease;
}

.domain-tab:hover {
    color: #0c4fb5;
}

.domain-tab.active {
    background: #0f73c9;
    color: #fff;
    box-shadow: 0 12px 24px rgba(15,106,217,0.25);
}

.objective-filter-wrapper {
    margin-top: 0.75rem;
    padding-top: 0.35rem;
    border-top: 1px solid #dfe4f4;
}

.objective-filter-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    overflow-x: auto;
}

.objective-filter-tab {
    border: none;
    background: transparent;
    color: #6a7091;
    font-weight: 600;
    font-size: 0.85rem;
    letter-spacing: 0.04em;
    padding: 0.35rem 0.65rem;
    border-bottom: 2px solid transparent;
    border-radius: 0;
    transition: color 0.15s ease, border-color 0.15s ease;
}

.objective-filter-tab:hover {
    color: #0c4fb5;
}

.objective-filter-tab.active {
    color: #0f2b5c;
    border-bottom-color: #0f6ad9;
}

.domain-overview-wrapper {
    border: 1px solid #e2e8fb;
    border-radius: 0.9rem;
    background: #fdfdff;
    box-shadow: 0 16px 35px rgba(15,43,92,0.06);
    padding: 0;
}

.domain-overview-toggle {
    width: 100%;
    border: none;
    background: transparent;
    padding: 0.95rem 1.25rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: 600;
    color: #0f2b5c;
    font-size: 1rem;
    letter-spacing: 0.03em;
}

.domain-overview-toggle .toggle-indicator {
    transition: transform 0.25s ease;
}

.domain-overview-wrapper.collapsed .toggle-indicator {
    transform: rotate(180deg);
}

.domain-level-card {
    background: #fff;
    border-top: 1px solid #e2e8fb;
    border-radius: 0 0 0.9rem 0.9rem;
    padding: 1.25rem 1.5rem 1.5rem;
}

.domain-level-card {
    background: #fff;
    border: 1px solid #e2e8fb;
    border-radius: 0.85rem;
    padding: 1.5rem;
    box-shadow: 0 18px 40px rgba(15,43,92,0.08);
}

.domain-level-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.domain-level-label {
    font-size: 0.78rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #6f7a98;
    margin-bottom: 0.25rem;
}

.domain-level-title {
    margin: 0;
    font-weight: 700;
    color: #0f2b5c;
}

.domain-level-pill {
    background: #0f2b5c;
    color: #fff;
    border-radius: 999px;
    padding: 0.4rem 1.3rem;
    font-weight: 600;
    letter-spacing: 0.06em;
}

.domain-level-table {
    border: 1px solid #e2e8fb;
    border-radius: 0.75rem;
    overflow: hidden;
}

.domain-level-table-head,
.domain-level-row {
    display: grid;
    grid-template-columns: minmax(170px, 1.3fr) minmax(250px, 2fr) 150px;
    gap: 0.85rem;
    padding: 0.9rem 1.2rem;
    align-items: center;
}

.domain-level-table {
    max-height: 320px;
    overflow-y: auto;
}

.domain-level-table-head {
    background: #f6f8ff;
    font-weight: 600;
    color: #42507a;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.recap-table-wrapper {
    border-radius: 0.75rem;
    border: 1px solid #e2e8fb;
}

.recap-table {
    margin: 0;
    background: #fff;
}

.recap-table thead th {
    position: sticky;
    top: 0;
    background: #f6f8ff;
    color: #42507a;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.78rem;
    letter-spacing: 0.04em;
    border-bottom: 1px solid #e2e8fb;
}

.recap-table tbody tr:nth-child(odd) {
    background: #fbfcff;
}

.recap-table tbody tr:hover {
    background: #f0f4ff;
}

.recap-domain-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.25rem 0.8rem;
    border-radius: 999px;
    font-weight: 600;
    letter-spacing: 0.04em;
    font-size: 0.85rem;
    border: 1px solid transparent;
}

.domain-pill-edm {
    background: rgba(15, 43, 92, 0.12);
    color: #081a3d;
    border-color: rgba(15, 43, 92, 0.3);
}

.domain-pill-apo {
    background: rgba(8, 122, 194, 0.12);
    color: #084c8c;
    border-color: rgba(8, 122, 194, 0.3);
}

.domain-pill-bai {
    background: rgba(19, 110, 82, 0.12);
    color: #0b5136;
    border-color: rgba(19, 110, 82, 0.3);
}

.domain-pill-dss {
    background: rgba(207, 99, 26, 0.12);
    color: #81370a;
    border-color: rgba(207, 99, 26, 0.3);
}

.domain-pill-mea {
    background: rgba(103, 35, 156, 0.12);
    color: #4b0f82;
    border-color: rgba(103, 35, 156, 0.3);
}

.recap-objective-code {
    font-weight: 600;
    color: #0f2b5c;
    letter-spacing: 0.03em;
}

.recap-objective-name {
    font-size: 0.86rem;
    color: #5a6482;
}

.domain-level-row:nth-child(odd) {
    background: #fbfcff;
}

.domain-level-row:not(:last-child) {
    border-bottom: 1px solid #eef2ff;
}

.domain-level-objective {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}

.domain-level-objective strong {
    color: #0f2b5c;
    font-size: 0.95rem;
}

.domain-level-objective span {
    font-size: 0.85rem;
    color: #66718f;
}

.domain-level-bar {
    display: flex;
    gap: 0.35rem;
}

.level-segment {
    flex: 1;
    border-radius: 0.4rem;
    border: 1px solid #d7ddf1;
    text-align: center;
    font-weight: 600;
    font-size: 0.8rem;
    padding: 0.35rem 0;
    color: #7a85a8;
}

.level-segment.active {
    color: #fff;
    border-color: transparent;
    box-shadow: 0 6px 14px rgba(15,106,217,0.18);
}

.level-segment.level-tier-1.active {
    background: #f97316;
}

.level-segment.level-tier-2.active {
    background: #facc15;
    color: #7a5d07;
    box-shadow: none;
}

.level-segment.level-tier-3.active {
    background: #86efac;
    color: #065f46;
    box-shadow: none;
}

.level-segment.level-tier-4.active {
    background: #15803d;
}

.level-segment.level-tier-5.active {
    background: linear-gradient(120deg, #0f6ad9, #0c4fb5);
}

.domain-level-current {
    text-align: right;
}

.level-badge {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-width: 110px;
    gap: 0.1rem;
    padding: 0.4rem 0.85rem;
    border-radius: 1.5rem;
    font-weight: 600;
    border: 1px solid transparent;
    font-size: 0.85rem;
}

.level-badge small {
    font-size: 0.7rem;
    text-transform: none;
    letter-spacing: 0.03em;
    opacity: 0.85;
}

.level-badge-1 {
    background: rgba(249, 115, 22, 0.15);
    color: #9a3412;
    border-color: rgba(249, 115, 22, 0.4);
}

.level-badge-2 {
    background: rgba(250, 204, 21, 0.2);
    color: #7a5d07;
    border-color: rgba(250, 204, 21, 0.6);
}

.level-badge-3 {
    background: rgba(134, 239, 172, 0.25);
    color: #065f46;
    border-color: rgba(16, 185, 129, 0.45);
}

.level-badge-4 {
    background: rgba(21, 128, 61, 0.2);
    color: #064e3b;
    border-color: rgba(21, 128, 61, 0.5);
}

.level-badge-5 {
    background: rgba(15, 74, 129, 0.15);
    color: #0f2b5c;
    border-color: rgba(15, 74, 129, 0.45);
}

.card-footer {
    border-top: 1px solid rgba(0,0,0,0.1);
}

.bg-light {
    background-color: #f8f9fa !important;
}

/* Assessment Interface Styling */
.capability-level-section {
    border: 1px solid #e2e8fb;
    border-radius: 0.85rem;
    padding: 1rem 1.25rem;
    background-color: #fff;
    box-shadow: 0 10px 25px rgba(15,43,92,0.04);
}

.capability-level-section:not(:last-child) {
    margin-bottom: 1rem;
}

.level-section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.level-section-info {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.85rem;
}

.level-pill {
    background-color: #0f2b5c;
    color: #fff;
    border-radius: 999px;
    padding: 0.35rem 1.2rem;
    font-weight: 600;
    box-shadow: inset 0 -2px 0 rgba(0,0,0,0.12);
}

.level-section-subtext {
    font-size: 0.9rem;
    color: #5e6681;
    font-weight: 500;
}

.level-section-actions {
    display: flex;
    align-items: center;
    gap: 0.9rem;
}

.capability-score {
    font-weight: 600;
    transition: all 0.3s ease;
}

.level-score-chip {
    border-radius: 999px;
    padding: 0.35rem 1rem;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    border: 1px solid transparent;
}

.score-chip-muted {
    background: #eef1f8;
    color: #5b6279;
}

.score-chip-danger {
    background: #fee4e2;
    color: #a01d17;
    border-color: #f8b4a9;
}

.score-chip-warning {
    background: #fff3cd;
    color: #7a5d07;
    border-color: #ffe69c;
}

.score-chip-info {
    background: #e0f2ff;
    color: #0b4c73;
    border-color: #b6e0fe;
}

.score-chip-success {
    background: #d1f2e2;
    color: #0f5132;
    border-color: #a5e3c6;
}

.sticky-action-group {
    position: fixed;
    right: 25px;
    bottom: 25px;
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    z-index: 1050;
}

.sticky-action-btn {
    border-radius: 999px;
    padding: 0.65rem 1.4rem;
    font-weight: 600;
    box-shadow: 0 12px 32px rgba(15,106,217,0.2);
}

.sticky-action-btn.btn-light {
    background: #fff;
    color: #0f2b5c;
    border: 1px solid rgba(15,43,92,0.15);
}

.sticky-action-btn.btn-primary {
    background: linear-gradient(120deg, #0f6ad9, #0c4fb5);
    border: none;
}

.level-toggle-btn {
    border-radius: 999px;
    padding: 0.45rem 1.35rem;
    font-weight: 600;
    background: linear-gradient(120deg, #0f6ad9, #0c4fb5);
    color: #fff;
    border: none;
    box-shadow: 0 6px 15px rgba(15,106,217,0.25);
    display: flex;
    align-items: center;
    gap: 0.35rem;
}

.level-toggle-btn:hover {
    color: #fff;
    transform: translateY(-1px);
}

.level-toggle-btn:disabled,
.level-toggle-btn-disabled {
    background: #c6cdde;
    color: #6b738a;
    box-shadow: none;
}

.assessment-section {
    margin-top: 1rem;
    padding-top: 1.25rem;
    border-top: 1px solid #e5e8f4;
}

.assessment-table th {
    background-color: #eef2ff;
    border-bottom: 2px solid #d5dcf3;
    color: #0f2b5c;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.78rem;
    letter-spacing: 0.04em;
}

.assessment-table th,
.assessment-table td {
    padding: 0.65rem;
    border-color: #d9d9d9;
}

.assessment-table {
    min-width: 1250px;
}

.table-responsive {
    overflow-x: auto;
}

.assessment-table td {
    vertical-align: top;
}

.assessment-table tbody tr:nth-child(even) {
    background-color: #fbfcff;
}

.level-cell {
    background-color: #eef2ff;
    font-weight: 600;
    font-size: 0.85rem;
    writing-mode: vertical-lr;
    text-orientation: mixed;
    padding: 0.35rem;
}

.practice-code-cell {
    background-color: #fff;
}

.practice-code-text {
    font-size: 0.9rem;
    font-weight: 600;
}

.practice-name-cell {
    font-size: 0.9rem;
}

.practice-name-cell small {
    font-size: 0.75rem;
}

.description-cell p {
    margin-bottom: 0;
}

.rating-cell select {
    width: 100%;
    margin-bottom: 0.35rem;
}


.activity-rating-select.rating-full {
    background-color: #198754;
    border-color: #198754;
    color: #fff;
}

.activity-rating-select.rating-high {
    background-color: #0dcaf0;
    border-color: #0dcaf0;
    color: #042c55;
}

.activity-rating-select.rating-medium {
    background-color: #ffe69c;
    border-color: #ffe69c;
    color: #5c4705;
}

.activity-rating-select.rating-low {
    background-color: #f8d7da;
    border-color: #f8d7da;
    color: #842029;
}

.evidence-cell .assessment-textarea,
.notes-cell .assessment-textarea {
    width: 100%;
}

.evidence-input-wrapper {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.evidence-history-select {
    font-size: 0.85rem;
}

.assessment-textarea {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    resize: vertical;
    min-height: 60px;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.assessment-textarea:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.assessment-textarea::placeholder {
    color: #6c757d;
    font-style: italic;
}

/* Smooth animations */
@keyframes slideDown {
    from {
        opacity: 0;
        max-height: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        max-height: 1000px;
        transform: translateY(0);
    }
}

.assessment-section {
    overflow: hidden;
    transition: all 0.3s ease;
}

/* Loading Overlay Styles */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(15, 43, 92, 0.95);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    transition: opacity 0.3s ease;
}

.loading-content {
    text-align: center;
    color: #fff;
    max-width: 400px;
    padding: 2rem;
}

.loading-spinner {
    width: 80px;
    height: 80px;
    margin: 0 auto 1.5rem;
    border: 6px solid rgba(255, 255, 255, 0.2);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.loading-text {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    letter-spacing: 0.03em;
}

.loading-progress-container {
    width: 100%;
    height: 8px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 999px;
    overflow: hidden;
    margin-bottom: 1rem;
}

.loading-progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #0f6ad9, #00d4ff);
    border-radius: 999px;
    transition: width 0.3s ease;
    box-shadow: 0 0 20px rgba(0, 212, 255, 0.5);
}

.loading-percentage {
    font-size: 2rem;
    font-weight: 700;
    color: #00d4ff;
    text-shadow: 0 0 20px rgba(0, 212, 255, 0.5);
    letter-spacing: 0.05em;
}

/* Corner Loading Indicator (Non-blocking) */
.corner-loading {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1040;
    background: linear-gradient(135deg, #0f2b5c, #1a3d6b);
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(15, 43, 92, 0.4);
    padding: 16px 20px;
    min-width: 280px;
    opacity: 0;
    transform: translateX(350px);
    transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    pointer-events: none;
}

.corner-loading.show {
    opacity: 1;
    transform: translateX(0);
}

.corner-loading-content {
    display: flex;
    align-items: flex-start;
    gap: 14px;
}

.corner-spinner {
    width: 32px;
    height: 32px;
    min-width: 32px;
    border: 3px solid rgba(255, 255, 255, 0.2);
    border-top-color: #00d4ff;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

.corner-loading-info {
    flex: 1;
    min-width: 0;
}

.corner-loading-text {
    color: #fff;
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 8px;
    line-height: 1.3;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.corner-loading-progress {
    width: 100%;
    height: 4px;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 999px;
    overflow: hidden;
    margin-bottom: 6px;
}

.corner-progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #0f6ad9, #00d4ff);
    border-radius: 999px;
    transition: width 0.3s ease;
    box-shadow: 0 0 10px rgba(0, 212, 255, 0.6);
}

.corner-loading-percentage {
    color: #00d4ff;
    font-size: 0.85rem;
    font-weight: 700;
    text-align: right;
}

@media (max-width: 768px) {
    .corner-loading {
        right: 10px;
        top: 10px;
        min-width: 240px;
    }
}
</style>
@endsection
