<script setup>
defineProps({
    practices: Array,
});
</script>

<template>
    <div class="space-y-12">
        <div v-for="practice in practices" :key="practice.id" class="bg-white dark:bg-[#1a1a1a] border-2 border-[#0f2b5c] dark:border-blue-900 rounded-lg overflow-hidden shadow-lg">
            
            <!-- Table Header: Title -->
            <div class="bg-[#0f2b5c] text-white px-4 py-2 font-bold text-lg">
                A. Component: Process
            </div>

            <!-- Row 1: Practice & Metrics -->
            <div class="grid grid-cols-1 lg:grid-cols-2 border-b-2 border-[#0f2b5c] dark:border-blue-900">
                <!-- Practice Column -->
                <div class="border-b lg:border-b-0 lg:border-r-2 border-[#0f2b5c] dark:border-blue-900 flex flex-col">
                    <div class="bg-red-50 dark:bg-red-900/20 px-4 py-1.5 font-bold text-[#8B0000] dark:text-red-300 border-b border-gray-300 dark:border-white/10 uppercase text-sm tracking-wider">
                        Governance Practice
                    </div>
                    <div class="p-4 flex-grow bg-white dark:bg-[#1a1a1a]">
                        <h3 class="font-bold text-gray-900 dark:text-white text-base mb-2">
                            {{ practice.practice_id }} {{ practice.practice_name }}
                        </h3>
                        <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                            {{ practice.description }}
                        </p>
                    </div>
                </div>

                <!-- Metrics Column -->
                <div class="flex flex-col">
                    <div class="bg-red-50 dark:bg-red-900/20 px-4 py-1.5 font-bold text-[#8B0000] dark:text-red-300 border-b border-gray-300 dark:border-white/10 uppercase text-sm tracking-wider">
                        Example Metrics
                    </div>
                    <div class="p-4 bg-white dark:bg-[#1a1a1a]">
                        <ul class="list-[lower-alpha] list-outside ml-4 space-y-1 text-sm text-gray-700 dark:text-gray-300">
                             <!-- Assuming metrics are joined or array. If string, just show. -->
                             <template v-if="practice.metrics && practice.metrics.length">
                                <li v-for="(metric, idx) in practice.metrics" :key="idx">
                                    {{ metric.metric || metric.description || metric }}
                                </li>
                             </template>
                             <li v-else class="text-gray-400 italic">No example metrics defined.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Row 2: Activities Header -->
            <div class="grid grid-cols-12 border-b border-[#0f2b5c] dark:border-blue-900 bg-blue-100 dark:bg-blue-900/40 font-bold text-[#0f2b5c] dark:text-blue-100 text-sm">
                <div class="col-span-10 px-4 py-2 border-r border-[#0f2b5c] dark:border-blue-900">
                    Activities
                </div>
                <div class="col-span-2 px-2 py-2 text-center flex items-center justify-center">
                    Capability Level
                </div>
            </div>

            <!-- Activities Rows -->
            <div class="bg-white dark:bg-[#1a1a1a]">
                 <div 
                    v-for="(activity, index) in practice.activities" 
                    :key="activity.id" 
                    class="grid grid-cols-12 border-b last:border-b-0 border-gray-200 dark:border-white/10 text-sm group hover:bg-gray-50 dark:hover:bg-white/5 transition-colors"
                >
                    <!-- Activity Text -->
                    <div class="col-span-10 px-4 py-3 border-r border-gray-200 dark:border-white/10 text-gray-800 dark:text-gray-200">
                        <span class="font-bold mr-2">{{ index + 1 }}.</span>
                        {{ activity.activity || activity.description }}
                    </div>
                    
                    <!-- Capability Level (Centered) -->
                    <div class="col-span-2 px-2 py-3 flex items-center justify-center font-bold text-[#0f2b5c] dark:text-blue-300 bg-gray-50 dark:bg-white/5">
                        {{ activity.capability_level || '' }}
                    </div>
                </div>
                 <div v-if="!practice.activities?.length" class="p-4 text-center text-gray-500 italic text-sm">
                    No activities mapped.
                </div>
            </div>

            <!-- Row 3: Related Guidance -->
            <!-- <div class="grid grid-cols-1 lg:grid-cols-2 border-t-2 border-[#0f2b5c] dark:border-blue-900 text-xs">
                <div class="border-r border-[#0f2b5c] dark:border-blue-900 p-2 bg-gray-100 dark:bg-white/5 font-bold">
                    Related Guidance
                </div>
                <div class="p-2 bg-gray-100 dark:bg-white/5 font-bold">
                    Detailed Reference
                </div>
            </div> -->
            <!-- Note: Related Guidance typically comes from a separate related table we might not have eager loaded yet.
                 Leaving placeholder if needed or omitting if empty. -->

        </div>
    </div>
</template>
