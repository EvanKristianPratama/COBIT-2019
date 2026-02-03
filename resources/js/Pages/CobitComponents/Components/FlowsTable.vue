<script setup>
const props = defineProps({
    practices: Array,
});
</script>

<template>
    <div class="space-y-4">
        <div class="bg-[#0f2b5c] text-white px-4 py-2 font-bold text-lg rounded-t-lg">
            C. Component: Information Flows and Items
        </div>
        
        <div class="border border-gray-200 dark:border-white/10 shadow-lg rounded-b-lg overflow-hidden">
            <div class="grid grid-cols-12 bg-blue-100 dark:bg-blue-900/40 border-b border-[#0f2b5c] dark:border-blue-900 text-sm font-bold text-[#0f2b5c] dark:text-blue-200 text-center">
                <div class="col-span-3 py-3 border-r border-[#0f2b5c] dark:border-blue-900 flex items-center justify-center">Governance Practice</div>
                <div class="col-span-4 border-r border-[#0f2b5c] dark:border-blue-900">
                    <div class="border-b border-[#0f2b5c] dark:border-blue-900 py-1">Inputs</div>
                    <div class="grid grid-cols-2 h-full">
                        <div class="py-1 border-r border-[#0f2b5c]/50">From</div>
                        <div class="py-1">Description</div>
                    </div>
                </div>
                 <div class="col-span-5">
                    <div class="border-b border-[#0f2b5c] dark:border-blue-900 py-1">Outputs</div>
                    <div class="grid grid-cols-2 h-full">
                        <div class="py-1 border-r border-[#0f2b5c]/50">Description</div>
                        <div class="py-1">To</div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-[#1a1a1a]">
                <div v-for="practice in practices" :key="practice.practice_id" class="grid grid-cols-12 border-b last:border-b-0 border-gray-200 dark:border-white/10 text-sm">
                    <!-- Practice Column -->
                    <div class="col-span-3 p-4 border-r border-gray-200 dark:border-white/10 font-bold text-gray-900 dark:text-white">
                        <div class="mb-1">{{ practice.practice_id }}</div>
                         <div class="text-xs font-normal text-gray-600 dark:text-gray-400">{{ practice.practice_name }}</div>
                    </div>

                    <!-- Inputs -->
                    <div class="col-span-4 border-r border-gray-200 dark:border-white/10">
                        <div v-if="practice.infoflowinput && practice.infoflowinput.length">
                            <div v-for="(input, idx) in practice.infoflowinput" :key="idx" class="grid grid-cols-2 border-b last:border-b-0 border-gray-100 dark:border-white/5">
                                <div class="p-2 border-r border-gray-100 dark:border-white/5 text-gray-600 dark:text-gray-400 font-mono text-xs">{{ input.input_from }}</div>
                                <div class="p-2 text-gray-800 dark:text-gray-200">{{ input.description }}</div>
                            </div>
                        </div>
                        <div v-else class="p-4 text-center text-gray-400 italic text-xs">No Inputs</div>
                    </div>

                    <!-- Outputs -->
                    <div class="col-span-5">
                       <div v-if="practice.infoflowoutput && practice.infoflowoutput.length">
                            <div v-for="(output, idx) in practice.infoflowoutput" :key="idx" class="grid grid-cols-2 border-b last:border-b-0 border-gray-100 dark:border-white/5">
                                <div class="p-2 border-r border-gray-100 dark:border-white/5 text-gray-800 dark:text-gray-200">{{ output.description }}</div>
                                <div class="p-2 text-gray-600 dark:text-gray-400 font-mono text-xs">{{ output.output_to }}</div>
                            </div>
                        </div>
                        <div v-else class="p-4 text-center text-gray-400 italic text-xs">No Outputs</div>
                    </div>
                </div>
                 <div v-if="!practices.length" class="p-6 text-center text-gray-500 italic">No practices or flow data available.</div>
            </div>
        </div>
    </div>
</template>
