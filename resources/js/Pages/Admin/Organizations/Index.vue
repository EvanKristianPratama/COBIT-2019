<script setup>
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import {
    PlusIcon,
    ExclamationTriangleIcon,
    BuildingOffice2Icon,
    CheckCircleIcon,
    XCircleIcon,
    UsersIcon,
    MagnifyingGlassIcon,
    PencilSquareIcon,
    TrashIcon
} from '@heroicons/vue/24/outline';

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
                    <PlusIcon class="w-5 h-5" />
                    Tambah Organisasi
                </Link>
            </div>
        </div>

        <!-- Demo Notice -->
        <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-xl">
            <div class="flex items-start gap-3">
                <ExclamationTriangleIcon class="w-5 h-5 text-amber-500 mt-0.5 flex-shrink-0" />
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
                        <BuildingOffice2Icon class="w-5 h-5 text-blue-600 dark:text-blue-400" />
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
                        <CheckCircleIcon class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
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
                        <XCircleIcon class="w-5 h-5 text-gray-600 dark:text-gray-400" />
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
                        <UsersIcon class="w-5 h-5 text-purple-600 dark:text-purple-400" />
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
                        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
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
                                        class="p-2 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors"
                                        title="Edit"
                                    >
                                        <PencilSquareIcon class="w-5 h-5" />
                                    </Link>
                                    <button
                                        @click="confirmDelete(org)"
                                        class="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors"
                                        title="Hapus"
                                    >
                                        <TrashIcon class="w-5 h-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredOrganizations.length === 0">
                            <td colspan="6" class="py-12 text-center">
                                <BuildingOffice2Icon class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-4" />
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
