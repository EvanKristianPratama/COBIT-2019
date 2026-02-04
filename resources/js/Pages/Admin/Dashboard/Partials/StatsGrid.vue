<script setup>
defineProps({
    stats: Object
});
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Users -->
        <div class="bg-white dark:bg-[#1a1a1a] p-6 rounded-xl border border-gray-200/80 dark:border-white/5 shadow-sm">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Users</h3>
            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.total_users }}</p>
        </div>

        <!-- Pending Approvals -->
        <div class="bg-white dark:bg-[#1a1a1a] p-6 rounded-xl border border-gray-200/80 dark:border-white/5 shadow-sm">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending Approval</h3>
            <p class="mt-2 text-3xl font-bold text-amber-600">{{ stats.pending_approval }}</p>
        </div>

        <!-- Total Roles -->
        <div class="bg-white dark:bg-[#1a1a1a] p-6 rounded-xl border border-gray-200/80 dark:border-white/5 shadow-sm">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Roles</h3>
            <p class="mt-2 text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ stats.total_roles }}</p>
        </div>
    </div>
</template>
