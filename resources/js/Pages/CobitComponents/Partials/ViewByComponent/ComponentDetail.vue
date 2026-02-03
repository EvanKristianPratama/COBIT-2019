<script setup>
import { computed } from 'vue';

// Shared Components
import ProcessTable from '../../Components/ProcessTable.vue';
import OrgStructureTable from '../../Components/OrgStructureTable.vue';
import FlowsTable from '../../Components/FlowsTable.vue';
import SkillsTable from '../../Components/SkillsTable.vue';
import PoliciesTable from '../../Components/PoliciesTable.vue';
import CultureTable from '../../Components/CultureTable.vue';
import ServicesTable from '../../Components/ServicesTable.vue';

const props = defineProps({
    data: Array, // Array of Objectives with attached component data
    type: String, // 'practices', 'skills', etc.
    masterRoles: Array,
});
</script>

<template>
    <div class="space-y-8">
        <!-- Iterate over objectives -->
        <div v-for="item in data" :key="item.objective_id" class="bg-white dark:bg-[#1a1a1a] rounded-2xl border border-gray-200/80 dark:border-white/5 overflow-hidden shadow-sm">
            <!-- Objective Header -->
            <div class="bg-gray-50 dark:bg-white/5 px-6 py-4 border-b border-gray-200 dark:border-white/10 flex justify-between items-center">
                <div>
                    <span class="font-bold text-[#0f2b5c] dark:text-emerald-400 mr-2">{{ item.objective_id }}</span>
                    <span class="text-gray-700 dark:text-gray-300 font-medium">{{ item.objective }}</span>
                </div>
            </div>

            <!-- Content Body -->
            <div class="p-6">
                <!-- A. Practices -->
                <ProcessTable v-if="type === 'practices'" :practices="item.practices" />

                <!-- B. Organizational Structures -->
                <OrgStructureTable 
                    v-else-if="type === 'organizational'" 
                    :practices="item.practices" 
                    :masterRoles="masterRoles" 
                />

                <!-- C. Information Flows -->
                <FlowsTable v-else-if="type === 'infoflows'" :practices="item.practices" />

                <!-- D. Skills -->
                <SkillsTable v-else-if="type === 'skills'" :skills="item.skills" />

                <!-- E. Policies -->
                <PoliciesTable v-else-if="type === 'policies'" :policies="item.policies" />

                <!-- F. Culture -->
                <CultureTable v-else-if="type === 'culture'" :culture="item.culture" />

                <!-- G. Services -->
                <ServicesTable v-else-if="type === 'services'" :services="item.s_i_a" />

                <!-- Fallback/Overview -->
                <div v-else class="text-sm text-gray-600 dark:text-gray-400">
                    <div v-if="item[type]?.length">
                         <ul class="space-y-2">
                            <li v-for="(subItem, idx) in item[type]" :key="idx" class="p-3 bg-gray-50 dark:bg-white/5 rounded-lg">
                                {{ subItem.description || subItem.name || JSON.stringify(subItem) }}
                            </li>
                         </ul>
                    </div>
                    <div v-else class="text-gray-400 italic">No data available for {{ type }}.</div>
                </div>
            </div>
        </div>
    </div>
</template>
