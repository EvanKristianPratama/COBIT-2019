<script setup>
import { ref, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import MasterIndex from './Partials/Master/MasterIndex.vue';
import GamoGrid from './Partials/ViewByGamo/GamoGrid.vue';
import ComponentSelector from './Partials/ViewByComponent/ComponentSelector.vue';

// Tabs State
const props = defineProps({
    masterEnterGoals: Array,
    masterAlignGoals: Array,
    masterRoles: Array,
    objectives: Array,
    initialTab: {
        type: String,
        default: 'gamo'
    },
    componentData: Object, // Data for ViewByComponent
    selectedComponent: String,
});

const activeTab = ref(props.initialTab); // 'gamo' | 'component' | 'master'

watch(() => props.initialTab, (val) => {
    if (val) activeTab.value = val;
});

const tabs = [
    { id: 'gamo', label: 'View by GAMO', icon: 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z' },
    { id: 'component', label: 'View by Component', icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10' },
    { id: 'master', label: 'Master Data', icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z' }
];
</script>

<template>
    <Head title="COBIT Dictionary" />

    <AuthenticatedLayout title="Kamus Component">
        <template #header>
            <PageHeader 
                title="Kamus Component" 
                subtitle="Dictionary of COBIT 2019 Objectives and Components"
                :breadcrumbs="[
                    { label: 'Dashboard', href: '/dashboard' },
                    { label: 'Kamus Component', href: '/cobit-dictionary' },
                ]"
            />
        </template>

        <div class="space-y-6">
            <!-- Tab Navigation -->
            <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl p-2 border border-gray-200/80 dark:border-white/5 shadow-sm inline-flex">
                <button 
                    v-for="tab in tabs" 
                    :key="tab.id"
                    @click="activeTab = tab.id"
                    class="flex items-center px-6 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200"
                    :class="activeTab === tab.id 
                        ? 'bg-[#0f2b5c] text-white shadow-md' 
                        : 'text-gray-500 dark:text-gray-400 hover:text-[#0f2b5c] dark:hover:text-white hover:bg-gray-50 dark:hover:bg-white/5'"
                >
                    <svg class="w-5 h-5 mr-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="tab.icon" />
                    </svg>
                    {{ tab.label }}
                </button>
            </div>

            <!-- Content Area -->
            <transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0 translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 translate-y-2"
                mode="out-in"
            >
                <!-- View by GAMO -->
                <div v-if="activeTab === 'gamo'" key="gamo">
                     <GamoGrid :objectives="objectives" />
                </div>

                <!-- View by Component -->
                <div v-else-if="activeTab === 'component'" key="component">
                     <ComponentSelector 
                        :selectedComponent="selectedComponent"
                        :componentData="componentData"
                        :masterRoles="masterRoles"
                     />
                </div>

                <!-- Master Data -->
                <div v-else-if="activeTab === 'master'" key="master">
                    <MasterIndex 
                        :masterEnterGoals="masterEnterGoals"
                        :masterAlignGoals="masterAlignGoals"
                        :masterRoles="masterRoles"
                    />
                </div>
            </transition>
        </div>
    </AuthenticatedLayout>
</template>
