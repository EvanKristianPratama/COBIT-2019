<script setup>
/**
 * DF2 - Enterprise Goals
 * 
 * Rate the importance of each enterprise goal for your organization.
 * Uses 2-step matrix multiplication for score calculation.
 */
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import PageHeader from '@/Components/PageHeader.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DesignFactorPagination from '../Components/DesignFactorPagination.vue';
import InputTable from '../Components/InputTable.vue';
import ChartCard from '../Components/ChartCard.vue';
import SpiderChart from '../Components/SpiderChart.vue';
import BarChart from '../Components/BarChart.vue';
import RelativeImportanceTable from '../Components/RelativeImportanceTable.vue';

const props = defineProps({
    dfNumber: Number,
    assessmentId: Number,
    inputCount: Number,
    map1: Array,
    map2: Array,
    baselineInputs: Array,
    baselineScores: Array,
    objectiveLabels: Array,
    fields: Array,
    routes: Object,
    historyInputs: {
        type: Array,
        default: () => null,
    },
});

// Initialize inputs (from history or defaults)
const inputs = ref(
    props.historyInputs || new Array(props.inputCount).fill(3)
);

// Form for submission
const form = useForm({
    inputs: inputs.value,
});

// Helper: Round to multiple of 5
const mround = (value, multiple) => {
    if (multiple === 0) return 0;
    return Math.round(value / multiple) * multiple;
};

// Step 1: Calculate Transition Scores using MAP_1
const transitionScores = computed(() => {
    // MAP_1 rows correspond to intermediate objectives
    return props.map1.map((row) => {
        let score = 0;
        for (let j = 0; j < props.inputCount; j++) {
            score += row[j] * (inputs.value[j] || 0);
        }
        return score;
    });
});

// Step 2: Calculate Final Scores using MAP_2
const scores = computed(() => {
    // MAP_2 rows correspond to final governance objectives (40)
    return props.map2.map((row) => {
        let score = 0;
        for (let j = 0; j < transitionScores.value.length; j++) {
            score += row[j] * transitionScores.value[j];
        }
        return score;
    });
});

// Calculate E14 (baseline ratio)
const e14 = computed(() => {
    const inputAvg = inputs.value.reduce((a, b) => a + b, 0) / inputs.value.length;
    const baselineAvg = props.baselineInputs.reduce((a, b) => a + b, 0) / props.baselineInputs.length;
    return inputAvg > 0 ? baselineAvg / inputAvg : 1;
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

// Update inputs when changed
const handleInputUpdate = (newInputs) => {
    inputs.value = newInputs;
};
</script>

<template>
    <AuthenticatedLayout title="DF2 - Enterprise Goals">
        <template #header>
            <PageHeader 
                title="Design Factor 2"
                subtitle="Enterprise Goals"
                :breadcrumbs="[
                    { label: 'Dashboard', href: routes.dashboard },
                    { label: 'Design Toolkit', href: routes.index },
                    { label: 'DF2 - Enterprise Goals' }
                ]"
            />
        </template>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <DesignFactorPagination :current-df="dfNumber" :routes="routes" position="top" />
            <form @submit.prevent="submit">
                <!-- Input Section -->
                <section class="mb-8">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">
                        Enterprise Goals Assessment
                    </h2>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">
                        Rate the importance of each enterprise goal for your organization (1 = Low, 5 = High).
                        <strong class="text-amber-600">Guidance:</strong> Typical enterprises prioritize 3–5 goals (score 5) and rate remaining goals lower (score 1–4).
                    </p>
                    
                    <InputTable
                        :fields="fields"
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
