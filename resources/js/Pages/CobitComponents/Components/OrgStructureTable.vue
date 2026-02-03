<script setup>
import { computed } from 'vue';

const props = defineProps({
    practices: Array,
    masterRoles: Array,
});

const getRaci = (practice, roleId) => {
    const role = practice.roles.find(r => r.role_id === roleId);
    return role ? role.pivot.r_a : '';
};
</script>

<template>
    <div class="space-y-4">
        <div class="bg-[#0f2b5c] text-white px-4 py-2 font-bold text-lg rounded-t-lg">
            B. Component: Organizational Structures
        </div>
        
        <div class="overflow-x-auto shadow-lg rounded-b-lg border border-gray-200 dark:border-white/10">
            <table class="w-full text-sm text-center border-collapse">
                <thead>
                    <tr>
                        <th class="border border-gray-300 dark:border-white/20 bg-gray-50 dark:bg-white/5 text-left px-4 py-3 font-bold text-gray-900 dark:text-white min-w-[300px]">
                            Key Governance Practice
                        </th>
                        <th 
                            v-for="role in masterRoles" 
                            :key="role.role_id"
                            class="border border-gray-300 dark:border-white/20 bg-blue-100 dark:bg-blue-900/40 px-2 py-3 font-semibold text-[#0f2b5c] dark:text-blue-200 w-12 vertical-text"
                            :title="role.role_name"
                        >
                            <div class="transform -rotate-90 whitespace-nowrap h-32 w-8 mx-auto flex items-center justify-start text-xs uppercase tracking-wider">
                                {{ role.role_name }}
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-[#1a1a1a]">
                    <template v-if="practices && practices.length">
                        <tr v-for="practice in practices" :key="practice.practice_id" class="hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="border border-gray-300 dark:border-white/20 px-4 py-2 text-left font-medium text-gray-800 dark:text-gray-200">
                                <span class="font-bold text-[#0f2b5c] dark:text-blue-400 block">{{ practice.practice_id }}</span>
                                {{ practice.practice_name }}
                            </td>
                            <td 
                                v-for="role in masterRoles" 
                                :key="role.role_id"
                                class="border border-gray-300 dark:border-white/20 px-2 py-2 font-bold text-gray-700 dark:text-gray-300"
                            >
                                {{ getRaci(practice, role.role_id) }}
                            </td>
                        </tr>
                    </template>
                    <tr v-else>
                        <td :colspan="(masterRoles?.length || 0) + 1" class="p-4 text-center italic text-gray-500">
                            No organizational structure data available.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Legend (Optional, but helpful) -->
         <div class="bg-gray-50 dark:bg-white/5 p-4 rounded-lg text-xs text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-white/10">
            <strong>Key:</strong> A = Accountable, R = Responsible, C = Consulted, I = Informed
        </div>
    </div>
</template>

<style scoped>
/* Optional tweak for vertical text alignment if Tailwind class stack isn't enough */
.vertical-text div {
    /* Origin center might be tricky for variable width, bottom-left is safer usually */
     transform-origin: center;
}
</style>
