<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    user: Object,
});
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout :user="user" title="Admin Dashboard">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
             <div class="bg-white dark:bg-[#1a1a1a] rounded-xl p-5 border border-gray-200/80 dark:border-white/5 shadow-sm">
                <div class="text-sm text-gray-500">Total Users</div>
                <div class="text-2xl font-bold mt-1">1,234</div>
             </div>
             <div class="bg-white dark:bg-[#1a1a1a] rounded-xl p-5 border border-gray-200/80 dark:border-white/5 shadow-sm">
                <div class="text-sm text-gray-500">Pending Requests</div>
                <div class="text-2xl font-bold mt-1 text-amber-600">5</div>
             </div>
        </div>

        <div class="bg-white dark:bg-[#1a1a1a] border border-gray-200/80 dark:border-white/5 rounded-xl p-6">
            <h3 class="text-lg font-medium mb-4">Recent Activity</h3>
            <p class="text-gray-500 text-sm">No recent activity found.</p>
        </div>
    </AuthenticatedLayout>
</template>
