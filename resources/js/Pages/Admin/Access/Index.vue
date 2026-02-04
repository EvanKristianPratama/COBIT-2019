<script setup>
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';

const props = defineProps({
    users: Array,
    modules: Array,
});

const page = usePage();
const searchQuery = ref('');
const selectedUsers = ref([]);
const showBulkModal = ref(false);
const bulkPermissions = ref([]);
const isUpdating = ref(false);

const filteredUsers = computed(() => {
    if (!searchQuery.value) return props.users;
    
    const query = searchQuery.value.toLowerCase();
    return props.users.filter(user => 
        user.name.toLowerCase().includes(query) ||
        user.email.toLowerCase().includes(query) ||
        user.organisasi?.toLowerCase().includes(query)
    );
});

const allSelected = computed(() => {
    return filteredUsers.value.length > 0 && 
           filteredUsers.value.every(user => selectedUsers.value.includes(user.id));
});

const toggleSelectAll = () => {
    if (allSelected.value) {
        selectedUsers.value = [];
    } else {
        selectedUsers.value = filteredUsers.value.map(user => user.id);
    }
};

const toggleUserSelection = (userId) => {
    const index = selectedUsers.value.indexOf(userId);
    if (index > -1) {
        selectedUsers.value.splice(index, 1);
    } else {
        selectedUsers.value.push(userId);
    }
};

const getModuleIcon = (iconName) => {
    const icons = {
        puzzle: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />`,
        clipboard: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />`,
        chart: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />`,
        document: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />`,
        cog: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />`,
    };
    return icons[iconName] || icons.puzzle;
};

const openBulkModal = () => {
    bulkPermissions.value = [];
    showBulkModal.value = true;
};

const toggleBulkPermission = (moduleId) => {
    const index = bulkPermissions.value.indexOf(moduleId);
    if (index > -1) {
        bulkPermissions.value.splice(index, 1);
    } else {
        bulkPermissions.value.push(moduleId);
    }
};

const applyBulkPermissions = () => {
    if (selectedUsers.value.length === 0 || bulkPermissions.value.length === 0) return;
    
    isUpdating.value = true;
    router.post(route('admin.access.bulk-update'), {
        user_ids: selectedUsers.value,
        permissions: bulkPermissions.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showBulkModal.value = false;
            selectedUsers.value = [];
            isUpdating.value = false;
        },
        onError: () => {
            isUpdating.value = false;
        },
    });
};
</script>

<template>
    <AdminLayout title="Access Management">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Access Management</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kelola akses user ke modul-modul dalam sistem</p>
                </div>
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

        <!-- Module Legend -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-xl border border-gray-200/80 dark:border-white/5 p-4 mb-6">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Modul Tersedia</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                <div 
                    v-for="module in modules" 
                    :key="module.id"
                    class="flex items-center gap-2 p-2 bg-gray-50 dark:bg-white/5 rounded-lg"
                >
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" v-html="getModuleIcon(module.icon)"></svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-900 dark:text-white">{{ module.name }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search & Bulk Actions -->
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
                            placeholder="Cari user..."
                            class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500"
                        />
                    </div>
                </div>
                <!-- Bulk Action Button -->
                <button
                    v-if="selectedUsers.length > 0"
                    @click="openBulkModal"
                    class="px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white text-sm font-medium rounded-xl transition-all flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                    Set Permissions ({{ selectedUsers.length }})
                </button>
            </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-xl border border-gray-200/80 dark:border-white/5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-white/5">
                            <th class="py-4 px-6 text-left">
                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        :checked="allSelected"
                                        @change="toggleSelectAll"
                                        class="w-4 h-4 text-emerald-500 border-gray-300 dark:border-gray-600 rounded focus:ring-emerald-500"
                                    />
                                </label>
                            </th>
                            <th class="text-left py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                            <th class="text-left py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
                            <th class="text-left py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Permissions</th>
                            <th class="text-right py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        <tr 
                            v-for="user in filteredUsers" 
                            :key="user.id"
                            class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors"
                        >
                            <td class="py-4 px-6">
                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        :checked="selectedUsers.includes(user.id)"
                                        @change="toggleUserSelection(user.id)"
                                        class="w-4 h-4 text-emerald-500 border-gray-300 dark:border-gray-600 rounded focus:ring-emerald-500"
                                    />
                                </label>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold text-sm">
                                        {{ user.name.substring(0, 2).toUpperCase() }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ user.name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span 
                                    :class="[
                                        'px-2.5 py-1 text-xs font-medium rounded-full',
                                        user.role === 'admin' 
                                            ? 'bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-400'
                                            : 'bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-400'
                                    ]"
                                >
                                    {{ user.role === 'admin' ? 'Admin' : 'User' }}
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex flex-wrap gap-1">
                                    <span 
                                        v-for="permission in user.permissions" 
                                        :key="permission"
                                        class="px-2 py-0.5 bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 text-xs rounded-md"
                                    >
                                        {{ modules.find(m => m.id === permission)?.name || permission }}
                                    </span>
                                    <span v-if="user.permissions.length === 0" class="text-xs text-gray-400">
                                        No permissions
                                    </span>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center justify-end">
                                    <Link
                                        :href="route('admin.access.edit', user.id)"
                                        class="px-3 py-1.5 text-xs font-medium text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 rounded-lg transition-colors"
                                    >
                                        Edit Permissions
                                    </Link>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredUsers.length === 0">
                            <td colspan="5" class="py-12 text-center">
                                <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <p class="text-gray-500 dark:text-gray-400">Tidak ada user ditemukan</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bulk Permission Modal -->
        <Teleport to="body">
            <div 
                v-if="showBulkModal" 
                class="fixed inset-0 z-50 overflow-y-auto"
            >
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="showBulkModal = false"></div>
                    
                    <div class="relative bg-white dark:bg-[#1a1a1a] rounded-2xl shadow-xl max-w-md w-full p-6 border border-gray-200 dark:border-white/10">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            Set Permissions untuk {{ selectedUsers.length }} User
                        </h3>
                        
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            Pilih modul yang akan diberikan akses:
                        </p>
                        
                        <div class="space-y-3 mb-6">
                            <label 
                                v-for="module in modules" 
                                :key="module.id"
                                class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-white/5 rounded-xl cursor-pointer hover:bg-gray-100 dark:hover:bg-white/10 transition-colors"
                            >
                                <input
                                    type="checkbox"
                                    :checked="bulkPermissions.includes(module.id)"
                                    @change="toggleBulkPermission(module.id)"
                                    class="mt-0.5 w-4 h-4 text-emerald-500 border-gray-300 dark:border-gray-600 rounded focus:ring-emerald-500"
                                />
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ module.name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ module.description }}</p>
                                </div>
                            </label>
                        </div>
                        
                        <div class="flex items-center justify-end gap-3">
                            <button
                                @click="showBulkModal = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-white/5 hover:bg-gray-200 dark:hover:bg-white/10 rounded-xl transition-colors"
                            >
                                Batal
                            </button>
                            <button
                                @click="applyBulkPermissions"
                                :disabled="bulkPermissions.length === 0 || isUpdating"
                                class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 rounded-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                            >
                                <svg v-if="isUpdating" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>{{ isUpdating ? 'Menyimpan...' : 'Terapkan' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </AdminLayout>
</template>
