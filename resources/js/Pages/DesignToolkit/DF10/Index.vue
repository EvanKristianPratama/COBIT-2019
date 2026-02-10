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
    labels: Array,
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

// Calculate scores using MAP matrix (inputs are percentages -> normalize to 0-1)
// Matches DesignFactorCalculator::calculateScores (rounded to 2 decimals).
const scores = computed(() => {
    const normalizedInputs = inputs.value.map(value => (value || 0) / 100);
    return props.map.map((row) => {
        let score = 0;
        for (let j = 0; j < props.inputCount; j++) {
            score += row[j] * (normalizedInputs[j] || 0);
        }
        return Math.round(score * 100) / 100;
    });
});

// Calculate Relative Importance
const relativeImportance = computed(() => {
    return scores.value.map((score, i) => {
        const baseline = props.baselineScores[i] ?? 0;
        if (baseline === 0) return 0;
        const percentage = (score / baseline) * 100;
        return mround(percentage, 5) - 100;
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
const barChartHeight = computed(() => {
    const rows = props.objectiveLabels?.length || 40;
    return `${Math.max(900, rows * 24)}px`;
});

</script>

<template>
    <AuthenticatedLayout title="DF10 - Technology Adoption Strategy">
        <template #header>
            <PageHeader 
                title="Design Factor 10"
                subtitle="Technology Adoption Strategy"
                :breadcrumbs="[
                    { label: 'Dashboard', href: routes.dashboard },
                    { label: 'Design Toolkit', href: routes.index },
                    { label: 'DF10 - Technology Adoption Strategy' }
                ]"
            />
        </template>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <DesignFactorPagination :current-df="dfNumber" :routes="routes" position="top" />
            <form @submit.prevent="submit">
                <!-- Input Section -->
                <section class="mb-8">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">
                        Technology Adoption Strategy Distribution
                    </h2>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">
                        Enter the percentage for each adoption strategy.
                        <strong class="text-amber-600">Requirement:</strong> Total must equal 100%.
                    </p>
                    
                    <PercentageInput
                        :labels="labels"
                        :model-value="inputs"
                        :baseline="baselineInputs"
                        @update:model-value="handleInputUpdate"
                    />
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
