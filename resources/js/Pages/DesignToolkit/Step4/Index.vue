<script setup>
import { ref, computed, reactive, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import DesignFactorPagination from '../Components/DesignFactorPagination.vue';
import ChartCard from '../Components/ChartCard.vue';
import RadarChart from '../Components/RadarChart.vue';
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
    weights2: Array,
    weights3: Array,
    step2Totals: Object,
    combinedTotals: Object,
    allRelImps: Object,
    initialScopes: Object,
    refinedScopes: Object,
    adjustments: Object,
    reasonAdjust: Object,
    reasonTarget: Object,
    agreedLevels: Object,
    selectedObjectives: Object,
    objectiveLabels: Array,
    routes: Object,
});

const weightsStep2 = ref([...(props.weights2 || [1, 1, 1, 1])]);
const weightsStep3 = ref([...(props.weights3 || [1, 1, 1, 1, 1, 1])]);

const toNumberArray = (source, fallback = 0) => {
    return props.objectiveLabels.map((_, i) => {
        const key = i + 1;
        const raw = (source && (source[key] ?? source[i])) ?? fallback;
        const val = parseFloat(raw);
        return Number.isFinite(val) ? val : fallback;
    });
};

const toStringArray = (source) => {
    return props.objectiveLabels.map((_, i) => {
        const key = i + 1;
        const raw = (source && (source[key] ?? source[i])) ?? '';
        return raw ?? '';
    });
};

const toBooleanArray = (source, fallback = false) => {
    return props.objectiveLabels.map((_, i) => {
        const key = i + 1;
        const raw = source && (source[key] ?? source[i]);
        if (raw === undefined || raw === null) return fallback;
        return Boolean(Number(raw));
    });
};

const adjustments = ref(toNumberArray(props.adjustments, 0));
const reasonsAdjust = ref(toStringArray(props.reasonAdjust));
const reasonsTarget = ref(toStringArray(props.reasonTarget));

// Helper: Round to nearest 5
const roundTo5 = (val) => Math.round(val / 5) * 5;

const step2TotalsArr = computed(() => toNumberArray(props.step2Totals, 0));
const combinedTotalsArr = computed(() => toNumberArray(props.combinedTotals, 0));

const initialScopeScores = computed(() => {
    const totals = step2TotalsArr.value;
    const maxT = Math.max(...totals.map(Math.abs), 1);
    return totals.map(t => {
        let pct = maxT ? Math.trunc((t / maxT) * 100) : 0;
        return t >= 0 ? roundTo5(pct) : -roundTo5(Math.abs(pct));
    });
});

const refinedScopeScores = computed(() => {
    const totals = combinedTotalsArr.value;
    const maxT = Math.max(...totals.map(Math.abs), 1);
    return totals.map(t => {
        let pct = maxT ? Math.trunc((t / maxT) * 100) : 0;
        return t >= 0 ? roundTo5(pct) : -roundTo5(Math.abs(pct));
    });
});

const concludedScopeScores = computed(() => {
    return refinedScopeScores.value.map((score, i) => roundTo5((score || 0) + (adjustments.value[i] || 0)));
});

const suggestedLevels = computed(() => {
    return concludedScopeScores.value.map((pct) => {
        if (pct >= 75) return 4;
        if (pct >= 50) return 3;
        if (pct >= 25) return 2;
        return 1;
    });
});

const agreedLevels = ref([]);
const agreedTouched = ref([]);
const selected = ref(toBooleanArray(props.selectedObjectives, false));

const clampLevel = (val, fallback) => {
    const num = parseInt(val, 10);
    if (!Number.isFinite(num)) return fallback;
    if (num < 1) return 1;
    if (num > 5) return 5;
    return num;
};

const setAgreedLevel = (idx, value) => {
    agreedLevels.value[idx] = clampLevel(value, suggestedLevels.value[idx] ?? 1);
    agreedTouched.value[idx] = true;
};

watch(
    suggestedLevels,
    (next) => {
        if (!agreedLevels.value.length) {
            const fromProps = toNumberArray(props.agreedLevels, null);
            agreedLevels.value = next.map((val, i) => clampLevel(fromProps[i], val));
            agreedTouched.value = next.map((_, i) => Number.isFinite(parseInt(fromProps[i], 10)));
            return;
        }

        next.forEach((val, i) => {
            if (!agreedTouched.value[i]) {
                agreedLevels.value[i] = val;
            }
        });
    },
    { immediate: true }
);

const maxCapabilityLevels = [
    4, 5, 4, 4, 4,
    5, 4, 5, 4, 5,
    5, 4, 5, 4, 5,
    5, 5, 5, 5, 5,
    4, 4, 5, 5, 4,
    5, 5, 5, 5, 4,
    5, 5, 5, 5, 4,
    5, 5, 5, 5, 4,
];

// Sorting states
const sortState = reactive({
    combined: { column: null, dir: null },
    step4: null,
});

const makeSortedIndices = (scores, dir) => {
    const indices = props.objectiveLabels.map((_, i) => i);
    if (!dir) return indices;

    return indices.sort((a, b) => {
        const valA = scores[a];
        const valB = scores[b];
        return dir === 'asc' ? valA - valB : valB - valA;
    });
};

const sortedCombinedIndices = computed(() => {
    const { column, dir } = sortState.combined;
    if (!column || !dir) return props.objectiveLabels.map((_, i) => i);
    const scores = column === 'initial' ? initialScopeScores.value : refinedScopeScores.value;
    return makeSortedIndices(scores, dir);
});

const sortedStep4Indices = computed(() => makeSortedIndices(concludedScopeScores.value, sortState.step4));

const toggleCombinedSort = (column) => {
    if (sortState.combined.column !== column) {
        sortState.combined.column = column;
        sortState.combined.dir = 'desc';
        return;
    }

    if (sortState.combined.dir === 'desc') sortState.combined.dir = 'asc';
    else if (sortState.combined.dir === 'asc') {
        sortState.combined.dir = null;
        sortState.combined.column = null;
    } else {
        sortState.combined.dir = 'desc';
    }
};

const toggleStep4Sort = () => {
    if (sortState.step4 === 'desc') sortState.step4 = 'asc';
    else if (sortState.step4 === 'asc') sortState.step4 = null;
    else sortState.step4 = 'desc';
};

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

const getLevelStyles = (level, color) => {
    const width = Math.min(Math.max(level, 0), 5) * 20;
    return { width: `${width}%`, backgroundColor: color };
};

const getRelImp = (objectiveIndex, dfIndex) => {
    const row = props.allRelImps?.[objectiveIndex + 1] ?? props.allRelImps?.[objectiveIndex] ?? [];
    return parseFloat(row[dfIndex] ?? 0) || 0;
};

const form = useForm({
    weights2: weightsStep2.value,
    weights3: weightsStep3.value,
    adjustment: {},
    reason_adjust: {},
    reason_target: {},
    selected: {},
    agreed_level: {},
});

const save = () => {
    const adjustmentMap = {};
    const reasonAdjustMap = {};
    const reasonTargetMap = {};
    const selectedMap = {};
    const agreedMap = {};
    props.objectiveLabels.forEach((_, i) => {
        const key = i + 1;
        adjustmentMap[key] = adjustments.value[i] ?? 0;
        reasonAdjustMap[key] = reasonsAdjust.value[i] ?? '';
        reasonTargetMap[key] = reasonsTarget.value[i] ?? '';
        selectedMap[key] = selected.value[i] ? 1 : 0;
        agreedMap[key] = agreedLevels.value[i] ?? suggestedLevels.value[i] ?? 1;
    });

    form.weights2 = weightsStep2.value;
    form.weights3 = weightsStep3.value;
    form.adjustment = adjustmentMap;
    form.reason_adjust = reasonAdjustMap;
    form.reason_target = reasonTargetMap;
    form.selected = selectedMap;
    form.agreed_level = agreedMap;
    form.post(props.routes.store, {
        preserveScroll: true,
    });
};
</script>

<template>
    <AuthenticatedLayout title="Step 4: Concluded Scope">
        <template #header>
            <PageHeader 
                title="Step 4: Concluded Scope"
                subtitle="Combine Step 2 & Step 3 results, then apply adjustments"
                :breadcrumbs="[
                    { label: 'Dashboard', href: routes.dashboard },
                    { label: 'Design Toolkit', href: routes.index },
                    { label: 'Step 4 Summary' }
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

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <DesignFactorPagination :current-df="dfNumber" :routes="routes" position="top" />

            <!-- Combined DF1-DF10 Table -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-700 bg-slate-900 text-white flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <TableCellsIcon class="w-5 h-5 text-slate-300" />
                        <h3 class="font-semibold text-white">Design Factors (DF1–DF10)</h3>
                    </div>
                    <div class="flex items-center gap-2">
                        <button 
                            @click="toggleCombinedSort('initial')"
                            class="flex items-center gap-2 px-3 py-1.5 text-xs font-medium bg-slate-800 border border-slate-700 rounded-md hover:bg-slate-700 text-slate-200 transition-colors"
                        >
                            <BarsArrowDownIcon class="w-4 h-4" />
                            Sort Initial
                            <ChevronUpIcon v-if="sortState.combined.column === 'initial' && sortState.combined.dir === 'asc'" class="w-3 h-3" />
                            <ChevronDownIcon v-if="sortState.combined.column === 'initial' && sortState.combined.dir === 'desc'" class="w-3 h-3" />
                        </button>
                        <button 
                            @click="toggleCombinedSort('refined')"
                            class="flex items-center gap-2 px-3 py-1.5 text-xs font-medium bg-slate-800 border border-slate-700 rounded-md hover:bg-slate-700 text-slate-200 transition-colors"
                        >
                            <BarsArrowDownIcon class="w-4 h-4" />
                            Sort Refined
                            <ChevronUpIcon v-if="sortState.combined.column === 'refined' && sortState.combined.dir === 'asc'" class="w-3 h-3" />
                            <ChevronDownIcon v-if="sortState.combined.column === 'refined' && sortState.combined.dir === 'desc'" class="w-3 h-3" />
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-[11px] text-left">
                        <thead class="text-[10px]">
                            <tr class="bg-slate-900 text-white uppercase tracking-wide">
                                <th rowspan="2" class="px-3 py-2 border-b border-slate-700 w-48">Design Factors</th>
                                <th colspan="5" class="px-3 py-2 border-b border-slate-700 text-center">Step 2: Determine the Initial Scope</th>
                                <th colspan="7" class="px-3 py-2 border-b border-slate-700 text-center">Step 3: Refine the Scope</th>
                            </tr>
                            <tr class="bg-slate-800 text-white">
                                <th class="px-2 py-2 text-center">Enterprise<br>Strategy</th>
                                <th class="px-2 py-2 text-center">Enterprise<br>Goals</th>
                                <th class="px-2 py-2 text-center">Risk<br>Profile</th>
                                <th class="px-2 py-2 text-center">IT‑Related<br>Issues</th>
                                <th class="px-2 py-2 text-center bg-slate-600">Initial Scope</th>
                                <th class="px-2 py-2 text-center border-l border-slate-500">Threat<br>Landscape</th>
                                <th class="px-2 py-2 text-center">Compliance<br>Req's</th>
                                <th class="px-2 py-2 text-center">Role of<br>IT</th>
                                <th class="px-2 py-2 text-center">Sourcing<br>Model</th>
                                <th class="px-2 py-2 text-center">IT<br>Implementation</th>
                                <th class="px-2 py-2 text-center">Technology<br>Adoption</th>
                                <th class="px-2 py-2 text-center bg-slate-600">Refined Scope</th>
                            </tr>
                            <tr class="bg-emerald-600 text-white">
                                <th class="px-3 py-2 text-left font-semibold">Weight</th>
                                <th class="px-2 py-2 text-center">
                                    <input 
                                        type="number" 
                                        v-model.number="weightsStep2[0]"
                                        step="0.1"
                                        class="w-12 text-center bg-white/90 text-emerald-900 rounded-md font-bold text-[10px]"
                                    />
                                </th>
                                <th class="px-2 py-2 text-center">
                                    <input 
                                        type="number" 
                                        v-model.number="weightsStep2[1]"
                                        step="0.1"
                                        class="w-12 text-center bg-white/90 text-emerald-900 rounded-md font-bold text-[10px]"
                                    />
                                </th>
                                <th class="px-2 py-2 text-center">
                                    <input 
                                        type="number" 
                                        v-model.number="weightsStep2[2]"
                                        step="0.1"
                                        class="w-12 text-center bg-white/90 text-emerald-900 rounded-md font-bold text-[10px]"
                                    />
                                </th>
                                <th class="px-2 py-2 text-center">
                                    <input 
                                        type="number" 
                                        v-model.number="weightsStep2[3]"
                                        step="0.1"
                                        class="w-12 text-center bg-white/90 text-emerald-900 rounded-md font-bold text-[10px]"
                                    />
                                </th>
                                <th class="px-2 py-2 text-center text-emerald-100">—</th>
                                <th class="px-2 py-2 text-center border-l border-emerald-300/60">
                                    <input 
                                        type="number" 
                                        v-model.number="weightsStep3[0]"
                                        step="0.1"
                                        class="w-12 text-center bg-white/90 text-emerald-900 rounded-md font-bold text-[10px]"
                                    />
                                </th>
                                <th class="px-2 py-2 text-center">
                                    <input 
                                        type="number" 
                                        v-model.number="weightsStep3[1]"
                                        step="0.1"
                                        class="w-12 text-center bg-white/90 text-emerald-900 rounded-md font-bold text-[10px]"
                                    />
                                </th>
                                <th class="px-2 py-2 text-center">
                                    <input 
                                        type="number" 
                                        v-model.number="weightsStep3[2]"
                                        step="0.1"
                                        class="w-12 text-center bg-white/90 text-emerald-900 rounded-md font-bold text-[10px]"
                                    />
                                </th>
                                <th class="px-2 py-2 text-center">
                                    <input 
                                        type="number" 
                                        v-model.number="weightsStep3[3]"
                                        step="0.1"
                                        class="w-12 text-center bg-white/90 text-emerald-900 rounded-md font-bold text-[10px]"
                                    />
                                </th>
                                <th class="px-2 py-2 text-center">
                                    <input 
                                        type="number" 
                                        v-model.number="weightsStep3[4]"
                                        step="0.1"
                                        class="w-12 text-center bg-white/90 text-emerald-900 rounded-md font-bold text-[10px]"
                                    />
                                </th>
                                <th class="px-2 py-2 text-center">
                                    <input 
                                        type="number" 
                                        v-model.number="weightsStep3[5]"
                                        step="0.1"
                                        class="w-12 text-center bg-white/90 text-emerald-900 rounded-md font-bold text-[10px]"
                                    />
                                </th>
                                <th class="px-2 py-2 text-center text-emerald-100">—</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            <tr v-for="idx in sortedCombinedIndices" :key="idx" class="hover:bg-slate-50 dark:hover:bg-slate-900/30 transition-colors">
                                <td class="px-3 py-2 font-semibold text-slate-900 dark:text-slate-100 uppercase">
                                    {{ objectiveLabels[idx] }}
                                </td>
                                <td v-for="n in [0,1,2,3]" :key="n" class="px-2 py-2 text-center">
                                    <span :class="getValClass(getRelImp(idx, n))">
                                        {{ getRelImp(idx, n) !== 0 ? getRelImp(idx, n) : '–' }}
                                    </span>
                                </td>
                                <td class="px-2 py-2">
                                    <div class="relative h-4 w-28 bg-slate-100 dark:bg-slate-700 rounded overflow-hidden mx-auto border border-slate-200 dark:border-slate-600">
                                        <div class="absolute left-1/2 top-0 bottom-0 w-px bg-slate-300 dark:bg-slate-500 z-10"></div>
                                        <div 
                                            class="absolute top-0.5 bottom-0.5 transition-all duration-300 rounded-sm"
                                            :style="getBarStyles(initialScopeScores[idx])"
                                        ></div>
                                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                            <span class="text-[10px] font-bold text-slate-900 dark:text-slate-100">
                                                {{ initialScopeScores[idx] }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td 
                                    v-for="n in [4,5,6,7,8,9]" 
                                    :key="n" 
                                    :class="[
                                        'px-2 py-2 text-center',
                                        n === 4 ? 'border-l border-slate-200 dark:border-slate-600' : ''
                                    ]"
                                >
                                    <span :class="getValClass(getRelImp(idx, n))">
                                        {{ getRelImp(idx, n) !== 0 ? getRelImp(idx, n) : '–' }}
                                    </span>
                                </td>
                                <td class="px-2 py-2">
                                    <div class="relative h-4 w-28 bg-slate-100 dark:bg-slate-700 rounded overflow-hidden mx-auto border border-slate-200 dark:border-slate-600">
                                        <div class="absolute left-1/2 top-0 bottom-0 w-px bg-slate-300 dark:bg-slate-500 z-10"></div>
                                        <div 
                                            class="absolute top-0.5 bottom-0.5 transition-all duration-300 rounded-sm"
                                            :style="getBarStyles(refinedScopeScores[idx])"
                                        ></div>
                                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                            <span class="text-[10px] font-bold text-slate-900 dark:text-slate-100">
                                                {{ refinedScopeScores[idx] }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Step 4 Table -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-700 bg-slate-900 text-white flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <TableCellsIcon class="w-5 h-5 text-slate-300" />
                        <h3 class="font-semibold text-white">Step 4: Concluded Scope</h3>
                    </div>
                    <button 
                        @click="toggleStep4Sort"
                        class="flex items-center gap-2 px-3 py-1.5 text-xs font-medium bg-slate-800 border border-slate-700 rounded-md hover:bg-slate-700 text-slate-200 transition-colors"
                    >
                        <BarsArrowDownIcon class="w-4 h-4" />
                        Sort by Score
                        <ChevronUpIcon v-if="sortState.step4 === 'asc'" class="w-3 h-3" />
                        <ChevronDownIcon v-if="sortState.step4 === 'desc'" class="w-3 h-3" />
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-[11px] text-left compact-table">
                        <thead class="text-[10px] uppercase tracking-wider">
                            <tr class="bg-slate-900 text-white">
                                <th class="px-3 py-2 border-b border-slate-700 w-12 text-center">Save</th>
                                <th class="px-4 py-2 border-b border-slate-700 w-20">GAMO</th>
                                <th class="px-3 py-2 border-b border-slate-700 text-center w-24">Adjustment</th>
                                <th class="px-3 py-2 border-b border-slate-700 w-56">Reason (Adjustment)</th>
                                <th class="px-4 py-2 border-b border-slate-700 text-center bg-slate-600 w-32">Concluded Priority</th>
                                <th class="px-4 py-2 border-b border-slate-700 text-center bg-slate-600 w-24">Suggested Level</th>
                                <th class="px-4 py-2 border-b border-slate-700 text-center bg-slate-600 w-24">Agreed Level</th>
                                <th class="px-3 py-2 border-b border-slate-700 w-56">Reason (Target)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            <tr v-for="idx in sortedStep4Indices" :key="idx" class="hover:bg-slate-50 dark:hover:bg-slate-900/30 transition-colors">
                                <td class="px-3 py-2 text-center">
                                    <input
                                        type="checkbox"
                                        v-model="selected[idx]"
                                        class="h-3 w-3 rounded border-slate-300 text-slate-700 focus:ring-2 focus:ring-slate-400 dark:border-slate-600 dark:bg-slate-900 dark:checked:bg-slate-200 dark:checked:border-slate-200"
                                    />
                                </td>
                                <td class="px-4 py-2 font-semibold text-slate-900 dark:text-slate-100 uppercase">
                                    {{ objectiveLabels[idx] }}
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <input
                                        type="number"
                                        v-model.number="adjustments[idx]"
                                        min="-100"
                                        max="100"
                                        step="1"
                                        class="w-16 text-center bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-md focus:ring-indigo-500 focus:border-indigo-500 text-[10px]"
                                    />
                                </td>
                                <td class="px-3 py-2">
                                    <input
                                        type="text"
                                        v-model="reasonsAdjust[idx]"
                                        class="w-full px-2 py-1.5 rounded-md border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900/40 text-[10px]"
                                        placeholder="Reason..."
                                    />
                                </td>
                                <td class="px-4 py-2">
                                    <div class="relative h-4 w-28 bg-slate-100 dark:bg-slate-700 rounded overflow-hidden mx-auto border border-slate-200 dark:border-slate-600">
                                        <div class="absolute left-1/2 top-0 bottom-0 w-px bg-slate-300 dark:bg-slate-500 z-10"></div>
                                        <div 
                                            class="absolute top-0.5 bottom-0.5 transition-all duration-300 rounded-sm"
                                            :style="getBarStyles(concludedScopeScores[idx])"
                                        ></div>
                                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                            <span class="text-[10px] font-bold text-slate-900 dark:text-slate-100">
                                                {{ concludedScopeScores[idx] }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-2">
                                    <div class="relative h-4 w-16 bg-slate-100 dark:bg-slate-700 rounded overflow-hidden mx-auto border border-slate-200 dark:border-slate-600">
                                        <div 
                                            class="absolute top-0.5 bottom-0.5 left-0 rounded-sm transition-all"
                                            :style="getLevelStyles(suggestedLevels[idx], '#2563eb')"
                                        ></div>
                                        <div class="absolute inset-0 flex items-center justify-center text-[10px] font-bold text-slate-900 dark:text-slate-100">
                                            {{ suggestedLevels[idx] }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <select
                                        :value="agreedLevels[idx]"
                                        @change="setAgreedLevel(idx, $event.target.value)"
                                        class="w-16 px-1.5 py-1 rounded-md border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-[10px] font-semibold text-slate-900 dark:text-slate-100 focus:ring-1 focus:ring-slate-500 focus:border-slate-500"
                                    >
                                        <option v-for="n in 5" :key="n" :value="n">{{ n }}</option>
                                    </select>
                                </td>
                                <td class="px-3 py-2">
                                    <input
                                        type="text"
                                        v-model="reasonsTarget[idx]"
                                        class="w-full px-2 py-1.5 rounded-md border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900/40 text-[10px]"
                                        placeholder="Reason..."
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Radar Chart -->
            <ChartCard title="Agreed Target Capability Radar" subtitle="Concluded scope mapped to capability levels" :flush="true" height="600px">
                <div class="h-full w-full">
                    <RadarChart
                        :labels="objectiveLabels"
                        :data="agreedLevels"
                        :max-data="maxCapabilityLevels"
                        height="600px"
                    />
                </div>
            </ChartCard>

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
