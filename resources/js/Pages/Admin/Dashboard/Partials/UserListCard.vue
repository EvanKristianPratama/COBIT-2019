<script setup>
import { Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';

defineProps({
    title: String,
    users: Array,
    viewAllLink: String,
    emptyMessage: String
});

const getInitials = (name) => {
    if (!name) return 'U';
    return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
};

const getStatusBadge = (status) => {
    const badges = {
        pending: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        approved: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        rejected: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    };
    return badges[status] || badges.pending;
};

const getStatusLabel = (status) => {
    const labels = {
        pending: 'Pending',
        approved: 'Aktif',
        rejected: 'Ditolak'
    };
    return labels[status] || status;
};

const approve = (user) => {
    if (confirm(`Approve user ${user.name}?`)) {
        router.put(route('admin.users.approve', user.id));
    }
};
</script>

<template>
    <div class="bg-white dark:bg-[#1a1a1a] rounded-xl border border-gray-200/80 dark:border-white/5">
        <div class="p-5 border-b border-gray-200/80 dark:border-white/5 flex items-center justify-between">
            <h2 class="font-semibold text-gray-900 dark:text-white">{{ title }}</h2>
            <Link 
                v-if="viewAllLink"
                :href="viewAllLink"
                class="text-sm text-blue-600 dark:text-blue-400 hover:underline"
            >
                Lihat Semua
            </Link>
        </div>
        <div class="divide-y divide-gray-200/80 dark:divide-white/5">
            <div
                v-for="user in users"
                :key="user.id"
                class="p-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-white/5 transition-colors"
            >
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 font-medium text-sm">
                        {{ getInitials(user.name) }}
                    </div>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white text-sm">{{ user.name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-1 text-xs rounded-full" :class="getStatusBadge(user.approval_status)">
                        {{ getStatusLabel(user.approval_status) }}
                    </span>
                    
                    <button 
                        v-if="user.approval_status === 'pending'"
                        @click="approve(user)"
                        class="text-xs text-emerald-600 hover:text-emerald-700 font-medium"
                    >
                        Approve
                    </button>
                </div>
            </div>
            <div v-if="!users?.length" class="p-8 text-center text-gray-500 dark:text-gray-400">
                {{ emptyMessage || 'Tidak ada data' }}
            </div>
        </div>
    </div>
</template>
