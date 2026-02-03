<script setup>
/**
 * RelativeImportanceTable - Display calculation results with color coding
 * 
 * Shows 40 governance objectives with their scores and relative importance values.
 * Color coding: Blue for positive RI, Red for negative RI, Gray for zero.
 */
import { computed } from 'vue';

const props = defineProps({
    /** Array of objective labels (e.g., ['EDM01', 'EDM02', ...]) */
    objectives: {
        type: Array,
        required: true,
    },
    /** Array of calculated scores */
    scores: {
        type: Array,
        required: true,
    },
    /** Array of relative importance values */
    relativeImportance: {
        type: Array,
        required: true,
    },
    /** Maximum height with scroll */
    maxHeight: {
        type: String,
        default: '400px',
    },
});

const tableData = computed(() => {
    return props.objectives.map((label, index) => ({
        index: index + 1,
        label,
        score: props.scores[index] ?? 0,
        ri: props.relativeImportance[index] ?? 0,
    }));
});

const getRowClass = (ri) => {
    if (ri > 0) return 'bg-blue-50 dark:bg-blue-900/20';
    if (ri < 0) return 'bg-red-50 dark:bg-red-900/20';
    return '';
};

const getRIClass = (ri) => {
    if (ri > 0) return 'text-blue-600 dark:text-blue-400 font-semibold';
    if (ri < 0) return 'text-red-600 dark:text-red-400 font-semibold';
    return 'text-slate-500 dark:text-slate-400';
};

const formatRI = (ri) => {
    if (ri > 0) return `+${ri}`;
    return ri.toString();
};
</script>

<template>
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
                Relative Importance Table
            </h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                40 Governance Objectives with calculated scores
            </p>
        </div>
        
        <!-- Table Container with scroll -->
        <div :style="{ maxHeight }" class="overflow-y-auto">
            <table class="w-full">
                <thead class="bg-slate-50 dark:bg-slate-700 sticky top-0">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                            #
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                            Objective
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                            Score
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                            Relative Importance
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    <tr 
                        v-for="row in tableData" 
                        :key="row.index"
                        :class="getRowClass(row.ri)"
                    >
                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400">
                            {{ row.index }}
                        </td>
                        <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-white">
                            {{ row.label }}
                        </td>
                        <td class="px-4 py-3 text-sm text-center text-slate-600 dark:text-slate-400">
                            {{ row.score.toFixed(2) }}
                        </td>
                        <td :class="['px-4 py-3 text-sm text-center', getRIClass(row.ri)]">
                            {{ formatRI(row.ri) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Legend -->
        <div class="px-6 py-3 bg-slate-50 dark:bg-slate-700/50 border-t border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-6 text-xs">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded bg-blue-500"></span>
                    <span class="text-slate-600 dark:text-slate-400">Positive (Higher Priority)</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded bg-red-500"></span>
                    <span class="text-slate-600 dark:text-slate-400">Negative (Lower Priority)</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded bg-slate-300 dark:bg-slate-600"></span>
                    <span class="text-slate-600 dark:text-slate-400">Neutral</span>
                </div>
            </div>
        </div>
    </div>
</template>
