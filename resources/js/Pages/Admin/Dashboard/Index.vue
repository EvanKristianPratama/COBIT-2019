<script setup>
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatsGrid from './Partials/StatsGrid.vue';
import QuickActions from './Partials/QuickActions.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import { Link, router, usePage } from '@inertiajs/vue3';


const props = defineProps({
    stats: Object,
    users: Object, // Paginated users
});

// Confirm modal state
const confirmModal = ref({
    show: false,
    title: '',
    message: '',
    type: 'warning',
    confirmText: 'Ya, Lanjutkan',
    loading: false,
    action: null,
});

const showConfirm = (options) => {
    confirmModal.value = {
        show: true,
        title: options.title || 'Konfirmasi',
        message: options.message || 'Apakah Anda yakin?',
        type: options.type || 'warning',
        confirmText: options.confirmText || 'Ya, Lanjutkan',
        loading: false,
        action: options.action,
    };
};

const handleConfirm = async () => {
    if (confirmModal.value.action) {
        confirmModal.value.loading = true;
        await confirmModal.value.action();
    }
    confirmModal.value.show = false;
    confirmModal.value.loading = false;
};

const closeConfirm = () => {
    confirmModal.value.show = false;
    confirmModal.value.loading = false;
};

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
    return badges[status] || 'bg-gray-100 text-gray-700';
};

const approve = (user) => {
    showConfirm({
        title: 'Setujui User',
        message: `Setujui akun "${user.name}"? User akan dapat mengakses sistem setelah disetujui.`,
        type: 'success',
        confirmText: 'Ya, Setujui',
        action: () => router.put(route('admin.users.approve', user.id)),
    });
};

const reject = (user) => {
    showConfirm({
        title: 'Tolak User',
        message: `Tolak akun "${user.name}"? User tidak akan dapat mengakses sistem.`,
        type: 'danger',
        confirmText: 'Ya, Tolak',
        action: () => router.put(route('admin.users.reject', user.id)),
    });
};

const toggleActivation = (user) => {
    const action = user.isActivated ? 'deactivate' : 'activate';
    const isDeactivate = user.isActivated;
    showConfirm({
        title: isDeactivate ? 'Nonaktifkan Akun' : 'Aktifkan Akun',
        message: isDeactivate 
            ? `Nonaktifkan akun "${user.name}"? User tidak akan dapat mengakses sistem.`
            : `Aktifkan kembali akun "${user.name}"? User akan dapat mengakses sistem.`,
        type: isDeactivate ? 'danger' : 'success',
        confirmText: isDeactivate ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan',
        action: () => router.put(route(`admin.users.${action}`, user.id)),
    });
};
</script>

<template>
    <AdminLayout title="Admin Dashboard">
        <div class="space-y-8">
            <!-- Header with Inline Quick Actions -->
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard Overview</h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">Manage users, roles, and system status.</p>
                </div>
                <QuickActions />
            </div>

            <!-- Stats -->
            <StatsGrid :stats="stats" />

            <!-- Consolidated User Management -->
            <div class="bg-white dark:bg-[#1a1a1a] rounded-xl border border-gray-200/80 dark:border-white/5 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-200/80 dark:border-white/5 flex justify-between items-center">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">User Management</h2>
                    <!-- Search could go here -->
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-white/5 text-xs uppercase text-gray-500 dark:text-gray-400 font-semibold tracking-wide">
                                <th class="px-6 py-4">User</th>
                                <th class="px-6 py-4">Role</th>
                                <th class="px-6 py-4">Approval Status</th>
                                <th class="px-6 py-4">Account Status</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                            <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">
                                            {{ getInitials(user.name) }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-white">{{ user.name }}</div>
                                            <div class="text-xs text-gray-500">{{ user.email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 capitalize">
                                        {{ user.role }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                     <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize" :class="getStatusBadge(user.approval_status)">
                                        {{ user.approval_status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span v-if="user.isActivated" class="text-emerald-600 text-xs font-medium flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-emerald-600"></span> Active
                                    </span>
                                    <span v-else class="text-red-600 text-xs font-medium flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-red-600"></span> Inactive
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <!-- Approval Actions -->
                                    <template v-if="user.approval_status === 'pending'">
                                        <button @click="approve(user)" class="text-emerald-600 hover:text-emerald-800 text-xs font-bold uppercase tracking-wider">Approve</button>
                                        <span class="text-gray-300">|</span>
                                        <button @click="reject(user)" class="text-red-600 hover:text-red-800 text-xs font-bold uppercase tracking-wider">Reject</button>
                                    </template>
                                    
                                    <template v-else>
                                        <!-- Edit / Manage Actions -->
                                        <!-- <button class="text-blue-600 hover:text-blue-800 text-xs font-medium">Edit</button> -->
                                        <button 
                                            @click="toggleActivation(user)" 
                                            class="text-xs font-bold uppercase tracking-wider"
                                            :class="user.isActivated ? 'text-amber-600 hover:text-amber-800' : 'text-emerald-600 hover:text-emerald-800'"
                                        >
                                            {{ user.isActivated ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </template>
                                </td>
                            </tr>
                            <tr v-if="users.data.length === 0">
                                <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                    No users found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="users.links.length > 3" class="px-6 py-4 border-t border-gray-200 dark:border-white/5 bg-gray-50 dark:bg-white/5 flex justify-center">
                    <div class="flex gap-1">
                        <Link 
                            v-for="(link, key) in users.links" 
                            :key="key" 
                            :href="link.url || '#'" 
                            v-html="link.label"
                            class="px-3 py-1 text-sm rounded-md"
                            :class="[
                                link.active ? 'bg-emerald-600 text-white' : 'bg-white dark:bg-[#1a1a1a] text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-white/10 hover:bg-gray-50',
                                !link.url ? 'opacity-50 pointer-events-none' : ''
                            ]"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirm Modal -->
        <ConfirmModal
            :show="confirmModal.show"
            :title="confirmModal.title"
            :message="confirmModal.message"
            :type="confirmModal.type"
            :confirm-text="confirmModal.confirmText"
            :loading="confirmModal.loading"
            @close="closeConfirm"
            @confirm="handleConfirm"
        />
    </AdminLayout>
</template>
