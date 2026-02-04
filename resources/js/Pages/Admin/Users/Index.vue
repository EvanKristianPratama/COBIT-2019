<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import { TransitionRoot, TransitionChild, Dialog, DialogPanel, DialogTitle } from '@headlessui/vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';

const props = defineProps({
    activatedUsers: Array,
    deactivatedUsers: Array,
    pendingUsers: Array,
});

const page = usePage();
const flash = computed(() => page.props.flash);

const searchTerm = ref('');
const editModal = ref(false);
const editingUser = ref(null);
const activeTab = ref('pending');

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

// Checklist states for bulk approval
const selectedPendingUsers = ref([]);
const selectAllPending = ref(false);

// Form for editing
const editForm = useForm({
    name: '',
    email: '',
    role: '',
    jabatan: '',
    organisasi: '',
});

const getInitials = (name) => {
    if (!name) return 'U';
    return name.split(' ').map(word => word[0]).join('').toUpperCase().slice(0, 2);
};

// Filtered users based on search
const filteredActivatedUsers = computed(() => {
    if (!searchTerm.value) return props.activatedUsers || [];
    const term = searchTerm.value.toLowerCase();
    return (props.activatedUsers || []).filter(u => 
        u.name.toLowerCase().includes(term) || 
        u.email.toLowerCase().includes(term) ||
        u.organisasi?.toLowerCase().includes(term)
    );
});

const filteredDeactivatedUsers = computed(() => {
    if (!searchTerm.value) return props.deactivatedUsers || [];
    const term = searchTerm.value.toLowerCase();
    return (props.deactivatedUsers || []).filter(u => 
        u.name.toLowerCase().includes(term) || 
        u.email.toLowerCase().includes(term) ||
        u.organisasi?.toLowerCase().includes(term)
    );
});

const filteredPendingUsers = computed(() => {
    if (!searchTerm.value) return props.pendingUsers || [];
    const term = searchTerm.value.toLowerCase();
    return (props.pendingUsers || []).filter(u => 
        u.name.toLowerCase().includes(term) || 
        u.email.toLowerCase().includes(term) ||
        u.organisasi?.toLowerCase().includes(term)
    );
});

// Toggle select all pending users
const toggleSelectAllPending = () => {
    if (selectAllPending.value) {
        selectedPendingUsers.value = filteredPendingUsers.value.map(u => u.id);
    } else {
        selectedPendingUsers.value = [];
    }
};

watch(selectAllPending, toggleSelectAllPending);

watch(selectedPendingUsers, (newVal) => {
    selectAllPending.value = newVal.length === filteredPendingUsers.value.length && filteredPendingUsers.value.length > 0;
}, { deep: true });

// Edit user
function openEditModal(user) {
    editingUser.value = user;
    editForm.name = user.name;
    editForm.email = user.email;
    editForm.role = user.role;
    editForm.jabatan = user.jabatan || '';
    editForm.organisasi = user.organisasi || '';
    editModal.value = true;
}

function closeEditModal() {
    editModal.value = false;
    editingUser.value = null;
    editForm.reset();
}

function submitEdit() {
    editForm.put(route('admin.users.update', editingUser.value.id), {
        onSuccess: () => closeEditModal(),
    });
}

// Actions
function deactivateUser(user) {
    showConfirm({
        title: 'Nonaktifkan Akun',
        message: `Apakah Anda yakin ingin menonaktifkan akun "${user.name}"? User tidak akan dapat mengakses sistem.`,
        type: 'danger',
        confirmText: 'Ya, Nonaktifkan',
        action: () => router.put(route('admin.users.deactivate', user.id)),
    });
}

function activateUser(user) {
    showConfirm({
        title: 'Aktifkan Akun',
        message: `Aktifkan kembali akun "${user.name}"? User akan dapat mengakses sistem kembali.`,
        type: 'success',
        confirmText: 'Ya, Aktifkan',
        action: () => router.put(route('admin.users.activate', user.id)),
    });
}

function approveUser(user) {
    showConfirm({
        title: 'Setujui User',
        message: `Setujui akun "${user.name}"? User akan dapat mengakses sistem setelah disetujui.`,
        type: 'success',
        confirmText: 'Ya, Setujui',
        action: () => router.put(route('admin.users.approve', user.id)),
    });
}

function rejectUser(user) {
    showConfirm({
        title: 'Tolak User',
        message: `Tolak akun "${user.name}"? User tidak akan dapat mengakses sistem.`,
        type: 'danger',
        confirmText: 'Ya, Tolak',
        action: () => router.put(route('admin.users.reject', user.id)),
    });
}

// Bulk approve selected users
function bulkApproveUsers() {
    if (selectedPendingUsers.value.length === 0) {
        showConfirm({
            title: 'Tidak Ada User Dipilih',
            message: 'Pilih minimal satu user untuk disetujui.',
            type: 'info',
            confirmText: 'OK',
            action: () => {},
        });
        return;
    }
    showConfirm({
        title: 'Setujui User Terpilih',
        message: `Setujui ${selectedPendingUsers.value.length} pengguna yang dipilih? Semua user akan dapat mengakses sistem.`,
        type: 'success',
        confirmText: 'Ya, Setujui Semua',
        action: () => {
            router.post(route('admin.users.bulk-approve'), {
                user_ids: selectedPendingUsers.value
            }, {
                onSuccess: () => {
                    selectedPendingUsers.value = [];
                    selectAllPending.value = false;
                }
            });
        },
    });
}

// Bulk reject selected users
function bulkRejectUsers() {
    if (selectedPendingUsers.value.length === 0) {
        showConfirm({
            title: 'Tidak Ada User Dipilih',
            message: 'Pilih minimal satu user untuk ditolak.',
            type: 'info',
            confirmText: 'OK',
            action: () => {},
        });
        return;
    }
    showConfirm({
        title: 'Tolak User Terpilih',
        message: `Tolak ${selectedPendingUsers.value.length} pengguna yang dipilih? Semua user tidak akan dapat mengakses sistem.`,
        type: 'danger',
        confirmText: 'Ya, Tolak Semua',
        action: () => {
            router.post(route('admin.users.bulk-reject'), {
                user_ids: selectedPendingUsers.value
            }, {
                onSuccess: () => {
                    selectedPendingUsers.value = [];
                    selectAllPending.value = false;
                }
            });
        },
    });
}

function getRoleBadgeClass(role) {
    switch (role) {
        case 'admin': return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400';
        case 'pic': return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
        default: return 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300';
    }
}

const tabs = [
    { id: 'pending', label: 'Menunggu Persetujuan', icon: 'clock' },
    { id: 'active', label: 'Akun Aktif', icon: 'check-circle' },
    { id: 'inactive', label: 'Akun Nonaktif', icon: 'x-circle' },
];
</script>

<template>
    <AdminLayout title="User Management">
        <Head title="User Management" />

        <!-- Flash Messages -->
        <div v-if="flash?.success" class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 rounded-xl flex items-center">
            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ flash.success }}
        </div>
        <div v-if="flash?.error" class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-xl flex items-center">
            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ flash.error }}
        </div>

        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">User Management</h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">Kelola akun dan persetujuan pengguna sistem</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('admin.users.create')"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white text-sm font-medium rounded-xl transition-all shadow-lg shadow-emerald-500/25"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Tambah User
                    </Link>
                    <div class="w-full md:w-64">
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input 
                                v-model="searchTerm"
                                type="text" 
                                placeholder="Cari user..." 
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-white/5 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
            <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl p-5 border border-gray-200/80 dark:border-white/5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Menunggu Approval</p>
                        <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ (pendingUsers || []).length }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                        <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl p-5 border border-gray-200/80 dark:border-white/5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">User Aktif</p>
                        <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ (activatedUsers || []).length }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                        <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl p-5 border border-gray-200/80 dark:border-white/5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">User Nonaktif</p>
                        <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">{{ (deactivatedUsers || []).length }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl border border-gray-200/80 dark:border-white/5 shadow-sm overflow-hidden">
            <!-- Tab Headers -->
            <div class="border-b border-gray-200 dark:border-white/5">
                <nav class="flex -mb-px overflow-x-auto">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        :class="[
                            'flex-1 sm:flex-none px-6 py-4 text-sm font-medium border-b-2 transition-colors flex items-center justify-center gap-2 whitespace-nowrap',
                            activeTab === tab.id 
                                ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400' 
                                : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'
                        ]"
                    >
                        <svg v-if="tab.icon === 'clock'" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <svg v-else-if="tab.icon === 'check-circle'" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ tab.label }}
                        <span 
                            v-if="tab.id === 'pending' && (pendingUsers || []).length > 0"
                            class="ml-1 px-2 py-0.5 text-xs rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400"
                        >
                            {{ (pendingUsers || []).length }}
                        </span>
                    </button>
                </nav>
            </div>

            <!-- Tab Content: Pending -->
            <div v-show="activeTab === 'pending'" class="p-6">
                <!-- Bulk Actions -->
                <div v-if="selectedPendingUsers.length > 0" class="mb-4 p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <p class="text-sm text-emerald-700 dark:text-emerald-400">
                        <span class="font-semibold">{{ selectedPendingUsers.length }}</span> user dipilih
                    </p>
                    <div class="flex gap-2">
                        <button @click="bulkApproveUsers" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Setujui Semua
                        </button>
                        <button @click="bulkRejectUsers" class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Tolak Semua
                        </button>
                    </div>
                </div>

                <div v-if="filteredPendingUsers.length === 0" class="py-16 text-center">
                    <div class="w-16 h-16 mx-auto rounded-2xl bg-gray-100 dark:bg-white/5 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400">Tidak ada user yang menunggu persetujuan</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-white/5">
                                <th class="px-4 py-3 text-left">
                                    <input 
                                        type="checkbox" 
                                        v-model="selectAllPending"
                                        class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-emerald-600 focus:ring-emerald-500 dark:bg-gray-700"
                                    >
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Organisasi</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jabatan</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            <tr v-for="user in filteredPendingUsers" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                <td class="px-4 py-4">
                                    <input 
                                        type="checkbox" 
                                        :value="user.id"
                                        v-model="selectedPendingUsers"
                                        class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-emerald-600 focus:ring-emerald-500 dark:bg-gray-700"
                                    >
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white font-semibold text-sm">
                                            {{ getInitials(user.name) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">{{ user.name }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">ID: {{ user.id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">{{ user.email }}</td>
                                <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">{{ user.organisasi || '-' }}</td>
                                <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">{{ user.jabatan || '-' }}</td>
                                <td class="px-4 py-4">
                                    <div class="flex justify-center gap-2">
                                        <button @click="approveUser(user)" class="p-2 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 rounded-lg transition-colors" title="Setujui">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                        <button @click="rejectUser(user)" class="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors" title="Tolak">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab Content: Active Users -->
            <div v-show="activeTab === 'active'" class="p-6">
                <div v-if="filteredActivatedUsers.length === 0" class="py-16 text-center">
                    <div class="w-16 h-16 mx-auto rounded-2xl bg-gray-100 dark:bg-white/5 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400">Belum ada user aktif</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-white/5">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Organisasi</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jabatan</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            <tr v-for="user in filteredActivatedUsers" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-white font-semibold text-sm">
                                            {{ getInitials(user.name) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">{{ user.name }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">ID: {{ user.id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">{{ user.email }}</td>
                                <td class="px-4 py-4">
                                    <span :class="getRoleBadgeClass(user.role)" class="px-2.5 py-1 text-xs font-medium rounded-lg">
                                        {{ user.role ? user.role.charAt(0).toUpperCase() + user.role.slice(1) : 'User' }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">{{ user.organisasi || '-' }}</td>
                                <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">{{ user.jabatan || '-' }}</td>
                                <td class="px-4 py-4">
                                    <div class="flex justify-center gap-2">
                                        <button @click="openEditModal(user)" class="p-2 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors" title="Edit">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button @click="deactivateUser(user)" class="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors" title="Nonaktifkan">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab Content: Inactive Users -->
            <div v-show="activeTab === 'inactive'" class="p-6">
                <div v-if="filteredDeactivatedUsers.length === 0" class="py-16 text-center">
                    <div class="w-16 h-16 mx-auto rounded-2xl bg-gray-100 dark:bg-white/5 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400">Tidak ada user nonaktif</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-white/5">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Organisasi</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jabatan</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            <tr v-for="user in filteredDeactivatedUsers" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gray-300 dark:bg-gray-600 flex items-center justify-center text-gray-600 dark:text-gray-300 font-semibold text-sm">
                                            {{ getInitials(user.name) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">{{ user.name }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">ID: {{ user.id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">{{ user.email }}</td>
                                <td class="px-4 py-4">
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-gray-400">
                                        {{ user.role ? user.role.charAt(0).toUpperCase() + user.role.slice(1) : 'User' }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">{{ user.organisasi || '-' }}</td>
                                <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">{{ user.jabatan || '-' }}</td>
                                <td class="px-4 py-4">
                                    <div class="flex justify-center">
                                        <button @click="activateUser(user)" class="p-2 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 rounded-lg transition-colors" title="Aktifkan">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <TransitionRoot appear :show="editModal" as="template">
            <Dialog as="div" class="relative z-50" @close="closeEditModal">
                <TransitionChild
                    as="template"
                    enter="duration-300 ease-out"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="duration-200 ease-in"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" />
                </TransitionChild>

                <div class="fixed inset-0 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4">
                        <TransitionChild
                            as="template"
                            enter="duration-300 ease-out"
                            enter-from="opacity-0 scale-95"
                            enter-to="opacity-100 scale-100"
                            leave="duration-200 ease-in"
                            leave-from="opacity-100 scale-100"
                            leave-to="opacity-0 scale-95"
                        >
                            <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white dark:bg-[#1a1a1a] p-6 shadow-xl transition-all">
                                <DialogTitle class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
                                    Edit User
                                </DialogTitle>
                                
                                <form @submit.prevent="submitEdit" class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama</label>
                                        <input 
                                            v-model="editForm.name" 
                                            type="text" 
                                            required 
                                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-white/5 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                                        >
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email</label>
                                        <input 
                                            v-model="editForm.email" 
                                            type="email" 
                                            required 
                                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-white/5 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                                        >
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Role</label>
                                        <select 
                                            v-model="editForm.role" 
                                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-white/5 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                                        >
                                            <option value="user">User</option>
                                            <option value="admin">Admin</option>
                                            <option value="pic">PIC</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Jabatan</label>
                                        <input 
                                            v-model="editForm.jabatan" 
                                            type="text" 
                                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-white/5 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                                        >
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Organisasi</label>
                                        <input 
                                            v-model="editForm.organisasi" 
                                            type="text" 
                                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-white/5 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                                        >
                                    </div>

                                    <div class="flex justify-end gap-3 pt-4">
                                        <button 
                                            type="button" 
                                            @click="closeEditModal" 
                                            class="px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-white/10 hover:bg-gray-200 dark:hover:bg-white/20 rounded-xl transition-colors"
                                        >
                                            Batal
                                        </button>
                                        <button 
                                            type="submit" 
                                            :disabled="editForm.processing" 
                                            class="px-4 py-2.5 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-colors disabled:opacity-50"
                                        >
                                            Simpan Perubahan
                                        </button>
                                    </div>
                                </form>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>

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
