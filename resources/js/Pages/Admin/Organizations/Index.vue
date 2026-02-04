<script setup>
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';

const props = defineProps({
    organizations: Array,
});

const page = usePage();
const searchQuery = ref('');
const statusFilter = ref('all');

// Modal state
const confirmModal = ref({
    show: false,
    title: '',
    message: '',
    type: 'danger',
    confirmText: 'Hapus',
    action: null,
    loading: false,
});

const filteredOrganizations = computed(() => {
    let result = props.organizations;

    // Filter by search
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(org => 
            org.name.toLowerCase().includes(query) ||
            org.code.toLowerCase().includes(query) ||
            org.email?.toLowerCase().includes(query)
        );
    }

    // Filter by status
    if (statusFilter.value !== 'all') {
        result = result.filter(org => org.status === statusFilter.value);
    }

    return result;
});

const stats = computed(() => ({
    total: props.organizations.length,
    active: props.organizations.filter(o => o.status === 'active').length,
    inactive: props.organizations.filter(o => o.status === 'inactive').length,
    totalUsers: props.organizations.reduce((sum, o) => sum + o.users_count, 0),
}));

const confirmDelete = (org) => {
    confirmModal.value = {
        show: true,
        title: 'Hapus Organisasi',
        message: `Apakah Anda yakin ingin menghapus organisasi "${org.name}"? Semua data terkait akan ikut terhapus.`,
        type: 'danger',
        confirmText: 'Hapus',
        action: () => deleteOrganization(org.id),
        loading: false,
    };
};

const deleteOrganization = (id) => {
    confirmModal.value.loading = true;
    router.delete(route('admin.organizations.destroy', id), {
        preserveScroll: true,
        onSuccess: () => {
            confirmModal.value.show = false;
            confirmModal.value.loading = false;
        },
        onError: () => {
            confirmModal.value.loading = false;
        },
    });
};

const closeModal = () => {
    if (!confirmModal.value.loading) {
        confirmModal.value.show = false;
    }
};
</script>

<template>
    <AdminLayout title="Organization Management">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Organization Management</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kelola data organisasi dalam sistem (Demo Mode)</p>
                </div>
                <Link
                    :href="route('admin.organizations.create')"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white text-sm font-medium rounded-xl transition-all shadow-lg shadow-emerald-500/25"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Tambah Organisasi
                </Link>
            </div>
        </div>

        <!-- Demo Notice -->
        <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-xl">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                    <p class="text-sm font-medium text-amber-800 dark:text-amber-200">Demo Mode</p>
                    <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">Halaman ini menggunakan data dummy. Perubahan tidak akan tersimpan ke database.</p>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-[#1a1a1a] rounded-xl border border-gray-200/80 dark:border-white/5 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total Organisasi</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-[#1a1a1a] rounded-xl border border-gray-200/80 dark:border-white/5 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.active }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Aktif</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-[#1a1a1a] rounded-xl border border-gray-200/80 dark:border-white/5 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.inactive }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Tidak Aktif</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-[#1a1a1a] rounded-xl border border-gray-200/80 dark:border-white/5 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.totalUsers }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total User</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-xl border border-gray-200/80 dark:border-white/5 p-4 mb-6">
            <div class="flex flex-col sm:flex-row gap-4">
                <!-- Search -->
                <div class="flex-1">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari organisasi..."
                            class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500"
                        />
                    </div>
                </div>
                <!-- Status Filter -->
                <select
                    v-model="statusFilter"
                    class="px-4 py-2.5 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500"
                >
                    <option value="all">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Tidak Aktif</option>
                </select>
            </div>
        </div>

        <!-- Organizations Table -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-xl border border-gray-200/80 dark:border-white/5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-white/5">
                            <th class="text-left py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Organisasi</th>
                            <th class="text-left py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kode</th>
                            <th class="text-left py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kontak</th>
                            <th class="text-left py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Users</th>
                            <th class="text-left py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="text-right py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        <tr 
                            v-for="org in filteredOrganizations" 
                            :key="org.id"
                            class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors"
                        >
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold text-sm">
                                        {{ org.name.substring(0, 2).toUpperCase() }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ org.name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ org.address }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-xs font-mono rounded-lg">
                                    {{ org.code }}
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                <p class="text-sm text-gray-900 dark:text-white">{{ org.email }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ org.phone }}</p>
                            </td>
                            <td class="py-4 px-6">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ org.users_count }}</span>
                            </td>
                            <td class="py-4 px-6">
                                <span 
                                    :class="[
                                        'px-2.5 py-1 text-xs font-medium rounded-full',
                                        org.status === 'active' 
                                            ? 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400'
                                            : 'bg-gray-100 dark:bg-gray-500/20 text-gray-700 dark:text-gray-400'
                                    ]"
                                >
                                    {{ org.status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center justify-end gap-2">
                                    <Link
                                        :href="route('admin.organizations.edit', org.id)"
                                        class="p-2 text-gray-500 hover:text-emerald-600 dark:text-gray-400 dark:hover:text-emerald-400 hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors"
                                        title="Edit"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </Link>
                                    <button
                                        @click="confirmDelete(org)"
                                        class="p-2 text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors"
                                        title="Hapus"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredOrganizations.length === 0">
                            <td colspan="6" class="py-12 text-center">
                                <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <p class="text-gray-500 dark:text-gray-400">Tidak ada organisasi ditemukan</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
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
            @confirm="confirmModal.action"
            @cancel="closeModal"
        />
    </AdminLayout>
</template>
