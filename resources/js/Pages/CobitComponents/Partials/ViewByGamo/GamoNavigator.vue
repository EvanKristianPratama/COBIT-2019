<script setup>
import { computed } from 'vue';
import DomainObjectiveNav from '../../Components/DomainObjectiveNav.vue';
import GamoDetail from './GamoDetail.vue';

/**
 * GamoNavigator - View by GAMO navigation
 * Uses DomainObjectiveNav with Link-based navigation for URL-driven objective selection.
 */
const props = defineProps({
    objectives: Array,
    selectedObjective: Object,
    masterRoles: Array,
});

// Derive selected ID from object
const selectedObjectiveId = computed(() => props.selectedObjective?.objective_id || null);

// Derive initial domain from selected objective
const initialDomain = computed(() => {
    if (props.selectedObjective) {
        return props.selectedObjective.objective_id.substring(0, 3).toUpperCase();
    }
    return 'EDM';
});
</script>

<template>
    <div class="space-y-6">
        <!-- Domain + Objective Navigation (Shared Component) -->
        <DomainObjectiveNav 
            :objectives="objectives"
            :selectedObjectiveId="selectedObjectiveId"
            :initialDomain="initialDomain"
            :useLinks="true"
            linkBase="/objectives/"
        />

        <!-- Detail View -->
        <div v-if="selectedObjective">
            <GamoDetail :objective="selectedObjective" :masterRoles="masterRoles" />
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-16 bg-white dark:bg-[#1a1a1a] rounded-2xl border border-dashed border-gray-300 dark:border-white/10">
            <div class="inline-flex p-4 rounded-full bg-gray-50 dark:bg-white/5 mb-4">
                <svg class="w-10 h-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Select an Objective</h3>
            <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">Choose an objective from the list above to view its details.</p>
        </div>
    </div>
</template>
