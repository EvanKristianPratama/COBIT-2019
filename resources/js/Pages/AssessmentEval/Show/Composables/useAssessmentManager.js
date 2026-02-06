import { ref, reactive, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useRatingCalculation } from './useRatingCalculation';

/**
 * Assessment Manager Composable
 * Core state management for assessment data, save/load operations, and interface locking.
 */
export function useAssessmentManager(evalId, initialStatus = 'draft', isOwner = false) {
    const { getRatingValue, getScoreLetter, calculateCapabilityLevel, calculateLevelScore, isLevelLocked } =
        useRatingCalculation();

    // Core state
    const status = ref(initialStatus);
    const loading = ref(false);
    const loadingMessage = ref('');
    const loadingProgress = ref(0);
    const isInterfaceLocked = ref(initialStatus === 'finished' || !isOwner);

    // Assessment data structures
    const levelScores = reactive({});
    const objectiveCapabilityLevels = reactive({});

    // Evidence library
    const evidenceLibrary = ref(new Set());

    /**
     * Initialize level score for an objective/level
     */
    const initializeLevelScore = (objectiveId, level) => {
        if (!levelScores[objectiveId]) {
            levelScores[objectiveId] = {};
        }
        if (!levelScores[objectiveId][level]) {
            levelScores[objectiveId][level] = {
                letter: 'N',
                score: 0,
                activities: {},
                evidence: {},
                notes: {},
            };
        }
    };

    /**
     * Set activity rating
     */
    const setActivityRating = (objectiveId, level, activityId, rating) => {
        initializeLevelScore(objectiveId, level);
        if (!rating) {
            delete levelScores[objectiveId][level].activities[activityId];
            return;
        }
        levelScores[objectiveId][level].activities[activityId] = getRatingValue(rating);
    };

    /**
     * Set activity evidence
     */
    const setActivityEvidence = (objectiveId, level, activityId, evidence) => {
        initializeLevelScore(objectiveId, level);
        levelScores[objectiveId][level].evidence[activityId] = evidence;
    };

    /**
     * Set activity note
     */
    const setActivityNote = (objectiveId, level, activityId, note) => {
        initializeLevelScore(objectiveId, level);
        levelScores[objectiveId][level].notes[activityId] = note;
    };

    /**
     * Update level capability score
     */
    const updateLevelCapability = (objectiveId, level, totalActivities) => {
        const levelData = levelScores[objectiveId]?.[level];
        if (!levelData) return;

        const ratedCount = Object.keys(levelData.activities).length;
        const avgScore = totalActivities > 0 ? calculateLevelScore(levelData.activities) : 0;

        levelData.score = avgScore;
        levelData.letter = getScoreLetter(avgScore);

        return { score: avgScore, letter: levelData.letter, ratedCount };
    };

    /**
     * Update objective capability level
     */
    const updateObjectiveCapabilityLevel = (objectiveId, minLevel = 2) => {
        const scores = {};
        for (let lvl = 2; lvl <= 5; lvl++) {
            scores[lvl] = levelScores[objectiveId]?.[lvl]?.score || 0;
        }
        const capLevel = calculateCapabilityLevel(scores, minLevel);
        objectiveCapabilityLevels[objectiveId] = capLevel;
        return capLevel;
    };

    /**
     * Show loading overlay
     */
    const showLoading = (message = 'Loading...', progress = 0) => {
        loading.value = true;
        loadingMessage.value = message;
        loadingProgress.value = progress;
    };

    /**
     * Update loading progress
     */
    const updateLoadingProgress = (message, progress) => {
        loadingMessage.value = message;
        loadingProgress.value = progress;
    };

    /**
     * Hide loading overlay
     */
    const hideLoading = () => {
        loading.value = false;
    };

    /**
     * Save assessment to server
     */
    const saveAssessment = async (fieldData) => {
        showLoading('Saving assessment...', 0);

        try {
            updateLoadingProgress('Preparing data...', 30);

            const payload = {
                assessmentData: levelScores,
                notes: fieldData.notes,
                evidence: fieldData.evidence,
                evidenceNames: fieldData.evidenceNames,
            };

            updateLoadingProgress('Saving to server...', 60);

            const response = await fetch(`/assessment-eval/${evalId}/save`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                },
                body: JSON.stringify(payload),
            });

            updateLoadingProgress('Processing...', 90);
            const result = await response.json();

            if (response.ok) {
                updateLoadingProgress('Saved!', 100);
                setTimeout(hideLoading, 500);
                return { success: true, message: 'Assessment saved successfully!' };
            } else {
                hideLoading();
                return { success: false, message: result.message || 'Failed to save' };
            }
        } catch (error) {
            console.error('Save error:', error);
            hideLoading();
            return { success: false, message: 'Failed to save assessment' };
        }
    };

    /**
     * Load assessment from server
     */
    const loadAssessment = async () => {
        showLoading('Loading assessment...', 0);

        try {
            updateLoadingProgress('Fetching data...', 20);

            const response = await fetch(`/assessment-eval/${evalId}/load`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                },
            });

            const result = await response.json();

            if (!response.ok || !result.data) {
                hideLoading();
                return { success: false, data: null };
            }

            updateLoadingProgress('Processing data...', 60);
            const data = result.data;

            updateLoadingProgress('Complete!', 100);
            setTimeout(hideLoading, 300);

            return { success: true, data };
        } catch (error) {
            console.error('Load error:', error);
            hideLoading();
            return { success: false, data: null };
        }
    };

    /**
     * Finish assessment (lock)
     */
    const finishAssessment = async () => {
        try {
            const response = await fetch(`/assessment-eval/${evalId}/finish`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                },
            });

            if (!response.ok) {
                const payload = await response.json().catch(() => ({}));
                throw new Error(payload.message || 'Failed to finish');
            }

            status.value = 'finished';
            isInterfaceLocked.value = true;
            return { success: true };
        } catch (error) {
            console.error('Finish error:', error);
            return { success: false, message: error.message };
        }
    };

    /**
     * Unlock assessment (edit mode)
     */
    const unlockAssessment = async () => {
        try {
            const response = await fetch(`/assessment-eval/${evalId}/unlock`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                },
            });

            if (!response.ok) {
                const payload = await response.json().catch(() => ({}));
                throw new Error(payload.message || 'Failed to unlock');
            }

            status.value = 'in_progress';
            isInterfaceLocked.value = false;
            return { success: true };
        } catch (error) {
            console.error('Unlock error:', error);
            return { success: false, message: error.message };
        }
    };

    /**
     * Parse legacy note payload format
     */
    const parseNotePayload = (rawValue, rawEvidence = null) => {
        if (rawEvidence !== null && rawEvidence !== undefined) {
            return { evidence: rawEvidence || '', note: rawValue || '' };
        }

        if (!rawValue) return { evidence: '', note: '' };

        if (typeof rawValue === 'string') {
            try {
                const parsed = JSON.parse(rawValue);
                if (parsed && typeof parsed === 'object') {
                    return {
                        evidence: parsed.evidence || parsed.comment || '',
                        note: parsed.note || parsed.notes || '',
                    };
                }
            } catch {
                return { evidence: rawValue, note: '' };
            }
        }

        if (typeof rawValue === 'object') {
            return {
                evidence: rawValue.evidence || rawValue.comment || '',
                note: rawValue.note || rawValue.notes || '',
            };
        }

        return { evidence: '', note: '' };
    };

    return {
        // State
        status,
        loading,
        loadingMessage,
        loadingProgress,
        isInterfaceLocked,
        levelScores,
        objectiveCapabilityLevels,
        evidenceLibrary,

        // Methods
        initializeLevelScore,
        setActivityRating,
        setActivityEvidence,
        setActivityNote,
        updateLevelCapability,
        updateObjectiveCapabilityLevel,
        showLoading,
        updateLoadingProgress,
        hideLoading,
        saveAssessment,
        loadAssessment,
        finishAssessment,
        unlockAssessment,
        parseNotePayload,
    };
}
