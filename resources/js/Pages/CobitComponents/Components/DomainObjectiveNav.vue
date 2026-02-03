<script setup>
import { ref, computed, watch } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    objectives: { type: Array, default: () => [] },
    selectedObjectiveId: { type: String, default: null },
    initialDomain: { type: String, default: 'EDM' },
    useLinks: { type: Boolean, default: false },
    linkBase: { type: String, default: '/objectives/' }
});

const emit = defineEmits(['select-objective', 'change-domain']);

const DOMAINS = ['EDM', 'APO', 'BAI', 'DSS', 'MEA'];
const activeDomain = ref(props.initialDomain);

const groupedObjectives = computed(() => {
    const groups = { EDM: [], APO: [], BAI: [], DSS: [], MEA: [] };
    props.objectives?.forEach(obj => {
        const prefix = obj.objective_id?.substring(0, 3).toUpperCase();
        if (groups[prefix]) groups[prefix].push(obj);
    });
    return groups;
});

const currentObjectives = computed(() => groupedObjectives.value[activeDomain.value] || []);

watch(() => props.selectedObjectiveId, (val) => {
    if (val) {
        const prefix = val.substring(0, 3).toUpperCase();
        if (DOMAINS.includes(prefix)) activeDomain.value = prefix;
    }
}, { immediate: true });

function selectDomain(domain) {
    activeDomain.value = domain;
    emit('change-domain', domain);
    const first = groupedObjectives.value[domain]?.[0];
    if (first && !props.useLinks) emit('select-objective', first.objective_id);
}

function selectObjective(id) {
    emit('select-objective', id);
}
</script>

<template>
    <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl border border-gray-200/80 dark:border-white/5 shadow-sm">
        <!-- Domain Underline Tabs -->
        <div class="flex border-b border-gray-200 dark:border-white/10">
            <button 
                v-for="domain in DOMAINS" 
                :key="domain"
                @click="selectDomain(domain)"
                class="px-6 py-3 text-sm font-medium transition-all relative"
                :class="activeDomain === domain 
                    ? 'text-slate-900 dark:text-white' 
                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'"
            >
                {{ domain }}
                <!-- Underline indicator -->
                <span 
                    v-if="activeDomain === domain"
                    class="absolute bottom-0 left-0 right-0 h-0.5 bg-slate-900 dark:bg-white"
                ></span>
            </button>
        </div>

        <!-- Objective Pills -->
        <div class="p-4 flex flex-wrap gap-2">
            <template v-if="useLinks">
                <Link
                    v-for="obj in currentObjectives" 
                    :key="obj.objective_id"
                    :href="`${linkBase}${obj.objective_id}`"
                    preserve-scroll
                    class="px-3 py-1.5 rounded-md text-sm font-medium transition-all"
                    :class="selectedObjectiveId === obj.objective_id
                        ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900'
                        : 'bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-white/10'"
                >
                    {{ obj.objective_id }}
                </Link>
            </template>
            <template v-else>
                <button
                    v-for="obj in currentObjectives" 
                    :key="obj.objective_id"
                    @click="selectObjective(obj.objective_id)"
                    class="px-3 py-1.5 rounded-md text-sm font-medium transition-all"
                    :class="selectedObjectiveId === obj.objective_id
                        ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900'
                        : 'bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-white/10'"
                >
                    {{ obj.objective_id }}
                </button>
            </template>
            <span v-if="!currentObjectives.length" class="text-sm text-slate-400 italic py-2">
                No objectives in {{ activeDomain }}
            </span>
        </div>
    </div>
</template>
