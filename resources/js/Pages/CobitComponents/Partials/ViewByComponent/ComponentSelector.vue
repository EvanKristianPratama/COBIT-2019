<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import ComponentDetail from './ComponentDetail.vue';

const props = defineProps({
    selectedComponent: String,
    componentData: Object, // Collection/Array from backend
    masterRoles: Array,
});
// ... 
// (Wait I cannot target split blocks without multi_replace. Let's do multi_replace if lines are far apart inside the file, or just one replace if I can target the whole block. 
// Props definition is at line 6. ComponentDetail usage is at line 73. I should use multi_replace or two replaces. I'll use multi_replace.)

const components = [
    { id: 'overview', label: 'Overview' },
    { id: 'practices', label: 'A. Component: Process (Practices)' },
    { id: 'organizational', label: 'B. Component: Organizational Structures' },
    { id: 'infoflows', label: 'C. Component: Information Flows and Items' },
    { id: 'skills', label: 'D. Component: People, Skills and Competencies' },
    { id: 'policies', label: 'E. Component: Policies and Procedures' },
    { id: 'culture', label: 'F. Component: Culture, Ethics and Behavior' },
    { id: 'services', label: 'G. Component: Services, Infrastructure and Applications' },
];

const selected = ref(props.selectedComponent || '');

function onSelectChange() {
    if (selected.value) {
        router.get(`/objectives/component/${selected.value}`, {}, {
            preserveState: true,
            preserveScroll: true,
            only: ['componentData', 'selectedComponent', 'initialTab']
        });
    }
}
</script>

<template>
    <div class="space-y-6">
        <!-- Selector Card -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl p-6 border border-gray-200/80 dark:border-white/5 shadow-sm flex items-center gap-4">
            <div class="flex-grow max-w-xl">
                <label for="component-select" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Select Component Type
                </label>
                <select 
                    id="component-select"
                    v-model="selected"
                    @change="onSelectChange"
                    class="w-full rounded-xl border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white focus:border-[#0f2b5c] focus:ring-[#0f2b5c] shadow-sm"
                >
                    <option value="" disabled>-- Choose a component to view --</option>
                    <option v-for="c in components" :key="c.id" :value="c.id">
                        {{ c.label }}
                    </option>
                </select>
            </div>
            <div class="hidden md:block text-sm text-gray-500 pt-6">
                <span v-if="selected">Viewing: <strong>{{ components.find(c => c.id === selected)?.label }}</strong></span>
                <span v-else>Please select a component from the list.</span>
            </div>
        </div>

        <!-- Detail View -->
         <transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-2"
            mode="out-in"
        >
            <ComponentDetail 
                v-if="selected && componentData" 
                :data="componentData" 
                :type="selected"
                :masterRoles="masterRoles"
                :key="selected"
            />
             <div v-else-if="!selected" class="bg-white dark:bg-[#1a1a1a] rounded-2xl p-12 text-center border border-gray-200/80 dark:border-white/5 text-gray-400">
                <div class="inline-flex p-4 rounded-full bg-gray-50 dark:bg-white/5 mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                </div>
                <p>Select a component above to see aggregated data across all objectives.</p>
            </div>
        </transition>
    </div>
</template>
