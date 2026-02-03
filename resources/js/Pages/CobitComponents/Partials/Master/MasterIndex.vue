<script setup>
import { ref } from 'vue';
import EnterpriseGoals from './EnterpriseGoals.vue';
import AlignmentGoals from './AlignmentGoals.vue';
import Roles from './Roles.vue';
import CapabilityLevels from './CapabilityLevels.vue';

const props = defineProps({
    masterEnterGoals: Array,
    masterAlignGoals: Array,
    masterRoles: Array,
});

const activeTab = ref('eg'); // 'eg' | 'ag' | 'roles' | 'cap'

const tabs = [
    { id: 'eg', label: 'Enterprise Goals' },
    { id: 'ag', label: 'Alignment Goals' },
    { id: 'roles', label: 'Roles' },
    { id: 'cap', label: 'Capability Levels' },
];
</script>

<template>
    <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl border border-gray-200/80 dark:border-white/5 shadow-sm overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 bg-[#0f2b5c] text-white flex justify-between items-center">
             <h2 class="font-bold text-lg">Data Master</h2>
             <!-- Tabs -->
              <div class="flex space-x-1">
                <button 
                    v-for="tab in tabs" 
                    :key="tab.id"
                    @click="activeTab = tab.id"
                    class="px-4 py-1.5 rounded-lg text-sm font-medium transition-colors"
                    :class="activeTab === tab.id 
                        ? 'bg-white text-[#0f2b5c] shadow' 
                        : 'text-white/70 hover:bg-white/10 hover:text-white'"
                >
                    {{ tab.label }}
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="p-6">
            <transition mode="out-in" enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 translate-y-1" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-1">
                
                <EnterpriseGoals 
                    v-if="activeTab === 'eg'" 
                    :goals="masterEnterGoals" 
                />

                <AlignmentGoals 
                    v-else-if="activeTab === 'ag'"
                    :goals="masterAlignGoals"
                />

                <Roles 
                    v-else-if="activeTab === 'roles'"
                    :roles="masterRoles"
                />

                <CapabilityLevels 
                    v-else-if="activeTab === 'cap'" 
                />

            </transition>
        </div>
    </div>
</template>
