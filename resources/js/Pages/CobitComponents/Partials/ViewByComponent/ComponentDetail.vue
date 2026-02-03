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

                <!-- Overview -->
                <div v-else-if="type === 'overview'">
                    <div class="mb-4">
                        <h4 class="font-bold text-gray-900 dark:text-gray-100 mb-1">Description</h4>
                        <p class="text-gray-700 dark:text-gray-300 leading-relaxed">{{ item.description || 'No description available.' }}</p>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-gray-100 mb-1">Purpose</h4>
                        <p class="text-gray-700 dark:text-gray-300 leading-relaxed">{{ item.purpose || 'No purpose statement available.' }}</p>
                    </div>
                </div>
                
                <!-- Goals --> <!-- Wait, Goals Cascade logic needs to be verified against data structure -->
                <!-- controller returns entergoals and aligngoals array -->
                 <div v-else-if="type === 'goals'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <h4 class="font-bold text-[#0f2b5c] border-b border-[#0f2b5c] pb-1">Enterprise Goals</h4>
                        <ul v-if="item.entergoals?.length" class="space-y-1 list-disc list-inside text-sm text-gray-700 dark:text-gray-300">
                            <li v-for="g in item.entergoals" :key="g.entergoals_id">{{ g.description }}</li>
                        </ul>
                         <p v-else class="text-sm italic text-gray-400">No Enterprise Goals mapped.</p>
                    </div>
                     <div class="space-y-2">
                        <h4 class="font-bold text-[#0f2b5c] border-b border-[#0f2b5c] pb-1">Alignment Goals</h4>
                        <ul v-if="item.aligngoals?.length" class="space-y-1 list-disc list-inside text-sm text-gray-700 dark:text-gray-300">
                             <li v-for="g in item.aligngoals" :key="g.aligngoals_id">{{ g.description }}</li>
                        </ul>
                        <p v-else class="text-sm italic text-gray-400">No Alignment Goals mapped.</p>
                    </div>
                 </div>

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
