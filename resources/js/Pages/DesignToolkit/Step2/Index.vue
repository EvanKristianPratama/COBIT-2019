<script setup>
import { ref, computed, watch } from 'vue';
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
    savedWeights: Array,
    matrix: Object,
    objectiveLabels: Array,
    routes: Object,
});

// Weights for DF1-DF4
const weights = ref([...props.savedWeights]);

// Form for saving
const form = useForm({
    weights: weights.value,
    totals: {},
    initialScopeScores: [],
});

// Helper: Round to nearest 5
const roundTo5 = (val) => Math.round(val / 5) * 5;

// Calculation Logic
const calculatedData = computed(() => {
    const totals = {};
    const rawScores = [];
    const labels = props.objectiveLabels;

    labels.forEach((code, index) => {
        const objectiveIndex = index + 1;
        const row = props.matrix[objectiveIndex];
        let total = 0;
        
        // Weighted sum: Σ (RelImp[DFn] * Weight[DFn])
        [1, 2, 3, 4].forEach((n, i) => {
            total += (row[`df${n}`] || 0) * weights.value[i];
        });
        
        totals[objectiveIndex] = total;
        rawScores.push(total);
    });

    const maxT = Math.max(...rawScores.map(Math.abs), 1);
    
    const initialScopeScores = rawScores.map(t => {
        let pct = maxT ? Math.trunc((t / maxT) * 100) : 0;
        return t >= 0 ? roundTo5(pct) : -roundTo5(Math.abs(pct));
    });

    return { totals, initialScopeScores };
});

// Sorting
const sortDir = ref(null); // null, 'asc', 'desc'
const sortedIndices = computed(() => {
    const indices = props.objectiveLabels.map((_, i) => i);
    if (!sortDir.value) return indices;

    return indices.sort((a, b) => {
        const valA = calculatedData.value.initialScopeScores[a];
        const valB = calculatedData.value.initialScopeScores[b];
        return sortDir.value === 'asc' ? valA - valB : valB - valA;
    });
});

const toggleSort = () => {
    if (sortDir.value === 'desc') sortDir.value = 'asc';
    else if (sortDir.value === 'asc') sortDir.value = null;
    else sortDir.value = 'desc';
};

// Auto-save logic (optional, but let's implement manual save first for stability)
const save = () => {
    form.weights = weights.value;
    form.totals = calculatedData.value.totals;
    form.initialScopeScores = calculatedData.value.initialScopeScores;
    form.post(props.routes.store, {
        preserveScroll: true,
        onSuccess: () => {
            // Optional success notification
        }
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
    const width = Math.min(Math.abs(score) / 2, 50); // Scale to 50% max each side
    return {
        left: isPos ? '50%' : 'auto',
        right: !isPos ? '50%' : 'auto',
        width: `${width}%`,
        backgroundColor: isPos ? '#10b981' : '#f43f5e'
    };
};

</script>

<template>
    <AuthenticatedLayout title="Step 2: Determine the initial scope of the Governance System">
        <template #header>
            <PageHeader 
                title="Step 2: Determine the initial scope of the Governance System"
                subtitle="Determine the Initial Scope of the Governance System"
                :breadcrumbs="[
                    { label: 'Dashboard', href: routes.dashboard },
                    { label: 'Design Toolkit', href: routes.index },
                    { label: 'Step 2 Summary' }
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
                <div class="px-6 py-4 border-b border-slate-700 bg-slate-900 text-white flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <TableCellsIcon class="w-5 h-5 text-slate-300" />
                        <h3 class="font-semibold text-white">Relative Importance Matrix</h3>
                    </div>
                    <button 
                        @click="toggleSort"
                        class="flex items-center gap-2 px-3 py-1.5 text-xs font-medium bg-slate-800 border border-slate-700 rounded-md hover:bg-slate-700 text-slate-200 transition-colors"
                    >
                        <BarsArrowDownIcon class="w-4 h-4" />
                        Sort by Score
                        <ChevronUpIcon v-if="sortDir === 'asc'" class="w-3 h-3" />
                        <ChevronDownIcon v-if="sortDir === 'desc'" class="w-3 h-3" />
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-[11px] text-left">
                        <thead class="text-[10px] uppercase tracking-wider">
                            <tr class="bg-slate-900 text-white">
                                <th class="px-4 py-2 border-b border-slate-700 w-24">Design Factors</th>
                                <th class="px-3 py-2 border-b border-slate-700 text-center" title="Enterprise Strategy">Enterprise<br>Strategy</th>
                                <th class="px-3 py-2 border-b border-slate-700 text-center" title="Enterprise Goals">Enterprise<br>Goals</th>
                                <th class="px-3 py-2 border-b border-slate-700 text-center" title="Risk Profile">Risk<br>Profile</th>
                                <th class="px-3 py-2 border-b border-slate-700 text-center" title="IT-Related Issues">IT‑Related<br>Issues</th>
                                <th class="px-4 py-2 border-b border-slate-700 text-center bg-slate-600 w-24">Total</th>
                                <th class="px-4 py-2 border-b border-slate-700 text-center bg-slate-600 w-48">Initial Scope</th>
                            </tr>
                            <tr class="bg-emerald-600 text-white">
                                <td class="px-4 py-2 font-semibold">Weight</td>
                                <td v-for="i in 4" :key="i" class="px-3 py-2 text-center">
                                    <input 
                                        type="number" 
                                        v-model.number="weights[i-1]"
                                        step="0.1"
                                        class="w-12 text-center bg-white/90 text-emerald-900 rounded-md font-bold text-[10px]"
                                    />
                                </td>
                                <td class="px-4 py-2 text-center text-emerald-100">—</td>
                                <td class="px-4 py-2"></td>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            <tr v-for="idx in sortedIndices" :key="idx" class="hover:bg-slate-50 dark:hover:bg-slate-900/30 transition-colors">
                                <td class="px-4 py-2 font-semibold text-slate-900 dark:text-slate-100 uppercase">
                                    {{ objectiveLabels[idx] }}
                                </td>
                                <td v-for="n in 4" :key="n" class="px-3 py-2 text-center">
                                    <span :class="getValClass(matrix[idx + 1][`df${n}`])">
                                        {{ matrix[idx + 1][`df${n}`] !== 0 ? matrix[idx + 1][`df${n}`] : '–' }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-center font-bold bg-slate-50 dark:bg-slate-900/5 text-slate-900 dark:text-white">
                                    {{ calculatedData.totals[idx + 1].toFixed(0) }}
                                </td>
                                <td class="px-4 py-2">
                                    <div class="relative h-4 w-28 bg-slate-100 dark:bg-slate-700 rounded overflow-hidden mx-auto border border-slate-200 dark:border-slate-600">
                                        <div class="absolute left-1/2 top-0 bottom-0 w-px bg-slate-300 dark:bg-slate-500 z-10"></div>
                                        <div 
                                            class="absolute top-0.5 bottom-0.5 transition-all duration-300 rounded-sm"
                                            :style="getBarStyles(calculatedData.initialScopeScores[idx])"
                                        ></div>
                                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                            <span class="text-[10px] font-bold text-slate-900 dark:text-slate-100">
                                                {{ calculatedData.initialScopeScores[idx] }}
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
            <div class="grid grid-cols-1 gap-6">
                <ChartCard title="Initial Scope Distribution" subtitle="Normalized scores across 40 objectives" :flush="true" height="900px">
                    <div class="h-full w-full">
                        <BarChart 
                            :labels="objectiveLabels"
                            :data="calculatedData.initialScopeScores"
                            :horizontal="true"
                            height="900px"
                            :colors="calculatedData.initialScopeScores.map(v => v >= 0 ? '#10b981' : '#f43f5e')"
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
