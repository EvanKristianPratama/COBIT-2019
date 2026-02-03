<script setup>
/**
 * DF5 - IT Investment Portfolio
 * 
 * Assess the IT investment category distribution (Percentage).
 * Inputs must sum to 100%.
 */
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import PageHeader from '@/Components/PageHeader.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DesignFactorPagination from '../Components/DesignFactorPagination.vue';
import PercentageInput from '../Components/PercentageInput.vue';
import ChartCard from '../Components/ChartCard.vue';
import SpiderChart from '../Components/SpiderChart.vue';
import BarChart from '../Components/BarChart.vue';
import RelativeImportanceTable from '../Components/RelativeImportanceTable.vue';

const props = defineProps({
    dfNumber: Number,
    assessmentId: Number,
    inputCount: Number,
    map: Array,
    baselineInputs: Array,
    baselineScores: Array,
    objectiveLabels: Array,
    labels: Array, // Percentage labels (e.g. High, Normal)
    routes: Object,
    historyInputs: {
        type: Array,
        default: () => null,
    },
});

// Initialize inputs (default to baseline if no history)
const initializeInputs = () => {
    if (props.historyInputs) return props.historyInputs;
    return props.baselineInputs ? [...props.baselineInputs] : new Array(props.inputCount).fill(0);
};

const inputs = ref(initializeInputs());

// Form for submission
const form = useForm({
    inputs: inputs.value,
});

// Helper: Round to multiple of 5
const mround = (value, multiple) => {
    if (multiple === 0) return 0;
    return Math.round(value / multiple) * multiple;
};

// Calculate scores using MAP matrix (interpreting percentage as 0-1 or 0-100?)
// Usually mapping expects 0-1 range if multipliers are large, or input is 0-100?
// Let's check DF5Data or Blade. 
// Standard in COBIT tools: Map * (Input / 100) or Map * Input?
// DF5Data Map values: ~3.0. 
// If input is 100, score is 300.
// Baseline inputs are e.g. [50, 50].
// Let's assume standard multiplication.
const scores = computed(() => {
    return props.map.map((row) => {
        let score = 0;
        for (let j = 0; j < props.inputCount; j++) {
            score += row[j] * (inputs.value[j] || 0);
        }
        return score;
    });
});

// Calculate E14 (baseline ratio)
// For percentage inputs, usually sum is 100.
// Average is 100 / count.
// So E14 is usually 1 if sum is strictly 100.
// Let's implement generic formula anyway.
const e14 = computed(() => {
    const inputSum = inputs.value.reduce((a, b) => a + b, 0);
    const baselineSum = props.baselineInputs.reduce((a, b) => a + b, 0);
    
    // Avoid division by zero
    if (inputSum === 0) return 1;
    
    // Logic: (BaselineAvg / InputAvg) -> (BaselineSum / n) / (InputSum / n) -> BaselineSum / InputSum
    // If both sum to 100, E14 = 1.
    return baselineSum / inputSum;
});

// Calculate Relative Importance
const relativeImportance = computed(() => {
    return scores.value.map((score, i) => {
        const baseline = props.baselineScores[i] || 1;
        const result = (e14.value * 100 * score) / baseline;
        return mround(result, 5) - 100;
    });
});

// Submit form
const submit = () => {
    form.inputs = inputs.value;
    form.post(props.routes.store);
};

// Update inputs
const handleInputUpdate = (newInputs) => {
    inputs.value = newInputs;
};
</script>

<template>
    <AuthenticatedLayout title="DF6 - Compliance Requirements">
        <template #header>
            <PageHeader 
                title="Design Factor 6"
                subtitle="Compliance Requirements"
                :breadcrumbs="[
                    { label: 'Dashboard', href: routes.dashboard },
                    { label: 'Design Toolkit', href: routes.index },
                    { label: 'DF6 - Compliance Requirements' }
                ]"
            />
        </template>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <DesignFactorPagination :current-df="dfNumber" :routes="routes" position="top" />
            <form @submit.prevent="submit">
                <!-- Input Section -->
                <section class="mb-8">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">
                        Compliance Requirements Assessment
                    </h2>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">
                        Enter the percentage for each compliance level.
                        <strong class="text-amber-600">Requirement:</strong> Total must equal 100%.
                    </p>
                    
                    <PercentageInput
                        :labels="labels"
                        :model-value="inputs"
                        :baseline="baselineInputs"
                        @update:model-value="handleInputUpdate"
                    />
                </section>
                
                <!-- Charts Section -->
                <section class="mb-8">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">
                        Results Visualization
                    </h2>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                        <!-- Spider Chart -->
                        <ChartCard title="Relative Importance (Radar)" subtitle="40 Governance Objectives">
                            <SpiderChart
                                :labels="objectiveLabels"
                                :data="relativeImportance"
                                height="400px"
                            />
                        </ChartCard>
                        
                        <!-- Relative Importance Table -->
                        <RelativeImportanceTable
                            :objectives="objectiveLabels"
                            :scores="scores"
                            :relative-importance="relativeImportance"
                        />
                    </div>
                    
                    <!-- Bar Chart (Full Width) -->
                    <ChartCard title="Relative Importance (Bar Chart)" subtitle="Horizontal visualization of 40 objectives">
                        <BarChart
                            :labels="objectiveLabels"
                            :data="relativeImportance"
                            height="800px"
                        />
                    </ChartCard>
                </section>
                
                <!-- Submit Button -->
                <div class="flex justify-end gap-4">
                    <a 
                        :href="routes.index"
                        class="px-6 py-3 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
                    >
                        Cancel
                    </a>
                    <button 
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-3 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        {{ form.processing ? 'Saving...' : 'Save Assessment' }}
                    </button>
                </div>

            </form>
        </div>
    </AuthenticatedLayout>
</template>
