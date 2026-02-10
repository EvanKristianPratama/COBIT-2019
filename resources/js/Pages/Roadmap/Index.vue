<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import ChartCard from '@/Pages/DesignToolkit/Components/ChartCard.vue';
import LineChart from '@/Pages/DesignToolkit/Components/LineChart.vue';
import SpiderChart from '@/Pages/DesignToolkit/Components/SpiderChart.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';

const props = defineProps({
    objectives: Array,
    mappedRoadmaps: Object,
    years: Array,
    assessments: Array,
    evals: Array,
    selectedAssessmentId: [String, Number, null],
    routes: Object,
});

const breadcrumbs = [
    { label: 'Dashboard', href: route('dashboard') },
    { label: 'Roadmap' },
];

const yearsLocal = ref([...props.years]);
const selectedYear = ref(yearsLocal.value[0] ?? new Date().getFullYear());
const source = ref('step4');
const infoText = ref('');
const isSaving = ref(false);
const isApplying = ref(false);
const isEditing = ref(false);
const confirmState = ref({
    show: false,
    title: '',
    message: '',
    confirmText: '',
    type: 'info',
});
let confirmAction = null;

const selectedAssessmentId = ref(props.selectedAssessmentId || '');
const selectedEvalId = ref('');
const selectedScopeId = ref('');
const scopes = ref([]);
const scopeCache = {};

const hiddenObjectiveIds = ref([]);

const levels = ref({});
const ratings = ref({});

const BUMN_GAMOS = [
    'EDM01', 'EDM02', 'APO01', 'APO02', 'APO03', 'APO05', 'APO06', 'APO09',
    'APO10', 'APO12', 'APO13', 'APO14', 'BAI02', 'BAI03', 'BAI04', 'BAI06',
    'BAI07', 'BAI09', 'BAI11', 'DSS01', 'DSS02', 'DSS04', 'DSS05', 'MEA01'
];

const initState = () => {
    const nextLevels = {};
    const nextRatings = {};

    (props.objectives || []).forEach((obj) => {
        const objId = obj.objective_id;
        nextLevels[objId] = {};
        nextRatings[objId] = {};
        yearsLocal.value.forEach((year) => {
            const existing = props.mappedRoadmaps?.[objId]?.[year] || {};
            nextLevels[objId][year] = existing.level ?? '';
            nextRatings[objId][year] = existing.rating ?? '';
        });
    });

    levels.value = nextLevels;
    ratings.value = nextRatings;
};

const ratingOptions = (level) => {
    const parsed = parseInt(level, 10);
    if (Number.isNaN(parsed)) return [];
    const options = [`${parsed}L`, `${parsed}F`];
    if (parsed < 5) {
        options.push(`${parsed + 1}P`);
    }
    return options;
};

const onLevelChange = (objectiveId, year) => {
    const level = levels.value?.[objectiveId]?.[year];
    const options = ratingOptions(level);
    const current = ratings.value?.[objectiveId]?.[year] || '';
    if (current && !options.includes(current)) {
        ratings.value[objectiveId][year] = '';
    }
};

const isHidden = (objectiveId) => hiddenObjectiveIds.value.includes(objectiveId);

const setScopeFilter = (objectiveIds, year, clearNonScope = false) => {
    const scopeSet = new Set(objectiveIds || []);
    const allIds = (props.objectives || []).map((o) => o.objective_id);
    hiddenObjectiveIds.value = allIds.filter((id) => !scopeSet.has(id));

    if (clearNonScope && year) {
        hiddenObjectiveIds.value.forEach((id) => {
            if (!levels.value[id]) return;
            levels.value[id][year] = '';
            ratings.value[id][year] = '';
        });
    }
};

const clearScopeFilter = () => {
    hiddenObjectiveIds.value = [];
};

const setLevelForObjectives = (objectiveLevelMap, year) => {
    Object.entries(objectiveLevelMap || {}).forEach(([objectiveId, level]) => {
        if (!levels.value[objectiveId]) return;
        levels.value[objectiveId][year] = level || '';
        onLevelChange(objectiveId, year);
    });
};

const loadScopes = async (evalId) => {
    if (!evalId) return [];
    if (scopeCache[evalId]) return scopeCache[evalId];
    const res = await fetch(`${props.routes.scopes}?eval_id=${evalId}`);
    const data = await res.json();
    scopeCache[evalId] = data.scopes || [];
    return scopeCache[evalId];
};

const applySource = async () => {
    const year = selectedYear.value;
    if (!year) {
        infoText.value = 'Pilih tahun terlebih dahulu.';
        return;
    }

    infoText.value = '';
    isApplying.value = true;

    try {
        if (source.value === 'step4') {
            if (!selectedAssessmentId.value) {
                infoText.value = 'Pilih assessment Step 4.';
                return;
            }
            const res = await fetch(`${props.routes.step4Scope}?assessment_id=${selectedAssessmentId.value}`);
            const data = await res.json();
            const objectives = data.objectives || [];

            if (!objectives.length) {
                infoText.value = 'Scope Step 4 kosong.';
                return;
            }

            const ids = objectives.map((o) => o.objective_id);
            setScopeFilter(ids, year, true);

            const levelMap = {};
            objectives.forEach((o) => {
                levelMap[o.objective_id] = o.agreed_level || '';
            });
            setLevelForObjectives(levelMap, year);

            infoText.value = `Step 4 diterapkan: ${ids.length} GAMO untuk tahun ${year}.`;
            return;
        }

        if (source.value === 'scope') {
            if (!selectedEvalId.value || !selectedScopeId.value) {
                infoText.value = 'Pilih eval dan scope.';
                return;
            }
            const allScopes = await loadScopes(selectedEvalId.value);
            const scope = allScopes.find((s) => String(s.id) === String(selectedScopeId.value));
            const objectives = scope?.objectives || [];
            if (!objectives.length) {
                infoText.value = 'Scope kosong.';
                return;
            }
            setScopeFilter(objectives, year, true);
            infoText.value = `Scope assessment diterapkan: ${objectives.length} GAMO.`;
            return;
        }

        if (source.value === 'bumn') {
            setScopeFilter(BUMN_GAMOS, year, true);
            const levelMap = {};
            BUMN_GAMOS.forEach((id) => {
                levelMap[id] = 3;
            });
            setLevelForObjectives(levelMap, year);
            infoText.value = `BUMN diterapkan: ${BUMN_GAMOS.length} GAMO level 3 untuk tahun ${year}.`;
            return;
        }

        clearScopeFilter();
        infoText.value = 'Manual input aktif.';
    } finally {
        isApplying.value = false;
    }
};

const addYear = () => {
    const year = parseInt(newYear.value, 10);
    if (!year) return;

    if (yearsLocal.value.includes(year)) {
        infoText.value = 'Year already exists.';
        return;
    }

    router.get(props.routes.roadmap, { add_year: year }, { preserveState: false });
};

const openConfirm = ({ title, message, confirmText, type, onConfirm }) => {
    confirmState.value = {
        show: true,
        title,
        message,
        confirmText,
        type,
    };
    confirmAction = onConfirm;
};

const closeConfirm = () => {
    confirmState.value.show = false;
    confirmAction = null;
};

const handleConfirm = () => {
    if (confirmAction) confirmAction();
    closeConfirm();
};

const requestEdit = () => {
    if (isEditing.value) {
        isEditing.value = false;
        return;
    }
    openConfirm({
        title: 'Enable Edit Mode',
        message: 'Table akan bisa diedit. Pastikan data sumber sudah benar.',
        confirmText: 'Enable',
        type: 'warning',
        onConfirm: () => {
            isEditing.value = true;
        },
    });
};

const getYearScore = (year) => {
    const values = visibleObjectiveList.value
        .map((obj) => {
            const raw = levels.value?.[obj.objective_id]?.[year];
            const num = typeof raw === 'number' ? raw : parseInt(raw, 10);
            return Number.isFinite(num) ? num : null;
        })
        .filter((v) => v !== null);

    if (!values.length) return null;
    const avg = values.reduce((acc, v) => acc + v, 0) / values.length;
    return Number(avg.toFixed(2));
};

const performDeleteYear = () => {
    const year = selectedYear.value;
    if (!year) return;

    router.post(props.routes.deleteYear, { year }, { preserveScroll: true });
};

const requestDeleteYear = () => {
    const year = selectedYear.value;
    if (!year) return;
    openConfirm({
        title: 'Delete Year',
        message: `Hapus semua roadmap untuk tahun ${year}? Data yang dihapus tidak bisa dikembalikan.`,
        confirmText: 'Delete',
        type: 'danger',
        onConfirm: performDeleteYear,
    });
};

const requestSave = () => {
    if (!isEditing.value) return;
    openConfirm({
        title: 'Save Roadmap',
        message: 'Simpan perubahan roadmap untuk semua GAMO?',
        confirmText: 'Save',
        type: 'success',
        onConfirm: saveRoadmap,
    });
};

const saveRoadmap = () => {
    const items = [];
    (props.objectives || []).forEach((obj) => {
        const objId = obj.objective_id;
        yearsLocal.value.forEach((year) => {
            const level = levels.value?.[objId]?.[year] ?? '';
            const rating = ratings.value?.[objId]?.[year] ?? '';
            items.push({
                objective_id: objId,
                year,
                level: level === '' ? null : level,
                rating: rating === '' ? null : rating,
            });
        });
    });

    isSaving.value = true;
    router.post(props.routes.store, { items }, {
        preserveScroll: true,
        onFinish: () => {
            isSaving.value = false;
        },
    });
};

const parseLevelValue = (value) => {
    if (value === '' || value === null || value === undefined) return null;
    if (typeof value === 'number') return Number.isFinite(value) ? value : null;
    const match = String(value).match(/-?\d+(\.\d+)?/);
    if (!match) return null;
    const parsed = parseFloat(match[0]);
    return Number.isFinite(parsed) ? parsed : null;
};

const toneClassForValue = (value) => {
    const num = parseLevelValue(value);
    if (num === null) {
        return 'text-slate-400 dark:text-slate-500';
    }
    if (num < 1.5) {
        return 'text-red-600 dark:text-red-400';
    }
    if (num < 2.5) {
        return 'text-amber-600 dark:text-amber-400';
    }
    if (num < 3.5) {
        return 'text-emerald-600 dark:text-emerald-400';
    }
    if (num < 4.5) {
        return 'text-blue-600 dark:text-blue-400';
    }
    return 'text-violet-600 dark:text-violet-400';
};

const valueTextClass = (value) => {
    return `text-[10px] font-semibold ${toneClassForValue(value)}`;
};

const toggleChartYear = (year) => {
    if (selectedChartYears.value.includes(year)) {
        if (selectedChartYears.value.length === 1) return;
        selectedChartYears.value = selectedChartYears.value.filter((y) => y !== year);
        return;
    }
    selectedChartYears.value = [...selectedChartYears.value, year];
};

const newYear = ref(new Date().getFullYear() + 1);

const totalObjectives = computed(() => (props.objectives || []).length);
const visibleObjectives = computed(() => totalObjectives.value - hiddenObjectiveIds.value.length);

const allObjectiveList = computed(() => (props.objectives || []));
const visibleObjectiveList = computed(() => (props.objectives || []).filter((obj) => !hiddenObjectiveIds.value.includes(obj.objective_id)));

const chartColors = ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#0ea5e9'];
const selectedChartYears = ref([...yearsLocal.value]);

const chartSeries = computed(() => {
    const years = selectedChartYears.value.length ? selectedChartYears.value : [selectedYear.value];
    const labels = [];
    const dataByYear = {};

    years.forEach((year) => {
        dataByYear[year] = [];
    });

    allObjectiveList.value.forEach((obj) => {
        const values = years.map((year) => {
            const raw = levels.value?.[obj.objective_id]?.[year];
            if (raw === '' || raw === null || raw === undefined) return null;
            const num = typeof raw === 'number' ? raw : parseInt(raw, 10);
            return Number.isFinite(num) ? num : null;
        });

        if (values.some((v) => v !== null)) {
            labels.push(obj.objective_id);
            years.forEach((year, idx) => {
                dataByYear[year].push(values[idx]);
            });
        }
    });

    const datasets = years.map((year, idx) => ({
        label: String(year),
        data: dataByYear[year],
        color: chartColors[idx % chartColors.length],
    }));

    return { labels, datasets };
});

const averageYearLabels = computed(() => yearsLocal.value || []);
const averageYearData = computed(() => {
    return (yearsLocal.value || []).map((year) => {
        const values = allObjectiveList.value
            .map((obj) => {
                const raw = levels.value?.[obj.objective_id]?.[year];
                const num = typeof raw === 'number' ? raw : parseInt(raw, 10);
                return Number.isFinite(num) ? num : null;
            })
            .filter((v) => v !== null);
        if (!values.length) return null;
        const avg = values.reduce((acc, v) => acc + v, 0) / values.length;
        return Number(avg.toFixed(2));
    });
});

watch(selectedEvalId, async (val) => {
    if (!val) {
        scopes.value = [];
        selectedScopeId.value = '';
        return;
    }
    scopes.value = await loadScopes(val);
    selectedScopeId.value = '';
});

watch(
    () => props.years,
    (val) => {
        yearsLocal.value = [...(val || [])];
        if (!yearsLocal.value.includes(selectedYear.value)) {
            selectedYear.value = yearsLocal.value[0] ?? new Date().getFullYear();
        }
        selectedChartYears.value = [...yearsLocal.value];
        initState();
    }
);

initState();
</script>

<template>
    <Head title="Roadmap Capability" />

    <AuthenticatedLayout title="Roadmap Capability">
        <template #header>
            <PageHeader
                title="Roadmap Capability"
                subtitle="Target capability dari tahun ke tahun (bisa tarik dari Step 4)"
                :breadcrumbs="breadcrumbs"
            />
        </template>

        <div class="max-w-6xl mx-auto space-y-5">
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-700 bg-slate-900 text-white flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-white">Roadmap Target</h2>
                        <p class="text-[11px] text-slate-300 mt-1">
                            Total GAMO: {{ totalObjectives }} · Tahun: {{ yearsLocal.length }}
                        </p>
                    </div>
                </div>

                <div class="p-4 space-y-4">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">
                        <div class="lg:col-span-2">
                            <label class="text-[11px] font-semibold text-slate-600 dark:text-slate-400">Tahun Target</label>
                            <select v-model.number="selectedYear" class="mt-1 w-full rounded-lg border-slate-200 dark:border-slate-600 dark:bg-slate-900/40 text-xs focus:ring-2 focus:ring-emerald-500">
                                <option v-for="year in yearsLocal" :key="year" :value="year">{{ year }}</option>
                            </select>
                        </div>

                        <div class="lg:col-span-3">
                            <label class="text-[11px] font-semibold text-slate-600 dark:text-slate-400">Sumber Target</label>
                            <select v-model="source" class="mt-1 w-full rounded-lg border-slate-200 dark:border-slate-600 dark:bg-slate-900/40 text-xs focus:ring-2 focus:ring-emerald-500">
                                <option value="step4">1. Design Factor / Step 4</option>
                                <option value="scope">2. Scope Assessment</option>
                                <option value="bumn">3. BUMN</option>
                                <option value="manual">4. Manual Input</option>
                            </select>
                        </div>

                        <div v-if="source === 'step4'" class="lg:col-span-4">
                            <label class="text-[11px] font-semibold text-slate-600 dark:text-slate-400">Assessment (Step 4)</label>
                            <select v-model="selectedAssessmentId" class="mt-1 w-full rounded-lg border-slate-200 dark:border-slate-600 dark:bg-slate-900/40 text-xs focus:ring-2 focus:ring-emerald-500">
                                <option value="">Pilih assessment</option>
                                <option v-for="assess in assessments" :key="assess.assessment_id" :value="assess.assessment_id">
                                    {{ assess.kode_assessment || `ID ${assess.assessment_id}` }}{{ assess.instansi ? ` - ${assess.instansi}` : '' }}
                                </option>
                            </select>
                        </div>

                        <div v-if="source === 'scope'" class="lg:col-span-3">
                            <label class="text-[11px] font-semibold text-slate-600 dark:text-slate-400">Assessment Eval</label>
                            <select v-model="selectedEvalId" class="mt-1 w-full rounded-lg border-slate-200 dark:border-slate-600 dark:bg-slate-900/40 text-xs focus:ring-2 focus:ring-emerald-500">
                                <option value="">Pilih eval</option>
                                <option v-for="evalItem in evals" :key="evalItem.eval_id" :value="evalItem.eval_id">
                                    Eval {{ evalItem.eval_id }}{{ evalItem.tahun ? ` - ${evalItem.tahun}` : '' }}
                                </option>
                            </select>
                        </div>

                        <div v-if="source === 'scope'" class="lg:col-span-3">
                            <label class="text-[11px] font-semibold text-slate-600 dark:text-slate-400">Scope</label>
                            <select v-model="selectedScopeId" class="mt-1 w-full rounded-lg border-slate-200 dark:border-slate-600 dark:bg-slate-900/40 text-xs focus:ring-2 focus:ring-emerald-500">
                                <option value="">Pilih scope</option>
                                <option v-for="scope in scopes" :key="scope.id" :value="scope.id">
                                    {{ scope.name || `Scope ${scope.id}` }}
                                </option>
                            </select>
                        </div>

                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3 text-[11px] text-slate-500 dark:text-slate-400">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-slate-600 dark:text-slate-400">Tambah Tahun</span>
                            <input v-model.number="newYear" type="number" class="w-24 rounded-md border-slate-200 dark:border-slate-600 dark:bg-slate-900/40 text-[11px] focus:ring-2 focus:ring-emerald-500" />
                            <button
                                class="inline-flex items-center px-3 py-1.5 text-[11px] font-semibold rounded-md border border-slate-200 text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700 transition"
                                @click="addYear"
                            >
                                Add Year
                            </button>
                        </div>
                        <div class="inline-flex items-center gap-2">
                            <button
                                class="px-4 py-2 text-xs font-semibold rounded-full bg-slate-900 text-white hover:bg-slate-800 transition shadow-sm disabled:opacity-70"
                                :disabled="isApplying"
                                @click="applySource"
                            >
                                {{ isApplying ? 'Applying...' : 'Terapkan' }}
                            </button>
                            <button
                                class="px-4 py-2 text-xs font-semibold rounded-full bg-white text-slate-700 hover:bg-slate-50 border border-slate-300 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-600 dark:hover:bg-slate-700 transition shadow-sm"
                                @click="requestEdit"
                            >
                                {{ isEditing ? 'Lock Table' : 'Edit Table' }}
                            </button>
                            <button
                                class="px-4 py-2 text-xs font-semibold rounded-full bg-emerald-600 text-white hover:bg-emerald-700 transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                :disabled="!isEditing || isSaving"
                                @click="requestSave"
                            >
                                {{ isSaving ? 'Saving...' : 'Save' }}
                            </button>
                            <button
                                class="px-4 py-2 text-xs font-semibold rounded-full bg-white text-rose-600 hover:bg-rose-50 border border-rose-200 dark:bg-slate-800 dark:text-rose-400 dark:border-rose-700 dark:hover:bg-rose-900/20 transition shadow-sm"
                                @click="requestDeleteYear"
                            >
                                Delete
                            </button>
                        </div>
                        <span v-if="infoText" class="text-emerald-700 dark:text-emerald-400">{{ infoText }}</span>
                    </div>
                </div>

                <div class="border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="overflow-auto max-h-[70vh]">
                        <table class="w-full text-[11px] text-left">
                            <thead class="sticky top-0 z-20 text-[10px] uppercase tracking-wider">
                                <tr class="bg-slate-900 text-white">
                                    <th rowspan="2" class="sticky left-0 z-30 bg-slate-900 px-3 py-2 text-left w-24 border-b border-slate-700">
                                        GAMO
                                    </th>
                                    <th rowspan="2" class="sticky left-24 z-30 bg-slate-900 px-3 py-2 text-left min-w-[260px] border-b border-slate-700">
                                        Description
                                    </th>
                                    <th v-for="year in yearsLocal" :key="year" colspan="2" class="px-3 py-2 text-center border-b border-slate-700">
                                        {{ year }}
                                    </th>
                                </tr>
                                <tr class="bg-slate-800 text-slate-100">
                                    <template v-for="year in yearsLocal" :key="`sub-${year}`">
                                        <th class="px-2 py-2 text-center border-b border-slate-700">Level</th>
                                        <th class="px-2 py-2 text-center border-b border-slate-700">Rating</th>
                                    </template>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                <tr
                                    v-for="obj in objectives"
                                    :key="obj.objective_id"
                                    v-show="!isHidden(obj.objective_id)"
                                    class="hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors"
                                >
                                    <td class="sticky left-0 z-10 bg-white dark:bg-slate-800 px-3 py-1.5 font-semibold border-b border-slate-200 dark:border-slate-700">
                                        {{ obj.objective_id }}
                                    </td>
                                    <td class="sticky left-24 z-10 bg-white dark:bg-slate-800 px-3 py-1.5 text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-700">
                                        {{ obj.objective }}
                                    </td>
                                    <template v-for="year in yearsLocal" :key="`${obj.objective_id}-${year}`">
                                        <td class="px-2 py-1 border-b border-slate-200 dark:border-slate-700 text-center">
                                            <template v-if="isEditing">
                                                <input
                                                    type="number"
                                                    min="0"
                                                    max="5"
                                                    class="w-14 text-center rounded-md border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-[10px] font-semibold text-slate-900 dark:text-slate-100 focus:ring-1 focus:ring-slate-500 focus:border-slate-500"
                                                    v-model.number="levels[obj.objective_id][year]"
                                                    @change="onLevelChange(obj.objective_id, year)"
                                                />
                                            </template>
                                            <template v-else>
                                                <span :class="valueTextClass(levels[obj.objective_id][year])">
                                                    {{ (levels[obj.objective_id][year] !== '' && levels[obj.objective_id][year] !== null && levels[obj.objective_id][year] !== undefined) ? levels[obj.objective_id][year] : '-' }}
                                                </span>
                                            </template>
                                        </td>
                                        <td class="px-2 py-1 border-b border-slate-200 dark:border-slate-700 text-center">
                                            <template v-if="isEditing">
                                                <select
                                                    class="w-16 text-center rounded-md border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-[10px] font-semibold text-slate-900 dark:text-slate-100 focus:ring-1 focus:ring-slate-500 focus:border-slate-500"
                                                    v-model="ratings[obj.objective_id][year]"
                                                >
                                                    <option value="">-</option>
                                                    <option
                                                        v-for="option in ratingOptions(levels[obj.objective_id][year])"
                                                        :key="option"
                                                        :value="option"
                                                    >
                                                        {{ option }}
                                                    </option>
                                                </select>
                                            </template>
                                            <template v-else>
                                                <span :class="valueTextClass(ratings[obj.objective_id][year])">
                                                    {{ ratings[obj.objective_id][year] || '-' }}
                                                </span>
                                            </template>
                                        </td>
                                    </template>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="sticky bottom-0 z-20 bg-slate-50 dark:bg-slate-900/70 text-slate-700 dark:text-slate-200">
                                    <td class="sticky left-0 bottom-0 z-30 bg-slate-50 dark:bg-slate-900/70 px-3 py-2 text-[10px] font-semibold border-t border-slate-200 dark:border-slate-700">
                                        Avg
                                    </td>
                                    <td class="sticky left-24 bottom-0 z-30 bg-slate-50 dark:bg-slate-900/70 px-3 py-2 text-[10px] text-slate-500 dark:text-slate-400 border-t border-slate-200 dark:border-slate-700">
                                        Rata-rata per Tahun (visible GAMO)
                                    </td>
                                    <template v-for="year in yearsLocal" :key="`avg-${year}`">
                                        <td class="px-2 py-2 text-center text-[10px] font-semibold border-t border-slate-200 dark:border-slate-700">
                                            <span :class="valueTextClass(getYearScore(year))">
                                                {{ getYearScore(year) !== null ? getYearScore(year).toFixed(2) : '-' }}
                                            </span>
                                        </td>
                                        <td class="px-2 py-2 text-center text-[10px] border-t border-slate-200 dark:border-slate-700">
                                            <span class="text-[10px] font-semibold text-slate-400 dark:text-slate-500">-</span>
                                        </td>
                                    </template>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

            </div>

            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Charts</h3>
                <span class="text-[11px] text-gray-500 dark:text-gray-400">Rata-rata per tahun & per GAMO</span>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="lg:col-span-1">
                    <ChartCard
                        title="Average per Year"
                        subtitle="Rata-rata target capability per tahun"
                        :flush="true"
                        height="260px"
                    >
                        <LineChart
                            :labels="averageYearLabels"
                            :data="averageYearData"
                            height="260px"
                            :min="0"
                            :max="5"
                            :step-size="1"
                            title="Average Level"
                            line-color="rgba(37, 99, 235, 0.9)"
                        />
                    </ChartCard>
                </div>
                <div class="lg:col-span-2">
                    <ChartCard
                        title="Target per GAMO (40)"
                        subtitle="Spider chart target capability per GAMO"
                        :flush="true"
                        height="520px"
                    >
                        <div class="h-full flex flex-col">
                            <div class="flex flex-wrap items-center gap-2 px-4 py-2 border-b border-slate-200 dark:border-slate-700 text-[11px]">
                                <span class="text-slate-500 dark:text-slate-400">Years</span>
                                <button
                                    v-for="year in yearsLocal"
                                    :key="`chart-${year}`"
                                    type="button"
                                    @click="toggleChartYear(year)"
                                    :class="selectedChartYears.includes(year)
                                        ? 'bg-slate-900 text-white border-slate-900'
                                        : 'bg-white text-slate-600 border-slate-300 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-600'"
                                    class="px-2 py-1 rounded-md border text-[10px] font-semibold transition"
                                >
                                    {{ year }}
                                </button>
                                <span class="text-slate-400">·</span>
                                <span class="text-slate-500 dark:text-slate-400">Non-null only</span>
                            </div>
                            <div class="flex-1 p-4">
                                <SpiderChart
                                    :labels="chartSeries.labels"
                                    :datasets="chartSeries.datasets"
                                    :min="0"
                                    :max="5"
                                    :step-size="1"
                                    dataset-label="Target Level"
                                    :reverse-labels="false"
                                    height="100%"
                                />
                            </div>
                        </div>
                    </ChartCard>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

    <ConfirmModal
        :show="confirmState.show"
        :title="confirmState.title"
        :message="confirmState.message"
        :confirmText="confirmState.confirmText"
        cancelText="Batal"
        :type="confirmState.type"
        :loading="isSaving"
        @close="closeConfirm"
        @confirm="handleConfirm"
    />
</template>
