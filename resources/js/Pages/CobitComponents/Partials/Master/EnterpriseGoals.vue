<script setup>
defineProps({
    goals: Array,
});
</script>

<template>
    <div>
        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-white/10">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-gray-900 dark:text-white uppercase font-semibold">
                    <tr>
                        <th class="px-6 py-3 border-b dark:border-white/10 w-32">ID</th>
                        <th class="px-6 py-3 border-b dark:border-white/10">Description</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                    <tr v-for="goal in goals" :key="goal.entergoals_id" class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                        <td class="px-6 py-3 font-medium text-[#0f2b5c] dark:text-emerald-400">
                            {{ goal.entergoals_id }}
                        </td>
                        <td class="px-6 py-3 text-gray-600 dark:text-gray-300">
                             {{ goal.description }}
                        </td>
                    </tr>
                    <tr v-if="!goals?.length">
                         <td colspan="2" class="px-6 py-8 text-center text-gray-500">
                            No Enterprise Goals found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
