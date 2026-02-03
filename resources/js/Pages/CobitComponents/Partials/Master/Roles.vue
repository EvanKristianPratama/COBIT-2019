<script setup>
defineProps({
    roles: Array,
});
</script>

<template>
    <div>
        <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded-xl text-sm border border-blue-100 dark:border-blue-800">
             <i class="mr-2">ℹ️</i> Table view info: Roles define the responsibilities in the governance framework.
             <!-- TODO: Add click-to-view RACI details interactivity in future iteration -->
        </div>

        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-white/10">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-gray-900 dark:text-white uppercase font-semibold">
                    <tr>
                         <th class="px-6 py-3 border-b dark:border-white/10 w-24">ID</th>
                         <th class="px-6 py-3 border-b dark:border-white/10 w-64">Role</th>
                         <th class="px-6 py-3 border-b dark:border-white/10">Description</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                    <tr v-for="role in roles" :key="role.role_id" class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors cursor-pointer group">
                        <td class="px-6 py-3 font-medium text-gray-500 dark:text-gray-400 group-hover:text-[#0f2b5c] dark:group-hover:text-white">
                            {{ role.role_id }}
                        </td>
                         <td class="px-6 py-3 font-medium text-[#0f2b5c] dark:text-emerald-400">
                            {{ role.role }}
                        </td>
                        <td class="px-6 py-3 text-gray-600 dark:text-gray-300">
                             {{ role.description }}
                        </td>
                    </tr>
                    <tr v-if="!roles?.length">
                         <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                            No Roles found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
