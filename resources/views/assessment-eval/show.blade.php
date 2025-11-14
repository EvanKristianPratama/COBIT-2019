@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container mx-auto p-6">
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
                </div>
            </div>
        </div>
    </div>

    {{-- Objectives Cards --}}
    <div class="row" id="objectives-container">
        @foreach($objectives as $objective)
            @php
                $domain = preg_replace('/\d+/', '', $objective->objective_id);
            @endphp
            <div class="col-12 mb-4 objective-card" data-domain="{{ $domain }}" data-objective-id="{{ $objective->objective_id }}">
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
                                        <div class="summary-label">Description</div>
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
                                                            <th>Description</th>
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
                                                                            <option value="N">N</option>
                                                                            <option value="P">P</option>
                                                                            <option value="L">L</option>
                                                                            <option value="F">F</option>
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
        this.init();
    }

    init() {
        this.setupDomainFiltering();
        this.setupAssessmentToggles();
        this.setupActivityRating();
        this.initializeDefaultStates();
        this.setupSaveLoadButtons();
        this.loadAssessment();
        this.refreshEvidenceDropdowns();
    }

    initializeDefaultStates() {
        const objectiveCards = document.querySelectorAll('.objective-card');
        objectiveCards.forEach(card => {
            const objectiveId = card.getAttribute('data-objective-id');
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
        const objectiveCards = document.querySelectorAll('.objective-card');
        const noResultsDiv = document.getElementById('no-results');

        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                filterButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');

                const selectedDomain = button.getAttribute('data-domain');
                let visibleCount = 0;

                objectiveCards.forEach(card => {
                    const cardDomain = card.getAttribute('data-domain');
                    
                    if (selectedDomain === 'all' || cardDomain === selectedDomain) {
                        card.style.display = 'block';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                noResultsDiv.style.display = visibleCount === 0 ? 'block' : 'none';
            });
        });
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
        this.addEvidenceToLibrary(evidence);
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
    background: #0f2b5c;
    border-radius: 0.65rem;
    padding: 0.5rem;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.08);
}

.domain-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
}

.domain-tab {
    border: none;
    background: rgba(255,255,255,0.15);
    color: #fff;
    padding: 0.45rem 1.1rem;
    border-radius: 999px;
    font-weight: 500;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    transition: all 0.2s ease;
}

.domain-tab:hover,
.domain-tab.active {
    background: #fff;
    color: #0f2b5c;
    box-shadow: 0 6px 18px rgba(15,43,92,0.2);
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
