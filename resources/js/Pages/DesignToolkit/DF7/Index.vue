<script setup>
/**
 * DF7 - Role of IT
 * 
 * Assess the importance of the role of IT (Support, Factory, Turnaround, Strategic).
 * Uses radio button inputs (1-5 scale).
 */
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import PageHeader from '@/Components/PageHeader.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DesignFactorPagination from '../Components/DesignFactorPagination.vue';
import InputTable from '../Components/InputTable.vue';
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

// Default baseline to 3 if not provided
const baselineData = computed(() => {
    return props.baselineInputs || new Array(props.inputCount).fill(3);
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

// Calculate scores using MAP matrix
const scores = computed(() => {
    return props.map.map((row) => {
        let score = 0;
        for (let j = 0; j < props.inputCount; j++) {
            score += row[j] * (inputs.value[j] || 0);
        }
        return Math.round(score * 100) / 100;
    });
});

// Calculate E14 (baseline ratio)
const e14 = computed(() => {
    const inputAvg = inputs.value.reduce((a, b) => a + b, 0) / inputs.value.length;
    const baselineAvg = baselineData.value.reduce((a, b) => a + b, 0) / baselineData.value.length;
    // Calculate ratio: Baseline / Input
    return baselineAvg / inputAvg;
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

// Update inputs when changed
const handleInputUpdate = (newInputs) => {
    inputs.value = newInputs;
};

// Input chart helpers
const inputChartLabels = computed(() => {
    return props.fields.map((field, i) => field.label || field.name || `Item ${i + 1}`);
});

const inputChartValues = computed(() => inputs.value.map((v) => v || 0));

const inputChartHeight = computed(() => {
    const rows = props.inputCount || inputChartLabels.value.length;
    return `${Math.max(220, rows * 28)}px`;
});
</script>

<template>
    <AuthenticatedLayout title="DF7 - Role of IT">
        <template #header>
            <PageHeader 
                title="Design Factor 7"
                subtitle="Importance of Role of IT"
                :breadcrumbs="[
                    { label: 'Dashboard', href: routes.dashboard },
                    { label: 'Design Toolkit', href: routes.index },
                    { label: 'DF7 - Role of IT' }
                ]"
            />
        </template>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <DesignFactorPagination :current-df="dfNumber" :routes="routes" position="top" />
            <form @submit.prevent="submit">
                <!-- Input Section -->
                <section class="mb-8">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">
                        Role of IT Assessment
                    </h2>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">
                        Rate the importance of each role of IT (1 = Low, 5 = High).
                    </p>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div class="lg:col-span-2">
                            <InputTable
                                :fields="fields"
                                :model-value="inputs"
                                :baseline="baselineData"
                                @update:model-value="handleInputUpdate"
                            />
                        </div>
                        <ChartCard title="Input Overview" subtitle="Current selections (1–5)" :flush="true" :height="inputChartHeight">
                            <InputBarChart
                                :labels="inputChartLabels"
                                :data="inputChartValues"
                                :max="5"
                                :height="inputChartHeight"
                            />
                        </ChartCard>
                    </div>
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
