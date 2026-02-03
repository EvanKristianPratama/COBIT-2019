<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import DesignFactorPagination from '../Components/DesignFactorPagination.vue';
import ChartCard from '../Components/ChartCard.vue';
import BarChart from '../Components/BarChart.vue';
import { 
    DocumentCheckIcon, 
    ArrowPathIcon, 
    BarsArrowDownIcon,
    TableCellsIcon,
    ChevronUpIcon,
    ChevronDownIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    auth: Object,
    assessment: Object,
    dfNumber: Number,
    savedWeights3: Array,
    step2Weights: Array,
    step2Totals: Object,
    matrix: Object,
    objectiveLabels: Array,
    routes: Object,
});

// Weights for DF5-DF10
const weights3 = ref([...props.savedWeights3]);

// Form for saving
const form = useForm({
    weights3: weights3.value,
    refinedScopeScores: [],
});

// Helper: Round to nearest 5
const roundTo5 = (val) => Math.round(val / 5) * 5;

// Calculation Logic
const calculatedData = computed(() => {
    const combinedTotals = [];
    const labels = props.objectiveLabels;

    labels.forEach((code, index) => {
        const objectiveIndex = index + 1;
        const row = props.matrix[objectiveIndex];
        const step2Total = parseFloat(props.step2Totals[objectiveIndex] || 0);

        // Weighted sum for Step 3: Σ (RelImp[DFn] * Weight[DFn]) where n = 5..10
        let tot3 = 0;
        [5, 6, 7, 8, 9, 10].forEach((n, i) => {
            tot3 += (row[`df${n}`] || 0) * weights3.value[i];
        });
        
        // Combined = Step 2 + Step 3
        const combined = step2Total + tot3;
        combinedTotals.push(combined);
    });

    const maxT = Math.max(...combinedTotals.map(Math.abs), 1);
    
    const refinedScopeScores = combinedTotals.map(t => {
        let pct = maxT ? Math.trunc((t / maxT) * 100) : 0;
        return t >= 0 ? roundTo5(pct) : -roundTo5(Math.abs(pct));
    });

    return { refinedScopeScores };
});

// Sorting
const sortDir = ref(null);
const sortedIndices = computed(() => {
    const indices = props.objectiveLabels.map((_, i) => i);
    if (!sortDir.value) return indices;

    return indices.sort((a, b) => {
        const valA = calculatedData.value.refinedScopeScores[a];
        const valB = calculatedData.value.refinedScopeScores[b];
        return sortDir.value === 'asc' ? valA - valB : valB - valA;
    });
});

const toggleSort = () => {
    if (sortDir.value === 'desc') sortDir.value = 'asc';
    else if (sortDir.value === 'asc') sortDir.value = null;
    else sortDir.value = 'desc';
};

const save = () => {
    form.weights3 = weights3.value;
    form.refinedScopeScores = calculatedData.value.refinedScopeScores;
    form.post(props.routes.store, {
        preserveScroll: true
    });
};

// Visual Helpers
const getValClass = (val) => {
    if (val > 0) return 'text-emerald-600 dark:text-emerald-400';
    if (val < 0) return 'text-rose-600 dark:text-rose-400';
    return 'text-slate-400';
};

const getBarStyles = (score) => {
    const isPos = score >= 0;
    const width = Math.min(Math.abs(score) / 2, 50);
    return {
        left: isPos ? '50%' : 'auto',
        right: !isPos ? '50%' : 'auto',
        width: `${width}%`,
        backgroundColor: isPos ? '#10b981' : '#f43f5e'
    };
};
</script>

<template>
    <AuthenticatedLayout title="Step 3: Refined Scope">
        <template #header>
            <PageHeader 
                title="Step 3: Refined Scope"
                subtitle="Refine the Scope of the Governance System"
                :breadcrumbs="[
                    { label: 'Dashboard', href: routes.dashboard },
                    { label: 'Design Toolkit', href: routes.index },
                    { label: 'Step 3 Summary' }
                ]"
            >
                <template #actions>
                    <div class="flex items-center gap-4">
                        <span v-if="form.recentlySuccessful" class="text-sm text-emerald-600 animate-fade-out">Data Saved Correcty</span>
                        <button 
                            @click="save" 
                            :disabled="form.processing"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 rounded-lg hover:bg-slate-800 dark:hover:bg-slate-200 transition-colors shadow-sm disabled:opacity-50"
                        >
                            <ArrowPathIcon v-if="form.processing" class="w-4 h-4 animate-spin" />
                            <DocumentCheckIcon v-else class="w-4 h-4" />
                            {{ form.processing ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </div>
                </template>
            </PageHeader>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
            <DesignFactorPagination :current-df="dfNumber" :routes="routes" position="top" />
            
            <!-- Matrix Table Card -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <TableCellsIcon class="w-5 h-5 text-slate-400" />
                        <h3 class="font-semibold text-slate-900 dark:text-white">Relative Importance Matrix (DF5–DF10)</h3>
                    </div>
                    <button 
                        @click="toggleSort"
                        class="flex items-center gap-2 px-3 py-1.5 text-xs font-medium bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-md hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 transition-colors"
                    >
                        <BarsArrowDownIcon class="w-4 h-4" />
                        Sort by Score
                        <ChevronUpIcon v-if="sortDir === 'asc'" class="w-3 h-3" />
                        <ChevronDownIcon v-if="sortDir === 'desc'" class="w-3 h-3" />
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-6 py-3 border-b dark:border-slate-700 w-24">GAMO</th>
                                <th class="px-4 py-3 border-b dark:border-slate-700 text-center" title="Threat Landscape">DF5</th>
                                <th class="px-4 py-3 border-b dark:border-slate-700 text-center" title="Compliance Requirements">DF6</th>
                                <th class="px-4 py-3 border-b dark:border-slate-700 text-center" title="Role of IT">DF7</th>
                                <th class="px-4 py-3 border-b dark:border-slate-700 text-center" title="Sourcing Model">DF8</th>
                                <th class="px-4 py-3 border-b dark:border-slate-700 text-center" title="IT Implementation">DF9</th>
                                <th class="px-4 py-3 border-b dark:border-slate-700 text-center" title="Technology Adoption">DF10</th>
                                <th class="px-6 py-3 border-b dark:border-slate-700 text-center bg-teal-50/50 dark:bg-teal-900/10 text-teal-600 dark:text-teal-400 w-24">Combined Total</th>
                                <th class="px-6 py-3 border-b dark:border-slate-700 text-center w-48">Refined Scope Score</th>
                            </tr>
                            <tr class="bg-amber-50/50 dark:bg-amber-900/10">
                                <td class="px-6 py-4 font-bold text-amber-700 dark:text-amber-400">Weight</td>
                                <td v-for="i in 6" :key="i" class="px-4 py-4 text-center">
                                    <input 
                                        type="number" 
                                        v-model.number="weights3[i-1]"
                                        step="0.1"
                                        class="w-14 text-center bg-white dark:bg-slate-800 border border-amber-200 dark:border-amber-900/50 rounded-md focus:ring-amber-500 focus:border-amber-500 font-bold text-amber-900 dark:text-amber-200 text-xs"
                                    />
                                </td>
                                <td class="px-6 py-4 text-center text-slate-300">—</td>
                                <td class="px-6 py-4"></td>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            <tr v-for="idx in sortedIndices" :key="idx" class="hover:bg-slate-50 dark:hover:bg-slate-900/30 transition-colors">
                                <td class="px-6 py-3 font-semibold text-teal-600 dark:text-teal-400 uppercase">
                                    {{ objectiveLabels[idx] }}
                                </td>
                                <td v-for="n in [5,6,7,8,9,10]" :key="n" class="px-4 py-3 text-center">
                                    <span :class="getValClass(matrix[idx + 1][`df${n}`])">
                                        {{ matrix[idx + 1][`df${n}`] !== 0 ? matrix[idx + 1][`df${n}`] : '–' }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-center font-bold bg-teal-50/20 dark:bg-teal-900/5 text-slate-900 dark:text-white">
                                    {{ (parseFloat(step2Totals[idx + 1] || 0) + [5,6,7,8,9,10].reduce((acc, n, i) => acc + (matrix[idx+1][`df${n}`] * weights3[i]), 0)).toFixed(0) }}
                                </td>
                                <td class="px-6 py-3">
                                    <div class="relative h-5 w-32 bg-slate-100 dark:bg-slate-700 rounded overflow-hidden mx-auto border border-slate-200 dark:border-slate-600">
                                        <div class="absolute left-1/2 top-0 bottom-0 w-px bg-slate-300 dark:bg-slate-500 z-10"></div>
                                        <div 
                                            class="absolute top-0.5 bottom-0.5 transition-all duration-300 rounded-sm"
                                            :style="getBarStyles(calculatedData.refinedScopeScores[idx])"
                                        ></div>
                                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                            <span class="text-[10px] font-bold text-slate-900 dark:text-slate-100">
                                                {{ calculatedData.refinedScopeScores[idx] }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Visualization Section -->
            <div class="grid grid-cols-1 gap-8">
                <ChartCard title="Refined Scope Distribution" subtitle="Combined results from all design factors (DF1-DF10)">
                    <div class="h-[800px]">
                        <BarChart 
                            :labels="objectiveLabels"
                            :data="calculatedData.refinedScopeScores"
                            :horizontal="true"
                            :colors="calculatedData.refinedScopeScores.map(v => v >= 0 ? '#10b981' : '#f43f5e')"
                        />
                    </div>
                </ChartCard>
            </div>

            <DesignFactorPagination :current-df="dfNumber" :routes="routes" position="bottom" />
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-fade-out {
    animation: fadeOut 3s forwards;
}

@keyframes fadeOut {
    0% { opacity: 1; }
    70% { opacity: 1; }
    100% { opacity: 0; }
}
</style>
