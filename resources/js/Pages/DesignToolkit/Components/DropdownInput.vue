<script setup>
/**
 * DropdownInput - Dropdown selector component for DF3 and DF4
 * 
 * DF3 Mode: Two dropdowns per row (Impact + Likelihood) with calculated Risk Rating
 * DF4 Mode: Single dropdown per row (1-3 scale: No Issue, Issue, Serious Issue)
 */
import { computed } from 'vue';

const props = defineProps({
    /** Array of field definitions */
    fields: {
        type: Array,
        required: true,
        // Each field: { name: string, label: string, description: string }
    },
    /** v-model value - array of values or objects */
    modelValue: {
        type: Array,
        required: true,
    },
    /** Mode: 'df3' for Impact+Likelihood, 'df4' for single dropdown 1-3 */
    mode: {
        type: String,
        required: true,
        validator: (v) => ['df3', 'df4'].includes(v),
    },
    /** Baseline values for comparison */
    baseline: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['update:modelValue']);

// DF3: Update Impact value
const updateImpact = (index, value) => {
    const newValues = [...props.modelValue];
    newValues[index] = {
        ...newValues[index],
        impact: parseInt(value) || 0,
    };
    emit('update:modelValue', newValues);
};

// DF3: Update Likelihood value
const updateLikelihood = (index, value) => {
    const newValues = [...props.modelValue];
    newValues[index] = {
        ...newValues[index],
        likelihood: parseInt(value) || 0,
    };
    emit('update:modelValue', newValues);
};

// DF4: Update single value
const updateValue = (index, value) => {
    const newValues = [...props.modelValue];
    newValues[index] = parseInt(value) || 0;
    emit('update:modelValue', newValues);
};

// DF3: Calculate risk rating
const getRiskRating = (index) => {
    const item = props.modelValue[index];
    if (!item || !item.impact || !item.likelihood) return 0;
    return item.impact * item.likelihood;
};

// DF3: Get risk rating color class
const getRiskColorClass = (rating) => {
    if (rating === 0) return 'bg-slate-100 dark:bg-slate-700 text-slate-500';
    if (rating <= 6) return 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400';
    if (rating <= 12) return 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400';
    return 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400';
};

// DF4: Get issue level color class
const getIssueColorClass = (value) => {
    if (value === 1) return 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400';
    if (value === 2) return 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400';
    if (value === 3) return 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400';
    return 'bg-slate-100 dark:bg-slate-700 text-slate-500';
};

// DF4: Get issue level label
const getIssueLabel = (value) => {
    const labels = { 1: 'No Issue', 2: 'Issue', 3: 'Serious Issue' };
    return labels[value] || '-';
};
</script>

<template>
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <!-- Legend -->
        <div class="px-6 py-3 bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700">
            <div v-if="mode === 'df3'" class="flex items-center gap-6 text-xs">
                <span class="font-medium text-slate-700 dark:text-slate-300">Risk Rating:</span>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded bg-green-500"></span>
                    <span class="text-slate-600 dark:text-slate-400">Low (1-6)</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded bg-yellow-500"></span>
                    <span class="text-slate-600 dark:text-slate-400">Medium (7-12)</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded bg-red-500"></span>
                    <span class="text-slate-600 dark:text-slate-400">High (>12)</span>
                </div>
            </div>
            <div v-else class="flex items-center gap-6 text-xs">
                <span class="font-medium text-slate-700 dark:text-slate-300">Issue Level:</span>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded bg-green-500"></span>
                    <span class="text-slate-600 dark:text-slate-400">No Issue (1)</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded bg-yellow-500"></span>
                    <span class="text-slate-600 dark:text-slate-400">Issue (2)</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded bg-red-500"></span>
                    <span class="text-slate-600 dark:text-slate-400">Serious Issue (3)</span>
                </div>
            </div>
        </div>
        
        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 dark:bg-slate-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider w-16">
                            #
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                            {{ mode === 'df3' ? 'Risk Category' : 'IT-Related Issue' }}
                        </th>
                        
                        <!-- DF3: Impact + Likelihood + Risk Rating -->
                        <template v-if="mode === 'df3'">
                            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider w-32">
                                Impact (1-5)
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider w-32">
                                Likelihood (1-5)
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider w-32">
                                Risk Rating
                            </th>
                        </template>
                        
                        <!-- DF4: Single dropdown -->
                        <template v-else>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider w-40">
                                Importance (1-3)
                            </th>
                        </template>
                        
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider w-24">
                            Baseline
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    <tr 
                        v-for="(field, index) in fields" 
                        :key="field.name"
                        class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors"
                    >
                        <!-- Index -->
                        <td class="px-4 py-4 text-sm font-medium text-blue-600 dark:text-blue-400">
                            {{ index + 1 }}
                        </td>
                        
                        <!-- Description -->
                        <td class="px-4 py-4">
                            <div class="text-sm text-slate-900 dark:text-white">
                                {{ field.description }}
                            </div>
                        </td>
                        
                        <!-- DF3: Impact + Likelihood + Risk Rating -->
                        <template v-if="mode === 'df3'">
                            <!-- Impact Dropdown -->
                            <td class="px-4 py-4">
                                <select
                                    :value="modelValue[index]?.impact || ''"
                                    @change="updateImpact(index, $event.target.value)"
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option value="" disabled>Select</option>
                                    <option v-for="n in 5" :key="n" :value="n">{{ n }}</option>
                                </select>
                            </td>
                            
                            <!-- Likelihood Dropdown -->
                            <td class="px-4 py-4">
                                <select
                                    :value="modelValue[index]?.likelihood || ''"
                                    @change="updateLikelihood(index, $event.target.value)"
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option value="" disabled>Select</option>
                                    <option v-for="n in 5" :key="n" :value="n">{{ n }}</option>
                                </select>
                            </td>
                            
                            <!-- Risk Rating (calculated) -->
                            <td class="px-4 py-4 text-center">
                                <span 
                                    :class="[
                                        'inline-flex items-center justify-center w-12 h-10 rounded-lg font-semibold',
                                        getRiskColorClass(getRiskRating(index))
                                    ]"
                                >
                                    {{ getRiskRating(index) || '-' }}
                                </span>
                            </td>
                        </template>
                        
                        <!-- DF4: Single dropdown -->
                        <template v-else>
                            <td class="px-4 py-4">
                                <select
                                    :value="modelValue[index] || ''"
                                    @change="updateValue(index, $event.target.value)"
                                    :class="[
                                        'w-full px-3 py-2 rounded-lg border focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
                                        getIssueColorClass(modelValue[index])
                                    ]"
                                >
                                    <option value="" disabled>Select</option>
                                    <option value="1">1 - No Issue</option>
                                    <option value="2">2 - Issue</option>
                                    <option value="3">3 - Serious Issue</option>
                                </select>
                            </td>
                        </template>
                        
                        <!-- Baseline -->
                        <td class="px-4 py-4 text-center">
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 font-semibold">
                                {{ baseline[index] ?? (mode === 'df3' ? 9 : 2) }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
