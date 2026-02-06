import { computed } from 'vue';
import { useRatingCalculation } from './useRatingCalculation';

/**
 * Chart Data Composable
 * Transforms assessment data for Chart.js radar charts and recap tables.
 */
export function useChartData(objectiveCapabilityLevels, levelScores, targetCapabilityMap = {}) {
    const { levelColors, levelTextColors, getScoreLetter } = useRatingCalculation();

    // Domain configuration
    const domainOrder = ['EDM', 'APO', 'BAI', 'DSS', 'MEA'];
    const domainNames = {
        EDM: 'Evaluate, Direct, and Monitor',
        APO: 'Align, Plan, and Organize',
        BAI: 'Build, Acquire, and Implement',
        DSS: 'Deliver, Service, and Support',
        MEA: 'Monitor, Evaluate, and Assess',
    };

    // All COBIT objectives
    const allObjectives = [
        'EDM01', 'EDM02', 'EDM03', 'EDM04', 'EDM05',
        'APO01', 'APO02', 'APO03', 'APO04', 'APO05', 'APO06', 'APO07', 'APO08', 'APO09',
        'APO10', 'APO11', 'APO12', 'APO13', 'APO14',
        'BAI01', 'BAI02', 'BAI03', 'BAI04', 'BAI05', 'BAI06', 'BAI07', 'BAI08', 'BAI09',
        'BAI10', 'BAI11',
        'DSS01', 'DSS02', 'DSS03', 'DSS04', 'DSS05', 'DSS06',
        'MEA01', 'MEA02', 'MEA03', 'MEA04',
    ];

    // Maximum capability per objective
    const maxCaps = [
        4, 5, 4, 4, 4, 5, 4, 5, 4, 5, 5, 4, 5, 4, 5, 5, 5, 5, 5, 5,
        4, 4, 5, 5, 4, 5, 5, 5, 5, 4, 5, 5, 5, 5, 4, 5, 5, 5, 5, 4,
    ];

    /**
     * Get max capability for an objective
     */
    const getMaxCapability = (objectiveId) => {
        const index = allObjectives.indexOf(objectiveId);
        return index !== -1 ? maxCaps[index] : 5;
    };

    /**
     * Get target capability for an objective
     */
    const getTargetCapability = (objectiveId) => {
        const val = targetCapabilityMap[objectiveId];
        if (val === null || val === undefined || val === '') return null;
        const num = Number(val);
        return isNaN(num) ? null : num;
    };

    /**
     * Get domain full name
     */
    const getDomainFullName = (code) => domainNames[code] || code;

    /**
     * Build recap data for all objectives
     */
    const buildRecapData = (objectives) => {
        const domainRank = (domain) => {
            const idx = domainOrder.indexOf(domain);
            return idx === -1 ? domainOrder.length : idx;
        };

        return objectives
            .map((obj) => {
                const domain = obj.domain || obj.objective_id?.replace(/\d+/g, '');
                const objectiveId = obj.objective_id || obj.objectiveId;
                const objectiveName = obj.objective_name || obj.objectiveName || objectiveId;
                if (!domain || !objectiveId) return null;

                const level = Math.min(Math.max(objectiveCapabilityLevels[objectiveId] || 0, 0), 5);
                let ratingLetter = 'N';
                if (level > 0 && levelScores[objectiveId]?.[level]) {
                    ratingLetter = levelScores[objectiveId][level].letter;
                }

                return {
                    domain,
                    objectiveId,
                    objectiveName,
                    level,
                    ratingLetter,
                };
            })
            .filter(Boolean)
            .sort((a, b) => {
                const domainComparison = domainRank(a.domain) - domainRank(b.domain);
                if (domainComparison !== 0) return domainComparison;
                return a.objectiveId.localeCompare(b.objectiveId, undefined, { numeric: true });
            });
    };

    /**
     * Build radar chart data
     */
    const buildRadarChartData = (recapData) => {
        const labels = recapData.map((item) => item.objectiveId);
        const capabilityData = recapData.map((item) => item.level);
        const targetData = labels.map((id) => getTargetCapability(id));
        const maxData = labels.map((id) => getMaxCapability(id));

        return {
            labels,
            datasets: [
                {
                    label: 'Capability Level',
                    data: capabilityData,
                    fill: true,
                    backgroundColor: 'rgba(54, 162, 235, 0.18)',
                    borderColor: 'rgb(37, 99, 235)',
                    pointBackgroundColor: 'rgb(37, 99, 235)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgb(37, 99, 235)',
                },
                {
                    label: 'Target Capability',
                    data: targetData,
                    fill: true,
                    backgroundColor: 'rgba(16, 185, 129, 0.18)',
                    borderColor: 'rgb(16, 185, 129)',
                    pointBackgroundColor: 'rgb(16, 185, 129)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgb(16, 185, 129)',
                },
                {
                    label: 'Maximum Capability',
                    data: maxData,
                    fill: true,
                    backgroundColor: 'rgba(255, 159, 64, 0.25)',
                    borderColor: 'rgb(255, 159, 64)',
                    pointBackgroundColor: 'rgb(255, 159, 64)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgb(255, 159, 64)',
                },
            ],
        };
    };

    /**
     * Calculate maturity score
     */
    const calculateMaturity = (recapData) => {
        if (!recapData.length) return '0.00';
        const total = recapData.reduce((sum, item) => sum + item.level, 0);
        return (total / recapData.length).toFixed(2);
    };

    /**
     * Build domain overview chart data
     */
    const buildDomainChartData = (objectives, selectedDomain) => {
        const filtered = objectives.filter((obj) => {
            const domain = obj.domain || obj.objective_id?.replace(/\d+/g, '');
            return domain === selectedDomain;
        });

        return filtered
            .map((obj) => {
                const objectiveId = obj.objective_id || obj.objectiveId;
                const objectiveName = obj.objective_name || obj.objectiveName || objectiveId;
                const level = Math.min(Math.max(objectiveCapabilityLevels[objectiveId] || 0, 0), 5);

                const ratings = {};
                for (let i = 1; i <= 5; i++) {
                    ratings[i] = levelScores[objectiveId]?.[i]?.letter || 'N';
                }

                return {
                    objectiveId,
                    objectiveName,
                    level,
                    ratings,
                    maxCapability: getMaxCapability(objectiveId),
                };
            })
            .sort((a, b) => a.objectiveId.localeCompare(b.objectiveId, undefined, { numeric: true }));
    };

    return {
        // Config
        domainOrder,
        domainNames,
        allObjectives,
        maxCaps,
        levelColors,
        levelTextColors,

        // Methods
        getMaxCapability,
        getTargetCapability,
        getDomainFullName,
        buildRecapData,
        buildRadarChartData,
        calculateMaturity,
        buildDomainChartData,
    };
}
