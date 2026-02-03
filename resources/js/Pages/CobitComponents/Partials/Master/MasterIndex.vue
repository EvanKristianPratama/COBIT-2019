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

const activeTab = ref('eg');
const tabs = [
    { id: 'eg', label: 'Enterprise Goals' },
    { id: 'ag', label: 'Alignment Goals' },
    { id: 'roles', label: 'Roles' },
    { id: 'cap', label: 'Capability Levels' },
];
</script>

<template>
    <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl border border-gray-200/80 dark:border-white/5 shadow-sm overflow-hidden">
        <!-- Underline Tabs -->
        <div class="flex border-b border-gray-200 dark:border-white/10">
            <button 
                v-for="tab in tabs" 
                :key="tab.id"
                @click="activeTab = tab.id"
                class="px-6 py-3 text-sm font-medium transition-all relative"
                :class="activeTab === tab.id 
                    ? 'text-slate-900 dark:text-white' 
                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'"
            >
                {{ tab.label }}
                <span 
                    v-if="activeTab === tab.id"
                    class="absolute bottom-0 left-0 right-0 h-0.5 bg-slate-900 dark:bg-white"
                ></span>
            </button>
        </div>

        <!-- Content -->
        <div class="p-4">
            <transition 
                mode="out-in" 
                enter-active-class="transition duration-150 ease-out" 
                enter-from-class="opacity-0" 
                enter-to-class="opacity-100" 
                leave-active-class="transition duration-100 ease-in" 
                leave-from-class="opacity-100" 
                leave-to-class="opacity-0"
            >
                <EnterpriseGoals v-if="activeTab === 'eg'" :goals="masterEnterGoals" key="eg" />
                <AlignmentGoals v-else-if="activeTab === 'ag'" :goals="masterAlignGoals" key="ag" />
                <Roles v-else-if="activeTab === 'roles'" :roles="masterRoles" key="roles" />
                <CapabilityLevels v-else-if="activeTab === 'cap'" key="cap" />
            </transition>
        </div>
    </div>
</template>
