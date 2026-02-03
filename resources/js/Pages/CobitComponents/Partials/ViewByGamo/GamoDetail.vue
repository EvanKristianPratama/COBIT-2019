<script setup>
import { computed, ref } from 'vue';

// Component Tables
import ProcessTable from '../../Components/ProcessTable.vue';
import OrgStructureTable from '../../Components/OrgStructureTable.vue';
import FlowsTable from '../../Components/FlowsTable.vue';
import SkillsTable from '../../Components/SkillsTable.vue';
import PoliciesTable from '../../Components/PoliciesTable.vue';
import CultureTable from '../../Components/CultureTable.vue';
import ServicesTable from '../../Components/ServicesTable.vue';

const props = defineProps({
    objective: Object,
    masterRoles: Array,
});

const activeTab = ref('components'); // Default to components list as it's the core content

const tabs = [
    { id: 'components', label: 'COBIT Components (A-G)' },
    { id: 'goals', label: 'Goals Cascade' },
    { id: 'overview', label: 'Overview' },
];

const domainColor = computed(() => {
    // Basic domain coloring
    return 'text-[#0f2b5c] bg-white'; 
});
</script>

<template>
    <div class="space-y-6">
        <!-- COBIT Header Style Card -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-none border-2 border-[#8B0000] dark:border-red-900 overflow-hidden shadow-md">
            <!-- Header Banner -->
            <div class="bg-[#8B0000] text-white px-4 py-3 flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <span class="block text-xs uppercase opacity-80 mb-1">Domain: {{ objective.domain_name || 'Evaluate, Direct and Monitor' }}</span>
                    <h2 class="text-xl font-bold">Governance Objective: {{ objective.objective_id }} - {{ objective.objective }}</h2>
                </div>
                <div class="mt-2 md:mt-0 bg-white/20 px-3 py-1 rounded text-sm font-semibold">
                    Focus Area: COBIT Core Model
                </div>
            </div>
            
            <!-- Description & Purpose -->
            <div class="bg-white dark:bg-[#1a1a1a]">
                <div class="border-b border-gray-300 dark:border-white/10 px-4 py-2 bg-gray-50 dark:bg-white/5 font-bold text-sm uppercase text-gray-700 dark:text-gray-300">
                    Description
                </div>
                <div class="p-4 text-gray-800 dark:text-gray-200 border-b border-gray-300 dark:border-white/10">
                    {{ objective.objective_description }}
                </div>
                <div class="border-b border-gray-300 dark:border-white/10 px-4 py-2 bg-gray-50 dark:bg-white/5 font-bold text-sm uppercase text-gray-700 dark:text-gray-300">
                    Purpose
                </div>
                <div class="p-4 text-gray-800 dark:text-gray-200">
                    {{ objective.objective_purpose || 'Secure optimal value from I&T-enabled initiatives...' }}
                </div>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="border-b border-gray-200 dark:border-white/10 mb-4 flex space-x-2">
                <button 
                v-for="tab in tabs" 
                :key="tab.id"
                @click="activeTab = tab.id"
                class="px-4 py-2 text-sm font-semibold rounded-t-lg border-t border-l border-r transition-colors"
                :class="activeTab === tab.id 
                    ? 'bg-white dark:bg-[#1a1a1a] border-[#0f2b5c] dark:border-blue-700 text-[#0f2b5c] dark:text-blue-400 relative top-[1px]' 
                    : 'bg-gray-100 dark:bg-white/5 border-transparent text-gray-500 hover:text-gray-700'"
            >
                {{ tab.label }}
            </button>
        </div>

        <!-- Components Tab (A-G) -->
        <div v-if="activeTab === 'components'" class="space-y-12 pb-12">
            <!-- A. Process -->
            <div id="comp-a">
                <ProcessTable :practices="objective.practices" />
            </div>

            <!-- B. Org Structures -->
            <div id="comp-b">
                <OrgStructureTable :practices="objective.practices" :masterRoles="masterRoles" />
            </div>

            <!-- C. Information Flows -->
            <div id="comp-c">
                <FlowsTable :practices="objective.practices" />
            </div>

            <!-- D. Skills -->
            <div id="comp-d">
                <SkillsTable :skills="objective.skill" />
            </div>

            <!-- E. Policies -->
            <div id="comp-e">
                    <PoliciesTable :policies="objective.policies" />
            </div>

            <!-- F. Culture -->
            <div id="comp-f">
                <CultureTable :culture="objective.keyculture" />
            </div>

            <!-- G. Services -->
            <div id="comp-g">
                <ServicesTable :services="objective.s_i_a" />
            </div>
        </div>

        <!-- Goals Cascade -->
        <div v-else-if="activeTab === 'goals'" class="bg-white dark:bg-[#1a1a1a] border-2 border-[#0f2b5c] dark:border-blue-900 rounded-lg overflow-hidden shadow-lg">
            <div class="bg-blue-100 dark:bg-blue-900/40 p-3 text-center text-sm font-bold text-[#0f2b5c] dark:text-blue-200 border-b border-[#0f2b5c]">
                The governance objective supports the achievement of a set of primary enterprise and alignment goals:
            </div>
            <!-- ... existing Goals layout ... -->
            <div class="grid grid-cols-1 md:grid-cols-2">
                <!-- Enterprise Goals -->
                <div class="border-r border-[#0f2b5c] dark:border-blue-900">
                    <div class="bg-[#0f2b5c]/10 dark:bg-blue-900/20 px-4 py-2 font-bold text-[#0f2b5c] dark:text-blue-300 border-b border-[#0f2b5c] flex items-center">
                        Enterprise Goals
                    </div>
                    <div class="p-4 space-y-2">
                            <div v-for="g in objective.entergoals" :key="g.entergoals_id" class="flex gap-2 text-sm text-gray-800 dark:text-gray-200">
                            <span class="font-bold whitespace-nowrap">{{ g.entergoals_id }}</span>
                            <span>{{ g.description }}</span>
                            </div>
                            <div v-if="!objective.entergoals?.length" class="text-gray-400 italic text-sm">No Enterprise Goals mapped.</div>
                    </div>
                        <!-- Example Metrics Stub -->
                        <div class="bg-[#0f2b5c]/5 dark:bg-blue-900/10 px-4 py-2 font-bold text-xs uppercase text-gray-600 border-y border-[#0f2b5c]/30">
                        Example Metrics for Enterprise Goals
                    </div>
                    <div class="p-4 text-xs text-gray-600 dark:text-gray-400 italic">
                        (Metrics data to be populated)
                    </div>
                </div>

                <!-- Alignment Goals -->
                <div>
                        <div class="bg-[#0f2b5c]/10 dark:bg-blue-900/20 px-4 py-2 font-bold text-[#0f2b5c] dark:text-blue-300 border-b border-[#0f2b5c] flex items-center">
                        Alignment Goals
                    </div>
                        <div class="p-4 space-y-2">
                            <div v-for="g in objective.aligngoals" :key="g.aligngoals_id" class="flex gap-2 text-sm text-gray-800 dark:text-gray-200">
                            <span class="font-bold whitespace-nowrap">{{ g.aligngoals_id }}</span>
                            <span>{{ g.description }}</span>
                            </div>
                            <div v-if="!objective.aligngoals?.length" class="text-gray-400 italic text-sm">No Alignment Goals mapped.</div>
                    </div>
                    <!-- Example Metrics Stub -->
                        <div class="bg-[#0f2b5c]/5 dark:bg-blue-900/10 px-4 py-2 font-bold text-xs uppercase text-gray-600 border-y border-[#0f2b5c]/30">
                        Example Metrics for Alignment Goals
                    </div>
                    <div class="p-4 text-xs text-gray-600 dark:text-gray-400 italic">
                        (Metrics data to be populated)
                    </div>
                </div>
            </div>
        </div>

        <!-- Overview (Spare) -->
        <div v-else-if="activeTab === 'overview'" class="p-6 bg-white dark:bg-[#1a1a1a] rounded-xl border border-gray-200 shadow-sm">
            <h3 class="font-bold text-lg mb-4">Objective Details</h3>
        </div>
        
    </div>
</template>
