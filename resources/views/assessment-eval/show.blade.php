@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
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
                <div class="domain-tabs-scroll" role="tablist" aria-label="Workflow and GAMO tabs">
                    <button type="button" class="workflow-tab" data-workflow="scoping">Scoping</button>
                    <button type="button" class="workflow-tab" data-workflow="evidence">ADD EVIDENCE</button>
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

    @php
        $domainOrderMap = ['EDM' => 1, 'APO' => 2, 'BAI' => 3, 'DSS' => 4, 'MEA' => 5];
        $sortedObjectives = collect($objectives)->sortBy(function($objective) use ($domainOrderMap) {
            $domain = preg_replace('/\d+/', '', $objective->objective_id);
            $domainRank = $domainOrderMap[$domain] ?? 99;
            return sprintf('%02d_%s', $domainRank, $objective->objective_id);
        });
    @endphp

    <div class="card shadow-sm mb-4 scoping-panel" id="scoping-panel" style="display: none;">
        <div class="card-header scoping-panel-header">
            <div>
                <div class="scoping-title">Scoping Assessment</div>
                <p class="mb-0 text-muted scoping-subtitle">Pilih objective GAMO yang ingin dinilai terlebih dahulu.</p>
            </div>
            <span class="scoping-pill"><i class="fas fa-flag-checkered me-2"></i>Scoping</span>
        </div>
        <div class="card-body scoping-panel-body">
            <div class="scoping-table-wrapper">
                <div class="table-responsive">
                    <table class="table table-hover align-middle scoping-table">
                        <thead>
                            <tr>
                                <th style="width: 70px;" class="text-center">No</th>
                                <th style="width: 140px;">GAMO</th>
                                <th>Nama GAMO</th>
                                <th style="width: 140px;" class="text-end">Pilih</th>
                            </tr>
                        </thead>
                        <tbody id="scoping-table-body">
                            @foreach($sortedObjectives as $objective)
                                @php
                                    $objectiveDomain = preg_replace('/\d+/', '', $objective->objective_id);
                                @endphp
                                <tr class="scoping-row" data-objective-id="{{ $objective->objective_id }}" data-domain="{{ $objectiveDomain }}">
                                    <td class="text-center fw-semibold">{{ $loop->iteration }}</td>
                                    <td class="scoping-gamo-code">{{ $objective->objective_id }}</td>
                                    <td class="scoping-gamo-name">{{ $objective->objective }}</td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-primary scoping-select-btn">
                                            <i class="fas fa-plus me-1"></i>Pilih
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="scoping-selection-footer">
                <div class="scoping-selection-summary" id="scoping-selection-summary">
                    Belum ada GAMO yang dipilih.
                </div>
                <div class="scoping-selection-actions">
                    <button type="button" class="btn btn-outline-secondary" id="scoping-clear-btn" disabled>
                        Reset Pilihan
                    </button>
                    <button type="button" class="btn btn-primary" id="scoping-apply-btn" disabled>
                        Mulai Assessment
                    </button>
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
        this.workflowTabs = null;
        this.activeWorkflow = 'domains';
        this.scopingPanel = null;
        this.scopingRows = [];
        this.selectedObjectiveIds = new Set();
        this.appliedScopingObjectives = new Set();
        this.scopingSelectionSummary = null;
        this.scopingApplyBtn = null;
        this.scopingClearBtn = null;
        this.init();
    }

    init() {
        this.cacheDomainChartElements();
        this.cacheObjectiveFilterElements();
        this.cacheScopingElements();
        this.buildDomainObjectiveMap();
        this.setupDomainFiltering();
        this.setupWorkflowTabs();
        this.setupScopingCards();
        this.setupAssessmentToggles();
        this.setupActivityRating();
        this.initializeDefaultStates();
        this.setupSaveLoadButtons();
        this.setupDomainOverviewAccordion();
        this.setupBackToTopButton();
        this.loadAssessment();
        this.refreshEvidenceDropdowns();
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

    cacheScopingElements() {
        this.scopingPanel = document.getElementById('scoping-panel');
        this.scopingRows = Array.from(document.querySelectorAll('.scoping-row'));
        this.scopingSelectionSummary = document.getElementById('scoping-selection-summary');
        this.scopingApplyBtn = document.getElementById('scoping-apply-btn');
        this.scopingClearBtn = document.getElementById('scoping-clear-btn');
        this.workflowTabs = document.querySelectorAll('.workflow-tab');
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

        if (domain === 'all' || domain === 'recap' || domain === 'multi' || !this.domainObjectiveMap[domain] || this.domainObjectiveMap[domain].length === 0) {
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

    setupWorkflowTabs() {
        if (!this.workflowTabs || !this.workflowTabs.length) {
            return;
        }

        this.workflowTabs.forEach(tab => {
            const workflow = tab.getAttribute('data-workflow');
            tab.addEventListener('click', () => {
                if (workflow === 'scoping') {
                    this.activateWorkflow('scoping');
                    return;
                }
                this.showNotification('Workflow ini masih tahap POC.', 'info');
            });
        });

        this.refreshWorkflowTabState();
    }

    activateWorkflow(workflow) {
        if (!workflow) {
            return;
        }

        this.activeWorkflow = workflow;
        this.refreshWorkflowTabState();

        if (workflow === 'scoping') {
            this.toggleScopingView(true);
            this.activeDomainFilter = 'scoping';
            this.activeObjectiveFilter = 'all';
            this.renderObjectiveFilterTabs('all');
            this.toggleRecapStandalone(false);
            this.updateObjectiveVisibility();
            this.updateDomainChart('all');
            return;
        }

        this.toggleScopingView(false);
        if (this.activeDomainFilter === 'scoping') {
            this.activeDomainFilter = 'all';
        }
        this.renderObjectiveFilterTabs(this.activeDomainFilter);
        this.updateObjectiveVisibility();
        this.updateDomainChart(this.activeDomainFilter);
    }

    refreshWorkflowTabState() {
        if (!this.workflowTabs) {
            return;
        }
        this.workflowTabs.forEach(tab => {
            tab.classList.remove('active');
            const wf = tab.getAttribute('data-workflow');
            if (wf === this.activeWorkflow) {
                tab.classList.add('active');
            }
        });
    }

    toggleScopingView(show) {
        if (!this.scopingPanel) {
            return;
        }
        this.scopingPanel.style.display = show ? 'block' : 'none';
        const objectivesContainer = document.getElementById('objectives-container');
        const overviewWrapper = document.getElementById('domain-overview-wrapper');
        if (objectivesContainer) {
            objectivesContainer.style.display = show ? 'none' : '';
        }
        if (overviewWrapper) {
            if (show) {
                overviewWrapper.style.display = 'none';
            } else {
                overviewWrapper.style.display = this.domainChartHasData ? '' : 'none';
            }
        }
        const recapRow = document.getElementById('recap-standalone-row');
        if (recapRow) {
            if (show) {
                recapRow.style.display = 'none';
            } else {
                recapRow.style.display = this.activeDomainFilter === 'recap' && this.recapStandaloneBody && this.recapStandaloneBody.children.length
                    ? ''
                    : 'none';
            }
        }
    }

    setupScopingCards() {
        if (!this.scopingRows || !this.scopingRows.length) {
            return;
        }

        this.scopingRows.forEach(row => {
            const toggleBtn = row.querySelector('.scoping-select-btn');

            if (toggleBtn) {
                toggleBtn.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    this.toggleScopingRowSelection(row);
                });
            }

            row.addEventListener('click', (event) => {
                if (event.target && event.target.closest('.scoping-select-btn')) {
                    return;
                }
                this.toggleScopingRowSelection(row);
            });
        });

        if (this.scopingApplyBtn) {
            this.scopingApplyBtn.addEventListener('click', () => this.applyScopingSelections());
        }

        if (this.scopingClearBtn) {
            this.scopingClearBtn.addEventListener('click', () => this.clearScopingSelections(true));
        }

        this.updateScopingSummary();
    }

    toggleScopingRowSelection(row) {
        if (!row) {
            return;
        }

        const objectiveId = row.getAttribute('data-objective-id');
        if (!objectiveId) {
            return;
        }

        let isSelected;
        if (this.selectedObjectiveIds.has(objectiveId)) {
            this.selectedObjectiveIds.delete(objectiveId);
            row.classList.remove('selected');
            isSelected = false;
        } else {
            this.selectedObjectiveIds.add(objectiveId);
            row.classList.add('selected');
            isSelected = true;
        }

        const toggleBtn = row.querySelector('.scoping-select-btn');
        if (toggleBtn) {
            toggleBtn.innerHTML = isSelected
                ? '<i class="fas fa-check me-1"></i>Dipilih'
                : '<i class="fas fa-plus me-1"></i>Pilih';
            toggleBtn.classList.toggle('btn-primary', isSelected);
            toggleBtn.classList.toggle('btn-outline-primary', !isSelected);
        }

        this.updateScopingSummary();
    }

    updateScopingSummary() {
        if (!this.scopingSelectionSummary) {
            return;
        }

        const count = this.selectedObjectiveIds.size;
        if (!count) {
            this.scopingSelectionSummary.textContent = 'Belum ada GAMO yang dipilih.';
            if (this.scopingApplyBtn) {
                this.scopingApplyBtn.disabled = true;
            }
            if (this.scopingClearBtn) {
                this.scopingClearBtn.disabled = true;
            }
            return;
        }

        const selectedList = Array.from(this.selectedObjectiveIds).sort();
        this.scopingSelectionSummary.textContent = `Terpilih (${count}): ${selectedList.join(', ')}`;
        if (this.scopingApplyBtn) {
            this.scopingApplyBtn.disabled = false;
        }
        if (this.scopingClearBtn) {
            this.scopingClearBtn.disabled = false;
        }
    }

    applyScopingSelections() {
        if (!this.selectedObjectiveIds.size) {
            this.showNotification('Pilih minimal satu GAMO.', 'info');
            return;
        }

        this.appliedScopingObjectives = new Set(this.selectedObjectiveIds);
        this.toggleScopingView(false);
        this.activeWorkflow = 'domains';
        this.refreshWorkflowTabState();
        this.activeDomainFilter = 'all';
        this.activeObjectiveFilter = 'all';
        this.renderObjectiveFilterTabs('all');
        this.toggleRecapStandalone(false);
        this.updateObjectiveVisibility();
        this.updateDomainChart('all');
        this.showNotification('Pilihan GAMO diterapkan.', 'success');
    }

    clearScopingSelections(clearApplied = false) {
        this.selectedObjectiveIds.clear();
        if (clearApplied) {
            this.appliedScopingObjectives.clear();
            this.activeDomainFilter = 'all';
            this.activeObjectiveFilter = 'all';
            this.renderObjectiveFilterTabs('all');
            this.toggleRecapStandalone(false);
            this.updateObjectiveVisibility();
            this.updateDomainChart('all');
        }
        if (this.scopingRows && this.scopingRows.length) {
            this.scopingRows.forEach(row => {
                row.classList.remove('selected');
                const toggleBtn = row.querySelector('.scoping-select-btn');
                if (toggleBtn) {
                    toggleBtn.innerHTML = '<i class="fas fa-plus me-1"></i>Pilih';
                    toggleBtn.classList.add('btn-outline-primary');
                    toggleBtn.classList.remove('btn-primary');
                }
            });
        }
        this.updateScopingSummary();
    }

    applyObjectiveFilter(objectiveId) {
        this.activeObjectiveFilter = objectiveId || 'all';
        this.updateObjectiveVisibility();
    }

    updateObjectiveVisibility() {
        const cards = this.objectiveCards.length ? this.objectiveCards : Array.from(document.querySelectorAll('.objective-card'));
        const noResultsDiv = document.getElementById('no-results');
        let visibleCount = 0;

        if (this.activeWorkflow === 'scoping') {
            cards.forEach(card => {
                card.style.display = 'none';
            });
            if (noResultsDiv) {
                noResultsDiv.style.display = 'none';
            }
            return;
        }

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
            const matchesDomain = this.activeDomainFilter === 'all' || cardDomain === this.activeDomainFilter;
            const matchesObjective = this.activeObjectiveFilter === 'all' || cardObjective === this.activeObjectiveFilter;
            const matchesScoping = !this.appliedScopingObjectives.size || this.appliedScopingObjectives.has(cardObjective);

            if (matchesDomain && matchesObjective && matchesScoping) {
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
        try {
            const fieldPayload = this.collectFieldData();
            const assessmentData = {
                assessmentData: this.levelScores,
                notes: fieldPayload.notes,
                evidence: fieldPayload.evidence
            };

            const response = await fetch(`/assessment-eval/${this.currentEvalId}/save`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(assessmentData)
            });

            const result = await response.json();

            if (response.ok) {
                this.showNotification('Assessment saved successfully!', 'success');
            } else {
                this.showNotification(result.message || 'Failed to save assessment', 'error');
            }
        } catch (error) {
            console.error('Save error:', error);
            this.showNotification('Failed to save assessment', 'error');
        }
    }

    async loadAssessment(triggeredManually = false) {
        try {
            const response = await fetch(`/assessment-eval/${this.currentEvalId}/load`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const result = await response.json();

            if (response.ok && result.data) {
                this.populateAssessmentData(result.data);
                if (triggeredManually) {
                    this.showNotification('Assessment loaded successfully!', 'success');
                }
            } else if (!response.ok && triggeredManually) {
                this.showNotification(result.message || 'Failed to load assessment', 'error');
            }
        } catch (error) {
            console.error('Load error:', error);
            if (triggeredManually) {
                this.showNotification('Failed to load assessment', 'error');
            }
        }
    }

    populateAssessmentData(data) {
        this.levelScores = {};
        this.evidenceLibrary = new Set();
        
        document.querySelectorAll('.activity-rating-select').forEach(select => select.value = '');
        document.querySelectorAll('.assessment-textarea').forEach(textarea => textarea.value = '');

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
                this.addEvidenceToLibrary(parsedNotes.evidence);
            });
        }

        if (data.activityData) {
            Object.keys(data.activityData).forEach(activityId => {
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
                            this.addEvidenceToLibrary(parsedNotes.evidence);
                        }
                        
                        this.updateActivityScore(activityId, levelAchieved);
                    }
                }
            });
        }

        this.refreshEvidenceDropdowns();
        this.updateAllCalculations();
    }

    updateAllCalculations() {
        const objectiveCards = document.querySelectorAll('.objective-card');
        objectiveCards.forEach(card => {
            const objectiveId = card.getAttribute('data-objective-id');
            const levelSections = card.querySelectorAll('.capability-level-section');
            
            levelSections.forEach(section => {
                const level = parseInt(section.getAttribute('data-level'));
                this.updateLevelCapability(objectiveId, level);
                this.checkLevelLock(objectiveId, level);
            });
            
            // Update overall objective capability level
            this.updateObjectiveCapabilityLevel(objectiveId);
        });

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
        const filterButtons = document.querySelectorAll('.domain-tab[data-domain]');
        this.objectiveCards = this.objectiveCards.length ? this.objectiveCards : Array.from(document.querySelectorAll('.objective-card'));

        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                const selectedDomain = button.getAttribute('data-domain') || 'all';

                if (this.activeWorkflow === 'scoping') {
                    this.activeWorkflow = 'domains';
                    this.refreshWorkflowTabState();
                    this.toggleScopingView(false);
                }

                filterButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');

                if (selectedDomain === 'recap') {
                    this.activeDomainFilter = 'recap';
                    this.activeObjectiveFilter = 'all';
                    this.renderObjectiveFilterTabs('recap');
                    this.updateObjectiveVisibility();
                    this.updateDomainChart('recap');
                    return;
                }

                this.activeDomainFilter = selectedDomain;
                this.activeObjectiveFilter = 'all';
                if (this.activeDomainFilter !== 'recap') {
                    this.toggleRecapStandalone(false);
                }
                this.renderObjectiveFilterTabs(this.activeDomainFilter);
                this.updateObjectiveVisibility();
                this.updateDomainChart(this.activeDomainFilter);
            });
        });

        const initialActive = document.querySelector(`.domain-tab[data-domain="${this.activeDomainFilter}"]`);
        if (initialActive) {
            initialActive.classList.add('active');
        }
        this.renderObjectiveFilterTabs(this.activeDomainFilter);
        this.updateObjectiveVisibility();
        this.updateDomainChart(this.activeDomainFilter);
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
        const evidenceSelects = document.querySelectorAll('.evidence-history-select');
        
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
        const ratingSelect = document.querySelector(`select.activity-rating-select[data-activity-id="${activityId}"]`);
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

    addEvidenceToLibrary(value) {
        const trimmed = (value || '').trim();
        if (!trimmed || this.evidenceLibrary.has(trimmed)) {
            return;
        }
        this.evidenceLibrary.add(trimmed);
        this.refreshEvidenceDropdowns();
    }

    refreshEvidenceDropdowns() {
        const selects = document.querySelectorAll('.evidence-history-select');
        if (!selects.length) {
            return;
        }
        const evidenceList = Array.from(this.evidenceLibrary)
            .filter(Boolean)
            .sort((a, b) => a.localeCompare(b));
        selects.forEach(select => {
            const placeholder = select.getAttribute('data-placeholder') || 'Select saved evidence';
            const currentValue = select.value;
            select.innerHTML = '';

            const placeholderOption = document.createElement('option');
            placeholderOption.value = '';
            placeholderOption.textContent = `${placeholder}...`;
            select.appendChild(placeholderOption);

            evidenceList.forEach(entry => {
                const option = document.createElement('option');
                option.value = entry;
                option.textContent = entry.length > 90 ? `${entry.slice(0, 87)}...` : entry;
                if (entry === currentValue) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
        });
    }

    updateDomainChart(selectedDomain = 'all') {
        if (!this.domainChartContainer || !this.domainChartRows) {
            return;
        }

        if (selectedDomain === 'scoping') {
            selectedDomain = 'all';
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
    padding: 0.9rem 1rem;
    border: 1px solid #e1e6f5;
}

.domain-tabs-scroll {
    display: flex;
    gap: 0.65rem;
    overflow-x: auto;
    padding-bottom: 0.25rem;
    scrollbar-width: thin;
    scrollbar-color: #c7cee6 transparent;
}

.domain-tabs-scroll::-webkit-scrollbar {
    height: 6px;
}

.domain-tabs-scroll::-webkit-scrollbar-thumb {
    background: #cbd2eb;
    border-radius: 999px;
}

.domain-tabs-scroll::-webkit-scrollbar-track {
    background: transparent;
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

.workflow-tab {
    border: 1px dashed rgba(15,106,217,0.4);
    background: rgba(15,106,217,0.06);
    color: #0f2b5c;
    padding: 0.4rem 1.4rem;
    border-radius: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    transition: all 0.2s ease;
}

.workflow-tab:hover {
    background: rgba(15,106,217,0.15);
}

.workflow-tab.active {
    background: linear-gradient(120deg, #0f6ad9, #0c4fb5);
    color: #fff;
    border-style: solid;
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
.recap-standalone-wrapper {
    border: 1px solid #e2e8fb;
    border-radius: 0.9rem;
    background: #fff;
    box-shadow: 0 18px 40px rgba(15,43,92,0.08);
    padding: 1.5rem;
}
.recap-standalone-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}
.recap-label {
    font-size: 0.78rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #6f7a98;
    margin-bottom: 0.2rem;
}
.recap-title {
    margin: 0;
    font-weight: 700;
    color: #0f2b5c;
}
.recap-pill {
    background: #0f2b5c;
    color: #fff;
    letter-spacing: 0.08em;
}
    letter-spacing: 0.04em;
    border-bottom: 1px solid #e2e8fb;
}

.recap-table tbody tr:nth-child(odd) {
    background: #fbfcff;
}

.recap-table tbody tr:hover {
    background: #f0f4ff;
}

.scoping-panel {
    border: none;
    border-radius: 1rem;
    box-shadow: 0 25px 60px rgba(9, 18, 56, 0.12) !important;
}

.scoping-panel-header {
    background: #081a3d;
    color: #fff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}

.scoping-title {
    font-size: 1.2rem;
    font-weight: 700;
    letter-spacing: 0.04em;
}

.scoping-pill {
    border-radius: 999px;
    padding: 0.35rem 1rem;
    background: rgba(255,255,255,0.15);
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.scoping-panel-body {
    background: #fdfdff;
}

.scoping-table-wrapper {
    border: 1px solid #e1e6f5;
    border-radius: 0.9rem;
    background: #fff;
    padding: 0.75rem;
}

.scoping-table thead th {
    background: #f6f8ff;
    color: #42507a;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-size: 0.82rem;
    border-bottom: 1px solid #dbe2fb;
}

.scoping-table tbody tr {
    border-color: #eef2ff;
    cursor: pointer;
    transition: background 0.2s ease;
}

.scoping-table tbody tr:hover {
    background: #f3f7ff;
}

.scoping-row.selected {
    background: #e8f1ff;
    box-shadow: inset 0 0 0 1px rgba(15,106,217,0.25);
}

.scoping-gamo-code {
    font-weight: 700;
    color: #0f2b5c;
    letter-spacing: 0.05em;
}

.scoping-gamo-name {
    font-weight: 600;
    color: #0f2b5c;
}

.scoping-select-btn {
    font-weight: 600;
    min-width: 110px;
}

.scoping-selection-footer {
    margin-top: 1.5rem;
    padding-top: 1.25rem;
    border-top: 1px solid #e1e6f5;
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    align-items: center;
    justify-content: space-between;
}

.scoping-selection-summary {
    font-weight: 600;
    color: #0f2b5c;
}

.scoping-selection-actions {
    display: flex;
    gap: 0.75rem;
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

</style>
@endsection
