<script setup>
/**
 * DF3 - Risk Profile
 * 
 * Assess the impact and likelihood of IT-related risks.
 * Uses Dropdown inputs for Impact (1-5) and Likelihood (1-5).
 * Risk Rating = Impact × Likelihood.
 */
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import PageHeader from '@/Components/PageHeader.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DesignFactorPagination from '../Components/DesignFactorPagination.vue';
import DropdownInput from '../Components/DropdownInput.vue';
import InputBarChart from '../Components/InputBarChart.vue';
import ChartCard from '../Components/ChartCard.vue';
import SpiderChart from '../Components/SpiderChart.vue';
import BarChart from '../Components/BarChart.vue';
import RelativeImportanceTable from '../Components/RelativeImportanceTable.vue';

const props = defineProps({
    dfNumber: Number,
    assessmentId: Number,
    inputCount: Number,
    map: Array,
    baselineScores: Array,
    objectiveLabels: Array,
    fields: Array,
    routes: Object,
    historyInputs: {
        type: Array,
        default: () => null,
    },
});

// Initialize inputs (array of {impact, likelihood})
const initializeInputs = () => {
    if (props.historyInputs) return props.historyInputs;
    return Array.from({ length: props.inputCount }, () => ({
        impact: 3,
        likelihood: 3
    }));
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

// Calculate Risk Ratings (Impact × Likelihood)
const riskRatings = computed(() => {
    return inputs.value.map(item => (item.impact || 0) * (item.likelihood || 0));
});

// Calculate scores using MAP matrix
const scores = computed(() => {
    return props.map.map((row) => {
        let score = 0;
        for (let j = 0; j < props.inputCount; j++) {
            score += row[j] * (riskRatings.value[j] || 0);
        }
        return score;
    });
});

// Calculate E14 (baseline ratio)
// Baseline input value is 9 (3*3) for all inputs
const BASELINE_VALUE = 9;
const e14 = computed(() => {
    const inputSum = riskRatings.value.reduce((a, b) => a + b, 0);
    const inputAvg = inputSum / props.inputCount;
    // const baselineAvg = BASELINE_VALUE; // Average of [9,9,...] is 9
    
    return inputAvg !== 0 ? BASELINE_VALUE / inputAvg : 0;
});

// Calculate Relative Importance
const relativeImportance = computed(() => {
    return scores.value.map((score, i) => {
        const baseline = props.baselineScores[i] ?? 0;
        if (baseline === 0) return 0;
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

// Input chart helpers
const inputChartLabels = computed(() => {
    return props.fields.map((field, i) => field.label || field.name || `Item ${i + 1}`);
});

const inputChartValues = computed(() => riskRatings.value.map((v) => v || 0));

const inputChartHeight = computed(() => {
    const rows = props.inputCount || inputChartLabels.value.length;
    return `${Math.max(260, rows * 26)}px`;
});
const barChartHeight = computed(() => {
    const rows = props.objectiveLabels?.length || 40;
    return `${Math.max(900, rows * 24)}px`;
});

</script>

<template>
    <AuthenticatedLayout title="DF3 - Risk Profile">
        <template #header>
            <PageHeader 
                title="Design Factor 3"
                subtitle="Risk Profile"
                :breadcrumbs="[
                    { label: 'Dashboard', href: routes.dashboard },
                    { label: 'Design Toolkit', href: routes.index },
                    { label: 'DF3 - Risk Profile' }
                ]"
            />
        </template>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <DesignFactorPagination :current-df="dfNumber" :routes="routes" position="top" />
            <form @submit.prevent="submit">
                <!-- Input Section -->
                <section class="mb-8">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">
                        Risk Profile Assessment
                    </h2>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">
                        Assess the risk profile of your enterprise by identifying significant IT-related risks.
                        Select <strong>Impact</strong> and <strong>Likelihood</strong> (1-5) for each risk category.
                        <br>
                        <span class="text-xs text-slate-500">Risk Rating = Impact × Likelihood</span>
                    </p>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div class="lg:col-span-2">
                            <DropdownInput
                                mode="df3"
                                :fields="fields"
                                :model-value="inputs"
                                @update:model-value="handleInputUpdate"
                            />
                        </div>
                        <ChartCard title="Input Risk Rating" subtitle="Impact × Likelihood (1–25)" :flush="true" :height="inputChartHeight">
                            <InputBarChart
                                :labels="inputChartLabels"
                                :data="inputChartValues"
                                :max="25"
                                :height="inputChartHeight"
                            />
                        </ChartCard>
                    </div>
                </section>
                
                <!-- Submit Button -->
                <div class="flex justify-end gap-4 mb-8">
                    <button 
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 rounded-md bg-[#1f4e79] text-white text-sm font-semibold hover:bg-[#163a59] disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        {{ form.processing ? 'Saving...' : 'Save Assessment' }}
                    </button>
                </div>

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
                    <ChartCard title="Relative Importance (Bar Chart)" subtitle="Horizontal visualization of 40 objectives" :height="barChartHeight">
                        <BarChart
                            :labels="objectiveLabels"
                            :data="relativeImportance"
                            :height="barChartHeight"
                        />
                    </ChartCard>
                </section>
                
                

            </form>
        </div>
    </AuthenticatedLayout>
</template>
