import { ref, reactive, computed, watch } from 'vue';

/**
 * NPLF Rating Calculation Composable
 * Provides functions for calculating COBIT capability scores and levels.
 */
export function useRatingCalculation() {
    // Rating value map (N, P, L, F)
    const ratingMap = {
        N: 0,
        P: 1 / 3,
        L: 2 / 3,
        F: 1,
    };

    // Level colors for UI display
    const levelColors = {
        0: '#dc3545', // Red
        1: '#f97316', // Orange
        2: '#facc15', // Yellow
        3: '#86efac', // Light Green
        4: '#15803d', // Green
        5: '#0f6ad9', // Blue
    };

    const levelTextColors = {
        0: '#fff',
        1: '#fff',
        2: '#7a5d07',
        3: '#065f46',
        4: '#fff',
        5: '#fff',
    };

    /**
     * Get numeric value for a rating letter
     */
    const getRatingValue = (rating) => {
        return ratingMap[rating] || 0;
    };

    /**
     * Get rating letter from score
     */
    const getScoreLetter = (score) => {
        if (score > 0.85) return 'F';
        if (score > 0.5) return 'L';
        if (score > 0.15) return 'P';
        return 'N';
    };

    /**
     * Calculate capability level based on scores at each level
     * Uses COBIT formula:
     * =IF(L2<=0.15,0, IF(L2<=0.5,1, IF(L2<=0.85,2, IF(L3<=0.5,2, IF(L3<=0.85,3, IF(L4<=0.5,3, IF(L4<=0.85,4, IF(L5<=0.5,4,5))))))))
     */
    const calculateCapabilityLevel = (scores, minLevel = 2) => {
        const getScore = (lvl) => {
            if (lvl < minLevel) return 1.0;
            return scores[lvl] || 0;
        };

        const score2 = getScore(2);
        const score3 = getScore(3);
        const score4 = getScore(4);
        const score5 = getScore(5);

        let finalLevel = 0;

        if (score2 <= 0.15) {
            finalLevel = 0;
        } else if (score2 <= 0.5) {
            finalLevel = 1;
        } else if (score2 <= 0.85) {
            finalLevel = 2;
        } else {
            // Level 2 > 0.85, check Level 3
            if (score3 <= 0.5) {
                finalLevel = 2;
            } else if (score3 <= 0.85) {
                finalLevel = 3;
            } else {
                // Level 3 > 0.85, check Level 4
                if (score4 <= 0.5) {
                    finalLevel = 3;
                } else if (score4 <= 0.85) {
                    finalLevel = 4;
                } else {
                    // Level 4 > 0.85, check Level 5
                    if (score5 <= 0.5) {
                        finalLevel = 4;
                    } else {
                        finalLevel = 5;
                    }
                }
            }
        }

        // Special handling for objectives starting at higher levels
        if (minLevel > 2) {
            const startScore = getScore(minLevel);
            if (startScore <= 0.15) {
                finalLevel = 0;
            }
        }

        return finalLevel;
    };

    /**
     * Calculate average score for a level
     */
    const calculateLevelScore = (activities) => {
        const values = Object.values(activities);
        if (values.length === 0) return 0;
        const total = values.reduce((sum, val) => sum + val, 0);
        return total / values.length;
    };

    /**
     * Get score chip CSS class based on score value
     */
    const getScoreColorClass = (score) => {
        if (score === 0) return 'bg-red-100 text-red-700 border-red-300';
        if (score < 0.5) return 'bg-amber-100 text-amber-700 border-amber-300';
        if (score < 0.85) return 'bg-sky-100 text-sky-700 border-sky-300';
        return 'bg-emerald-100 text-emerald-700 border-emerald-300';
    };

    /**
     * Get level badge CSS classes
     */
    const getLevelBadgeClass = (level) => {
        const classes = {
            0: 'bg-red-500 text-white',
            1: 'bg-orange-100 text-orange-800 border-orange-300',
            2: 'bg-yellow-100 text-yellow-800 border-yellow-400',
            3: 'bg-green-100 text-green-800 border-green-400',
            4: 'bg-green-600 text-white',
            5: 'bg-blue-600 text-white',
        };
        return classes[level] || classes[0];
    };

    /**
     * Check if a level should be locked based on previous level score
     */
    const isLevelLocked = (levelScores, level, minLevel = 2) => {
        if (level === minLevel) return false;
        const prevLevelData = levelScores[level - 1];
        return !prevLevelData || prevLevelData.letter !== 'F';
    };

    return {
        ratingMap,
        levelColors,
        levelTextColors,
        getRatingValue,
        getScoreLetter,
        calculateCapabilityLevel,
        calculateLevelScore,
        getScoreColorClass,
        getLevelBadgeClass,
        isLevelLocked,
    };
}
