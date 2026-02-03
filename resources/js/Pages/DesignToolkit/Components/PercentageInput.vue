<script setup>
/**
 * PercentageInput - Percentage distribution input for DF5, DF6, DF8, DF9, DF10
 * 
 * Displays input fields for percentages that must sum to 100%.
 * Auto-adjusts values when one changes to maintain the sum.
 */
import { computed, ref } from 'vue';
import PieChart from './PieChart.vue';

const props = defineProps({
    /** Array of labels for each input (e.g., ['High', 'Normal'] for DF5) */
    labels: {
        type: Array,
        required: true,
    },
    /** v-model value - array of percentage values */
    modelValue: {
        type: Array,
        required: true,
    },
    /** Baseline values for comparison */
    baseline: {
        type: Array,
        default: () => [],
    },
    /** Description for each label (optional) */
    descriptions: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['update:modelValue']);

// Calculate total percentage
const total = computed(() => {
    return props.modelValue.reduce((sum, val) => sum + (val || 0), 0);
});

// Check if total is valid (100%)
const isValid = computed(() => {
    return Math.abs(total.value - 100) < 0.01;
});

// Update a single value and auto-adjust others
const updateValue = (index, rawValue) => {
    const value = Math.max(0, Math.min(100, parseFloat(rawValue) || 0));
    const newValues = [...props.modelValue];
    newValues[index] = value;
    
    // Auto-adjust remaining values if only 2 inputs (DF5)
    if (props.labels.length === 2) {
        const otherIndex = index === 0 ? 1 : 0;
        newValues[otherIndex] = 100 - value;
    }
    
    emit('update:modelValue', newValues);
};

// Get color class based on validity
const getTotalClass = computed(() => {
    if (isValid.value) return 'text-green-600 dark:text-green-400 bg-green-100 dark:bg-green-900/30';
    return 'text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-900/30';
});

// Get bar width for visual representation
const getBarWidth = (value) => {
    return `${Math.min(100, Math.max(0, value || 0))}%`;
};

// Color palette for bars
const barColors = [
    'bg-blue-500',
    'bg-green-500', 
    'bg-purple-500',
    'bg-orange-500',
    'bg-cyan-500',
];

const getBarColor = (index) => {
    return barColors[index % barColors.length];
};
</script>

<template>
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <!-- Header with total -->
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
                    Percentage Distribution
                </h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Values must sum to 100%
                </p>
            </div>
            <div 
                :class="[
                    'px-4 py-2 rounded-lg font-semibold',
                    getTotalClass
                ]"
            >
                Total: {{ total.toFixed(0) }}%
                <span v-if="!isValid" class="ml-2 text-xs">({{ total < 100 ? 'Need more' : 'Too much' }})</span>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 p-6">
            <!-- Left Column: Inputs -->
            <div class="space-y-6">
                <div 
                    v-for="(label, index) in labels" 
                    :key="index"
                    class="space-y-2 p-4 rounded-lg bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-700"
                >
                    <!-- Label and Input Row -->
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-slate-900 dark:text-white">
                                {{ label }}
                            </label>
                            <p v-if="descriptions[index]" class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                {{ descriptions[index] }}
                            </p>
                        </div>
                        
                        <!-- Input with % suffix -->
                        <div class="relative w-28">
                            <input
                                type="number"
                                :value="modelValue[index]"
                                @input="updateValue(index, $event.target.value)"
                                min="0"
                                max="100"
                                step="1"
                                class="w-full px-3 py-2 pr-8 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-right font-semibold"
                            />
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 font-medium">%</span>
                        </div>
                    </div>
                    
                    <!-- Baseline Compare -->
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>Baseline Ref:</span>
                        <span class="font-medium text-slate-700 dark:text-slate-300">{{ baseline[index] ?? 50 }}%</span>
                    </div>
                </div>
            </div>
            
            <!-- Right Column: Pie Chart -->
            <div class="flex flex-col items-center justify-center p-4 bg-slate-50 dark:bg-slate-900/30 rounded-xl border border-dotted border-slate-300 dark:border-slate-700">
                <h4 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4">Input Visualization</h4>
                <div class="w-full max-w-xs">
                    <PieChart :labels="labels" :data="modelValue" height="250px" />
                </div>
            </div>
        </div>
    </div>
</template>
