<script setup>
defineProps({
    policies: Array,
});
</script>

<template>
    <div class="space-y-4">
        <div class="bg-[#0f2b5c] text-white px-4 py-2 font-bold text-lg rounded-t-lg">
            E. Component: Policies and Procedures
        </div>

        <div class="overflow-hidden shadow-lg rounded-b-lg border border-[#0f2b5c] dark:border-blue-900">
            <table class="w-full text-sm text-left border-collapse">
                <thead class="bg-blue-100 dark:bg-blue-900/40 text-[#0f2b5c] dark:text-blue-200">
                    <tr>
                        <th class="px-4 py-2 border-r border-b border-[#0f2b5c] dark:border-blue-900 w-1/4">Relevant Policy</th>
                        <th class="px-4 py-2 border-r border-b border-[#0f2b5c] dark:border-blue-900 w-2/4">Policy Description</th>
                        <th class="px-4 py-2 border-r border-b border-[#0f2b5c] dark:border-blue-900 w-1/8">Related Guidance</th>
                        <th class="px-4 py-2 border-b border-[#0f2b5c] dark:border-blue-900 w-1/8">Detailed Reference</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-[#1a1a1a]">
                    <tr v-for="policy in policies" :key="policy.id" class="border-b last:border-b-0 border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3 border-r border-gray-200 dark:border-white/10 font-bold text-gray-900 dark:text-white align-top">
                            {{ policy.policy_name }}
                        </td>
                        <td class="px-4 py-3 border-r border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 align-top text-justify">
                            {{ policy.description }}
                        </td>
                         <td class="px-4 py-3 border-r border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 align-top">
                            <div v-for="guidance in policy.guidances" :key="guidance.id">
                                {{ guidance.guidance }}
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300 align-top">
                           <div v-for="guidance in policy.guidances" :key="guidance.id">
                                {{ guidance.pivot.component || '' }}
                             </div>
                        </td>
                    </tr>
                     <tr v-if="!policies || !policies.length">
                        <td colspan="4" class="p-4 text-center italic text-gray-500">No policies mapped.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
