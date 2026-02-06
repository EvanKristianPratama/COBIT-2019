/**
 * NPLF Calculation Utilities for COBIT Assessment
 * Ported from app/Services/EvaluationService.php
 */

/**
 * Rating Map: Letter to Score conversion
 * N = Not achieved (0%), P = Partially (33%), L = Largely (67%), F = Fully (100%)
 */
export const RATING_MAP = {
    'N': 0.00,    // Not achieved (0-15%)
    'P': 1 / 3,     // Partially achieved (15-50%)
    'L': 2 / 3,     // Largely achieved (50-85%)
    'F': 1.00     // Fully achieved (85-100%)
};

/**
 * Convert numeric score (0-1) to letter grade
 * @param {number} score - Score between 0 and 1
 * @returns {string} Letter grade (N, P, L, or F)
 */
export function scoreToLetter(score) {
    if (score > 0.85) return 'F';
    if (score > 0.50) return 'L';
    if (score > 0.15) return 'P';
    return 'N';
}

/**
 * Convert letter grade to numeric score
 * @param {string} letter - Letter grade (N, P, L, or F)
 * @returns {number} Score between 0 and 1
 */
export function letterToScore(letter) {
    return RATING_MAP[letter] ?? 0;
}

/**
 * Get display info for a rating
 * @param {string} letter - Letter grade
 * @returns {Object} { letter, label, color, bgColor }
 */
export function getRatingInfo(letter) {
    const info = {
        'N': { letter: 'N', label: 'Not Achieved', color: '#dc3545', bgColor: 'rgba(220, 53, 69, 0.1)' },
        'P': { letter: 'P', label: 'Partially Achieved', color: '#fd7e14', bgColor: 'rgba(253, 126, 20, 0.1)' },
        'L': { letter: 'L', label: 'Largely Achieved', color: '#198754', bgColor: 'rgba(25, 135, 84, 0.1)' },
        'F': { letter: 'F', label: 'Fully Achieved', color: '#0d6efd', bgColor: 'rgba(13, 110, 253, 0.1)' }
    };
    return info[letter] || info['N'];
}

/**
 * Calculate capability level for a single objective based on activity scores
 * Implements cascading logic from EvaluationService.php
 * 
 * @param {Object} objective - Objective with practices and activities
 * @param {Object} activityData - Map of activity_id to { level_achieved }
 * @returns {number} Capability level 0-5
 */
export function calculateObjectiveMaturity(objective, activityData) {
    // Group activities by capability level (2-5)
    const activitiesByLevel = { 2: [], 3: [], 4: [], 5: [] };
    const allLevelsFound = [];

    if (objective.practices) {
        objective.practices.forEach(practice => {
            if (practice.activities) {
                practice.activities.forEach(activity => {
                    const lvl = parseInt(activity.capability_lvl);
                    if (lvl >= 2 && lvl <= 5) {
                        activitiesByLevel[lvl].push(activity);
                        allLevelsFound.push(lvl);
                    }
                });
            }
        });
    }

    if (allLevelsFound.length === 0) {
        return 0;
    }

    const minLevel = Math.min(...allLevelsFound);

    // Calculate average score for a given level
    const getScore = (lvl) => {
        if (lvl < minLevel) return 1.0;

        const acts = activitiesByLevel[lvl] || [];
        if (acts.length === 0) return 0.0;

        let total = 0;
        acts.forEach(a => {
            const rating = activityData[a.activity_id]?.level_achieved || 'N';
            total += RATING_MAP[rating] || 0;
        });

        return total / acts.length;
    };

    const score2 = getScore(2);
    const score3 = getScore(3);
    const score4 = getScore(4);
    const score5 = getScore(5);

    // Cascading level determination
    let finalLevel = 0;

    if (score2 <= 0.15) {
        finalLevel = 0;
    } else if (score2 <= 0.50) {
        finalLevel = 1;
    } else if (score2 <= 0.85) {
        finalLevel = 2;
    } else {
        // Level 2 passed (>0.85), check Level 3
        if (score3 <= 0.50) {
            finalLevel = 2;
        } else if (score3 <= 0.85) {
            finalLevel = 3;
        } else {
            // Level 3 passed, check Level 4
            if (score4 <= 0.50) {
                finalLevel = 3;
            } else if (score4 <= 0.85) {
                finalLevel = 4;
            } else {
                // Level 4 passed, check Level 5
                finalLevel = (score5 <= 0.50) ? 4 : 5;
            }
        }
    }

    // Special case: if minimum level > 2, check if starting score is too low
    if (minLevel > 2) {
        const startScore = getScore(minLevel);
        if (startScore <= 0.15) {
            finalLevel = 0;
        }
    }

    return finalLevel;
}

/**
 * Calculate overall I&T Maturity Score (average of all objectives)
 * @param {Array} objectives - Array of objectives with practices/activities
 * @param {Object} activityData - Map of activity_id to { level_achieved }
 * @returns {number} Average maturity score
 */
export function calculateOverallMaturity(objectives, activityData) {
    if (!objectives || objectives.length === 0) return 0;

    const scores = objectives.map(obj => calculateObjectiveMaturity(obj, activityData));
    const sum = scores.reduce((a, b) => a + b, 0);

    return sum / scores.length;
}

/**
 * Get level rating description
 * @param {number} level - Capability level 0-5
 * @returns {Object} { level, label, color }
 */
export function getLevelInfo(level) {
    const labels = {
        0: { label: 'Incomplete', color: '#dc3545' },
        1: { label: 'Performed', color: '#fd7e14' },
        2: { label: 'Managed', color: '#ffc107' },
        3: { label: 'Established', color: '#20c997' },
        4: { label: 'Predictable', color: '#198754' },
        5: { label: 'Optimizing', color: '#0d6efd' }
    };
    return { level, ...labels[level] || labels[0] };
}
