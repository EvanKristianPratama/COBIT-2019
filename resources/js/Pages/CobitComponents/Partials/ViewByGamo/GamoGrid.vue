<script setup>
import { computed } from 'vue';

const props = defineProps({
    objectives: Array,
});

const DOMAINS = {
    'EDM': { label: 'Evaluate, Direct and Monitor', color: 'bg-blue-600', text: 'text-blue-600', border: 'border-blue-200' },
    'APO': { label: 'Align, Plan and Organize', color: 'bg-teal-600', text: 'text-teal-600', border: 'border-teal-200' },
    'BAI': { label: 'Build, Acquire and Implement', color: 'bg-emerald-600', text: 'text-emerald-600', border: 'border-emerald-200' },
    'DSS': { label: 'Deliver, Service and Support', color: 'bg-indigo-600', text: 'text-indigo-600', border: 'border-indigo-200' },
    'MEA': { label: 'Monitor, Evaluate and Assess', color: 'bg-purple-600', text: 'text-purple-600', border: 'border-purple-200' },
};

const groupedObjectives = computed(() => {
    const groups = { EDM: [], APO: [], BAI: [], DSS: [], MEA: [] };
    
    props.objectives?.forEach(obj => {
        const prefix = obj.objective_id.substring(0, 3).toUpperCase();
        if (groups[prefix]) {
            groups[prefix].push(obj);
        } else {
            // Fallback for unknown prefixes
            if(!groups['OTHER']) groups['OTHER'] = [];
            groups['OTHER'].push(obj);
        }
    });

    return groups;
});
</script>

<template>
    <div class="space-y-8">
        <div v-for="(objs, prefix) in groupedObjectives" :key="prefix">
            <template v-if="objs.length > 0">
                <!-- Domain Header -->
                <div class="flex items-center mb-4">
                     <span class="text-2xl font-bold mr-3" :class="DOMAINS[prefix]?.text || 'text-gray-600'">
                        {{ prefix }}
                    </span>
                    <span class="h-px flex-grow bg-gray-200 dark:bg-white/10"></span>
                    <span class="ml-3 text-sm font-medium text-gray-500 uppercase tracking-wider">
                         {{ DOMAINS[prefix]?.label || 'Other Objectives' }}
                    </span>
                </div>

                <!-- Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <a 
                        v-for="obj in objs" 
                        :key="obj.objective_id"
                        :href="`/objectives/${obj.objective_id}`" 
                        class="group block bg-white dark:bg-[#1a1a1a] rounded-xl border border-gray-200/80 dark:border-white/5 hover:border-[color] hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 overflow-hidden relative"
                    >
                         <!-- Top Accent Line -->
                        <div class="absolute top-0 left-0 w-full h-1" :class="DOMAINS[prefix]?.color || 'bg-gray-400'"></div>
                        
                        <div class="p-5">
                            <div class="flex justify-between items-start mb-3">
                                <span class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-[#0f2b5c] dark:group-hover:text-emerald-400 transition-colors">
                                    {{ obj.objective_id }}
                                </span>
                                <!-- Optional: Status Icon or Badge here -->
                            </div>
                            
                            <h4 class="text-sm font-medium text-gray-600 dark:text-gray-300 line-clamp-2 min-h-[40px]">
                                {{ obj.objective || obj.objective_description }}
                            </h4>
                        </div>
                        
                        <!-- Hover Effect Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-br from-white/0 to-white/0 group-hover:from-white/5 group-hover:to-white/10 pointer-events-none transition-all duration-300"></div>
                    </a>
                </div>
            </template>
        </div>
    </div>
</template>
