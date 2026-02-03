<script setup>
import { ref, watch, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import DomainObjectiveNav from '../../Components/DomainObjectiveNav.vue';
import ComponentDetail from './ComponentDetail.vue';

const props = defineProps({
    selectedComponent: String,
    componentData: Object,
    masterRoles: Array,
});

const emit = defineEmits(['update-breadcrumb']);

const components = [
    { id: 'overview', label: 'Overview' },
    { id: 'practices', label: 'Process' },
    { id: 'organizational', label: 'Org Structures' },
    { id: 'infoflows', label: 'Info Flows' },
    { id: 'skills', label: 'Skills' },
    { id: 'policies', label: 'Policies' },
    { id: 'culture', label: 'Culture' },
    { id: 'services', label: 'Services' },
];

const selected = ref(props.selectedComponent || 'overview');
const activeObjectiveId = ref(null);

const objectivesForNav = computed(() => props.componentData ? Array.from(props.componentData) : []);
const filteredData = computed(() => {
    if (!activeObjectiveId.value || !props.componentData) return [];
    return props.componentData.filter(o => o.objective_id === activeObjectiveId.value);
});

watch(activeObjectiveId, (val) => emit('update-breadcrumb', val));

function onSelectChange() {
    if (selected.value) {
        router.get(`/objectives/component/${selected.value}`, {}, {
            preserveState: true,
            preserveScroll: true,
            only: ['componentData', 'selectedComponent', 'initialTab'],
        });
    }
}

onMounted(() => {
    if (!props.componentData && selected.value) onSelectChange();
});

watch(() => props.componentData, (val) => {
    if (val?.length > 0 && !val.find(o => o.objective_id === activeObjectiveId.value)) {
        activeObjectiveId.value = val[0]?.objective_id;
    }
}, { immediate: true });
</script>

<template>
    <div class="space-y-4">
        <!-- Component Type Underline Tabs -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl border border-gray-200/80 dark:border-white/5 shadow-sm">
            <div class="flex border-b border-gray-200 dark:border-white/10 overflow-x-auto">
                <button 
                    v-for="c in components" 
                    :key="c.id"
                    @click="() => { selected = c.id; onSelectChange(); }"
                    class="px-5 py-3 text-sm font-medium transition-all relative whitespace-nowrap"
                    :class="selected === c.id 
                        ? 'text-slate-900 dark:text-white' 
                        : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'"
                >
                    {{ c.label }}
                    <span 
                        v-if="selected === c.id"
                        class="absolute bottom-0 left-0 right-0 h-0.5 bg-slate-900 dark:bg-white"
                    ></span>
                </button>
            </div>
        </div>

        <!-- Domain + Objective Navigation -->
        <template v-if="componentData">
            <DomainObjectiveNav 
                :objectives="objectivesForNav"
                :selectedObjectiveId="activeObjectiveId"
                :useLinks="false"
                @select-objective="activeObjectiveId = $event"
            />

            <ComponentDetail 
                v-if="selected && filteredData.length" 
                :data="filteredData" 
                :type="selected"
                :masterRoles="masterRoles"
                :key="selected + activeObjectiveId"
            />
        </template>

        <!-- Loading -->
        <div v-else class="text-center py-12 bg-white dark:bg-[#1a1a1a] rounded-2xl border border-gray-200/80 dark:border-white/5">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-slate-600 mx-auto mb-3"></div>
            <p class="text-slate-500 dark:text-slate-400 text-sm">Loading...</p>
        </div>
    </div>
</template>
